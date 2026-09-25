<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Referrals are clinical history — they must soft-delete, never hard-delete,
     * so FHSIS/MNCHN reports and emergency fast-lane audits stay intact.
     */
    public function up(): void
    {
        Schema::table('checkup_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('checkup_referrals', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkup_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('checkup_referrals', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
