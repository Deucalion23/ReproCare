<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Home-address completion for pregnant patients.
 *
 * Once a patient has an active pregnancy, a missing house number or
 * street/sitio/purok triggers: (1) a modal alert on the dashboard (once
 * per session) pointing at the profile form, (2) a persistent warning
 * notification (created once), and (3) a hard gate on self-reported
 * pregnancy creation until the profile is fixed. Staff read the same
 * users row, so the address is visible to them the moment it is saved.
 */
class AddressCompletionTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tables the dashboard view + profile update touch.
        Schema::create('puroks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('barangay');
            $t->timestamps();
        });
        Schema::create('maternal_care_target_clients', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pregnancy_id')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('forum_posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('content')->nullable();
            $t->string('status')->default('active');
            $t->string('user_type')->nullable();
            $t->string('post_image')->nullable();
            $t->longText('post_image_data')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('forum_likes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('post_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });
        Schema::create('forum_comments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('post_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('content')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    protected function pregnantPatient(array $overrides = []): User
    {
        $user = $this->patient($overrides);

        DB::table('pregnancies')->insert([
            'user_id' => $user->id,
            'lmp' => '2026-06-01',
            'edd' => '2026-12-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user->fresh();
    }

    public function test_missing_detection_flags_house_and_street(): void
    {
        $bare = $this->patient(['address' => null]);

        $this->assertSame(['house_number', 'street'], $bare->missingAddressDetails());

        $withHouseOnly = $this->patient([
            'house_number' => '123',
            'address' => 'House/Unit 123, Burgos St, San Carlos City, Pangasinan',
        ]);

        // Purok-less legacy text still counts: "Burgos St" is a street marker.
        $this->assertSame([], $withHouseOnly->missingAddressDetails());

        $noMarkers = $this->patient(['address' => 'San Carlos City, Pangasinan']);

        $this->assertSame(['house_number', 'street'], $noMarkers->missingAddressDetails());
    }

    public function test_dashboard_shows_modal_and_notification_once(): void
    {
        $user = $this->pregnantPatient(['address' => null]);
        $this->actingAs($user);

        $this->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('showAddressAlert')
            ->assertSee('Complete your home address');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Complete your home address',
        ]);

        // Second visit in the same session: no auto-show, no duplicate.
        $this->get(route('user.dashboard'))
            ->assertOk()
            ->assertDontSee('showAddressAlert');

        $this->assertSame(1, DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('title', 'Complete your home address')
            ->count());
    }

    public function test_no_modal_or_notification_without_pregnancy_or_with_address(): void
    {
        $notPregnant = $this->patient(['address' => null]);
        $this->actingAs($notPregnant);
        $this->get(route('user.dashboard'))->assertOk()->assertDontSee('showAddressAlert');

        $complete = $this->pregnantPatient([
            'house_number' => '456',
            'address' => 'House/Unit 456, Purok 2, Burgos St, San Carlos City, Pangasinan',
        ]);
        $this->actingAs($complete);
        $this->get(route('user.dashboard'))->assertOk()->assertDontSee('showAddressAlert');

        $this->assertDatabaseMissing('notifications', [
            'title' => 'Complete your home address',
        ]);
    }

    public function test_pregnancy_creation_requires_address_details(): void
    {
        $user = $this->pregnantPatient(['address' => null]);
        $this->actingAs($user);

        $response = $this->post(route('user.pregnancies.store'), ['lmp' => '2026-06-01']);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('address_details');
    }

    public function test_pregnancy_creation_proceeds_with_complete_address(): void
    {
        $user = $this->pregnantPatient([
            'house_number' => '789',
            'address' => 'House/Unit 789, Purok 3, Burgos St, San Carlos City, Pangasinan',
        ]);
        $this->actingAs($user);

        // An active pregnancy already exists, so the controller's own
        // duplicate guard (not the address gate) must answer here.
        $response = $this->post(route('user.pregnancies.store'), ['lmp' => '2026-06-01']);

        $response->assertSessionDoesntHaveErrors('address_details');
        $response->assertSessionHasErrors('pregnancy');
    }

    public function test_profile_update_persists_house_number_and_composed_address(): void
    {
        $user = $this->patient(['address' => null]);
        $this->actingAs($user);

        $response = $this->put(route('profile.update'), [
            'house_number' => '321',
            'purok' => 'Purok 7',
            'barangay' => 'Burgos St',
        ]);

        $response->assertRedirect();
        $fresh = $user->fresh();
        $this->assertSame('321', $fresh->house_number);
        $this->assertStringContainsString('House/Unit 321', (string) $fresh->address);
        $this->assertSame([], $fresh->missingAddressDetails());
    }
}
