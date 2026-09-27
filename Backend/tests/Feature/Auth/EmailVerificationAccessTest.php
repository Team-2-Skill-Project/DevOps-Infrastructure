<?php

use App\Models\User;

test('verified Google users can access account settings', function () {
    $user = User::factory()->create([
        'phone' => null,
        'google_id' => 'google-123',
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();
});
