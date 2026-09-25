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

        return response()->json([
            'status' => $user->status ?? 'approved',
            'active' => ($user->status ?? 'approved') === 'approved',
            'pwa_cache_version' => (int) ($user->pwa_cache_version ?? 1),
        ]);
    }
}
