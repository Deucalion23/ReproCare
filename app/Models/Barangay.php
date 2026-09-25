<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = [
        'name',
        'rhu_assignment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRhu($query, string $rhu)
    {
        return $query->where('rhu_assignment', $rhu);
    }

    /** Canonical City Health Office catchments (86 barangays, San Carlos City). */
    public const RHU_CATCHMENTS = [
        'RHU 1' => ['Bonifacio St', 'Burgos St', 'Cacaritan', 'Calomboyan', 'Capataan', 'Lucban St', 'Mamarlao', 'Naguilayan', 'Pagal', 'Palaming', 'Pangalangan', 'Pangpang', 'Quintong', 'Roxas Blvd', 'San Pedro St', 'Tandoc'],
        'RHU 2' => ['Abanon', 'Agdao', 'Anando', 'Bacnar', 'Bolosan', 'Caingal', 'Calobaoan', 'Caoayan Kiling', 'Guelew', 'Libas', 'Malacanang', 'Payar', 'Polo', 'Supo', 'Tebag'],
        'RHU 3' => ['Antipangol', 'Aponit', 'Balaya', 'Baldog', 'Balococ', 'Bani', 'Bocboc', 'Bogaoan', 'Buenglat', 'Gamata', 'Isla', 'Mabalabalino', 'Maliwara', 'Mestizo Norte', 'Pangoloan', 'Salinap', 'San Juan', 'Talang', 'Tamayo', 'Tayambani'],
        'RHU 4' => ['Ano', 'Balite Sur', 'Bega', 'Cobol', 'Coliling', 'Ilang', 'Lilimasan', 'Longos', 'Magtaking', 'Paitan', 'Palospos', 'Payapa', 'Sapinit', 'Tarec', 'Tarectec', 'Turac'],
        'RHU 5' => ['Balayong', 'Bolingit', 'Bugallon St', 'Cruz', 'Doyong', 'Inerangan', 'M. Soriano St', 'Mabini St', 'Manzon', 'Matagdem', 'Nelintap', 'Padilla St', 'Palaris St', 'Parayao', 'Perez Blvd', 'PNR Site', 'Quezon Blvd', 'Rizal Ave', 'Tandang Sora'],
    ];

    public const RHU1_CATCHMENT = self::RHU_CATCHMENTS['RHU 1'];

    /**
     * DB-driven catchment names for an RHU assignment.
     * Falls back to the canonical constant when the table is empty
     * (e.g. fresh checkout before seeding) so forms never break.
     */
    public static function catchmentNames(string $rhu = 'RHU 1'): array
    {
        try {
            $names = static::active()->forRhu($rhu)->orderBy('name')->pluck('name')->all();
            if (!empty($names)) {
                return $names;
            }
        } catch (\Throwable $e) {
        }

        return self::RHU_CATCHMENTS[$rhu] ?? [];
    }

    /** All active San Carlos City barangays, ordered by RHU then name. */
    public static function allNames(): array
    {
        try {
            $names = static::active()->orderBy('rhu_assignment')->orderBy('name')->pluck('name')->all();
            if (!empty($names)) return $names;
        } catch (\Throwable $e) {
        }

        return array_merge(...array_values(self::RHU_CATCHMENTS));
    }
}
