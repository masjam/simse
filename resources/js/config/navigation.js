/**
 * Central Navigation Configuration for SIMSE
 * SD Muhammadiyah Al Mujahidin Wonosari
 *
 * Structure: Role -> Groups[] -> Items[] { label, route, icon, module, badge }
 */

export const navigationConfig = {
    'Super Admin': [
        {
            group: 'Utama',
            items: [
                { label: 'Dashboard', route: 'admin.dashboard', icon: 'home' },
                { label: 'PPDB Online', route: 'admin.ppdb.index', icon: 'user-plus', module: 'ppdb' },
            ],
        },
        {
            group: 'Data Induk Akademik',
            items: [
                { label: 'Data Siswa', route: 'admin.students.index', icon: 'users' },
                { label: 'Data Guru', route: 'admin.teachers.index', icon: 'briefcase' },
                { label: 'Data Staf', route: 'admin.staffs.index', icon: 'user-check' },
                { label: 'Rombel & Kelas', route: 'admin.student-classes.index', icon: 'layers' },
                { label: 'Mata Pelajaran', route: 'admin.subjects.index', icon: 'book' },
                { label: 'Jadwal Pelajaran', route: 'admin.schedules.index', icon: 'calendar' },
            ],
        },
        {
            group: 'Konfigurasi Sistem',
            items: [
                { label: 'Modul Sistem', route: 'admin.modules', icon: 'sliders' },
                { label: 'Pengaturan Sekolah', route: 'admin.settings', icon: 'settings' },
            ],
        },
    ],

    'Guru Pengajar': [
        {
            group: 'Utama',
            items: [
                { label: 'Dashboard', route: 'guru.dashboard', icon: 'home' },
                { label: 'Jadwal Mengajar', route: 'guru.jadwal', icon: 'calendar' },
            ],
        },
        {
            group: 'Pembelajaran',
            items: [
                { label: 'Jurnal Mengajar', route: 'guru.jurnal', icon: 'edit-3' },
                { label: 'Presensi Siswa', route: 'guru.absensi', icon: 'check-square' },
                { label: 'LMS & Materi', route: 'guru.lms', icon: 'book-open', module: 'lms' },
                { label: 'Penugasan Tugas', route: 'guru.tugas', icon: 'file-text', module: 'lms' },
                { label: 'CBT Ujian Online', route: 'guru.cbt', icon: 'cpu', module: 'cbt' },
                { label: 'Nilai & e-Raport', route: 'guru.nilai', icon: 'award' },
            ],
        },
        {
            group: 'Layanan Siswa & Kepegawaian',
            items: [
                { label: 'Bimbingan Konseling', route: 'guru.bk', icon: 'shield', module: 'bk' },
                { label: 'Slip Gaji', route: 'guru.payroll', icon: 'dollar-sign' },
            ],
        },
    ],

    'Bendahara': [
        {
            group: 'Utama',
            items: [
                { label: 'Dashboard Keuangan', route: 'bendahara.dashboard', icon: 'home' },
                { label: 'Kasir Pembayaran', route: 'bendahara.kasir', icon: 'credit-card' },
            ],
        },
        {
            group: 'Manajemen Keuangan',
            items: [
                { label: 'Tagihan SPP', route: 'bendahara.spp', icon: 'file-text' },
                { label: 'Dompet Digital Siswa', route: 'bendahara.dompet', icon: 'wallet' },
                { label: 'Payroll Pegawai', route: 'bendahara.payroll', icon: 'users' },
                { label: 'Laporan Kantin', route: 'bendahara.kantin', icon: 'coffee', module: 'kantin' },
            ],
        },
    ],

    'Staf Sekolah': [
        {
            group: 'Utama',
            items: [
                { label: 'Dashboard', route: 'staf.dashboard', icon: 'home' },
                { label: 'Presensi GPS', route: 'staf.absensi', icon: 'map-pin' },
                { label: 'Perpustakaan', route: 'staf.perpustakaan', icon: 'book', module: 'perpustakaan' },
                { label: 'Slip Gaji', route: 'staf.gaji', icon: 'file-text' },
            ],
        },
    ],

    'Siswa': [
        {
            group: 'Utama',
            items: [
                { label: 'Beranda', route: 'siswa.dashboard', icon: 'home' },
                { label: 'Jadwal', route: 'siswa.jadwal', icon: 'calendar' },
                { label: 'Materi', route: 'siswa.materi-mingguan', icon: 'book-open' },
                { label: 'Tugas LMS', route: 'siswa.lms', icon: 'file-text', module: 'lms' },
                { label: 'Ujian CBT', route: 'siswa.cbt', icon: 'cpu', module: 'cbt' },
                { label: 'Nilai', route: 'siswa.nilai', icon: 'award' },
                { label: 'Kantin', route: 'siswa.kantin', icon: 'shopping-bag', module: 'kantin' },
                { label: 'KTS Digital', route: 'siswa.kts', icon: 'user' },
            ],
        },
    ],

    'Orang Tua': [
        {
            group: 'Utama',
            items: [
                { label: 'Beranda', route: 'ortu.dashboard', icon: 'home' },
            ],
        },
    ],

    'Pengelola Stand Kantin': [
        {
            group: 'Utama',
            items: [
                { label: 'Dashboard Stand', route: 'kantin.dashboard', icon: 'home' },
                { label: 'Daftar Menu', route: 'kantin.menus', icon: 'coffee' },
                { label: 'Scan Pengambilan', route: 'kantin.scan', icon: 'maximize' },
                { label: 'Kasir Kartu RFID', route: 'kantin.rfid', icon: 'credit-card' },
            ],
        },
    ],
};

/**
 * Filter navigation items based on active modules and route existence.
 */
export function getNavigationForRole(roleName, activeModules = {}) {
    const groups = navigationConfig[roleName] || [];
    return groups
        .map((grp) => {
            const filteredItems = grp.items.filter((item) => {
                if (item.module && activeModules[item.module] === false) {
                    return false;
                }
                return true;
            });
            return {
                ...grp,
                items: filteredItems,
            };
        })
        .filter((grp) => grp.items.length > 0);
}

