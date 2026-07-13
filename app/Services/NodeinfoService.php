<?php

namespace App\Services;

use App\Models\Instance;

class NodeInfoService
{
    public function fetchFor(Instance $instance): bool
    {
        $domain = $instance->domain;

        if (app(DomainPolicy::class)->isBlocked($domain)) {
            return false;
        }

        $well = $this->getJson("https://{$domain}/.well-known/nodeinfo");
        if (! $well || empty($well['links'])) {
            return false;
        }

        $href = $this->pickHref($well['links']);
        if (! $href) {
            return false;
        }

        $node = $this->getJson($href);
        if (! $node) {
            return false;
        }

        $instance->software = data_get($node, 'software.name');
        $instance->software_version = data_get($node, 'software.version');
        $instance->user_count = (int) data_get($node, 'usage.users.total', 0);
        $instance->status_count = (int) data_get($node, 'usage.localPosts', 0);

        $description = data_get($node, 'metadata.nodeDescription') ?: data_get($node, 'metadata.description');
        if ($description && ! $instance->description) {
            $instance->description = trim(strip_tags((string) $description));
        }

        $instance->last_fetched_at = now();
        $instance->save();

        return true;
    }

    protected function pickHref(array $links): ?string
    {
        $best = null;
        $bestScore = -1;

        foreach ($links as $link) {
            $rel = $link['rel'] ?? '';
            $href = $link['href'] ?? null;
            if (! $href) {
                continue;
            }

            $score = match (true) {
                str_contains($rel, '2.1') => 21,
                str_contains($rel, '2.0') => 20,
                str_contains($rel, '1.1') => 11,
                str_contains($rel, '1.0') => 10,
                default => 1,
            };

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $href;
            }
        }

        return $best;
    }

    protected function getJson(string $url): ?array
    {
        try {
            $response = GuardedHttp::for($url)
                ->timeout(8)
                ->connectTimeout(5)
                ->acceptJson()
                ->withHeaders(['User-Agent' => app('user_agent')])
                ->get($url);

            return $response->ok() ? $response->json() : null;
        } catch (SsrfException $e) {
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
