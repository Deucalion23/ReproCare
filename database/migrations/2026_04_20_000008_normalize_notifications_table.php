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
        Schema::table('notifications', function (Blueprint $table) {
            // Add new specific foreign key columns
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');

            // Add foreign key constraints
            $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('bhws')->onDelete('cascade');

            // Add indexes
            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        // Migrate data from polymorphic columns to specific columns
        DB::statement("
            UPDATE notifications n
            SET n.woman_id = n.user_id
            WHERE n.user_type = 'App\\\\Models\\\\Woman' OR n.user_type = 'App\\\\Models\\\\Patient'
        ");

        DB::statement("
            UPDATE notifications n
            SET n.midwife_id = n.user_id
            WHERE n.user_type = 'App\\\\Models\\\\Midwife'
        ");

        DB::statement("
            UPDATE notifications n
            SET n.bhw_id = n.user_id
            WHERE n.user_type = 'App\\\\Models\\\\Bhw'
        ");

        // Drop polymorphic columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Migrate data back
        DB::statement("UPDATE notifications SET user_id = woman_id, user_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE notifications SET user_id = midwife_id, user_type = 'App\\\\Models\\\\Midwife' WHERE midwife_id IS NOT NULL");
        DB::statement("UPDATE notifications SET user_id = bhw_id, user_type = 'App\\\\Models\\\\Bhw' WHERE bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });
    }
};
