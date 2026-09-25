<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purok extends Model
{
    protected $fillable = [
        'name',
        'barangay',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Resolve a free-text Sitio / Street / Purok entry to a purok id,
     * creating the registry row on demand so typed locations stay linkable
     * for GIS, assignments, and barangay derivation.
     */
    public static function resolveIdFromText(?string $name, ?string $barangay): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $barangay = trim((string) $barangay);

        return static::firstOrCreate(
            ['name' => $name, 'barangay' => $barangay !== '' ? $barangay : 'Unspecified'],
        )->id;
    }
}
