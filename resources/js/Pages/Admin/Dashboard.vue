<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            users_count: 0,
            teachers_count: 0,
            students_count: 0,
        }),
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Dashboard Overview"
                subtitle="Selamat datang kembali di Master Control Panel SIMSE SD Muhammadiyah Al Mujahidin"
            />

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <StatCard
                    title="Total Pengguna"
                    :value="stats.users_count"
                    subtitle="Seluruh akun aktif"
                    icon-bg="brand"
                />
                <StatCard
                    title="Total Guru Pengajar"
                    :value="stats.teachers_count"
                    subtitle="Pendidik & wali kelas"
                    icon-bg="success"
                />
                <StatCard
                    title="Total Siswa"
                    :value="stats.students_count"
                    subtitle="Peserta didik terdaftar"
                    icon-bg="accent"
                />
            </div>

            <!-- Quick Actions -->
            <AppCard class="space-y-4">
                <h3 class="text-sm font-bold text-ink">Aksi Cepat Administrator</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link :href="route('admin.students.index')" class="block">
                        <div class="p-4 rounded-control bg-surface-raised border border-line hover:border-brand-500 hover:shadow-card transition flex flex-col items-center justify-center gap-2 text-center h-full">
                            <span class="text-2xl">🎓</span>
                            <div>
                                <p class="font-bold text-ink text-xs">Data Siswa</p>
                                <p class="text-[11px] text-ink-muted">Kelola peserta didik</p>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('admin.teachers.index')" class="block">
                        <div class="p-4 rounded-control bg-surface-raised border border-line hover:border-brand-500 hover:shadow-card transition flex flex-col items-center justify-center gap-2 text-center h-full">
                            <span class="text-2xl">👨‍🏫</span>
                            <div>
                                <p class="font-bold text-ink text-xs">Data Guru</p>
                                <p class="text-[11px] text-ink-muted">Ustadz & ustadzah</p>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('admin.staffs.index')" class="block">
                        <div class="p-4 rounded-control bg-surface-raised border border-line hover:border-brand-500 hover:shadow-card transition flex flex-col items-center justify-center gap-2 text-center h-full">
                            <span class="text-2xl">💼</span>
                            <div>
                                <p class="font-bold text-ink text-xs">Data Staf</p>
                                <p class="text-[11px] text-ink-muted">Karyawan & TU</p>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('admin.settings')" class="block">
                        <div class="p-4 rounded-control bg-surface-raised border border-line hover:border-brand-500 hover:shadow-card transition flex flex-col items-center justify-center gap-2 text-center h-full">
                            <span class="text-2xl">⚙️</span>
                            <div>
                                <p class="font-bold text-ink text-xs">Pengaturan Sistem</p>
                                <p class="text-[11px] text-ink-muted">Konfigurasi & modul</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
