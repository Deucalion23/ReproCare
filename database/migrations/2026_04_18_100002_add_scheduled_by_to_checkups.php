<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            // Who scheduled this checkup (BHW or Midwife user ID)
            $table->unsignedBigInteger('scheduled_by_id')->nullable()->after('midwife_user_id');
            $table->foreign('scheduled_by_id')->references('id')->on('users')->onDelete('set null');
            // Add notes column if missing
            if (!Schema::hasColumn('checkups', 'notes')) {
                $table->text('notes')->nullable()->after('purpose');
            }
            // Add actual_date + cancelled_at
            if (!Schema::hasColumn('checkups', 'actual_date')) {
                $table->date('actual_date')->nullable()->after('scheduled_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by_id']);
            $table->dropColumn('scheduled_by_id');
        });
    }
};
