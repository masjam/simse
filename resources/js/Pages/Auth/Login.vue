<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import RoleHeroPanel from './Partials/RoleHeroPanel.vue';
import LoginCard from './Partials/LoginCard.vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: '',
    },
    initialRole: {
        type: String,
        default: 'guru',
    },
    otpSentPhone: {
        type: String,
        default: '',
    },
});

const roles = [
    {
        id: 'guru',
        title: 'Guru & Pendidik',
        shortTitle: 'Guru',
        badge: 'Pendidik & Ustadz/ah',
        portalName: 'Portal Akademik Guru',
        desc: 'Akses jadwal mengajar, e-Raport, absensi kelas, dan bank soal ujian CBT SD Muhammadiyah Al Mujahidin.',
        placeholder: 'nama.guru@sdmujahidin-wns.sch.id / NIP',
        features: [
            'Input Nilai Harian, PTS, dan PAS Otomatis',
            'Manajemen Jadwal Mengajar Bebas Bentrok',
            'Ujian Online CBT Anti-Curang & Tugas LMS',
            'Rekapitulasi Slip Gaji & Payroll Transparan',
        ],
    },
    {
        id: 'siswa',
        title: 'Siswa & Peserta Didik',
        shortTitle: 'Siswa',
        badge: 'Peserta Didik',
        portalName: 'Portal Akademik Siswa',
        desc: 'Pantau jadwal pelajaran, presensi harian, materi LMS, tugas, dan ujian CBT.',
        placeholder: 'nisn.siswa@sdmujahidin-wns.sch.id / NISN',
        features: [
            'KTS Digital Ber-QR Code untuk Presensi & Perpus',
            'Akses Materi Ajar Digital & Pengumpulan Tugas',
            'Ujian Online CBT & Pemantauan Nilai Harian',
            'Capaian Mutaba\'ah Hafalan Tahfidz Qur\'an',
        ],
    },
    {
        id: 'ortu',
        title: 'Orang Tua / Wali Siswa',
        shortTitle: 'Wali Santri',
        badge: 'Wali Siswa',
        portalName: 'Portal Wali Siswa',
        desc: 'Pantau kehadiran anak, e-Raport, tagihan SPP, dan saldo jajan sehat e-Kantin.',
        placeholder: 'wali.siswa@sdmujahidin-wns.sch.id',
        features: [
            'Notifikasi Kehadiran Anak Otomatis ke WhatsApp',
            'Monitoring Tagihan SPP & Riwayat Pembayaran',
            'Pantau Saldo & Riwayat Jajan Sehat e-Kantin',
            'Laporan Perkembangan Hafalan & e-Raport',
        ],
    },
    {
        id: 'staf',
        title: 'Staf & Layanan Sekolah',
        shortTitle: 'Staf',
        badge: 'Tata Usaha & Layanan',
        portalName: 'Portal Staf & Tata Usaha',
        desc: 'Layanan administrasi sekolah, sirkulasi perpustakaan, absensi, dan operasional kantin.',
        placeholder: 'staf@sdmujahidin-wns.sch.id',
        features: [
            'Layanan Sirkulasi Peminjaman Perpustakaan',
            'Presensi Kehadiran Pegawai & Staf Harian',
            'Administrasi Surat & Arsip Tata Usaha',
            'Akses Rincian Slip Gaji & Payroll Bulanan',
        ],
    },
    {
        id: 'bendahara',
        title: 'Bendahara Sekolah',
        shortTitle: 'Bendahara',
        badge: 'Keuangan & Kas',
        portalName: 'Portal Keuangan & Bendahara',
        desc: 'Pengelolaan tagihan SPP siswa, kasir kas sekolah, dan rekapitulasi payroll pegawai.',
        placeholder: 'bendahara@sdmujahidin-wns.sch.id',
        features: [
            'Pembukuan Tagihan SPP & Invoice Otomatis',
            'Point of Sales (POS) Kasir Kas Sekolah',
            'Kalkulasi Honor Jam Mengajar & Slip Gaji',
            'Laporan Neraca Keuangan Harian & Bulanan',
        ],
    },
    {
        id: 'admin',
        title: 'Administrator SIMSE',
        shortTitle: 'Admin',
        badge: 'Super Admin',
        portalName: 'Portal Master Administrator',
        desc: 'Kendali penuh hak akses, sinkronisasi data master, jadwal otomatis, dan modul sistem.',
        placeholder: 'admin@sdmujahidin-wns.sch.id',
        features: [
            'Auto-Generate Jadwal Cerdas Bebas Bentrok',
            'Manajemen Data Siswa, Guru, dan Kelas',
            'Kendali Modul Aktif & Izin Akses Pengguna',
            'Monitoring Log Keamanan & Audit Sistem',
        ],
    },
];

const selectedRole = ref(props.initialRole || 'guru');

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    if (roleParam && roles.some(r => r.id === roleParam)) {
        selectedRole.value = roleParam;
    }
});

const currentRole = computed(() => {
    return roles.find(r => r.id === selectedRole.value) || roles[0];
});

const selectRole = (roleId) => {
    selectedRole.value = roleId;
    if (route().has('role.login')) {
        window.history.replaceState({}, '', route('role.login', { role: roleId }));
    }
};
</script>

<template>
    <Head :title="`Masuk ${currentRole.title} - SD Muhammadiyah Al Mujahidin Wonosari`" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between max-w-6xl mx-auto">
            <!-- Header Nav -->
            <div class="flex items-center justify-between pb-6 border-b border-line">
                <Link href="/">
                    <AppButton variant="secondary" size="sm">
                        ← Kembali ke Beranda
                    </AppButton>
                </Link>
                <div class="flex items-center gap-2 text-xs text-ink-muted">
                    <span class="hidden sm:inline">Pendaftaran Siswa Baru:</span>
                    <Link :href="route('ppdb.register')">
                        <AppButton variant="outline" size="sm">
                            Daftar PPDB Online →
                        </AppButton>
                    </Link>
                </div>
            </div>

            <!-- Role Selector Tabs -->
            <div class="my-6">
                <p class="text-xs font-bold uppercase tracking-wider text-ink-muted text-center mb-3">
                    Pilih Jalur Masuk Berdasarkan Peran:
                </p>
                <div class="bg-surface rounded-card border border-line p-1.5 shadow-card flex flex-wrap gap-1 max-w-3xl mx-auto">
                    <AppButton
                        v-for="r in roles"
                        :key="r.id"
                        size="sm"
                        :variant="currentRole.id === r.id ? 'primary' : 'ghost'"
                        class="flex-1 min-w-[100px]"
                        @click="selectRole(r.id)"
                    >
                        {{ r.shortTitle }}
                    </AppButton>
                </div>
            </div>

            <!-- Dual-Panel Login Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch my-auto">
                <!-- Left: Hero Role Panel -->
                <RoleHeroPanel :role="currentRole" />

                <!-- Right: Modular Login Card Form -->
                <LoginCard
                    :role="currentRole"
                    :can-reset-password="canResetPassword"
                    :status="status"
                    :otp-sent-phone="otpSentPhone"
                />
            </div>

            <!-- Footer -->
            <div class="pt-8 text-center text-xs text-ink-muted">
                &copy; {{ new Date().getFullYear() }} SD Muhammadiyah Al Mujahidin Wonosari • SIMSE Terintegrasi
            </div>
        </div>
    </PortalShell>
</template>
