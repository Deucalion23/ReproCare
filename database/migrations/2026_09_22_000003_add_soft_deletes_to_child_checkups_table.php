<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Child checkups are clinical history (growth + immunizations) —
     * they must soft-delete, never hard-delete.
     */
    public function up(): void
    {
        Schema::table('child_checkups', function (Blueprint $table) {
            if (! Schema::hasColumn('child_checkups', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('child_checkups', function (Blueprint $table) {
            if (Schema::hasColumn('child_checkups', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
