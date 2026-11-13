<?php

namespace App\Http\Controllers\Student;

use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LmsController extends BaseStudentController
{
    public function index(Request $request): Response
    {
        $student = $this->resolveStudent();
        $selectedSubjectId = $request->query('subject_id', '');

        $lessonsQuery = Lesson::with([
            'subject',
            'teacher.user',
            'assignments.submissions' => fn ($query) => $query->where('student_id', $student->id),
        ])
            ->where('student_class_id', $student->student_class_id)
            ->orderBy('order');

        $allClassLessons = (clone $lessonsQuery)->get();

        $subjects = $allClassLessons
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]);

        if ($selectedSubjectId !== '') {
            $lessonsQuery->where('subject_id', $selectedSubjectId);
        }

        $lessons = $lessonsQuery->get()->map(function (Lesson $lesson): array {
            $hasPrivateFile = $lesson->attachment && Storage::disk('local')->exists($lesson->attachment);

            return [
                'id' => $lesson->id,
                'order' => $lesson->order,
                'title' => $lesson->title,
                'content' => $lesson->content,
                'subject' => $lesson->subject?->name ?? '-',
                'subject_id' => $lesson->subject_id,
                'teacher' => $lesson->teacher?->user?->name ?? '-',
                'attachment' => $lesson->attachment,
                'has_downloadable_file' => $hasPrivateFile,
                'attachment_url' => $lesson->attachment ? route('siswa.lms.attachment', $lesson->id) : null,
                'video_url' => $lesson->video_url,
                'date' => $lesson->created_at?->format('d M Y') ?? '-',
                'assignments' => $lesson->assignments->map(function (Assignment $assignment): array {
                    $submission = $assignment->submissions->first();

                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'description' => $assignment->description,
                        'due_date' => $assignment->due_date->format('d M Y, H:i'),
                        'is_overdue' => $assignment->due_date->isPast(),
                        'max_score' => $assignment->max_score,
                        'submission' => $submission ? [
                            'id' => $submission->id,
                            'text_answer' => $submission->text_answer,
                            'has_file' => (bool) $submission->file_path,
                            'file_url' => $submission->file_path ? route('siswa.submissions.download', $submission->id) : null,
                            'score' => $submission->score,
                            'feedback' => $submission->feedback,
                            'submitted_at' => $submission->submitted_at?->format('d M Y, H:i') ?? '-',
                        ] : null,
                    ];
                })->values(),
            ];
        })->values();

        $submissions = Submission::where('student_id', $student->id)->get();
        $totalSubmissions = $submissions->count();
        $totalXp = (int) $submissions->sum('score');
        $highScoreCount = $submissions->where('score', '>=', 90)->count();

        $level = match (true) {
            $totalXp >= 500 => ['level' => 5, 'title' => 'Cendekiawan Belia Mendunia', 'icon' => '👑'],
            $totalXp >= 300 => ['level' => 4, 'title' => 'Pendekar Cilik Al Mujahidin', 'icon' => '🌟'],
            $totalXp >= 150 => ['level' => 3, 'title' => 'Bintang Kelas', 'icon' => '⭐'],
            $totalXp >= 50 => ['level' => 2, 'title' => 'Santri Rajin', 'icon' => '📚'],
            default => ['level' => 1, 'title' => 'Penuntut Ilmu Pemula', 'icon' => '🌱'],
        };

        $badges = [
            [
                'name' => 'Langkah Pertama',
                'desc' => 'Mengumpulkan tugas perdana',
                'icon' => '🎯',
                'unlocked' => $totalSubmissions >= 1,
            ],
            [
                'name' => 'Kolektor Nilai A',
                'desc' => 'Meraih nilai 90 ke atas',
                'icon' => '💎',
                'unlocked' => $highScoreCount >= 1,
            ],
            [
                'name' => 'Santri Tekun',
                'desc' => 'Menyelesaikan 3 tugas pembelajaran',
                'icon' => '🔥',
                'unlocked' => $totalSubmissions >= 3,
            ],
            [
                'name' => 'Bintang Mujahidin',
                'desc' => 'Mengumpulkan lebih dari 300 Poin XP',
                'icon' => '🏆',
                'unlocked' => $totalXp >= 300,
            ],
        ];

        return Inertia::render('Student/LMS', [
            'student' => $this->formatStudentHeader($student),
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'lessons' => $lessons,
            'gamification' => [
                'total_xp' => $totalXp,
                'total_submissions' => $totalSubmissions,
                'level' => $level,
                'badges' => $badges,
            ],
        ]);
    }

    public function downloadLessonAttachment(Lesson $lesson): StreamedResponse
    {
        Gate::authorize('view', $lesson);

        if (! $lesson->attachment || ! Storage::disk('local')->exists($lesson->attachment)) {
            abort(404, 'Lampiran materi tidak ditemukan.');
        }

        return Storage::disk('local')->download($lesson->attachment);
    }

    public function submitAssignment(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('submit', $assignment);

        $student = $this->resolveStudent();
        $validated = $request->validated();

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $filePath = $existing?->file_path;

        if ($request->hasFile('file')) {
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            $filePath = $request->file('file')->store('submissions', 'local');
        }

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'text_answer' => $validated['text_answer'] ?? $existing?->text_answer,
                'file_path' => $filePath,
                'submitted_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan ke guru.');
    }

    public function downloadSubmissionFile(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);

        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            abort(404, 'File pengumpulan tugas tidak ditemukan.');
        }

        return Storage::disk('local')->download($submission->file_path);
    }
}
