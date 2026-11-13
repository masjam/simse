<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Requests\Teacher\StoreTeachingJournalRequest;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class JurnalController extends BaseTeacherController
{
    public function index(Request $request): Response
    {
        $teacher = $this->getTeacher();
        $teacherId = $teacher?->id;

        $selectedClassId = $request->query('class_id', '');
        $selectedSubjectId = $request->query('subject_id', '');
        $selectedWeek = $request->query('week', Carbon::now()->format('o-\WW'));

        $query = TeachingJournal::with(['subject', 'studentClass', 'teachingSchedule'])
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($selectedClassId !== '', fn ($q) => $q->where('student_class_id', $selectedClassId))
            ->when($selectedSubjectId !== '', fn ($q) => $q->where('subject_id', $selectedSubjectId));

        if ($selectedWeek !== '' && preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)) {
            $weekStart = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $query->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        $journals = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TeachingJournal $journal): array {
                return [
                    'id' => $journal->id,
                    'student_class_id' => $journal->student_class_id,
                    'class_name' => $journal->studentClass?->name ?? '-',
                    'subject_id' => $journal->subject_id,
                    'subject_name' => $journal->subject?->name ?? '-',
                    'teaching_schedule_id' => $journal->teaching_schedule_id,
                    'date' => $journal->date?->format('Y-m-d') ?? '',
                    'date_formatted' => $journal->date?->locale('id')->translatedFormat('l, d M Y') ?? '-',
                    'week_key' => $journal->date?->format('o-\WW') ?? '',
                    'meeting_number' => $journal->meeting_number,
                    'topic' => $journal->topic,
                    'summary' => $journal->summary,
                    'activities' => $journal->activities,
                    'notes' => $journal->notes,
                ];
            });

        $classes = StudentClass::orderBy('name')->get(['id', 'name']);
        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        $schedules = TeachingSchedule::with(['subject', 'studentClass'])
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('day')
            ->orderBy('time_start')
            ->get()
            ->map(fn (TeachingSchedule $s): array => [
                'id' => $s->id,
                'student_class_id' => $s->student_class_id,
                'subject_id' => $s->subject_id,
                'label' => ($s->day_name).' • '.substr((string) $s->time_start, 0, 5).' — '.($s->subject?->name ?? '-').' ('.($s->studentClass?->name ?? '-').')',
            ]);

        return Inertia::render('Teacher/Jurnal', [
            'journals' => $journals,
            'classes' => $classes,
            'subjects' => $subjects,
            'schedules' => $schedules,
            'filters' => [
                'class_id' => $selectedClassId,
                'subject_id' => $selectedSubjectId,
                'week' => $selectedWeek,
            ],
        ]);
    }

    public function store(StoreTeachingJournalRequest $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        abort_if(! $teacher, 403, 'Profil guru tidak ditemukan.');

        $validated = $request->validated();

        TeachingJournal::create([
            'teacher_id' => $teacher->id,
            'student_class_id' => $validated['student_class_id'],
            'subject_id' => $validated['subject_id'],
            'teaching_schedule_id' => $validated['teaching_schedule_id'] ?? null,
            'date' => $validated['date'],
            'meeting_number' => $validated['meeting_number'] ?? 1,
            'topic' => $validated['topic'],
            'summary' => $validated['summary'],
            'activities' => $validated['activities'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil disimpan dan ringkasannya tampil di Materi Mingguan siswa.');
    }

    public function update(StoreTeachingJournalRequest $request, TeachingJournal $journal): RedirectResponse
    {
        Gate::authorize('update', $journal);

        $validated = $request->validated();

        $journal->update([
            'student_class_id' => $validated['student_class_id'],
            'subject_id' => $validated['subject_id'],
            'teaching_schedule_id' => $validated['teaching_schedule_id'] ?? null,
            'date' => $validated['date'],
            'meeting_number' => $validated['meeting_number'] ?? 1,
            'topic' => $validated['topic'],
            'summary' => $validated['summary'],
            'activities' => $validated['activities'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingJournal $journal): RedirectResponse
    {
        Gate::authorize('delete', $journal);

        $journal->delete();

        return redirect()->back()->with('success', 'Jurnal mengajar berhasil dihapus.');
    }
}
