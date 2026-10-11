<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class AiServiceException extends RuntimeException
{
    public const REASON_CONFIGURATION = 'configuration';

    public const REASON_CONNECTION = 'connection';

    public const REASON_HTTP = 'http';

    public const REASON_INVALID_RESPONSE = 'invalid_response';

    public function __construct(
        public readonly string $reason,
        public readonly ?int $status = null,
    ) {
        parent::__construct('The AI service request could not be completed.');
    }

    public static function configuration(): self
    {
        return new self(self::REASON_CONFIGURATION);
    }

    public static function connection(): self
    {
        return new self(self::REASON_CONNECTION);
    }

    public static function http(int $status): self
    {
        return new self(self::REASON_HTTP, $status);
    }

    public static function invalidResponse(): self
    {
        return new self(self::REASON_INVALID_RESPONSE);
    }

    public function isClientError(): bool
    {
        return $this->status !== null && $this->status >= 400 && $this->status < 500;
    }
}
