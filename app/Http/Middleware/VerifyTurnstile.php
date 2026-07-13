<?php

namespace App\Http\Middleware;

use App\Support\Turnstile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Turnstile::configured()) {
            return $next($request);
        }

        $token = $request->input('cf-turnstile-response') ?? $request->input('turnstile_token');

        if (! Turnstile::verify($token, $request->ip())) {
            return response()->json([
                'message' => 'Bot verification failed. Please try again.',
                'errors' => ['turnstile' => ['Verification failed.']],
            ], 422);
        }

        return $next($request);
    }
}
