<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Older SQLite databases can retain this constraint after the historic
     * patients-to-women-to-users consolidation. The women table no longer
     * exists, so it prevents every pregnancy insert.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('pregnancies')) {
            return;
        }

        try {
            Schema::table('pregnancies', function (Blueprint $table) {
                $table->dropForeign('pregnancies_woman_id_foreign');
            });
        } catch (\Throwable $e) {
            // The legacy constraint is absent on healthy or non-SQLite databases.
        }
    }

    public function down(): void
    {
        // Do not restore a reference to the removed women table.
    }
};
