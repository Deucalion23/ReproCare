<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Enforces the ReproCare staffing invariant:
 * ONE active BHW President per ONE active Barangay.
 *
 * Barangay lookups are normalized before comparison so that
 * "Burgos", "Barangay Burgos" and "Barangay Burgos Padlan,
 * San Carlos City, Pangasinan" resolve to the same jurisdiction.
 */
class BhwPresidentAssignmentService
{
    /**
     * Normalize a barangay lookup into a comparable key.
     */
    public static function normalizeBarangay(?string $barangay): string
    {
        if (!$barangay) {
            return '';
        }

        $key = strtolower(trim($barangay));
        // Strip common prefixes: "barangay", "brgy.", "brgy", "brg."
        $key = preg_replace('/^(barangay|brgy\.?|brg\.?)\s+/i', '', $key);
        // Strip city/province suffixes after the first comma.
        $key = trim(explode(',', $key)[0]);
        // Collapse whitespace/punctuation.
        $key = trim(preg_replace('/[^a-z0-9 ]+/', ' ', $key));
        $key = preg_replace('/\s+/', ' ', $key);

        return $key;
    }

    /**
     * Display form of a barangay lookup: "Barangay Burgos".
     */
    public static function displayBarangay(string $barangay): string
    {
        $key = self::normalizeBarangay($barangay);
        if ($key === '') {
            return $barangay;
        }

        return 'Barangay ' . ucwords($key);
    }

    /**
     * Find the active BHW President holding the given barangay,
     * optionally ignoring one user (the record being updated).
     */
    public static function findConflict(string $barangay, ?int $ignoreUserId = null): ?User
    {
        $key = self::normalizeBarangay($barangay);
        if ($key === '') {
            return null;
        }

        $candidates = User::where('role', 'bhw_president')
            ->where('status', 'approved')
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'barangay']);

        foreach ($candidates as $candidate) {
            if (self::jurisdictionsOverlap($key, self::normalizeBarangay($candidate->barangay))) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Two normalized barangay keys overlap when they are equal or when
     * one jurisdiction name contains the other (e.g. "burgos" vs
     * "burgos padlan").
     */
    protected static function jurisdictionsOverlap(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }

        return $a === $b || str_contains($a, $b) || str_contains($b, $a);
    }

    /**
     * Constrain a query builder to rows whose barangay falls in the given
     * jurisdiction. Same normalize-and-overlap rule as jurisdictionsOverlap,
     * expressed as a portable LIKE so it works in SQL on mysql/pgsql/sqlite:
     * legacy spellings ('Burgos', 'Barangay Burgos Padlan, San Carlos City,
     * Pangasinan', 'Brgy. Burgos St') all match. Empty jurisdiction (or a
     * key with no safe characters) applies no constraint.
     *
     * Note: matching runs president-key direction (data containing the key),
     * which covers real registries where the president holds the short core
     * name. Exact bidirectional checks remain PHP-side (settings page).
     */
    public static function applyJurisdictionFilter($query, ?string $barangay, string $column = 'barangay'): void
    {
        $key = self::normalizeBarangay($barangay);
        if ($key === '' || !preg_match('/^[a-z0-9 ]+$/', $key)) {
            return;
        }
        $like = '%' . str_replace(' ', '%', $key) . '%';
        $query->whereRaw("LOWER({$column}) LIKE ?", [$like]);
    }

    /**
     * Throw a styled validation error when the barangay is taken.
     *
     * @throws ValidationException
     */
    public static function assertAvailable(string $barangay, ?int $ignoreUserId = null): void
    {
        $holder = self::findConflict($barangay, $ignoreUserId);

        if ($holder) {
            throw ValidationException::withMessages([
                'barangay' => 'VALIDATION ERROR: Barangay ' . ucwords(self::normalizeBarangay($barangay))
                    . ' already has an active BHW President assigned (' . $holder->name . ').'
                    . ' Assignment BLOCKED. System enforces ONE Active President per ONE Active Barangay.'
                    . ' Resolve conflict externally.',
            ]);
        }
    }

    /**
     * Promote an existing staff account to BHW President of a barangay.
     *
     * @throws ValidationException when the barangay is already held.
     */
    public static function promoteToPresident(User $user, string $barangay): User
    {
        self::assertAvailable($barangay, $user->id);

        $user->update([
            'role' => 'bhw_president',
            'barangay' => self::displayBarangay($barangay),
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return $user->fresh();
    }
}
