<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ChildRecord Model
 *
 * STORAGE-ONLY MODEL: This model is used for backend data storage only.
 * It has no dedicated controller, views, or routes for UI management.
 * Child records are managed through the Pregnancy and related models.
 */
class ChildRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'mother_id',
        'pregnancy_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'birth_weight',
        'birth_length',
        'apgar_score',
        'delivery_type',
        'complications',
        'purok_id',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'birth_weight' => 'decimal:2',
        'birth_length' => 'decimal:2',
    ];

    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id');
    }

    public function pregnancy()
    {
        return $this->belongsTo(Pregnancy::class, 'pregnancy_id');
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class);
    }

    public function targetClient()
    {
        return $this->hasOne(ChildCareTargetClient::class, 'child_id');
    }

    public function checkups()
    {
        return $this->hasMany(ChildCheckup::class, 'child_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ', ' . $this->first_name);
    }
}
