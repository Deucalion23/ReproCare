<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff succession support:
     * - ROLE_CHANGE_EVENT audit action,
     * - PWA offline-cache version per user,
     * - midwife catchment barangays,
     * - immutable staff transition ledger.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pwa_cache_version')) {
                $table->unsignedInteger('pwa_cache_version')->default(1)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'catchment_barangays')) {
                $table->json('catchment_barangays')->nullable()->after('assigned_barangay');
            }
        });

        if (!Schema::hasTable('staff_transitions')) {
            Schema::create('staff_transitions', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // midwife_replace | president_replace | bhw_transfer
                $table->foreignId('outgoing_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('incoming_user_ids')->nullable();
                $table->foreignId('performed_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('outgoing_name');
                $table->text('incoming_names')->nullable();
                $table->json('counts')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_transitions');

        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(
                ['pwa_cache_version', 'catchment_barangays'],
                fn ($column) => Schema::hasColumn('users', $column)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
