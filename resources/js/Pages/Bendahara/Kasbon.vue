<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    advances: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            pending_count: 0,
            active_count: 0,
            total_disbursed: 0,
            total_outstanding: 0,
        }),
    },
    selectedStatus: { type: String, default: '' },
});

const statusFilter = ref(props.selectedStatus);
const showRejectModal = ref(false);
const targetAdvance = ref(null);
const expandedAdvanceId = ref(null);

const rejectForm = useForm({
    reason: '',
});

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'Diajukan', label: 'Menunggu Persetujuan' },
    { value: 'Disetujui', label: 'Disetujui & Berjalan' },
    { value: 'Lunas', label: 'Lunas' },
    { value: 'Ditolak', label: 'Ditolak' },
];

const filterStatus = (val) => {
    statusFilter.value = val;
    router.get(
        route('bendahara.kasbon'),
        { status: statusFilter.value },
        { preserveState: true, preserveScroll: true }
    );
};

const toggleExpand = (id) => {
    expandedAdvanceId.value = expandedAdvanceId.value === id ? null : id;
};

const approveAdvance = (advance) => {
    if (confirm(`Setujui pengajuan kasbon sebesar Rp ${advance.amount.toLocaleString('id-ID')} untuk ${advance.employee_name}?`)) {
        router.post(route('bendahara.kasbon.approve', advance.id), {}, {
            preserveScroll: true,
        });
    }
};

const openRejectModal = (advance) => {
    targetAdvance.value = advance;
    rejectForm.reason = '';
    showRejectModal.value = true;
};

const submitReject = () => {
    if (!targetAdvance.value) return;
    rejectForm.post(route('bendahara.kasbon.reject', targetAdvance.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            targetAdvance.value = null;
        },
    });
};

const statusVariant = (status) => {
    return {
        Diajukan: 'warning',
        Disetujui: 'info',
        Lunas: 'success',
        Ditolak: 'danger',
    }[status] || 'neutral';
};
</script>

<template>
    <Head title="Manajemen Kasbon Pegawai" />

    <PortalShell variant="sidebar">
        <div class="space-y-5 max-w-7xl">
            <PageHeader
                title="Manajemen Kasbon Pegawai"
                subtitle="Verifikasi permohonan pinjaman darurat, pemantauan jadwal angsuran, dan pemotongan slip gaji guru serta staf"
            />

            <!-- Ringkasan Statistik Bendahara -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <AppCard class="p-4">
                    <span class="text-xs text-ink-muted">Menunggu Persetujuan</span>
                    <div class="text-xl font-bold text-status-warning-fg mt-1">
                        {{ summary.pending_count }} Pengajuan
                    </div>
                </AppCard>

                <AppCard class="p-4">
                    <span class="text-xs text-ink-muted">Kasbon Aktif Berjalan</span>
                    <div class="text-xl font-bold text-ink mt-1">
                        {{ summary.active_count }} Pegawai
                    </div>
                </AppCard>

                <AppCard class="p-4">
                    <span class="text-xs text-ink-muted">Total Dana Disalurkan</span>
                    <div class="text-xl font-bold text-brand-700 mt-1">
                        <MoneyText :amount="summary.total_disbursed" />
                    </div>
                </AppCard>

                <AppCard class="p-4">
                    <span class="text-xs text-ink-muted">Sisa Piutang / Outstanding</span>
                    <div class="text-xl font-bold text-status-danger-fg mt-1">
                        <MoneyText :amount="summary.total_outstanding" />
                    </div>
                </AppCard>
            </div>

            <!-- Filter Status -->
            <div class="bg-surface rounded-card border border-line p-3.5 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <label for="kasbon-status-filter" class="text-xs font-bold text-ink shrink-0">
                    Filter Status Pengajuan:
                </label>
                <div class="w-full sm:w-64">
                    <AppSelect
                        id="kasbon-status-filter"
                        :model-value="statusFilter"
                        :options="statusOptions"
                        @update:model-value="filterStatus"
                    />
                </div>
            </div>

            <!-- Daftar Kasbon Masuk -->
            <div class="space-y-3">
                <div v-if="advances.length === 0">
                    <EmptyState
                        title="Tidak Ada Data Kasbon"
                        description="Belum ada data pengajuan kasbon pegawai pada status yang dipilih."
                    />
                </div>
                <div v-else class="space-y-3">
                    <AppCard
                        v-for="advance in advances"
                        :key="advance.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-line">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-ink">{{ advance.employee_name }}</h4>
                                    <StatusBadge :variant="statusVariant(advance.status)">
                                        {{ advance.status }}
                                    </StatusBadge>
                                </div>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    Diajukan: {{ advance.created_at }} &bull; Tenor: {{ advance.installment_count }} Bulan &bull; Angsuran: <MoneyText :amount="advance.monthly_amount" /> / bln
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-base font-bold text-brand-700 mr-2">
                                    <MoneyText :amount="advance.amount" />
                                </span>

                                <AppButton
                                    v-if="advance.installments && advance.installments.length > 0"
                                    size="sm"
                                    variant="secondary"
                                    @click="toggleExpand(advance.id)"
                                >
                                    {{ expandedAdvanceId === advance.id ? 'Tutup Rincian' : 'Rincian Angsuran' }}
                                </AppButton>

                                <template v-if="advance.status === 'Diajukan'">
                                    <AppButton
                                        size="sm"
                                        variant="primary"
                                        @click="approveAdvance(advance)"
                                    >
                                        ✓ Setujui
                                    </AppButton>
                                    <AppButton
                                        size="sm"
                                        variant="danger"
                                        @click="openRejectModal(advance)"
                                    >
                                        ✕ Tolak
                                    </AppButton>
                                </template>
                            </div>
                        </div>

                        <div class="text-xs text-ink leading-relaxed">
                            <span class="text-ink-muted font-medium">Alasan Pengajuan: </span>
                            {{ advance.reason }}
                        </div>

                        <!-- Info Persetujuan -->
                        <div v-if="advance.approved_by" class="p-2.5 rounded-control bg-surface-raised border border-line text-xs text-ink-muted">
                            Persetujuan dilakukan oleh <strong>{{ advance.approved_by }}</strong> pada {{ advance.approved_at }}.
                        </div>
                        <div v-if="advance.rejection_reason" class="p-2.5 rounded-control bg-status-danger-bg text-status-danger-fg text-xs">
                            <strong>Alasan Penolakan:</strong> {{ advance.rejection_reason }}
                        </div>

                        <!-- Jadwal Angsuran -->
                        <div
                            v-if="expandedAdvanceId === advance.id && advance.installments && advance.installments.length > 0"
                            class="pt-3 border-t border-line"
                        >
                            <h5 class="text-xs font-bold uppercase tracking-wider text-ink-muted mb-2">
                                Jadwal Angsuran Pemotongan Slip Gaji:
                            </h5>
                            <div class="divide-y divide-line rounded-control border border-line overflow-hidden bg-surface">
                                <div
                                    v-for="ins in advance.installments"
                                    :key="ins.id"
                                    class="p-2.5 flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-surface-sunken flex items-center justify-center font-bold text-[10px]">
                                            #{{ ins.number }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-ink">Bulan {{ ins.period }}</span>
                                            <span v-if="ins.paid_at" class="text-[10px] text-ink-muted block">
                                                Lunas terpotong ({{ ins.paid_at }})
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="font-mono font-bold text-ink">
                                            <MoneyText :amount="ins.amount" />
                                        </span>
                                        <StatusBadge :variant="ins.status === 'Lunas' ? 'success' : 'warning'">
                                            {{ ins.status }}
                                        </StatusBadge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>

        <!-- Modal Tolak Pengajuan -->
        <Modal :show="showRejectModal" max-width="md" @close="showRejectModal = false">
            <div class="p-5 space-y-4">
                <div>
                    <h3 class="text-base font-bold text-ink">Tolak Pengajuan Kasbon</h3>
                    <p class="text-xs text-ink-muted mt-0.5">
                        Berikan alasan penolakan yang jelas untuk pemohon {{ targetAdvance?.employee_name }}.
                    </p>
                </div>

                <form @submit.prevent="submitReject" class="space-y-4">
                    <FormField label="Alasan Penolakan" :error="rejectForm.errors.reason" required>
                        <AppTextarea
                            v-model="rejectForm.reason"
                            placeholder="Contoh: Tenor melebihi batas ketentuan, tanggungan kasbon sebelumnya belum tuntas, dsb."
                            rows="3"
                            required
                        />
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
                        <AppButton type="button" variant="secondary" @click="showRejectModal = false">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="danger" :loading="rejectForm.processing">
                            Tolak Pengajuan
                        </AppButton>
                    </div>
                </form>
            </div>
        </Modal>
    </PortalShell>
</template>
