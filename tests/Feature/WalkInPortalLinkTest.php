<?php

namespace Tests\Feature;

use App\Models\Pregnancy;
use App\Models\User;
use App\Models\WalkInPatient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A field record collected in the barangay carries the pregnancy while it has
 * no account. Turning that record into a portal account has to hand the
 * pregnancy over too, otherwise the case keeps reading as an unlinked profile:
 * it hides from every "registered woman" search and offers no route to the
 * new record.
 */
class WalkInPortalLinkTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('walk_in_patients', function (Blueprint $t) {
            if (!Schema::hasColumn('walk_in_patients', 'user_id')) $t->unsignedBigInteger('user_id')->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'converted_to_user_id')) $t->unsignedBigInteger('converted_to_user_id')->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'converted_at')) $t->timestamp('converted_at')->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'has_portal_access')) $t->boolean('has_portal_access')->default(false);
            if (!Schema::hasColumn('walk_in_patients', 'middle_initial')) $t->string('middle_initial', 5)->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'purok_id')) $t->unsignedBigInteger('purok_id')->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'date_of_birth')) $t->date('date_of_birth')->nullable();
            if (!Schema::hasColumn('walk_in_patients', 'barangay')) $t->string('barangay')->nullable();
        });

        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'created_by_midwife_id')) $t->unsignedBigInteger('created_by_midwife_id')->nullable();
        });

        Schema::table('pregnancies', function (Blueprint $t) {
            if (!Schema::hasColumn('pregnancies', 'walk_in_patient_id')) $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            if (!Schema::hasColumn('pregnancies', 'is_high_risk')) $t->boolean('is_high_risk')->default(false);
        });

        // pregnancies.user_id must be nullable for a field record's case.
        DB::statement('CREATE TABLE __pregnancies_new (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, walk_in_patient_id INTEGER NULL, edd DATE NULL, ended_at TIMESTAMP NULL, lmp DATE NULL, is_high_risk INTEGER DEFAULT 0, aog INTEGER NULL, gravida INTEGER NULL, para INTEGER NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL)');
        DB::statement('INSERT INTO __pregnancies_new SELECT id, user_id, walk_in_patient_id, edd, ended_at, lmp, is_high_risk, aog, gravida, para, created_at, updated_at, deleted_at FROM pregnancies');
        DB::statement('DROP TABLE pregnancies');
        DB::statement('ALTER TABLE __pregnancies_new RENAME TO pregnancies');
    }

    public function test_activating_a_walk_in_hands_her_pregnancy_to_the_new_account(): void
    {
        $midwife = $this->patient(['role' => 'midwife']);
        $walkIn = WalkInPatient::create([
            'first_name' => 'Nena', 'last_name' => 'Fiel', 'contact_number' => '09170001122',
        ]);
        $pregnancy = Pregnancy::create([
            'user_id' => null, 'walk_in_patient_id' => $walkIn->id, 'edd' => today()->addDays(90),
        ]);

        $this->actingAs($midwife)
            ->post(route('midwife.walk-in-patients.store-activate', $walkIn->id), [
                'email' => 'nena.fiel@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'address' => 'Purok 4, San Carlos City',
                'barangay' => 'Poblb',
                'contact_number' => '09170001122',
            ])->assertRedirect();

        $account = User::where('email', 'nena.fiel@example.com')->firstOrFail();

        $this->assertSame($account->id, $walkIn->fresh()->linkedUserId());
        $this->assertTrue($walkIn->fresh()->has_portal_access);
        $this->assertSame($account->id, $pregnancy->fresh()->user_id);
        $this->assertNotNull($pregnancy->fresh()->woman, 'The case must now resolve to the registered woman.');
    }

    public function test_activation_does_not_repoint_a_pregnancy_already_owned_by_another_account(): void
    {
        $midwife = $this->patient(['role' => 'midwife']);
        $other = $this->patient();
        $walkIn = WalkInPatient::create(['first_name' => 'Liza', 'last_name' => 'Maco', 'contact_number' => '09170003344']);
        $pregnancy = Pregnancy::create([
            'user_id' => $other->id, 'walk_in_patient_id' => $walkIn->id, 'edd' => today()->addDays(120),
        ]);

        $walkIn->linkPortalAccount($this->patient());

        $this->assertSame($other->id, $pregnancy->fresh()->user_id);
    }

    public function test_backfill_migration_links_promoted_walk_ins_and_leaves_the_rest(): void
    {
        $account = $this->patient(['first_name' => 'Old', 'last_name' => 'Conversion']);
        $promoted = WalkInPatient::create(['first_name' => 'Old', 'last_name' => 'Conversion', 'contact_number' => '09170005566']);
        $stillField = WalkInPatient::create(['first_name' => 'Never', 'last_name' => 'Promoted', 'contact_number' => '09170007788']);

        // The shape left behind by the previous activation code: the profile is
        // linked, the pregnancy collected under it still is not.
        DB::table('walk_in_patients')->where('id', $promoted->id)->update(['user_id' => $account->id]);

        $orphan = Pregnancy::create(['user_id' => null, 'walk_in_patient_id' => $promoted->id, 'edd' => today()->addDays(30)]);
        $fieldCase = Pregnancy::create(['user_id' => null, 'walk_in_patient_id' => $stillField->id, 'edd' => today()->addDays(45)]);

        $migration = require database_path('migrations/2026_11_02_000001_backfill_pregnancy_user_id_for_linked_walk_ins.php');
        $migration->up();

        $this->assertSame($account->id, $orphan->fresh()->user_id);
        $this->assertNull($fieldCase->fresh()->user_id, 'A record with no account must stay a field record.');

        // Re-runnable: a second boot changes nothing.
        $before = DB::table('pregnancies')->orderBy('id')->pluck('user_id')->all();
        $migration->up();
        $this->assertSame($before, DB::table('pregnancies')->orderBy('id')->pluck('user_id')->all());
    }
}
