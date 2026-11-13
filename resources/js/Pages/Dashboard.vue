<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const userRoles = computed(() => user.value.roles || []);

const quickLinks = computed(() => {
    const links = [];
    if (userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Administrator', route: 'admin.dashboard', desc: 'Kelola data master dan sistem' });
    }
    if (userRoles.value.includes('Guru Pengajar') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Guru', route: 'guru.dashboard', desc: 'Jadwal, presensi kelas, dan nilai siswa' });
    }
    if (userRoles.value.includes('Wali Santri / Orang Tua') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Orang Tua', route: 'ortu.dashboard', desc: 'Pantau perkembangan ananda dan SPP' });
    }
    if (userRoles.value.includes('Siswa') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Siswa', route: 'siswa.dashboard', desc: 'CBT ujian, materi kelas, dan KTS digital' });
    }
    if (userRoles.value.includes('Bendahara') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Bendahara', route: 'bendahara.dashboard', desc: 'Kasir SPP, payroll, dan laporan kantin' });
    }
    if (userRoles.value.includes('Pengelola Kantin') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Kantin', route: 'kantin.dashboard', desc: 'Menu e-kantin dan transaksi POS RFID' });
    }
    if (userRoles.value.includes('Staf Karyawan') || userRoles.value.includes('Super Admin')) {
        links.push({ title: 'Portal Staf', route: 'staf.dashboard', desc: 'Presensi harian staf dan slip gaji' });
    }
    return links;
});
</script>

<template>
    <Head title="Beranda Utama SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <!-- Welcome Header -->
            <PageHeader
                :title="`Selamat Datang, ${user.name || 'Pengguna'}!`"
                subtitle="Sistem Informasi Manajemen Terpadu SD Muhammadiyah Al Mujahidin Wonosari"
            >
                <template #actions>
                    <Link :href="route('kts')">
                        <AppButton variant="secondary" size="sm">
                            Pratinjau KTS Digital
                        </AppButton>
                    </Link>
                </template>
            </PageHeader>

            <!-- Stat Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Status Akun"
                    :value="userRoles[0] || 'Pengguna'"
                    subtitle="Role aktif saat ini"
                    icon-bg="brand"
                />
                <StatCard
                    title="Presensi Sekolah"
                    value="98.5%"
                    subtitle="Tingkat kehadiran siswa"
                    trend="+1.2%"
                    :trend-up="true"
                    icon-bg="success"
                />
                <StatCard
                    title="e-Kantin Terpadu"
                    value="Aktif"
                    subtitle="Layanan transaksi cashless"
                    icon-bg="accent"
                />
                <StatCard
                    title="Ujian CBT"
                    value="Online"
                    subtitle="Sistem CBT anti-curang"
                    icon-bg="brand"
                />
            </div>

            <!-- Quick Portals Navigation -->
            <div>
                <h3 class="text-base font-bold text-ink mb-3">Pusat Portal & Layanan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.title"
                        :href="route(link.route)"
                        class="block"
                    >
                        <AppCard class="hover:border-brand-500 hover:shadow-card transition-all cursor-pointer h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-sm font-bold text-ink hover:text-brand-700">
                                        {{ link.title }}
                                    </h4>
                                    <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                        {{ link.desc }}
                                    </p>
                                </div>
                                <span class="text-xs text-brand-700 font-bold ml-2">Buka →</span>
                            </div>
                        </AppCard>
                    </Link>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
