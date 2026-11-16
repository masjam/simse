<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Advance;
use App\Models\Badge;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint5CAdvanceAndLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private User $bendaharaUser;

    private User $studentUser;

    private Student $student;

    private StudentClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Staf Sekolah', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $this->seed(BadgeSeeder::class);

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $this->bendaharaUser = User::factory()->create(['name' => 'Ibu Rahayu Bendahara']);
        $this->bendaharaUser->assignRole('Bendahara');

        $this->teacherUser = User::factory()->create(['name' => 'Ustadz Fajar Guru']);
        $this->teacherUser->assignRole('Guru Pengajar');
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198903032014011002',
        ]);

        $this->class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'name' => '6 Abu Dzar',
            'level' => '6',
            'teacher_id' => $this->teacher->id,
        ]);

        $this->studentUser = User::factory()->create(['name' => 'Rayhan Pratama']);
        $this->studentUser->assignRole('Siswa');
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_class_id' => $this->class->id,
            'nis' => '202606001',
            'nisn' => '0148819001',
            'nik' => '3403011501140001',
            'gender' => 'L',
        ]);
    }

    public function test_teacher_can_view_kasbon_page_and_summary(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('guru.kasbon'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Kasbon')
            ->has('advances')
            ->has('summary')
            ->where('summary.can_request', true)
        );
    }

    public function test_teacher_can_submit_kasbon_request(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('guru.kasbon.store'), [
            'amount' => 1500000,
            'installment_count' => 3,
            'reason' => 'Biaya mendesak perbaikan sepeda motor dinas.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('advances', [
            'user_id' => $this->teacherUser->id,
            'amount' => 1500000,
            'installment_count' => 3,
            'monthly_amount' => 500000,
            'status' => 'Diajukan',
        ]);
    }

    public function test_teacher_cannot_submit_duplicate_active_kasbon_request(): void
    {
        Advance::create([
            'user_id' => $this->teacherUser->id,
            'amount' => 2000000,
            'installment_count' => 4,
            'monthly_amount' => 500000,
            'reason' => 'Kasbon aktif sebelumnya',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($this->teacherUser)->post(route('guru.kasbon.store'), [
            'amount' => 1000000,
            'installment_count' => 2,
            'reason' => 'Pengajuan kedua',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('advances', 1);
    }

    public function test_bendahara_can_view_kasbon_list_and_approve(): void
    {
        $advance = Advance::create([
            'user_id' => $this->teacherUser->id,
            'amount' => 1200000,
            'installment_count' => 3,
            'monthly_amount' => 400000,
            'reason' => 'Kebutuhan mendesak pengobatan anak',
            'status' => 'Diajukan',
        ]);

        $indexResponse = $this->actingAs($this->bendaharaUser)->get(route('bendahara.kasbon'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertInertia(fn ($page) => $page
            ->component('Bendahara/Kasbon')
            ->has('advances', 1)
            ->where('summary.pending_count', 1)
        );

        $approveResponse = $this->actingAs($this->bendaharaUser)
            ->post(route('bendahara.kasbon.approve', $advance->id));

        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        $this->assertDatabaseHas('advances', [
            'id' => $advance->id,
            'status' => 'Disetujui',
            'approved_by' => $this->bendaharaUser->id,
        ]);

        // 3 monthly installments created
        $this->assertDatabaseCount('advance_installments', 3);
        $this->assertDatabaseHas('advance_installments', [
            'advance_id' => $advance->id,
            'installment_number' => 1,
            'amount' => 400000,
            'status' => 'Pending',
        ]);
    }

    public function test_bendahara_can_reject_kasbon_with_reason(): void
    {
        $advance = Advance::create([
            'user_id' => $this->teacherUser->id,
            'amount' => 5000000,
            'installment_count' => 10,
            'monthly_amount' => 500000,
            'reason' => 'Pinjaman renovasi rumah',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($this->bendaharaUser)
            ->post(route('bendahara.kasbon.reject', $advance->id), [
                'reason' => 'Plafon kasbon melebihi batas ketentuan internal sekolah.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('advances', [
            'id' => $advance->id,
            'status' => 'Ditolak',
            'rejection_reason' => 'Plafon kasbon melebihi batas ketentuan internal sekolah.',
        ]);

        $this->assertDatabaseCount('advance_installments', 0);
    }

    public function test_advance_installment_is_automatically_deducted_during_payroll_generation(): void
    {
        // 1. Create and approve advance
        $advance = Advance::create([
            'user_id' => $this->teacherUser->id,
            'amount' => 1000000,
            'installment_count' => 2,
            'monthly_amount' => 500000,
            'reason' => 'Kasbon untuk biaya darurat',
            'status' => 'Disetujui',
            'approved_by' => $this->bendaharaUser->id,
            'approved_at' => now(),
        ]);

        $advance->installments()->create([
            'installment_number' => 1,
            'amount' => 500000,
            'month' => 'November',
            'year' => '2026',
            'status' => 'Pending',
        ]);

        $advance->installments()->create([
            'installment_number' => 2,
            'amount' => 500000,
            'month' => 'Desember',
            'year' => '2026',
            'status' => 'Pending',
        ]);

        // 2. Bendahara generates payroll for November 2026
        $generateResponse = $this->actingAs($this->bendaharaUser)
            ->post(route('bendahara.payroll.generate'), [
                'month' => 'November',
                'year' => '2026',
                'base_salary' => 3500000,
            ]);

        $generateResponse->assertRedirect();

        // 3. Check that payroll deduction component exists
        $this->assertDatabaseHas('payroll_components', [
            'type' => 'Potongan',
            'name' => 'Angsuran Kasbon (#1/2)',
            'amount' => 500000,
        ]);

        // 4. Installment #1 marked as Lunas
        $this->assertDatabaseHas('advance_installments', [
            'advance_id' => $advance->id,
            'installment_number' => 1,
            'status' => 'Lunas',
        ]);

        // Installment #2 still Pending
        $this->assertDatabaseHas('advance_installments', [
            'advance_id' => $advance->id,
            'installment_number' => 2,
            'status' => 'Pending',
        ]);
    }

    public function test_student_can_view_library_catalog_and_loans(): void
    {
        $book = Book::create([
            'title' => 'Kisah Teladan Para Sahabat Nabi',
            'author' => 'Tim Pustaka SDAM',
            'publisher' => 'Suara Muhammadiyah',
            'isbn' => '978-602-9988-11-2',
            'category' => 'Agama Islam',
            'physical_stock' => 5,
        ]);

        BookLoan::create([
            'book_id' => $book->id,
            'user_id' => $this->studentUser->id,
            'loan_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'Dipinjam',
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('siswa.perpustakaan'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Student/Perpustakaan')
            ->has('books', 1)
            ->where('books.0.title', 'Kisah Teladan Para Sahabat Nabi')
            ->has('loans', 1)
            ->where('summary.borrowed_count', 1)
        );
    }

    public function test_student_reading_ebook_triggers_gamification_points_and_serves_pdf(): void
    {
        Storage::disk('local')->put('ebooks/sirah_nabawiyah.pdf', '%PDF-1.4 sample content');

        $book = Book::create([
            'title' => 'Sirah Nabawiyah untuk Siswa Madrasah & SD',
            'author' => 'Majelis Dikdasmen',
            'publisher' => 'Pustaka Pelajar',
            'category' => 'Sejarah Islam',
            'file_path' => 'ebooks/sirah_nabawiyah.pdf',
            'physical_stock' => 10,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('siswa.perpustakaan.read', $book->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Gamification check: 30 points awarded
        $this->assertDatabaseHas('point_events', [
            'student_id' => $this->student->id,
            'event_type' => 'book_borrowed',
            'points' => 30,
        ]);

        // book_explorer badge unlocked
        $bookBadge = Badge::where('code', 'book_explorer')->firstOrFail();
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $this->student->id,
            'badge_id' => $bookBadge->id,
        ]);
    }
}
