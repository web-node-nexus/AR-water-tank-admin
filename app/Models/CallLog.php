<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'provider_id', 'booking_id', 'customer_phone', 'virtual_number',
        'provider_phone', 'provider_call_id', 'status', 'duration_seconds', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
