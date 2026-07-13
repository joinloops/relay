<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'moderated_at' => 'datetime',
        'follow_object' => 'array',
        'last_fetched_at' => 'datetime',
        'can_post' => 'boolean',
    ];

    public function scopePending($query)
    {
        return $query->where('state', 'pending');
    }

    public function scopeState($query, string $state)
    {
        return $query->where('state', $state);
    }

    public function deliveryInbox(): string
    {
        return $this->shared_inbox_uri ?: $this->inbox_uri;
    }

    public function scopeAccepted($query)
    {
        return $query->where('state', 'accepted');
    }
}
