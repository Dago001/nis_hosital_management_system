<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Clinical Decision Support (CDSS) assistant.
 *
 * Uses the Anthropic Claude API when an API key is configured, and transparently
 * falls back to a curated offline clinical knowledge base otherwise — so the
 * advisor remains useful in air-gapped or on-premise hospital deployments.
 *
 * When online, the advisor:
 *   - reasons with extended (adaptive) thinking at high effort,
 *   - can search the web in real time for the latest guidance, outbreaks,
 *     drug recalls and evidence (server-side web_search tool), and
 *   - keeps multi-turn conversation context and optional patient context,
 * so it can advise on essentially any illness worldwide with current information.
 */
class ClinicalAiService
{
    /**
     * System prompt that scopes the model to safe, worldwide clinical support.
     */
    protected function systemPrompt(?string $patientContext = null): string
    {
        $prompt = <<<'PROMPT'
You are the Clinical Decision Support (CDSS) assistant embedded in the Nigeria
Immigration Service Hospital Management System, assisting licensed clinicians.

SCOPE
- You are a broad clinical knowledge resource covering the full range of human
  disease worldwide: infectious (tropical and non-tropical), non-communicable,
  paediatric, obstetric, surgical, psychiatric, dermatological, genetic, rare and
  emerging conditions, toxicology, and medical emergencies.
- You support diagnosis, differential diagnosis, investigation, management,
  drug dosing (including paediatric weight-based dosing), drug interactions and
  contraindications, treatment protocols, and ICD-10/ICD-11 coding.

REAL-TIME EVIDENCE
- When a question depends on current or fast-changing information — the latest
  WHO/CDC/FMOH or specialty-society guidelines, current outbreaks or travel
  advisories, drug shortages or recalls, or newly approved therapies — USE THE
  WEB SEARCH TOOL to retrieve up-to-date sources, then cite them.
- Prefer authoritative sources (WHO, CDC, Nigeria FMOH/NCDC, UpToDate-style
  guidelines, major journals). State the guideline/version and year where possible.

HOW TO ANSWER
- Be concise, structured and practical for a busy outpatient/inpatient setting.
- Where useful, organise the answer as: Assessment / Differential diagnosis,
  Recommended investigations, Management (with doses and durations), Red flags /
  when to escalate or refer, and Sources.
- Use clear Markdown: short headings, bullet points and tables where helpful.
- Give weight-based paediatric doses and note pregnancy/renal/hepatic caveats.
- Localise to the Nigerian context where relevant (endemic disease, first-line
  agents, resource-appropriate options) while remaining globally accurate.

SAFETY
- Never fabricate patient-specific data, references or dosages. If uncertain, say so
  and recommend verification against an authoritative source or specialist.
- Always end with a brief safety note: final clinical judgement rests with the
  attending physician and all doses must be independently cross-checked.
- This is decision support, not a diagnosis or a substitute for clinical assessment.
PROMPT;

        if ($patientContext !== null && trim($patientContext) !== '') {
            $ctx = trim(mb_substr($patientContext, 0, 2000));
            $prompt .= "\n\nCURRENT PATIENT CONTEXT (provided by the clinician; use it to tailor advice, "
                . "do not repeat it verbatim):\n" . $ctx;
        }

        return $prompt;
    }

    /**
     * Produce a reply for the given clinician message.
     *
     * @param string $message         The clinician's latest question.
     * @param array  $history         Prior turns: [['role'=>'user'|'assistant','content'=>string], ...].
     * @param string|null $patientContext Optional free-text patient summary.
     */
    public function respond(string $message, array $history = [], ?string $patientContext = null): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (! empty($apiKey)) {
            $live = $this->askClaude($message, $apiKey, $history, $patientContext);
            if ($live !== null && trim($live) !== '') {
                return ['reply' => $live, 'source' => 'live'];
            }
            // Fall through to offline knowledge base on any API failure.
        }

        return ['reply' => $this->offlineReply($message), 'source' => 'offline'];
    }

    /**
     * Call the Anthropic Messages API with extended thinking + web search.
     * Handles the server-tool `pause_turn` continuation loop. Returns the reply
     * text (with sources appended), or null on failure.
     */
    protected function askClaude(string $message, string $apiKey, array $history, ?string $patientContext): ?string
    {
        try {
            $model = config('services.anthropic.model', 'claude-opus-5');
            $effort = config('services.anthropic.effort', 'high');
            $maxTokens = (int) config('services.anthropic.max_tokens', 4096);
            $timeout = (int) config('services.anthropic.timeout', 120);
            $webSearch = filter_var(config('services.anthropic.web_search', true), FILTER_VALIDATE_BOOLEAN);
            $maxWebUses = (int) config('services.anthropic.web_search_max_uses', 5);

            // Build the conversation: prior turns + the new question.
            $messages = [];
            foreach ($history as $turn) {
                $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
                $content = trim((string) ($turn['content'] ?? ''));
                if ($content !== '') {
                    $messages[] = ['role' => $role, 'content' => $content];
                }
            }
            $messages[] = ['role' => 'user', 'content' => $message];

            $payload = [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'system' => $this->systemPrompt($patientContext),
                'output_config' => ['effort' => $effort],
                'thinking' => ['type' => 'adaptive'],
                'messages' => $messages,
            ];
            if ($webSearch) {
                // Real-time evidence retrieval (dynamic-filtering variant on Opus/Sonnet 5).
                $payload['tools'] = [[
                    'type' => 'web_search_20260209',
                    'name' => 'web_search',
                    'max_uses' => $maxWebUses,
                ]];
            }

            $answer = '';
            $sources = [];

            // Server tools (web search) may return stop_reason "pause_turn"; resend
            // with the accumulated assistant content until the turn completes.
            for ($i = 0; $i < 6; $i++) {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
                    'content-type' => 'application/json',
                ])->timeout($timeout)
                  ->post(rtrim(config('services.anthropic.base_url'), '/') . '/messages', $payload);

                if (! $response->successful()) {
                    // If web search is the problem, retry once without it before giving up.
                    if ($webSearch && $i === 0 && in_array($response->status(), [400, 403, 404], true)) {
                        Log::warning('Clinical AI: retrying without web search', ['body' => $response->body()]);
                        unset($payload['tools']);
                        $webSearch = false;
                        continue;
                    }
                    Log::warning('Clinical AI live call failed', [
                        'status' => $response->status(),
                        'body' => mb_substr($response->body(), 0, 500),
                    ]);
                    return $answer !== '' ? $this->appendSources($answer, $sources) : null;
                }

                $data = $response->json();
                $content = $data['content'] ?? [];

                foreach ($content as $block) {
                    $type = $block['type'] ?? '';
                    if ($type === 'text') {
                        $answer .= ($answer === '' ? '' : "\n") . ($block['text'] ?? '');
                        foreach (($block['citations'] ?? []) as $cite) {
                            $this->collectSource($sources, $cite);
                        }
                    } elseif ($type === 'web_search_tool_result') {
                        $items = $block['content'] ?? [];
                        if (is_array($items)) {
                            foreach ($items as $item) {
                                $this->collectSource($sources, $item);
                            }
                        }
                    }
                }

                if (($data['stop_reason'] ?? '') === 'pause_turn' && ! empty($content)) {
                    // Continue the turn: echo the assistant blocks back verbatim.
                    $messages[] = ['role' => 'assistant', 'content' => $content];
                    $payload['messages'] = $messages;
                    continue;
                }
                break;
            }

            return trim($answer) !== '' ? $this->appendSources($answer, $sources) : null;
        } catch (\Throwable $e) {
            Log::warning('Clinical AI live call exception: ' . $e->getMessage());
            return null;
        }
    }

    /** Add a source (from a citation or a web_search_result) to the unique list. */
    protected function collectSource(array &$sources, array $item): void
    {
        $url = $item['url'] ?? null;
        if (! $url) {
            return;
        }
        $title = $item['title'] ?? $item['document_title'] ?? $url;
        $sources[$url] = $title; // keyed by URL -> deduped
    }

    /** Append a compact, de-duplicated Sources section to the answer. */
    protected function appendSources(string $answer, array $sources): string
    {
        if (empty($sources)) {
            return $answer;
        }
        $lines = [];
        $n = 1;
        foreach ($sources as $url => $title) {
            $lines[] = "{$n}. [" . trim((string) $title) . "]({$url})";
            $n++;
            if ($n > 8) {
                break;
            }
        }
        return $answer . "\n\n### Sources\n" . implode("\n", $lines);
    }

    /**
     * Offline curated clinical knowledge base (keyword routed).
     */
    protected function offlineReply(string $rawMessage): string
    {
        $query = strtolower(trim($rawMessage));

        if (str_contains($query, 'dosage') || str_contains($query, 'dose') || str_contains($query, 'pediatric') || str_contains($query, 'paediatric') || str_contains($query, 'weight')) {
            return "### Clinical AI Advisor: Pediatric Dosing & Calculations\n\n"
                . "For pediatric weight-based calculations, please use the following clinical standard variables:\n\n"
                . "1. **Paracetamol Suspension:**\n"
                . "   - *Standard Dose:* 15 mg/kg per dose, administered every 4-6 hours (Max 4 doses in 24 hours).\n"
                . "   - *Example:* For a 10kg infant, prescribe 150mg (6ml of 125mg/5ml suspension) per dose.\n\n"
                . "2. **Amoxicillin Suspension:**\n"
                . "   - *Standard Dose:* 15 to 30 mg/kg/dose twice daily (or 20-40 mg/kg/day split into 3 doses) depending on severity.\n"
                . "   - *Example:* For a 10kg child, prescribe 250mg (5ml of 250mg/5ml suspension) twice daily.\n\n"
                . "3. **Artesunate (Severe Malaria IV):**\n"
                . "   - *Dose (<20kg):* 3.0 mg/kg per dose at 0 hours, 12 hours, 24 hours, then daily.\n"
                . "   - *Dose (≥20kg):* 2.4 mg/kg per dose at 0, 12, 24 hours, then daily.\n\n"
                . "> *Clinical Warning: Always double-check calculations and ensure weight is measured accurately on a calibrated scale before prescribing.*";
        }

        if (str_contains($query, 'interaction') || str_contains($query, 'contraindicat') || str_contains($query, 'safe') || str_contains($query, 'nsaid')) {
            return "### Clinical AI Advisor: Drug Interaction Analysis\n\n"
                . "Here is the safety summary for common clinical interactions in GOPD:\n\n"
                . "- **NSAIDs + ACE Inhibitors (e.g., Ibuprofen + Amlodipine/Losartan):**\n"
                . "  - *Risk:* NSAIDs decrease prostaglandin synthesis, causing renal afferent vasoconstriction. Concomitant use increases risk of acute kidney injury (AKI) and reduces antihypertensive efficacy.\n"
                . "  - *Advisory:* Monitor renal function; consider Paracetamol as alternative analgesic.\n\n"
                . "- **Metronidazole + Alcohol:**\n"
                . "  - *Risk:* Disulfiram-like reaction (severe vomiting, tachycardia, flushing).\n"
                . "  - *Advisory:* Advise patient to avoid alcohol during and for 48 hours post-therapy.\n\n"
                . "- **Ciprofloxacin + Oral Antacids/Calcium:**\n"
                . "  - *Risk:* Divalent cations chelate quinolones, reducing absorption by up to 50%.\n"
                . "  - *Advisory:* Administer Ciprofloxacin 2 hours before or 6 hours after antacids.";
        }

        if (str_contains($query, 'malaria') || str_contains($query, 'artesunate') || str_contains($query, 'coartem')) {
            return "### Clinical AI Advisor: Malaria Treatment Protocol (WHO/FMOH)\n\n"
                . "Based on current guidelines for malaria management in Nigeria:\n\n"
                . "1. **Uncomplicated Malaria (Confirmed by RDT/Microscopy):**\n"
                . "   - First-line: ACTs (Artemether-Lumefantrine or Artesunate-Amodiaquine).\n"
                . "   - *Dosing (Adult):* Artemether-Lumefantrine 80/480mg (4 tablets of 20/120mg) twice daily for 3 days.\n"
                . "   - *Counseling:* Advise taking with fatty meals to optimize absorption.\n\n"
                . "2. **Severe Malaria (Cerebral, Severe Anemia, Persistent Vomiting):**\n"
                . "   - First-line: IV Artesunate 2.4 mg/kg (or 3mg/kg for children <20kg) at 0, 12, and 24 hours, followed by once daily until oral tolerance.\n"
                . "   - Transition to full 3-day course of oral ACT as soon as patient can tolerate oral fluids.";
        }

        if (str_contains($query, 'typhoid') || str_contains($query, 'salmonella') || str_contains($query, 'enteric')) {
            return "### Clinical AI Advisor: Typhoid Fever Treatment Guidelines\n\n"
                . "For suspected or confirmed Enteric (Typhoid) Fever (caused by *Salmonella typhi*):\n\n"
                . "1. **First-Line Empirical Therapy (Uncomplicated):**\n"
                . "   - **Ciprofloxacin:** 500mg orally twice daily for 7-10 days (contraindicated in pregnancy).\n"
                . "   - **Cefixime:** 400mg orally once daily for 7-14 days.\n\n"
                . "2. **Severe Enteric Fever (Requires Admission):**\n"
                . "   - **Ceftriaxone:** 1g to 2g IV once daily for 7-14 days.\n\n"
                . "3. **Diagnostic Workup:**\n"
                . "   - Order Blood Culture (Gold Standard in first week).\n"
                . "   - Order Stool Culture (second week).\n"
                . "   - Widal test is not recommended for definitive diagnosis due to high cross-reactivity.";
        }

        if (str_contains($query, 'hypertension') || str_contains($query, 'bp') || str_contains($query, 'blood pressure') || str_contains($query, 'cardio')) {
            return "### Clinical AI Advisor: Hypertension Management Guidelines\n\n"
                . "For adult patients with Elevated Blood Pressure (BP ≥ 140/90 mmHg):\n\n"
                . "1. **Pharmacological Prescribing Presets:**\n"
                . "   - **Calcium Channel Blockers (CCB):** e.g., Amlodipine 5mg to 10mg orally once daily. Preferred first-line in patients of African descent.\n"
                . "   - **Thiazide-like Diuretics:** e.g., Hydrochlorothiazide 12.5mg to 25mg orally once daily.\n"
                . "   - **ACE Inhibitors / ARBs:** e.g., Lisinopril 10mg orally once daily. Preferred in patients with Diabetes Mellitus or chronic kidney disease.\n\n"
                . "2. **Lifestyle Modifications:**\n"
                . "   - Low sodium diet (<2g/day), weight management, regular aerobic exercise, and alcohol restriction.";
        }

        if (str_contains($query, 'icd') || str_contains($query, 'code') || str_contains($query, 'coding') || str_contains($query, 'diagnosis')) {
            return "### Clinical AI Advisor: Common ICD-10 Coding Reference\n\n"
                . "Here are frequently used diagnostic codes in our General Outpatient Department:\n\n"
                . "| Diagnosis | ICD-10 Code | Clinical Category |\n"
                . "| :--- | :---: | :--- |\n"
                . "| **Plasmodium falciparum Malaria** | `B50.9` | Parasitic Infection |\n"
                . "| **Essential (Primary) Hypertension** | `I10` | Cardiovascular |\n"
                . "| **Type 2 Diabetes Mellitus** | `E11.9` | Endocrine / Metabolic |\n"
                . "| **Gastroenteritis (Infectious)** | `A09` | Gastrointestinal |\n"
                . "| **Acute Upper Respiratory Infection** | `J06.9` | Respiratory |\n"
                . "| **Urinary Tract Infection (UTI)** | `N39.0` | Nephrology / Urology |\n"
                . "| **Typhoid Fever** | `A01.0` | Bacterial Infection |";
        }

        if (preg_match('/^(what should i do|hello|hi|help|what do you do|explain|how to use|who are you)/', $query) || strlen($query) < 15) {
            return "### Clinical AI Companion Welcome Guide\n\n"
                . "Hello Doctor! I am your clinical decision support system (CDSS) assistant. I can help you across the full range of illnesses — diagnosis, investigations, treatment, dosing, interactions and coding. Examples:\n\n"
                . "- **Diagnosis & Differentials:** e.g. *'differential for fever + jaundice in a returning traveller'*\n"
                . "- **Pediatric Weight-Based Dosage:** e.g. *'paracetamol dose for a 12kg child'*\n"
                . "- **Drug Interaction Analysis:** e.g. *'interactions for ibuprofen and ACE inhibitors'*\n"
                . "- **Treatment Protocols:** e.g. *'latest WHO severe malaria guideline'*\n"
                . "- **ICD-10 Coding:** e.g. *'ICD-10 code for essential hypertension'*\n\n"
                . "> *Note: live web-connected answers with current guidelines are available when the AI service is configured. Offline, I provide curated reference guidance.*";
        }

        return "### Clinical AI Advisor: Consultation Analysis\n\n"
            . "I have analyzed your query regarding: *\"" . htmlentities($rawMessage) . "\"*.\n\n"
            . "**Clinical Support Guidelines:**\n"
            . "1. **Differential Diagnosis Workup:** Consider presenting symptoms, duration, and patient risk factors (e.g. travel history, occupational exposure, age).\n"
            . "2. **Diagnostic Support:** Order vital markers (BP, Temp, Pulse) and request corroborating tests (FBC, Blood Film, Urinalysis, Chemistries) via the laboratory tab.\n"
            . "3. **Empirical Therapy:** Initiate therapy based on local susceptibility patterns. Ensure patient allergy status (e.g. penicillin sensitivity) is cross-checked in the patient's file.\n\n"
            . "*Note: live web-connected answers with current global guidelines are available when the AI service is configured (ANTHROPIC_API_KEY). Clinical decision remains the sole responsibility of the attending physician.*";
    }
}
