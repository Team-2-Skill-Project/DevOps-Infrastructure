<?php

namespace App\Services;

/**
 * Outcome of duplicate evaluation for a normalized RawJob.
 *
 * Decisions: matched (strong evidence, canonical reused), possible
 * (uncertain similarity, never merged), distinct (no duplicate found),
 * failed (evaluation could not complete safely).
 */
final readonly class DuplicateDecision
{
    /**
     * @param  array<string, mixed>  $evidence  Sanitized, explainable evidence only; never secrets or payloads.
     */
    public function __construct(
        public string $decision,
        public ?int $canonicalJobPostId,
        public ?string $method,
        public string $fingerprint,
        public int $fingerprintVersion,
        public array $evidence,
    ) {}

    public function isMatched(): bool
    {
        return $this->decision === 'matched';
    }
}
