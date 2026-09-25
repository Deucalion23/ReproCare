<?php

namespace App\Http\Middleware;

use App\Models\HealthRecord;
use App\Models\SyncLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Offline-sync safety net for PWA outbox replays (BHW field endpoints).
 *
 * The outbox tags every queued entry with sync_uuid + client_timestamp.
 *  - Same uuid twice   → duplicate replay of an accepted entry: acknowledge
 *    WITHOUT creating a second row (idempotent), as JSON or redirect.
 *  - Validation failed → release the claim so a corrected retry is accepted.
 *  - Success (JSON)    → structured payload incl. an upstream warning when
 *    newer clinical entries exist for the same patient (race signal).
 *
 * Plain online form posts carry no sync_uuid and pass through untouched.
 */
class HandleSyncRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid = trim((string) $request->input('sync_uuid', ''));
        if ($uuid === '') {
            return $next($request);
        }

        $claimed = SyncLog::claim(
            $uuid,
            $request->path(),
            auth()->id(),
            $request->input('client_timestamp')
        );

        if (!$claimed) {
            return $this->dedupeReply($request);
        }

        /** @var \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse $response */
        $response = $next($request);

        if ($this->isFailure($request, $response)) {
            // Validation / error: release the claim so a fixed retry works.
            SyncLog::where('sync_uuid', $uuid)->delete();

            return $response;
        }

        if ($this->wantsSyncJson($request)) {
            return response()->json(array_filter([
                'status' => 'synced',
                'upstream_warning' => $this->upstreamWarning($request),
            ]));
        }

        return $response;
    }

    private function dedupeReply(Request $request)
    {
        if ($this->wantsSyncJson($request)) {
            return response()->json([
                'status' => 'deduped',
                'message' => 'Already synced — duplicate replay ignored.',
            ]);
        }

        return redirect()->back()->with('success', 'Already synced — duplicate replay ignored.');
    }

    private function wantsSyncJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function isFailure(Request $request, $response): bool
    {
        $status = $response->getStatusCode();

        if ($status === 422) {
            return true;
        }

        if ($status >= 400) {
            return true;
        }

        // Redirects back with validation errors are failures too.
        // NOTE: an (empty) error bag is always shared, so only a NON-EMPTY
        // bag means failure — successful saves redirect with flashes only.
        if ($status >= 300 && $status < 400) {
            try {
                $errors = session()->get('errors');
                if ($errors && method_exists($errors, 'any') && $errors->any()) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
        }

        return false;
    }

    /**
     * Race signal: clinical entries for the same patient that are NEWER
     * than the offline entry mean someone (e.g. a midwife) updated the
     * patient while the BHW was offline.
     */
    private function upstreamWarning(Request $request): ?string
    {
        try {
            $since = $request->input('client_timestamp');
            if (!$since) {
                return null;
            }
            $since = \Carbon\Carbon::parse($since);

            $userId = $request->input('user_id');
            if ($userId) {
                $newer = HealthRecord::where('user_id', $userId)
                    ->where('created_at', '>', $since)
                    ->orderByDesc('created_at')
                    ->first();
                if ($newer) {
                    return "Newer clinical entries exist for this patient (latest {$newer->created_at->format('M j, g:i A')}, {$newer->risk_level} risk). Please review before relying on offline data.";
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }
}
