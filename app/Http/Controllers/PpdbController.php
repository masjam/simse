<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ppdb\StorePpdbRequest;
use App\Models\AcademicYear;
use App\Models\PpdbRegistration;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\SimseNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PpdbController extends Controller
{
    private function generateRegistrationNumber(): string
    {
        $year = date('Y');
        $random = strtoupper(Str::random(5));

        return "PPDB-{$year}-{$random}";
    }

    public function form(): Response
    {
        return Inertia::render('Ppdb/Register');
    }

    public function store(StorePpdbRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $regNumber = $this->generateRegistrationNumber();

        // Simpan dokumen ke disk local privat
        $kkPath = $request->hasFile('kk_file')
            ? $request->file('kk_file')->store('ppdb/kk', 'local')
            : null;

        $birthCertPath = $request->hasFile('birth_certificate_file')
            ? $request->file('birth_certificate_file')->store('ppdb/akta', 'local')
            : null;

        $ijazahPath = $request->hasFile('ijazah_file')
            ? $request->file('ijazah_file')->store('ppdb/ijazah', 'local')
            : null;

        $registration = PpdbRegistration::create([
            'registration_number' => $regNumber,
            'student_name' => $validated['student_name'],
            'nik' => $validated['nik'],
            'nisn' => $validated['nisn'] ?? null,
            'gender' => $validated['gender'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'],
            'previous_school' => $validated['previous_school'] ?? null,
            'parent_name' => $validated['parent_name'],
            'parent_phone' => $validated['parent_phone'],
            'parent_email' => $validated['parent_email'] ?? null,
            'parent_job' => $validated['parent_job'] ?? null,
            'kk_path' => $kkPath,
            'birth_certificate_path' => $birthCertPath,
            'ijazah_path' => $ijazahPath,
            'status' => 'Menunggu Verifikasi',
        ]);

        // Kirim konfirmasi WA ke orang tua
        $waMessage = "Assalamu'alaikum Wr. Wb. Terima kasih telah mendaftar PPDB di SD Muhammadiyah Al Mujahidin Wonosari. Nomor pendaftaran ananda {$registration->student_name} adalah *{$regNumber}*. Pantau status verifikasi secara berkala.";
        app(WhatsAppService::class)->queue(
            phoneNumber: $registration->parent_phone,
            message: $waMessage,
            recipientName: $registration->parent_name,
        );

        return redirect()->route('ppdb.success', ['number' => $regNumber]);
    }

    public function success(Request $request): Response
    {
        $number = (string) $request->query('number');
        $registration = PpdbRegistration::where('registration_number', $number)->firstOrFail();

        return Inertia::render('Ppdb/Success', [
            'registration' => [
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'parent_name' => $registration->parent_name,
                'parent_phone' => $registration->parent_phone,
                'status' => $registration->status,
                'created_at' => $registration->created_at?->format('d M Y, H:i') ?? '-',
            ],
        ]);
    }

    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', 'all');

        $query = PpdbRegistration::when($search, function ($q) use ($search): void {
            $q->where(function ($sub) use ($search): void {
                $sub->where('student_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        });

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $registrations = $query->latest()->paginate(15)->through(fn (PpdbRegistration $r): array => [
            'id' => $r->id,
            'registration_number' => $r->registration_number,
            'student_name' => $r->student_name,
            'gender' => $r->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'birth_place_date' => "{$r->birth_place}, ".Carbon::parse($r->birth_date)->format('d M Y'),
            'parent_name' => $r->parent_name,
            'parent_phone' => $r->parent_phone,
            'previous_school' => $r->previous_school ?? '-',
            'status' => $r->status,
            'has_kk' => ! empty($r->kk_path),
            'has_akta' => ! empty($r->birth_certificate_path),
            'has_ijazah' => ! empty($r->ijazah_path),
            'student_id' => $r->student_id,
            'created_at' => $r->created_at?->format('d M Y, H:i') ?? '-',
        ]);

        $availableClasses = StudentClass::where('level', 1)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Ppdb/Index', [
            'registrations' => $registrations,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'availableClasses' => $availableClasses,
            'stats' => [
                'total' => PpdbRegistration::count(),
                'pending' => PpdbRegistration::where('status', 'Menunggu Verifikasi')->count(),
                'accepted' => PpdbRegistration::where('status', 'Diterima')->count(),
                'rejected' => PpdbRegistration::where('status', 'Ditolak')->count(),
            ],
        ]);
    }

    public function downloadDocument(PpdbRegistration $registration, string $type): StreamedResponse
    {
        $path = match ($type) {
            'kk' => $registration->kk_path,
            'akta' => $registration->birth_certificate_path,
            'ijazah' => $registration->ijazah_path,
            default => null,
        };

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Berkas dokumen tidak ditemukan di storage server.');
        }

        return Storage::disk('local')->download($path);
    }

    public function updateStatus(Request $request, PpdbRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Menunggu Verifikasi,Diterima,Ditolak'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
            'class_id' => ['nullable', 'uuid', 'exists:student_classes,id'],
        ]);

        if ($validated['status'] === 'Diterima') {
            DB::transaction(function () use ($registration, $validated): void {
                // Pastikan role Siswa & Orang Tua tersedia
                Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);

                // 1. Akun Orang Tua
                $parentEmail = $registration->parent_email ?: 'ortu.'.strtolower(Str::random(6)).'@sekolah.com';
                $parentUser = User::firstOrCreate(
                    ['email' => $parentEmail],
                    [
                        'name' => $registration->parent_name,
                        'phone' => $registration->parent_phone,
                        'password' => Hash::make('password123'),
                    ]
                );
                if (! $parentUser->hasRole('Orang Tua')) {
                    $parentUser->assignRole('Orang Tua');
                }

                // 2. Akun Siswa Baru
                $cleanName = Str::slug($registration->student_name, '');
                $studentEmail = "{$cleanName}.ppdb@sekolah.com";
                if (User::where('email', $studentEmail)->exists()) {
                    $studentEmail = "{$cleanName}.".strtolower(Str::random(4)).'@sekolah.com';
                }

                $studentUser = User::firstOrCreate(
                    ['email' => $studentEmail],
                    [
                        'name' => $registration->student_name,
                        'phone' => $registration->parent_phone,
                        'password' => Hash::make('siswa123'),
                    ]
                );
                if (! $studentUser->hasRole('Siswa')) {
                    $studentUser->assignRole('Siswa');
                }

                // 3. Tentukan Rombel Kelas 1 & Tahun Ajaran Aktif
                $academicYear = AcademicYear::where('is_active', true)->first()
                    ?: AcademicYear::firstOrCreate(['name' => '2026/2027', 'semester' => 'Ganjil'], ['is_active' => true]);

                $classId = $validated['class_id'] ?? null;
                if (! $classId) {
                    $class = StudentClass::where('level', 1)->first()
                        ?: StudentClass::create([
                            'academic_year_id' => $academicYear->id,
                            'name' => '1A',
                            'level' => 1,
                        ]);
                    $classId = $class->id;
                }

                // 4. Buat Record Siswa
                $nisn = $registration->nisn ?: (string) random_int(1000000000, 9999999999);
                $nis = (string) random_int(10000, 99999);

                $student = Student::create([
                    'user_id' => $studentUser->id,
                    'parent_user_id' => $parentUser->id,
                    'student_class_id' => $classId,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nik' => $registration->nik,
                    'tempat_lahir' => $registration->birth_place,
                    'tanggal_lahir' => $registration->birth_date,
                    'alamat' => $registration->address,
                    'no_telp_ortu' => $registration->parent_phone,
                    'nama_ayah' => $registration->parent_name,
                    'pekerjaan_ayah' => $registration->parent_job,
                ]);

                // 5. Inisialisasi Dompet Digital Siswa Baru
                Wallet::firstOrCreate(
                    ['student_id' => $student->id],
                    ['balance' => 0, 'pin' => '123456']
                );

                // 6. Update PPDB
                $registration->update([
                    'status' => 'Diterima',
                    'admin_notes' => $validated['admin_notes'] ?? 'Diterima dan dikonversi otomatis menjadi siswa aktif.',
                    'student_id' => $student->id,
                    'student_user_id' => $studentUser->id,
                    'parent_user_id' => $parentUser->id,
                ]);

                // Notifikasi in-app ke ortu
                $parentUser->notify(new SimseNotification(
                    title: 'Selamat! Pendaftaran PPDB Diterima',
                    message: "Ananda {$registration->student_name} resmi diterima di SD Muhammadiyah Al Mujahidin Wonosari.",
                    category: 'ppdb',
                    url: route('ortu.dashboard'),
                ));
            });

            return redirect()->back()->with('success', "Calon siswa {$registration->student_name} berhasil DITERIMA dan dikonversi menjadi siswa aktif!");
        }

        $registration->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return redirect()->back()->with('success', "Status pendaftaran {$registration->student_name} diubah menjadi {$validated['status']}.");
    }
}
