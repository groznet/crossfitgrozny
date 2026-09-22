<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsRuGateway
{
    private const ENDPOINT = 'https://sms.ru/sms/send';

    /**
     * Send a single SMS. Returns false (and logs) on any failure so callers
     * can decide how to surface that without ever crashing the request.
     */
    public function send(string $phone, string $message, ?string $ip = null): bool
    {
        $to = preg_replace('/\D/', '', $phone);

        $response = Http::asForm()->post(self::ENDPOINT, [
            'api_id' => config('services.smsru.api_id'),
            'to' => $to,
            'msg' => $message,
            'json' => 1,
            'test' => config('services.smsru.test_mode') ? 1 : 0,
            'ip' => $ip,
        ]);

        $body = $response->json();

        if (($body['status'] ?? null) !== 'OK' || ($body['sms'][$to]['status'] ?? null) !== 'OK') {
            Log::warning('sms.ru: send failed', ['phone' => $to, 'response' => $body]);

            return false;
        }

        return true;
    }
}
