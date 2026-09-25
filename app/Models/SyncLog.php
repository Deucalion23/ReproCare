<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sync_uuid',
        'endpoint',
        'user_id',
        'client_timestamp',
        'synced_at',
    ];

    protected $casts = [
        'client_timestamp' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Atomically claim a sync uuid. Returns true when this is the first
     * time the uuid is seen (caller should process), false on replay.
     */
    public static function claim(string $uuid, string $endpoint, ?int $userId = null, $clientTimestamp = null): bool
    {
        try {
            self::create([
                'sync_uuid' => $uuid,
                'endpoint' => $endpoint,
                'user_id' => $userId,
                'client_timestamp' => $clientTimestamp,
                'synced_at' => now(),
            ]);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            return false; // unique violation → replay of an accepted entry
        }
    }
}
