<?php

use App\Http\Controllers\ActorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\WellKnownController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::get('/actor/activities/{uuid}', [ActorController::class, 'activity']);

Route::get('/actor', [ActorController::class, 'actor']);
Route::post('/actor/inbox', [ActorController::class, 'inbox']);
Route::post('/inbox', [ActorController::class, 'inbox']);
Route::get('/actor/outbox', [ActorController::class, 'outbox']);
Route::get('/actor/followers', [ActorController::class, 'followers']);
Route::get('/actor/following', [ActorController::class, 'following']);

Route::get('/.well-known/webfinger', [WellKnownController::class, 'webfinger']);
Route::get('/.well-known/nodeinfo', [WellKnownController::class, 'nodeinfoIndex']);
Route::get('/nodeinfo/2.0', [WellKnownController::class, 'nodeinfo']);
