<?php

use App\Http\Controllers\AchievementVerificationController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StudentClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\TingkatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\CanteenController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\KioskDeviceController;
use App\Http\Controllers\KioskPresensiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentCanteenController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TeacherController;
use App\Models\Post;
use App\Services\InstagramService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', function (InstagramService $instagramService) {
    $latestNews = Schema::hasTable('posts')
        ? Post::query()
            ->where('is_published', true)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'category' => $p->category ?? 'Berita',
                'date' => $p->created_at?->format('d M Y') ?? date('d M Y'),
                'readTime' => '3 Menit Baca',
                'excerpt' => Str::limit(strip_tags($p->content), 140),
                'img' => $p->image ?: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
                'badgeColor' => match ($p->category) {
                    'Prestasi' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                    'Pengumuman' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
                    'Artikel' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                    default => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                },
            ])
        : collect();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'instagramPosts' => $instagramService->getRecentPosts(4),
        'latestNews' => $latestNews,
    ]);
})->name('home');

// PPDB Pendaftaran Online Siswa Baru (Publik)
Route::prefix('ppdb')->name('ppdb.')->group(function () {
    Route::get('/daftar', [PpdbController::class, 'form'])->name('form');
    Route::post('/daftar', [PpdbController::class, 'store'])->name('store');
    Route::get('/sukses', [PpdbController::class, 'success'])->name('success');
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

        // PPDB Admin
        Route::get('/ppdb', [PpdbController::class, 'index'])->name('ppdb.index');
        Route::post('/ppdb/{registration}/status', [PpdbController::class, 'updateStatus'])->name('ppdb.status');
        Route::get('/ppdb/{registration}/doc/{type}', [PpdbController::class, 'downloadDocument'])->name('ppdb.document');
    });

    // Portal Khusus Bendahara
    Route::middleware(['role:Bendahara|Super Admin'])->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/dashboard', [BendaharaController::class, 'dashboard'])->name('dashboard');

        Route::middleware('module:keuangan')->group(function () {
            // Kasir Pembayaran & Verifikasi Bukti Transfer
            Route::get('/kasir', [BendaharaController::class, 'kasir'])->name('kasir');
            Route::post('/kasir/pay', [BendaharaController::class, 'storePayment'])->name('kasir.pay');
            Route::post('/payments/{payment}/verify', [BendaharaController::class, 'verifyPayment'])->name('payments.verify');
            Route::get('/payments/{payment}/receipt', [BendaharaController::class, 'downloadReceipt'])->name('payments.receipt');

            // Manajemen SPP & Pos Tagihan
            Route::get('/spp', [BendaharaController::class, 'spp'])->name('spp');
            Route::post('/spp/bill-types', [BendaharaController::class, 'storeBillType'])->name('spp.bill-types.store');
            Route::put('/spp/bill-types/{billType}', [BendaharaController::class, 'updateBillType'])->name('spp.bill-types.update');
            Route::delete('/spp/bill-types/{billType}', [BendaharaController::class, 'destroyBillType'])->name('spp.bill-types.destroy');
            Route::post('/spp/generate', [BendaharaController::class, 'generateBills'])->name('spp.generate');
            Route::delete('/spp/bills/{bill}', [BendaharaController::class, 'destroyBill'])->name('spp.bills.destroy');
            Route::post('/spp/remind', [BendaharaController::class, 'sendArrearsReminder'])->name('spp.remind');

            // Tabungan & Dompet Digital Siswa
            Route::get('/dompet', [BendaharaController::class, 'dompet'])->name('dompet');
            Route::post('/dompet/transaction', [BendaharaController::class, 'storeWalletTransaction'])->name('dompet.transaction');
            Route::post('/dompet/{student}/pin', [BendaharaController::class, 'updateWalletPin'])->name('dompet.pin');
            Route::post('/dompet/{student}/rfid', [BendaharaController::class, 'updateStudentRfid'])->name('dompet.rfid');
        });

        // Payroll & Penggajian Pegawai
        Route::middleware('module:payroll')->group(function () {
            Route::get('/payroll', [BendaharaController::class, 'payroll'])->name('payroll');
            Route::post('/payroll/generate', [BendaharaController::class, 'generatePayroll'])->name('payroll.generate');
            Route::post('/payroll/{payroll}/components', [BendaharaController::class, 'storePayrollComponent'])->name('payroll.components.store');
            Route::delete('/payroll/components/{component}', [BendaharaController::class, 'destroyPayrollComponent'])->name('payroll.components.destroy');
            Route::patch('/payroll/{payroll}/status', [BendaharaController::class, 'updatePayrollStatus'])->name('payroll.status');
            Route::post('/payroll/submit-approval', [BendaharaController::class, 'submitPayrollApproval'])->name('payroll.submit-approval');
        });

        // Laporan e-Kantin
        Route::get('/kantin', [BendaharaController::class, 'kantinReport'])->middleware('module:kantin')->name('kantin');
    });

    // Portal Khusus Pengelola Stand Kantin
    Route::middleware(['role:Pengelola Stand Kantin|Super Admin', 'module:kantin'])->prefix('kantin')->name('kantin.')->group(function () {
        Route::get('/dashboard', [CanteenController::class, 'dashboard'])->name('dashboard');
        Route::post('/toggle-open', [CanteenController::class, 'toggleOpen'])->name('toggle-open');
        Route::post('/stand', [CanteenController::class, 'updateStand'])->name('stand.update');
        Route::get('/menus', [CanteenController::class, 'menus'])->name('menus');
        Route::post('/menus', [CanteenController::class, 'storeMenu'])->name('menus.store');
        Route::put('/menus/{menu}', [CanteenController::class, 'updateMenu'])->name('menus.update');
        Route::delete('/menus/{menu}', [CanteenController::class, 'destroyMenu'])->name('menus.destroy');
        Route::patch('/orders/{order}/status', [CanteenController::class, 'updateOrderStatus'])->name('orders.status');
        Route::get('/scan', [CanteenController::class, 'scanQr'])->name('scan');
        Route::post('/scan', [CanteenController::class, 'validateQr'])->name('scan.validate');
        Route::get('/rfid', [CanteenController::class, 'rfidPos'])->name('rfid');
        Route::post('/rfid/check-balance', [CanteenController::class, 'checkRfidBalance'])->name('rfid.check-balance');
        Route::post('/rfid/pay', [CanteenController::class, 'payWithRfid'])->name('rfid.pay');
    });

    // Portal Khusus Staf Sekolah
    Route::middleware(['role:Staf Sekolah|Super Admin'])->prefix('staf')->name('staf.')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');

        // Presensi Pegawai (Geolokasi & Radius)
        Route::middleware('module:absensi')->group(function () {
            Route::get('/absensi', [StaffController::class, 'absensi'])->name('absensi');
            Route::post('/absensi', [StaffController::class, 'storeAbsensi'])->name('absensi.store');
        });

        // Perpustakaan & Sirkulasi Buku
        Route::middleware('module:perpustakaan')->group(function () {
            Route::get('/perpustakaan', [StaffController::class, 'perpustakaan'])->name('perpustakaan');
            Route::post('/perpustakaan/pinjam', [StaffController::class, 'borrowBook'])->name('perpustakaan.borrow');
            Route::post('/perpustakaan/kembali/{bookLoan}', [StaffController::class, 'returnBook'])->name('perpustakaan.return');
        });

        // Slip Gaji Staf
        Route::get('/gaji', [StaffController::class, 'gaji'])->middleware('module:payroll')->name('gaji');
    });

    // Portal Khusus Guru (Hanya Guru & Admin)
    Route::middleware(['role:Guru Pengajar|Guru BK|Super Admin'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [TeacherController::class, 'jadwal'])->name('jadwal');

        // Absensi
        Route::middleware('module:absensi')->group(function () {
            Route::get('/absensi', [TeacherController::class, 'absensi'])->name('absensi');
            Route::post('/absensi', [TeacherController::class, 'storeAbsensi'])->name('absensi.store');
        });

        // Nilai & e-Raport
        Route::get('/nilai', [TeacherController::class, 'nilai'])->name('nilai');
        Route::post('/nilai', [TeacherController::class, 'storeNilai'])->name('nilai.store');
        Route::get('/nilai/export', [TeacherController::class, 'exportNilai'])->name('nilai.export');
        Route::post('/nilai/import', [TeacherController::class, 'importNilai'])->name('nilai.import');
        Route::get('/raport', [TeacherController::class, 'raport'])->name('raport');

        // LMS & Tugas
        Route::middleware('module:lms')->group(function () {
            Route::get('/lms', [TeacherController::class, 'lms'])->name('lms');
            Route::post('/lms', [TeacherController::class, 'storeLesson'])->name('lms.store');
            Route::get('/lms/{lesson}/attachment', [TeacherController::class, 'downloadLessonAttachment'])->name('lms.attachment');
            Route::delete('/lms/{lesson}', [TeacherController::class, 'destroyLesson'])->name('lms.destroy');
            Route::get('/jurnal', [TeacherController::class, 'jurnal'])->name('jurnal');
            Route::post('/jurnal', [TeacherController::class, 'storeJurnal'])->name('jurnal.store');
            Route::put('/jurnal/{journal}', [TeacherController::class, 'updateJurnal'])->name('jurnal.update');
            Route::delete('/jurnal/{journal}', [TeacherController::class, 'destroyJurnal'])->name('jurnal.destroy');
            Route::get('/tugas', [TeacherController::class, 'tugas'])->name('tugas');
            Route::post('/tugas', [TeacherController::class, 'storeAssignment'])->name('tugas.store');
            Route::post('/tugas/{submission}/grade', [TeacherController::class, 'gradeSubmission'])->name('tugas.grade');
        });

        // CBT & Bank Soal
        Route::middleware('module:cbt')->group(function () {
            Route::get('/cbt', [TeacherController::class, 'cbt'])->name('cbt');
            Route::post('/cbt/exam', [TeacherController::class, 'storeExam'])->name('cbt.exam.store');
            Route::post('/cbt/exam/{exam}/question', [TeacherController::class, 'storeQuestion'])->name('cbt.question.store');
            Route::patch('/cbt/exam/{exam}/toggle', [TeacherController::class, 'toggleExam'])->name('cbt.exam.toggle');
        });

        // Bimbingan Konseling (BK)
        Route::middleware('module:bk')->group(function () {
            Route::get('/bk', [TeacherController::class, 'bk'])->name('bk');
            Route::post('/bk/violation', [TeacherController::class, 'storeViolation'])->name('bk.violation.store');
            Route::post('/bk/counseling', [TeacherController::class, 'storeCounseling'])->name('bk.counseling.store');
        });

        // Perpustakaan & Payroll
        Route::get('/perpustakaan', [TeacherController::class, 'perpustakaan'])->middleware('module:perpustakaan')->name('perpustakaan');
        Route::get('/payroll', [TeacherController::class, 'payroll'])->middleware('module:payroll')->name('payroll');
    });

    // Portal Khusus Siswa
    Route::middleware(['role:Siswa|Super Admin'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [StudentPortalController::class, 'jadwal'])->name('jadwal');
        Route::get('/nilai', [StudentPortalController::class, 'nilai'])->name('nilai');
        Route::get('/presensi', [StudentPortalController::class, 'presensi'])->middleware('module:absensi')->name('presensi');

        // LMS, Materi Mingguan & Pengumpulan Tugas
        Route::middleware('module:lms')->group(function () {
            Route::get('/lms', [StudentPortalController::class, 'lms'])->name('lms');
            Route::get('/materi-mingguan', [StudentPortalController::class, 'materiMingguan'])->name('materi-mingguan');
            Route::get('/lms/{lesson}/attachment', [StudentPortalController::class, 'downloadLessonAttachment'])->name('lms.attachment');
            Route::post('/tugas/{assignment}/submit', [StudentPortalController::class, 'submitAssignment'])->name('tugas.submit');
            Route::get('/submissions/{submission}/download', [StudentPortalController::class, 'downloadSubmissionFile'])->name('submissions.download');
        });

        // CBT & Anti-Cheat
        Route::middleware('module:cbt')->group(function () {
            Route::get('/cbt', [StudentPortalController::class, 'cbt'])->name('cbt');
            Route::get('/cbt/{exam}', [StudentPortalController::class, 'startExam'])->name('cbt.start');
            Route::post('/cbt/{exam}/answer', [StudentPortalController::class, 'saveExamAnswer'])->name('cbt.answer');
            Route::post('/cbt/{exam}/violation', [StudentPortalController::class, 'recordExamViolation'])->name('cbt.violation');
            Route::post('/cbt/{exam}/submit', [StudentPortalController::class, 'submitExam'])->name('cbt.submit');
        });

        // KTS Digital Siswa
        Route::get('/kts', [StudentPortalController::class, 'kts'])->name('kts');

        // e-Kantin Digital Siswa
        Route::middleware('module:kantin')->group(function () {
            Route::get('/kantin', [StudentCanteenController::class, 'index'])->name('kantin');
            Route::post('/kantin/checkout', [StudentCanteenController::class, 'checkout'])->name('kantin.checkout');
            Route::post('/kantin/check-balance', [StudentCanteenController::class, 'checkBalanceWithPin'])->name('kantin.check-balance');
        });
    });

    // Portal Khusus Orang Tua
    Route::middleware(['role:Orang Tua|Super Admin'])->prefix('ortu')->name('ortu.')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::post('/switch-child', [ParentPortalController::class, 'switchChild'])->name('switch-child');
        Route::post('/wallet-limit', [ParentPortalController::class, 'updateWalletDailyLimit'])->name('wallet.limit');
        Route::post('/prestasi', [ParentPortalController::class, 'storeAchievement'])->name('achievements.store');
        Route::get('/prestasi/{achievement}/certificate', [ParentPortalController::class, 'downloadAchievementCertificate'])->name('achievements.certificate');
    });

    // Verifikasi Prestasi Siswa (Guru BK, TU, Staf Sekolah, Bidang Lomba, Super Admin)
    Route::middleware(['role:Guru BK|Admin Tata Usaha|Staf Sekolah|Guru Pengajar|Bidang Lomba|Super Admin'])
        ->prefix('prestasi/verifikasi')
        ->name('achievements.verification.')
        ->group(function () {
            Route::get('/', [AchievementVerificationController::class, 'index'])->name('index');
            Route::post('/{achievement}', [AchievementVerificationController::class, 'verify'])->name('verify');
            Route::get('/{achievement}/certificate', [AchievementVerificationController::class, 'downloadCertificate'])->name('certificate');
        });

    // 1. KTS & Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kts', [StudentPortalController::class, 'kts'])->middleware('role:Siswa|Super Admin')->name('kts');

    // 2. Modul Akademik (Dialihkan langsung ke portal terkait - bebas mock)
    Route::prefix('akademik')->name('akademik.')->group(function () {
        Route::get('/absensi', fn () => redirect()->route('guru.absensi'))->name('absensi');
        Route::get('/jadwal', fn () => redirect()->route('guru.jadwal'))->name('jadwal');
        Route::get('/nilai', fn () => redirect()->route('guru.nilai'))->name('nilai');
    });

    // 3. Modul LMS & CBT
    Route::prefix('learning')->name('learning.')->group(function () {
        Route::get('/lms', function () {
            return Inertia::render('Placeholder', ['title' => 'Learning Management System']);
        })->middleware('module:lms')->name('lms');
        Route::get('/cbt', [CBTController::class, 'index'])->middleware('module:cbt')->name('cbt');
        Route::get('/perpustakaan', function () {
            return Inertia::render('Placeholder', ['title' => 'Perpustakaan Digital']);
        })->middleware('module:perpustakaan')->name('perpustakaan');
    });

    // 4. Modul Keuangan & e-Kantin
    Route::prefix('finansial')->name('finansial.')->group(function () {
        Route::get('/spp', function () {
            return Inertia::render('Placeholder', ['title' => 'Tagihan SPP']);
        })->middleware('module:keuangan')->name('spp');
        Route::get('/dompet', function () {
            return Inertia::render('Placeholder', ['title' => 'Dompet Digital']);
        })->middleware('module:keuangan')->name('dompet');
        Route::get('/kantin', [KantinController::class, 'index'])->middleware('module:kantin')->name('kantin');
        Route::get('/payroll', function () {
            return Inertia::render('Placeholder', ['title' => 'Payroll & Penggajian']);
        })->middleware('module:payroll')->name('payroll');
    });

    // 5. Modul BK & PPDB
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/bk', function () {
            return Inertia::render('Placeholder', ['title' => 'Bimbingan Konseling']);
        })->middleware('module:bk')->name('bk');
        Route::get('/ppdb', function () {
            return Inertia::render('Placeholder', ['title' => 'Penerimaan Siswa Baru']);
        })->middleware('module:ppdb')->name('ppdb');
    });

    // Notifikasi In-App
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    });

    // 6. Pengaturan & Sistem
    Route::middleware(['role:Super Admin'])->prefix('sistem')->name('sistem.')->group(function () {
        Route::get('/pengaturan', [SystemSettingController::class, 'settings'])->name('pengaturan');
        Route::post('/pengaturan', [SystemSettingController::class, 'updateSettings'])->name('pengaturan.update');
        Route::patch('/modules/{module}/toggle', [SystemSettingController::class, 'toggleModule'])->name('modules.toggle');

        Route::get('/wa-gateway', [SystemSettingController::class, 'waGateway'])->name('wa');
        Route::post('/wa-gateway', [SystemSettingController::class, 'updateWaGateway'])->name('wa.update');
        Route::post('/wa-gateway/announcement', [SystemSettingController::class, 'sendAnnouncement'])->name('wa.announcement');
        Route::post('/wa-gateway/{whatsappLog}/retry', [SystemSettingController::class, 'retryWaLog'])->name('wa.retry');

        Route::get('/kiosk-devices', [KioskDeviceController::class, 'index'])->name('kiosk-devices');
        Route::post('/kiosk-devices', [KioskDeviceController::class, 'store'])->name('kiosk-devices.store');
        Route::patch('/kiosk-devices/{kioskDevice}/toggle', [KioskDeviceController::class, 'toggle'])->name('kiosk-devices.toggle');
        Route::delete('/kiosk-devices/{kioskDevice}', [KioskDeviceController::class, 'destroy'])->name('kiosk-devices.destroy');

        Route::get('/website', [CmsController::class, 'index'])->name('website');
        Route::post('/website/posts', [CmsController::class, 'store'])->name('website.store');
        Route::put('/website/posts/{post}', [CmsController::class, 'update'])->name('website.update');
        Route::delete('/website/posts/{post}', [CmsController::class, 'destroy'])->name('website.destroy');
        Route::patch('/website/posts/{post}/toggle', [CmsController::class, 'togglePublish'])->name('website.toggle');
    });
});

// Kiosk Presensi Siswa (Terkunci MAC Address PC Kiosk terdaftar)
Route::middleware(['kiosk.mac', 'module:absensi'])->prefix('kiosk-presensi')->name('kiosk.')->group(function () {
    Route::get('/', [KioskPresensiController::class, 'index'])->name('presensi');
    Route::post('/tap', [KioskPresensiController::class, 'tap'])->name('presensi.tap');
});

require __DIR__.'/auth.php';
