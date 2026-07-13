<?php

namespace App\Services;

use App\Models\DomainAllow;
use App\Models\DomainBlock;
use Illuminate\Support\Facades\Cache;

class DomainPolicy
{
    private const BLOCKS_CACHE_KEY = 'relay:blocks:v2';

    private const ALLOWS_CACHE_KEY = 'relay:allows:v2';

    public function isBlocked(string $domain): bool
    {
        return $this->matches($domain, $this->blocked());
    }

    public function isAllowed(string $domain): bool
    {
        return $this->matches($domain, $this->allowed());
    }

    public function restrictMode(): bool
    {
        return (bool) config('relay.restrict');
    }

    public function restrictPosting(): bool
    {
        return (bool) config('relay.restrict_posting');
    }

    public function decideFollowState(string $domain): string
    {
        if ($this->restrictMode() && ! $this->isAllowed($domain)) {
            return 'pending';
        }

        return 'accepted';
    }

    /**
     * @param  array<int, string>  $list
     */
    protected function matches(string $domain, array $list): bool
    {
        $domain = $this->normalize($domain);

        foreach ($list as $entry) {
            if (
                $domain === $entry ||
                str_ends_with($domain, '.'.$entry)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected function blocked(): array
    {
        return Cache::remember(
            self::BLOCKS_CACHE_KEY,
            now()->addHours(12),
            fn (): array => DomainBlock::query()
                ->pluck('domain')
                ->map(fn (string $domain): string => $this->normalize($domain))
                ->filter()
                ->unique()
                ->values()
                ->toArray()
        );
    }

    /**
     * @return array<int, string>
     */
    protected function allowed(): array
    {
        return Cache::remember(
            self::ALLOWS_CACHE_KEY,
            now()->addHours(12),
            fn (): array => DomainAllow::query()
                ->pluck('domain')
                ->map(fn (string $domain): string => $this->normalize($domain))
                ->filter()
                ->unique()
                ->values()
                ->toArray()
        );
    }

    public function flushCache(): void
    {
        Cache::forget(self::BLOCKS_CACHE_KEY);
        Cache::forget(self::ALLOWS_CACHE_KEY);
        Cache::forget('relay:blocks');
        Cache::forget('relay:allows');
    }

    protected function normalize(string $domain): string
    {
        return strtolower(trim($domain, " \n\r\t\v\0."));
    }
}
