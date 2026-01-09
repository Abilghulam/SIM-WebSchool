<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Requests\StudentDocumentStoreRequest;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $q = Student::query()
            ->visibleTo(auth()->user())
            ->with(['activeEnrollment.classroom.major']);

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                  ->orWhere('nis', 'like', "%{$s}%");
            });
        }

        $students = $q->latest()->paginate(15)->withQueryString();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        $this->authorize('create', Student::class);
        return view('students.create');
    }

    public function store(StudentStoreRequest $request)
    {
        $this->authorize('create', Student::class);

        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $student = Student::create([
                'nis' => $data['nis'],
                'full_name' => $data['full_name'],
                'gender' => $data['gender'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'religion' => $data['religion'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
                'status' => $data['status'] ?? 'aktif',
                'entry_year' => $data['entry_year'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_year_id' => $data['school_year_id'],
                'classroom_id' => $data['classroom_id'],
                'is_active' => true,
                'note' => $data['enrollment_note'] ?? null,
            ]);

            if (!empty($data['documents'])) {
                foreach ($data['documents'] as $docInput) {
                    $file = $docInput['file'] ?? null;
                    if (!$file) continue;

                    $path = $file->store("students/{$student->id}", 'public');

                    StudentDocument::create([
                        'student_id' => $student->id,
                        'document_type_id' => $docInput['document_type_id'] ?? null,
                        'title' => $docInput['title'] ?? null,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student->load([
            'enrollments.schoolYear',
            'enrollments.classroom.major',
            'documents.type',
            'activeEnrollment.classroom.major',
        ]);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $this->authorize('update', $student);

        $student->load(['activeEnrollment']);
        return view('students.edit', compact('student'));
    }

    public function update(StudentUpdateRequest $request, Student $student)
    {
        $this->authorize('update', $student);

        $data = $request->validated();

        // Batasi field yang boleh diubah wali kelas
        if (auth()->user()->isWaliKelas()) {
            $data = collect($data)->only([
                'phone', 'email', 'address',
                'father_name', 'mother_name', 'guardian_name', 'parent_phone',
            ])->toArray();
        }

        DB::transaction(function () use ($data, $student) {
            // Update biodata (yang ada di $data saja)
            $student->fill($data);
            $student->updated_by = auth()->id();
            $student->save();

            // Enrollment hanya boleh diubah Admin/Operator (wali kelas tidak akan punya field ini karena di-whitelist)
            $hasEnrollmentChange = !empty($data['school_year_id']) && !empty($data['classroom_id']);
            if ($hasEnrollmentChange) {
                StudentEnrollment::where('student_id', $student->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'school_year_id' => $data['school_year_id'],
                    ],
                    [
                        'classroom_id' => $data['classroom_id'],
                        'is_active' => true,
                        'note' => $data['enrollment_note'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('students.show', $student)->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Siswa berhasil dihapus (soft delete).');
    }

    public function storeDocument(StudentDocumentStoreRequest $request, Student $student)
    {
        $this->authorize('uploadDocument', $student);

        $file = $request->file('file');
        $path = $file->store("students/{$student->id}", 'public');

        StudentDocument::create([
            'student_id' => $student->id,
            'document_type_id' => $request->integer('document_type_id') ?: null,
            'title' => $request->string('title') ?: null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dokumen siswa berhasil diupload.');
    }
}
