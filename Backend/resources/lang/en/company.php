<?php

return [
    'not_found' => 'Company not found.',
    'forbidden' => 'You are not authorized to access this company.',
    'validation' => [
        'name_taken' => 'This company name is already in use.',
        'merge_subject_missing' => 'The source or target company no longer exists.',
        'merge_conflict' => 'The company merge encountered a conflicting company or alias.',
        'name_unavailable' => 'This company name is already used by a company or alias.',
        'merge_alias_conflict' => 'A company name or alias conflicts with another company.',
        'different_target' => 'Choose a different target company.',
    ],
    'attributes' => [
        'name' => 'name',
        'website_url' => 'website URL',
        'linkedin_url' => 'LinkedIn URL',
        'logo_url' => 'logo URL',
        'industry' => 'industry',
        'country' => 'country',
        'state' => 'state',
        'city' => 'city',
        'description' => 'description',
        'is_verified' => 'verification status',
        'is_active' => 'active status',
        'search' => 'search',
        'per_page' => 'items per page',
        'page' => 'page',
        'target_company_id' => 'target company',
    ],
];
