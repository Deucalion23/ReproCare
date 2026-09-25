<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->enum('workflow_status', ['draft', 'submitted_to_bhw_president', 'bhw_president_review', 'bhw_president_approved', 'bhw_president_rejected', 'completed'])->default('draft')->after('notes');
            $table->timestamp('submitted_to_bhw_president_at')->nullable()->after('workflow_status');
            $table->timestamp('bhw_president_reviewed_at')->nullable()->after('submitted_to_bhw_president_at');
            $table->text('workflow_notes')->nullable()->after('bhw_president_reviewed_at');
            $table->text('bhw_president_notes')->nullable()->after('workflow_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn([
                'workflow_status',
                'submitted_to_bhw_president_at',
                'bhw_president_reviewed_at',
                'workflow_notes',
                'bhw_president_notes'
            ]);
        });
    }
};
