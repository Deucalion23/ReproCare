<?php

namespace Tests\Feature;

use App\Http\Controllers\ForumController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForumUnifyTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('forum_posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('content')->nullable();
            $t->string('status')->default('active');
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

    public function test_every_role_uses_the_shared_forum_views(): void
    {
        foreach (['user', 'midwife', 'bhw', 'bhw_president', 'rhu', 'cho'] as $role) {
            $this->actingAs($this->patient(['role' => $role]));

            $this->assertSame('forum.index', (new ForumController())->index()->getName(), $role);
            $this->assertSame('forum.create', (new ForumController())->create()->getName(), $role);
        }
    }

    public function test_forum_never_labels_the_viewer_with_their_role(): void
    {
        foreach (['user', 'midwife', 'bhw', 'bhw_president', 'rhu', 'cho'] as $role) {
            $index = $this->actingAs($this->patient(['role' => $role]))
                ->get(route('forum.index'))
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString('forum-role-badge', $index, $role);

            $create = $this->actingAs($this->patient(['role' => $role]))
                ->get(route('forum.create'))
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString('Posting as', $create, $role);
        }
    }
}
