<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GuardedHttp
{
    /**
     * A pending HTTP client pinned to a validated public IP for the given URL.
     *
     * @throws SsrfException
     */
    public static function for(string $url): PendingRequest
    {
        [$host, $port, $ip] = SsrfGuard::validate($url);

        return Http::withOptions([
            'curl' => [
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"],
            ],
            'allow_redirects' => false,
        ]);
    }
}
