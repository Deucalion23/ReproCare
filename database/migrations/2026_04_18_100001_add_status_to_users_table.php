<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('role');
            $table->string('rejection_reason')->nullable()->after('status');
            // Health profile columns (separate, not JSON – easier to query)
            $table->string('feeding_method')->nullable()->after('contact_number'); // breastfeed|bottle|hybrid
            $table->string('family_planning_method')->nullable()->after('feeding_method');
            $table->string('vitamins')->nullable()->after('family_planning_method');
            $table->json('medical_history')->nullable()->after('vitamins'); // flexible: allergies, conditions
        });

        // Self-registered patients start as pending; staff (bhw/midwife) remain approved
        // This is handled in AuthController, not migration
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'feeding_method', 'family_planning_method', 'vitamins', 'medical_history']);
        });
    }
};
