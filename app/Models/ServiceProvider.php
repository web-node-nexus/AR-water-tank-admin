<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ServiceProvider extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'user_id', 'zone_id', 'name', 'phone', 'email', 'password', 'photo',
        'service_area', 'availability_status', 'rating_avg',
        'total_jobs', 'total_earnings', 'is_active',
        'last_login_at', 'fcm_token',
    ];

    protected $hidden = ['fcm_token', 'password'];

    protected function casts(): array
    {
        return [
            'rating_avg' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'provider_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'provider_id');
    }

    public function jobPhotos()
    {
        return $this->hasMany(JobPhoto::class, 'provider_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'provider_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'provider_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'provider_id');
    }

    public function devices()
    {
        return $this->hasMany(ProviderDevice::class, 'provider_id');
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class, 'provider_id');
    }
}
