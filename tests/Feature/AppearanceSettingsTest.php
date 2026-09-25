<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AppearanceTheme;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppearanceSettingsTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->text('value')->nullable();
            $table->string('group')->default('general'); $table->string('label')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable(); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable();
            foreach (['user_role', 'user_name', 'action', 'model_type', 'description', 'ip_address', 'user_agent'] as $column) {
                $table->text($column)->nullable();
            }
            $table->unsignedBigInteger('model_id')->nullable(); $table->timestamps();
        });
    }

    public function test_cho_saves_global_appearance_and_every_role_receives_it(): void
    {
        $cho = $this->patient(['role' => 'cho']);
        $data = ['section' => 'appearance', 'primary' => 'pink', 'secondary' => 'teal', 'accent' => 'amber', 'mode' => 'dark'];
        $this->actingAs($cho)->put(route('cho.settings.update'), $data)
            ->assertRedirect(route('cho.settings').'#appearance')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', ['key' => 'appearance.theme', 'group' => 'appearance', 'updated_by_id' => $cho->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'update', 'user_id' => $cho->id]);
        $theme = app(AppearanceTheme::class)->current();
        $this->assertSame('pink', $theme['primary']);
        $this->assertSame('dark', $theme['mode']);
        $this->assertNotSame('default', $theme['revision']);
        foreach (['user', 'bhw', 'bhw_president', 'midwife', 'rhu', 'cho'] as $role) {
            $this->actingAs($this->patient(['role' => $role]));
            $layout = $role === 'bhw_president' ? 'bhw-president' : $role;
            $html = view($layout.'.layout')->render();
            $this->assertStringContainsString('--theme-primary: #E75480;', $html, $role);
            $this->assertStringContainsString('--theme-secondary: #2A9D8F;', $html, $role);
            $this->assertStringContainsString('theme-', $html);
        }
        auth()->logout();
        $this->get(route('login'))->assertOk()->assertSee('--theme-primary: #E75480;', false);
    }

    public function test_invalid_or_matching_colors_cannot_be_saved(): void
    {
        $this->actingAs($this->patient(['role' => 'cho']));
        foreach ([['primary' => 'pink', 'secondary' => 'pink'], ['primary' => 'red'], ['secondary' => '#000000'], ['accent' => 'invalid'], ['mode' => 'auto']] as $invalid) {
            $data = array_merge(['section' => 'appearance'], config('appearance.defaults'), $invalid);
            $this->from(route('cho.settings'))->put(route('cho.settings.update'), $data)->assertSessionHasErrors();
        }
        $this->assertDatabaseMissing('settings', ['key' => 'appearance.theme']);
    }

    public function test_appearance_settings_render_the_preview_and_existing_settings(): void
    {
        $this->actingAs($this->patient(['role' => 'cho', 'first_name' => 'Demo', 'last_name' => 'Officer']));
        $response = $this->get(route('cho.settings'))->assertOk()
            ->assertSee('Save Appearance')->assertSee('Reset to ReproCare Default')
            ->assertSee('appearance-preview')->assertSee('Risk Thresholds');
        if (getenv('REPROCARE_CAPTURE_PREVIEW')) {
            file_put_contents(storage_path('app/appearance-check/settings.html'), $response->getContent());
        }
    }

    public function test_only_cho_can_change_the_global_palette(): void
    {
        foreach (['user', 'bhw', 'bhw_president', 'midwife', 'rhu'] as $role) {
            $this->actingAs($this->patient(['role' => $role]));
            $this->put(route('cho.settings.update'), ['section' => 'appearance'] + config('appearance.defaults'))->assertForbidden();
        }
        $this->assertDatabaseMissing('settings', ['key' => 'appearance.theme']);
    }

    public function test_saved_colors_survive_service_recreation_and_defaults_can_be_restored(): void
    {
        $this->actingAs($this->patient(['role' => 'cho']));
        $this->put(route('cho.settings.update'), ['section' => 'appearance', 'primary' => 'green', 'secondary' => 'peach', 'accent' => 'purple', 'mode' => 'dark']);
        $oldRevision = (new AppearanceTheme)->current()['revision'];
        $this->assertSame('green', (new AppearanceTheme)->current()['primary']);
        $this->put(route('cho.settings.update'), ['section' => 'appearance'] + config('appearance.defaults'))->assertSessionHasNoErrors();
        $current = (new AppearanceTheme)->current();
        $this->assertNotSame($oldRevision, $current['revision']);
        unset($current['revision']);
        $this->assertSame(config('appearance.defaults'), $current);
    }

    public function test_every_selectable_color_has_readable_button_and_soft_surface_text(): void
    {
        $service = new AppearanceTheme;
        foreach ($service->palette() as $name => $color) {
            foreach ([['hex', 'on'], ['hover', 'on-hover'], ['soft', 'text-light'], ['soft-dark', 'text-dark']] as [$background, $text]) {
                $this->assertGreaterThanOrEqual(4.5, $service->contrast($color[$background], $color[$text]), "$name $background");
            }
        }
    }

    public function test_stored_invalid_values_and_missing_settings_use_safe_defaults(): void
    {
        Setting::create(['key' => 'appearance.theme', 'value' => '{"primary":"<style>","secondary":"purple","mode":"bad"}']);
        $current = (new AppearanceTheme)->current();
        $this->assertSame('purple', $current['primary']);
        $this->assertSame('pink', $current['secondary']);
        $this->assertSame('light', $current['mode']);
        Schema::drop('settings');
        $this->assertSame('purple', (new AppearanceTheme)->current()['primary']);
    }
}
