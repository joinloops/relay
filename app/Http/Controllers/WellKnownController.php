<?php

namespace App\Http\Controllers;

use App\Services\RelayActor;
use Illuminate\Http\Request;

class WellKnownController extends Controller
{
    public function webfinger(Request $request)
    {
        $resource = $request->query('resource');
        $expected = 'acct:'.RelayActor::username().'@'.RelayActor::domain();

        if ($resource !== $expected) {
            abort(404);
        }

        return response()->json([
            'subject' => $expected,
            'links' => [[
                'rel' => 'self',
                'type' => 'application/activity+json',
                'href' => RelayActor::id(),
            ]],
        ])->header('Content-Type', 'application/jrd+json');
    }

    public function nodeinfoIndex()
    {
        return response()->json([
            'links' => [[
                'rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
                'href' => 'https://'.RelayActor::domain().'/nodeinfo/2.0',
            ]],
        ]);
    }

    public function nodeinfo()
    {
        return response()->json([
            'version' => '2.0',
            'software' => ['name' => 'loops-relay', 'version' => '1.0.0'],
            'protocols' => ['activitypub'],
            'services' => ['inbound' => [], 'outbound' => []],
            'openRegistrations' => false,
            'usage' => ['users' => ['total' => 1]],
            'metadata' => (object) ['software' => ['repo' => 'https://github.com/joinLoops/relay']],
        ]);
    }
}
