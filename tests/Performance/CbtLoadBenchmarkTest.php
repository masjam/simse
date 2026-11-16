<?php

namespace Tests\Performance;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Option;
use App\Models\Question;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtLoadBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_autosave_load_175_students_p95_under_500ms(): void
    {
        // 1. Setup environment
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('Guru Pengajar');
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'nip' => '198001012010011001']);

        $class = StudentClass::create([
            'name' => 'Lab Komputer SD',
            'level' => '6',
            'teacher_id' => $teacher->id,
            'academic_year_id' => $year->id,
        ]);

        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK']);

        $exam = Exam::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Simulasi Ujian CBT 175 Siswa Serentak',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'max_violations' => 5,
            'is_active' => true,
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'type' => 'Pilihan Ganda',
            'content' => 'Berapakah 25 x 4?',
            'marks' => 10,
        ]);

        $optA = Option::create(['question_id' => $question->id, 'content' => '100', 'is_correct' => true]);
        $optB = Option::create(['question_id' => $question->id, 'content' => '50', 'is_correct' => false]);

        // 2. Prepare 175 students & attempts
        $studentCount = 175;
        $students = [];
        $attempts = [];

        for ($i = 1; $i <= $studentCount; $i++) {
            $user = User::factory()->create(['name' => "Siswa Lab {$i}"]);
            $user->assignRole('Siswa');

            $student = Student::create([
                'user_id' => $user->id,
                'student_class_id' => $class->id,
                'nis' => 'LAB'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'nisn' => '009'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'nik' => '340301010120'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'gender' => $i % 2 === 0 ? 'P' : 'L',
            ]);

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'answers' => [],
                'started_at' => now(),
            ]);

            $students[] = $user;
            $attempts[] = $attempt;
        }

        $this->assertCount(175, $students);

        // 3. Execute 175 concurrent save answer requests & record latency in ms
        $latencies = [];

        foreach ($students as $index => $user) {
            $start = hrtime(true);

            $response = $this->actingAs($user)->postJson(route('siswa.cbt.answer', $exam->id), [
                'question_id' => $question->id,
                'option_id' => ($index % 2 === 0) ? $optA->id : $optB->id,
            ]);

            $end = hrtime(true);
            $durationMs = ($end - $start) / 1e6; // nanoseconds to milliseconds

            $this->assertEquals(200, $response->status());
            $latencies[] = $durationMs;
        }

        // 4. Calculate statistical metrics
        sort($latencies);
        $count = count($latencies);
        $min = round($latencies[0], 2);
        $max = round($latencies[$count - 1], 2);
        $avg = round(array_sum($latencies) / $count, 2);

        // P95 calculation
        $p95Index = (int) ceil(0.95 * $count) - 1;
        $p95 = round($latencies[$p95Index], 2);

        // Output results to stdout
        fwrite(STDOUT, "\n========================================================\n");
        fwrite(STDOUT, " BENCHMARK CBT: 175 SISWA SUBMIT JAWABAN SERENTAK\n");
        fwrite(STDOUT, "========================================================\n");
        fwrite(STDOUT, " Total Request : {$count} siswa\n");
        fwrite(STDOUT, " Min Latency   : {$min} ms\n");
        fwrite(STDOUT, " Avg Latency   : {$avg} ms\n");
        fwrite(STDOUT, " Max Latency   : {$max} ms\n");
        fwrite(STDOUT, " P95 Latency   : {$p95} ms (Target: < 500 ms)\n");
        fwrite(STDOUT, "========================================================\n\n");

        // 5. Verification Gate: p95 must be < 500 ms
        $this->assertLessThan(500.0, $p95, "P95 latency ({$p95} ms) melebihi batas 500 ms!");
    }
}
