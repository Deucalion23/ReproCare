<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 1: Prepare for midwives/bhw merge into users
     * Adds legacy tracking columns and new FK to checkups
     */
    public function up(): void
    {
        // Step 1: Add legacy tracking columns to users (if not exists)
        // Portable across mysql / pgsql / sqlite (no SHOW COLUMNS).
        if (!Schema::hasColumn('users', 'legacy_midwife_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_midwife_id')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'legacy_bhw_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_bhw_id')->nullable();
            });
        }

        // Add indexes if not exists (portable: attempt, ignore if already exists)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('legacy_midwife_id', 'idx_users_legacy_midwife_id');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('legacy_bhw_id', 'idx_users_legacy_bhw_id');
            });
        } catch (\Throwable $e) {
        }

        // Step 2: Add soft delete support (if not exists)
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Step 3: Migrate midwives to users (if they don't exist)
        $this->migrateMidwivesToUsers();

        // Step 4: Migrate bhw to users (if they don't exist)
        $this->migrateBhwToUsers();

        // Step 5: Add new FK column to checkups (if not exists)
        if (Schema::hasTable('checkups') && !Schema::hasColumn('checkups', 'midwife_user_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                $table->unsignedBigInteger('midwife_user_id')->nullable();

                try {
                    $table->foreign('midwife_user_id', 'fk_checkups_midwife_user')
                        ->references('id')->on('users');
                } catch (\Throwable $e) {
                }
            });
            try {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->index('midwife_user_id', 'idx_checkups_midwife_user_id');
                });
            } catch (\Throwable $e) {
            }

            // Step 6: Populate new FK from migrated data (portable, only if legacy data exists)
            $this->populateMidwifeUserIds();
        }

        // Step 7: Create legacy views for backward compatibility
        $this->createMidwivesLegacyView();
        $this->createBhwLegacyView();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views
        try {
            DB::statement('DROP VIEW IF EXISTS bhw_legacy');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('DROP VIEW IF EXISTS midwives_legacy');
        } catch (\Throwable $e) {
        }

        // Remove new FK from checkups
        try {
            Schema::table('checkups', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_checkups_midwife_user');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('idx_checkups_midwife_user_id');
                } catch (\Throwable $e) {
                }
                if (Schema::hasColumn('checkups', 'midwife_user_id')) {
                    $table->dropColumn('midwife_user_id');
                }
            });
        } catch (\Throwable $e) {
        }

        // Remove legacy tracking from users
        try {
            Schema::table('users', function (Blueprint $table) {
                foreach (['idx_users_legacy_midwife_id', 'idx_users_legacy_bhw_id'] as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (\Throwable $e) {
                    }
                }
                $drop = [];
                foreach (['legacy_midwife_id', 'legacy_bhw_id'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $drop[] = $col;
                    }
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Migrate midwife records to users table (portable across mysql/pgsql/sqlite).
     */
    protected function migrateMidwivesToUsers(): void
    {
        if (!Schema::hasTable('midwives') || !Schema::hasTable('users')) {
            return;
        }

        try {
            $count = DB::table('midwives')->count();
        } catch (\Throwable $e) {
            return;
        }

        if ($count === 0) {
            return;
        }

        try {
            $midwives = DB::table('midwives')->get();
            foreach ($midwives as $m) {
                $exists = DB::table('users')->where('email', $m->email)->where('role', 'midwife')->exists();
                if ($exists) {
                    continue;
                }
                DB::table('users')->insert([
                    'name' => $m->name ?? $m->email,
                    'email' => $m->email,
                    'password' => '$2y$10$TEMP' . md5($m->email),
                    'role' => 'midwife',
                    'address' => $m->address ?? null,
                    'barangay' => $m->barangay ?? null,
                    'contact_number' => $m->contact ?? null,
                    'created_at' => $m->created_at ?? now(),
                    'updated_at' => $m->updated_at ?? now(),
                ]);
            }

            // Link midwives to their user records (portable loop, no UPDATE..JOIN)
            $links = DB::table('midwives')->select('id', 'email')->get();
            foreach ($links as $m) {
                DB::table('users')
                    ->where('email', $m->email)
                    ->where('role', 'midwife')
                    ->whereNull('legacy_midwife_id')
                    ->update(['legacy_midwife_id' => $m->id]);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Migrate bhw records to users table (portable).
     */
    protected function migrateBhwToUsers(): void
    {
        if (!Schema::hasTable('bhw') || !Schema::hasTable('users')) {
            return;
        }

        try {
            $count = DB::table('bhw')->count();
        } catch (\Throwable $e) {
            return;
        }

        if ($count === 0) {
            return;
        }

        try {
            $rows = DB::table('bhw')->get();
            foreach ($rows as $b) {
                $exists = DB::table('users')->where('email', $b->email)->where('role', 'bhw')->exists();
                if ($exists) {
                    continue;
                }
                DB::table('users')->insert([
                    'name' => $b->name ?? $b->email,
                    'email' => $b->email,
                    'password' => '$2y$10$TEMP' . md5($b->email),
                    'role' => 'bhw',
                    'address' => $b->address ?? null,
                    'barangay' => $b->barangay ?? null,
                    'contact_number' => $b->contact ?? null,
                    'created_at' => $b->created_at ?? now(),
                    'updated_at' => $b->updated_at ?? now(),
                ]);
            }

            $links = DB::table('bhw')->select('id', 'email')->get();
            foreach ($links as $b) {
                DB::table('users')
                    ->where('email', $b->email)
                    ->where('role', 'bhw')
                    ->whereNull('legacy_bhw_id')
                    ->update(['legacy_bhw_id' => $b->id]);
            }
        } catch (\Throwable $e) {
        }
    }

    protected function populateMidwifeUserIds(): void
    {
        try {
            if (!Schema::hasTable('checkups') || !Schema::hasTable('midwives') || !Schema::hasTable('users')) {
                return;
            }
            if (!Schema::hasColumn('checkups', 'midwife_id') || !Schema::hasColumn('checkups', 'midwife_user_id')) {
                return;
            }
            $checkups = DB::table('checkups')->whereNull('midwife_user_id')->whereNotNull('midwife_id')->get();
            foreach ($checkups as $c) {
                $midwife = DB::table('midwives')->where('id', $c->midwife_id)->first();
                if (!$midwife) {
                    continue;
                }
                $user = DB::table('users')->where('legacy_midwife_id', $midwife->id)->first();
                if ($user) {
                    DB::table('checkups')->where('id', $c->id)->update(['midwife_user_id' => $user->id]);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    protected function createView(string $name, string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            // sqlite has no CREATE OR REPLACE VIEW — retry as DROP + CREATE
            try {
                DB::statement("DROP VIEW IF EXISTS {$name}");
                $plain = preg_replace('/CREATE\s+OR\s+REPLACE\s+VIEW/i', 'CREATE VIEW', $sql);
                DB::statement($plain);
            } catch (\Throwable $e2) {
            }
        }
    }

    /**
     * Create legacy view for midwives
     */
    protected function createMidwivesLegacyView(): void
    {
        $this->createView('midwives_legacy', "
            CREATE OR REPLACE VIEW midwives_legacy AS
            SELECT
                u.legacy_midwife_id AS id,
                u.name,
                u.email,
                u.contact_number AS contact,
                u.address,
                u.barangay,
                u.created_at,
                u.updated_at
            FROM users u
            WHERE u.role = 'midwife' AND u.legacy_midwife_id IS NOT NULL
        ");
    }

    /**
     * Create legacy view for bhw
     */
    protected function createBhwLegacyView(): void
    {
        $this->createView('bhw_legacy', "
            CREATE OR REPLACE VIEW bhw_legacy AS
            SELECT
                u.legacy_bhw_id AS id,
                u.name,
                u.email,
                u.contact_number AS contact,
                u.address,
                u.barangay,
                u.created_at,
                u.updated_at
            FROM users u
            WHERE u.role = 'bhw' AND u.legacy_bhw_id IS NOT NULL
        ");
    }
};
