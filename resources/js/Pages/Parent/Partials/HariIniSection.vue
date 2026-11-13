<script setup>
import { computed } from 'vue';
import AppButton from '@/Components/ui/AppButton.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    activeChild: {
        type: Object,
        required: true,
    },
    overview: {
        type: Object,
        required: true,
    },
    presensi: {
        type: Object,
        required: true,
    },
    keuangan: {
        type: Object,
        required: true,
    },
    akademik: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['select-tab', 'open-payment-modal']);

// Recent attendance today if available
const todayAttendance = computed(() => {
    const list = props.presensi?.records || [];
    return list[0] || null;
});

// Unpaid bills count & total
const unpaidCount = computed(() => props.overview?.unpaid_bills_count || 0);
const unpaidTotal = computed(() => props.overview?.unpaid_bills_total || 0);

// SP Status
const spStatus = computed(() => props.overview?.sp_status || 'Aman');
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">
                    Hari Ini untuk Ananda
                </h3>
            </div>
            <span class="text-xs text-ink-muted">
                {{ new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(new Date()) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Kehadiran Hari Ini -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold text-ink-muted">Presensi Terkini</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span
                            class="px-2.5 py-1 rounded-control text-xs font-extrabold"
                            :class="todayAttendance?.status === 'Hadir' ? 'bg-status-success-bg text-status-success-fg border border-status-success-line' : 'bg-status-info-bg text-status-info-fg border border-status-info-line'"
                        >
                            {{ todayAttendance?.status || 'Belum Ada Sesi' }}
                        </span>
                        <span v-if="todayAttendance?.time_in" class="text-xs text-ink-muted">
                            Masuk: {{ todayAttendance.time_in }}
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-t border-line">
                    <AppButton
                        variant="ghost"
                        size="sm"
                        class="p-0 text-left text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline justify-start"
                        @click="emit('select-tab', 'presensi')"
                    >
                        Lihat Rekap Kehadiran ({{ overview.attendance_pct }}%) &rarr;
                    </AppButton>
                </div>
            </div>

            <!-- Tagihan SPP & Keuangan (Menonjol) -->
            <div
                class="rounded-card border p-4 shadow-card flex flex-col justify-between gap-2 transition-all"
                :class="unpaidCount > 0 ? 'bg-status-warning-bg border-status-warning-line ring-1 ring-status-warning-line' : 'bg-surface border-line'"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-ink-muted">Tagihan Sekolah</p>
                        <span
                            v-if="unpaidCount > 0"
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-status-danger-bg text-status-danger-fg"
                        >
                            {{ unpaidCount }} Perlu Bayar
                        </span>
                    </div>
                    <div class="mt-1">
                        <MoneyText
                            :amount="unpaidTotal"
                            class="text-lg sm:text-xl font-extrabold text-ink"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-line">
                    <AppButton
                        size="sm"
                        variant="primary"
                        class="w-full justify-center text-xs font-bold"
                        @click="emit('open-payment-modal')"
                    >
                        Bayar / Info Rekening
                    </AppButton>
                </div>
            </div>

            <!-- Status Pembinaan & SP -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold text-ink-muted">Status Pembinaan Siswa</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span
                            class="px-2.5 py-1 rounded-control text-xs font-extrabold"
                            :class="spStatus === 'Aman' ? 'bg-status-success-bg text-status-success-fg border border-status-success-line' : 'bg-status-warning-bg text-status-warning-fg border border-status-warning-line'"
                        >
                            {{ spStatus }}
                        </span>
                        <span class="text-xs text-ink-muted">
                            {{ overview.violation_points || 0 }} Poin
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-t border-line">
                    <AppButton
                        variant="ghost"
                        size="sm"
                        class="p-0 text-left text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline justify-start"
                        @click="emit('select-tab', 'perilaku')"
                    >
                        Lihat Catatan Guru & BK &rarr;
                    </AppButton>
                </div>
            </div>

            <!-- Capaian Akademik Rata-rata -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold text-ink-muted">Rata-rata Nilai Raport</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-ink">
                            {{ overview.average_score || '0.0' }}
                        </span>
                        <span class="text-xs text-ink-muted">/ 100</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-line">
                    <AppButton
                        variant="ghost"
                        size="sm"
                        class="p-0 text-left text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline justify-start"
                        @click="emit('select-tab', 'akademik')"
                    >
                        Lihat Nilai & Raport &rarr;
                    </AppButton>
                </div>
            </div>
        </div>
    </div>
</template>
