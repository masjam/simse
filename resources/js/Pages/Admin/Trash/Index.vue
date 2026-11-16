<script setup>
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    currentType: {
        type: String,
        default: 'student',
    },
    items: {
        type: Object,
        required: true,
    },
    counts: {
        type: Object,
        default: () => ({}),
    },
});

const { confirm } = useConfirm();
const toast = useToast();

const tabs = [
    { key: 'student', label: 'Peserta Didik' },
    { key: 'lesson', label: 'Materi KBM' },
    { key: 'assignment', label: 'Tugas' },
    { key: 'exam', label: 'Ujian CBT' },
    { key: 'question', label: 'Soal Ujian' },
    { key: 'book', label: 'Buku Pustaka' },
    { key: 'bill', label: 'Tagihan SPP' },
    { key: 'bill_type', label: 'Jenis Biaya' },
    { key: 'post', label: 'Artikel & Berita' },
];

const switchTab = (type) => {
    router.get(route('admin.trash.index'), { type }, { preserveState: true, preserveScroll: true });
};

const handleRestore = async (id, name) => {
    const ok = await confirm({
        title: 'Pulihkan Data?',
        message: `Apakah Anda yakin ingin memulihkan data "${name}" kembali ke sistem aktif?`,
        confirmText: 'Ya, Pulihkan',
        variant: 'primary',
    });

    if (ok) {
        router.post(route('admin.trash.restore', { type: props.currentType, id }), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success('Data berhasil dipulihkan.'),
        });
    }
};

const handleForceDelete = async (id, name) => {
    const ok = await confirm({
        title: 'Hapus Permanen?',
        message: `PERINGATAN: Data "${name}" akan dihapus selamanya dari basis data dan tidak dapat dikembalikan!`,
        confirmText: 'Hapus Selamanya',
        variant: 'danger',
    });

    if (ok) {
        router.delete(route('admin.trash.force', { type: props.currentType, id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Data telah dihapus permanen.'),
        });
    }
};

const columns = [
    { key: 'name', label: 'Nama / Judul Data' },
    { key: 'deleted_at', label: 'Waktu Dihapus' },
    { key: 'actions', label: 'Aksi Pemulihan' },
];
</script>

<template>
    <Head title="Tempat Sampah Data - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Tempat Sampah & Pemulihan Data"
                subtitle="Kelola data yang terhapus sementara (soft deletes), pulihkan atau bersihkan permanen"
            />

            <!-- Entity Tabs -->
            <AppCard class="p-2 overflow-x-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <AppButton
                        v-for="t in tabs"
                        :key="t.key"
                        :variant="currentType === t.key ? 'primary' : 'ghost'"
                        size="sm"
                        @click="switchTab(t.key)"
                    >
                        {{ t.label }}
                        <span
                            class="ml-1.5 px-1.5 py-0.2 rounded-full text-[10px]"
                            :class="currentType === t.key ? 'bg-white/20 text-white' : 'bg-surface-sunken text-ink-muted'"
                        >
                            {{ counts[t.key] || 0 }}
                        </span>
                    </AppButton>
                </div>
            </AppCard>

            <!-- Data Table -->
            <DataTable
                :columns="columns"
                :items="items.data || []"
                :pagination="items"
                empty-title="Tempat sampah kosong"
                empty-message="Tidak ada data terhapus untuk kategori entitas ini."
            >
                <template #cell(name)="{ item }">
                    <div class="font-medium text-ink">{{ item.name }}</div>
                    <div class="text-[10px] font-mono text-ink-muted">ID: {{ item.id }}</div>
                </template>

                <template #cell(deleted_at)="{ item }">
                    <span class="text-xs text-ink-muted">{{ item.deleted_at }}</span>
                </template>

                <template #cell(actions)="{ item }">
                    <div class="flex items-center gap-2">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            @click="handleRestore(item.id, item.name)"
                        >
                            ♻️ Pulihkan
                        </AppButton>
                        <AppButton
                            variant="danger"
                            size="sm"
                            @click="handleForceDelete(item.id, item.name)"
                        >
                            🗑️ Hapus Permanen
                        </AppButton>
                    </div>
                </template>
            </DataTable>
        </div>
    </PortalShell>
</template>

