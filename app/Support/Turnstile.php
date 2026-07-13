<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class Turnstile
{
    public static function configured(): bool
    {
        return ! empty(config('services.turnstile.secret'));
    }

    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (! static::configured()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->ok() && $response->json('success') === true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
