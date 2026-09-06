<?php

namespace App\Services;

use App\Models\AppointmentRequest;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Public-facing landing-page assistant ("MediBot").
 *
 * - Answers questions about the hospital using LIVE application data
 *   (departments, services, facility info) so it is always "trained" on the app.
 * - Books outpatient appointments through a guided slot-filling flow, optionally
 *   using a patient's Hospital Number to identify a returning patient.
 * - Uses Anthropic Claude for free-form Q&A when configured, and falls back to a
 *   built-in knowledge base so it keeps working offline.
 */
class ChatbotService
{
    /**
     * Main entry point.
     *
     * @param  string  $message  The visitor's message.
     * @param  array   $state    Conversation/booking state echoed back by the client.
     * @return array{reply:string, state:array, booked:bool, source:string}
     */
    public function handle(string $message, array $state = []): array
    {
        $text = trim($message);
        $lower = strtolower($text);

        // Continue an in-progress booking flow.
        if (($state['flow'] ?? null) === 'booking') {
            return $this->continueBooking($text, $state);
        }

        // Detect a new booking intent.
        if ($this->isBookingIntent($lower)) {
            return $this->startBooking($state);
        }

        // Otherwise answer the question.
        return [
            'reply' => $this->answerQuestion($text),
            'state' => $state,
            'booked' => false,
            'source' => empty(config('services.anthropic.api_key')) ? 'offline' : 'live',
        ];
    }

    protected function isBookingIntent(string $lower): bool
    {
        foreach (['book', 'appointment', 'schedule', 'see a doctor', 'consult', 'reserve', 'booking'] as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }
        return false;
    }

    // ─────────────────────────── Booking flow ───────────────────────────

    protected function startBooking(array $state): array
    {
        $state = [
            'flow' => 'booking',
            'step' => 'hospital_number',
            'data' => [],
        ];

        return [
            'reply' => "I can help you book an outpatient appointment. \n\n"
                . "If you already have a **Hospital Number** (e.g. NIS/PAT/000123), please type it now so I can pull up your file. "
                . "If you are a new patient, just type **new**.",
            'state' => $state,
            'booked' => false,
            'source' => 'booking',
        ];
    }

    protected function continueBooking(string $text, array $state): array
    {
        $step = $state['step'] ?? 'hospital_number';
        $data = $state['data'] ?? [];
        $lower = strtolower(trim($text));

        // Allow cancelling at any point.
        if (in_array($lower, ['cancel', 'stop', 'quit', 'exit'], true)) {
            return [
                'reply' => "No problem, I have cancelled the booking. Is there anything else I can help you with?",
                'state' => ['flow' => null],
                'booked' => false,
                'source' => 'booking',
            ];
        }

        switch ($step) {
            case 'hospital_number':
                if (! in_array($lower, ['new', 'no', 'none', 'skip'], true)) {
                    $code = strtoupper(trim($text));
                    $patient = Patient::where('immigration_service_number', $code)->first();
                    if (! $patient) {
                        return $this->ask($state, 'hospital_number',
                            "I could not find a patient with Hospital Number **{$code}**. "
                            . "Please re-check and type it again, or type **new** to continue as a new patient.");
                    }
                    $data['immigration_service_number'] = $patient->immigration_service_number;
                    $data['first_name'] = $patient->first_name;
                    $data['last_name'] = $patient->last_name;
                    $data['phone'] = $patient->phone;
                    $data['email'] = $patient->email;
                    $state['data'] = $data;
                    // Skip straight to date since we know the patient.
                    return $this->ask($state, 'date',
                        "Welcome back, **{$patient->first_name} {$patient->last_name}**! "
                        . "What date would you like to come in? (please use YYYY-MM-DD, e.g. " . now()->addDay()->format('Y-m-d') . ")");
                }
                $state['data'] = $data;
                return $this->ask($state, 'first_name', "Let's get you set up as a new patient. What is your **first name**?");

            case 'first_name':
                if (! preg_match("/^[A-Za-z][A-Za-z\s'.\-]{1,59}$/", $text)) {
                    return $this->ask($state, 'first_name', "Please enter a valid first name (letters only).");
                }
                $data['first_name'] = ucfirst(trim($text));
                $state['data'] = $data;
                return $this->ask($state, 'last_name', "Thanks {$data['first_name']}. What is your **last name**?");

            case 'last_name':
                if (! preg_match("/^[A-Za-z][A-Za-z\s'.\-]{1,59}$/", $text)) {
                    return $this->ask($state, 'last_name', "Please enter a valid last name (letters only).");
                }
                $data['last_name'] = ucfirst(trim($text));
                $state['data'] = $data;
                return $this->ask($state, 'email', "What is your **email address**? (or type **skip**)");

            case 'email':
                if (! in_array($lower, ['skip', 'none', 'no'], true)) {
                    if (! filter_var(trim($text), FILTER_VALIDATE_EMAIL)) {
                        return $this->ask($state, 'email', "That email doesn't look valid. Please enter a valid email, or type **skip**.");
                    }
                    $data['email'] = trim($text);
                }
                $state['data'] = $data;
                return $this->ask($state, 'phone', "What is your **phone number**? (digits only)");

            case 'phone':
                $digits = preg_replace('/[^\d+]/', '', $text);
                if (! preg_match('/^\+?\d{7,15}$/', $digits)) {
                    return $this->ask($state, 'phone', "Please enter a valid phone number (7 to 15 digits).");
                }
                $data['phone'] = $digits;
                $state['data'] = $data;
                return $this->ask($state, 'date',
                    "What **date** would you like to come in? (YYYY-MM-DD, e.g. " . now()->addDay()->format('Y-m-d') . ")");

            case 'date':
                $date = $this->parseDate($text);
                if (! $date) {
                    return $this->ask($state, 'date', "I couldn't read that date. Please use the format YYYY-MM-DD (e.g. " . now()->addDay()->format('Y-m-d') . ").");
                }
                if ($date->isPast() && ! $date->isToday()) {
                    return $this->ask($state, 'date', "That date is in the past. Please choose today or a future date (YYYY-MM-DD).");
                }
                $data['appointment_date'] = $date->format('Y-m-d');
                $state['data'] = $data;
                return $this->ask($state, 'time', "What **time** would you prefer? (24-hour HH:MM, e.g. 09:30)");

            case 'time':
                $time = $this->parseTime($text);
                if (! $time) {
                    return $this->ask($state, 'time', "I couldn't read that time. Please use 24-hour format HH:MM (e.g. 14:00).");
                }
                $data['appointment_time'] = $time;
                $state['data'] = $data;
                return $this->ask($state, 'reason', "Briefly, what is the **reason** for your visit? (or type **skip**)");

            case 'reason':
                if (! in_array($lower, ['skip', 'none', 'no'], true)) {
                    $data['notes'] = mb_substr(trim($text), 0, 500);
                }
                $state['data'] = $data;
                return $this->finalizeBooking($state);
        }

        // Unknown step — restart.
        return $this->startBooking([]);
    }

    protected function finalizeBooking(array $state): array
    {
        $data = $state['data'] ?? [];

        try {
            $req = AppointmentRequest::create([
                'first_name' => $data['first_name'] ?? 'Guest',
                'last_name' => $data['last_name'] ?? 'Patient',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'immigration_service_number' => $data['immigration_service_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Chatbot booking failed: ' . $e->getMessage());
            return [
                'reply' => "I'm sorry, something went wrong while saving your request. Please try the booking form on this page, or call the front desk.",
                'state' => ['flow' => null],
                'booked' => false,
                'source' => 'booking',
            ];
        }

        $ref = 'REQ-' . str_pad((string) $req->id, 6, '0', STR_PAD_LEFT);
        $when = Carbon::parse($data['appointment_date'])->format('l, j M Y') . ' at ' . $data['appointment_time'];

        return [
            'reply' => "✅ Your appointment request is booked!\n\n"
                . "**Reference:** {$ref}\n"
                . "**Name:** {$data['first_name']} {$data['last_name']}\n"
                . "**When:** {$when}\n\n"
                . "Our front desk will review and confirm your appointment, and you'll receive a confirmation "
                . (! empty($data['email']) ? "at {$data['email']}." : "by phone.")
                . " Is there anything else I can help you with?",
            'state' => ['flow' => null],
            'booked' => true,
            'source' => 'booking',
        ];
    }

    protected function ask(array $state, string $nextStep, string $reply): array
    {
        $state['flow'] = 'booking';
        $state['step'] = $nextStep;
        return ['reply' => $reply, 'state' => $state, 'booked' => false, 'source' => 'booking'];
    }

    protected function parseDate(string $text): ?Carbon
    {
        $text = trim($text);
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
                return Carbon::createFromFormat('Y-m-d', $text)->startOfDay();
            }
            $ts = strtotime($text);
            return $ts ? Carbon::createFromTimestamp($ts)->startOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseTime(string $text): ?string
    {
        $text = trim($text);
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $text, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }
        $ts = strtotime($text);
        return $ts ? date('H:i', $ts) : null;
    }

    // ─────────────────────────── Q&A ───────────────────────────

    /**
     * Live knowledge context assembled from the application's own data.
     */
    protected function knowledge(): array
    {
        $facility = Setting::getVal('facility_name', 'NIS Medical Clinic HQ');
        $branch = Setting::getVal('active_branch', 'Central Command, Abuja');
        $departments = Department::orderBy('name')->pluck('name')->all();

        return compact('facility', 'branch', 'departments');
    }

    protected function answerQuestion(string $text): string
    {
        // Try the live model first when configured.
        $live = $this->askClaude($text);
        if ($live !== null) {
            return $live;
        }
        return $this->offlineAnswer($text);
    }

    protected function offlineAnswer(string $text): string
    {
        $q = strtolower($text);
        $k = $this->knowledge();
        $depts = implode(', ', $k['departments']);

        if ($this->matches($q, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening']) || mb_strlen(trim($q)) < 3) {
            return "Hello! 👋 I'm **MediBot**, the virtual assistant for {$k['facility']}. I can:\n\n"
                . "• Answer questions about our clinical services and departments\n"
                . "• Explain how to register or find your Hospital Number\n"
                . "• **Book an outpatient appointment** for you\n\n"
                . "How can I help? You can say *\"I'd like to book an appointment\"* to get started.";
        }
        if ($this->matches($q, ['service', 'what do you offer', 'what can you do', 'treatment', 'department', 'unit', 'clinic'])) {
            return "We run a full range of clinical services across these departments:\n\n• " . str_replace(', ', "\n• ", $depts)
                . "\n\nWould you like to book an appointment with any of them?";
        }
        if ($this->matches($q, ['hour', 'open', 'time', 'when are you'])) {
            return "Our General Outpatient Department runs **Monday to Friday, 8:00am to 5:00pm**. "
                . "The **Accident & Emergency (A&E) unit is open 24/7** for emergencies.";
        }
        if ($this->matches($q, ['where', 'location', 'address', 'find you', 'direction'])) {
            return "You'll find us at the **NIS Headquarters, Sauka, Airport Road, Abuja** ({$k['branch']}). "
                . "You can also reach the front desk on +234 (0) 9-234-5678.";
        }
        if ($this->matches($q, ['register', 'new patient', 'sign up', 'enrol', 'enroll'])) {
            return "To register as a new patient, visit the Medical Records desk with a valid ID (and your NIS service number if you are staff or a dependant). "
                . "You'll be issued a unique **Hospital Number** (e.g. NIS/PAT/000123) used for all future visits. "
                . "I can also start an appointment request for you right now — just say *\"book an appointment\"*.";
        }
        if ($this->matches($q, ['hospital number', 'hospital code', 'patient number', 'file number', 'my number'])) {
            return "Your **Hospital Number** is the unique code (like NIS/PAT/000123) issued when you first registered. "
                . "It appears on your patient card and receipts. If you have it, I can use it to book your appointment faster.";
        }
        if ($this->matches($q, ['emergency', 'urgent', 'ambulance', 'accident', 'a&e', 'critical'])) {
            return "🚨 For emergencies, please go straight to our **Accident & Emergency unit, open 24/7**, or call the emergency line immediately. "
                . "Do not wait for an online appointment for urgent or life-threatening conditions.";
        }
        if ($this->matches($q, ['bill', 'pay', 'cost', 'price', 'fee', 'nhis', 'insurance', 'charge'])) {
            return "Consultation and services are billed at our cashier points, with support for Cash, POS, Bank Transfer and Insurance. "
                . "NIS officers and their registered dependants enjoy NHIS co-payment coverage on eligible services.";
        }
        if ($this->matches($q, ['appointment', 'book', 'schedule', 'see a doctor'])) {
            return "I'd be glad to help you book an appointment. Just say *\"book an appointment\"* and I'll walk you through it step by step.";
        }

        return "I'm here to help with information about {$k['facility']} and to book appointments. "
            . "You can ask about our **services, departments, opening hours, location, registration, or billing**, "
            . "or say *\"book an appointment\"* to schedule a visit.";
    }

    protected function matches(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Free-form answer via Anthropic Claude, grounded in live app data.
     * Returns null when unavailable so the offline KB can take over.
     */
    protected function askClaude(string $message): ?string
    {
        $apiKey = config('services.anthropic.api_key');
        if (empty($apiKey)) {
            return null;
        }

        $k = $this->knowledge();
        $depts = implode(', ', $k['departments']);
        $system = "You are MediBot, the friendly virtual assistant on the public website of {$k['facility']} "
            . "({$k['branch']}), a Nigeria Immigration Service hospital. "
            . "Answer visitor questions helpfully and concisely (2-5 sentences), using ONLY this hospital's context. "
            . "Departments available: {$depts}. "
            . "General Outpatient hours are Mon-Fri 8am-5pm; Accident & Emergency is 24/7. "
            . "Location: NIS Headquarters, Sauka, Airport Road, Abuja. "
            . "Patients are identified by a Hospital Number like NIS/PAT/000123. "
            . "If a visitor wants to book an appointment, tell them to say 'book an appointment' and you will guide them. "
            . "Never give a specific personal diagnosis; for emergencies tell them to go to the 24/7 A&E. "
            . "Do not invent services or facts beyond this context.";

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
                'content-type' => 'application/json',
            ])->timeout(30)->post(rtrim(config('services.anthropic.base_url'), '/') . '/messages', [
                'model' => config('services.anthropic.model', 'claude-opus-5'),
                'max_tokens' => 700,
                'system' => $system,
                'output_config' => ['effort' => 'low'],
                'messages' => [['role' => 'user', 'content' => $message]],
            ]);

            if (! $response->successful()) {
                Log::warning('Chatbot live call failed', ['status' => $response->status()]);
                return null;
            }

            $reply = collect($response->json('content', []))
                ->where('type', 'text')->pluck('text')->implode("\n");

            return trim($reply) !== '' ? $reply : null;
        } catch (\Throwable $e) {
            Log::warning('Chatbot live call exception: ' . $e->getMessage());
            return null;
        }
    }
}
