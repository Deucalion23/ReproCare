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
        // Make the owner column nullable to support walk-in patients.
        // NOTE: at this point the column is still woman_id (renamed to
        // user_id only by a later migration).
        $owner = Schema::hasColumn('maternal_care_target_clients', 'woman_id') ? 'woman_id'
            : (Schema::hasColumn('maternal_care_target_clients', 'user_id') ? 'user_id' : null);
        if (!$owner) {
            return;
        }
        Schema::table('maternal_care_target_clients', function (Blueprint $table) use ($owner) {
            $table->unsignedBigInteger($owner)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make user_id not nullable again
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
