<?php

namespace Tests\Feature;

use App\Http\Controllers\RhuController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RhuBhwPresidentViewTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('barangays', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('rhu_assignment')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function test_edit_bhw_president_passes_president_to_view(): void
    {
        $president = $this->patient(['role' => 'bhw_president', 'first_name' => 'Ana', 'last_name' => 'Pres']);
        $this->actingAs($this->patient(['role' => 'rhu']));

        $view = (new RhuController())->editBhwPresident($president->id);

        $this->assertArrayHasKey('president', $view->getData());
        $this->assertSame($president->id, $view->getData()['president']->id);
        $this->assertArrayHasKey('barangays', $view->getData());
    }

    public function test_show_bhw_president_passes_president_to_view(): void
    {
        $president = $this->patient(['role' => 'bhw_president', 'first_name' => 'Ana', 'last_name' => 'Pres']);
        $this->actingAs($this->patient(['role' => 'rhu']));

        $view = (new RhuController())->showBhwPresident($president->id);

        $this->assertArrayHasKey('president', $view->getData());
        $this->assertSame($president->id, $view->getData()['president']->id);
    }
}
