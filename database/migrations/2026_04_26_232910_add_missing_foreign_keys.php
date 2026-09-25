<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up orphaned data before adding foreign keys (best effort —
        // on sqlite these tables can hold dangling FK definitions to already
        // dropped legacy tables, which makes even empty DELETEs fail).
        $cleanups = [
            ['cycles', 'user_id', 'users'],
            ['forum_likes', 'post_id', 'forum_posts'],
            ['pregnancies', 'user_id', 'users'],
            ['bhw_monthly_reports', 'bhw_id', 'users'],
        ];
        foreach ($cleanups as [$table, $column, $parent]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasTable($parent)) {
                continue;
            }
            try {
                DB::statement("DELETE FROM {$table} WHERE {$column} IS NOT NULL AND {$column} NOT IN (SELECT id FROM {$parent})");
            } catch (\Throwable $e) {
            }
        }

        $keys = [
            ['cycles', 'user_id', 'users', 'cascade'],
            ['forum_likes', 'post_id', 'forum_posts', 'cascade'],
            ['pregnancies', 'user_id', 'users', 'cascade'],
            ['bhw_monthly_reports', 'bhw_id', 'users', 'cascade'],
        ];
        foreach ($keys as [$table, $column, $parent, $delete]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasTable($parent)) {
                continue;
            }
            try {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $parent, $delete) {
                    $tableBlueprint->foreign($column)->references('id')->on($parent)->onDelete($delete);
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
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
        });

        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->dropForeign(['bhw_id']);
        });
    }
};
