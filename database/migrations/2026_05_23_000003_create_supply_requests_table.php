<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_requests', function (Blueprint $table) {
            $table->id();

            // Who is requesting (RHU admin)
            $table->foreignId('requested_by_id')->constrained('users')->onDelete('cascade');

            // Who approved (CHO)
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();

            // Supply info
            $table->enum('supply_category', [
                'vitamins',
                'vaccines',
                'birthing_kits',
                'medicines',
                'equipment',
                'ppe',
                'other'
            ])->default('other');

            $table->string('supply_name');
            $table->integer('quantity_requested');
            $table->string('unit')->nullable(); // pieces, boxes, vials, bottles, sets

            // Priority / reason
            $table->enum('urgency', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->text('reason')->nullable();

            // Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'declined',
                'delivered'
            ])->default('draft');

            // CHO feedback
            $table->text('cho_notes')->nullable();
            $table->date('expected_delivery_date')->nullable();

            // Timestamps for workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index('requested_by_id');
            $table->index('approved_by_id');
            $table->index('status');
            $table->index('urgency');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_requests');
    }
};
