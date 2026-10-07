<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Patient;
use App\Http\Resources\PatientResource;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json([
            'settings' => [
                'facility_name' => Setting::getVal('facility_name', 'NIS Medical Clinic HQ'),
                'active_branch' => Setting::getVal('active_branch', 'Central command, Abuja'),
                'maintenance_mode' => Setting::getVal('maintenance_mode', '0'),
                'external_api_token' => Setting::getVal('external_api_token', ''),
                'webhook_url' => Setting::getVal('webhook_url', ''),
                'webhook_secret' => Setting::getVal('webhook_secret', ''),
                'webhook_events' => Setting::getVal('webhook_events', 'patient.registered'),
                'integration_fetch_url' => Setting::getVal('integration_fetch_url', ''),
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'active_branch' => 'required|string|max:255',
            'maintenance_mode' => 'required|string|in:0,1',
            'external_api_token' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:255',
            'webhook_secret' => 'nullable|string|max:255',
            'webhook_events' => 'nullable|string|max:255',
            'integration_fetch_url' => 'nullable|url|max:255',
        ]);

        Setting::setVal('facility_name', $validated['facility_name']);
        Setting::setVal('active_branch', $validated['active_branch']);
        Setting::setVal('maintenance_mode', $validated['maintenance_mode']);
        Setting::setVal('external_api_token', $validated['external_api_token'] ?? '');
        Setting::setVal('webhook_url', $validated['webhook_url'] ?? '');
        Setting::setVal('webhook_secret', $validated['webhook_secret'] ?? '');
        Setting::setVal('webhook_events', $validated['webhook_events'] ?? '');
        Setting::setVal('integration_fetch_url', $validated['integration_fetch_url'] ?? '');

        return response()->json([
            'message' => 'System settings and REST API hooks updated successfully.',
            'settings' => $validated
        ]);
    }

    /**
     * INCOMING API HOOK: Allow other systems to fetch patient data from this app in real-time.
     */
    public function syncPatients(Request $request)
    {
        $token = $request->bearerToken() ?: $request->query('api_key');
        $storedToken = Setting::getVal('external_api_token');

        if (empty($storedToken) || !is_string($token) || !hash_equals($storedToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing External API Sync Token.'
            ], 401);
        }

        $patients = Patient::limit(50)->get();

        return response()->json([
            'success' => true,
            'source_facility' => Setting::getVal('facility_name', 'NIS Medical Clinic HQ'),
            'branch' => Setting::getVal('active_branch', 'Central command, Abuja'),
            'timestamp' => now()->toIso8601String(),
            'count' => $patients->count(),
            'patients' => PatientResource::collection($patients)
        ]);
    }

    /**
     * OUTGOING API HOOK: Fetch real-time data from an external platform configured url.
     */
    public function fetchExternalData()
    {
        $url = Setting::getVal('integration_fetch_url');

        if (empty($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Error: External Sync Source URL has not been defined in settings.'
            ], 400);
        }

        if (!$this->isPublicHttpUrl($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Blocked: The configured URL must be a public http(s) endpoint. Internal, loopback and metadata addresses are not permitted.'
            ], 422);
        }

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Real-time data retrieved successfully from external source.',
                    'status_code' => $response->status(),
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'External platform responded with error status: ' . $response->status(),
                'status_code' => $response->status()
            ], 502);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection Failed: Could not fetch from external source. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TEST WEBHOOK TRIGGER: Simulate sending a real-time event webhook payload to external url.
     */
    public function triggerWebhook(Request $request)
    {
        $url = Setting::getVal('webhook_url');
        $secret = Setting::getVal('webhook_secret', 'secret');

        if (empty($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Error: Webhook Target URL has not been defined in settings.'
            ], 400);
        }

        if (!$this->isPublicHttpUrl($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Blocked: The webhook target must be a public http(s) endpoint. Internal, loopback and metadata addresses are not permitted.'
            ], 422);
        }

        $payload = [
            'event' => Setting::getVal('webhook_events', 'patient.registered'),
            'timestamp' => now()->toIso8601String(),
            'facility' => Setting::getVal('facility_name', 'NIS Medical Clinic HQ'),
            'data' => [
                'hospital_code' => 'NIS/PAT/' . rand(10000, 99999),
                'full_name' => 'Adewale Babajide',
                'gender' => 'Male',
                'vitals' => [
                    'systolic' => 120,
                    'diastolic' => 80,
                    'temperature' => 36.8
                ]
            ]
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-NIS-Signature' => $signature,
                    'Content-Type' => 'application/json'
                ])
                ->post($url, $payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook event dispatched successfully.',
                'webhook_url' => $url,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook delivery failed. Connection Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * SSRF guard: only allow outbound calls to public http(s) endpoints.
     * Rejects non-http schemes, and hostnames that resolve to loopback,
     * private, link-local, or cloud-metadata address ranges.
     */
    protected function isPublicHttpUrl(string $url): bool
    {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];

        // Resolve the host to its IP addresses (covers hostnames and literal IPs).
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            $records = @dns_get_record($host, DNS_A + DNS_AAAA);
            foreach ($records ?: [] as $record) {
                if (!empty($record['ip'])) {
                    $ips[] = $record['ip'];
                } elseif (!empty($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
            // Fallback for environments where dns_get_record is limited.
            if (empty($ips)) {
                $resolved = gethostbyname($host);
                if ($resolved && $resolved !== $host) {
                    $ips[] = $resolved;
                }
            }
        }

        if (empty($ips)) {
            return false;
        }

        foreach ($ips as $ip) {
            // Reject anything that is not a global, routable, public address.
            if (!filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            )) {
                return false;
            }
        }

        return true;
    }
}
