<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Major;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class ClassroomController extends BaseController
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Classroom::class);

        $q = Classroom::query()->with('major');

        if ($search = $request->string('search')->trim()->toString()) {
            $q->where('name', 'like', "%{$search}%");
        }

        if ($majorId = $request->integer('major_id')) {
            $q->where('major_id', $majorId);
        }

        if ($grade = $request->string('grade_level')->trim()->toString()) {
            if (in_array($grade, ['10', '11', '12'], true)) {
                $q->where('grade_level', (int) $grade);
            }
        }

        $classrooms = $q->orderBy('grade_level')->orderBy('name')->paginate(15)->withQueryString();
        $majors = Major::query()->orderBy('name')->get(['id', 'name']);

        return view('classrooms.index', compact('classrooms', 'majors'));
    }

    public function create()
    {
        $this->authorize('create', Classroom::class);

        $majors = Major::query()->orderBy('name')->get(['id', 'name']);
        return view('classrooms.create', compact('majors'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Classroom::class);

        $data = $request->validate([
            'major_id' => ['nullable', 'exists:majors,id'],
            'grade_level' => ['nullable', 'integer', 'in:10,11,12'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        Classroom::create($data);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Classroom $classroom)
    {
        $this->authorize('update', $classroom);

        $majors = Major::query()->orderBy('name')->get(['id', 'name']);
        return view('classrooms.edit', compact('classroom', 'majors'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $this->authorize('update', $classroom);

        $data = $request->validate([
            'major_id' => ['nullable', 'exists:majors,id'],
            'grade_level' => ['nullable', 'integer', 'in:10,11,12'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $classroom->update($data);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
