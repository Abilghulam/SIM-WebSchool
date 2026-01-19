<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentDocumentStoreRequest;
use App\Models\Student;
use App\Models\StudentDocument;
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
