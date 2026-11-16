<?php

namespace App\Http\Controllers\Teacher;

use App\Imports\ExamQuestionsImport;
use App\Models\Exam;
use App\Models\ExamStudentTarget;
use App\Models\Option;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class CbtController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $students = Student::with('user', 'studentClass')
            ->orderBy('nis')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->name ?? 'Siswa',
                'nis' => $s->nis,
                'class' => $s->studentClass?->name ?? '-',
            ]);

        $exams = Exam::with(['subject', 'questions.options', 'attempts', 'targets.student.user'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get()
            ->map(function ($e) {
                $status = 'scheduled';
                $now = Carbon::now();
                if ($e->is_active && $now->between($e->start_time, $e->end_time)) {
                    $status = 'active';
                } elseif (! $e->is_active) {
                    $status = 'draft';
                } elseif ($now->gt($e->end_time)) {
                    $status = 'completed';
                }

                $avgScore = $e->attempts->whereNotNull('score')->avg('score');
                $pendingEssayCount = $e->attempts->where('needs_manual_grading', true)->count();

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'subject' => $e->subject?->name ?? 'Mata Pelajaran',
                    'class' => $e->is_remedial ? 'Remedial ('.$e->targets->count().' Siswa)' : 'Semua Rombel',
                    'date' => Carbon::parse($e->start_time)->format('d M Y'),
                    'time' => Carbon::parse($e->start_time)->format('H:i').' - '.Carbon::parse($e->end_time)->format('H:i'),
                    'duration' => $e->duration_minutes,
                    'totalQuestions' => $e->questions->count(),
                    'status' => $status,
                    'isActive' => $e->is_active,
                    'scoringRule' => $e->scoring_rule,
                    'randomizeQuestions' => $e->randomize_questions,
                    'randomizeOptions' => $e->randomize_options,
                    'isRemedial' => $e->is_remedial,
                    'remedialStudents' => $e->targets->map(fn ($t) => [
                        'id' => $t->student_id,
                        'name' => $t->student?->user?->name,
                    ]),
                    'participants' => $e->attempts->count(),
                    'finishedCount' => $e->attempts->whereNotNull('finished_at')->count(),
                    'pendingEssayCount' => $pendingEssayCount,
                    'maxViolations' => $e->max_violations,
                    'avgScore' => $avgScore ? round($avgScore, 1) : null,
                    'questions' => $e->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'type' => $q->type,
                            'content' => $q->content,
                            'marks' => $q->marks,
                            'image_url' => $q->image_path ? route('cbt.image', ['path' => basename($q->image_path)]) : null,
                            'metadata' => $q->metadata,
                            'options' => $q->options->map(fn ($o) => [
                                'id' => $o->id,
                                'content' => $o->content,
                                'pair_match' => $o->pair_match,
                                'image_url' => $o->image_path ? route('cbt.image', ['path' => basename($o->image_path)]) : null,
                                'is_correct' => (bool) $o->is_correct,
                            ]),
                        ];
                    }),
                ];
            });

        return Inertia::render('Teacher/CBT', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'students' => $students,
            'exams' => $exams,
        ]);
    }

    public function storeExam(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_name' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5',
            'max_violations' => 'required|integer|min:1|max:10',
            'scoring_rule' => 'nullable|string|in:standard,partial',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'is_remedial' => 'boolean',
            'remedial_student_ids' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();

        $exam = Exam::create([
            'teacher_id' => $teacher?->id ?? Teacher::value('id'),
            'subject_id' => $subject?->id ?? Subject::value('id'),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'max_violations' => $validated['max_violations'],
            'scoring_rule' => $validated['scoring_rule'] ?? 'standard',
            'randomize_questions' => $request->boolean('randomize_questions', true),
            'randomize_options' => $request->boolean('randomize_options', true),
            'is_remedial' => $request->boolean('is_remedial', false),
            'is_active' => true,
        ]);

        if ($exam->is_remedial && ! empty($validated['remedial_student_ids'])) {
            foreach ($validated['remedial_student_ids'] as $studentId) {
                ExamStudentTarget::create([
                    'exam_id' => $exam->id,
                    'student_id' => $studentId,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Paket ujian CBT baru berhasil dijadwalkan!');
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Pilihan Ganda,Ganda Kompleks,Benar Salah,Menjodohkan,Isian Rumpang,Esai',
            'content' => 'required|string',
            'marks' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array',
            'options.*.content' => 'nullable|string',
            'options.*.pair_match' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cbt_images', 'local');
        }

        $question = Question::create([
            'exam_id' => $exam->id,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'marks' => $validated['marks'],
            'image_path' => $imagePath,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        if (! empty($validated['options'])) {
            foreach ($validated['options'] as $idx => $opt) {
                $optImagePath = null;
                if ($request->hasFile("options.{$idx}.image")) {
                    $optImagePath = $request->file("options.{$idx}.image")->store('cbt_images', 'local');
                }

                Option::create([
                    'question_id' => $question->id,
                    'content' => $opt['content'] ?? '',
                    'pair_match' => $opt['pair_match'] ?? null,
                    'image_path' => $optImagePath,
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Butir soal berhasil ditambahkan ke paket ujian!');
    }

    public function updateQuestion(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Pilihan Ganda,Ganda Kompleks,Benar Salah,Menjodohkan,Isian Rumpang,Esai',
            'content' => 'required|string',
            'marks' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            if ($question->image_path && Storage::disk('local')->exists($question->image_path)) {
                Storage::disk('local')->delete($question->image_path);
            }
            $question->image_path = $request->file('image')->store('cbt_images', 'local');
        }

        $question->update([
            'type' => $validated['type'],
            'content' => $validated['content'],
            'marks' => $validated['marks'],
            'metadata' => $validated['metadata'] ?? $question->metadata,
        ]);

        if (isset($validated['options'])) {
            $question->options()->delete();
            foreach ($validated['options'] as $opt) {
                Option::create([
                    'question_id' => $question->id,
                    'content' => $opt['content'] ?? '',
                    'pair_match' => $opt['pair_match'] ?? null,
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Butir soal berhasil diperbarui!');
    }

    public function destroyQuestion(Question $question): RedirectResponse
    {
        if ($question->image_path && Storage::disk('local')->exists($question->image_path)) {
            Storage::disk('local')->delete($question->image_path);
        }

        $question->delete();

        return redirect()->back()->with('success', 'Butir soal berhasil dihapus.');
    }

    public function importQuestions(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $importer = new ExamQuestionsImport($exam);
        Excel::import($importer, $request->file('file'));

        $errors = $importer->getErrors();
        $count = $importer->getImportedCount();

        if (! empty($errors)) {
            return redirect()->back()->with('warning', "Berhasil mengimpor {$count} butir soal, namun terdapat peringatan:\n".implode("\n", array_slice($errors, 0, 5)));
        }

        return redirect()->back()->with('success', "Berhasil mengimpor {$count} butir soal dari berkas Excel!");
    }

    public function toggleExam(Exam $exam): RedirectResponse
    {
        $exam->update(['is_active' => ! $exam->is_active]);

        return redirect()->back()->with('success', 'Status keaktifan ujian berhasil diubah.');
    }
}
