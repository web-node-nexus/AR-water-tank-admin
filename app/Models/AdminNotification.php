<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'title', 'body', 'target_type', 'target_provider_ids',
        'sent_by', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'target_provider_ids' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
