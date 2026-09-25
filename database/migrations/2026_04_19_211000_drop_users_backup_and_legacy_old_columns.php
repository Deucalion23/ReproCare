<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexExists = function (string $table, string $indexName): bool {
            return DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', $table)
                ->where('INDEX_NAME', $indexName)
                ->exists();
        };

        if (Schema::hasTable('forum_likes') && !$indexExists('forum_likes', 'forum_likes_post_id_user_id_user_type_unique')) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'user_id', 'user_type'], 'forum_likes_post_id_user_id_user_type_unique');
            });
        }

        if (Schema::hasTable('messages') && !$indexExists('messages', 'messages_receiver_type_read_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['receiver_id', 'receiver_type', 'is_read'], 'messages_receiver_type_read_index');
            });
        }

        if (Schema::hasTable('messages') && !$indexExists('messages', 'messages_sender_type_index')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['sender_id', 'sender_type'], 'messages_sender_type_index');
            });
        }

        if (Schema::hasTable('fertility_logs') && !$indexExists('fertility_logs', 'fertility_logs_patient_log_date_unique')) {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->unique(['patient_id', 'patient_type', 'log_date'], 'fertility_logs_patient_log_date_unique');
            });
        }

        if (Schema::hasTable('fertility_logs') && !$indexExists('fertility_logs', 'fertility_logs_patient_log_date_index')) {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->index(['patient_id', 'patient_type', 'log_date'], 'fertility_logs_patient_log_date_index');
            });
        }

        if (Schema::hasTable('menstruation_dailies') && !$indexExists('menstruation_dailies', 'menstruation_dailies_patient_date_unique')) {
            Schema::table('menstruation_dailies', function (Blueprint $table) {
                $table->unique(['patient_id', 'patient_type', 'date'], 'menstruation_dailies_patient_date_unique');
            });
        }

        if (Schema::hasTable('preventive_interventions') && !$indexExists('preventive_interventions', 'preventive_interventions_patient_index')) {
            Schema::table('preventive_interventions', function (Blueprint $table) {
                $table->index(['patient_id', 'patient_type'], 'preventive_interventions_patient_index');
            });
        }

        $dropIndexedColumnIfExists = function (string $table, string $column): void {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                return;
            }

            $indexes = DB::table('information_schema.STATISTICS')
                ->select('INDEX_NAME')
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->where('INDEX_NAME', '!=', 'PRIMARY')
                ->distinct()
                ->pluck('INDEX_NAME');

            foreach ($indexes as $indexName) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP INDEX `%s`',
                    $table,
                    $indexName
                ));
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                $tableBlueprint->dropColumn($column);
            });
        };

        $legacyColumns = [
            'checkups' => ['user_id_old', 'midwife_user_id_old', 'scheduled_by_id_old'],
            'health_records' => ['user_id_old', 'recorded_by_id_old'],
            'pregnancies' => ['user_id_old'],
            'notifications' => ['user_id_old'],
            'forum_posts' => ['user_id_old'],
            'forum_comments' => ['user_id_old'],
            'forum_likes' => ['user_id_old'],
            'fertility_logs' => ['user_id_old'],
            'menstruation_dailies' => ['user_id_old'],
            'preventive_interventions' => ['user_id_old'],
            'messages' => ['sender_id_old', 'receiver_id_old'],
        ];

        foreach ($legacyColumns as $table => $columns) {
            foreach ($columns as $column) {
                $dropIndexedColumnIfExists($table, $column);
            }
        }

        if (Schema::hasTable('users_backup')) {
            Schema::drop('users_backup');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexIfExists = function (string $table, string $indexName): void {
            $exists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', $table)
                ->where('INDEX_NAME', $indexName)
                ->exists();

            if ($exists) {
                DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName));
            }
        };

        if (!Schema::hasTable('users_backup')) {
            Schema::create('users_backup', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('name');
                $table->string('email');
                $table->enum('role', ['midwife', 'bhw', 'user']);
                $table->enum('status', ['pending', 'approved', 'rejected']);
                $table->string('rejection_reason')->nullable();
                $table->string('address')->nullable();
                $table->string('barangay')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('gender')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('profile_image')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $restoreColumnIfMissing = function (string $table, string $column): void {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
                return;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
                $tableBlueprint->unsignedBigInteger($column)->nullable()->index();
            });
        };

        $legacyColumns = [
            'checkups' => ['user_id_old', 'midwife_user_id_old', 'scheduled_by_id_old'],
            'health_records' => ['user_id_old', 'recorded_by_id_old'],
            'pregnancies' => ['user_id_old'],
            'notifications' => ['user_id_old'],
            'forum_posts' => ['user_id_old'],
            'forum_comments' => ['user_id_old'],
            'forum_likes' => ['user_id_old'],
            'fertility_logs' => ['user_id_old'],
            'menstruation_dailies' => ['user_id_old'],
            'preventive_interventions' => ['user_id_old'],
            'messages' => ['sender_id_old', 'receiver_id_old'],
        ];

        foreach ($legacyColumns as $table => $columns) {
            foreach ($columns as $column) {
                $restoreColumnIfMissing($table, $column);
            }
        }

        $dropIndexIfExists('forum_likes', 'forum_likes_post_id_user_id_user_type_unique');
        $dropIndexIfExists('messages', 'messages_receiver_type_read_index');
        $dropIndexIfExists('messages', 'messages_sender_type_index');
        $dropIndexIfExists('fertility_logs', 'fertility_logs_patient_log_date_unique');
        $dropIndexIfExists('fertility_logs', 'fertility_logs_patient_log_date_index');
        $dropIndexIfExists('menstruation_dailies', 'menstruation_dailies_patient_date_unique');
        $dropIndexIfExists('preventive_interventions', 'preventive_interventions_patient_index');
    }
};
