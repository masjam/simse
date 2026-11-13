<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import JournalModal from './Partials/JournalModal.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    journals: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    schedules: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ class_id: '', subject_id: '', week: '' }) },
});

const confirm = useConfirm();
const toast = useToast();

const filterClass = ref(props.filters.class_id || '');
const filterSubject = ref(props.filters.subject_id || '');
const showModal = ref(false);
const editingJournal = ref(null);

const classOptions = computed(() => [
    { value: '', label: 'Semua Kelas' },
    ...props.classes.map(c => ({ value: c.id, label: c.name })),
]);

const subjectOptions = computed(() => [
    { value: '', label: 'Semua Mata Pelajaran' },
    ...props.subjects.map(s => ({ value: s.id, label: s.name })),
]);

const applyFilters = () => {
    router.get(
        route('guru.jurnal'),
        { class_id: filterClass.value, subject_id: filterSubject.value },
        { preserveState: true, preserveScroll: true }
    );
};

const openCreate = () => {
    editingJournal.value = null;
    showModal.value = true;
};

const openEdit = (journal) => {
    editingJournal.value = journal;
    showModal.value = true;
};

const deleteJournal = async (journal) => {
    const agreed = await confirm.ask({
        title: 'Hapus Jurnal Mengajar',
        message: `Yakin ingin menghapus catatan jurnal pertemuan ke-${journal.meeting_number} (${journal.topic})?`,
        confirmLabel: 'Ya, Hapus',
        variant: 'danger',
    });
    if (!agreed) return;

    router.delete(route('guru.jurnal.destroy', journal.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Jurnal mengajar berhasil dihapus.');
        },
    });
};
</script>

<template>
    <Head title="Jurnal Mengajar Guru" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Jurnal Mengajar Guru"
                description="Catatan Pelaksanaan Pembelajaran & Materi Mingguan Kelas SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Filter & Action Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col sm:flex-row items-end justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto flex-1">
                    <div>
                        <label for="filter-cls" class="block text-xs font-bold text-ink-muted mb-1">Filter Kelas</label>
                        <AppSelect
                            id="filter-cls"
                            v-model="filterClass"
                            :options="classOptions"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label for="filter-sub" class="block text-xs font-bold text-ink-muted mb-1">Filter Mata Pelajaran</label>
                        <AppSelect
                            id="filter-sub"
                            v-model="filterSubject"
                            :options="subjectOptions"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <div class="shrink-0 self-start sm:self-auto">
                    <AppButton size="sm" variant="primary" @click="openCreate">
                        + Tambah Jurnal Baru
                    </AppButton>
                </div>
            </div>

            <!-- Journals Timeline List -->
            <div v-if="journals.length === 0">
                <EmptyState
                    title="Belum Ada Jurnal"
                    description="Belum ada catatan jurnal mengajar yang dicatat. Silakan tambah jurnal untuk membagikan ringkasan materi kepada siswa dan orang tua."
                    action-label="+ Tambah Jurnal Baru"
                    @action="openCreate"
                />
            </div>
            <div v-else class="space-y-3">
                <div
                    v-for="j in journals"
                    :key="j.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/30 transition-all space-y-3"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-2.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                {{ j.subject_name }}
                            </span>
                            <span class="text-xs text-ink-muted">
                                Kelas {{ j.class_name }} &bull; Pertemuan ke-{{ j.meeting_number }}
                            </span>
                        </div>
                        <span class="text-xs font-mono text-ink-muted font-semibold">{{ j.date }}</span>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-ink">{{ j.topic }}</h4>
                        <p class="text-xs text-ink-muted leading-relaxed mt-1 whitespace-pre-line">{{ j.summary }}</p>
                    </div>

                    <div v-if="j.activities" class="p-2.5 rounded-control bg-surface-raised border border-line text-xs text-ink-muted">
                        <span class="font-bold text-ink block mb-0.5">Aktivitas Kelas:</span>
                        {{ j.activities }}
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-line text-xs">
                        <span v-if="j.notes" class="text-[11px] text-ink-muted italic">
                            Catatan: {{ j.notes }}
                        </span>
                        <span v-else></span>

                        <div class="flex items-center gap-2">
                            <AppButton size="sm" variant="secondary" @click="openEdit(j)">
                                Edit
                            </AppButton>
                            <AppButton size="sm" variant="danger" @click="deleteJournal(j)">
                                Hapus
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Modal -->
        <JournalModal
            :show="showModal"
            :journal="editingJournal"
            :classes="classes"
            :subjects="subjects"
            :schedules="schedules"
            @close="showModal = false"
        />
    </PortalShell>
</template>
