<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    achievements: {
        type: Array,
        default: () => [],
    },
    statusFilter: {
        type: String,
        default: 'Menunggu Verifikasi',
    },
    counts: {
        type: Object,
        default: () => ({ pending: 0, approved: 0, rejected: 0 }),
    },
});

const page = usePage();
const toast = useToast();

const selectedItem = ref(null);
const decisionStatus = ref('Disetujui');

const verifyForm = useForm({
    status: 'Disetujui',
    verification_notes: '',
});

const openVerifyModal = (item, status) => {
    selectedItem.value = item;
    decisionStatus.value = status;
    verifyForm.status = status;
    verifyForm.verification_notes = item.verification_notes || '';
    verifyForm.clearErrors();
};

const closeVerifyModal = () => {
    selectedItem.value = null;
    verifyForm.reset();
};

const submitVerify = () => {
    if (!selectedItem.value) return;

    verifyForm.post(route('achievements.verification.verify', selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Prestasi berhasil ${decisionStatus.value.toLowerCase()}.`);
            closeVerifyModal();
        },
    });
};

const filterByStatus = (status) => {
    router.get(
        route('achievements.verification.index'),
        { status },
        { preserveState: true, replace: true }
    );
};

const filterTabs = [
    { id: 'Menunggu Verifikasi', label: 'Menunggu Verifikasi' },
    { id: 'Disetujui', label: 'Disetujui' },
    { id: 'Ditolak', label: 'Ditolak' },
    { id: 'Semua', label: 'Semua' },
];

const statusVariant = (status) => {
    if (status === 'Disetujui') return 'success';
    if (status === 'Ditolak') return 'danger';
    return 'warning';
};
</script>

<template>
    <Head title="Verifikasi Prestasi Siswa" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Verifikasi Prestasi & Lomba Siswa"
                subtitle="Validasi pengajuan prestasi siswa oleh Guru BK, Tata Usaha, atau Pembina Lomba untuk e-Raport"
            />

            <!-- Summary KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <StatCard
                    title="Menunggu Verifikasi"
                    :value="counts.pending"
                    subtitle="Perlu ditinjau segera"
                    icon-bg="warning"
                />
                <StatCard
                    title="Disetujui"
                    :value="counts.approved"
                    subtitle="Tampil di e-Raport"
                    icon-bg="success"
                />
                <StatCard
                    title="Ditolak"
                    :value="counts.rejected"
                    subtitle="Tidak memenuhi syarat"
                    icon-bg="danger"
                />
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 bg-surface p-1.5 rounded-control border border-line w-fit">
                <AppButton
                    v-for="tab in filterTabs"
                    :key="tab.id"
                    size="sm"
                    :variant="statusFilter === tab.id ? 'primary' : 'ghost'"
                    @click="filterByStatus(tab.id)"
                >
                    {{ tab.label }}
                </AppButton>
            </div>

            <!-- List -->
            <AppCard padding="none">
                <div v-if="achievements.length === 0" class="p-8">
                    <EmptyState
                        title="Tidak ada pengajuan prestasi"
                        :description="`Tidak ada data pengajuan prestasi dengan status '${statusFilter}'.`"
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="item in achievements"
                        :key="item.id"
                        class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
                    >
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-control bg-surface-raised border border-line text-ink-muted text-xs font-semibold">
                                    {{ item.student_name }} ({{ item.class_name }})
                                </span>
                                <StatusBadge
                                    :variant="statusVariant(item.status)"
                                    :text="item.status"
                                    size="sm"
                                />
                            </div>
                            <h3 class="font-bold text-ink text-sm sm:text-base">{{ item.name }}</h3>
                            <p class="text-xs text-ink-muted">
                                {{ item.category }} • Tingkat {{ item.level }} • Tahun {{ item.year }}
                                <span v-if="item.organizer && item.organizer !== '-'"> • Penyelenggara: {{ item.organizer }}</span>
                            </p>
                            <p v-if="item.description" class="text-xs text-ink-muted">{{ item.description }}</p>
                            <p class="text-[11px] text-ink-muted">
                                Diajukan oleh {{ item.submitter_name }} pada {{ item.created_at }}
                                <span v-if="item.verifier_name"> • Diverifikasi oleh {{ item.verifier_name }} ({{ item.verified_at }})</span>
                            </p>
                            <div v-if="item.verification_notes" class="text-xs text-ink bg-surface-raised p-2.5 rounded-control border border-line mt-2">
                                <span class="font-bold">Catatan:</span> {{ item.verification_notes }}
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a
                                v-if="item.has_certificate"
                                :href="route('achievements.verification.certificate', item.id)"
                                target="_blank"
                                class="inline-flex"
                            >
                                <AppButton variant="secondary" size="sm">
                                    Cek Sertifikat
                                </AppButton>
                            </a>
                            <AppButton
                                variant="primary"
                                size="sm"
                                @click="openVerifyModal(item, 'Disetujui')"
                            >
                                Setujui
                            </AppButton>
                            <AppButton
                                variant="danger"
                                size="sm"
                                @click="openVerifyModal(item, 'Ditolak')"
                            >
                                Tolak
                            </AppButton>
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Modal Verifikasi -->
            <Modal
                :show="!!selectedItem"
                :title="decisionStatus === 'Disetujui' ? 'Setujui Prestasi Siswa' : 'Tolak Pengajuan Prestasi'"
                @close="closeVerifyModal"
            >
                <div v-if="selectedItem" class="space-y-4">
                    <p class="text-xs text-ink-muted">
                        <strong class="text-ink">{{ selectedItem.student_name }}</strong> — {{ selectedItem.name }} ({{ selectedItem.level }}, {{ selectedItem.year }})
                    </p>

                    <form @submit.prevent="submitVerify" class="space-y-4">
                        <FormField
                            :label="`Catatan Verifikasi ${decisionStatus === 'Ditolak' ? '(Wajib diisi alasan penolakan)' : '(Opsional)'}`"
                            :error="verifyForm.errors.verification_notes"
                        >
                            <AppTextarea
                                v-model="verifyForm.verification_notes"
                                :rows="3"
                                placeholder="Contoh: Sertifikat sesuai dan sah dari penyelenggara resmi."
                            />
                        </FormField>

                        <div class="flex justify-end gap-2 pt-2">
                            <AppButton variant="ghost" @click="closeVerifyModal">
                                Batal
                            </AppButton>
                            <AppButton
                                :variant="decisionStatus === 'Disetujui' ? 'primary' : 'danger'"
                                :loading="verifyForm.processing"
                                type="submit"
                            >
                                Konfirmasi {{ decisionStatus }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </PortalShell>
</template>
