<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PatientPresentation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;

class PatientPresentationTest extends AutomationTestCase
{
    public function test_original_patients_are_first_in_queries_and_collections(): void
    {
        $sample = $this->patient();
        $original = $this->patient();
        $service = new class($sample->id) extends PatientPresentation {
            public function __construct(private int $id) {}
            public function sampleIds(): array { return [$this->id]; }
        };
        $this->assertSame($original->id, $service->sortPatients(collect([$sample, $original]))->first()->id);
        $this->assertSame($original->id, $service->originalPatientsFirst(User::query(), 'id')->first()->id);
    }

    public function test_barangay_bhw_fallback_does_not_claim_record_authorship(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('barangay')->nullable(); $t->string('assigned_barangay')->nullable(); $t->json('catchment_barangays')->nullable();
        });
        $bhw = $this->patient(['role' => 'bhw', 'first_name' => 'BurgosWorker', 'barangay' => 'Barangay Burgos']);
        $this->patient(['role' => 'bhw', 'first_name' => 'DifferentWorker', 'barangay' => 'Other area']);
        $this->assertSame([$bhw->id], app(PatientPresentation::class)->barangayWorkers('Burgos - Padlan St')->pluck('id')->all());
        $html = Blade::render('<x-record-author :record="$record" barangay="Burgos St" />', ['record' => (object) ['recordedBy' => null]]);
        $this->assertStringContainsString('Recorder not recorded', $html);
        $this->assertStringContainsString('Barangay BHW: BurgosWorker', $html);
        $this->assertStringNotContainsString('DifferentWorker', $html);
        $this->assertCount(0, app(PatientPresentation::class)->barangayWorkers('Cacaritan'));
        $html = Blade::render('<x-record-author :record="$record" barangay="Burgos St" />', ['record' => (object) ['recordedBy' => (object) ['name' => 'Original Recorder']]]);
        $this->assertStringContainsString('Original Recorder', $html);
        $this->assertStringNotContainsString('BurgosWorker', $html);
    }
}
