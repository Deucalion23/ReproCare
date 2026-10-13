<?php

namespace Tests\Feature;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * Provisional (limited) access for patients awaiting RHU approval.
 *
 * Verified + onboarded pending patients may browse the dashboard,
 * learning materials, read-only community and their own profile, while
 * clinical and write features (pregnancies, cycle logging, checkups,
 * health records, care chat, community posting) stay locked until
 * approval. Demo accounts always enjoy full access.
 */
class ProvisionalAccessTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('emergency_contacts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name')->nullable();
            $t->string('relationship')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('address')->nullable();
            $t->integer('contact_order')->default(1);
            $t->boolean('is_primary')->default(false);
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
        Schema::create('learning_materials', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->text('content')->nullable();
            $t->string('material_type')->nullable();
            $t->string('category')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        View::share('unreadMsgs', 0);
        View::share('pendingCount', 0);
    }

    protected function provisional(array $overrides = []): User
    {
        return $this->patient(array_merge([
            'status' => 'pending',
            'email_verified_at' => now(),
            'is_profile_complete' => true,
        ], $overrides));
    }

    protected function viewOnly(array $overrides = []): User
    {
        // Pending, still owing verification + onboarding: browses the
        // portal in view-only mode, every input blocked.
        return $this->patient(array_merge([
            'status' => 'pending',
            'email_verified_at' => null,
            'is_profile_complete' => false,
        ], $overrides));
    }

    protected function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'View',
            'last_name' => 'Only',
            'email' => 'viewonly-new-' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'barangay' => 'Burgos St',
            'id_image_data_front' => 'data:image/png;base64,iVBORw0KGgo=',
            'id_image_data_back' => 'data:image/png;base64,iVBORw0KGgo=',
            'emergency_name_1' => 'Ana Reyes',
            'emergency_relationship_1' => 'Mother',
            'emergency_contact_number_1' => '09173334444',
        ], $overrides);
    }

    public function test_pending_verified_complete_login_keeps_limited_session(): void
    {
        $user = $this->provisional();

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_pending_unverified_complete_login_lands_on_dashboard(): void
    {
        // Unverified-but-complete pending users browse view-only instead
        // of parking on the verification notice.
        $user = $this->provisional(['email_verified_at' => null]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('viewonly_welcome');
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_pending_incomplete_login_goes_to_profile_completion(): void
    {
        $user = $this->provisional(['is_profile_complete' => false]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('profile.complete'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_provisional_user_can_open_dashboard_learning_and_read_only_community(): void
    {
        $user = $this->provisional();
        $post = ForumPost::create([
            'user_id' => $user->id,
            'content' => 'A community post to browse while pending approval.',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $this->get(route('user.dashboard'))->assertOk();
        $this->get(route('learning.index'))->assertOk();
        $this->get(route('forum.index'))->assertOk();
        $this->get(route('forum.show', $post->id))->assertOk();
        $this->get(route('user.settings'))->assertOk();
        $this->get(route('user.notifications'))->assertOk();
    }

    public function test_provisional_user_is_bounced_from_clinical_pages(): void
    {
        $user = $this->provisional();
        $this->actingAs($user);

        foreach ([
            'user.pregnancies.index',
            'user.menstruation.index',
            'user.checkups',
            'user.health-records',
            'user.messages.index',
            'forum.create',
        ] as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('user.dashboard'));
            $response->assertSessionHas('provisional_blocked');
        }
    }

    public function test_provisional_user_writes_are_rejected_without_side_effects(): void
    {
        $user = $this->provisional();
        $post = ForumPost::create([
            'user_id' => $user->id,
            'content' => 'Like and comment targets stay untouched.',
            'status' => 'active',
        ]);
        $this->actingAs($user);

        $this->post(route('forum.store'), ['content' => 'Should never persist.'])
            ->assertSessionHasErrors('access');
        $this->assertDatabaseMissing('forum_posts', ['content' => 'Should never persist.']);

        $this->post(route('forum.comment', $post->id), ['content' => 'Blocked comment.'])
            ->assertSessionHasErrors('access');

        $this->post(route('forum.like', $post->id))
            ->assertSessionHasErrors('access');

        $this->post(route('user.messages.send'), ['content' => 'Blocked message.'])
            ->assertSessionHasErrors('access');

        $this->post(route('user.menstruation.store'), ['start_date' => '2026-09-01'])
            ->assertSessionHasErrors('access');
    }

    public function test_approved_user_keeps_full_access(): void
    {
        $user = $this->patient();
        $this->actingAs($user);

        $this->get(route('user.pregnancies.index'))->assertOk();
        $this->get(route('user.messages.index'))->assertOk();
        $this->get(route('forum.create'))->assertOk();
    }

    public function test_pending_demo_account_keeps_full_access(): void
    {
        $demo = $this->provisional([
            'email' => User::DEMO_EMAILS[0],
            'email_verified_at' => null,
            'is_profile_complete' => false,
        ]);

        // Even unverified + incomplete, the demo logs straight in.
        $response = $this->post(route('login.post'), [
            'email' => $demo->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('user.dashboard'));

        // ...and clinical pages stay open for it.
        $this->actingAs($demo->fresh());
        $this->get(route('user.pregnancies.index'))->assertOk();
        $this->get(route('user.messages.index'))->assertOk();
        $this->get(route('forum.create'))->assertOk();
    }

    public function test_registration_signs_new_account_into_view_only_portal(): void
    {
        Storage::fake('public');
        $payload = $this->registrationPayload();

        $response = $this->post(route('register.post'), $payload);

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('viewonly_welcome');
        $this->assertAuthenticated();

        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user);
        $this->assertSame('pending', $user->status);
        $this->assertTrue($user->hasViewOnlyAccess());

        // And the portal actually renders for them.
        $this->get(route('user.dashboard'))->assertOk()->assertSee('Browsing in view-only mode');
    }

    public function test_view_only_login_lands_on_dashboard(): void
    {
        $user = $this->viewOnly(['is_profile_complete' => true]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_view_only_user_browses_open_pages(): void
    {
        $user = $this->viewOnly();
        $this->actingAs($user);

        $this->get(route('user.dashboard'))->assertOk()->assertSee('Browsing in view-only mode');
        $this->get(route('learning.index'))->assertOk();
        $this->get(route('forum.index'))->assertOk();
        $this->get(route('user.notifications'))->assertOk();
        // Own profile resolves through settings (pre-existing redirect).
        $this->get(route('profile.show'))->assertRedirect(route('user.settings'));
        $this->get(route('user.settings'))->assertOk();
    }

    public function test_view_only_clinical_pages_bounce_to_dashboard(): void
    {
        $user = $this->viewOnly();
        $this->actingAs($user);

        foreach ([
            'user.pregnancies.index',
            'user.menstruation.index',
            'user.checkups',
            'user.health-records',
            'user.messages.index',
            'forum.create',
        ] as $route) {
            $this->get(route($route))
                ->assertRedirect(route('user.dashboard'))
                ->assertSessionHas('provisional_blocked');
        }
    }

    public function test_view_only_writes_are_rejected(): void
    {
        $user = $this->viewOnly();
        $this->actingAs($user);

        $this->post(route('user.pregnancies.store'), ['lmp' => '2026-06-01'])
            ->assertSessionHasErrors('access');
        $this->assertDatabaseCount('pregnancies', 0);

        $this->post(route('forum.store'), ['content' => 'Blocked post.'])
            ->assertSessionHasErrors('access');
        $this->assertDatabaseCount('forum_posts', 0);

        $this->put(route('profile.update'), ['house_number' => '999'])
            ->assertSessionHasErrors('access');
        $this->assertNull($user->fresh()->house_number);

        $materialId = DB::table('learning_materials')->insertGetId([
            'title' => 'Quiz material',
            'material_type' => 'quiz',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->post(route('learning.quiz.submit', $materialId), ['answers' => []])
            ->assertSessionHasErrors('access');
    }

    public function test_provisional_user_may_still_update_profile(): void
    {
        $user = $this->provisional();
        $this->actingAs($user);

        $this->put(route('profile.update'), ['house_number' => '123'])
            ->assertSessionDoesntHaveErrors('access');

        $this->assertSame('123', $user->fresh()->house_number);
    }

    public function test_session_check_treats_pending_sessions_as_active(): void
    {
        $this->actingAs($this->viewOnly());
        $this->getJson(route('api.device.session-check'))->assertJsonPath('active', true);

        $this->actingAs($this->provisional());
        $this->getJson(route('api.device.session-check'))->assertJsonPath('active', true);

        $suspended = $this->patient(['status' => 'suspended']);
        $this->actingAs($suspended);
        $this->getJson(route('api.device.session-check'))->assertJsonPath('active', false);
    }
}
