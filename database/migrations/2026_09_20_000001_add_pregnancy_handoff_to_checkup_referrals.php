<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pregnancy-report handoff: link a referral to the reported pregnancy
     * plus the exact health records the BHW attached, and record when the
     * midwife accepted it. Accepted referrals keep their history instead of
     * being deleted, so both sides can audit the handoff.
     */
    public function up(): void
    {
        Schema::table('checkup_referrals', function (Blueprint $table) {
            $table->foreignId('pregnancy_id')->nullable()->after('walk_in_patient_id')
                ->constrained('pregnancies')->nullOnDelete();
            $table->json('health_record_ids')->nullable()->after('pregnancy_id');
            $table->timestamp('accepted_at')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('checkup_referrals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pregnancy_id');
            $table->dropColumn(['health_record_ids', 'accepted_at']);
        });
    }
};
