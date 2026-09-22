<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OtpService
{
    private const CODE_TTL_MINUTES = 5;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private SmsRuGateway $gateway) {}

    /**
     * Generate a code for a freshly-submitted form, stash the validated
     * field data alongside it (so verify() can create the Member without
     * asking the visitor to re-fill or re-upload anything), and text it.
     */
    public function send(string $phone, array $formData, ?string $ip): void
    {
        $code = $this->generateCode();

        Cache::put($this->key($phone), [
            'code' => $code,
            'data' => $formData,
            'attempts' => 0,
            'last_sent_at' => now()->timestamp,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        $this->gateway->send($phone, $this->message($code), $ip);
    }

    /**
     * Re-send a fresh code for an already-stashed submission. Returns false
     * (without sending anything) if there's nothing stashed for this phone
     * or the cooldown hasn't elapsed yet.
     */
    public function resend(string $phone, ?string $ip): bool
    {
        $entry = Cache::get($this->key($phone));

        if (! $entry || $this->cooldownRemaining($phone) > 0) {
            return false;
        }

        $entry['code'] = $this->generateCode();
        $entry['attempts'] = 0;
        $entry['last_sent_at'] = now()->timestamp;

        Cache::put($this->key($phone), $entry, now()->addMinutes(self::CODE_TTL_MINUTES));

        $this->gateway->send($phone, $this->message($entry['code']), $ip);

        return true;
    }

    public function cooldownRemaining(string $phone): int
    {
        $entry = Cache::get($this->key($phone));

        if (! $entry) {
            return 0;
        }

        return max(0, self::RESEND_COOLDOWN_SECONDS - (now()->timestamp - $entry['last_sent_at']));
    }

    /**
     * @return array{ok: bool, data?: array, reason?: string, attemptsLeft?: int}
     */
    public function verify(string $phone, string $code): array
    {
        $key = $this->key($phone);
        $entry = Cache::get($key);

        if (! $entry) {
            return ['ok' => false, 'reason' => 'expired'];
        }

        if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return ['ok' => false, 'reason' => 'too_many_attempts'];
        }

        if (! hash_equals($entry['code'], $code)) {
            $entry['attempts']++;

            if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($key);

                return ['ok' => false, 'reason' => 'too_many_attempts'];
            }

            Cache::put($key, $entry, now()->addMinutes(self::CODE_TTL_MINUTES));

            return [
                'ok' => false,
                'reason' => 'invalid_code',
                'attemptsLeft' => self::MAX_ATTEMPTS - $entry['attempts'],
            ];
        }

        Cache::forget($key);

        return ['ok' => true, 'data' => $entry['data']];
    }

    private function generateCode(): string
    {
        return (string) random_int(1000, 9999);
    }

    private function message(string $code): string
    {
        return "Grozny Gym: код подтверждения {$code}";
    }

    private function key(string $phone): string
    {
        return "profile-otp:{$phone}";
    }
}
