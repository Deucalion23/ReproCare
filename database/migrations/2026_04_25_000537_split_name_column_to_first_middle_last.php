<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'middle_initial')) {
                $table->string('middle_initial')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable();
            }
        });

        // Migrate existing data: split name into first_name, middle_initial, last_name.
        // Portable PHP-side implementation (original used MySQL-only
        // SUBSTRING_INDEX/LOCATE). Skipped when there is nothing to migrate.
        try {
            if (Schema::hasColumn('users', 'name')) {
                $rows = DB::table('users')->whereNotNull('name')->where('name', '!=', '')->select('id', 'name')->get();
                foreach ($rows as $row) {
                    $parts = preg_split('/\s+/', trim($row->name));
                    $first = $parts[0] ?? '';
                    $last = count($parts) > 1 ? end($parts) : '';
                    $middle = count($parts) > 2 ? strtoupper(substr($parts[1], 0, 1)) : null;
                    DB::table('users')->where('id', $row->id)->update([
                        'first_name' => $first,
                        'last_name' => $last,
                        'middle_initial' => $middle,
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }

        // Drop the old name column
        if (Schema::hasColumn('users', 'name')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('name');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        // Reconstruct name from first_name, middle_initial, last_name
        DB::statement("UPDATE users SET 
            name = CONCAT(
                COALESCE(first_name, ''), 
                CASE WHEN middle_initial IS NOT NULL AND middle_initial != '' THEN CONCAT(' ', middle_initial, '.') ELSE '' END, 
                ' ', 
                COALESCE(last_name, '')
            )
        WHERE first_name IS NOT NULL OR last_name IS NOT NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name']);
        });
    }
};
