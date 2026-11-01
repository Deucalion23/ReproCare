<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
     * The station this build serves. Accounts carrying no RHU assignment fall
     * back to it so a picker can never drift into another RHU's barangays.
     */
    public const DEPLOYMENT_RHU = 'RHU 1';

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

    /**
     * Catchment a staff account chooses from: the RHU its own account is
     * assigned to, otherwise the deployment's station.
     */
    private static function staffRhu(): string
    {
        $value = strtoupper(trim((string) (auth()->user()?->rhu_assignment ?? '')));
        $roman = ['RHU I' => 'RHU 1', 'RHU II' => 'RHU 2', 'RHU III' => 'RHU 3', 'RHU IV' => 'RHU 4', 'RHU V' => 'RHU 5'];
        $value = $roman[$value] ?? $value;

        return isset(self::RHU_CATCHMENTS[$value]) ? $value : self::DEPLOYMENT_RHU;
    }

    /**
     * Dropdown rows for the staff pickers. `$keep` pins the name an existing
     * record or an active filter already carries — even when it sits outside
     * this catchment — so editing a legacy record never silently rewrites its
     * barangay to whichever option happens to sort first.
     *
     * @return Collection<int, static>
     */
    public static function catchmentOptions(array|string|null $keep = null): Collection
    {
        $rows = static::active()->forRhu(static::staffRhu())->orderBy('name')->get(['id', 'name']);
        $names = $rows->pluck('name')->all();

        foreach ((array) $keep as $name) {
            $name = trim((string) $name);
            if ($name !== '' && !in_array($name, $names, true)) {
                $rows->push(new static(['name' => $name]));
                $names[] = $name;
            }
        }

        return $rows->sortBy(fn ($row) => mb_strtolower($row->name))->values();
    }

    /** The same catchment as plain names, for the filters that render strings. */
    public static function catchmentOptionNames(array|string|null $keep = null): array
    {
        return static::catchmentOptions($keep)->pluck('name')->all();
    }

    /** All active San Carlos City barangays, alphabetical (A–Z). */
    public static function allNames(): array
    {
        try {
            $names = static::active()->orderBy('name')->pluck('name')->all();
            if (!empty($names)) return $names;
        } catch (\Throwable $e) {
        }

        $merged = array_merge(...array_values(self::RHU_CATCHMENTS));
        sort($merged, SORT_STRING | SORT_FLAG_CASE);

        return $merged;
    }

    /**
     * The complete 86-barangay San Carlos City list, independent of how many
     * rows the current deployment has seeded (RHU 1-only installs included).
     */
    public static function cityWideNames(): array
    {
        $names = array_merge(...array_values(self::RHU_CATCHMENTS));
        foreach (static::allNames() as $name) {
            if (!in_array($name, $names, true)) {
                $names[] = $name;
            }
        }
        $names = array_values(array_unique($names));
        sort($names, SORT_STRING | SORT_FLAG_CASE);

        return $names;
    }

    /** Which RHU catchment a barangay name belongs to, if mapped. */
    public static function rhuFor(?string $name): ?string
    {
        if (!$name) {
            return null;
        }
        try {
            $assigned = static::where('name', $name)->value('rhu_assignment');
            if ($assigned) {
                return $assigned;
            }
        } catch (\Throwable $e) {
        }

        foreach (self::RHU_CATCHMENTS as $rhu => $barangays) {
            if (in_array($name, $barangays, true)) {
                return $rhu;
            }
        }

        return null;
    }
}
