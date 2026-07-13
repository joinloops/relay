<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessInboxActivity;
use App\Models\Instance;
use App\Models\RelayAnnounce;
use App\Services\RelayActor;
use App\Services\Signature;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    public function actor()
    {
        return response()->json(RelayActor::object())
            ->header('Content-Type', 'application/activity+json');
    }

    public function inbox(Request $request, Signature $signature)
    {
        if (! $signature->verify($request)) {
            abort(401, 'Invalid signature');
        }

        $activity = $request->json()->all();

        if (! isset($activity['type'], $activity['actor'])) {
            abort(400);
        }

        ProcessInboxActivity::dispatch($activity);

        return response('', 202);
    }

    public function outbox()
    {
        return response()->json([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => RelayActor::id().'/outbox',
            'type' => 'OrderedCollection',
            'totalItems' => 0,
            'orderedItems' => [],
        ])->header('Content-Type', 'application/activity+json');
    }

    public function followers()
    {
        return $this->collection('followers', Instance::accepted()->count());
    }

    public function following()
    {
        return $this->collection('following', Instance::accepted()->count());
    }

    public function activity(string $uuid, ?RelayAnnounce $model = null)
    {
        $announce = RelayAnnounce::where('uuid', $uuid)->firstOrFail();

        return response()
            ->json(RelayActor::announce($announce->object_uri, $announce->uuid))
            ->header('Content-Type', 'application/activity+json');
    }

    protected function collection(string $name, int $count)
    {
        return response()->json([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => RelayActor::id().'/'.$name,
            'type' => 'OrderedCollection',
            'totalItems' => $count,
        ])->header('Content-Type', 'application/activity+json');
    }
}
