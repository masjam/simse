<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Tingkat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AchievementVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $parentUser;

    private User $otherParentUser;

    private User $guruBkUser;

    private User $tuUser;

    private User $bidangLombaUser;

    private User $teacherUser;

    private User $studentUser;

    private Student $student;

    private Student $otherStudent;

    private StudentClass $studentClass;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru BK', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Tata Usaha', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bidang Lomba', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $tingkat = Tingkat::create(['name' => 'Kelas 4']);

        $this->teacherUser = User::factory()->create(['name' => 'Siti Guru']);
        $this->teacherUser->assignRole('Guru Pengajar');

        $teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198407152010012015',
        ]);

        $this->studentClass = StudentClass::create([
            'name' => '4A',
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat->id,
            'teacher_id' => $teacher->id,
            'level' => '4',
        ]);

        $this->parentUser = User::factory()->create(['name' => 'Bapak Andi']);
        $this->parentUser->assignRole('Orang Tua');

        $this->otherParentUser = User::factory()->create(['name' => 'Ibu Budi']);
        $this->otherParentUser->assignRole('Orang Tua');

        $this->studentUser = User::factory()->create(['name' => 'Andi Siswa']);
        $this->studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'parent_user_id' => $this->parentUser->id,
            'student_class_id' => $this->studentClass->id,
            'nisn' => '0152026001',
            'nik' => '3403011505160001',
            'gender' => 'L',
        ]);

        $otherStudentUser = User::factory()->create(['name' => 'Budi Siswa']);
        $otherStudentUser->assignRole('Siswa');
        $this->otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'parent_user_id' => $this->otherParentUser->id,
            'student_class_id' => $this->studentClass->id,
            'nisn' => '0152026099',
            'nik' => '3403011001160099',
            'gender' => 'L',
        ]);

        $this->guruBkUser = User::factory()->create(['name' => 'Guru BK']);
        $this->guruBkUser->assignRole('Guru BK');

        $this->tuUser = User::factory()->create(['name' => 'Admin TU']);
        $this->tuUser->assignRole('Admin Tata Usaha');

        $this->bidangLombaUser = User::factory()->create(['name' => 'Koordinator Lomba']);
        $this->bidangLombaUser->assignRole('Bidang Lomba');

        $subject = Subject::create(['code' => 'PAI', 'name' => 'PAI']);
        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'type' => 'UTS',
            'name' => 'UTS',
            'score' => 90,
        ]);
    }

    public function test_parent_can_submit_achievement_with_certificate_and_cannot_submit_for_unlinked_child(): void
    {
        $certificate = UploadedFile::fake()->create('piagam-tahfidz.pdf', 200, 'application/pdf');

        $this->actingAs($this->parentUser)
            ->post(route('ortu.achievements.store'), [
                'student_id' => $this->student->id,
                'name' => 'Juara 1 Lomba Tahfidz',
                'category' => 'Keagamaan',
                'level' => 'Kabupaten/Kota',
                'year' => 2026,
                'organizer' => 'PDM Gunungkidul',
                'certificate' => $certificate,
            ])
            ->assertRedirect(route('ortu.dashboard', ['tab' => 'akademik']));

        $achievement = StudentAchievement::where('student_id', $this->student->id)->first();
        $this->assertNotNull($achievement);
        $this->assertSame('Menunggu Verifikasi', $achievement->status);
        $this->assertSame($this->parentUser->id, $achievement->submitted_by);
        $this->assertNotNull($achievement->certificate_path);
        Storage::disk('local')->assertExists($achievement->certificate_path);

        $this->actingAs($this->parentUser)
            ->post(route('ortu.achievements.store'), [
                'student_id' => $this->otherStudent->id,
                'name' => 'Juara Palsu',
                'category' => 'Akademik',
                'level' => 'Nasional',
                'year' => 2026,
            ])
            ->assertForbidden();
    }

    public function test_guru_bk_tu_and_bidang_lomba_can_verify_and_unverified_roles_are_blocked(): void
    {
        $achievement = StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Juara 1 Olimpiade IPA',
            'category' => 'Akademik',
            'level' => 'Provinsi',
            'year' => 2026,
            'status' => 'Menunggu Verifikasi',
            'submitted_by' => $this->parentUser->id,
        ]);

        // Siswa dan Orang Tua tidak boleh memverifikasi
        $this->actingAs($this->studentUser)
            ->post(route('achievements.verification.verify', $achievement), [
                'status' => 'Disetujui',
            ])
            ->assertForbidden();

        $this->actingAs($this->parentUser)
            ->post(route('achievements.verification.verify', $achievement), [
                'status' => 'Disetujui',
            ])
            ->assertForbidden();

        // Guru BK dapat melihat daftar & menyetujui
        $this->actingAs($this->guruBkUser)
            ->get(route('achievements.verification.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Achievements/Verification')
                ->has('achievements', 1)
            );

        $this->actingAs($this->guruBkUser)
            ->post(route('achievements.verification.verify', $achievement), [
                'status' => 'Disetujui',
                'verification_notes' => 'Piagam tervalidasi.',
            ])
            ->assertRedirect();

        $achievement->refresh();
        $this->assertSame('Disetujui', $achievement->status);
        $this->assertSame($this->guruBkUser->id, $achievement->verified_by);
        $this->assertNotNull($achievement->verified_at);

        // Admin TU dan Bidang Lomba juga memiliki akses halaman verifikasi
        $this->actingAs($this->tuUser)
            ->get(route('achievements.verification.index', ['status' => 'Disetujui']))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->has('achievements', 1));

        $this->actingAs($this->bidangLombaUser)
            ->post(route('achievements.verification.verify', $achievement), [
                'status' => 'Ditolak',
                'verification_notes' => 'Perlu unggah ulang piagam asli.',
            ])
            ->assertRedirect();

        $this->assertSame('Ditolak', $achievement->fresh()->status);
    }

    public function test_only_approved_achievements_appear_on_teacher_and_student_eraport(): void
    {
        StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Juara 1 Tahfidz (Disetujui)',
            'category' => 'Keagamaan',
            'level' => 'Nasional',
            'year' => 2026,
            'status' => 'Disetujui',
        ]);

        StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Lomba Menunggu',
            'category' => 'Seni & Budaya',
            'level' => 'Sekolah',
            'year' => 2026,
            'status' => 'Menunggu Verifikasi',
        ]);

        StudentAchievement::create([
            'student_id' => $this->student->id,
            'name' => 'Lomba Ditolak',
            'category' => 'Olahraga',
            'level' => 'Kecamatan',
            'year' => 2026,
            'status' => 'Ditolak',
        ]);

        // Guru e-Raport hanya menampilkan prestasi Disetujui
        $this->actingAs($this->teacherUser)
            ->get(route('guru.raport', ['class' => $this->studentClass->name]))
            ->assertOk()
            ->assertInertia(function (Assert $page): void {
                $page->component('Teacher/Raport')
                    ->has('students', 2);

                $students = collect($page->toArray()['props']['students']);
                $targetStudent = $students->firstWhere('id', $this->student->id);

                $this->assertNotNull($targetStudent);
                $this->assertCount(1, $targetStudent['achievements']);
                $this->assertSame('Juara 1 Tahfidz (Disetujui)', $targetStudent['achievements'][0]['name']);
            });

        // Siswa e-Raport hanya menampilkan prestasi Disetujui
        $this->actingAs($this->studentUser)
            ->get(route('siswa.nilai'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Student/Nilai')
                ->has('achievements', 1)
                ->where('achievements.0.name', 'Juara 1 Tahfidz (Disetujui)')
            );
    }
}
