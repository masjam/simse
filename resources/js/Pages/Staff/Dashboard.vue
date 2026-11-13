<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

defineProps({
    stats: { type: Object, required: true },
    recentAttendances: { type: Array, required: true },
});
</script>

<template>
    <Head title="Dashboard Staf" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Dashboard Staf Operasional"
                subtitle="Ringkasan aktivitas operasional harian, presensi geolokasi, dan sirkulasi perpustakaan SD Muhammadiyah Al Mujahidin"
            />

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Presensi Hari Ini"
                    :value="stats.today_status"
                    :subtitle="`Masuk: ${stats.time_in} • Pulang: ${stats.time_out}`"
                    :icon-bg="stats.today_status === 'Hadir' ? 'success' : (stats.today_status === 'Terlambat' ? 'warning' : 'brand')"
                >
                    <template #icon>📍</template>
                </StatCard>

                <StatCard
                    title="Kehadiran Bulan Ini"
                    :value="`${stats.present_days} Hari`"
                    subtitle="Tercatat dalam Payroll"
                    icon-bg="success"
                >
                    <template #icon>📅</template>
                </StatCard>

                <StatCard
                    title="Sirkulasi Pustaka"
                    :value="`${stats.active_loans} Buku`"
                    :subtitle="stats.overdue_loans > 0 ? `${stats.overdue_loans} Melewati Tempo` : 'Semua Tepat Waktu'"
                    :icon-bg="stats.overdue_loans > 0 ? 'danger' : 'accent'"
                >
                    <template #icon>📚</template>
                </StatCard>

                <StatCard
                    title="Slip Gaji Terakhir"
                    :value="stats.latest_salary"
                    subtitle="Honorarium Terverifikasi"
                    icon-bg="brand"
                >
                    <template #icon>💵</template>
                </StatCard>
            </div>

            <!-- Quick Access & Riwayat Presensi -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Navigation -->
                <div class="lg:col-span-1">
                    <AppCard>
                        <h3 class="text-base font-bold text-ink mb-4">Aksi Cepat Staf</h3>
                        <div class="space-y-3">
                            <Link
                                :href="route('staf.absensi')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-brand-50 text-brand-700 rounded-control flex items-center justify-center font-bold shrink-0">
                                    📍
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Presensi Geolokasi GPS</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        Check-in / out dalam radius sekolah
                                    </p>
                                </div>
                            </Link>

                            <Link
                                :href="route('staf.perpustakaan')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-accent-50 text-accent-700 rounded-control flex items-center justify-center font-bold shrink-0">
                                    📖
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Sirkulasi Perpustakaan</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        Peminjaman & pengembalian buku
                                    </p>
                                </div>
                            </Link>

                            <Link
                                :href="route('staf.gaji')"
                                class="w-full flex items-center gap-3.5 p-3.5 rounded-control border border-line bg-surface hover:bg-surface-raised hover:border-brand-300 transition group"
                            >
                                <div class="w-10 h-10 bg-status-success-bg text-status-success-fg rounded-control flex items-center justify-center font-bold shrink-0">
                                    🧾
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-bold text-ink text-sm">Slip Gaji Digital</p>
                                    <p class="text-xs text-ink-muted truncate">
                                        Lihat & cetak slip honorarium staf
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </AppCard>
                </div>

                <!-- Recent Attendance List -->
                <div class="lg:col-span-2">
                    <AppCard>
                        <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                            <div>
                                <h3 class="text-base font-bold text-ink">Riwayat Presensi Terbaru</h3>
                                <p class="text-xs text-ink-muted">Catatan presensi masuk dan pulang staf</p>
                            </div>
                            <Link :href="route('staf.absensi')">
                                <AppButton variant="secondary" size="sm">
                                    Presensi GPS &rarr;
                                </AppButton>
                            </Link>
                        </div>

                        <div v-if="recentAttendances.length === 0">
                            <EmptyState
                                title="Belum Ada Presensi"
                                description="Belum ada catatan presensi kehadiran staf yang terekam bulan ini."
                            />
                        </div>

                        <div v-else class="divide-y divide-line">
                            <div
                                v-for="att in recentAttendances"
                                :key="att.id"
                                class="py-3 flex items-center justify-between text-xs"
                            >
                                <div class="space-y-0.5">
                                    <p class="font-bold text-ink">{{ att.date }}</p>
                                    <p class="text-ink-muted font-numeric">
                                        Masuk: <strong class="text-ink">{{ att.time_in }}</strong> • Pulang: <strong class="text-ink">{{ att.time_out }}</strong>
                                    </p>
                                </div>
                                <StatusBadge
                                    :variant="att.status === 'Hadir' ? 'success' : 'warning'"
                                    size="sm"
                                >
                                    {{ att.status }}
                                </StatusBadge>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
