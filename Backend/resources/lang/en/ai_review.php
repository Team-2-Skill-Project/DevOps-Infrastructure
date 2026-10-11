<?php

return [
    'not_found' => 'AI review item not found.',
    'invalid_state' => 'This AI review item has already been decided.',
    'unsupported_entity' => 'This AI review item type is not supported.',
    'invalid_correction' => 'The corrected AI value is invalid.',
    'attributes' => [
        'status' => 'status',
        'entity_type' => 'entity type',
        'reason' => 'review reason',
        'source' => 'source',
        'reviewer_id' => 'reviewer',
        'confidence_min' => 'minimum confidence',
        'confidence_max' => 'maximum confidence',
        'created_from' => 'created-from date',
        'created_to' => 'created-to date',
        'search' => 'search',
        'corrected_value' => 'corrected value',
        'corrected_value.skills' => 'corrected skills',
        'corrected_value.skills.*.name' => 'corrected skill name',
        'corrected_value.experiences' => 'corrected experiences',
        'corrected_value.experiences.*.company_name' => 'corrected company name',
        'corrected_value.experiences.*.title' => 'corrected job title',
        'decision_reason' => 'decision reason',
        'reviewer_notes' => 'reviewer notes',
    ],
];
