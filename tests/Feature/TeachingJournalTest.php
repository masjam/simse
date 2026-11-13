<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\Tingkat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TeachingJournalTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private User $otherTeacherUser;

    private Teacher $otherTeacher;

    private User $studentUser;

    private StudentClass $classA;

    private StudentClass $classB;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $tingkat4 = Tingkat::create(['name' => 'Kelas 4']);
        $tingkat5 = Tingkat::create(['name' => 'Kelas 5']);

        $this->teacherUser = User::factory()->create(['name' => 'Ibu Siti Guru']);
        $this->teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198001012010012001']);

        $this->otherTeacherUser = User::factory()->create(['name' => 'Pak Budi Guru']);
        $this->otherTeacherUser->assignRole('Guru Pengajar');
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'nip' => '198001012010012002']);

        $this->classA = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat4->id,
            'teacher_id' => $this->teacher->id,
            'level' => '4',
            'name' => '4A',
        ]);

        $this->classB = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat5->id,
            'teacher_id' => $this->otherTeacher->id,
            'level' => '5',
            'name' => '5A',
        ]);

        $this->subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);

        $this->studentUser = User::factory()->create(['name' => 'Andi Siswa']);
        $this->studentUser->assignRole('Siswa');
        Student::create([
            'user_id' => $this->studentUser->id,
            'student_class_id' => $this->classA->id,
            'nisn' => '0150000010',
            'nik' => '3403010101160010',
            'gender' => 'L',
        ]);
    }

    public function test_teacher_can_create_update_and_delete_teaching_journal_with_ownership_enforcement(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('guru.jurnal'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Teacher/Jurnal'));

        // Validasi Bahasa Indonesia jika topik/ringkasan kosong
        $this->actingAs($this->teacherUser)
            ->post(route('guru.jurnal.store'), [
                'student_class_id' => $this->classA->id,
                'subject_id' => $this->subject->id,
                'date' => Carbon::now()->toDateString(),
                'topic' => '',
                'summary' => '',
            ])
            ->assertSessionHasErrors(['topic', 'summary']);

        // Guru membuat jurnal
        $this->actingAs($this->teacherUser)
            ->post(route('guru.jurnal.store'), [
                'student_class_id' => $this->classA->id,
                'subject_id' => $this->subject->id,
                'date' => Carbon::now()->toDateString(),
                'meeting_number' => 2,
                'topic' => 'Operasi Hitung Pecahan',
                'summary' => 'Siswa berlatih penjumlahan dan pengurangan pecahan berpenyebut sama.',
                'activities' => 'Latihan soal papan tulis',
                'notes' => 'Bawa penggaris untuk pertemuan berikutnya',
            ])
            ->assertRedirect();

        $journal = TeachingJournal::where('topic', 'Operasi Hitung Pecahan')->first();
        $this->assertNotNull($journal);
        $this->assertSame($this->teacher->id, $journal->teacher_id);

        // Guru lain tidak boleh mengubah atau menghapus jurnal milik Ibu Siti
        $this->actingAs($this->otherTeacherUser)
            ->put(route('guru.jurnal.update', $journal->id), [
                'student_class_id' => $this->classA->id,
                'subject_id' => $this->subject->id,
                'date' => Carbon::now()->toDateString(),
                'meeting_number' => 2,
                'topic' => 'Diubah Paksa',
                'summary' => 'Tidak sah',
            ])
            ->assertForbidden();

        $this->actingAs($this->otherTeacherUser)
            ->delete(route('guru.jurnal.destroy', $journal->id))
            ->assertForbidden();

        // Pemilik jurnal dapat memperbarui dan menghapus
        $this->actingAs($this->teacherUser)
            ->put(route('guru.jurnal.update', $journal->id), [
                'student_class_id' => $this->classA->id,
                'subject_id' => $this->subject->id,
                'date' => Carbon::now()->toDateString(),
                'meeting_number' => 3,
                'topic' => 'Operasi Hitung Pecahan Campuran',
                'summary' => 'Ringkasan diperbarui untuk materi mingguan.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teaching_journals', [
            'id' => $journal->id,
            'topic' => 'Operasi Hitung Pecahan Campuran',
            'meeting_number' => 3,
        ]);

        $this->actingAs($this->teacherUser)
            ->delete(route('guru.jurnal.destroy', $journal->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('teaching_journals', ['id' => $journal->id]);
    }

    public function test_student_receives_weekly_materials_from_own_class_journals_only(): void
    {
        $now = Carbon::create(2026, 11, 12, 10, 0, 0);
        Carbon::setTestNow($now);

        $thisWeekDate = $now->copy()->startOfWeek()->addDay()->toDateString();
        $lastWeekDate = $now->copy()->subWeek()->startOfWeek()->addDay()->toDateString();

        // Jurnal kelas 4A minggu ini
        TeachingJournal::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classA->id,
            'date' => $thisWeekDate,
            'meeting_number' => 1,
            'topic' => 'Materi Minggu Ini 4A',
            'summary' => 'Ringkasan perkalian bilangan cacah untuk kelas 4A.',
        ]);

        // Jurnal kelas 4A minggu lalu
        TeachingJournal::create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classA->id,
            'date' => $lastWeekDate,
            'meeting_number' => 1,
            'topic' => 'Materi Minggu Lalu 4A',
            'summary' => 'Ringkasan pembagian bilangan cacah minggu lalu.',
        ]);

        // Jurnal kelas 5A minggu ini (tidak boleh bocor ke siswa 4A)
        TeachingJournal::create([
            'teacher_id' => $this->otherTeacher->id,
            'subject_id' => $this->subject->id,
            'student_class_id' => $this->classB->id,
            'date' => $thisWeekDate,
            'meeting_number' => 1,
            'topic' => 'Materi Rahasia Kelas 5A',
            'summary' => 'Konten kelas 5A tidak boleh muncul di siswa 4A.',
        ]);

        // Dashboard siswa hanya menampilkan ringkasan minggu ini untuk kelas 4A
        $this->actingAs($this->studentUser)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Dashboard')
                ->where('summary.weekly_materials_count', 1)
                ->has('weeklyMaterials', 1)
                ->where('weeklyMaterials.0.topic', 'Materi Minggu Ini 4A')
            );

        // Halaman Materi Mingguan menampilkan semua minggu kelas 4A tanpa bocoran kelas 5A
        $this->actingAs($this->studentUser)
            ->get(route('siswa.materi-mingguan'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/MateriMingguan')
                ->has('weeklyGroups', 2)
                ->where('weeklyGroups.0.is_current_week', true)
                ->where('weeklyGroups.0.subjects.0.entries.0.topic', 'Materi Minggu Ini 4A')
                ->where('weeklyGroups.1.subjects.0.entries.0.topic', 'Materi Minggu Lalu 4A')
            );

        // Filter minggu tertentu
        $currentWeekKey = $now->format('o-\WW');
        $this->actingAs($this->studentUser)
            ->get(route('siswa.materi-mingguan', ['week' => $currentWeekKey]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('weeklyGroups', 1)
                ->where('weeklyGroups.0.week_key', $currentWeekKey)
            );

        Carbon::setTestNow();
    }
}
