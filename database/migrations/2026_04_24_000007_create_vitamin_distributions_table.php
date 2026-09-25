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
        if (!Schema::hasTable('vitamin_distributions')) {
            Schema::create('vitamin_distributions', function (Blueprint $table) {
                $table->id();
                
                // Only add user_id foreign key if users table exists
                if (Schema::hasTable('users')) {
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->foreignId('distributed_by_id')->nullable()->constrained('users')->onDelete('set null');
                } else {
                    $table->foreignId('user_id')->nullable();
                    $table->foreignId('distributed_by_id')->nullable();
                }
                
                $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->onDelete('cascade');
                $table->foreignId('vitamin_id')->constrained('vitamins')->onDelete('restrict');
                $table->timestamp('distributed_at')->useCurrent();
                $table->integer('quantity')->default(1);
                $table->text('notes')->nullable();
                $table->enum('compliance_status', ['compliant', 'non_compliant', 'unknown'])->default('unknown');
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('pregnancy_id');
                $table->index('vitamin_id');
                $table->index('distributed_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitamin_distributions');
    }
};
