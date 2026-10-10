<?php

namespace Tests\Feature;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

    public function test_pending_unverified_login_goes_to_verification_notice(): void
    {
        $user = $this->provisional(['email_verified_at' => null]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
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
}
