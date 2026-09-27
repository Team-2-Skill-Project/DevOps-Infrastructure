<?php

return [
    'source_mismatch' => 'The listing source does not match the ingestion run.',
    'run_finished' => 'Raw jobs cannot be added to a finished ingestion run.',
    'already_finalized' => 'This raw job extraction has already finished.',
    'normalization_requires_extraction' => 'Job normalization requires successful detail extraction.',
    'normalization_already_finalized' => 'This raw job normalization has already finished.',
    'invalid_payload' => 'The raw payload must contain valid JSON data.',
    'sensitive_data' => 'Credential-like content cannot be stored as raw job data.',
    'invalid_url' => 'The source URL must be a valid HTTP or HTTPS URL.',
    'errors' => [
        'extraction_failed' => 'Job detail extraction failed.',
        'invalid_payload' => 'The job payload could not be extracted.',
        'unsupported_format' => 'The job payload format is unsupported.',
    ],
    'normalization_errors' => [
        'invalid_data' => 'The extracted job data could not be normalized.',
        'unsupported_value' => 'A job value is not supported for normalization.',
    ],
    'deduplication_requires_normalization' => 'Job deduplication requires successful normalization.',
    'deduplication_already_finalized' => 'This raw job deduplication has already finished.',
    'reference_provenance_mismatch' => 'The source reference provenance does not match the raw job.',
    'deduplication_errors' => [
        'deduplication_failed' => 'Job deduplication failed.',
    ],
];
