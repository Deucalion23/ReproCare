<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('relationship'); // e.g. Spouse, Parent, Sibling, Friend
            $table->string('contact_number');
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(true); // true = 1st contact, false = 2nd
            $table->unsignedTinyInteger('contact_order')->default(1);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
