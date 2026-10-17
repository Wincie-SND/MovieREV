<?php

namespace App\Services;

use App\Mail\AccountVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountVerificationCodeService
{
    public const EMAIL_CHANGE = 'email-change';

    public const PASSWORD_CHANGE = 'password-change';

    public const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function send(User $user, string $purpose, string $recipient, ?string $destination = null): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);
        $key = $this->key($user, $purpose);

        Cache::put($key, [
            'hash' => Hash::make($code),
            'destination' => $destination,
            'expires_at' => $expiresAt->timestamp,
        ], $expiresAt);
        Cache::put($this->attemptsKey($user, $purpose), 0, $expiresAt);

        try {
            Mail::to($recipient)->send(new AccountVerificationCodeMail($code, $purpose, $user->name));
        } catch (Throwable $exception) {
            $this->forget($user, $purpose);

            throw $exception;
        }
    }

    public function pendingDestination(User $user, string $purpose): ?string
    {
        $record = Cache::get($this->key($user, $purpose));

        if (! is_array($record) || (int) ($record['expires_at'] ?? 0) <= now()->timestamp) {
            $this->forget($user, $purpose);

            return null;
        }

        return is_string($record['destination'] ?? null) ? $record['destination'] : null;
    }

    public function hasPendingCode(User $user, string $purpose): bool
    {
        $record = Cache::get($this->key($user, $purpose));

        if (! is_array($record) || (int) ($record['expires_at'] ?? 0) <= now()->timestamp) {
            $this->forget($user, $purpose);

            return false;
        }

        return true;
    }

    public function verify(User $user, string $purpose, string $code): ?string
    {
        $key = $this->key($user, $purpose);

        return Cache::lock($key.':verification-lock', 10)->block(3, function () use ($key, $user, $purpose, $code): ?string {
            $record = Cache::get($key);

            if (! is_array($record) || (int) ($record['expires_at'] ?? 0) <= now()->timestamp) {
                $this->forget($user, $purpose);

                throw ValidationException::withMessages([
                    'verification_code' => 'That code has expired. Request a new one and try again.',
                ]);
            }

            $attemptsKey = $this->attemptsKey($user, $purpose);
            $attempts = (int) Cache::get($attemptsKey, 0);

            if ($attempts >= self::MAX_ATTEMPTS) {
                $this->forget($user, $purpose);

                throw ValidationException::withMessages([
                    'verification_code' => 'Too many incorrect attempts. Request a new code.',
                ]);
            }

            if (! Hash::check($code, (string) ($record['hash'] ?? ''))) {
                $attempts++;

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $this->forget($user, $purpose);

                    throw ValidationException::withMessages([
                        'verification_code' => 'Too many incorrect attempts. Request a new code.',
                    ]);
                }

                $remainingSeconds = max(1, (int) $record['expires_at'] - now()->timestamp);
                Cache::put($attemptsKey, $attempts, now()->addSeconds($remainingSeconds));

                throw ValidationException::withMessages([
                    'verification_code' => 'That code is incorrect. Check it and try again.',
                ]);
            }

            $destination = is_string($record['destination'] ?? null) ? $record['destination'] : null;
            $this->forget($user, $purpose);

            return $destination;
        });
    }

    private function forget(User $user, string $purpose): void
    {
        Cache::forget($this->key($user, $purpose));
        Cache::forget($this->attemptsKey($user, $purpose));
    }

    private function key(User $user, string $purpose): string
    {
        return 'account-verification-code:'.$purpose.':'.$user->getKey();
    }

    private function attemptsKey(User $user, string $purpose): string
    {
        return $this->key($user, $purpose).':attempts';
    }
}
