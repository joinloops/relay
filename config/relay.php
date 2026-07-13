<?php

return [
    'domain' => env('RELAY_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST)),
    'username' => 'relay',
    'name' => env('RELAY_NAME', 'Loops Relay'),
    'private_key_path' => storage_path('app/relay/private.pem'),
    'public_key_path' => storage_path('app/relay/public.pem'),
    'restrict' => true,
    'restrict_message' => 'This relay is <span class="font-medium text-amber-200">invite-only</span> and meant for Loops servers exclusively.',
    'admin_emails' => env('RELAY_ADMIN_EMAIL'),
    'restrict_posting' => env('RELAY_RESTRICT_POSTING', false),
];
