<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

/**
 * Public patient self-service portal. A patient authenticates lightly with
 * their hospital code plus surname (two matching identifiers) and can then see
 * a limited, safe view of their own upcoming appointments, prescriptions and
 * recent visits. No clinical notes or diagnostics are exposed. The route is
 * rate-limited to deter enumeration.
 */
class PortalController extends Controller
{
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'hospital_code' => 'required|string|max:50',
            'surname' => 'required|string|max:100',
        ]);

        $code = trim($validated['hospital_code']);
        $surname = mb_strtolower(trim($validated['surname']));

        $patient = Patient::whereRaw('LOWER(immigration_service_number) = ?', [mb_strtolower($code)])
            ->whereRaw('LOWER(last_name) = ?', [$surname])
            ->with([
                'appointments' => fn ($q) => $q->whereDate('appointment_date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'checked_in'])
                    ->orderBy('appointment_date')->with('department:id,name', 'doctor:id,first_name,last_name'),
                'prescriptions' => fn ($q) => $q->latest()->limit(5)->with('items:id,prescription_id,drug_name,dosage,frequency,status'),
                'visits' => fn ($q) => $q->latest()->limit(5)->with('department:id,name'),
            ])
            ->first();

        // Deliberately identical response shape on failure — do not reveal
        // whether the code exists.
        if (! $patient) {
            return response()->json(['found' => false, 'message' => 'No record matches that hospital code and surname.'], 404);
        }

        return response()->json([
            'found' => true,
            'patient' => [
                'name' => $patient->full_name,
                'hospital_code' => $patient->immigration_service_number,
            ],
            'appointments' => $patient->appointments->map(fn ($a) => [
                'date' => $a->appointment_date,
                'time' => $a->appointment_time,
                'department' => $a->department?->name,
                'doctor' => $a->doctor ? trim($a->doctor->first_name . ' ' . $a->doctor->last_name) : null,
                'status' => $a->status,
            ]),
            'prescriptions' => $patient->prescriptions->map(fn ($p) => [
                'date' => $p->created_at?->toDateString(),
                'status' => $p->status,
                'items' => $p->items->map(fn ($it) => [
                    'drug_name' => $it->drug_name,
                    'dosage' => $it->dosage,
                    'frequency' => $it->frequency,
                    'status' => $it->status,
                ]),
            ]),
            'visits' => $patient->visits->map(fn ($v) => [
                'date' => $v->created_at?->toDateString(),
                'department' => $v->department?->name,
            ]),
        ]);
    }
}
