<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'walk_in_patients',
        'learning_materials',
        'messages',
        'notifications',
        'cycles',
        'bhw_assignments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
