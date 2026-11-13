<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentAcademicSummaryService
{
    /**
     * Build subject raport summary and raw grade list for a student.
     *
     * @return array{raportSubjects: Collection<int, array<string, mixed>>, detailedGrades: Collection<int, array<string, mixed>>}
     */
    public function gradesAndRaport(Student $student): array
    {
        $grades = Grade::with(['subject', 'teacher.user'])
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $raportSubjects = $grades->groupBy('subject_id')->map(function (Collection $subjectGrades): array {
            $first = $subjectGrades->first();
            $tugasValues = $subjectGrades->where('type', 'Tugas')->pluck('score');
            $kuisValues = $subjectGrades->where('type', 'Kuis')->pluck('score');
            $utsScore = $subjectGrades->where('type', 'UTS')->avg('score');
            $uasScore = $subjectGrades->where('type', 'UAS')->avg('score');

            $tugasAvg = $tugasValues->isNotEmpty() ? (float) $tugasValues->avg() : null;
            $kuisAvg = $kuisValues->isNotEmpty() ? (float) $kuisValues->avg() : null;

            $weightedParts = [];
            $weightTotal = 0.0;

            if ($tugasAvg !== null) {
                $weightedParts[] = $tugasAvg * 0.30;
                $weightTotal += 0.30;
            }
            if ($kuisAvg !== null) {
                $weightedParts[] = $kuisAvg * 0.20;
                $weightTotal += 0.20;
            }
            if ($utsScore !== null) {
                $weightedParts[] = (float) $utsScore * 0.25;
                $weightTotal += 0.25;
            }
            if ($uasScore !== null) {
                $weightedParts[] = (float) $uasScore * 0.25;
                $weightTotal += 0.25;
            }

            $finalScore = $weightTotal > 0 ? round(array_sum($weightedParts) / $weightTotal, 1) : 0.0;

            [$predikat, $catatan] = $this->resolvePredikat($finalScore, $weightTotal);

            return [
                'subject_id' => $first->subject_id,
                'subject_name' => $first->subject?->name ?? '-',
                'teacher_name' => $first->teacher?->user?->name ?? '-',
                'tugas_avg' => $tugasAvg !== null ? round($tugasAvg, 1) : null,
                'kuis_avg' => $kuisAvg !== null ? round($kuisAvg, 1) : null,
                'uts' => $utsScore !== null ? round((float) $utsScore, 1) : null,
                'uas' => $uasScore !== null ? round((float) $uasScore, 1) : null,
                'nilai_akhir' => $finalScore,
                'predikat' => $predikat,
                'catatan' => $catatan,
            ];
        })->values();

        $detailedGrades = $grades->map(fn (Grade $grade): array => [
            'id' => $grade->id,
            'subject' => $grade->subject?->name ?? '-',
            'teacher' => $grade->teacher?->user?->name ?? '-',
            'type' => $grade->type,
            'name' => $grade->name ?? $grade->type,
            'score' => (float) $grade->score,
            'notes' => $grade->notes,
            'date' => $grade->created_at?->format('d M Y') ?? '-',
        ])->values();

        return [
            'raportSubjects' => $raportSubjects,
            'detailedGrades' => $detailedGrades,
        ];
    }

    /**
     * Build attendance summary and chronological records for a student.
     *
     * @return array{summary: array<string, int>, attendances: Collection<int, array<string, mixed>>}
     */
    public function attendanceSummary(Student $student): array
    {
        $records = Attendance::query()
            ->where('user_id', $student->user_id)
            ->orderByDesc('date')
            ->get();

        $summary = [
            'total' => $records->count(),
            'hadir' => $records->where('status', 'Hadir')->count(),
            'terlambat' => $records->where('status', 'Terlambat')->count(),
            'izin' => $records->where('status', 'Izin')->count(),
            'sakit' => $records->where('status', 'Sakit')->count(),
            'alpa' => $records->where('status', 'Alpa')->count(),
        ];

        $attendances = $records->map(fn (Attendance $attendance): array => [
            'id' => $attendance->id,
            'date' => Carbon::parse($attendance->date)->format('d M Y'),
            'day_name' => Carbon::parse($attendance->date)->locale('id')->translatedFormat('l'),
            'time_in' => $attendance->time_in ? substr((string) $attendance->time_in, 0, 5) : '-',
            'time_out' => $attendance->time_out ? substr((string) $attendance->time_out, 0, 5) : '-',
            'status' => $attendance->status,
            'leave_reason' => $attendance->leave_reason,
        ])->values();

        return [
            'summary' => $summary,
            'attendances' => $attendances,
        ];
    }

    /**
     * Build weekly class schedules for a student's class.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function classSchedules(Student $student): Collection
    {
        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return TeachingSchedule::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->orderBy('day')
            ->orderBy('time_start')
            ->get()
            ->map(fn (TeachingSchedule $schedule): array => [
                'id' => $schedule->id,
                'day' => (int) $schedule->day,
                'day_name' => $dayNames[(int) $schedule->day] ?? '-',
                'subject' => $schedule->subject?->name ?? '-',
                'teacher' => $schedule->teacher?->user?->name ?? '-',
                'time_start' => substr((string) $schedule->time_start, 0, 5),
                'time_end' => substr((string) $schedule->time_end, 0, 5),
                'room' => $schedule->room ?? '-',
                'jpl' => $schedule->jpl ?? 2,
            ]);
    }

    /**
     * Build weekly journal digests for a student's class.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function weeklyJournals(Student $student, int $limitWeeks = 4): Collection
    {
        $journals = TeachingJournal::with(['subject', 'teacher.user'])
            ->where('student_class_id', $student->student_class_id)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return $journals
            ->groupBy(fn (TeachingJournal $journal): string => $journal->date?->format('o-\WW') ?? 'unknown')
            ->take($limitWeeks)
            ->map(function (Collection $weekItems, string $weekKey): array {
                $sampleDate = $weekItems->first()?->date ?? Carbon::now();
                $start = $sampleDate->copy()->startOfWeek();
                $end = $sampleDate->copy()->endOfWeek();

                return [
                    'week_key' => $weekKey,
                    'week_label' => 'Minggu Ke-'.$sampleDate->isoWeek().' ('.$start->format('d M').' - '.$end->format('d M Y').')',
                    'is_current_week' => $weekKey === Carbon::now()->format('o-\WW'),
                    'entries' => $weekItems->sortBy('date')->map(fn (TeachingJournal $item): array => [
                        'id' => $item->id,
                        'subject_name' => $item->subject?->name ?? '-',
                        'teacher_name' => $item->teacher?->user?->name ?? '-',
                        'date' => $item->date?->locale('id')->translatedFormat('d M Y') ?? '-',
                        'meeting_number' => $item->meeting_number,
                        'topic' => $item->topic,
                        'summary' => $item->summary,
                    ])->values()->all(),
                ];
            })
            ->values();
    }

    /**
     * Get approved student achievements formatted for e-Raport display.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function approvedAchievements(Student $student): Collection
    {
        return StudentAchievement::query()
            ->where('student_id', $student->id)
            ->approved()
            ->orderByDesc('year')
            ->orderByDesc('achievement_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StudentAchievement $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category ?? 'Umum',
                'level' => $item->level,
                'year' => $item->year,
                'organizer' => $item->organizer ?? '-',
                'achievement_date' => $item->achievement_date?->format('d M Y') ?? '-',
                'description' => $item->description,
            ])
            ->values();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolvePredikat(float $finalScore, float $weightTotal): array
    {
        if ($finalScore >= 90) {
            return ['A', 'Sangat baik dalam memahami materi pembelajaran, berakhlak mulia, dan konsisten berprestasi.'];
        }
        if ($finalScore >= 80) {
            return ['B+', 'Baik dalam penguasaan materi, tekun mengerjakan tugas, dan santun kepada guru.'];
        }
        if ($finalScore >= 70) {
            return ['C+', 'Cukup memahami materi, perlu bimbingan tambahan dalam pemahaman konsep.'];
        }
        if ($weightTotal > 0) {
            return ['D', 'Perlu pendampingan intensif dalam pembelajaran dan pengulangan materi.'];
        }

        return ['B', 'Menunjukkan pemahaman yang baik dan aktif dalam kegiatan pembelajaran.'];
    }
}
