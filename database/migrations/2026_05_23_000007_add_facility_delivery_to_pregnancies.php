<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            // Feature C — Facility Delivery Tracking
            if (!Schema::hasColumn('pregnancies', 'facility_delivery_place')) {
                $table->enum('facility_delivery_place', [
                    'home',
                    'barangay_health_station',
                    'rhu_birth_center',
                    'city_hospital',
                    'provincial_hospital',
                    'private_hospital',
                    'other'
                ])->nullable()->after('ended_at');
            }

            if (!Schema::hasColumn('pregnancies', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('facility_delivery_place');
            }

            if (!Schema::hasColumn('pregnancies', 'delivery_time')) {
                $table->time('delivery_time')->nullable()->after('delivery_date');
            }

            if (!Schema::hasColumn('pregnancies', 'delivery_attendant')) {
                // Who attended the delivery: midwife, doctor, nurse, hilot, self, other
                $table->string('delivery_attendant')->nullable()->after('delivery_time');
            }

            if (!Schema::hasColumn('pregnancies', 'delivery_notes')) {
                $table->text('delivery_notes')->nullable()->after('delivery_attendant');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $cols = ['facility_delivery_place', 'delivery_date', 'delivery_time', 'delivery_attendant', 'delivery_notes'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('pregnancies', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
