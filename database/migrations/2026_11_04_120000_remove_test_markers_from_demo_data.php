<?php

use Database\Seeders\DemoFixtureText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The demo seeders used to stamp "[TEST]" into every note, purpose and title
 * they wrote, name people "Test Elisa" and "Test Partner 31", and serialise
 * family records as "TEST-47". Those strings show up on patient profiles, the
 * women directory, notifications, tasks and the dashboards.
 *
 * Seeders only run against a fresh database, so the rows already sitting on the
 * deployed one are cleaned here. Rewrites are grouped by the distinct stored
 * value, which keeps this to a few statements per column instead of one per row.
 */
return new class extends Migration
{
    /**
     * Free-text columns that can hold a fixture label. Each one is read once,
     * passed through the same helper the seeders use, and written back only
     * where the text actually changes.
     */
    private const TEXT_COLUMNS = [
        ['health_records', 'notes'],
        ['health_records', 'risk_notes'],
        ['pregnancies', 'notes'],
        ['pregnancies', 'risk_notes'],
        ['pregnancies', 'rejection_reason'],
        ['cycles', 'notes'],
        ['checkups', 'purpose'],
        ['checkup_referrals', 'reason'],
        ['postpartum_visits', 'notes'],
        ['child_checkups', 'notes'],
        ['newborns', 'danger_signs'],
        ['notifications', 'title'],
        ['notifications', 'message'],
        ['messages', 'subject'],
        ['messages', 'body'],
        ['tasks', 'description'],
        ['supply_requests', 'reason'],
        ['puroks', 'description'],
    ];

    /** Rough pass over the columns; anything unmatched is left untouched. */
    private const FINDINGS = ['%[TEST]%', '%Fictional%', '%testing%', '%test scenario%', '%test catchment%'];

    public function up(): void
    {
        // These carry an email or a bare location index, so they are rebuilt
        // from the row's own relations before the generic pass runs.
        $this->retagTitles();

        foreach (self::TEXT_COLUMNS as [$table, $column]) {
            $this->cleanColumn($table, $column);
        }

        $this->renameStaffPlaceholders();
        $this->renamePrefixedNames();
        $this->renamePartnerLabels();
        $this->renameFamilySerials();
        $this->renamePurokDescriptions();
    }

    /**
     * down() has nothing to roll back: the marker carried no information the
     * rows can be returned to, and re-adding it would put the labels back on
     * screens people use.
     */
    public function down(): void
    {
        //
    }

    private function cleanColumn(string $table, string $column): void
    {
        if (! $this->hasColumn($table, $column)) {
            return;
        }

        $query = DB::table($table)->distinct()->select($column);
        foreach (self::FINDINGS as $index => $pattern) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $query->{$method}($column, 'like', $pattern);
        }

        foreach ($query->pluck($column) as $value) {
            $clean = $this->cleanText((string) $value);
            if ($clean !== null && $clean !== $value) {
                DB::table($table)->where($column, $value)->update([$column => $clean]);
            }
        }
    }

    /** Shared with the seeders so a fresh database and these rows agree. */
    private function cleanText(string $value): ?string
    {
        $clean = DemoFixtureText::strip($value);

        return trim((string) $clean) === '' ? null : trim((string) $clean);
    }

    /** "Test Midwife" / "Test BHW" were created by the digital-solution seeder. */
    private function renameStaffPlaceholders(): void
    {
        $swaps = [
            ['Test', 'Midwife', 'Marisol', 'Domingo'],
            ['Test', 'BHW', 'Josephine', 'Navarro'],
        ];
        foreach ($swaps as [$first, $last, $newFirst, $newLast]) {
            DB::table('users')->where('first_name', $first)->where('last_name', $last)
                ->update(['first_name' => $newFirst, 'last_name' => $newLast]);
        }
    }

    /** "Test Elisa" -> "Elisa". The demo email keeps its address: the deploy
        script counts it to decide whether the scenario accounts are seeded. */
    private function renamePrefixedNames(): void
    {
        $names = DB::table('users')->where('first_name', 'like', 'Test %')->distinct()->pluck('first_name');
        foreach ($names as $name) {
            DB::table('users')->where('first_name', $name)
                ->update(['first_name' => trim(substr($name, 5))]);
        }
    }

    /** "Test Partner 31" served as both the partner name and the emergency contact. */
    private function renamePartnerLabels(): void
    {
        foreach ([['users', 'partner_name', 'id'], ['emergency_contacts', 'name', 'user_id']] as [$table, $column, $seedColumn]) {
            if (! $this->hasColumn($table, $column)) {
                continue;
            }
            DB::table($table)->where($column, 'like', 'Test Partner %')
                ->select(['id', $seedColumn])->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $column, $seedColumn) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)
                            ->update([$column => DemoFixtureText::partnerName($row->{$seedColumn})]);
                    }
                });
        }
    }

    /**
     * Task, supply and monthly-report titles were keyed on a marker plus an
     * email or a bare location index. Rebuild them from the person or barangay
     * the row already points at.
     */
    private function retagTitles(): void
    {
        DB::table('tasks')->where('title', 'like', '[TEST] Follow-up %')
            ->select(['id', 'assigned_to_id'])->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $staff = DB::table('users')->where('id', $row->assigned_to_id)
                        ->select(['first_name', 'last_name'])->first();
                    DB::table('tasks')->where('id', $row->id)->update([
                        'title' => $staff ? 'Household follow-up for ' . trim($staff->first_name . ' ' . $staff->last_name)
                            : 'Household follow-up visit',
                    ]);
                }
            });

        $specs = [
            ['supply_requests', 'supply_name', 'requested_by_id', '[TEST] Prenatal kits %', 'Prenatal kits for ', 'Prenatal kits'],
            ['bhw_monthly_reports', 'title', 'bhw_id', '[TEST] Monthly report %', 'Monthly FHSIS report for ', 'Monthly FHSIS report'],
        ];
        foreach ($specs as [$table, $column, $whoColumn, $like, $prefix, $fallback]) {
            if (! $this->hasColumn($table, $column)) {
                continue;
            }
            DB::table($table)->where($column, 'like', $like)
                ->select(['id', $whoColumn])->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $column, $whoColumn, $prefix, $fallback) {
                    foreach ($rows as $row) {
                        $user = DB::table('users')->where('id', $row->{$whoColumn})
                            ->select(['assigned_barangay', 'barangay'])->first();
                        $barangay = trim((string) ($user?->assigned_barangay ?: $user?->barangay));
                        DB::table($table)->where('id', $row->id)
                            ->update([$column => $barangay === '' ? $fallback : $prefix . $barangay]);
                    }
                });
        }
    }

    /** A family serial is a record id, not a label: TEST-47 -> MCTC-47. */
    private function renameFamilySerials(): void
    {
        if (! $this->hasColumn('maternal_care_target_clients', 'family_serial_no')) {
            return;
        }
        $serials = DB::table('maternal_care_target_clients')
            ->where('family_serial_no', 'like', 'TEST-%')->distinct()->pluck('family_serial_no');
        foreach ($serials as $serial) {
            DB::table('maternal_care_target_clients')->where('family_serial_no', $serial)
                ->update(['family_serial_no' => 'MCTC-' . substr($serial, 5)]);
        }
    }

    /** Purok descriptions were seeded as catchment labels for the fixtures. */
    private function renamePurokDescriptions(): void
    {
        if (! $this->hasColumn('puroks', 'description')) {
            return;
        }
        DB::table('puroks')
            ->where(function ($query) {
                $query->where('description', 'like', '%test%')->orWhere('description', 'like', '%Test%');
            })
            ->select(['id', 'barangay'])->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $barangay = trim((string) $row->barangay);
                    DB::table('puroks')->where('id', $row->id)->update([
                        'description' => $barangay === ''
                            ? 'Residential area of the barangay'
                            : "Residential area for {$barangay}, San Carlos City",
                    ]);
                }
            });
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
};
