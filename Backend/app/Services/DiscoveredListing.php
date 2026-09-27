<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Validator;

final readonly class DiscoveredListing
{
    public CarbonImmutable $discoveredAt;

    /** @param array<string, mixed>|null $metadata */
    public function __construct(
        public int $jobSourceId,
        public string $sourceUrl,
        public ?string $externalId = null,
        public ?string $detailUrl = null,
        public ?string $title = null,
        public ?string $companyName = null,
        ?DateTimeInterface $discoveredAt = null,
        public ?array $metadata = null,
    ) {
        Validator::make([
            'job_source_id' => $jobSourceId, 'source_url' => $sourceUrl, 'external_id' => $externalId,
            'detail_url' => $detailUrl, 'title' => $title, 'company_name' => $companyName,
        ], [
            'job_source_id' => ['required', 'integer', 'min:1'],
            'source_url' => ['required', 'url:http,https', 'max:2048'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'detail_url' => ['nullable', 'url:http,https', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ])->validate();
        $this->discoveredAt = $discoveredAt === null ? CarbonImmutable::now() : CarbonImmutable::instance($discoveredAt);
    }
}
