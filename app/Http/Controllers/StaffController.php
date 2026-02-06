<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class StaffController extends BaseController
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Staff::class);

        $q = Staff::query()->visibleTo($request->user());

        // ===== Filters =====
        if ($request->filled('active')) {
            $q->where('is_active', (bool) $request->boolean('active'));
        }

        if ($request->filled('gender')) {
            $q->where('gender', $request->string('gender')->toString()); // L / P
        }

        if ($request->filled('religion')) {
            $q->where('religion', $request->string('religion')->toString());
        }

        if ($request->filled('marital_status')) {
            $q->where('marital_status', $request->string('marital_status')->toString());
        }

        if ($request->filled('employment_status')) {
            $q->where('employment_status', $request->string('employment_status')->toString());
        }

        // akun login: has / none
        if ($request->filled('account')) {
            $acc = $request->string('account')->toString();
            if ($acc === 'has') {
                $q->whereHas('user');
            } elseif ($acc === 'none') {
                $q->whereDoesntHave('user');
            }
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        $staffs = $q->latest()->paginate(15)->withQueryString();

        // ===== Options (DB-driven) =====
        $activeOptions = [
            '' => 'Semua',
            '1' => 'Aktif',
            '0' => 'Nonaktif',
        ];

        $genderOptions = [
            '' => 'Semua',
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
        ];

        // enum (kalau konsisten sama teachers)
        $religionOptions = [
            '' => 'Semua',
            'Islam' => 'Islam',
            'Kristen' => 'Kristen',
            'Katolik' => 'Katolik',
            'Hindu' => 'Hindu',
            'Buddha' => 'Buddha',
            'Konghucu' => 'Konghucu',
            'Lainnya' => 'Lainnya',
        ];

        $maritalOptions = [
            '' => 'Semua',
            'Kawin' => 'Kawin',
            'Belum Kawin' => 'Belum Kawin',
            'Cerai Hidup' => 'Cerai Hidup',
            'Cerai Mati' => 'Cerai Mati',
        ];

        $accountOptions = [
            '' => 'Semua',
            'has' => 'Punya Akun',
            'none' => 'Belum Punya Akun',
        ];

        // ambil dari DB (distinct)
        $employmentOptions = Staff::query()
            ->whereNotNull('employment_status')
            ->where('employment_status', '!=', '')
            ->distinct()
            ->orderBy('employment_status')
            ->pluck('employment_status', 'employment_status')
            ->prepend('Semua', '');

        return view('staff.index', compact(
            'staffs',
            'activeOptions',
            'genderOptions',
            'religionOptions',
            'maritalOptions',
            'accountOptions',
            'employmentOptions',
        ));
    }

    public function create()
    {
        $this->authorize('create', Staff::class);
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Staff::class);

        $data = $request->validate([
            'nip' => ['required','string','max:30', Rule::unique('staff','nip')],
            'full_name' => ['required','string','max:150'],

            'gender' => ['nullable', Rule::in(['L','P'])],
            'birth_place' => ['nullable','string','max:100'],
            'birth_date' => ['nullable','date'],

            'religion' => ['nullable', Rule::in(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya'])],
            'religion_other' => ['nullable','string','max:80'],
            'marital_status' => ['nullable', Rule::in(['Kawin','Belum Kawin','Cerai Hidup','Cerai Mati'])],

            'phone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'address' => ['nullable','string'],
            'employment_status' => ['nullable','string','max:50'],
            'is_active' => ['nullable','boolean'],
        ]);

        if (($data['religion'] ?? null) !== 'Lainnya') {
            $data['religion_other'] = null;
        }

        Staff::create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('staff.index')->with('success', 'TAS berhasil ditambahkan.');
    }

    public function show(Staff $staff)
    {
        $this->authorize('view', $staff);

        $staff->load(['user', 'documents.uploadedBy', 'documents.type']);

        $documentTypes = \App\Models\DocumentType::query()
            ->where('for', 'staff')
            ->orderBy('name')
            ->get();

        return view('staff.show', compact('staff', 'documentTypes'));
    }

    public function edit(Staff $staff)
    {
        $this->authorize('update', $staff);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorize('update', $staff);

        $data = $request->validate([
            'nip' => ['required','string','max:30', Rule::unique('staff','nip')->ignore($staff->id)],
            'full_name' => ['required','string','max:150'],

            'gender' => ['nullable', Rule::in(['L','P'])],
            'birth_place' => ['nullable','string','max:100'],
            'birth_date' => ['nullable','date'],

            'religion' => ['nullable', Rule::in(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya'])],
            'religion_other' => ['nullable','string','max:80'],
            'marital_status' => ['nullable', Rule::in(['Kawin','Belum Kawin','Cerai Hidup','Cerai Mati'])],

            'phone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'address' => ['nullable','string'],
            'employment_status' => ['nullable','string','max:50'],
            'is_active' => ['nullable','boolean'],
        ]);

        if (($data['religion'] ?? null) !== 'Lainnya') {
            $data['religion_other'] = null;
        }

        $staff->fill($data);
        $staff->updated_by = $request->user()->id;
        $staff->save();

        return redirect()->route('staff.show', $staff)->with('success', 'Data TAS berhasil diperbarui.');
    }

    public function destroy(Staff $staff)
    {
        $this->authorize('delete', $staff);

        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'TAS berhasil dihapus (soft delete).');
    }

    public function createAccount(Request $request, Staff $staff)
    {
        $this->authorize('createAccount', $staff);

        if (User::query()->where('staff_id', $staff->id)->exists()) {
            return back()->with('warning', 'Akun untuk TAS ini sudah ada.');
        }

        $data = $request->validate([
            'email' => ['nullable','email','max:255', Rule::unique('users','email')],
            'is_active' => ['nullable','boolean'],
        ]);

        $username = trim((string) $staff->nip);
        if ($username === '') {
            return back()->with('warning', 'NIP TAS kosong. Tidak bisa membuat akun.');
        }

        if (User::query()->where('username', $username)->exists()) {
            return back()->with('warning', "Username {$username} sudah dipakai akun lain.");
        }

        $email = $data['email'] ?? ($username . '@school.local');
        if (User::query()->where('email', $email)->exists()) {
            $email = $username . '+' . $staff->id . '@school.local';
        }

        $account = User::create([
            'name' => $staff->full_name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($username), // password awal = NIP
            'role_label' => 'operator',          // ✅ role login TAS
            'staff_id' => $staff->id,
            'is_active' => $data['is_active'] ?? true,
            'must_change_password' => true,
        ]);

        activity()
            ->useLog('domain')
            ->event('staff_account_created')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties([
                'staff_id' => (int) $staff->id,
                'staff_nip' => (string) ($staff->nip ?? ''),
                'staff_name' => (string) ($staff->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),
                'role_label' => (string) ($account->role_label ?? ''),
                'is_active' => (bool) $account->is_active,
                'must_change_password' => (bool) $account->must_change_password,
            ])
            ->log('Staff account created');

        return back()->with('success', 'Akun TAS berhasil dibuat. Username & password awal = NIP. Wajib ganti password saat login pertama.');
    }

    public function toggleAccountActive(Request $request, Staff $staff)
    {
        $this->authorize('createAccount', $staff);

        $account = $staff->user;
        if (!$account) return back()->with('warning', 'TAS ini belum memiliki akun login.');

        if ((int) $account->id === (int) $request->user()->id) {
            return back()->with('warning', 'Tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        $old = (bool) $account->is_active;

        $account->update(['is_active' => !$account->is_active]);

        activity()
            ->useLog('domain')
            ->event('staff_account_toggled')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties([
                'staff_id' => (int) $staff->id,
                'staff_nip' => (string) ($staff->nip ?? ''),
                'staff_name' => (string) ($staff->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_is_active' => $old,
                'new_is_active' => (bool) $account->is_active,
            ])
            ->log('Staff account toggled');

        return back()->with('success', $account->is_active ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.');
    }

    public function forceChangePassword(Request $request, Staff $staff)
    {
        $this->authorize('createAccount', $staff);

        $account = $staff->user;
        if (!$account) return back()->with('warning', 'TAS ini belum memiliki akun login.');

        $old = (bool) $account->must_change_password;

        $account->update(['must_change_password' => true]);

        activity()
            ->useLog('domain')
            ->event('staff_account_force_change_password')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties([
                'staff_id' => (int) $staff->id,
                'staff_nip' => (string) ($staff->nip ?? ''),
                'staff_name' => (string) ($staff->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_must_change_password' => $old,
                'new_must_change_password' => (bool) $account->must_change_password,
            ])
            ->log('Staff account force change password');

        return back()->with('success', 'Berhasil: TAS akan diminta ganti password saat login berikutnya.');
    }

    public function resetAccountPassword(Request $request, Staff $staff)
    {
        $this->authorize('createAccount', $staff);

        $account = $staff->user;
        if (!$account) return back()->with('warning', 'TAS ini belum memiliki akun login.');

        $data = $request->validate([
            'new_password' => ['required','string','min:6','confirmed'],
        ]);

        $oldMustChange = (bool) $account->must_change_password;

        $account->update([
            'password' => Hash::make($data['new_password']),
            'must_change_password' => true,
        ]);

        activity()
            ->useLog('domain')
            ->event('staff_account_password_reset')
            ->causedBy($request->user())
            ->performedOn($staff)
            ->withProperties([
                'staff_id' => (int) $staff->id,
                'staff_nip' => (string) ($staff->nip ?? ''),
                'staff_name' => (string) ($staff->full_name ?? ''),

                'user_id' => (int) $account->id,
                'username' => (string) ($account->username ?? ''),
                'email' => (string) ($account->email ?? ''),

                'old_must_change_password' => $oldMustChange,
                'new_must_change_password' => (bool) $account->must_change_password,
                'password_reset' => true,
            ])
            ->log('Staff account password reset');

        return back()->with('success', 'Password berhasil direset. TAS wajib mengganti password saat login berikutnya.');
    }
}
