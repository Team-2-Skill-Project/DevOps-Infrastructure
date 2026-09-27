<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailPasswordResetRequest;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailPasswordResetController extends Controller
{
    use PasswordValidationRules;

    public function store(EmailPasswordResetRequest $request, EmailOtpService $emailOtpService): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $emailOtpService): JsonResponse {
                $user = User::whereRaw('LOWER(email) = ?', [Str::lower($request->validated('email'))])->lockForUpdate()->first();

                if (! $user) {
                    return $this->invalid('email', __('auth.reset_code_failed'));
                }

                $previousOtp = $this->resetOtp($user);

                if ($previousOtp?->last_sent_at?->greaterThan(now()->subMinute())) {
                    return response()->json(['message' => __('auth.wait_before_new_code')], 429)
                        ->header('Retry-After', '60');
                }

                $emailOtpService->send($user, EmailOtp::PASSWORD_RESET);

                return response()->json(['message' => __('auth.password_reset_code_sent')]);
            });
        } catch (TransportExceptionInterface) {
            return response()->json(['message' => __('auth.email_code_failed')], 503);
        }
    }

    public function verifyOtp(EmailPasswordResetRequest $request): JsonResponse
    {
        $validated = $request->validate(['otp' => ['required', 'string', 'regex:/^[0-9]{6}$/']]);

        return DB::transaction(function () use ($request, $validated): JsonResponse {
            $user = User::whereRaw('LOWER(email) = ?', [Str::lower($request->validated('email'))])->lockForUpdate()->first();
            $emailOtp = $user ? $this->resetOtp($user) : null;

            if (! $emailOtp || $emailOtp->reset_token_hash || now()->greaterThanOrEqualTo($emailOtp->expires_at)) {
                return $this->invalid('otp', __('auth.invalid_or_expired_code'));
            }

            if ($emailOtp->attempts >= 5) {
                return response()->json(['message' => __('auth.too_many_invalid_attempts')], 429);
            }

            if (! Hash::check($validated['otp'], $emailOtp->code_hash)) {
                $emailOtp->increment('attempts');

                return $this->invalid('otp', __('auth.invalid_or_expired_code'));
            }

            $resetToken = Str::random(64);
            $emailOtp->update([
                'reset_token_hash' => Hash::make($resetToken),
                'reset_token_expires_at' => now()->addMinutes(10),
            ]);

            return response()->json([
                'message' => __('auth.reset_code_verified'),
                'reset_token' => $resetToken,
                'expires_in' => 600,
            ])->header('Cache-Control', 'no-store');
        });
    }

    public function resetPassword(EmailPasswordResetRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string', 'size:64'],
            'password' => $this->passwordRules(),
        ]);

        return DB::transaction(function () use ($request, $validated): JsonResponse {
            $user = User::whereRaw('LOWER(email) = ?', [Str::lower($request->validated('email'))])->lockForUpdate()->first();
            $emailOtp = $user ? $this->resetOtp($user) : null;

            if (! $emailOtp?->reset_token_hash || ! $emailOtp->reset_token_expires_at
                || now()->greaterThanOrEqualTo($emailOtp->reset_token_expires_at)
                || ! Hash::check($validated['reset_token'], $emailOtp->reset_token_hash)) {
                return $this->invalid('reset_token', __('auth.invalid_reset_session'));
            }

            $user->password = Hash::make($validated['password']);
            $user->setRememberToken(Str::random(60));
            $user->save();

            EmailOtp::where('user_id', $user->id)->where('purpose', EmailOtp::PASSWORD_RESET)->delete();
            Password::broker(config('fortify.passwords'))->deleteToken($user);
            event(new PasswordReset($user));

            return response()->json(['message' => __('auth.password_reset_successful')]);
        });
    }

    private function resetOtp(User $user): ?EmailOtp
    {
        return EmailOtp::where('user_id', $user->id)
            ->where('email', $user->email)
            ->where('purpose', EmailOtp::PASSWORD_RESET)
            ->latest('id')->lockForUpdate()->first();
    }

    private function invalid(string $field, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
    }
}
