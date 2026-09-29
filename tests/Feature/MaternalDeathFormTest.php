<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class MaternalDeathFormTest extends AutomationTestCase
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

        Schema::create('puroks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('barangay')->nullable();
            $t->timestamps();
        });

        Schema::table('pregnancies', function (Blueprint $t) {
            $t->date('lmp')->nullable();
        });

        Schema::table('walk_in_patients', function (Blueprint $t) {
            $t->unsignedBigInteger('converted_to_user_id')->nullable();
        });

        Schema::create('maternal_deaths', function (Blueprint $t) {
            $t->id();
            $t->timestamps();
            $t->softDeletes();
        });

        View::share('unreadMsgs', 0);
        View::share('pendingCount', 0);
    }

    public function test_create_form_renders_with_patients(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));

        $this->get(route('rhu.maternal-deaths.create'))
            ->assertOk()
            ->assertSee('Link Enrolled Patient', false);
    }

    public function test_edit_form_renders_with_patients(): void
    {
        $this->actingAs($this->patient(['role' => 'rhu']));
        $id = DB::table('maternal_deaths')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('rhu.maternal-deaths.edit', $id))->assertOk();
    }
}
