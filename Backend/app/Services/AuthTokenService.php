<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tymon\JWTAuth\JWTGuard;

class AuthTokenService
{
    /**
     * Issue a JWT access token and an opaque refresh token for an authorized user.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function issue(User $user): array
    {
        return DB::transaction(function () use ($user): array {
            $refreshToken = $this->createRefreshToken($user);
            $guard = $this->guard();
            $accessToken = $guard->login($user);

            return $this->tokenResponse($guard, $accessToken, $refreshToken['plain_text']);
        });
    }

    /**
     * Rotate a valid refresh token and reject every token that has already been rotated.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}|null
     */
    public function rotate(string $plainTextToken): ?array
    {
        if ($plainTextToken === '' || strlen($plainTextToken) > 256) {
            return null;
        }

        $tokenHash = $this->hash($plainTextToken);

        return DB::transaction(function () use ($tokenHash): ?array {
            $refreshToken = RefreshToken::query()
                ->where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if ($refreshToken === null || $refreshToken->revoked_at !== null
                || now()->greaterThanOrEqualTo($refreshToken->expires_at)) {
                return null;
            }

            $user = $refreshToken->user;
            if ($user === null || ! $user->hasVerifiedEmail() || ! (bool) $user->is_active) {
                $refreshToken->forceFill(['revoked_at' => now()])->save();

                return null;
            }

            $replacement = $this->createRefreshToken($user);
            $refreshToken->forceFill(['revoked_at' => now()])->save();

            $guard = $this->guard();
            $accessToken = $guard->login($user);

            return $this->tokenResponse($guard, $accessToken, $replacement['plain_text']);
        });
    }

    public function revokeForUser(User $user, ?string $plainTextToken = null): void
    {
        $query = $user->refreshTokens()->whereNull('revoked_at');

        if ($plainTextToken !== null && $plainTextToken !== '') {
            $query->where('token_hash', $this->hash($plainTextToken));
        }

        $query->update(['revoked_at' => now()]);
    }

    /**
     * @return array{plain_text: string, token_hash: string}
     */
    private function createRefreshToken(User $user): array
    {
        $plainTextToken = bin2hex(random_bytes(64));

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => $this->hash($plainTextToken),
            'expires_at' => now()->addMinutes($this->refreshTokenTtl()),
        ]);

        return [
            'plain_text' => $plainTextToken,
            'token_hash' => $this->hash($plainTextToken),
        ];
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    private function tokenResponse(JWTGuard $guard, string $accessToken, string $refreshToken): array
    {
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ];
    }

    private function refreshTokenTtl(): int
    {
        $ttl = filter_var(config('auth.refresh_token_ttl', 20160), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($ttl === false) {
            throw new RuntimeException('The refresh token lifetime configuration is invalid.');
        }

        return $ttl;
    }

    private function hash(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }

    private function guard(): JWTGuard
    {
        $guard = Auth::guard('api');

        if (! $guard instanceof JWTGuard) {
            throw new RuntimeException('The API authentication guard is not configured for JWT.');
        }

        return $guard;
    }
}
