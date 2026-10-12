<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\AuthTokenService;
use App\Services\EmailOtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user('api')]);
    }

    public function login(Request $request, AuthTokenService $authTokenService): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        if (! $guard->validate($credentials)) {
            return response()->json(['message' => __('auth.invalid_credentials')], 401);
        }

        /** @var User $user */
        $user = $guard->getLastAttempted();

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.email_not_verified')], 403);
        }

        if (! (bool) $user->is_active) {
            return response()->json(['message' => __('auth.account_inactive')], 403);
        }

        $tokens = $authTokenService->issue($user);

        return response()->json([
            ...$tokens,
            'user' => $user,
        ]);
    }

    public function register(Request $request, EmailOtpService $emailOtpService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = DB::transaction(function () use ($validated, $emailOtpService): User {
                $user = User::create($validated);
                $emailOtpService->send($user, EmailOtp::EMAIL_VERIFICATION);

                return $user;
            });
        } catch (TransportExceptionInterface) {
            return response()->json(['message' => __('auth.registration_email_failed')], 503);
        }

        return response()->json([
            'message' => __('auth.registration_successful'),
            'email' => $user->email,
            'user' => $user,
        ], 201);
    }

    public function verifyEmailOtp(Request $request, AuthTokenService $authTokenService): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ]);

        $result = DB::transaction(function () use ($validated): User|JsonResponse {
            $user = User::whereRaw('LOWER(email) = ?', [Str::lower($validated['email'])])->lockForUpdate()->first();
            $otp = $user ? EmailOtp::where('user_id', $user->id)->where('email', $user->email)
                ->where('purpose', EmailOtp::EMAIL_VERIFICATION)->lockForUpdate()->first() : null;

            if (! $otp || $user->hasVerifiedEmail() || now()->greaterThanOrEqualTo($otp->expires_at)) {
                return $this->invalidCode();
            }

            if ($otp->attempts >= 5) {
                return response()->json(['message' => __('auth.too_many_invalid_attempts')], 429);
            }

            if (! (bool) $user->is_active) {
                return response()->json(['message' => __('auth.account_inactive')], 403);
            }

            if (! Hash::check($validated['otp'], $otp->code_hash)) {
                $otp->increment('attempts');

                return $this->invalidCode();
            }

            $user->markEmailAsVerified();
            $otp->delete();
            event(new Verified($user));

            return $user;
        });

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'message' => __('auth.email_verified'),
            ...$authTokenService->issue($result),
            'user' => $result,
        ]);
    }

    public function refresh(Request $request, AuthTokenService $authTokenService): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => ['required', 'string', 'max:256'],
        ]);

        $tokens = $authTokenService->rotate($validated['refresh_token']);

        if ($tokens === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        return response()->json($tokens);
    }

    public function logout(Request $request, AuthTokenService $authTokenService): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => ['sometimes', 'nullable', 'string', 'max:256'],
        ]);

        /** @var User $user */
        $user = $request->user('api');
        $authTokenService->revokeForUser($user, $validated['refresh_token'] ?? null);
        Auth::guard('api')->logout();

        return response()->json(['message' => __('auth.logout_successful')]);
    }

    public function resendEmailOtp(Request $request, EmailOtpService $emailOtpService): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        try {
            return DB::transaction(function () use ($validated, $emailOtpService): JsonResponse {
                $user = User::whereRaw('LOWER(email) = ?', [Str::lower($validated['email'])])->lockForUpdate()->first();
                if (! $user || $user->hasVerifiedEmail()) {
                    return response()->json(['message' => __('auth.unverified_account_required')], 422);
                }
                $emailOtpService->send($user, EmailOtp::EMAIL_VERIFICATION);

                return response()->json(['message' => __('auth.verification_code_resent')]);
            });
        } catch (TransportExceptionInterface) {
            return response()->json(['message' => __('auth.email_code_failed')], 503);
        }
    }

    private function invalidCode(): JsonResponse
    {
        $message = __('auth.invalid_or_expired_code');

        return response()->json(['message' => $message, 'errors' => ['otp' => [$message]]], 422);
    }
}
