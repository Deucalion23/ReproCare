<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupplyRequestCatalogTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_05_23_000003_create_supply_requests_table.php'))->up();
        (require database_path('migrations/2026_05_23_000004_create_activity_logs_table.php'))->up();
        Schema::table('supply_requests', fn (Blueprint $t) => $t->softDeletes());
        $this->actingAs($this->patient(['role' => 'rhu']));
    }

    private function payload(): array
    {
        return ['supply_category' => 'vitamins', 'supply_name' => 'Iron tablets', 'quantity_requested' => 100,
            'unit' => 'boxes', 'urgency' => 'urgent', 'reason' => 'Low stock for scheduled prenatal visits.'];
    }

    public function test_supply_form_and_valid_supplement_submission(): void
    {
        $this->get(route('rhu.supply-requests.create'))->assertOk()->assertSee('id="supply-category"', false)
            ->assertSee('id="supply-name"', false)->assertSee('id="supply-unit"', false)->assertSee('Supplements / Micronutrients');
        $this->post(route('rhu.supply-requests.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect(route('rhu.supply-requests.index'));
        $this->assertDatabaseHas('supply_requests', ['supply_category' => 'vitamins', 'supply_name' => 'Iron tablets', 'unit' => 'boxes', 'quantity_requested' => 100]);
    }

    public function test_mismatched_item_unit_and_invalid_category_are_rejected(): void
    {
        foreach ([['supply_category' => 'supplements'], ['supply_name' => 'Surgical masks'], ['unit' => 'vials'], ['quantity_requested' => 0]] as $change) {
            $this->postJson(route('rhu.supply-requests.store'), array_replace($this->payload(), $change))
                ->assertUnprocessable()->assertJsonValidationErrors(array_keys($change));
        }
        $this->assertDatabaseCount('supply_requests', 0);
    }

    public function test_both_supply_desks_open_on_the_slim_queue_head(): void
    {
        // The tall page-hero and the filter card that squeezed Go/Clear into one
        // grid column are gone: both desks lead with the shared head bar, carry the
        // whole-scope counts as chips, and keep every filter in one header row.
        $this->get(route('rhu.supply-requests.index'))->assertOk()
            ->assertSee('class="rq-bar', false)->assertSee('awaiting CHO', false)
            ->assertSee('wd-filters', false);

        $this->actingAs($this->patient(['role' => 'cho']))
            ->get(route('cho.supply-requests.index'))->assertOk()
            ->assertSee('class="rq-bar', false)->assertSee('awaiting review', false);

        foreach ([['rhu', 'rhu.supply-requests.index'], ['cho', 'cho.supply-requests.index']] as [$role, $route]) {
            $html = $this->actingAs($this->patient(['role' => $role]))->get(route($route))->assertOk()->getContent();
            $this->assertStringNotContainsString('page-hero fade-in-card', $html, $role . ' still leads with the tall hero');
            $this->assertStringNotContainsString('col-md-1 d-flex', $html, $role . ' still squeezes the filter buttons');
        }
    }

    public function test_other_item_requires_name_and_saves_the_actual_name(): void
    {
        $data = array_replace($this->payload(), ['supply_category' => 'other', 'supply_name' => '__other__', 'unit' => 'pieces']);
        $this->postJson(route('rhu.supply-requests.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('supply_name_other');
        $this->post(route('rhu.supply-requests.store'), $data + ['supply_name_other' => 'Specimen containers'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('supply_requests', ['supply_name' => 'Specimen containers', 'unit' => 'pieces']);
        $this->actingAs($this->patient(['role' => 'user']))->postJson(route('rhu.supply-requests.store'), $this->payload())->assertForbidden();
    }
}
