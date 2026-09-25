<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize menstruation_dailies (already has woman_id, just remove old columns)
        // Skipping for now due to constraint issues - will handle separately
        // if (Schema::hasColumn('menstruation_dailies', 'patient_id')) {
        //     DB::statement('ALTER TABLE menstruation_dailies DROP COLUMN patient_id');
        // }
        // if (Schema::hasColumn('menstruation_dailies', 'patient_type')) {
        //     DB::statement('ALTER TABLE menstruation_dailies DROP COLUMN patient_type');
        // }

        // Cycles already normalized - skip

        // Normalize fertility_logs (already has woman_id, just remove old columns)
        // Skipping due to constraint issues
        // if (Schema::hasColumn('fertility_logs', 'patient_id')) {
        //     DB::statement('ALTER TABLE fertility_logs DROP COLUMN patient_id');
        // }
        // if (Schema::hasColumn('fertility_logs', 'patient_type')) {
        //     DB::statement('ALTER TABLE fertility_logs DROP COLUMN patient_type');
        // }

        // Normalize forum_likes (remove old polymorphic columns, keep explicit FKs)
        // Skipping due to constraint issues
        // if (Schema::hasColumn('forum_likes', 'user_id')) {
        //     DB::statement('ALTER TABLE forum_likes DROP COLUMN user_id');
        // }
        // if (Schema::hasColumn('forum_likes', 'user_type')) {
        //     DB::statement('ALTER TABLE forum_likes DROP COLUMN user_type');
        // }

        // Normalize health_records_archived (remove old columns, keep explicit FKs)
        // Skipping due to constraint issues
        // if (Schema::hasColumn('health_records_archived', 'user_id')) {
        //     DB::statement('ALTER TABLE health_records_archived DROP COLUMN user_id');
        // }
        // if (Schema::hasColumn('health_records_archived', 'patient_id')) {
        //     DB::statement('ALTER TABLE health_records_archived DROP COLUMN patient_id');
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse menstruation_dailies
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('woman_id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Reverse cycles
        Schema::table('cycles', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('woman_id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });
        DB::statement("UPDATE cycles SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn('woman_id');
        });

        // Reverse fertility_logs
        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('woman_id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });
        DB::statement("UPDATE fertility_logs SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropColumn('woman_id');
        });

        // Reverse forum_likes
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('bhw_id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Reverse health_records_archived
        Schema::table('health_records_archived', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('patient_id')->nullable()->after('user_id');
        });
    }
};
