<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new specific foreign key columns (only missing ones)
        Schema::table('notifications', function (Blueprint $table) {
            foreach (['woman_id', 'midwife_id', 'bhw_id'] as $column) {
                if (!Schema::hasColumn('notifications', $column)) {
                    $table->unsignedBigInteger($column)->nullable();
                }
            }
        });

        // Add foreign key constraints + indexes (best effort)
        foreach ([
            ['woman_id', 'women'],
            ['midwife_id', 'midwives'],
            ['bhw_id', 'bhws'],
        ] as [$column, $on]) {
            if (!Schema::hasColumn('notifications', $column) || !Schema::hasTable($on)) {
                continue;
            }
            try {
                Schema::table('notifications', function (Blueprint $table) use ($column, $on) {
                    $table->foreign($column)->references('id')->on($on)->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('notifications', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Migrate data from polymorphic columns to specific columns (portable —
        // original MySQL "UPDATE .. alias SET alias.col" does not parse elsewhere).
        $moves = [
            ['woman_id', ['App\\Models\\Woman', 'App\\Models\\Patient']],
            ['midwife_id', ['App\\Models\\Midwife']],
            ['bhw_id', ['App\\Models\\Bhw']],
        ];
        foreach ($moves as [$target, $types]) {
            if (!Schema::hasColumn('notifications', $target) || !Schema::hasColumn('notifications', 'user_id') || !Schema::hasColumn('notifications', 'user_type')) {
                continue;
            }
            try {
                DB::table('notifications')->whereIn('user_type', $types)->update([$target => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
        }

        // Drop polymorphic columns
        $drop = [];
        foreach (['user_id', 'user_type'] as $column) {
            if (Schema::hasColumn('notifications', $column)) {
                $drop[] = $column;
            }
        }
        if (!empty($drop)) {
            try {
                Schema::table('notifications', function (Blueprint $table) use ($drop) {
                    $table->dropColumn($drop);
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
        Schema::table('notifications', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Migrate data back
        DB::statement("UPDATE notifications SET user_id = woman_id, user_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE notifications SET user_id = midwife_id, user_type = 'App\\\\Models\\\\Midwife' WHERE midwife_id IS NOT NULL");
        DB::statement("UPDATE notifications SET user_id = bhw_id, user_type = 'App\\\\Models\\\\Bhw' WHERE bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });
    }
};
