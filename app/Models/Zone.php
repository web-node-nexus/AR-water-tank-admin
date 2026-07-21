<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = ['code', 'name', 'city', 'pincodes', 'is_active'];

    protected function casts(): array
    {
        return [
            'pincodes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function providers(): HasMany
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
