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
        // Convert menstruation_records from user_id to patient_id
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
        });

        // Migrate data from user_id_old to patient_id
        DB::statement('UPDATE menstruation_records SET patient_id = user_id_old');

        // Drop the old column
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn('user_id_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->renameColumn('patient_id', 'patient_id_old');
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        DB::statement('UPDATE menstruation_records SET user_id = patient_id_old');

        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn('patient_id_old');
        });
    }
};
