<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Exceptions\AiServiceException;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FastApiAiClient
{
    private string $baseUrl;

    private ?string $apiKey;

    private int $timeout;

    private int $connectTimeout;

    public function __construct()
    {
        $baseUrl = config('services.ai.url');
        $apiKey = config('services.ai.key');

        $this->baseUrl = is_string($baseUrl) ? rtrim(trim($baseUrl), '/') : '';
        $this->apiKey = is_string($apiKey) && trim($apiKey) !== '' ? $apiKey : null;
        $this->timeout = max(1, (int) config('services.ai.timeout', 30));
        $this->connectTimeout = max(1, (int) config('services.ai.connect_timeout', 5));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function postJson(string $path, array $payload): array
    {
        return $this->send('POST', $path, function (PendingRequest $request) use ($path, $payload): Response {
            return $request->asJson()->post($this->requestPath($path), $payload);
        });
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     */
    public function postMultipart(
        string $path,
        string $field,
        string $contents,
        string $filename,
        ?string $mimeType = null,
        array $fields = [],
    ): array {
        return $this->send('POST', $path, function (PendingRequest $request) use ($path, $field, $contents, $filename, $mimeType, $fields): Response {
            $headers = $mimeType === null ? [] : ['Content-Type' => $mimeType];

            return $request
                ->attach($field, $contents, $filename, $headers)
                ->post($this->requestPath($path), $fields);
        });
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function getJson(string $path, array $query = []): array
    {
        return $this->send('GET', $path, function (PendingRequest $request) use ($path, $query): Response {
            return $request->get($this->requestPath($path), $query);
        });
    }

    /**
     * @param  Closure(PendingRequest): Response  $send
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, Closure $send): array
    {
        $this->validateConfiguration($path);
        $startedAt = hrtime(true);

        try {
            $response = $send($this->pendingRequest());
        } catch (ConnectionException) {
            $this->logFailure($method, $path, null, 'connection', $startedAt);

            throw AiServiceException::connection();
        } catch (AiServiceException $exception) {
            throw $exception;
        } catch (\Throwable) {
            $this->logFailure($method, $path, null, 'connection', $startedAt);

            throw AiServiceException::connection();
        }

        if (! $response->successful()) {
            $this->logFailure($method, $path, $response->status(), 'http', $startedAt);

            throw AiServiceException::http($response->status());
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->logFailure($method, $path, $response->status(), 'invalid_response', $startedAt);

            throw AiServiceException::invalidResponse();
        }

        Log::debug('AI service request completed', [
            'method' => $method,
            'path' => $this->requestPath($path),
            'status' => $response->status(),
            'duration_ms' => $this->durationInMilliseconds($startedAt),
        ]);

        return $payload;
    }

    private function pendingRequest(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout);

        return $this->apiKey === null
            ? $request
            : $request->withHeader('X-API-Key', $this->apiKey);
    }

    private function validateConfiguration(string $path): void
    {
        $scheme = parse_url($this->baseUrl, PHP_URL_SCHEME);

        if ($this->baseUrl === '' || ! filter_var($this->baseUrl, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
            Log::error('AI service configuration is invalid', [
                'path' => $this->requestPath($path),
            ]);

            throw AiServiceException::configuration();
        }
    }

    private function requestPath(string $path): string
    {
        return '/'.ltrim($path, '/');
    }

    private function durationInMilliseconds(int $startedAt): float
    {
        return round((hrtime(true) - $startedAt) / 1_000_000, 2);
    }

    private function logFailure(string $method, string $path, ?int $status, string $reason, int $startedAt): void
    {
        Log::warning('AI service request failed', [
            'method' => $method,
            'path' => $this->requestPath($path),
            'status' => $status,
            'reason' => $reason,
            'duration_ms' => $this->durationInMilliseconds($startedAt),
        ]);
    }
}
