<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('emergency_contacts', 'contact_order')) {
            Schema::table('emergency_contacts', function (Blueprint $table) {
                $table->unsignedTinyInteger('contact_order')->default(1)->after('is_primary');
            });

            $contacts = DB::table('emergency_contacts')
                ->orderBy('user_id')
                ->orderByDesc('is_primary')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $currentUserId = null;
            $order = 1;

            foreach ($contacts as $contact) {
                if ($currentUserId !== $contact->user_id) {
                    $currentUserId = $contact->user_id;
                    $order = 1;
                }

                DB::table('emergency_contacts')
                    ->where('id', $contact->id)
                    ->update([
                        'contact_order' => $order,
                        'is_primary' => $order === 1,
                    ]);

                $order++;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('emergency_contacts', 'contact_order')) {
            Schema::table('emergency_contacts', function (Blueprint $table) {
                $table->dropColumn('contact_order');
            });
        }
    }
};
