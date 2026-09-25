<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Terminates web sessions of a given user (database session driver).
 * Used by role handovers and staff replacements so a deactivated
 * account loses access on every device immediately.
 */
class SessionRevocationService
{
    public static function terminateUserSessions(int $userId): int
    {
        $deleted = 0;
        try {
            $rows = DB::table('sessions')->select('id', 'payload')->get();
        } catch (\Throwable $e) {
            return 0;
        }
        foreach ($rows as $row) {
            if (self::sessionBelongsTo($row->payload ?? '', $userId)) {
                DB::table('sessions')->where('id', $row->id)->delete();
                $deleted++;
            }
        }
        return $deleted;
    }

    public static function sessionBelongsTo(string $payload, int $userId): bool
    {
        if ($payload === '') {
            return false;
        }
        $candidates = [$payload];
        $decoded = base64_decode($payload, true);
        if ($decoded !== false && $decoded !== $payload) {
            $candidates[] = $decoded;
        }
        foreach ($candidates as $candidate) {
            try {
                $data = unserialize($candidate, ['allowed_classes' => false]);
            } catch (\Throwable $e) {
                continue;
            }
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $key => $value) {
                if (is_string($key) && str_starts_with($key, 'login_web_') && (int) $value === $userId) {
                    return true;
                }
            }
        }
        return false;
    }
}
