<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_role',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'is_protected',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['is_protected' => 'boolean'];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject()
    {
        return $this->morphTo('model');
    }

    // Helper method to log an action easily
    public static function log($action, $description, $model = null)
    {
        $user = auth()->user();
        
        self::create([
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'user_name' => $user ? $user->name : 'System/Guest',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log an unalterable system event (excluded from retention pruning).
     * Used for role handovers and other succession events.
     */
    public static function logProtected($action, $description, $model = null)
    {
        $user = auth()->user();

        return self::create([
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'user_name' => $user ? $user->name : 'System/Guest',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'is_protected' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
