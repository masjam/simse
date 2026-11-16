<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import KasbonRequestModal from '@/Pages/Teacher/Partials/KasbonRequestModal.vue';

const props = defineProps({
    advances: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            total_borrowed: 0,
            total_remaining: 0,
            can_request: true,
        }),
    },
});

const showRequestModal = ref(false);
const expandedAdvanceId = ref(null);

const toggleExpand = (id) => {
    expandedAdvanceId.value = expandedAdvanceId.value === id ? null : id;
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
    <Head title="Kasbon & Pinjaman Staf" />

    <PortalShell variant="sidebar">
        <div class="space-y-5 max-w-7xl">
            <PageHeader
                title="Kasbon & Pinjaman Staf"
                subtitle="Fasilitas pinjaman darurat dan angsuran terpotong otomatis dari slip gaji bulanan tenaga kependidikan"
            >
                <template #actions>
                    <AppButton
                        v-if="summary.can_request"
                        variant="primary"
                        @click="showRequestModal = true"
                    >
                        + Ajukan Kasbon Baru
                    </AppButton>
                    <span
                        v-else
                        class="text-xs text-ink-muted bg-surface-raised border border-line px-3 py-1.5 rounded-control"
                    >
                        Tanggungan Kasbon Masih Aktif
                    </span>
                </template>
            </PageHeader>

            <!-- Ringkasan Kasbon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <AppCard class="p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-ink-muted">Total Kasbon Disetujui</span>
                        <div class="text-lg font-bold text-ink mt-0.5">
                            <MoneyText :amount="summary.total_borrowed" />
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold">
                        💳
                    </div>
                </AppCard>

                <AppCard class="p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-ink-muted">Sisa Tanggungan Angsuran</span>
                        <div class="text-lg font-bold text-status-warning-fg mt-0.5">
                            <MoneyText :amount="summary.total_remaining" />
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-status-warning-bg text-status-warning-fg flex items-center justify-center font-bold">
                        ⏳
                    </div>
                </AppCard>
            </div>

            <!-- Riwayat Pengajuan Kasbon -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-ink">Riwayat Pengajuan & Jadwal Angsuran</h3>

                <div v-if="advances.length === 0">
                    <EmptyState
                        title="Belum Ada Pengajuan Kasbon"
                        description="Anda belum pernah mengajukan pinjaman kasbon staf."
                        action-label="Ajukan Kasbon Sekarang"
                        @action="showRequestModal = true"
                    />
                </div>
                <div v-else class="space-y-3">
                    <AppCard
                        v-for="advance in advances"
                        :key="advance.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-line">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-bold text-ink">
                                        <MoneyText :amount="advance.amount" />
                                    </span>
                                    <StatusBadge :variant="statusVariant(advance.status)">
                                        {{ advance.status }}
                                    </StatusBadge>
                                </div>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    Diajukan pada {{ advance.created_at }} &bull; Tenor: {{ advance.installment_count }} Bulan
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-ink-muted">
                                    Angsuran: <strong><MoneyText :amount="advance.monthly_amount" /></strong> / bln
                                </span>
                                <AppButton
                                    v-if="advance.installments && advance.installments.length > 0"
                                    size="sm"
                                    variant="secondary"
                                    @click="toggleExpand(advance.id)"
                                >
                                    {{ expandedAdvanceId === advance.id ? 'Tutup Rincian' : 'Lihat Angsuran' }}
                                </AppButton>
                            </div>
                        </div>

                        <div class="text-xs text-ink leading-relaxed">
                            <span class="text-ink-muted font-medium">Keperluan: </span>
                            {{ advance.reason }}
                        </div>

                        <!-- Catatan Persetujuan / Penolakan -->
                        <div v-if="advance.approved_by" class="p-2.5 rounded-control bg-surface-raised border border-line text-xs text-ink-muted">
                            Disetujui oleh <strong>{{ advance.approved_by }}</strong> pada {{ advance.approved_at }}.
                        </div>
                        <div v-if="advance.rejection_reason" class="p-2.5 rounded-control bg-status-danger-bg text-status-danger-fg text-xs">
                            <strong>Alasan Penolakan:</strong> {{ advance.rejection_reason }}
                        </div>

                        <!-- Rincian Tabel Angsuran Bulanan -->
                        <div
                            v-if="expandedAdvanceId === advance.id && advance.installments && advance.installments.length > 0"
                            class="pt-3 border-t border-line"
                        >
                            <h4 class="text-xs font-bold uppercase tracking-wider text-ink-muted mb-2">
                                Jadwal Pemotongan Gaji Bulanan:
                            </h4>
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
                                                Dipotong pada slip gaji ({{ ins.paid_at }})
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

        <!-- Modal Pengajuan Kasbon -->
        <KasbonRequestModal
            :show="showRequestModal"
            :post-route="route('staf.kasbon.store')"
            @close="showRequestModal = false"
        />
    </PortalShell>
</template>
