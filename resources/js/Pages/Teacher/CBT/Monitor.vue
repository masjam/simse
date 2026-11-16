<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import Modal from '@/Components/ui/Modal.vue';
import { usePolling } from '@/composables/usePolling';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    exam: { type: Object, required: true },
    monitorData: { type: Object, required: true },
});

const toast = useToast();
const { confirm } = useConfirm();

const liveData = ref(props.monitorData);
const searchQuery = ref('');
const statusFilter = ref('Semua');

const tableColumns = [
    { key: 'student', label: 'Siswa' },
    { key: 'status', label: 'Status' },
    { key: 'progress', label: 'Progres Soal' },
    { key: 'violations', label: 'Pelanggaran' },
    { key: 'remaining_time', label: 'Waktu Sisa' },
    { key: 'score', label: 'Nilai' },
];

// Polling 5 seconds
usePolling(async () => {
    try {
        const res = await window.axios.get(route('guru.cbt.monitor.poll', props.exam.id));
        liveData.value = res.data;
    } catch {
        // network retry
    }
}, 5000, { autoStart: true });

const filteredStudents = computed(() => {
    const list = liveData.value?.students || [];
    const q = searchQuery.value.toLowerCase().trim();
    const st = statusFilter.value;

    return list.filter((s) => {
        const matchesQuery = !q || s.name.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q);
        const matchesStatus = st === 'Semua' || s.status.includes(st);
        return matchesQuery && matchesStatus;
    });
});

// Extra Time Modal
const showExtraTimeModal = ref(false);
const targetAttempt = ref(null);
const extraMinutes = ref(10);
const actionLoading = ref(false);

const openExtraTime = (student) => {
    targetAttempt.value = student;
    showExtraTimeModal.value = true;
};

const submitExtraTime = async () => {
    if (!targetAttempt.value?.attempt_id) return;
    actionLoading.value = true;
    try {
        await window.axios.post(route('guru.cbt.monitor.extra-time', targetAttempt.value.attempt_id), {
            minutes: extraMinutes.value,
        });
        toast.success(`Berhasil menambah ${extraMinutes.value} menit waktu untuk ${targetAttempt.value.name}.`);
        showExtraTimeModal.value = false;
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal menambah waktu.');
    } finally {
        actionLoading.value = false;
    }
};

const handleResetViolations = async (student) => {
    if (!student.attempt_id) return;
    const ok = await confirm({
        title: 'Reset Pelanggaran Siswa',
        message: `Apakah Anda yakin ingin me-reset pelanggaran untuk ${student.name}? Siswa akan dapat melanjutkan ujian.`,
        confirmText: 'Ya, Izinkan Lanjut',
        type: 'warning',
    });
    if (!ok) return;

    try {
        await window.axios.post(route('guru.cbt.monitor.reset-violations', student.attempt_id));
        toast.success(`Pelanggaran ${student.name} berhasil di-reset.`);
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal mereset pelanggaran.');
    }
};

const handleForceSubmit = async (student) => {
    if (!student.attempt_id) return;
    const ok = await confirm({
        title: 'Paksa Kumpulkan Ujian',
        message: `Ujian ${student.name} akan langsung dihentikan dan dinilai secara otomatis. Lanjutkan?`,
        confirmText: 'Paksa Kumpulkan',
        type: 'danger',
    });
    if (!ok) return;

    try {
        await window.axios.post(route('guru.cbt.monitor.force-submit', student.attempt_id));
        toast.success(`Ujian ${student.name} berhasil dipaksa kumpul dan dinilai.`);
    } catch (e) {
        toast.error(e.response?.data?.message || 'Gagal memaksa kumpul.');
    }
};
</script>

<template>
    <Head :title="`Pengawasan Realtime: ${exam.title}`" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                :title="`Pengawasan CBT: ${exam.title}`"
                :description="`${exam.subject} &bull; Durasi ${exam.duration_minutes} Mnt &bull; Max Pelanggaran: ${exam.max_violations}x`"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Link :href="route('guru.cbt.report', exam.id)">
                            <AppButton size="sm" variant="secondary">
                                Laporan Hasil
                            </AppButton>
                        </Link>
                        <Link :href="route('guru.cbt')">
                            <AppButton size="sm" variant="ghost">
                                &larr; Kembali
                            </AppButton>
                        </Link>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-4">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block">Total Peserta</span>
                    <span class="text-xl font-bold font-numeric text-ink">{{ liveData.counts?.total || 0 }}</span>
                </div>
                <div class="p-3.5 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block">Sedang Mengerjakan</span>
                    <span class="text-xl font-bold font-numeric text-brand-600">{{ liveData.counts?.in_progress || 0 }}</span>
                </div>
                <div class="p-3.5 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block">Selesai Dikumpulkan</span>
                    <span class="text-xl font-bold font-numeric text-status-success-fg">{{ liveData.counts?.finished || 0 }}</span>
                </div>
                <div class="p-3.5 rounded-card bg-surface border border-line shadow-card text-center">
                    <span class="text-xs text-ink-muted block">Total Pelanggaran</span>
                    <span class="text-xl font-bold font-numeric text-status-danger-fg">{{ liveData.counts?.violations_total || 0 }}</span>
                </div>
            </div>

            <!-- Filter Bar & Search -->
            <div class="bg-surface rounded-card border border-line p-3 shadow-card flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-ink-muted">Filter:</span>
                    <AppButton
                        v-for="st in ['Semua', 'Mengerjakan', 'Selesai', 'Belum Mulai']"
                        :key="st"
                        size="sm"
                        :variant="statusFilter === st ? 'primary' : 'secondary'"
                        @click="statusFilter = st"
                    >
                        {{ st }}
                    </AppButton>
                </div>

                <div class="w-full sm:w-64">
                    <AppInput
                        v-model="searchQuery"
                        placeholder="Cari siswa atau NIS..."
                    />
                </div>
            </div>

            <!-- Students Live Table -->
            <DataTable :columns="tableColumns" :items="filteredStudents">
                <template #cell(student)="{ item }">
                    <span class="font-bold block text-ink">{{ item.name }}</span>
                    <span class="text-[11px] text-ink-muted">{{ item.nis }} &bull; {{ item.class }}</span>
                </template>
                <template #cell(status)="{ item }">
                    <StatusBadge :status="item.status.includes('Selesai') ? 'Selesai' : (item.status === 'Mengerjakan' ? 'Aktif' : 'Terjadwal')" />
                </template>
                <template #cell(progress)="{ item }">
                    <div class="w-32 space-y-1">
                        <div class="flex items-center justify-between text-[10px] font-numeric">
                            <span>{{ item.answered_count }}/{{ item.total_questions }}</span>
                            <span>{{ item.progress_pct }}%</span>
                        </div>
                        <ProgressBar :value="item.progress_pct" :max="100" height="h-1.5" />
                    </div>
                </template>
                <template #cell(violations)="{ item }">
                    <span
                        :class="[
                            'px-2 py-0.5 rounded-full font-numeric font-bold text-xs inline-block',
                            item.violations_count >= item.max_violations
                                ? 'bg-status-danger-bg text-status-danger-fg border border-status-danger-line'
                                : (item.violations_count > 0 ? 'bg-status-warning-bg text-status-warning-fg' : 'bg-status-success-bg text-status-success-fg')
                        ]"
                    >
                        {{ item.violations_count }} / {{ item.max_violations }}x
                    </span>
                </template>
                <template #cell(remaining_time)="{ item }">
                    <span v-if="item.remaining_seconds !== null" class="font-numeric">
                        {{ Math.floor(item.remaining_seconds / 60) }}m {{ item.remaining_seconds % 60 }}s
                    </span>
                    <span v-else class="text-ink-muted">-</span>
                </template>
                <template #cell(score)="{ item }">
                    <span class="font-numeric font-bold">
                        {{ item.score !== null ? item.score : '-' }}
                    </span>
                </template>
                <template #actions="{ item }">
                    <div v-if="item.attempt_id && item.status === 'Mengerjakan'" class="flex items-center justify-end gap-1.5">
                        <AppButton size="sm" variant="ghost" class="!px-2 !py-0.5 text-[11px]" @click="openExtraTime(item)">
                            + Waktu
                        </AppButton>
                        <AppButton
                            v-if="item.violations_count > 0"
                            size="sm"
                            variant="secondary"
                            class="!px-2 !py-0.5 text-[11px]"
                            @click="handleResetViolations(item)"
                        >
                            Reset
                        </AppButton>
                        <AppButton size="sm" variant="danger" class="!px-2 !py-0.5 text-[11px]" @click="handleForceSubmit(item)">
                            Paksa
                        </AppButton>
                    </div>
                    <span v-else class="text-ink-muted text-[11px]">-</span>
                </template>
            </DataTable>
        </div>

        <!-- Extra Time Modal -->
        <Modal :show="showExtraTimeModal" title="Tambah Waktu Ujian Individu" max-width="sm" @close="showExtraTimeModal = false">
            <div class="space-y-3">
                <p class="text-xs text-ink leading-relaxed">
                    Tambahkan perpanjangan waktu untuk siswa <strong>{{ targetAttempt?.name }}</strong>:
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <AppButton
                        v-for="m in [5, 10, 15]"
                        :key="m"
                        size="sm"
                        :variant="extraMinutes === m ? 'primary' : 'secondary'"
                        @click="extraMinutes = m"
                    >
                        +{{ m }} Menit
                    </AppButton>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                    <AppButton size="sm" variant="ghost" @click="showExtraTimeModal = false">
                        Batal
                    </AppButton>
                    <AppButton size="sm" variant="primary" :loading="actionLoading" @click="submitExtraTime">
                        Terapkan
                    </AppButton>
                </div>
            </div>
        </Modal>
    </PortalShell>
</template>
