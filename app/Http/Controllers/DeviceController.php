<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Lets field devices verify that the signed-in account is still active
 * and that the local offline cache version is current. The PWA calls
 * this on reconnect; a revoked version forces local data wipe.
 */
class DeviceController extends Controller
{
    public function sessionCheck(Request $request)
    {
        $user = $request->user()->fresh();
        $status = $user->status ?? 'approved';

        // Pending patient sessions are legitimately alive (provisional or
        // view-only portal access) — reporting them inactive would make the
        // PWA wipe local data and bounce the user to login. Anything the
        // account.active middleware would kill stays inactive.
        $active = $status === 'approved'
            || ($status === 'pending' && ($user->role ?? null) === 'user');

        return response()->json([
            'status' => $status,
            'active' => $active,
            'pwa_cache_version' => (int) ($user->pwa_cache_version ?? 1),
        ]);
    }
}
