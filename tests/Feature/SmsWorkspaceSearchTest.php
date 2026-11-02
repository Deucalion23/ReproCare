<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The SMS workspace is where staff hunt for someone to text before a reminder
 * goes out, so the list has to answer to how they actually type — lowercase
 * names, numbers read out in groups — and it has to stay inside their own
 * station and cover the health workers who relay messages in the field.
 */
class SmsWorkspaceSearchTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('puroks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->timestamps();
        });
    }

    private function staff(): User
    {
        return $this->patient(['role' => 'midwife', 'first_name' => 'Midwife', 'last_name' => 'Staff']);
    }

    /** Cacaritan is in RHU 1, the station this build deploys to. */
    private function client(string $first, string $last, string $number, string $barangay = 'Cacaritan'): User
    {
        return $this->make($first, $last, $number, 'user', $barangay);
    }

    private function worker(string $first, string $last, string $number, string $role = 'bhw', string $barangay = 'Cacaritan'): User
    {
        return $this->make($first, $last, $number, $role, $barangay);
    }

    private function make(string $first, string $last, string $number, string $role, string $barangay): User
    {
        return $this->patient([
            'role' => $role,
            'first_name' => $first,
            'last_name' => $last,
            'contact_number' => $number,
            'barangay' => $barangay,
        ]);
    }

    private function search(string $term): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->staff())->get(route('midwife.sms.index', ['search' => $term]));
    }

    private function visit(array $query = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->staff())->get(route('midwife.sms.index', $query));
    }

    public function test_a_lowercase_name_still_finds_the_patient(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->client('Mackie', 'Austria', '09175550002');

        $this->search('lorna pascual')
            ->assertOk()
            ->assertSee('Lorna Pascual')
            ->assertDontSee('Mackie Austria');
    }

    public function test_names_typed_in_reverse_order_still_match(): void
    {
        $this->client('Maria', 'Clara', '09567739968');
        $this->client('Josephine', 'Barro', '09123450009');

        $this->search('clara maria')
            ->assertOk()
            ->assertSee('Maria Clara')
            ->assertDontSee('Josephine Barro');
    }

    public function test_a_number_typed_in_groups_finds_the_patient(): void
    {
        $this->client('Madonna', 'De Vera', '09175550003');
        $this->client('Margarita', 'Ferrer', '09085550004');

        // The column holds digits only; staff copy the number off a form in groups.
        $this->search('0917 555 0003')
            ->assertOk()
            ->assertSee('Madonna De Vera')
            ->assertDontSee('Margarita Ferrer');
    }

    public function test_a_partial_number_finds_the_patient(): void
    {
        $this->client('Maria', 'Clara', '09567739968');
        $this->client('Josephine', 'Barro', '09123450009');

        $this->search('7739968')
            ->assertOk()
            ->assertSee('Maria Clara')
            ->assertDontSee('Josephine Barro');
    }

    public function test_a_term_nobody_matches_shows_the_empty_state(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');

        $this->search('zzz-nobody')
            ->assertOk()
            ->assertSee('No match')
            ->assertDontSee('Lorna Pascual');
    }

    public function test_a_woman_outside_the_station_catchment_is_not_offered(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        // Abanon belongs to RHU 2 — a midwife must not reach its numbers.
        $this->client('Nenita', 'Alcantara', '09175550006', 'Abanon');

        $this->visit()
            ->assertOk()
            ->assertSee('Lorna Pascual')
            ->assertDontSee('Nenita Alcantara');
    }

    public function test_the_health_workers_of_the_station_are_textable(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->worker('Selena', 'Mendoza', '09700000010');
        $this->worker('Giselle', 'Tan', '09700000007', 'bhw_president');

        $this->visit()
            ->assertOk()
            ->assertSee('Selena Mendoza')
            ->assertSee('Giselle Tan');
    }

    public function test_a_health_worker_outside_the_station_is_not_offered(): void
    {
        $this->worker('Selena', 'Mendoza', '09700000010');
        $this->worker('Bong', 'Salvacion', '09700000020', 'bhw', 'Cobol');

        $this->visit(['role' => 'bhw'])
            ->assertOk()
            ->assertSee('Selena Mendoza')
            ->assertDontSee('Bong Salvacion');
    }

    public function test_the_role_filter_hides_the_women_it_is_not_set_to(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->worker('Selena', 'Mendoza', '09700000010');

        $this->visit(['role' => 'bhw'])
            ->assertOk()
            ->assertSee('Selena Mendoza')
            ->assertDontSee('Lorna Pascual');

        $this->visit(['role' => 'user'])
            ->assertOk()
            ->assertSee('Lorna Pascual')
            ->assertDontSee('Selena Mendoza');
    }

    public function test_an_unknown_role_still_shows_everyone(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->worker('Selena', 'Mendoza', '09700000010');

        $this->visit(['role' => 'not-a-role'])
            ->assertOk()
            ->assertSee('Lorna Pascual')
            ->assertSee('Selena Mendoza');
    }

    public function test_opening_a_health_worker_thread_gives_a_composer(): void
    {
        $worker = $this->worker('Selena', 'Mendoza', '09700000010');

        $this->visit(['to' => $worker->id])
            ->assertOk()
            ->assertSee('Selena Mendoza')
            ->assertSee('Barangay health worker')
            ->assertSee('SMS to Selena Mendoza');
    }

    public function test_a_search_applies_inside_the_selected_role(): void
    {
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->worker('Lorna', 'Aguilar', '09700000013');

        $this->visit(['role' => 'bhw', 'search' => 'lorna'])
            ->assertOk()
            ->assertSee('Lorna Aguilar')
            ->assertDontSee('Lorna Pascual');
    }

    public function test_the_rhu_portal_shares_the_station_scope_and_the_role_pills(): void
    {
        $admin = $this->patient(['role' => 'rhu', 'first_name' => 'RHU', 'last_name' => 'Admin']);
        $this->client('Lorna', 'Pascual', '09175550001');
        $this->client('Nenita', 'Alcantara', '09175550006', 'Abanon');
        $this->worker('Selena', 'Mendoza', '09700000010');

        $this->actingAs($admin)->get(route('rhu.sms.index'))
            ->assertOk()
            ->assertSee('Lorna Pascual')
            ->assertSee('Selena Mendoza')
            ->assertDontSee('Nenita Alcantara');
    }

    public function test_a_walk_in_number_still_opens_a_thread_with_no_composer(): void
    {
        $this->visit(['phone' => '09175550099'])
            ->assertOk()
            ->assertSee('no portal account');
    }
}
