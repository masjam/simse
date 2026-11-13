<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    registrations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all' }),
    },
    availableClasses: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
});

const toast = useToast();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

const applyFilter = () => {
    router.get(route('admin.ppdb.index'), {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Ctrl+K shortcut
const searchInputRef = ref(null);
const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchInputRef.value?.$el?.querySelector('input')?.focus() || searchInputRef.value?.focus?.();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const selectedReg = ref(null);
const showModal = ref(false);

const form = useForm({
    status: 'Diterima',
    admin_notes: '',
    class_id: props.availableClasses.length > 0 ? props.availableClasses[0].id : '',
});

const statusOptions = [
    { value: 'Diterima', label: 'Diterima (Konversi ke Siswa Aktif)' },
    { value: 'Menunggu Verifikasi', label: 'Menunggu Verifikasi' },
    { value: 'Ditolak', label: 'Ditolak' },
];

const classOptions = props.availableClasses.map((c) => ({
    value: c.id,
    label: `Kelas ${c.name}`,
}));

const openVerifyModal = (reg) => {
    selectedReg.value = reg;
    form.status = reg.status === 'Menunggu Verifikasi' ? 'Diterima' : reg.status;
    form.admin_notes = '';
    form.class_id = props.availableClasses.length > 0 ? props.availableClasses[0].id : '';
    showModal.value = true;
};

const submitVerification = () => {
    if (!selectedReg.value) return;

    form.post(route('admin.ppdb.status', selectedReg.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Keputusan verifikasi PPDB berhasil disimpan.');
            showModal.value = false;
            selectedReg.value = null;
        },
    });
};

const statusVariant = (st) => {
    if (st === 'Diterima') return 'success';
    if (st === 'Ditolak') return 'danger';
    return 'warning';
};

const columns = [
    { key: 'reg_no', label: 'No. Reg' },
    { key: 'student', label: 'Calon Siswa' },
    { key: 'birth', label: 'TTL & Asal TK' },
    { key: 'parent', label: 'Orang Tua' },
    { key: 'docs', label: 'Berkas' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <Head title="Verifikasi PPDB Siswa Baru - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Penerimaan Peserta Didik Baru (PPDB)"
                subtitle="Verifikasi berkas calon siswa dan konversi otomatis menjadi siswa aktif dengan akun portal & dompet"
            />

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <StatCard
                    title="Total Pendaftar"
                    :value="stats.total"
                    subtitle="Seluruh pendaftaran masuk"
                    icon-bg="brand"
                />
                <StatCard
                    title="Menunggu Verifikasi"
                    :value="stats.pending"
                    subtitle="Perlu ditinjau segera"
                    icon-bg="warning"
                />
                <StatCard
                    title="Diterima"
                    :value="stats.accepted"
                    subtitle="Menjadi siswa aktif"
                    icon-bg="success"
                />
                <StatCard
                    title="Ditolak"
                    :value="stats.rejected"
                    subtitle="Tidak memenuhi syarat"
                    icon-bg="danger"
                />
            </div>

            <!-- Filters Bar -->
            <AppCard class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
                    <AppButton
                        size="sm"
                        :variant="status === 'all' ? 'primary' : 'ghost'"
                        @click="status = 'all'; applyFilter()"
                    >
                        Semua ({{ stats.total }})
                    </AppButton>
                    <AppButton
                        size="sm"
                        :variant="status === 'Menunggu Verifikasi' ? 'primary' : 'ghost'"
                        @click="status = 'Menunggu Verifikasi'; applyFilter()"
                    >
                        Pending ({{ stats.pending }})
                    </AppButton>
                    <AppButton
                        size="sm"
                        :variant="status === 'Diterima' ? 'primary' : 'ghost'"
                        @click="status = 'Diterima'; applyFilter()"
                    >
                        Diterima ({{ stats.accepted }})
                    </AppButton>
                    <AppButton
                        size="sm"
                        :variant="status === 'Ditolak' ? 'primary' : 'ghost'"
                        @click="status = 'Ditolak'; applyFilter()"
                    >
                        Ditolak ({{ stats.rejected }})
                    </AppButton>
                </div>

                <div class="w-full sm:w-72">
                    <SearchInput
                        ref="searchInputRef"
                        v-model="search"
                        placeholder="Cari pendaftar... (Ctrl+K)"
                        @search="applyFilter"
                    />
                </div>
            </AppCard>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="registrations.data || []"
                :pagination="registrations"
                empty-title="Tidak ada pendaftar"
                empty-message="Tidak ada data pendaftaran PPDB yang cocok dengan filter saat ini."
            >
                <template #cell(reg_no)="{ item }">
                    <span class="font-numeric font-bold text-brand-700">
                        {{ item.registration_number }}
                    </span>
                </template>

                <template #cell(student)="{ item }">
                    <div>
                        <p class="font-bold text-sm text-ink">{{ item.student_name }}</p>
                        <p class="text-xs text-ink-muted">{{ item.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                </template>

                <template #cell(birth)="{ item }">
                    <div>
                        <p class="text-xs text-ink">{{ item.birth_place_date }}</p>
                        <p class="text-[11px] text-ink-muted">TK: {{ item.previous_school || '-' }}</p>
                    </div>
                </template>

                <template #cell(parent)="{ item }">
                    <div>
                        <p class="text-xs font-semibold text-ink">{{ item.parent_name }}</p>
                        <p class="text-xs text-brand-700 font-numeric">{{ item.parent_phone }}</p>
                    </div>
                </template>

                <template #cell(docs)="{ item }">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a
                            v-if="item.has_kk"
                            :href="route('admin.ppdb.document', { registration: item.id, type: 'kk' })"
                            target="_blank"
                            class="inline-flex"
                        >
                            <span class="px-2 py-0.5 rounded-control text-[10px] font-bold bg-surface-raised border border-line text-brand-700 hover:bg-brand-50">
                                KK
                            </span>
                        </a>
                        <a
                            v-if="item.has_akta"
                            :href="route('admin.ppdb.document', { registration: item.id, type: 'akta' })"
                            target="_blank"
                            class="inline-flex"
                        >
                            <span class="px-2 py-0.5 rounded-control text-[10px] font-bold bg-surface-raised border border-line text-brand-700 hover:bg-brand-50">
                                Akta
                            </span>
                        </a>
                        <a
                            v-if="item.has_ijazah"
                            :href="route('admin.ppdb.document', { registration: item.id, type: 'ijazah' })"
                            target="_blank"
                            class="inline-flex"
                        >
                            <span class="px-2 py-0.5 rounded-control text-[10px] font-bold bg-surface-raised border border-line text-brand-700 hover:bg-brand-50">
                                Ijazah
                            </span>
                        </a>
                        <span v-if="!item.has_kk && !item.has_akta && !item.has_ijazah" class="text-xs text-ink-muted">-</span>
                    </div>
                </template>

                <template #cell(status)="{ item }">
                    <StatusBadge
                        :variant="statusVariant(item.status)"
                        :text="item.status"
                        size="sm"
                    />
                </template>

                <template #actions="{ item }">
                    <AppButton
                        variant="primary"
                        size="sm"
                        @click="openVerifyModal(item)"
                    >
                        Verifikasi
                    </AppButton>
                </template>
            </DataTable>

            <!-- Verification Modal -->
            <Modal
                :show="showModal"
                title="Verifikasi Pendaftaran PPDB"
                @close="showModal = false"
            >
                <div v-if="selectedReg" class="space-y-4">
                    <div class="p-3.5 rounded-control bg-surface-raised border border-line text-xs space-y-1">
                        <p><span class="text-ink-muted">Calon Siswa:</span> <strong class="text-ink">{{ selectedReg.student_name }}</strong> ({{ selectedReg.registration_number }})</p>
                        <p><span class="text-ink-muted">Wali Santri:</span> {{ selectedReg.parent_name }} ({{ selectedReg.parent_phone }})</p>
                        <p><span class="text-ink-muted">TTL:</span> {{ selectedReg.birth_place_date }}</p>
                    </div>

                    <form @submit.prevent="submitVerification" class="space-y-4">
                        <FormField label="Keputusan Status" :error="form.errors.status" required>
                            <AppSelect
                                v-model="form.status"
                                :options="statusOptions"
                                required
                            />
                        </FormField>

                        <div v-if="form.status === 'Diterima'">
                            <FormField label="Pilih Rombongan Belajar (Kelas 1)" help="Otomatis membuat akun Siswa, Orang Tua, dompet cashless, dan notifikasi WA" :error="form.errors.class_id" required>
                                <AppSelect
                                    v-model="form.class_id"
                                    :options="classOptions"
                                    placeholder="Pilih Kelas 1..."
                                    required
                                />
                            </FormField>
                        </div>

                        <FormField label="Catatan Admin / Verifikator (Opsional)" :error="form.errors.admin_notes">
                            <AppTextarea
                                v-model="form.admin_notes"
                                :rows="2"
                                placeholder="Catatan untuk arsip atau dikirim ke orang tua..."
                            />
                        </FormField>

                        <div class="pt-4 border-t border-line flex justify-end gap-2">
                            <AppButton variant="ghost" @click="showModal = false">
                                Batal
                            </AppButton>
                            <AppButton type="submit" variant="primary" :loading="form.processing">
                                Simpan Keputusan
                            </AppButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </PortalShell>
</template>
