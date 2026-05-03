<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_type', 'description', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public static function record(string $eventType, string $description, ?int $userId = null): void
    {
        static::query()->create([
            'event_type' => $eventType,
            'description' => $description,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }
}
