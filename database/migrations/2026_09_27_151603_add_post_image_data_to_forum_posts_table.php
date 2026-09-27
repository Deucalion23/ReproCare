<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep a resized attachment in the database because Render's local disk is
     * reset between deployments and restarts.
     */
    public function up(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_posts', 'post_image_data')) {
                $table->mediumText('post_image_data')->nullable()->after('post_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            if (Schema::hasColumn('forum_posts', 'post_image_data')) {
                $table->dropColumn('post_image_data');
            }
        });
    }
};
