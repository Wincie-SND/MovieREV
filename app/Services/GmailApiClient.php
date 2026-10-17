<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GmailApiClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SEND_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

    public function sendRawMessage(string $rawMessage): void
    {
        $raw = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

        Http::acceptJson()
            ->asJson()
            ->withToken($this->accessToken())
            ->timeout(20)
            ->post(self::SEND_URL, ['raw' => $raw])
            ->throw();
    }

    private function accessToken(): string
    {
        $clientId = (string) config('services.gmail.client_id');
        $clientSecret = (string) config('services.gmail.client_secret');
        $refreshToken = (string) config('services.gmail.refresh_token');

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            throw new RuntimeException('Gmail API credentials are missing. Configure GMAIL_API_CLIENT_ID, GMAIL_API_CLIENT_SECRET, and GMAIL_API_REFRESH_TOKEN.');
        }

        $cacheKey = 'gmail-api:access-token:'.hash('sha256', $clientId.'|'.$refreshToken);
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post(self::TOKEN_URL, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ])
            ->throw();

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Google did not return a Gmail API access token.');
        }

        $expiresIn = max(60, (int) $response->json('expires_in', 3600) - 60);

        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }
}
