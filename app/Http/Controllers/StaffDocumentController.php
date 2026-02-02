<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffDocumentStoreRequest;
use App\Http\Requests\StaffDocumentUpdateRequest;
use App\Models\Staff;
use App\Models\StaffDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class StaffDocumentController extends BaseController
{
    use AuthorizesRequests;

    public function store(StaffDocumentStoreRequest $request, Staff $staff)
    {
        $this->authorize('uploadDocument', $staff);

        $file = $request->file('file');
        $path = $file->store("staff/{$staff->id}", 'public');

        $doc = StaffDocument::create([
            'staff_id' => $staff->id,
            'document_type_id' => $request->integer('document_type_id') ?: null,
            'title' => $request->string('title')->trim()->toString() ?: null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        activity()
            ->useLog('domain')
            ->event('staff_document_uploaded')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties([
                'document_id' => (int) $doc->id,
                'staff_id' => (int) $staff->id,
                'document_type_id' => $doc->document_type_id ? (int) $doc->document_type_id : null,
                'title' => (string) ($doc->title ?? ''),
                'file_name' => (string) ($doc->file_name ?? ''),
                'mime_type' => (string) ($doc->mime_type ?? ''),
                'file_size' => (int) ($doc->file_size ?? 0),
            ])
            ->log('Staff document uploaded');

        return back()->with('success', 'Dokumen TAS berhasil diupload.');
    }

    public function show(Request $request, Staff $staff, StaffDocument $document)
    {
        abort_unless((int) $document->staff_id === (int) $staff->id, 404);
        $this->authorize('view', $staff);

        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);

        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function edit(Request $request, Staff $staff, StaffDocument $document)
    {
        abort_unless((int) $document->staff_id === (int) $staff->id, 404);
        $this->authorize('uploadDocument', $staff);

        $documentTypes = DocumentType::query()
            ->where('for', 'staff')
            ->orderBy('name')
            ->get();

        return view('staff.documents.edit', compact('staff', 'document', 'documentTypes'));
    }

    public function update(StaffDocumentUpdateRequest $request, Staff $staff, StaffDocument $document)
    {
        abort_unless((int) $document->staff_id === (int) $staff->id, 404);
        $this->authorize('uploadDocument', $staff);

        $propsBefore = [
            'document_id' => (int) $document->id,
            'staff_id' => (int) $staff->id,
            'old_document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
            'old_title' => (string) ($document->title ?? ''),
            'old_file_name' => (string) ($document->file_name ?? ''),
            'old_file_path' => (string) ($document->file_path ?? ''),
        ];

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store("staff/{$staff->id}", 'public');

            $document->file_path = $path;
            $document->file_name = $file->getClientOriginalName();
            $document->mime_type = $file->getClientMimeType();
            $document->file_size = $file->getSize();
            $document->uploaded_by = auth()->id();
        }

        $document->document_type_id = $request->integer('document_type_id') ?: null;
        $document->title = $request->string('title')->trim()->toString() ?: null;
        $document->save();

        activity()
            ->useLog('domain')
            ->event('staff_document_updated')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties($propsBefore + [
                'new_document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
                'new_title' => (string) ($document->title ?? ''),
                'new_file_name' => (string) ($document->file_name ?? ''),
                'new_file_path' => (string) ($document->file_path ?? ''),
            ])
            ->log('Staff document updated');

        return redirect()
            ->route('staff.show', $staff)
            ->with('success', 'Dokumen TAS berhasil diperbarui.');
    }

    public function destroy(Request $request, Staff $staff, StaffDocument $document)
    {
        abort_unless((int) $document->staff_id === (int) $staff->id, 404);
        $this->authorize('uploadDocument', $staff);

        $props = [
            'document_id' => (int) $document->id,
            'staff_id' => (int) $staff->id,
            'document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
            'title' => (string) ($document->title ?? ''),
            'file_name' => (string) ($document->file_name ?? ''),
            'mime_type' => (string) ($document->mime_type ?? ''),
            'file_size' => (int) ($document->file_size ?? 0),
        ];

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        activity()
            ->useLog('domain')
            ->event('staff_document_deleted')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties($props)
            ->log('Staff document deleted');

        return back()->with('success', 'Dokumen TAS berhasil dihapus.');
    }
}
