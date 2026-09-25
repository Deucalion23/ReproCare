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
        Schema::table('messages', function (Blueprint $table) {
            // Add new specific foreign key columns for sender
            $table->unsignedBigInteger('sender_woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('sender_midwife_id')->nullable()->after('sender_woman_id');
            $table->unsignedBigInteger('sender_bhw_id')->nullable()->after('sender_midwife_id');

            // Add new specific foreign key columns for receiver
            $table->unsignedBigInteger('receiver_woman_id')->nullable()->after('sender_bhw_id');
            $table->unsignedBigInteger('receiver_midwife_id')->nullable()->after('receiver_woman_id');
            $table->unsignedBigInteger('receiver_bhw_id')->nullable()->after('receiver_midwife_id');

            // Add foreign key constraints
            $table->foreign('sender_woman_id')->references('id')->on('women')->onDelete('cascade');
            $table->foreign('sender_midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            $table->foreign('sender_bhw_id')->references('id')->on('bhws')->onDelete('cascade');
            $table->foreign('receiver_woman_id')->references('id')->on('women')->onDelete('cascade');
            $table->foreign('receiver_midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            $table->foreign('receiver_bhw_id')->references('id')->on('bhws')->onDelete('cascade');

            // Add indexes
            $table->index('sender_woman_id');
            $table->index('sender_midwife_id');
            $table->index('sender_bhw_id');
            $table->index('receiver_woman_id');
            $table->index('receiver_midwife_id');
            $table->index('receiver_bhw_id');
        });

        // Migrate sender data
        DB::statement("
            UPDATE messages m
            SET m.sender_woman_id = m.sender_id
            WHERE m.sender_type = 'App\\\\Models\\\\Woman' OR m.sender_type = 'App\\\\Models\\\\Patient'
        ");

        DB::statement("
            UPDATE messages m
            SET m.sender_midwife_id = m.sender_id
            WHERE m.sender_type = 'App\\\\Models\\\\Midwife'
        ");

        DB::statement("
            UPDATE messages m
            SET m.sender_bhw_id = m.sender_id
            WHERE m.sender_type = 'App\\\\Models\\\\Bhw'
        ");

        // Migrate receiver data
        DB::statement("
            UPDATE messages m
            SET m.receiver_woman_id = m.receiver_id
            WHERE m.receiver_type = 'App\\\\Models\\\\Woman' OR m.receiver_type = 'App\\\\Models\\\\Patient'
        ");

        DB::statement("
            UPDATE messages m
            SET m.receiver_midwife_id = m.receiver_id
            WHERE m.receiver_type = 'App\\\\Models\\\\Midwife'
        ");

        DB::statement("
            UPDATE messages m
            SET m.receiver_bhw_id = m.receiver_id
            WHERE m.receiver_type = 'App\\\\Models\\\\Bhw'
        ");

        // Drop polymorphic columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['sender_id', 'sender_type', 'receiver_id', 'receiver_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('sender_id')->nullable()->after('id');
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_bhw_id');
            $table->string('receiver_type')->nullable()->after('receiver_id');
        });

        // Migrate sender data back
        DB::statement("UPDATE messages SET sender_id = sender_woman_id, sender_type = 'App\\\\Models\\\\Woman' WHERE sender_woman_id IS NOT NULL");
        DB::statement("UPDATE messages SET sender_id = sender_midwife_id, sender_type = 'App\\\\Models\\\\Midwife' WHERE sender_midwife_id IS NOT NULL");
        DB::statement("UPDATE messages SET sender_id = sender_bhw_id, sender_type = 'App\\\\Models\\\\Bhw' WHERE sender_bhw_id IS NOT NULL");

        // Migrate receiver data back
        DB::statement("UPDATE messages SET receiver_id = receiver_woman_id, receiver_type = 'App\\\\Models\\\\Woman' WHERE receiver_woman_id IS NOT NULL");
        DB::statement("UPDATE messages SET receiver_id = receiver_midwife_id, receiver_type = 'App\\\\Models\\\\Midwife' WHERE receiver_midwife_id IS NOT NULL");
        DB::statement("UPDATE messages SET receiver_id = receiver_bhw_id, receiver_type = 'App\\\\Models\\\\Bhw' WHERE receiver_bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_woman_id']);
            $table->dropForeign(['sender_midwife_id']);
            $table->dropForeign(['sender_bhw_id']);
            $table->dropForeign(['receiver_woman_id']);
            $table->dropForeign(['receiver_midwife_id']);
            $table->dropForeign(['receiver_bhw_id']);
            $table->dropColumn(['sender_woman_id', 'sender_midwife_id', 'sender_bhw_id', 'receiver_woman_id', 'receiver_midwife_id', 'receiver_bhw_id']);
        });
    }
};
