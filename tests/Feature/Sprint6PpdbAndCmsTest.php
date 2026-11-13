<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Post;
use App\Models\PpdbRegistration;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint6PpdbAndCmsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Storage::fake('local');

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'name' => 'Super Admin Al Mujahidin',
            'email' => 'admin@sdmujahidin.sch.id',
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_public_user_can_view_ppdb_registration_form(): void
    {
        $response = $this->get(route('ppdb.form'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Ppdb/Register'));
    }

    public function test_public_user_can_submit_ppdb_registration_with_documents(): void
    {
        $kkFile = UploadedFile::fake()->create('kk_keluarga.pdf', 500, 'application/pdf');
        $aktaFile = UploadedFile::fake()->image('akta_kelahiran.jpg', 600, 800);

        $payload = [
            'student_name' => 'Ahmad Fatih Pratama',
            'nik' => '3403011205190001',
            'nisn' => '0123456789',
            'gender' => 'L',
            'birth_place' => 'Gunungkidul',
            'birth_date' => '2019-05-12',
            'address' => 'Jl. Baron Km 3, Wonosari, Gunungkidul',
            'previous_school' => 'TK ABA Wonosari Barat',
            'parent_name' => 'Bambang Pratama',
            'parent_phone' => '081234567899',
            'parent_email' => 'bambang.pratama@gmail.com',
            'parent_job' => 'Wiraswasta',
            'kk_file' => $kkFile,
            'birth_certificate_file' => $aktaFile,
        ];

        $response = $this->post(route('ppdb.store'), $payload);

        $this->assertDatabaseHas('ppdb_registrations', [
            'student_name' => 'Ahmad Fatih Pratama',
            'nik' => '3403011205190001',
            'parent_phone' => '081234567899',
            'status' => 'Menunggu Verifikasi',
        ]);

        $registration = PpdbRegistration::where('nik', '3403011205190001')->firstOrFail();
        $this->assertNotNull($registration->kk_path);
        $this->assertNotNull($registration->birth_certificate_path);
        Storage::disk('local')->assertExists($registration->kk_path);
        Storage::disk('local')->assertExists($registration->birth_certificate_path);

        $response->assertRedirect(route('ppdb.success', ['number' => $registration->registration_number]));

        // WA confirmation logged
        $this->assertDatabaseHas('whatsapp_logs', [
            'phone_number' => '6281234567899',
            'recipient_name' => 'Bambang Pratama',
        ]);
    }

    public function test_admin_can_view_ppdb_registrations_and_download_private_documents(): void
    {
        $path = Storage::disk('local')->putFile('ppdb/kk', UploadedFile::fake()->create('kk.pdf', 300, 'application/pdf'));

        $registration = PpdbRegistration::create([
            'registration_number' => 'PPDB-2026-TEST1',
            'student_name' => 'Ananda Siti',
            'nik' => '3403015506190002',
            'gender' => 'P',
            'birth_place' => 'Gunungkidul',
            'birth_date' => '2019-06-15',
            'address' => 'Wonosari',
            'parent_name' => 'Sulastri',
            'parent_phone' => '081398765432',
            'kk_path' => $path,
            'status' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.ppdb.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Ppdb/Index')
            ->has('registrations.data', 1)
        );

        // Protected document download
        $docResponse = $this->actingAs($this->admin)->get(route('admin.ppdb.document', [
            'registration' => $registration->id,
            'type' => 'kk',
        ]));
        $docResponse->assertStatus(200);
    }

    public function test_admin_can_approve_ppdb_registration_with_auto_conversion_to_active_student(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $tingkat = Tingkat::create([
            'name' => 'Tingkat 1',
        ]);

        $class = StudentClass::create([
            'academic_year_id' => $academicYear->id,
            'tingkat_id' => $tingkat->id,
            'name' => '1A - Abu Bakar',
            'level' => 1,
        ]);

        $registration = PpdbRegistration::create([
            'registration_number' => 'PPDB-2026-NEW01',
            'student_name' => 'Bilal Al Farabi',
            'nik' => '3403011111190001',
            'gender' => 'L',
            'birth_place' => 'Gunungkidul',
            'birth_date' => '2019-01-11',
            'address' => 'Selang, Wonosari',
            'parent_name' => 'Farabi Senior',
            'parent_phone' => '081299887766',
            'parent_email' => 'farabi.parent@gmail.com',
            'status' => 'Menunggu Verifikasi',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.ppdb.status', $registration->id), [
            'status' => 'Diterima',
            'class_id' => $class->id,
            'admin_notes' => 'Selamat, calon siswa dinyatakan lolos seleksi berkas.',
        ]);

        $response->assertRedirect();

        $registration->refresh();
        $this->assertEquals('Diterima', $registration->status);
        $this->assertNotNull($registration->student_id);
        $this->assertNotNull($registration->student_user_id);
        $this->assertNotNull($registration->parent_user_id);

        // Check created student
        $student = Student::find($registration->student_id);
        $this->assertNotNull($student);
        $this->assertEquals($class->id, $student->student_class_id);
        $this->assertEquals('Bilal Al Farabi', $student->user->name);
        $this->assertTrue($student->user->hasRole('Siswa'));

        // Check created parent
        $parent = User::find($registration->parent_user_id);
        $this->assertNotNull($parent);
        $this->assertEquals('Farabi Senior', $parent->name);
        $this->assertTrue($parent->hasRole('Orang Tua'));

        // Check wallet created
        $wallet = Wallet::where('student_id', $student->id)->first();
        $this->assertNotNull($wallet);
        $this->assertEquals(0, $wallet->balance);
    }

    public function test_super_admin_can_manage_cms_articles(): void
    {
        // 1. Create Article
        $createResponse = $this->actingAs($this->admin)->post(route('sistem.website.store'), [
            'title' => 'Prestasi Membanggakan Siswa SDAM di Ajang Internasional',
            'category' => 'Prestasi',
            'content' => 'Siswa SD Muhammadiyah Al Mujahidin berhasil meraih medali emas pada kejuaraan sains tingkat Asia Tenggara.',
            'image' => 'https://example.com/prestasi.jpg',
            'is_published' => true,
        ]);

        $createResponse->assertRedirect(route('sistem.website'));

        $this->assertDatabaseHas('posts', [
            'title' => 'Prestasi Membanggakan Siswa SDAM di Ajang Internasional',
            'category' => 'Prestasi',
            'is_published' => true,
        ]);

        $post = Post::where('title', 'Prestasi Membanggakan Siswa SDAM di Ajang Internasional')->firstOrFail();
        $this->assertEquals('prestasi-membanggakan-siswa-sdam-di-ajang-internasional', $post->slug);

        // 2. Toggle Publish
        $toggleResponse = $this->actingAs($this->admin)->patch(route('sistem.website.toggle', $post->id));
        $toggleResponse->assertRedirect();
        $this->assertFalse($post->fresh()->is_published);

        // 3. Update Article
        $updateResponse = $this->actingAs($this->admin)->put(route('sistem.website.update', $post->id), [
            'title' => 'Prestasi Emas Siswa SDAM di Ajang Internasional',
            'category' => 'Prestasi',
            'content' => 'Konten telah diperbarui dengan testimoni kepala sekolah.',
            'image' => 'https://example.com/prestasi-update.jpg',
            'is_published' => true,
        ]);
        $updateResponse->assertRedirect(route('sistem.website'));
        $this->assertEquals('Prestasi Emas Siswa SDAM di Ajang Internasional', $post->fresh()->title);

        // 4. Delete Article
        $deleteResponse = $this->actingAs($this->admin)->delete(route('sistem.website.destroy', $post->id));
        $deleteResponse->assertRedirect(route('sistem.website'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_landing_page_loads_published_articles(): void
    {
        Post::create([
            'title' => 'Pelepasan Program Belia Mendunia Menuju Singapura',
            'slug' => 'pelepasan-program-belia-mendunia-menuju-singapura',
            'category' => 'Berita',
            'content' => 'Acara pelepasan delegasi pertukaran pelajar internasional.',
            'is_published' => true,
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('latestNews', 1)
        );
    }
}
