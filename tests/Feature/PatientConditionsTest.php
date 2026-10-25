<?php

namespace Tests\Feature;

use App\Models\PatientCondition;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

class PatientConditionsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $table) {
            $table->string('rhu_assignment')->nullable();
        });
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rhu_assignment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('barangay')->nullable();
            $table->timestamps();
        });
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('contact_order')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        (require database_path('migrations/2026_10_25_000001_create_patient_conditions_table.php'))->up();
    }

    public function test_rhu_can_record_multiple_conditions_and_attribute_updates(): void
    {
        $this->barangay('Padlan', 'RHU 1');
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $woman = $this->patient(['barangay' => 'Padlan']);

        $this->actingAs($rhu)->post(route('rhu.women.conditions.store', $woman->id), [
            'condition_name' => 'Diabetes',
            'diagnosed_on' => '2024-02-10',
            'status' => 'Active',
            'verification_status' => 'Verified',
            'notes' => 'Diagnosis reported by clinic.',
        ])->assertRedirect(route('rhu.women.show', ['type' => 'registered', 'id' => $woman->id]));

        $second = $this->actingAs($rhu)->post(route('rhu.women.conditions.store', $woman->id), [
            'condition_name' => 'Anemia',
            'status' => 'Under Review',
            'verification_status' => 'Awaiting Verification',
        ]);
        $second->assertRedirect();

        $diabetes = PatientCondition::where('user_id', $woman->id)->where('condition_name', 'Diabetes')->firstOrFail();
        $this->assertSame($rhu->id, $diabetes->recorded_by_id);
        $this->assertSame('2024-02-10', $diabetes->diagnosed_on->format('Y-m-d'));
        $this->assertDatabaseHas('patient_conditions', [
            'user_id' => $woman->id,
            'condition_name' => 'Anemia',
            'diagnosed_on' => null,
            'recorded_by_id' => $rhu->id,
        ]);

        $this->actingAs($rhu)->put(route('rhu.women.conditions.update', [$woman->id, $diabetes->id]), [
            'condition_name' => 'Diabetes',
            'diagnosed_on' => '2024-02-10',
            'status' => 'Resolved',
            'verification_status' => 'Verified',
            'notes' => 'Follow-up confirms condition resolved.',
        ])->assertRedirect();

        $this->assertDatabaseHas('patient_conditions', [
            'id' => $diabetes->id,
            'status' => 'Resolved',
            'updated_by_id' => $rhu->id,
            'recorded_by_id' => $rhu->id,
        ]);
    }

    public function test_midwife_cannot_create_or_update_conditions(): void
    {
        $this->barangay('Padlan', 'RHU 1');
        $midwife = $this->patient(['role' => 'midwife']);
        $woman = $this->patient(['barangay' => 'Padlan']);
        $condition = PatientCondition::create([
            'user_id' => $woman->id,
            'condition_name' => 'Asthma',
            'status' => 'Active',
            'verification_status' => 'Reported',
        ]);
        $data = [
            'condition_name' => 'Asthma',
            'status' => 'Active',
            'verification_status' => 'Reported',
        ];

        $this->actingAs($midwife)
            ->post(route('rhu.women.conditions.store', $woman->id), $data)
            ->assertForbidden();
        $this->actingAs($midwife)
            ->put(route('rhu.women.conditions.update', [$woman->id, $condition->id]), $data)
            ->assertForbidden();

        $this->assertDatabaseHas('patient_conditions', [
            'id' => $condition->id,
            'verification_status' => 'Reported',
            'updated_by_id' => null,
        ]);
    }

    public function test_rhu_cannot_add_a_condition_to_a_woman_outside_its_catchment(): void
    {
        $this->barangay('Outside', 'RHU 2');
        $rhu = $this->patient(['role' => 'rhu', 'rhu_assignment' => 'RHU 1']);
        $woman = $this->patient(['barangay' => 'Outside']);

        $this->actingAs($rhu)
            ->post(route('rhu.women.conditions.store', $woman->id), [
                'condition_name' => 'Hypertension',
                'status' => 'Active',
                'verification_status' => 'Reported',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('patient_conditions', 0);
    }

    public function test_shared_condition_history_is_read_only_for_midwife_panels(): void
    {
        $woman = $this->patient();
        $woman->setAttribute('medical_history', json_encode(['allergies' => 'Latex']));
        $condition = PatientCondition::create([
            'user_id' => $woman->id,
            'condition_name' => 'Thyroid condition',
            'status' => 'Under Review',
            'verification_status' => 'Awaiting Verification',
        ])->load(['recordedBy', 'updatedBy']);

        $html = Blade::render(view('partials.patient-conditions', [
            'woman' => $woman,
            'medicalConditions' => collect([$condition]),
            'canManageConditions' => false,
        ])->render());

        $this->assertStringContainsString('Thyroid condition', $html);
        $this->assertStringContainsString('Awaiting Verification', $html);
        $this->assertStringContainsString('View and review only', $html);
        $this->assertStringNotContainsString('Record a medical condition', $html);
        $this->assertStringNotContainsString('Edit condition record', $html);
        $this->assertStringContainsString('Latex', $html);
        $this->assertStringContainsString('not a verified diagnosis', $html);

        $rhuHtml = Blade::render(view('partials.patient-conditions', [
            'woman' => $woman,
            'medicalConditions' => collect([$condition]),
            'canManageConditions' => true,
        ])->render());

        $this->assertStringContainsString('Record a medical condition', $rhuHtml);
        $this->assertStringContainsString('Edit condition record', $rhuHtml);
        $this->assertStringContainsString(route('rhu.women.conditions.store', $woman->id), $rhuHtml);
    }

    private function barangay(string $name, string $rhu): void
    {
        \Illuminate\Support\Facades\DB::table('barangays')->insert([
            'name' => $name,
            'rhu_assignment' => $rhu,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
