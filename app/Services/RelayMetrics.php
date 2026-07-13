<?php

namespace App\Services;

use App\Models\Instance;
use App\Models\RelayAnnounce;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RelayMetrics
{
    protected const TTL_HOURS = 26;

    public function recordDelivery(bool $ok): void
    {
        $key = $this->bucketKey($ok ? 'delivered' : 'failed', now());
        Cache::add($key, 0, now()->addHours(self::TTL_HOURS));
        Cache::increment($key);
    }

    public function overview(): array
    {
        return [
            'counts' => $this->stateCounts(),
            'throughput' => $this->announceThroughput(),
            'delivery' => $this->deliverySeries(),
        ];
    }

    protected function stateCounts(): array
    {
        $rows = Instance::selectRaw('state, count(*) as total')->groupBy('state')->pluck('total', 'state');

        return [
            'total' => (int) $rows->sum(),
            'accepted' => (int) ($rows['accepted'] ?? 0),
            'pending' => (int) ($rows['pending'] ?? 0),
            'dead' => (int) ($rows['dead'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
        ];
    }

    protected function announceThroughput(): array
    {
        $since = now()->subHours(24)->startOfHour();

        $rows = RelayAnnounce::where('created_at', '>=', $since)
            ->get(['created_at'])
            ->groupBy(fn ($a) => $a->created_at->format('YmdH'))
            ->map->count();

        return $this->fillHourly($since, fn (Carbon $h) => (int) ($rows[$h->format('YmdH')] ?? 0));
    }

    protected function deliverySeries(): array
    {
        $since = now()->subHours(24)->startOfHour();

        $series = $this->fillHourly($since, function (Carbon $h) {
            return [
                'ok' => (int) Cache::get($this->bucketKey('delivered', $h), 0),
                'fail' => (int) Cache::get($this->bucketKey('failed', $h), 0),
            ];
        });

        $ok = array_sum(array_column(array_column($series, 'value'), 'ok'));
        $fail = array_sum(array_column(array_column($series, 'value'), 'fail'));
        $total = $ok + $fail;

        return [
            'series' => $series,
            'success_rate' => $total > 0 ? round($ok / $total * 100, 1) : null,
            'delivered' => $ok,
            'failed' => $fail,
        ];
    }

    protected function fillHourly(Carbon $since, callable $value): array
    {
        $out = [];
        for ($h = $since->copy(); $h <= now(); $h->addHour()) {
            $out[] = ['hour' => $h->format('H:00'), 'ts' => $h->toIso8601String(), 'value' => $value($h)];
        }

        return $out;
    }

    protected function bucketKey(string $type, Carbon $hour): string
    {
        return "relay:metrics:{$type}:".$hour->format('YmdH');
    }
}
