<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\NilaiExport;
use App\Imports\NilaiImport;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $data = $this->getTeacherData();
        $classes = $data['classes'];
        $subjects = $data['subjects'];

        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedSubject = $request->query('subject', $subjects[0] ?? 'Pendidikan Agama Islam dan Budi Pekerti');

        $studentClass = StudentClass::where('name', $selectedClass)->first();
        $subject = Subject::where('name', $selectedSubject)->first();

        $students = collect();
        if ($studentClass && $subject) {
            $studentModels = Student::with('user')
                ->where('student_class_id', $studentClass->id)
                ->get();

            $grades = Grade::where('subject_id', $subject->id)
                ->whereIn('student_id', $studentModels->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $students = $studentModels->map(function ($s) use ($grades) {
                $studentGrades = $grades->get($s->id, collect());

                $tugas = [];
                for ($i = 1; $i <= 4; $i++) {
                    $g = $studentGrades->firstWhere('name', 'T'.$i);
                    $tugas[] = $g ? (float) $g->score : null;
                }

                $kuis = [];
                for ($i = 1; $i <= 2; $i++) {
                    $g = $studentGrades->firstWhere('name', 'K'.$i);
                    $kuis[] = $g ? (float) $g->score : null;
                }

                $uts = $studentGrades->firstWhere('name', 'UTS')?->score;
                $uas = $studentGrades->firstWhere('name', 'UAS')?->score;

                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? 'Siswa',
                    'nisn' => $s->nisn,
                    'tugas' => $tugas,
                    'kuis' => $kuis,
                    'uts' => $uts !== null ? (float) $uts : null,
                    'uas' => $uas !== null ? (float) $uas : null,
                ];
            });
        }

        return Inertia::render('Teacher/Nilai', [
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClass' => $selectedClass,
            'selectedSubject' => $selectedSubject,
            'students' => $students,
        ]);
    }

    public function store(Request $request): mixed
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            return response()->json(['error' => 'Teacher record not found'], 404);
        }

        $validated = $request->validate([
            'class_name' => 'required|string',
            'subject_name' => 'required|string',
            'type' => 'required|string|in:tugas,kuis,uts,uas',
            'students' => 'required|array',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();
        if (! $subject) {
            return redirect()->back()->with('error', 'Mata pelajaran tidak ditemukan.');
        }

        $typeMap = ['tugas' => 'Tugas', 'kuis' => 'Kuis', 'uts' => 'UTS', 'uas' => 'UAS'];
        $dbType = $typeMap[$validated['type']] ?? 'Tugas';

        foreach ($validated['students'] as $student) {
            $studentId = $student['id'];

            if ($validated['type'] === 'uts' || $validated['type'] === 'uas') {
                $score = $student[$validated['type']] ?? null;
                if ($score !== null && $score !== '') {
                    Grade::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'type' => $dbType,
                            'name' => strtoupper($validated['type']),
                        ],
                        ['score' => $score]
                    );
                }
            } else {
                $scores = $student[$validated['type']] ?? [];
                foreach ($scores as $index => $score) {
                    if ($score !== null && $score !== '') {
                        $prefix = $validated['type'] === 'tugas' ? 'T' : 'K';
                        $name = $prefix.($index + 1);
                        Grade::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'subject_id' => $subject->id,
                                'teacher_id' => $teacher->id,
                                'type' => $dbType,
                                'name' => $name,
                            ],
                            ['score' => $score]
                        );
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Nilai '.strtoupper($validated['type']).' berhasil disimpan!');
    }

    public function export(Request $request): mixed
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            abort(403);
        }

        $className = $request->query('class_name', '1 Brilliant');
        $subjectName = $request->query('subject_name', 'Pendidikan Agama Islam dan Budi Pekerti');

        return Excel::download(
            new NilaiExport($className, $subjectName, $teacher->id),
            'Nilai_'.$className.'_'.$subjectName.'.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'subject_name' => 'required|string',
        ]);

        Excel::import(
            new NilaiImport($teacher->id, $request->subject_name),
            $request->file('file')
        );

        return redirect()->back()->with('success', 'Nilai berhasil diimpor!');
    }

    public function raport(Request $request): Response
    {
        $data = $this->getTeacherData();
        $teacher = $this->getTeacher();
        $classes = $data['classes'];
        $selectedClass = $request->query('class', $classes[0] ?? '1 Brilliant');
        $selectedSemester = $request->query('semester', 'Ganjil 2026/2027');

        $studentClass = StudentClass::where('name', $selectedClass)->first();
        $students = collect();

        if ($studentClass) {
            $studentModels = Student::with([
                'user',
                'grades',
                'achievements' => fn ($query) => $query
                    ->where('status', 'Disetujui')
                    ->orderByDesc('year')
                    ->orderByDesc('created_at'),
            ])->where('student_class_id', $studentClass->id)->get();

            $students = $studentModels->map(function ($s) {
                $grades = $s->grades;
                $tugasAvg = $grades->where('type', 'Tugas')->avg('score') ?? 85;
                $kuisAvg = $grades->where('type', 'Kuis')->avg('score') ?? 85;
                $utsScore = $grades->where('name', 'UTS')->first()?->score ?? 85;
                $uasScore = $grades->where('name', 'UAS')->first()?->score ?? $utsScore;

                $final = round(($tugasAvg * 0.3) + ($kuisAvg * 0.2) + ($utsScore * 0.25) + ($uasScore * 0.25), 1);

                $predikat = 'B';
                $catatan = 'Menunjukkan pemahaman yang baik dan aktif dalam kegiatan pembelajaran.';
                if ($final >= 90) {
                    $predikat = 'A';
                    $catatan = 'Sangat baik dalam memahami materi pembelajaran, berakhlak mulia dan konsisten berprestasi.';
                } elseif ($final >= 80) {
                    $predikat = 'B+';
                    $catatan = 'Baik dalam penguasaan materi, tekun mengerjakan tugas dan santun kepada guru.';
                } elseif ($final >= 70) {
                    $predikat = 'C+';
                    $catatan = 'Cukup memahami materi, perlu bimbingan tambahan dalam pemahaman konsep.';
                } else {
                    $predikat = 'D';
                    $catatan = 'Perlu pendampingan intensif dan mengikuti program remedial.';
                }

                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? 'Siswa',
                    'nisn' => $s->nisn,
                    'nilaiAkhir' => $final,
                    'predikat' => $predikat,
                    'catatan' => $catatan,
                    'achievements' => $s->achievements->map(fn ($achievement): array => [
                        'id' => $achievement->id,
                        'name' => $achievement->name,
                        'category' => $achievement->category ?? 'Umum',
                        'level' => $achievement->level,
                        'year' => $achievement->year,
                        'organizer' => $achievement->organizer ?? '-',
                        'description' => $achievement->description,
                    ])->values()->all(),
                ];
            });
        }

        return Inertia::render('Teacher/Raport', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedSemester' => $selectedSemester,
            'students' => $students,
            'teacher' => [
                'name' => Auth::user()?->name ?? 'Siti Guru, S.Pd.I',
                'nip' => $teacher?->nip ?? '198407152010012015',
            ],
        ]);
    }
}
