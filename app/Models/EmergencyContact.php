<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'contact_number',
        'address',
        'is_primary',
        'contact_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'contact_order' => 'integer',
    ];

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeOrdered($query)
    {
        return $query->orderBy('contact_order')->orderBy('created_at');
    }

    public function scopePrimary($query)
    {
        return $query->where('contact_order', 1);
    }

    public function scopeSecondary($query)
    {
        return $query->where('contact_order', 2);
    }

    public function scopeTertiary($query)
    {
        return $query->where('contact_order', 3);
    }

    // ─── Relationships ───────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
