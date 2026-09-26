<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function to drop foreign key if it exists (portable)
        $dropForeignKeyIfExists = function ($table, $foreignKey) {
            if (DB::getDriverName() !== 'mysql') {
                try {
                    Schema::table($table, function (Blueprint $t) use ($foreignKey) {
                        $t->dropForeign($foreignKey);
                    });
                } catch (\Throwable $e) {
                }
                return;
            }
            $exists = DB::select("SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?", [$table, $foreignKey]);
            if ($exists[0]->count > 0) {
                DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$foreignKey}");
            }
        };

        // Checkups table - convert user_id, midwife_user_id, scheduled_by_id to polymorphic
        $dropForeignKeyIfExists('checkups', 'checkups_user_id_foreign');
        $dropForeignKeyIfExists('checkups', 'checkups_midwife_user_id_foreign');
        $dropForeignKeyIfExists('checkups', 'checkups_scheduled_by_id_foreign');

        // Renames first (only if old exists and backup name is free)
        Schema::table('checkups', function (Blueprint $table) {
            if (Schema::hasColumn('checkups', 'user_id') && !Schema::hasColumn('checkups', 'user_id_old')) {
                $table->renameColumn('user_id', 'user_id_old');
            }
            if (Schema::hasColumn('checkups', 'midwife_user_id') && !Schema::hasColumn('checkups', 'midwife_user_id_old')) {
                $table->renameColumn('midwife_user_id', 'midwife_user_id_old');
            }
            if (Schema::hasColumn('checkups', 'scheduled_by_id') && !Schema::hasColumn('checkups', 'scheduled_by_id_old')) {
                $table->renameColumn('scheduled_by_id', 'scheduled_by_id_old');
            }
        });

        // Add new polymorphic columns (only missing ones)
        Schema::table('checkups', function (Blueprint $table) {
            if (!Schema::hasColumn('checkups', 'patient_id')) {
                $table->unsignedBigInteger('patient_id')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'midwife_id')) {
                $table->unsignedBigInteger('midwife_id')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'scheduled_by_id')) {
                $table->unsignedBigInteger('scheduled_by_id')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'patient_type')) {
                $table->string('patient_type')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'midwife_type')) {
                $table->string('midwife_type')->nullable();
            }
            if (!Schema::hasColumn('checkups', 'scheduled_by_type')) {
                $table->string('scheduled_by_type')->nullable();
            }
        });

        // Health records table - convert user_id and recorded_by_id to polymorphic
        $dropForeignKeyIfExists('health_records', 'health_records_user_id_foreign');
        $dropForeignKeyIfExists('health_records', 'health_records_recorded_by_id_foreign');

        Schema::table('health_records', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');
            $table->renameColumn('recorded_by_id', 'recorded_by_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->unsignedBigInteger('recorded_by_id')->nullable()->after('patient_id');
            $table->string('patient_type')->nullable()->after('patient_id');
            $table->string('recorded_by_type')->nullable()->after('recorded_by_id');
        });

        // Pregnancies table - convert user_id to polymorphic
        $dropForeignKeyIfExists('pregnancies', 'pregnancies_user_id_foreign');

        Schema::table('pregnancies', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Notifications table - convert user_id to polymorphic
        $dropForeignKeyIfExists('notifications', 'notifications_user_id_foreign');

        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Forum posts table - convert user_id to polymorphic
        $dropForeignKeyIfExists('forum_posts', 'forum_posts_user_id_foreign');

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Forum comments table - convert user_id to polymorphic
        $dropForeignKeyIfExists('forum_comments', 'forum_comments_user_id_foreign');

        Schema::table('forum_comments', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Forum likes table - convert user_id to polymorphic
        $dropForeignKeyIfExists('forum_likes', 'forum_likes_user_id_foreign');

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        // Cycles table - convert user_id to polymorphic
        $dropForeignKeyIfExists('cycles', 'cycles_user_id_foreign');

        Schema::table('cycles', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Menstruation dailies table - convert user_id to polymorphic
        $dropForeignKeyIfExists('menstruation_dailies', 'menstruation_dailies_user_id_foreign');

        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Fertility logs table - convert user_id to polymorphic
        $dropForeignKeyIfExists('fertility_logs', 'fertility_logs_user_id_foreign');

        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Preventive interventions table - convert user_id to polymorphic
        $dropForeignKeyIfExists('preventive_interventions', 'preventive_interventions_user_id_foreign');

        Schema::table('preventive_interventions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id_old');

            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // Messages table - convert sender_id and receiver_id to polymorphic
        $dropForeignKeyIfExists('messages', 'messages_sender_id_foreign');
        $dropForeignKeyIfExists('messages', 'messages_receiver_id_foreign');

        Schema::table('messages', function (Blueprint $table) {
            $table->renameColumn('sender_id', 'sender_id_old');
            $table->renameColumn('receiver_id', 'receiver_id_old');

            $table->unsignedBigInteger('sender_id')->nullable()->after('id');
            $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->string('receiver_type')->nullable()->after('receiver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This would be very complex to rollback
        // For now, we'll just note that rollback should be done by restoring from backup
        throw new \Exception('Rollback not supported. Restore from database backup instead.');
    }
};
