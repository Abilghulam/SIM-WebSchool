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
            'documents.uploadedBy',
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

    public function createAccount(Request $request, Teacher $teacher)
    {
        $this->authorize('createAccount', $teacher);

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

        if (User::query()->where('username', $username)->exists()) {
            return back()->with('warning', "Username {$username} sudah dipakai akun lain.");
        }

        $email = $data['email'] ?? ($username . '@school.local');
        if (User::query()->where('email', $email)->exists()) {
            $email = $username . '+' . $teacher->id . '@school.local';
        }

        $account = User::create([
            'name' => $teacher->full_name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($username),
            'role_label' => 'guru',
            'teacher_id' => $teacher->id,
            'is_active' => $data['is_active'] ?? true,
            'must_change_password' => true,
        ]);

        activity()
            ->useLog('domain')
            ->event('teacher_account_created')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties([
                'teacher_id' => (int) $teacher->id,
                'teacher_nip' => (string) ($teacher->nip ?? ''),
                'teacher_name' => (string) ($teacher->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),
                'role_label' => (string) ($account->role_label ?? ''),
                'is_active' => (bool) $account->is_active,
                'must_change_password' => (bool) $account->must_change_password,
            ])
            ->log('Teacher account created');

        return back()->with(
            'success',
            "Akun login guru berhasil dibuat. Login pakai NIP (username) dan password awal NIP. Wajib ganti password saat login pertama."
        );
    }

    public function toggleAccountActive(Request $request, Teacher $teacher)
    {
        $this->authorize('createAccount', $teacher);

        $account = $teacher->user;
        if (!$account) {
            return back()->with('warning', 'Guru ini belum memiliki akun login.');
        }

        if ((int) $account->id === (int) auth()->id()) {
            return back()->with('warning', 'Tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        $old = (bool) $account->is_active;

        $account->update([
            'is_active' => !$account->is_active,
        ]);

        activity()
            ->useLog('domain')
            ->event('teacher_account_toggled')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties([
                'teacher_id' => (int) $teacher->id,
                'teacher_nip' => (string) ($teacher->nip ?? ''),
                'teacher_name' => (string) ($teacher->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_is_active' => $old,
                'new_is_active' => (bool) $account->is_active,
            ])
            ->log('Teacher account toggled');

        return back()->with(
            'success',
            $account->is_active ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.'
        );
    }

    public function forceChangePassword(Request $request, Teacher $teacher)
    {
        $this->authorize('createAccount', $teacher);

        $account = $teacher->user;
        if (!$account) {
            return back()->with('warning', 'Guru ini belum memiliki akun login.');
        }

        $old = (bool) $account->must_change_password;

        $account->update([
            'must_change_password' => true,
        ]);

        activity()
            ->useLog('domain')
            ->event('teacher_account_force_change_password')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties([
                'teacher_id' => (int) $teacher->id,
                'teacher_nip' => (string) ($teacher->nip ?? ''),
                'teacher_name' => (string) ($teacher->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_must_change_password' => $old,
                'new_must_change_password' => (bool) $account->must_change_password,
            ])
            ->log('Teacher account force change password');

        return back()->with('success', 'Berhasil: guru akan diminta ganti password saat login berikutnya.');
    }

    public function resetAccountPassword(Request $request, Teacher $teacher)
    {
        $this->authorize('createAccount', $teacher);

        $account = $teacher->user;
        if (!$account) {
            return back()->with('warning', 'Guru ini belum memiliki akun login.');
        }

        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $oldMustChange = (bool) $account->must_change_password;

        $account->update([
            'password' => Hash::make($data['new_password']),
            'must_change_password' => true,
        ]);

        activity()
            ->useLog('domain')
            ->event('teacher_account_password_reset')
            ->causedBy($request->user())
            ->performedOn($teacher)
            ->withProperties([
                'teacher_id' => (int) $teacher->id,
                'teacher_nip' => (string) ($teacher->nip ?? ''),
                'teacher_name' => (string) ($teacher->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_must_change_password' => $oldMustChange,
                'new_must_change_password' => (bool) $account->must_change_password,
                'password_reset' => true,
            ])
            ->log('Teacher account password reset');

        return back()->with(
            'success',
            'Password berhasil direset. Guru wajib mengganti password saat login berikutnya.'
        );
    }

}
