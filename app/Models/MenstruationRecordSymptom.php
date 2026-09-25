<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenstruationRecordSymptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'menstruation_record_id',
        'symptom_id',
    ];

    public function menstruationRecord()
    {
        return $this->belongsTo(MenstruationRecord::class, 'menstruation_record_id');
    }

    public function symptom()
    {
        return $this->belongsTo(Symptom::class, 'symptom_id');
    }
}
