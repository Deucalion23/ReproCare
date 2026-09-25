<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'requested_by_id',
        'approved_by_id',
        'supply_category',
        'supply_name',
        'quantity_requested',
        'unit',
        'urgency',
        'reason',
        'status',
        'cho_notes',
        'expected_delivery_date',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'delivered_at',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
