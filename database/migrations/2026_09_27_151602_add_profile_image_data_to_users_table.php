<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist a small resized copy of the avatar in the database so the
     * photo survives ephemeral container disks (Render free) and renders
     * identically on every device. The file-path column stays as fallback.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_image_data')) {
                $table->text('profile_image_data')->nullable()->after('profile_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_image_data')) {
                $table->dropColumn('profile_image_data');
            }
        });
    }
};
