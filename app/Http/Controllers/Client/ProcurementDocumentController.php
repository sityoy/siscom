<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\CompanySetting;
use App\Models\ProcurementDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcurementDocumentController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;
        abort_unless($client, 403);

        $documents = ProcurementDocument::with(['project', 'invoice'])
            ->whereHas('project', fn ($query) => $query->where('client_id', $client->id))
            ->whereNot('status', 'draft')
            ->latest('document_date')
            ->paginate(10);

        return view('clients.procurement-documents.index', compact('documents'));
    }

    public function pdf(ProcurementDocument $procurementDocument)
    {
        $this->authorizeDocument($procurementDocument);
        $procurementDocument->load(['project.client', 'invoice']);

        return Pdf::loadView('admin.procurement-documents.pdf', [
            'document' => $procurementDocument,
            'company' => CompanySetting::first(),
        ])->setPaper('a4')->stream(
            Str::slug($procurementDocument->type_label.'-'.$procurementDocument->document_number).'.pdf'
        );
    }

    public function downloadSigned(ProcurementDocument $procurementDocument)
    {
        $this->authorizeDocument($procurementDocument);

        abort_unless(
            $procurementDocument->signed_file_path
                && Storage::disk('public')->exists($procurementDocument->signed_file_path),
            404,
        );

        return Storage::disk('public')->download(
            $procurementDocument->signed_file_path,
            'signed-'.basename($procurementDocument->signed_file_path),
        );
    }

    public function uploadSigned(Request $request, ProcurementDocument $procurementDocument)
    {
        $this->authorizeDocument($procurementDocument);

        abort_unless(
            in_array($procurementDocument->status, ['awaiting_signature', 'signed'], true),
            422,
            'Dokumen ini tidak sedang menunggu tanda tangan.',
        );

        $request->validate([
            'signed_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($procurementDocument->signed_file_path) {
            Storage::disk('public')->delete($procurementDocument->signed_file_path);
        }

        $path = $request->file('signed_file')->store('procurement-documents/signed', 'public');

        $procurementDocument->update([
            'signed_file_path' => $path,
            'status' => 'signed',
        ]);

        AdminNotification::create([
            'title' => 'Dokumen Rekanan Ditandatangani',
            'message' => $procurementDocument->project->client->name
                .' mengunggah dokumen bertanda tangan '
                .$procurementDocument->document_number.'.',
        ]);

        return back()->with('success', 'Dokumen bertanda tangan berhasil dikirim ke SIS.COM.');
    }

    private function authorizeDocument(ProcurementDocument $document): void
    {
        $client = auth()->user()->client;

        abort_unless(
            $client
                && $document->status !== 'draft'
                && $document->project()->where('client_id', $client->id)->exists(),
            403,
        );
    }
}
