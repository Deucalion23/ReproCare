<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The SMS workspace is where staff hunt for a patient before sending a reminder,
 * so the box has to answer to how they actually type: lowercase names and numbers
 * read out in groups.
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

    private function client(string $first, string $last, string $number): User
    {
        return $this->patient(['first_name' => $first, 'last_name' => $last, 'contact_number' => $number]);
    }

    private function search(string $term): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->staff())->get(route('midwife.sms.index', ['search' => $term]));
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
}
