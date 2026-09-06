<?php

namespace App\Services;

use App\Models\Patient;

/**
 * Clinical drug-safety checks performed at prescribing time:
 *  - allergy conflicts (including common cross-reactive drug classes), and
 *  - well-known drug-drug interactions among the drugs being prescribed.
 *
 * This is decision SUPPORT (advisory), not a hard block — the prescriber decides.
 */
class DrugSafetyService
{
    /**
     * Cross-reactivity: an allergy keyword -> drug name keywords it flags.
     */
    protected array $allergyClasses = [
        'penicillin'   => ['penicillin', 'amoxicillin', 'ampicillin', 'augmentin', 'amoxiclav', 'flucloxacillin', 'piperacillin'],
        'sulfa'        => ['sulfa', 'sulfonamide', 'cotrimoxazole', 'septrin', 'sulfamethoxazole'],
        'sulfonamide'  => ['sulfa', 'sulfonamide', 'cotrimoxazole', 'septrin', 'sulfamethoxazole'],
        'nsaid'        => ['ibuprofen', 'diclofenac', 'aspirin', 'naproxen', 'indomethacin', 'nsaid'],
        'aspirin'      => ['aspirin', 'ibuprofen', 'diclofenac', 'naproxen', 'nsaid'],
        'cephalosporin'=> ['cef', 'ceftriaxone', 'cefuroxime', 'cefixime', 'cephalexin'],
    ];

    /**
     * Interaction pairs: [keyword A, keyword B, severity, advisory].
     */
    protected array $interactions = [
        ['warfarin', 'aspirin', 'high', 'Increased bleeding risk — avoid or monitor INR closely.'],
        ['warfarin', 'ibuprofen', 'high', 'NSAIDs increase bleeding risk with warfarin.'],
        ['warfarin', 'diclofenac', 'high', 'NSAIDs increase bleeding risk with warfarin.'],
        ['ace', 'ibuprofen', 'moderate', 'NSAID + ACE inhibitor: risk of acute kidney injury and reduced antihypertensive effect.'],
        ['lisinopril', 'ibuprofen', 'moderate', 'NSAID + ACE inhibitor: renal risk; monitor renal function.'],
        ['losartan', 'ibuprofen', 'moderate', 'NSAID + ARB: renal risk; monitor renal function.'],
        ['ciprofloxacin', 'antacid', 'moderate', 'Antacids reduce ciprofloxacin absorption — separate doses by 2–6 hours.'],
        ['metronidazole', 'alcohol', 'high', 'Disulfiram-like reaction — counsel to avoid alcohol.'],
        ['methotrexate', 'ibuprofen', 'high', 'NSAIDs raise methotrexate toxicity.'],
        ['digoxin', 'furosemide', 'moderate', 'Hypokalaemia from furosemide potentiates digoxin toxicity.'],
        ['tramadol', 'sertraline', 'high', 'Serotonergic combination — risk of serotonin syndrome.'],
        ['simvastatin', 'clarithromycin', 'high', 'Raises statin levels — myopathy/rhabdomyolysis risk.'],
    ];

    /**
     * @param  array<int,string>  $drugNames
     * @return array{allergy_alerts: array, interaction_alerts: array, has_alerts: bool}
     */
    public function check(?Patient $patient, array $drugNames): array
    {
        $drugs = array_values(array_filter(array_map(fn ($d) => strtolower(trim((string) $d)), $drugNames)));

        $allergyAlerts = [];
        $allergyTokens = $this->patientAllergyTokens($patient);
        foreach ($drugs as $drug) {
            foreach ($allergyTokens as $allergy) {
                if ($this->drugMatchesAllergy($drug, $allergy)) {
                    $allergyAlerts[] = [
                        'severity' => 'high',
                        'drug' => $drug,
                        'allergy' => $allergy,
                        'message' => "Patient is allergic to \"{$allergy}\" — \"{$drug}\" may cross-react. Verify before prescribing.",
                    ];
                }
            }
        }

        $interactionAlerts = [];
        foreach ($this->interactions as [$a, $b, $severity, $advisory]) {
            $hasA = $this->listHas($drugs, $a);
            $hasB = $this->listHas($drugs, $b);
            if ($hasA && $hasB) {
                $interactionAlerts[] = [
                    'severity' => $severity,
                    'pair' => [$a, $b],
                    'message' => ucfirst($a) . ' + ' . ucfirst($b) . ': ' . $advisory,
                ];
            }
        }

        return [
            'allergy_alerts' => $allergyAlerts,
            'interaction_alerts' => $interactionAlerts,
            'has_alerts' => (count($allergyAlerts) + count($interactionAlerts)) > 0,
        ];
    }

    protected function patientAllergyTokens(?Patient $patient): array
    {
        $raw = strtolower(trim((string) ($patient?->allergies ?? '')));
        if ($raw === '' || $raw === 'none' || $raw === 'nil' || $raw === 'n/a') {
            return [];
        }
        return array_values(array_filter(array_map('trim', preg_split('/[,;\/]+/', $raw))));
    }

    protected function drugMatchesAllergy(string $drug, string $allergy): bool
    {
        // Direct substring match either way.
        if (str_contains($drug, $allergy) || str_contains($allergy, $drug)) {
            return true;
        }
        // Cross-reactive class match.
        foreach ($this->allergyClasses as $classKey => $members) {
            if (str_contains($allergy, $classKey)) {
                foreach ($members as $m) {
                    if (str_contains($drug, $m)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function listHas(array $drugs, string $keyword): bool
    {
        foreach ($drugs as $d) {
            if (str_contains($d, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
