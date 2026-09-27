<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only(['id', 'name', 'email', 'phone', 'avatar', 'email_verified_at', 'role']),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
