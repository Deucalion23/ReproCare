<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction: each column is attempted on its own and a
     * failure on one must not abort the rest (Postgres poisons the whole
     * transaction on the first caught error).
     */
    public $withinTransaction = false;

    /**
     * The abandoned polymorphic rewrite renamed the original id columns to
     * *_old__deprecated and a replacement column took their name. A rename
     * keeps the NOT NULL constraint, so the dead columns still reject any
     * insert that does not mention them — and no controller mentions them,
     * because nothing reads them. That is why forum likes never saved
     * (ForumController::like() swallows the QueryException) and why sending a
     * new message threw. Seeders had been papering over it by filling the
     * dead column by hand.
     */
    public function up(): void
    {
        foreach ([
            'forum_likes' => ['user_id_old__deprecated'],
            'health_records' => ['user_id_old__deprecated', 'recorded_by_id_old__deprecated'],
            'menstruation_dailies' => ['user_id_old__deprecated'],
            'messages' => ['sender_id_old__deprecated', 'receiver_id_old__deprecated'],
        ] as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->unsignedBigInteger($column)->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        // Restoring the constraint would re-break the writes this exists to unblock.
    }
};
