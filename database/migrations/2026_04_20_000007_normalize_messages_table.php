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
        // Add new specific foreign key columns (only missing ones)
        Schema::table('messages', function (Blueprint $table) {
            foreach (['sender_woman_id', 'sender_midwife_id', 'sender_bhw_id', 'receiver_woman_id', 'receiver_midwife_id', 'receiver_bhw_id'] as $column) {
                if (!Schema::hasColumn('messages', $column)) {
                    $table->unsignedBigInteger($column)->nullable();
                }
            }
        });

        // Add foreign key constraints + indexes (best effort)
        foreach ([
            ['sender_woman_id', 'women', 'cascade'],
            ['sender_midwife_id', 'midwives', 'cascade'],
            ['sender_bhw_id', 'bhws', 'cascade'],
            ['receiver_woman_id', 'women', 'cascade'],
            ['receiver_midwife_id', 'midwives', 'cascade'],
            ['receiver_bhw_id', 'bhws', 'cascade'],
        ] as [$column, $on, $delete]) {
            if (!Schema::hasColumn('messages', $column) || !Schema::hasTable($on)) {
                continue;
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column, $on, $delete) {
                    $table->foreign($column)->references('id')->on($on)->onDelete($delete);
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Migrate sender/receiver data via query builder (portable — the
        // original MySQL "UPDATE .. alias SET alias.col" syntax does not
        // parse on pgsql/sqlite).
        $moves = [
            ['sender_woman_id', 'sender_id', 'sender_type', ['App\\Models\\Woman', 'App\\Models\\Patient']],
            ['sender_midwife_id', 'sender_id', 'sender_type', ['App\\Models\\Midwife']],
            ['sender_bhw_id', 'sender_id', 'sender_type', ['App\\Models\\Bhw']],
            ['receiver_woman_id', 'receiver_id', 'receiver_type', ['App\\Models\\Woman', 'App\\Models\\Patient']],
            ['receiver_midwife_id', 'receiver_id', 'receiver_type', ['App\\Models\\Midwife']],
            ['receiver_bhw_id', 'receiver_id', 'receiver_type', ['App\\Models\\Bhw']],
        ];
        foreach ($moves as [$target, $source, $typeColumn, $types]) {
            if (!Schema::hasColumn('messages', $target) || !Schema::hasColumn('messages', $source) || !Schema::hasColumn('messages', $typeColumn)) {
                continue;
            }
            try {
                DB::table('messages')->whereIn($typeColumn, $types)->update([$target => DB::raw($source)]);
            } catch (\Throwable $e) {
            }
        }

        // Drop polymorphic columns
        $drop = [];
        foreach (['sender_id', 'sender_type', 'receiver_id', 'receiver_type'] as $column) {
            if (Schema::hasColumn('messages', $column)) {
                $drop[] = $column;
            }
        }
        if (!empty($drop)) {
            try {
                Schema::table('messages', function (Blueprint $table) use ($drop) {
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
        Schema::table('messages', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('sender_id')->nullable()->after('id');
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_bhw_id');
            $table->string('receiver_type')->nullable()->after('receiver_id');
        });

        // Migrate sender data back
        DB::statement("UPDATE messages SET sender_id = sender_woman_id, sender_type = 'App\\\\Models\\\\Woman' WHERE sender_woman_id IS NOT NULL");
        DB::statement("UPDATE messages SET sender_id = sender_midwife_id, sender_type = 'App\\\\Models\\\\Midwife' WHERE sender_midwife_id IS NOT NULL");
        DB::statement("UPDATE messages SET sender_id = sender_bhw_id, sender_type = 'App\\\\Models\\\\Bhw' WHERE sender_bhw_id IS NOT NULL");

        // Migrate receiver data back
        DB::statement("UPDATE messages SET receiver_id = receiver_woman_id, receiver_type = 'App\\\\Models\\\\Woman' WHERE receiver_woman_id IS NOT NULL");
        DB::statement("UPDATE messages SET receiver_id = receiver_midwife_id, receiver_type = 'App\\\\Models\\\\Midwife' WHERE receiver_midwife_id IS NOT NULL");
        DB::statement("UPDATE messages SET receiver_id = receiver_bhw_id, receiver_type = 'App\\\\Models\\\\Bhw' WHERE receiver_bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_woman_id']);
            $table->dropForeign(['sender_midwife_id']);
            $table->dropForeign(['sender_bhw_id']);
            $table->dropForeign(['receiver_woman_id']);
            $table->dropForeign(['receiver_midwife_id']);
            $table->dropForeign(['receiver_bhw_id']);
            $table->dropColumn(['sender_woman_id', 'sender_midwife_id', 'sender_bhw_id', 'receiver_woman_id', 'receiver_midwife_id', 'receiver_bhw_id']);
        });
    }
};
