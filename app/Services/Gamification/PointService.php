<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\BookLoan;
use App\Models\CounselingSession;
use App\Models\ExamAttempt;
use App\Models\PointEvent;
use App\Models\Student;
use App\Models\StudentBadge;
use App\Models\Submission;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Cek apakah modul gamifikasi aktif di sistem sekolah.
     */
    public function isEnabled(): bool
    {
        return SystemSetting::getValue('gamification_enabled', '1') === '1';
    }

    /**
     * Berikan poin kepada siswa berdasarkan event tertentu dan evaluasi lencana.
     */
    public function award(
        Student $student,
        string $eventType,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $customPoints = null
    ): ?PointEvent {
        if (! $this->isEnabled()) {
            return null;
        }

        $defaultPoints = [
            'assignment_submitted' => 50,
            'cbt_completed' => 60,
            'cbt_high_score' => 40,
            'high_score' => 50,
            'book_borrowed' => 30,
            'character_guidance' => 40,
            'attendance_streak' => 50,
            'manual_reward' => 25,
        ];

        $points = $customPoints ?? ($defaultPoints[$eventType] ?? 25);

        return DB::transaction(function () use ($student, $eventType, $reference, $notes, $points): PointEvent {
            $event = PointEvent::create([
                'student_id' => $student->id,
                'event_type' => $eventType,
                'points' => $points,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'notes' => $notes,
            ]);

            $this->checkAndUnlockBadges($student);

            return $event;
        });
    }

    /**
     * Periksa kriteria seluruh lencana dan buka lencana baru jika syarat terpenuhi.
     */
    public function checkAndUnlockBadges(Student $student): Collection
    {
        $activeBadges = Badge::where('is_active', true)->get();
        $ownedBadgeIds = StudentBadge::where('student_id', $student->id)->pluck('badge_id')->toArray();

        $totalPoints = (int) PointEvent::where('student_id', $student->id)->sum('points');
        $submissionCount = Submission::where('student_id', $student->id)->count();
        $highScoreSubmissionCount = Submission::where('student_id', $student->id)->where('score', '>=', 90)->count();
        $cbtAttemptsCount = ExamAttempt::where('student_id', $student->id)->whereNotNull('finished_at')->count();
        $highScoreCbtCount = ExamAttempt::where('student_id', $student->id)->where('score', '>=', 90)->count();
        $bookLoansCount = BookLoan::where('user_id', $student->user_id)->count();
        $ebookReadsCount = PointEvent::where('student_id', $student->id)->where('event_type', 'book_borrowed')->count();
        $positiveSessionsCount = CounselingSession::where('student_id', $student->id)->count();

        $newlyUnlocked = collect();

        foreach ($activeBadges as $badge) {
            if (in_array($badge->id, $ownedBadgeIds, true)) {
                continue;
            }

            $eligible = match ($badge->code) {
                'first_step' => $submissionCount >= 1,
                'score_master' => ($highScoreSubmissionCount + $highScoreCbtCount) >= 1,
                'cbt_champion' => $cbtAttemptsCount >= 1,
                'diligent_student' => $submissionCount >= 3,
                'book_explorer' => ($bookLoansCount + $ebookReadsCount) >= 1,
                'character_role_model' => $positiveSessionsCount >= 1,
                'mujahidin_star' => $totalPoints >= 500,
                default => ($badge->points_required > 0 && $totalPoints >= $badge->points_required),
            };

            if ($eligible) {
                $studentBadge = StudentBadge::create([
                    'student_id' => $student->id,
                    'badge_id' => $badge->id,
                    'awarded_at' => now(),
                ]);

                $newlyUnlocked->push($studentBadge);
                $ownedBadgeIds[] = $badge->id;
            }
        }

        return $newlyUnlocked;
    }

    /**
     * Dapatkan profil gamifikasi lengkap untuk siswa (total poin, level, progress bar, daftar badge).
     */
    public function getStudentProfile(Student $student): array
    {
        $totalPoints = (int) PointEvent::where('student_id', $student->id)->sum('points');
        $earnedBadges = StudentBadge::with('badge')
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('badge_id');

        $level = match (true) {
            $totalPoints >= 1000 => [
                'level' => 5,
                'title' => 'Cendekiawan Belia Mendunia',
                'icon' => '👑',
                'min_points' => 1000,
                'next_points' => 1000,
                'progress_pct' => 100,
            ],
            $totalPoints >= 500 => [
                'level' => 4,
                'title' => 'Pendekar Cilik Al Mujahidin',
                'icon' => '🌟',
                'min_points' => 500,
                'next_points' => 1000,
                'progress_pct' => (int) round((($totalPoints - 500) / 500) * 100),
            ],
            $totalPoints >= 300 => [
                'level' => 3,
                'title' => 'Bintang Kelas',
                'icon' => '⭐',
                'min_points' => 300,
                'next_points' => 500,
                'progress_pct' => (int) round((($totalPoints - 300) / 200) * 100),
            ],
            $totalPoints >= 150 => [
                'level' => 2,
                'title' => 'Santri Rajin',
                'icon' => '📚',
                'min_points' => 150,
                'next_points' => 300,
                'progress_pct' => (int) round((($totalPoints - 150) / 150) * 100),
            ],
            default => [
                'level' => 1,
                'title' => 'Penuntut Ilmu Pemula',
                'icon' => '🌱',
                'min_points' => 0,
                'next_points' => 150,
                'progress_pct' => (int) round(($totalPoints / 150) * 100),
            ],
        };

        $allBadges = Badge::where('is_active', true)
            ->orderBy('points_required')
            ->get()
            ->map(function (Badge $b) use ($earnedBadges): array {
                $isEarned = $earnedBadges->has($b->id);
                $awardedAt = $isEarned ? $earnedBadges->get($b->id)->awarded_at : null;

                return [
                    'id' => $b->id,
                    'code' => $b->code,
                    'name' => $b->name,
                    'description' => $b->description,
                    'icon' => $b->icon,
                    'category' => $b->category,
                    'points_required' => $b->points_required,
                    'is_unlocked' => $isEarned,
                    'awarded_at' => $awardedAt?->translatedFormat('d M Y') ?? null,
                ];
            })
            ->all();

        $recentEvents = PointEvent::where('student_id', $student->id)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (PointEvent $pe): array => [
                'id' => $pe->id,
                'event_type' => $pe->event_type,
                'points' => $pe->points,
                'notes' => $pe->notes ?? 'Aktivitas pembelajaran',
                'created_at' => $pe->created_at?->translatedFormat('d M Y, H:i') ?? '-',
            ])
            ->all();

        return [
            'is_enabled' => $this->isEnabled(),
            'total_points' => $totalPoints,
            'level' => $level,
            'badges' => $allBadges,
            'unlocked_count' => $earnedBadges->count(),
            'total_badges' => count($allBadges),
            'recent_events' => $recentEvents,
        ];
    }

    /**
     * Dapatkan daftar peringkat positif sahabat kelas / sekolah.
     */
    public function getLeaderboard(?string $studentClassId = null, int $limit = 10): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $query = Student::query()
            ->with(['user', 'studentClass'])
            ->withCount('studentBadges')
            ->withSum('pointEvents', 'points');

        if ($studentClassId) {
            $query->where('student_class_id', $studentClassId);
        }

        $students = $query->get()
            ->sortByDesc('point_events_sum_points')
            ->take($limit)
            ->values();

        return $students->map(function (Student $s, int $index): array {
            $rank = $index + 1;
            $medal = match ($rank) {
                1 => '🥇',
                2 => '🥈',
                3 => '🥉',
                default => null,
            };

            return [
                'rank' => $rank,
                'medal' => $medal,
                'student_id' => $s->id,
                'name' => $s->user?->name ?? 'Siswa',
                'class_name' => $s->studentClass?->name ?? '-',
                'total_points' => (int) ($s->point_events_sum_points ?? 0),
                'badges_count' => (int) $s->student_badges_count,
            ];
        })->all();
    }
}
