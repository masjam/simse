<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

defineProps({
    backups: {
        type: Array,
        default: () => [],
    },
});

const { confirm } = useConfirm();
const toast = useToast();
const isCreating = ref(false);

const handleCreateBackup = async () => {
    const ok = await confirm({
        title: 'Buat Cadangan Sistem Sekarang?',
        message: 'Proses pencadangan mencakup seluruh basis data MySQL dan penyimpanan berkas privat. Proses ini mungkin memerlukan waktu beberapa saat.',
        confirmText: 'Mulai Pencadangan',
        variant: 'primary',
    });

    if (ok) {
        isCreating.value = true;
        router.post(route('admin.backups.create'), {}, {
            preserveScroll: true,
            onFinish: () => {
                isCreating.value = false;
            },
            onSuccess: () => {
                toast.success('Cadangan baru berhasil dibuat.');
            },
            onError: () => {
                toast.error('Gagal membuat cadangan sistem.');
            },
        });
    }
};

const handleDeleteBackup = async (filename) => {
    const ok = await confirm({
        title: 'Hapus File Cadangan?',
        message: `Apakah Anda yakin ingin menghapus arsip cadangan "${filename}"?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger',
    });

    if (ok) {
        router.delete(route('admin.backups.destroy', { filename }), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('File cadangan berhasil dihapus.');
            },
        });
    }
};

const columns = [
    { key: 'filename', label: 'Nama Berkas Arsip (.zip)' },
    { key: 'size', label: 'Ukuran Berkas' },
    { key: 'created_at', label: 'Waktu Pencadangan' },
    { key: 'actions', label: 'Aksi Unduh / Kelola' },
];
</script>

<template>
    <Head title="Cadangan Sistem - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Cadangan & Pemulihan Sistem (Backup & Restore)"
                subtitle="Manajemen arsip cadangan basis data harian dan berkas privat sekolah"
            >
                <template #actions>
                    <AppButton
                        variant="primary"
                        size="sm"
                        :loading="isCreating"
                        @click="handleCreateBackup"
                    >
                        ⚡ Buat Cadangan Sekarang
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Restore Guide Banner -->
            <AppCard class="border-status-info-line bg-status-info-bg/30">
                <div class="flex items-start gap-3">
                    <span class="text-xl">ℹ️</span>
                    <div class="space-y-1 text-xs">
                        <h4 class="font-bold text-ink">Prosedur Pemulihan Basis Data (Disaster Recovery)</h4>
                        <p class="text-ink-muted leading-relaxed">
                            Arsip cadangan disimpan otomatis setiap hari pada pukul 01:00 WIB dengan retensi 7 hari.
                            Untuk memulihkan basis data pada Laragon / Windows server, ikuti panduan lengkap di dokumen <code>docs/RESTORE.md</code>.
                        </p>
                    </div>
                </div>
            </AppCard>

            <!-- Table of Backups -->
            <DataTable
                :columns="columns"
                :items="backups"
                empty-title="Belum ada arsip cadangan"
                empty-message="Silakan klik tombol 'Buat Cadangan Sekarang' untuk membuat arsip pertama."
            >
                <template #cell(filename)="{ item }">
                    <div class="flex items-center gap-2">
                        <span>📦</span>
                        <span class="font-mono text-xs font-semibold text-ink">{{ item.filename }}</span>
                    </div>
                </template>

                <template #cell(size)="{ item }">
                    <span class="font-mono text-xs text-ink-muted">{{ item.size }}</span>
                </template>

                <template #cell(created_at)="{ item }">
                    <span class="text-xs text-ink">{{ item.created_at }}</span>
                </template>

                <template #cell(actions)="{ item }">
                    <div class="flex items-center gap-2">
                        <a :href="route('admin.backups.download', { filename: item.filename })">
                            <AppButton variant="secondary" size="sm">
                                📥 Unduh ZIP
                            </AppButton>
                        </a>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            @click="handleDeleteBackup(item.filename)"
                        >
                            🗑️
                        </AppButton>
                    </div>
                </template>
            </DataTable>
        </div>
    </PortalShell>
</template>

