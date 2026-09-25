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
        // Skip column creation if they already exist (partial migration)
        if (!Schema::hasColumn('messages', 'sender_id') || !Schema::hasColumn('messages', 'receiver_id')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'sender_id')) {
                    $table->unsignedBigInteger('sender_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('messages', 'receiver_id')) {
                    $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
                }
            });
        }

        // Migrate sender/receiver data from polymorphic columns to single ids.
        // Portable query-builder version — the original MySQL
        // "UPDATE .. INNER JOIN .. SET" syntax does not parse on pgsql/sqlite.
        // Only migrates ids that belong to a user with a matching role.
        $moves = [
            ['sender_woman_id', 'sender_id', ['user']],
            ['sender_midwife_id', 'sender_id', ['midwife']],
            ['sender_bhw_id', 'sender_id', ['bhw', 'bhw_president']],
            ['receiver_woman_id', 'receiver_id', ['user']],
            ['receiver_midwife_id', 'receiver_id', ['midwife']],
            ['receiver_bhw_id', 'receiver_id', ['bhw', 'bhw_president']],
        ];
        foreach ($moves as [$source, $target, $roles]) {
            if (!Schema::hasColumn('messages', $source) || !Schema::hasColumn('messages', $target)) {
                continue;
            }
            try {
                $ids = DB::table('users')->whereIn('role', $roles)->pluck('id');
                if ($ids->isNotEmpty()) {
                    DB::table('messages')->whereNotNull($source)->whereIn($source, $ids)->update([$target => DB::raw($source)]);
                }
            } catch (\Throwable $e) {
            }
        }

        // Delete messages where sender or receiver couldn't be migrated (orphaned)
        DB::statement("
            DELETE FROM messages
            WHERE sender_id IS NULL OR receiver_id IS NULL
        ");

        // Delete messages where sender_id or receiver_id don't exist in users table
        DB::statement("
            DELETE FROM messages
            WHERE sender_id NOT IN (SELECT id FROM users)
               OR receiver_id NOT IN (SELECT id FROM users)
        ");

        // Now add foreign key constraints + indexes for new columns (best effort)
        foreach (['sender_id', 'receiver_id'] as $column) {
            if (!Schema::hasColumn('messages', $column)) {
                continue;
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->foreign($column)->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Drop old polymorphic columns (drop FKs/indexes first, best effort)
        foreach (['sender_woman_id', 'sender_midwife_id', 'sender_bhw_id', 'receiver_woman_id', 'receiver_midwife_id', 'receiver_bhw_id'] as $column) {
            if (!Schema::hasColumn('messages', $column)) {
                continue;
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    try {
                        $table->dropForeign([$column]);
                    } catch (\Throwable $e) {
                    }
                    try {
                        $table->dropIndex([$column]);
                    } catch (\Throwable $e) {
                    }
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            } catch (\Throwable $e) {
                // SQLite cannot drop FK-bound columns — rename away instead.
                if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('messages', $column)) {
                    try {
                        Schema::table('messages', function (Blueprint $table) use ($column) {
                            $table->renameColumn($column, $column . '__deprecated');
                        });
                    } catch (\Throwable $e2) {
                    }
                }
            }
        }

        // Drop scheduled_at and is_sent columns
        foreach (['scheduled_at', 'is_sent'] as $column) {
            if (!Schema::hasColumn('messages', $column)) {
                continue;
            }
            try {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('sender_woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('sender_midwife_id')->nullable()->after('sender_woman_id');
            $table->unsignedBigInteger('sender_bhw_id')->nullable()->after('sender_midwife_id');
            $table->unsignedBigInteger('receiver_woman_id')->nullable()->after('sender_bhw_id');
            $table->unsignedBigInteger('receiver_midwife_id')->nullable()->after('receiver_woman_id');
            $table->unsignedBigInteger('receiver_bhw_id')->nullable()->after('receiver_midwife_id');

            // Re-add scheduled_at and is_sent columns
            $table->timestamp('scheduled_at')->nullable()->after('reply_to_id');
            $table->boolean('is_sent')->default(true)->after('scheduled_at');
        });

        // Migrate sender data back to polymorphic columns
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_woman_id = m.sender_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_midwife_id = m.sender_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_bhw_id = m.sender_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        // Migrate receiver data back to polymorphic columns
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_woman_id = m.receiver_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_midwife_id = m.receiver_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_bhw_id = m.receiver_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        // Add foreign keys and indexes for polymorphic columns
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_bhw_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('sender_woman_id');
            $table->index('sender_midwife_id');
            $table->index('sender_bhw_id');
            $table->index('receiver_woman_id');
            $table->index('receiver_midwife_id');
            $table->index('receiver_bhw_id');
        });

        // Drop new simplified columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['receiver_id']);
            $table->dropColumn(['sender_id', 'receiver_id']);
        });
    }
};
