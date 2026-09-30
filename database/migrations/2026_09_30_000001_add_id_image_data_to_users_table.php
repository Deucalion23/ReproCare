<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist a database copy of each valid-ID scan (data URL) so the photo
     * survives ephemeral container disks (Render free wipes
     * storage/app/public on every deploy — the file-path column alone kept
     * turning into "No ID on file"). Same pattern as profile_image_data.
     * The file-path columns stay as fallback.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'id_image_front_data')) {
                $table->longText('id_image_front_data')->nullable()->after('id_image_front');
            }
            if (! Schema::hasColumn('users', 'id_image_back_data')) {
                $table->longText('id_image_back_data')->nullable()->after('id_image_back');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'id_image_front_data')) {
                $table->dropColumn('id_image_front_data');
            }
            if (Schema::hasColumn('users', 'id_image_back_data')) {
                $table->dropColumn('id_image_back_data');
            }
        });
    }
};
