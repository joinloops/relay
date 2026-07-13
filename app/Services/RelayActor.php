<?php

namespace App\Services;

class RelayActor
{
    public static function domain(): string
    {
        return config('relay.domain');
    }

    public static function id(): string
    {
        return 'https://'.self::domain().'/actor';
    }

    public static function username(): string
    {
        return config('relay.username');
    }

    public static function object(): array
    {
        $id = self::id();

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $id,
            'type' => 'Application',
            'preferredUsername' => self::username(),
            'name' => config('relay.name'),
            'summary' => app('about'),
            'inbox' => $id.'/inbox',
            'outbox' => $id.'/outbox',
            'followers' => $id.'/followers',
            'following' => $id.'/following',
            'url' => $id,
            'endpoints' => ['sharedInbox' => 'https://'.self::domain().'/inbox'],
            'publicKey' => [
                'id' => $id.'#main-key',
                'owner' => $id,
                'publicKeyPem' => app(Signature::class)->publicKeyPem(),
            ],
        ];
    }

    public static function announce(string $objectUri, string $uuid): array
    {
        $id = self::id();

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $id.'/activities/'.$uuid,
            'type' => 'Announce',
            'actor' => $id,
            'published' => now()->toIso8601ZuluString(),
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'cc' => [$id.'/followers'],
            'object' => $objectUri,
        ];
    }

    public static function accept(array $follow, string $uuid): array
    {
        return self::wrap('Accept', $follow, $uuid);
    }

    public static function reject(array $follow, string $uuid): array
    {
        return self::wrap('Reject', $follow, $uuid);
    }

    protected static function wrap(string $type, array $follow, string $uuid): array
    {
        $id = self::id();

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $id.'/activities/'.$uuid,
            'type' => $type,
            'actor' => $id,
            'object' => $follow,
        ];
    }
}
