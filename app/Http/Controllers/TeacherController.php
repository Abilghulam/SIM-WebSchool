<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherStoreRequest;
use App\Http\Requests\TeacherUpdateRequest;
use App\Http\Requests\TeacherDocumentStoreRequest;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use Illuminate\Http\Request;
use App\Models\DocumentType;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class TeacherController extends BaseController
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Teacher::class);

        $q = Teacher::query()->visibleTo(auth()->user());

        if ($request->filled('active')) {
            $q->where('is_active', (bool) $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        $teachers = $q->latest()->paginate(15)->withQueryString();
        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        $this->authorize('create', Teacher::class);
        return view('teachers.create');
    }

    public function store(TeacherStoreRequest $request)
    {
        $this->authorize('create', Teacher::class);

        $data = $request->validated();

        Teacher::create([
            'nip' => $data['nip'],
            'full_name' => $data['full_name'],
            'gender' => $data['gender'] ?? null,
            'birth_place' => $data['birth_place'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'employment_status' => $data['employment_status'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function show(Teacher $teacher)
    {
        $this->authorize('view', $teacher);

        $teacher->load([
            'documents.type',
            'homeroomAssignments.schoolYear',
            'homeroomAssignments.classroom'
        ]);

        $documentTypes = DocumentType::query()->orderBy('name')->get();

        return view('teachers.show', compact('teacher', 'documentTypes'));
    }

    public function edit(Teacher $teacher)
    {
        $this->authorize('update', $teacher);
        return view('teachers.edit', compact('teacher'));
    }

    public function update(TeacherUpdateRequest $request, Teacher $teacher)
    {
        $this->authorize('update', $teacher);

        $data = $request->validated();

        // Guru/WaliKelas hanya boleh update field tertentu (konsep "self-edit")
        if (auth()->user()->isGuru() || auth()->user()->isWaliKelas()) {
            $data = collect($data)->only([
                'phone', 'email', 'address',
            ])->toArray();
        }

        $teacher->fill($data);
        $teacher->updated_by = auth()->id();
        $teacher->save();

        return redirect()->route('teachers.show', $teacher)->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Guru berhasil dihapus (soft delete).');
    }

    public function storeDocument(TeacherDocumentStoreRequest $request, Teacher $teacher)
    {
        $this->authorize('uploadDocument', $teacher);

        $file = $request->file('file');
        $path = $file->store("teachers/{$teacher->id}", 'public');

        TeacherDocument::create([
            'teacher_id' => $teacher->id,
            'document_type_id' => $request->integer('document_type_id') ?: null,
            'title' => $request->string('title') ?: null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dokumen guru berhasil diupload.');
    }
}
