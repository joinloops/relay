<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use App\Models\RelayAnnounce;
use App\Services\DomainPolicy;

class LandingController extends Controller
{
    public function __invoke(DomainPolicy $policy)
    {
        return view('landing', [
            'subscriberCount' => Instance::accepted()->count(),
            'announces24h' => RelayAnnounce::where('created_at', '>=', now()->subDay())->count(),
            'restrict' => $policy->restrictMode(),
        ]);
    }
}
