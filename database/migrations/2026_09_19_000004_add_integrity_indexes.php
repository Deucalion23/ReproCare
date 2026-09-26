<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        // Deduplicate same-day cycle entries before constraining.
        try {
            $dupes = DB::table('cycles')->select('user_id', 'period_start_date')
                ->groupBy('user_id', 'period_start_date')->havingRaw('COUNT(*) > 1')->get();
            foreach ($dupes as $d) {
                $ids = DB::table('cycles')->where('user_id', $d->user_id)
                    ->where('period_start_date', $d->period_start_date)->orderBy('id')->pluck('id');
                $ids->shift();
                if ($ids->isNotEmpty()) {
                    DB::table('cycles')->whereIn('id', $ids)->delete();
                }
            }
        } catch (\Throwable $e) {
        }
        // NOTE: guards must wrap the Schema::table() call itself — a
        // try/catch *inside* the closure is dead code because Blueprint
        // commands only throw when the call executes.
        try {
            Schema::table('cycles', function (Blueprint $table) {
                $table->unique(['user_id', 'period_start_date'], 'cycles_user_start_unique');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'user_id'], 'forum_likes_post_user_unique');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('sms_logs', function (Blueprint $table) {
                $table->index('phone_number', 'sms_logs_phone_index');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            try {
                $table->dropUnique('cycles_user_start_unique');
            } catch (\Throwable $e) {
            }
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            try {
                $table->dropUnique('forum_likes_post_user_unique');
            } catch (\Throwable $e) {
            }
        });
        Schema::table('sms_logs', function (Blueprint $table) {
            try {
                $table->dropIndex('sms_logs_phone_index');
            } catch (\Throwable $e) {
            }
        });
    }
};
