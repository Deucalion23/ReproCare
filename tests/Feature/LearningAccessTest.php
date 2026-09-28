<?php

namespace Tests\Feature;

use App\Http\Controllers\ForumController;
use App\Http\Controllers\LearningController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class LearningAccessTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_midwife_forum_admin_is_removed(): void
    {
        foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'bulk-delete', 'restore'] as $action) {
            $this->assertFalse(Route::has('midwife.forum.admin.' . $action), $action);
        }

        foreach (['adminIndex', 'adminCreate', 'adminStore', 'adminShow', 'adminEdit', 'adminUpdate', 'adminDestroy', 'bulkDelete', 'restorePost'] as $method) {
            $this->assertFalse(method_exists(ForumController::class, $method), $method);
        }

        $this->actingAs($this->patient(['role' => 'midwife']));
        $this->get('/midwife/forum-admin')->assertNotFound();
    }

    public function test_midwife_learning_admin_is_removed(): void
    {
        foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'] as $action) {
            $this->assertFalse(Route::has('midwife.learning.' . $action), $action);
        }

        $this->actingAs($this->patient(['role' => 'midwife']));
        $this->get('/midwife/learning')->assertNotFound();
        $this->get('/midwife/learning/create')->assertNotFound();
    }

    public function test_rhu_and_cho_own_learning_upload_routes(): void
    {
        foreach (['rhu', 'cho'] as $prefix) {
            foreach (['index', 'create', 'store', 'edit', 'update', 'destroy'] as $action) {
                $this->assertTrue(Route::has($prefix . '.learning.' . $action), $prefix . '.' . $action);
            }
        }
    }

    public function test_rhu_and_cho_manage_pages_render(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));
        $this->get(route('rhu.learning.index'))->assertOk()->assertSee('Learning Materials', false);

        $this->actingAs($this->patient(['role' => 'cho']));
        $this->get(route('cho.learning.index'))->assertOk()->assertSee('Learning Materials', false);
    }

    public function test_only_rhu_and_cho_pass_the_upload_gate(): void
    {
        foreach (['midwife', 'bhw', 'bhw_president', 'user'] as $role) {
            $this->actingAs($this->patient(['role' => $role]));
            try {
                (new LearningController())->adminIndex(new Request());
                $this->fail("{$role} should be blocked from learning upload.");
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }

        $this->actingAs($this->patient(['role' => 'rhu']));
        $view = (new LearningController())->adminIndex(new Request());
        $this->assertSame('rhu.learning', $view->getData()['routeBase']);

        $this->actingAs($this->patient(['role' => 'cho']));
        $view = (new LearningController())->adminIndex(new Request());
        $this->assertSame('cho.learning', $view->getData()['routeBase']);
    }
}
