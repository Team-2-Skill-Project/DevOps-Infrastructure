<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;

class RawJobDataSanitizer
{
    /**
     * Collectors must supply public job content only. Strip credential/header fields recursively;
     * reject recognizable credentials in free text rather than persisting raw diagnostics.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function structured(array $data): array
    {
        try {
            json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages(['raw_payload' => __('raw_jobs.invalid_payload')]);
        }

        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && $this->sensitiveKey($key)) {
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = $this->structured($value);
            } elseif (is_string($value)) {
                $clean[$key] = $this->text($value);
            } elseif ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
                $clean[$key] = $value;
            } else {
                throw ValidationException::withMessages(['raw_payload' => __('raw_jobs.invalid_payload')]);
            }
        }

        return $clean;
    }

    public function text(string $text): string
    {
        if (preg_match('/^(?:(?:GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS|CONNECT|TRACE)\s+\S+\s+HTTP\/|HTTP\/)/im', $text)
            || preg_match('/(?:authorization|set-cookie|cookie|password|passwd|secret|api[_ -]?key|access[_ -]?token|refresh[_ -]?token|token)\s*["\']?\s*[:=]|\b(?:Bearer|Basic)\s+[A-Za-z0-9+\/_=-]{8,}|-----BEGIN [A-Z ]*PRIVATE KEY-----/i', $text)) {
            throw ValidationException::withMessages(['raw_payload' => __('raw_jobs.sensitive_data')]);
        }

        return preg_replace_callback('~https?://[^\s<>"\']+~i', fn (array $match): string => $this->normalizeUrl($match[0]), $text) ?? $text;
    }

    public function normalizeUrl(string $url): string
    {
        Validator::make(['url' => $url], ['url' => ['required', 'url:http,https', 'max:2048']])->validate();
        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['host'], $parts['scheme'])) {
            throw ValidationException::withMessages(['url' => __('raw_jobs.invalid_url')]);
        }
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? null;
        $portPart = $port !== null && ! ($scheme === 'https' && $port === 443) && ! ($scheme === 'http' && $port === 80) ? ':'.$port : '';
        $query = [];
        foreach (explode('&', $parts['query'] ?? '') as $parameter) {
            if ($parameter === '') {
                continue;
            }
            $pair = explode('=', $parameter, 2);
            $name = urldecode($pair[0]);
            if (! $this->sensitiveKey($name)) {
                $query[] = rawurlencode($name).(isset($pair[1]) ? '='.rawurlencode(urldecode($pair[1])) : '');
            }
        }
        sort($query, SORT_STRING);

        return $scheme.'://'.$host.$portPart.($parts['path'] ?? '/').($query ? '?'.implode('&', $query) : '');
    }

    private function sensitiveKey(string $key): bool
    {
        $key = strtolower(preg_replace('/[^a-z0-9]/i', '', $key) ?? $key);

        return preg_match('/authorization|credential|password|passwd|secret|token|apikey|cookie|headers|privatekey|signature/', $key) === 1
            || in_array($key, ['auth', 'key', 'session', 'sessionid'], true);
    }
}
