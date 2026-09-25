<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 2. Offline sync: idempotency ledger (duplicate replays collapse) ──
        if (!Schema::hasTable('sync_logs')) {
            Schema::create('sync_logs', function (Blueprint $table) {
                $table->id();
                $table->string('sync_uuid', 64)->unique();
                $table->string('endpoint', 255);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('client_timestamp')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });
        }

        // ── 1. Duplicate reviews: RHU merge/link decisions, never silent ─────
        if (!Schema::hasTable('duplicate_reviews')) {
            Schema::create('duplicate_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('walk_in_patient_id')->nullable()->constrained('walk_in_patients')->cascadeOnDelete();
                $table->foreignId('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('matched_walk_in_patient_id')->nullable()->constrained('walk_in_patients')->nullOnDelete();
                $table->string('match_type', 50)->default('name_birthdate');
                $table->unsignedTinyInteger('score')->default(0);
                $table->string('status', 20)->default('pending');
                $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reviewer_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        // ── 4. Pregnancy archival lock: delivered records become read-only ────
        if (!Schema::hasColumn('pregnancies', 'is_locked')) {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('postpartum_transitioned_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_reviews');
        Schema::dropIfExists('sync_logs');

        if (Schema::hasColumn('pregnancies', 'is_locked')) {
            Schema::table('pregnancies', fn (Blueprint $t) => $t->dropColumn('is_locked'));
        }
    }
};
