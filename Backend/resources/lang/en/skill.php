<?php

return [
    'not_found' => 'Skill not found.',
    'alias_not_found' => 'Skill alias not found.',
    'forbidden' => 'You are not authorized to access this skill.',
    'admin_account_inactive' => 'Your account is inactive.',
    'admin_only' => 'Only administrators can manage skills.',
    'validation' => [
        'name_taken' => 'This skill name is already in use.',
        'alias_taken' => 'This skill alias is already in use.',
        'name_unavailable' => 'This name is already used by a canonical skill or alias.',
        'different_target' => 'Choose a different target skill.',
        'merge_subject_missing' => 'The source or target skill no longer exists.',
        'merge_conflict' => 'The taxonomy changed during this merge. Retry after resolving conflicting names or links.',
    ],
    'attributes' => [
        'name' => 'name',
        'proficiency_level' => 'proficiency level',
        'q' => 'search query',
        'skill_category_id' => 'skill category',
        'category' => 'category',
        'alias' => 'alias',
        'target_skill_id' => 'target skill',
    ],
];
