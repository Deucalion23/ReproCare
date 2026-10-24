<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'condition_name',
        'diagnosed_on',
        'status',
        'verification_status',
        'notes',
        'recorded_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'diagnosed_on' => 'date',
    ];

    public function woman()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
