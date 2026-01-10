<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class MajorController extends BaseController
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Major::class);

        $q = Major::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $majors = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('majors.index', compact('majors'));
    }

    public function create()
    {
        $this->authorize('create', Major::class);
        return view('majors.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Major::class);

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('majors', 'code')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100', Rule::unique('majors', 'name')->whereNull('deleted_at')],
        ]);

        Major::create($data);

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Major $major)
    {
        $this->authorize('update', $major);
        return view('majors.edit', compact('major'));
    }

    public function update(Request $request, Major $major)
    {
        $this->authorize('update', $major);

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('majors', 'code')->ignore($major->id)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100', Rule::unique('majors', 'name')->ignore($major->id)->whereNull('deleted_at')],
        ]);

        $major->update($data);

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major)
    {
        $this->authorize('delete', $major);

        $major->delete();

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
