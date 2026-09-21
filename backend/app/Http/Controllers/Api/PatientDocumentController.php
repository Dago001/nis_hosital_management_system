<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Documents attached to a patient's file (referral letters, consent forms,
 * scanned ID copies, insurance cards, outside results). Files are held on the
 * private disk and only ever streamed back through the authenticated download
 * route — they are never exposed publicly, since they are PHI.
 */
class PatientDocumentController extends Controller
{
    protected AuditLogRepositoryInterface $auditLog;

    public function __construct(AuditLogRepositoryInterface $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(int $patientId)
    {
        $documents = PatientDocument::where('patient_id', $patientId)
            ->with('uploader:id,name')
            ->latest()
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'category' => $d->category,
                'original_name' => $d->original_name,
                'mime_type' => $d->mime_type,
                'size_kb' => round($d->size_bytes / 1024, 1),
                'uploaded_by' => $d->uploader?->name,
                'created_at' => $d->created_at?->toDateTimeString(),
            ]);

        return response()->json(['documents' => $documents]);
    }

    public function store(Request $request, int $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|in:referral,consent,id_copy,lab_report,insurance,other',
            // 10 MB cap; common clinical document / scan formats only. `mimes`
            // validates the real (content-guessed) type, and `extensions` pins
            // the client extension so a spoofed content-type cannot slip through.
            'file' => [
                'required', 'file', 'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx',
                'extensions:pdf,jpg,jpeg,png,webp,doc,docx',
            ],
        ]);

        $file = $request->file('file');

        // Defence-in-depth: reject any file whose original name carries a
        // dangerous / executable extension (including double extensions such as
        // "report.pdf.php"), regardless of what the mime guesser reported.
        $original = $file->getClientOriginalName();
        if (preg_match('/\.(php\d*|phtml|phar|exe|sh|bat|cmd|com|cgi|pl|py|rb|js|jsp|asp|aspx|htaccess|html?|svg)(\.|$)/i', $original)) {
            return response()->json(['message' => 'This file type is not permitted.'], 422);
        }

        // Hashed, non-guessable name on the private (non-public) disk.
        $path = $file->store('patient_documents/' . $patient->id, 'local');

        $doc = PatientDocument::create([
            'patient_id' => $patient->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'file_path' => $path,
            // Keep the display name but strip any path components and control chars.
            'original_name' => mb_substr(preg_replace('/[\x00-\x1F\/\\\\]+/', '_', basename($original)) ?? 'document', 0, 200),
            // Trust the server-side guessed type, not the client-supplied header.
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        $this->auditLog->log(
            userId: Auth::id(),
            action: 'upload_patient_document',
            auditableType: PatientDocument::class,
            auditableId: $doc->id,
            payload: ['patient_id' => $patient->id, 'category' => $doc->category],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json(['message' => 'Document uploaded.', 'id' => $doc->id], 201);
    }

    public function download(Request $request, int $documentId)
    {
        $doc = PatientDocument::findOrFail($documentId);

        if (! Storage::disk('local')->exists($doc->file_path)) {
            return response()->json(['message' => 'File no longer available.'], 404);
        }

        $this->auditLog->log(
            userId: $request->user()?->id,
            action: 'download_patient_document',
            auditableType: PatientDocument::class,
            auditableId: $doc->id,
            payload: ['patient_id' => $doc->patient_id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return Storage::disk('local')->download($doc->file_path, $doc->original_name);
    }

    public function destroy(Request $request, int $documentId)
    {
        $doc = PatientDocument::findOrFail($documentId);
        Storage::disk('local')->delete($doc->file_path);

        $this->auditLog->log(
            userId: $request->user()?->id,
            action: 'delete_patient_document',
            auditableType: PatientDocument::class,
            auditableId: $doc->id,
            payload: ['patient_id' => $doc->patient_id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $doc->delete();

        return response()->json(['message' => 'Document deleted.']);
    }
}
