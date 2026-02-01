<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentDocumentStoreRequest;
use App\Http\Requests\StudentDocumentUpdateRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class StudentDocumentController extends BaseController
{
    use AuthorizesRequests;

    public function store(StudentDocumentStoreRequest $request, Student $student)
    {
        $this->authorize('uploadDocument', $student);

        $file = $request->file('file');
        $path = $file->store("students/{$student->id}", 'public');

        $doc = StudentDocument::create([
            'student_id' => $student->id,
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
            ->event('student_document_uploaded')
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'document_id' => (int) $doc->id,
                'student_id' => (int) $student->id,
                'document_type_id' => $doc->document_type_id ? (int) $doc->document_type_id : null,
                'title' => (string) ($doc->title ?? ''),
                'file_name' => (string) ($doc->file_name ?? ''),
                'mime_type' => (string) ($doc->mime_type ?? ''),
                'file_size' => (int) ($doc->file_size ?? 0),
            ])
            ->log('Student document uploaded');

        return back()->with('success', 'Dokumen siswa berhasil diupload.');
    }

    public function show(Request $request, Student $student, StudentDocument $document)
    {
        abort_unless((int) $document->student_id === (int) $student->id, 404);
        $this->authorize('view', $student); // wali kelas di policy view sudah true untuk siswanya

        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);

        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function edit(Request $request, Student $student, StudentDocument $document)
    {
        abort_unless((int) $document->student_id === (int) $student->id, 404);
        $this->authorize('uploadDocument', $student);

        $documentTypes = DocumentType::query()
            ->where('for', 'student')
            ->orderBy('name')
            ->get();

        return view('students.documents.edit', compact('student', 'document', 'documentTypes'));
    }

    public function update(StudentDocumentUpdateRequest $request, Student $student, StudentDocument $document)
    {
        abort_unless((int) $document->student_id === (int) $student->id, 404);
        $this->authorize('uploadDocument', $student);

        $propsBefore = [
            'document_id' => (int) $document->id,
            'student_id' => (int) $student->id,
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
            $path = $file->store("students/{$student->id}", 'public');

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
            ->event('student_document_updated')
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties($propsBefore + [
                'new_document_type_id' => $document->document_type_id ? (int) $document->document_type_id : null,
                'new_title' => (string) ($document->title ?? ''),
                'new_file_name' => (string) ($document->file_name ?? ''),
                'new_file_path' => (string) ($document->file_path ?? ''),
            ])
            ->log('Student document updated');

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Dokumen siswa berhasil diperbarui.');
    }

    public function destroy(Request $request, Student $student, StudentDocument $document)
    {
        abort_unless((int) $document->student_id === (int) $student->id, 404);
        $this->authorize('uploadDocument', $student);

        $props = [
            'document_id' => (int) $document->id,
            'student_id' => (int) $student->id,
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
            ->event('student_document_deleted')
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties($props)
            ->log('Student document deleted');

        return back()->with('success', 'Dokumen siswa berhasil dihapus.');
    }
}
