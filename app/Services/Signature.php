<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Signature
{
    public function keyId(): string
    {
        return RelayActor::id().'#main-key';
    }

    protected function privateKey(): string
    {
        return File::get(config('relay.private_key_path'));
    }

    public function publicKeyPem(): string
    {
        return File::get(config('relay.public_key_path'));
    }

    public function signedGet(string $url): Response
    {
        return $this->guardedClient($url)
            ->withHeaders($this->signRequest('GET', $url))
            ->get($url);
    }

    public function signedPost(string $url, string $body): Response
    {
        return $this->guardedClient($url)
            ->withHeaders($this->signRequest('POST', $url, $body))
            ->withBody($body, 'application/activity+json')
            ->post($url);
    }

    public function signRequest(string $method, string $url, ?string $body = null): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'];
        $path = $parsed['path'] ?? '/';
        if (isset($parsed['query'])) {
            $path .= '?'.$parsed['query'];
        }

        $date = now()->toRfc7231String();

        $headers = [
            '(request-target)' => strtolower($method).' '.$path,
            'host' => $host,
            'date' => $date,
        ];

        $httpHeaders = [
            'Host' => $host,
            'Date' => $date,
            'Accept' => 'application/activity+json',
            'User-Agent' => app('user_agent'),
        ];

        if (! is_null($body)) {
            $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));
            $headers['digest'] = $digest;
            $httpHeaders['Digest'] = $digest;
            $httpHeaders['Content-Type'] = 'application/activity+json';
        }

        $signingString = collect($headers)
            ->map(fn ($value, $name) => "{$name}: {$value}")
            ->implode("\n");

        openssl_sign($signingString, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256);

        $httpHeaders['Signature'] = sprintf(
            'keyId="%s",algorithm="rsa-sha256",headers="%s",signature="%s"',
            $this->keyId(),
            implode(' ', array_keys($headers)),
            base64_encode($signature),
        );

        return $httpHeaders;
    }

    public function verify(Request $request): bool
    {
        $header = $request->header('Signature');
        if (! $header) {
            return false;
        }

        $params = $this->parseSignatureHeader($header);
        if (! isset($params['keyId'], $params['headers'], $params['signature'])) {
            return false;
        }

        $publicKeyPem = $this->fetchPublicKey($params['keyId']);
        if (! $publicKeyPem) {
            return false;
        }

        $body = $request->getContent();
        if ($request->header('Digest')) {
            $expected = 'SHA-256='.base64_encode(hash('sha256', $body, true));
            if (! hash_equals($expected, $request->header('Digest'))) {
                return false;
            }
        }

        $lines = [];
        foreach (explode(' ', $params['headers']) as $name) {
            if ($name === '(request-target)') {
                $lines[] = '(request-target): '.strtolower($request->method()).' '.$request->getRequestUri();

                continue;
            }
            $lines[] = $name.': '.$request->header($name);
        }

        $signature = base64_decode($params['signature']);

        return openssl_verify(implode("\n", $lines), $signature, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1;
    }

    protected function fetchPublicKey(string $keyId): ?string
    {
        $cacheKey = "relay:key:{$keyId}";
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $actorUri = strtok($keyId, '#');
        $response = $this->signedGet($actorUri);
        $actor = $response->json();
        $pem = $actor['publicKey']['publicKeyPem'] ?? null;

        if ($pem) {
            Cache::put($cacheKey, $pem, now()->addHours(6));
        }

        return $pem;
    }

    protected function parseSignatureHeader(string $header): array
    {
        $params = [];
        preg_match_all('/(\w+)="([^"]*)"/', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $params[$match[1]] = $match[2];
        }

        return $params;
    }

    protected function guardedClient(string $url): PendingRequest
    {
        return GuardedHttp::for($url)->timeout(15)->connectTimeout(10);
    }
}
