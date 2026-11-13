<?php

namespace App\Http\Controllers\Student;

use App\Models\TeachingJournal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MateriMingguanController extends BaseStudentController
{
    public function materiMingguan(Request $request): Response
    {
        $student = $this->resolveStudent();
        $selectedSubjectId = $request->query('subject_id', '');
        $selectedWeek = $request->query('week', '');

        $baseQuery = TeachingJournal::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $allClassJournals = (clone $baseQuery)->get();

        $subjects = $allClassJournals
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]);

        $availableWeeks = $allClassJournals
            ->map(function (TeachingJournal $journal): ?array {
                if (! $journal->date) {
                    return null;
                }
                $start = $journal->date->copy()->startOfWeek();
                $end = $journal->date->copy()->endOfWeek();

                return [
                    'key' => $journal->date->format('o-\WW'),
                    'label' => 'Minggu '.$journal->date->isoWeek().' ('.$start->format('d M').' - '.$end->format('d M Y').')',
                ];
            })
            ->filter()
            ->unique('key')
            ->values();

        if ($selectedSubjectId !== '') {
            $baseQuery->where('subject_id', $selectedSubjectId);
        }

        if ($selectedWeek !== '' && preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)) {
            $weekStart = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $baseQuery->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        }

        $filteredJournals = $baseQuery->get();

        $weeklyGroups = $filteredJournals
            ->groupBy(fn (TeachingJournal $journal): string => $journal->date?->format('o-\WW') ?? 'unknown')
            ->map(function ($weekItems, string $weekKey): array {
                $sampleDate = $weekItems->first()?->date ?? Carbon::now();
                $start = $sampleDate->copy()->startOfWeek();
                $end = $sampleDate->copy()->endOfWeek();
                $isCurrentWeek = $weekKey === Carbon::now()->format('o-\WW');

                $subjectDigests = $weekItems->groupBy('subject_id')->map(function ($subjectItems): array {
                    $subjectName = $subjectItems->first()?->subject?->name ?? '-';
                    $teacherName = $subjectItems->first()?->teacher?->user?->name ?? '-';

                    return [
                        'subject_id' => $subjectItems->first()->subject_id,
                        'subject_name' => $subjectName,
                        'teacher_name' => $teacherName,
                        'meetings_count' => $subjectItems->count(),
                        'topics' => $subjectItems->pluck('topic')->unique()->values()->all(),
                        'entries' => $subjectItems->sortBy('date')->map(fn (TeachingJournal $item): array => [
                            'id' => $item->id,
                            'date' => $item->date?->locale('id')->translatedFormat('l, d M Y') ?? '-',
                            'meeting_number' => $item->meeting_number,
                            'topic' => $item->topic,
                            'summary' => $item->summary,
                            'activities' => $item->activities,
                            'notes' => $item->notes,
                        ])->values()->all(),
                    ];
                })->values()->all();

                return [
                    'week_key' => $weekKey,
                    'week_label' => 'Minggu Ke-'.$sampleDate->isoWeek().' ('.$start->format('d M').' - '.$end->format('d M Y').')',
                    'is_current_week' => $isCurrentWeek,
                    'total_entries' => $weekItems->count(),
                    'subjects' => $subjectDigests,
                ];
            })
            ->values();

        return Inertia::render('Student/MateriMingguan', [
            'student' => $this->formatStudentHeader($student),
            'subjects' => $subjects,
            'weeks' => $availableWeeks,
            'filters' => [
                'subject_id' => $selectedSubjectId,
                'week' => $selectedWeek,
            ],
            'weeklyGroups' => $weeklyGroups,
        ]);
    }
}
