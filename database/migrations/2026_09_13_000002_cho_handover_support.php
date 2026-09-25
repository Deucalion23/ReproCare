<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CHO Super Admin handover & succession support:
     * - protects transition audit events from pruning,
     * - records every role transfer without deleting accounts.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'is_protected')) {
                $table->boolean('is_protected')->default(false)->after('description');
            }
        });

        if (!Schema::hasTable('cho_handovers')) {
            Schema::create('cho_handovers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outgoing_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('incoming_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('performed_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('outgoing_name');
                $table->string('incoming_name');
                $table->string('auth_method'); // recovery_key | rhu_cosign
                $table->string('former_signature_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cho_handovers');

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
        });
    }
};
