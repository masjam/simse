<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LmsController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $lessons = Lesson::with(['subject', 'studentClass', 'assignments'])
            ->where('teacher_id', $teacherId)
            ->orderBy('order')
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'bab' => $l->order,
                    'title' => $l->title,
                    'description' => $l->content,
                    'subject' => $l->subject?->name ?? '-',
                    'class' => $l->studentClass?->name ?? '-',
                    'type' => $l->video_url ? 'video' : 'pdf',
                    'fileSize' => '2.4 MB',
                    'attachment' => $l->attachment,
                    'attachmentUrl' => $l->attachment ? route('guru.lms.attachment', $l->id) : null,
                    'video_url' => $l->video_url,
                    'totalTopics' => $l->assignments->count(),
                    'completedTopics' => $l->assignments->count(),
                    'status' => 'published',
                    'date' => $l->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Teacher/LMS', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'lessons' => $lessons,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if (! $teacher) {
            return redirect()->back()->with('error', 'Profil guru tidak ditemukan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_name' => 'required|string',
            'class_name' => 'required|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240',
            'order' => 'nullable|integer',
        ]);

        $subject = Subject::where('name', $validated['subject_name'])->first();
        $studentClass = StudentClass::where('name', $validated['class_name'])->first();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('materials', 'private');
        }

        Lesson::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject?->id ?? Subject::value('id'),
            'student_class_id' => $studentClass?->id ?? StudentClass::value('id'),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'attachment' => $attachmentPath,
            'video_url' => $validated['video_url'] ?? null,
            'order' => $validated['order'] ?? (Lesson::where('teacher_id', $teacher->id)->max('order') + 1),
        ]);

        return redirect()->back()->with('success', 'Materi ajar berhasil diterbitkan!');
    }

    public function downloadAttachment(Lesson $lesson): StreamedResponse
    {
        $teacher = $this->getTeacher();
        if ($lesson->teacher_id !== $teacher?->id && ! Auth::user()?->hasRole('Super Admin')) {
            abort(403);
        }

        if (! $lesson->attachment || ! Storage::disk('private')->exists($lesson->attachment)) {
            abort(404);
        }

        return Storage::disk('private')->download($lesson->attachment);
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if ($lesson->teacher_id !== $teacher?->id && ! Auth::user()?->hasRole('Super Admin')) {
            abort(403);
        }

        if ($lesson->attachment) {
            if (Storage::disk('private')->exists($lesson->attachment)) {
                Storage::disk('private')->delete($lesson->attachment);
            }

            if (Storage::disk('public')->exists($lesson->attachment)) {
                Storage::disk('public')->delete($lesson->attachment);
            }
        }

        $lesson->delete();

        return redirect()->back()->with('success', 'Materi ajar berhasil dihapus.');
    }

    public function indexTugas(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id ?? 0;
        $data = $this->getTeacherData();

        $assignments = Assignment::with(['lesson.subject', 'lesson.studentClass.students', 'submissions.student.user'])
            ->whereHas('lesson', function ($q) use ($teacherId) {
                if ($teacherId > 0) {
                    $q->where('teacher_id', $teacherId);
                }
            })
            ->latest()
            ->get()
            ->map(function ($a) {
                $totalStudents = $a->lesson?->studentClass?->students?->count() ?? 30;
                $submittedCount = $a->submissions->count();
                $gradedCount = $a->submissions->whereNotNull('score')->count();
                $isCompleted = Carbon::parse($a->due_date)->isPast() && ($submittedCount === $gradedCount);

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject' => $a->lesson?->subject?->name ?? 'PAI',
                    'class' => $a->lesson?->studentClass?->name ?? '1 Brilliant',
                    'deadline' => Carbon::parse($a->due_date)->format('d M Y, H:i'),
                    'totalStudents' => $totalStudents,
                    'submitted' => $submittedCount,
                    'graded' => $gradedCount,
                    'status' => $isCompleted ? 'completed' : 'active',
                    'type' => 'file',
                    'submissions' => $a->submissions->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'studentName' => $sub->student?->user?->name ?? 'Siswa',
                            'nisn' => $sub->student?->nisn ?? '-',
                            'submittedAt' => $sub->submitted_at ? Carbon::parse($sub->submitted_at)->diffForHumans() : '-',
                            'textAnswer' => $sub->text_answer,
                            'filePath' => $sub->file_path,
                            'score' => $sub->score,
                            'feedback' => $sub->feedback,
                        ];
                    }),
                ];
            });

        $lessons = Lesson::where('teacher_id', $teacherId)->get(['id', 'title']);

        return Inertia::render('Teacher/Tugas', [
            'classes' => $data['classes'],
            'subjects' => $data['subjects'],
            'lessons' => $lessons,
            'assignments' => $assignments,
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'lesson_id' => 'nullable|exists:lessons,id',
            'class_name' => 'nullable|string',
            'subject_name' => 'nullable|string',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
        ]);

        $lessonId = $validated['lesson_id'] ?? null;
        if (! $lessonId) {
            $lesson = Lesson::where('teacher_id', $teacher?->id)->first();
            $lessonId = $lesson?->id;
        }

        if (! $lessonId) {
            return redirect()->back()->with('error', 'Silakan buat materi bab pembelajaran terlebih dahulu.');
        }

        Assignment::create([
            'lesson_id' => $lessonId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'max_score' => $validated['max_score'],
        ]);

        return redirect()->back()->with('success', 'Tugas baru berhasil dibagikan ke siswa!');
    }

    public function gradeSubmission(Request $request, Submission $submission): RedirectResponse
    {
        Gate::authorize('update', $submission);

        $teacher = $this->getTeacher();
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
        ]);

        $subjectId = $submission->assignment?->lesson?->subject_id ?? Subject::value('id');
        Grade::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'subject_id' => $subjectId,
                'teacher_id' => $teacher?->id ?? Teacher::value('id'),
                'type' => 'Tugas',
                'name' => 'T1',
            ],
            ['score' => $validated['score']]
        );

        return redirect()->back()->with('success', 'Nilai dan feedback tugas berhasil disimpan!');
    }
}
