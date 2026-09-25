<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\User;
use App\Models\Notification;
use App\Models\Checkup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    // ── Bilingual Message Templates ────────────────────────────────────────────

    private array $templates = [
        'appointment_reminder' => [
            'en' => "📅 REPROCARE REMINDER: Hi {name}, you have a checkup scheduled on {date} at {time}. Please do not miss it. Contact your health worker for details.",
            'tl' => "📅 REPROCARE PAALALA: Kumusta {name}, mayroon kang checkup sa {date} ng {time}. Huwag kalimutang dumalo. Makipag-ugnayan sa iyong health worker para sa detalye.",
        ],
        'high_risk_alert' => [
            'en' => "🚨 REPROCARE HEALTH ALERT: Hi {name}, our system has detected a health concern: {reason}. Please contact your health worker immediately or visit the nearest health facility.",
            'tl' => "🚨 REPROCARE BABALA SA KALUSUGAN: Kumusta {name}, natukoy ng aming sistema ang isang alalahanin sa kalusugan: {reason}. Makipag-ugnayan kaagad sa iyong health worker o pumunta sa pinakamalapit na health facility.",
        ],
        'missed_checkup' => [
            'en' => "⛔ REPROCARE NOTICE: Hi {name}, you missed your scheduled checkup on {date}. Please reschedule as soon as possible. Your health matters!",
            'tl' => "⛔ REPROCARE ABISO: Kumusta {name}, napalampas mo ang iyong nakatalagang checkup noong {date}. Mangyaring mag-iskedyul muli sa lalong madaling panahon. Mahalaga ang iyong kalusugan!",
        ],
        'custom' => [
            'en' => "{message}",
            'tl' => "{message_tl}",
        ],
        'broadcast' => [
            'en' => "📢 REPROCARE ANNOUNCEMENT: {message}",
            'tl' => "📢 REPROCARE ANUNSYO: {message}",
        ],
    ];

    // ── Public Interface ────────────────────────────────────────────────────────

    /**
     * Send a templated SMS to a registered User.
     * Checks opt-out and contact number before sending.
     *
     * Automated alert types (appointment_reminder, high_risk_alert,
     * missed_checkup) are capped at ONE send per patient per day, since
     * risk re-evaluation runs on every dashboard visit. Manual types
     * (custom, broadcast) are always sent on explicit user action.
     */
    public function send(User $user, string $type, array $params = []): bool
    {
        if (!$user->hasSmsEnabled()) {
            Log::info("SmsService: SMS skipped for user {$user->id} — opt-out or no contact number.");
            return false;
        }

        if ($this->isAutomated($type)) {
            if ($user->role !== 'user' || $user->status !== 'approved') {
                return false;
            }
            return (bool) Cache::lock("sms:{$user->id}:{$type}:" . today()->toDateString(), 60)->get(function () use ($user, $type, $params) {
                $category = $type === 'high_risk_alert' ? 'risk' : $type;
                $alert = Notification::withTrashed()->where('user_id', $user->id)->where('category', $category)
                    ->when(isset($params['notification_id']), fn ($q) => $q->whereKey($params['notification_id']))
                    ->latest('id')->first();
                // Fail closed: an automated SMS must have a live, unacknowledged source.
                if (!$alert || $alert->is_read || $alert->read_at || $alert->resolved_at || $alert->trashed()) {
                    return false;
                }
                if ($type === 'high_risk_alert') {
                    $latest = Notification::withTrashed()->where('user_id', $user->id)->where('category', 'risk')->latest('id')->first();
                    if ($latest?->id !== $alert->id || $user->healthRecords()->latest('id')->first()?->risk_level === 'Low') {
                        return false;
                    }
                } else {
                    $checkup = Checkup::find($alert->checkup_id);
                    if (!$checkup || $checkup->status !== ($type === 'missed_checkup' ? 'Missed' : 'Scheduled')) {
                        return false;
                    }
                    $key = "{$type}:{$checkup->id}:" . $checkup->scheduled_date->toDateString() . ':' . ($checkup->scheduled_time ?? '');
                    if ($key !== $alert->event_key || ($type === 'appointment_reminder' && !$checkup->scheduled_date->isTomorrow())) {
                        return false;
                    }
                }
                // Include pending/failed attempts: a timeout may already have reached the gateway.
                if (SmsLog::where('user_id', $user->id)->where('type', $type)->whereDate('created_at', today())->exists()) {
                    return false;
                }
                $message = $this->buildBilingualMessage($type, $params, $user->first_name ?? '');
                $message .= "\nRead/acknowledge in ReproCare to stop reminders: " . url('/user/notifications');
                return $this->dispatch($user->contact_number, $message, $type, $user->id, $alert->id);
            });
        }

        $message = $this->buildBilingualMessage($type, $params, $user->first_name);
        return $this->dispatch($user->contact_number, $message, $type, $user->id);
    }

    /**
     * Send a custom bilingual SMS to a registered User.
     * $en = English message, $tl = Tagalog message
     */
    public function sendCustom(User $user, string $en, string $tl = ''): bool
    {
        if (!$user->hasSmsEnabled()) {
            return false;
        }

        $message = $en;
        if (!empty($tl)) {
            $message .= "\n\n" . $tl;
        }

        return $this->dispatch($user->contact_number, $message, 'custom', $user->id);
    }

    /**
     * Send a templated SMS to a walk-in patient via their contact number.
     * Walk-ins have no portal account, so there is no in-app acknowledgement;
     * automated types are still capped at one send per phone number per day.
     */
    public function sendToWalkIn(\App\Models\WalkInPatient $walkIn, string $type, array $params = []): bool
    {
        if (!$walkIn->hasSmsEnabled()) {
            Log::info("SmsService: SMS skipped for walk-in {$walkIn->id} — no contact number.");
            return false;
        }
        $message = $this->buildBilingualMessage($type, $params, $walkIn->first_name ?? '');
        return $this->sendToPhone($walkIn->contact_number, $message, $type);
    }

    /**
     * Send an SMS directly to a phone number (no user association required).
     */
    public function sendToPhone(string $phone, string $message, string $type = 'custom'): bool
    {
        if ($this->isAutomated($type)) {
            $phone = $this->normalizePhilippinesPhone($phone);
            return (bool) Cache::lock('sms:phone:' . hash('sha256', $phone . $type . today()->toDateString()), 60)->get(function () use ($phone, $message, $type) {
                if (SmsLog::where('phone_number', $phone)->where('type', $type)->whereDate('created_at', today())->exists()) {
                    return false;
                }
                return $this->dispatch($phone, $message, $type, null);
            });
        }
        return $this->dispatch($phone, $message, $type, null);
    }

    private function isAutomated(string $type): bool
    {
        return in_array($type, ['appointment_reminder', 'high_risk_alert', 'missed_checkup'], true);
    }

    /**
     * Send a broadcast SMS to all patients in a given purok or barangay.
     * Rate-limited to 500 per call.
     */
    public function broadcast(string $enMessage, string $tlMessage = '', ?int $purokId = null, ?string $barangay = null): int
    {
        $query = User::where('role', 'user')
            ->where('status', 'approved')
            ->where('sms_opt_out', false)
            ->whereNotNull('contact_number')
            ->where('contact_number', '!=', '');

        if ($purokId) {
            $query->where('purok_id', $purokId);
        }

        if ($barangay) {
            $query->where('barangay', $barangay);
        }

        $users = $query->limit(500)->get();
        $sent = 0;

        foreach ($users as $user) {
            $message = $enMessage;
            if (!empty($tlMessage)) {
                $message .= "\n\n" . $tlMessage;
            }

            if ($this->dispatch($user->contact_number, $message, 'broadcast', $user->id)) {
                $sent++;
            }
        }

        return $sent;
    }

    // ── Internal ────────────────────────────────────────────────────────────────

    /**
     * Build a bilingual message string from a template type + params.
     */
    private function buildBilingualMessage(string $type, array $params, string $firstName = ''): string
    {
        $template = $this->templates[$type] ?? $this->templates['custom'];

        $defaults = [
            'name'       => $firstName,
            'date'       => $params['date'] ?? 'N/A',
            'time'       => $params['time'] ?? '',
            'reason'     => $params['reason'] ?? 'a health concern was detected',
            'message'    => $params['message'] ?? '',
            'message_tl' => $params['message_tl'] ?? $params['message'] ?? '',
        ];

        $enMessage = $this->interpolate($template['en'], $defaults);
        $tlMessage = $this->interpolate($template['tl'], $defaults);

        // If both are the same (custom with no TL), just return one
        if ($enMessage === $tlMessage || empty($tlMessage)) {
            return $enMessage;
        }

        return $enMessage . "\n\n" . $tlMessage;
    }

    /**
     * Replace {placeholders} in a template string.
     */
    private function interpolate(string $template, array $params): string
    {
        foreach ($params as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Dispatch the actual SMS via the configured provider (TextBee or Movider) and log the result.
     *
     * Switch via SMS_PROVIDER env: 'textbee' or 'movider'
     */
    private function dispatch(string $phone, string $message, string $type, ?int $userId, ?int $notificationId = null): bool
    {
        $normalizedPhone = $this->normalizePhilippinesPhone($phone);

        // Build log record first as pending
        $log = SmsLog::create([
            'user_id'      => $userId,
            'phone_number' => $phone,
            'message'      => $message,
            'type'         => $type,
            'status'       => 'pending',
            'notification_id' => $notificationId,
        ]);

        // DB settings (CHO → Settings → SMS Gateway) override config/services.php when filled.
        $provider = \App\Models\Setting::get('sms.provider', config('services.sms_provider', 'movider')) ?: 'movider';

        // ── MOCK MODE ──────────────────────────────────────────────────
        $mock = \App\Models\Setting::get('sms.mock', null);
        $mockMode = filter_var($mock !== null && $mock !== '' ? $mock : config('services.movider.mock'), FILTER_VALIDATE_BOOLEAN);
        if ($mockMode) {
            $log->update([
                'status'       => 'sent',
                'provider_sid' => 'MOCK_' . Str::random(10),
                'sent_at'      => now(),
            ]);
            Log::info("SmsService: [MOCK] SMS sent to {$phone}. Type: {$type}.");
            return true;
        }

        // ── ROUTE TO PROVIDER ──────────────────────────────────────────
        if ($provider === 'textbee') {
            return $this->dispatchViaTextBee($log, $phone, $normalizedPhone, $message, $type);
        }

        return $this->dispatchViaMovider($log, $phone, $normalizedPhone, $message, $type);
    }

    /**
     * Dispatch SMS via TextBee (Android phone gateway).
     * API: POST https://api.textbee.dev/api/v1/gateway/devices/{deviceId}/sendSMS
     */
    private function dispatchViaTextBee($log, string $phone, string $normalizedPhone, string $message, string $type): bool
    {
        $apiKey   = \App\Models\Setting::get('sms.textbee_api_key', '') ?: config('services.textbee.api_key');
        $deviceId = \App\Models\Setting::get('sms.textbee_device_id', '') ?: config('services.textbee.device_id');
        $apiUrl   = config('services.textbee.api_url', 'https://api.textbee.dev/api/v1/gateway/devices');

        if (empty($apiKey) || empty($deviceId)) {
            Log::warning("SmsService: TextBee not configured. SMS not sent to {$phone}.");
            $log->update(['status' => 'failed', 'error_message' => 'TextBee API key or device ID not configured.']);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'    => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$apiUrl}/{$deviceId}/sendSMS", [
                'recipients' => ['+' . ltrim($normalizedPhone, '+')],
                'message'    => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['data']['success'])) {
                $batchId = $data['data']['smsBatchId'] ?? ('TEXTBEE_' . Str::random(10));
                $log->update([
                    'status'       => 'sent',
                    'provider_sid' => $batchId,
                    'sent_at'      => now(),
                ]);
                Log::info("SmsService: SMS sent via TextBee to {$phone}. Type: {$type}. Batch: {$batchId}");
                return true;
            }

            $errorMsg = $data['message'] ?? $data['error'] ?? "HTTP {$response->status()}: " . substr($response->body(), 0, 300);
            $log->update(['status' => 'failed', 'error_message' => $errorMsg]);
            Log::error("SmsService: TextBee error for {$phone}. Response: " . $response->body());
            return false;

        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error("SmsService: TextBee exception for {$phone}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dispatch SMS via Movider cloud gateway.
     * API Endpoint: POST https://api.movider.co/v1/sms
     */
    private function dispatchViaMovider($log, string $phone, string $normalizedPhone, string $message, string $type): bool
    {
        $apiKey    = config('services.movider.api_key');
        $apiSecret = config('services.movider.api_secret');
        $apiUrl    = config('services.movider.api_url', 'https://api.movider.co/v1/sms');

        if (empty($apiKey)) {
            Log::warning("SmsService: Movider not configured. SMS not sent to {$phone}. Message: {$message}");
            $log->update(['status' => 'failed', 'error_message' => 'Movider API key not configured.']);
            return false;
        }

        $endpoint = $this->formatMoviderEndpoint($apiUrl);

        try {
            $payload = [
                'api_key' => $apiKey,
                'to'      => $normalizedPhone,
                'text'    => $message,
            ];

            if (!empty($apiSecret)) {
                $payload['api_secret'] = $apiSecret;
            }

            $response = Http::asForm()
                ->timeout(30)
                ->acceptJson()
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $data = $response->json();

            if ($response->successful() && !empty($data['phone_number_list'])) {
                $item = $data['phone_number_list'][0];
                $providerSid = $item['message_id'] ?? ('MOVIDER_' . Str::random(10));

                $log->update([
                    'status'       => 'sent',
                    'provider_sid' => $providerSid,
                    'sent_at'      => now(),
                ]);

                Log::info("SmsService: SMS sent via Movider to {$phone}. Type: {$type}. Provider SID: {$providerSid}");
                return true;
            }

            $errorMsg = $data['error']['description']
                ?? $data['error']['name']
                ?? (isset($data['bad_phone_number_list']) && !empty($data['bad_phone_number_list']) ? 'Failed to deliver to number: ' . json_encode($data['bad_phone_number_list']) : null)
                ?? ($data['message'] ?? null)
                ?? "HTTP {$statusCode}: " . substr($response->body(), 0, 500);

            $log->update([
                'status'        => 'failed',
                'error_message' => $errorMsg,
            ]);

            Log::error("SmsService: Movider API error for {$phone}. HTTP {$statusCode}. Response: " . $response->body());
            return false;

        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("SmsService: Exception sending SMS to {$phone}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build the Movider SMS API endpoint URL.
     */
    private function formatMoviderEndpoint(string $apiUrl): string
    {
        $url = trim($apiUrl);
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }
        $url = rtrim($url, '/');
        if (str_contains($url, 'console.movider.co')) {
            return 'https://api.movider.co/v1/sms';
        }
        if (!str_contains($url, '/v1/sms')) {
            $url .= '/v1/sms';
        }
        return $url;
    }

    /**
     * Normalize a Philippine phone number to international MSISDN format (e.g. 639384548234).
     */
    public function normalizePhilippinesPhone(string $phone): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '63' . substr($digits, 1);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return '63' . $digits;
        }

        // Return digits if available, otherwise original string
        return $digits ?: $phone;
    }
}
