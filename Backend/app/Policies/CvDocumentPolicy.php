<?php

namespace App\Policies;

use App\Models\CvDocument;
use App\Models\User;

class CvDocumentPolicy
{
    public function view(User $user, CvDocument $cvDocument): bool
    {
        return $user->candidateProfile?->id === $cvDocument->candidate_profile_id;
    }

    public function update(User $user, CvDocument $cvDocument): bool
    {
        return $user->candidateProfile?->id === $cvDocument->candidate_profile_id;
    }
}
