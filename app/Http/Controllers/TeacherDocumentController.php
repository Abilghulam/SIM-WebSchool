<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherDocumentStoreRequest;
use App\Http\Requests\TeacherDocumentUpdateRequest;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class TeacherDocumentController extends BaseController
{
    use AuthorizesRequests;

    public function store(TeacherDocumentStoreRequest $request, Teacher $teacher)
    {
        $this->authorize('uploadDocument', $teacher);

        $file = $request->file('file');
        $path = $file->store("teachers/{$teacher->id}", 'public');

        $doc = TeacherDocument::create([
            'teacher_id' => $teacher->id,
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
            ->event('teacher_document_uploaded')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties([
                'document_id' => (int) $doc->id,
                'teacher_id' => (int) $teacher->id,
                'document_type_id' => $doc->document_type_id ? (int) $doc->document_type_id : null,
                'title' => (string) ($doc->title ?? ''),
                'file_name' => (string) ($doc->file_name ?? ''),
                'mime_type' => (string) ($doc->mime_type ?? ''),
                'file_size' => (int) ($doc->file_size ?? 0),
            ])
            ->log('Teacher document uploaded');

        return back()->with('success', 'Dokumen guru berhasil diupload.');
    }

    public function show(Request $request, Teacher $teacher, TeacherDocument $document)
    {
        abort_unless((int) $document->teacher_id === (int) $teacher->id, 404);
        $this->authorize('view', $teacher); // atau uploadDocument kalau mau lebih ketat

        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);

        // preview di tab baru
        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function edit(Request $request, Teacher $teacher, TeacherDocument $document)
    {
        abort_unless((int) $document->teacher_id === (int) $teacher->id, 404);
        $this->authorize('uploadDocument', $teacher);

        $documentTypes = DocumentType::query()
            ->where('for', 'teacher')
            ->orderBy('name')
            ->get();

        return view('teachers.documents.edit', compact('teacher', 'document', 'documentTypes'));
    }

    public function update(TeacherDocumentUpdateRequest $request, Teacher $teacher, TeacherDocument $document)
    {
        abort_unless((int) $document->teacher_id === (int) $teacher->id, 404);
        $this->authorize('uploadDocument', $teacher);

        $propsBefore = [
            'document_id' => (int) $document->id,
            'teacher_id' => (int) $teacher->id,
            'old_document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
            'old_title' => (string) ($document->title ?? ''),
            'old_file_name' => (string) ($document->file_name ?? ''),
            'old_file_path' => (string) ($document->file_path ?? ''),
        ];

        // replace file jika ada
        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store("teachers/{$teacher->id}", 'public');

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
            ->event('teacher_document_updated')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties($propsBefore + [
                'new_document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
                'new_title' => (string) ($document->title ?? ''),
                'new_file_name' => (string) ($document->file_name ?? ''),
                'new_file_path' => (string) ($document->file_path ?? ''),
            ])
            ->log('Teacher document updated');

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Dokumen guru berhasil diperbarui.');
    }

    public function destroy(Request $request, Teacher $teacher, TeacherDocument $document)
    {
        abort_unless((int) $document->teacher_id === (int) $teacher->id, 404);
        $this->authorize('uploadDocument', $teacher);

        $props = [
            'document_id' => (int) $document->id,
            'teacher_id' => (int) $teacher->id,
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
            ->event('teacher_document_deleted')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties($props)
            ->log('Teacher document deleted');

        return back()->with('success', 'Dokumen guru berhasil dihapus.');
    }
}
