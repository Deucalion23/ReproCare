<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\CyclePredictionService;
use Carbon\Carbon;

class Cycle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'period_start_date',
        'period_end_date',
        'cycle_length',
        'notes',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
    ];

    // Relationships
    public function woman()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias for backward compatibility
    public function patient()
    {
        return $this->woman();
    }

    public function user()
    {
        return $this->woman();
    }

    // Accessors
    public function getPeriodLengthAttribute()
    {
        if (!$this->period_end_date) {
            return null;
        }
        return $this->period_start_date->diffInDays($this->period_end_date, false) + 1;
    }

    // Use service for cycle length calculation
    public function calculateCycleLength()
    {
        $service = new CyclePredictionService();
        return $service->calculateCycleLength($this);
    }

    // Boot method to auto-calculate cycle length on save
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cycle) {
            $cycle->cycle_length = $cycle->calculateCycleLength();
        });
        static::saved(function ($cycle) {
            $service = new CyclePredictionService();
            $service->recalculateCycleLengths($cycle->user_id);
            if ($cycle->wasChanged('user_id') && $cycle->getOriginal('user_id')) {
                $service->recalculateCycleLengths($cycle->getOriginal('user_id'));
            }
        });
        static::deleted(fn ($cycle) => (new CyclePredictionService())->recalculateCycleLengths($cycle->user_id));
        static::restored(fn ($cycle) => (new CyclePredictionService())->recalculateCycleLengths($cycle->user_id));
    }

    // Methods for prediction - delegate to service
    public static function predictNextPeriod($userId)
    {
        $service = new CyclePredictionService();
        return $service->predictNextPeriod($userId);
    }

    public static function getAverageCycleLength($userId)
    {
        $service = new CyclePredictionService();
        return $service->getAverageCycleLength($userId);
    }

    public static function getAveragePeriodLength($userId)
    {
        $service = new CyclePredictionService();
        return $service->getAveragePeriodLength($userId);
    }

    // Get calendar data for a month - delegate to service
    public static function getCalendarData($userId, $year, $month)
    {
        $service = new CyclePredictionService();
        return $service->getCalendarData($userId, $year, $month);
    }

    // Additional delegated methods
    public static function predictFuturePeriods($userId, $count = 3)
    {
        $service = new CyclePredictionService();
        return $service->predictFuturePeriods($userId, $count);
    }

    public static function getCycleStatistics($userId)
    {
        $service = new CyclePredictionService();
        return $service->getCycleStatistics($userId);
    }

    public static function getCycleRegularity($userId)
    {
        $service = new CyclePredictionService();
        return $service->getCycleRegularity($userId);
    }

    public static function recalculateCycleLengths($userId)
    {
        $service = new CyclePredictionService();
        return $service->recalculateCycleLengths($userId);
    }
}
