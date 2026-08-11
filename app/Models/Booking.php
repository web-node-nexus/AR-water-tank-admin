<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'booking_number', 'customer_id', 'service_id', 'pricing_slab_id',
        'provider_id', 'zone_id', 'created_by', 'customer_name', 'customer_phone',
        'customer_address', 'latitude', 'longitude', 'pincode', 'tank_type', 'tank_size', 'special_notes',
        'scheduled_date', 'scheduled_time', 'status', 'amount', 'payment_status',
        'assigned_at', 'started_at', 'completed_at', 'cancelled_at',
        'cancellation_reason', 'rejection_reason',
        'provider_accepted_at', 'provider_rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'status' => BookingStatus::class,
            'amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'assigned_at' => 'datetime',
            'provider_accepted_at' => 'datetime',
            'provider_rejected_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function generateBookingNumber(): string
    {
        return 'AR'.date('Ymd').strtoupper(substr(uniqid(), -5));
    }

    public function mapsUrl(): string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='
                .$this->latitude.','.$this->longitude;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='
            .urlencode((string) $this->customer_address);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function pricingSlab(): BelongsTo
    {
        return $this->belongsTo(PricingSlab::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobPhoto::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
