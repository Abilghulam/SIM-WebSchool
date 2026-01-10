<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherStoreRequest;
use App\Http\Requests\TeacherUpdateRequest;
use App\Http\Requests\TeacherDocumentStoreRequest;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use Illuminate\Http\Request;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


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
            'user',
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

    public function createAccount(Request $request, Teacher $teacher)
    {
        $this->authorize('createAccount', $teacher);

        // pastikan belum ada akun
        if (User::query()->where('teacher_id', $teacher->id)->exists()) {
            return back()->with('warning', 'Akun untuk guru ini sudah ada.');
        }

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $username = trim((string) $teacher->nip);

        if ($username === '') {
            return back()->with('warning', 'NIP guru kosong. Tidak bisa membuat akun.');
        }

        // username unik
        if (User::query()->where('username', $username)->exists()) {
            return back()->with('warning', "Username {$username} sudah dipakai akun lain.");
        }

        // email fallback kalau kosong (wajib karena kolom users.email NOT NULL)
        $email = $data['email'] ?? ($username . '@school.local');

        // kalau email fallback ternyata sudah dipakai (misalnya ada akun lama),
        // bikin versi unik: nip+ID@school.local
        if (User::query()->where('email', $email)->exists()) {
            $email = $username . '+' . $teacher->id . '@school.local';
        }

        User::create([
            'name' => $teacher->full_name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($username), // password awal = NIP
            'role_label' => 'guru',
            'teacher_id' => $teacher->id,
            'is_active' => $data['is_active'] ?? true,
            'must_change_password' => true,
        ]);

        return back()->with(
            'success',
            "Akun login guru berhasil dibuat. Login pakai NIP (username) dan password awal NIP. Wajib ganti password saat login pertama."
        );
    }

}
