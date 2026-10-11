<?php

return [
    'uploaded' => 'CV uploaded successfully and queued for processing.',
    'processing_retried' => 'CV processing retried successfully.',
    'extraction_verified' => 'Extracted data verified and synced to profile successfully.',
    'candidate_profile_not_found' => 'Candidate profile not found.',
    'document_not_found' => 'CV document not found.',
    'extraction_not_found' => 'CV extraction not found.',
    'forbidden' => 'You are not authorized to access this CV.',
    'validation' => [
        'empty_file' => 'The uploaded file is empty and contains no data.',
    ],
    'attributes' => [
        'cv' => 'CV',
        'skills' => 'skills',
        'skills.*.name' => 'skill name',
        'skills.*.category' => 'skill category',
        'skills.*.proficiency_level' => 'skill proficiency level',
        'skills.*.confidence_score' => 'skill confidence score',
        'experiences' => 'experiences',
        'experiences.*.company_name' => 'company name',
        'experiences.*.title' => 'job title',
        'experiences.*.start_date' => 'experience start date',
        'experiences.*.end_date' => 'experience end date',
        'experiences.*.description' => 'experience description',
    ],
    // Compatibility aliases for keys introduced upstream.
    'profile_not_found' => 'Candidate profile not found.',
    'uploaded_success' => 'CV uploaded successfully and queued for processing.',
    'retried_success' => 'CV processing retried successfully.',
    'verified_success' => 'Extracted data verified and synced to profile successfully.',
];
