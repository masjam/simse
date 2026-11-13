<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $exams = Exam::with(['subject', 'questions.options', 'attempts'])
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

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'subject' => $e->subject?->name ?? 'Mata Pelajaran',
                    'class' => 'Semua Rombel',
                    'date' => Carbon::parse($e->start_time)->format('d M Y'),
                    'time' => Carbon::parse($e->start_time)->format('H:i').' - '.Carbon::parse($e->end_time)->format('H:i'),
                    'duration' => $e->duration_minutes,
                    'totalQuestions' => $e->questions->count(),
                    'status' => $status,
                    'isActive' => $e->is_active,
                    'participants' => $e->attempts->count(),
                    'maxViolations' => $e->max_violations,
                    'avgScore' => $avgScore ? round($avgScore, 1) : null,
                    'questions' => $e->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'type' => $q->type,
                            'content' => $q->content,
                            'marks' => $q->marks,
                            'options' => $q->options->map(fn ($o) => ['content' => $o->content, 'is_correct' => $o->is_correct]),
                        ];
                    }),
                ];
            });

        return Inertia::render('Teacher/CBT', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
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
            'duration_minutes' => 'required|integer|min:10',
            'max_violations' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();

        Exam::create([
            'teacher_id' => $teacher?->id ?? Teacher::value('id'),
            'subject_id' => $subject?->id ?? Subject::value('id'),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'max_violations' => $validated['max_violations'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Ujian CBT baru berhasil dijadwalkan!');
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Pilihan Ganda,Benar Salah,Esai,Menjodohkan',
            'content' => 'required|string',
            'marks' => 'required|integer|min:1',
            'options' => 'nullable|array',
        ]);

        $q = Question::create([
            'exam_id' => $exam->id,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'marks' => $validated['marks'],
        ]);

        if (! empty($validated['options'])) {
            foreach ($validated['options'] as $opt) {
                Option::create([
                    'question_id' => $q->id,
                    'content' => $opt['content'],
                    'is_correct' => $opt['is_correct'] ?? false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Soal berhasil ditambahkan ke bank soal ujian!');
    }

    public function toggleExam(Exam $exam): RedirectResponse
    {
        $exam->update(['is_active' => ! $exam->is_active]);

        return redirect()->back()->with('success', 'Status ujian berhasil diperbarui.');
    }
}
