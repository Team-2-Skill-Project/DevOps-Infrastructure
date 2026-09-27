<?php

namespace App\Actions\Fortify;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class SendRegistrationEmailVerification extends SendEmailVerificationNotification
{
    public function handle(Registered $event): void
    {
        if ($event->user instanceof User && EmailOtp::where('user_id', $event->user->id)
            ->where('email', $event->user->email)
            ->where('purpose', EmailOtp::EMAIL_VERIFICATION)
            ->exists()) {
            return;
        }

        parent::handle($event);
    }
}
