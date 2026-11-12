<?php

use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StudentClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\TingkatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AkademikController;
use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TeacherController;
use App\Services\InstagramService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (InstagramService $instagramService) {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'instagramPosts' => $instagramService->getRecentPosts(4),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // 0. Dashboard Utama
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Portal Khusus Super Admin
    Route::middleware(['role:Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers.index');
        Route::get('/staffs', [AdminController::class, 'staffs'])->name('staffs.index');
        Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('users.export');
        Route::post('/users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::get('/users/template', [AdminController::class, 'downloadTemplate'])->name('users.template');
        Route::get('/modules', [AdminController::class, 'modules'])->name('modules');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

        Route::resource('tingkat', TingkatController::class);
        Route::resource('student-classes', StudentClassController::class);
        Route::get('/students/{user}/print', [StudentController::class, 'print'])->name('students.print');
        Route::resource('students', StudentController::class);
        Route::post('students/{student}/achievements', [StudentController::class, 'storeAchievement'])->name('students.achievements.store');
        Route::post('students/{student}/periodics', [StudentController::class, 'storePeriodic'])->name('students.periodics.store');
        Route::resource('subjects', SubjectController::class);
        Route::resource('teaching-assignments', TeachingAssignmentController::class);
        Route::post('schedules/auto-generate', [ScheduleController::class, 'autoGenerate'])->name('schedules.generate');
        Route::resource('schedules', ScheduleController::class);
    });

    // Portal Khusus Bendahara
    Route::middleware(['role:Bendahara|Super Admin'])->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/dashboard', [BendaharaController::class, 'dashboard'])->name('dashboard');
        Route::get('/spp', [BendaharaController::class, 'spp'])->name('spp');
        Route::get('/kasir', [BendaharaController::class, 'kasir'])->name('kasir');
        Route::get('/payroll', [BendaharaController::class, 'payroll'])->name('payroll');
    });

    // Portal Khusus Staf Sekolah
    Route::middleware(['role:Staf Sekolah|Super Admin'])->prefix('staf')->name('staf.')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::get('/absensi', [StaffController::class, 'absensi'])->name('absensi');
        Route::get('/perpustakaan', [StaffController::class, 'perpustakaan'])->name('perpustakaan');
        Route::get('/gaji', [StaffController::class, 'gaji'])->name('gaji');
    });

    // Portal Khusus Guru (Hanya Guru & Admin)
    Route::middleware(['role:Guru Pengajar|Guru BK|Super Admin'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [TeacherController::class, 'jadwal'])->name('jadwal');

        // Absensi
        Route::get('/absensi', [TeacherController::class, 'absensi'])->name('absensi');
        Route::post('/absensi', [TeacherController::class, 'storeAbsensi'])->name('absensi.store');

        // Nilai & e-Raport
        Route::get('/nilai', [TeacherController::class, 'nilai'])->name('nilai');
        Route::post('/nilai', [TeacherController::class, 'storeNilai'])->name('nilai.store');
        Route::get('/nilai/export', [TeacherController::class, 'exportNilai'])->name('nilai.export');
        Route::post('/nilai/import', [TeacherController::class, 'importNilai'])->name('nilai.import');
        Route::get('/raport', [TeacherController::class, 'raport'])->name('raport');

        // LMS & Tugas
        Route::get('/lms', [TeacherController::class, 'lms'])->name('lms');
        Route::post('/lms', [TeacherController::class, 'storeLesson'])->name('lms.store');
        Route::delete('/lms/{lesson}', [TeacherController::class, 'destroyLesson'])->name('lms.destroy');
        Route::get('/tugas', [TeacherController::class, 'tugas'])->name('tugas');
        Route::post('/tugas', [TeacherController::class, 'storeAssignment'])->name('tugas.store');
        Route::post('/tugas/{submission}/grade', [TeacherController::class, 'gradeSubmission'])->name('tugas.grade');

        // CBT & Bank Soal
        Route::get('/cbt', [TeacherController::class, 'cbt'])->name('cbt');
        Route::post('/cbt/exam', [TeacherController::class, 'storeExam'])->name('cbt.exam.store');
        Route::post('/cbt/exam/{exam}/question', [TeacherController::class, 'storeQuestion'])->name('cbt.question.store');
        Route::patch('/cbt/exam/{exam}/toggle', [TeacherController::class, 'toggleExam'])->name('cbt.exam.toggle');

        // Bimbingan Konseling (BK)
        Route::get('/bk', [TeacherController::class, 'bk'])->name('bk');
        Route::post('/bk/violation', [TeacherController::class, 'storeViolation'])->name('bk.violation.store');
        Route::post('/bk/counseling', [TeacherController::class, 'storeCounseling'])->name('bk.counseling.store');

        // Perpustakaan & Payroll
        Route::get('/perpustakaan', [TeacherController::class, 'perpustakaan'])->name('perpustakaan');
        Route::get('/payroll', [TeacherController::class, 'payroll'])->name('payroll');
    });

    // 1. KTS & Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kts', function () {
        return Inertia::render('Student/KTS');
    })->name('kts');

    // 2. Modul Akademik (SIAKAD)
    Route::prefix('akademik')->name('akademik.')->group(function () {
        Route::get('/absensi', [AkademikController::class, 'absensi'])->name('absensi');
        Route::get('/jadwal', function () {
            return Inertia::render('Placeholder', ['title' => 'Jadwal Pelajaran']);
        })->name('jadwal');
        Route::get('/nilai', function () {
            return Inertia::render('Placeholder', ['title' => 'e-Raport & Nilai']);
        })->name('nilai');
    });

    // 3. Modul LMS & CBT
    Route::prefix('learning')->name('learning.')->group(function () {
        Route::get('/lms', function () {
            return Inertia::render('Placeholder', ['title' => 'Learning Management System']);
        })->name('lms');
        Route::get('/cbt', [CBTController::class, 'index'])->name('cbt');
        Route::get('/perpustakaan', function () {
            return Inertia::render('Placeholder', ['title' => 'Perpustakaan Digital']);
        })->name('perpustakaan');
    });

    // 4. Modul Keuangan & e-Kantin
    Route::prefix('finansial')->name('finansial.')->group(function () {
        Route::get('/spp', function () {
            return Inertia::render('Placeholder', ['title' => 'Tagihan SPP']);
        })->name('spp');
        Route::get('/dompet', function () {
            return Inertia::render('Placeholder', ['title' => 'Dompet Digital']);
        })->name('dompet');
        Route::get('/kantin', [KantinController::class, 'index'])->name('kantin');
        Route::get('/payroll', function () {
            return Inertia::render('Placeholder', ['title' => 'Payroll & Penggajian']);
        })->name('payroll');
    });

    // 5. Modul BK & PPDB
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/bk', function () {
            return Inertia::render('Placeholder', ['title' => 'Bimbingan Konseling']);
        })->name('bk');
        Route::get('/ppdb', function () {
            return Inertia::render('Placeholder', ['title' => 'Penerimaan Siswa Baru']);
        })->name('ppdb');
    });

    // 6. Pengaturan & Sistem
    Route::prefix('sistem')->name('sistem.')->group(function () {
        Route::get('/pengaturan', function () {
            return Inertia::render('Placeholder', ['title' => 'Pengaturan Modul']);
        })->name('pengaturan');
        Route::get('/wa-gateway', function () {
            return Inertia::render('Placeholder', ['title' => 'WhatsApp Gateway']);
        })->name('wa');
        Route::get('/website', function () {
            return Inertia::render('Placeholder', ['title' => 'CMS Website Sekolah']);
        })->name('website');
    });
});

require __DIR__.'/auth.php';
