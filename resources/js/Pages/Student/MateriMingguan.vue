<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    student: { type: Object, required: true },
    subjects: { type: Array, default: () => [] },
    weeks: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ subject_id: '', week: '' }) },
    weeklyGroups: { type: Array, default: () => [] },
});

const subjectFilter = ref(props.filters.subject_id || '');
const weekFilter = ref(props.filters.week || '');

const weekOptions = computed(() => [
    { value: '', label: 'Semua Minggu' },
    ...props.weeks.map((w) => ({ value: w.key, label: w.label })),
]);

const subjectOptions = computed(() => [
    { value: '', label: 'Semua Mata Pelajaran' },
    ...props.subjects.map((s) => ({ value: s.id, label: s.name })),
]);

const onWeekChange = (val) => {
    weekFilter.value = val;
    applyFilters();
};

const onSubjectChange = (val) => {
    subjectFilter.value = val;
    applyFilters();
};

const applyFilters = () => {
    router.get(
        route('siswa.materi-mingguan'),
        {
            subject_id: subjectFilter.value,
            week: weekFilter.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};
</script>

<template>
    <Head title="Materi Mingguan Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Materi Mingguan"
                :description="`Ringkasan Jurnal Guru • Kelas ${student.class_name}`"
            />
        </template>

        <div class="space-y-4">
            <!-- Filter Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="filter-week" class="block text-xs font-bold text-ink-muted mb-1">
                        Filter Periode Minggu
                    </label>
                    <AppSelect
                        id="filter-week"
                        :model-value="weekFilter"
                        :options="weekOptions"
                        @update:model-value="onWeekChange"
                    />
                </div>

                <div>
                    <label for="filter-subject" class="block text-xs font-bold text-ink-muted mb-1">
                        Filter Mata Pelajaran
                    </label>
                    <AppSelect
                        id="filter-subject"
                        :model-value="subjectFilter"
                        :options="subjectOptions"
                        @update:model-value="onSubjectChange"
                    />
                </div>
            </div>

            <!-- Content / Empty State -->
            <div v-if="weeklyGroups.length === 0">
                <EmptyState
                    title="Belum Ada Ringkasan Materi"
                    :description="`Belum ada materi jurnal guru yang dipublikasikan untuk kelas ${student.class_name}.`"
                />
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="week in weeklyGroups"
                    :key="week.label"
                    class="bg-surface rounded-card border border-line shadow-card overflow-hidden"
                >
                    <div class="px-4 py-3 bg-surface-raised border-b border-line flex items-center justify-between">
                        <h3 class="text-sm font-bold text-ink">{{ week.label }}</h3>
                        <span class="text-xs text-ink-muted font-semibold">{{ week.items?.length || 0 }} Materi</span>
                    </div>

                    <div class="divide-y divide-line">
                        <div
                            v-for="item in week.items"
                            :key="item.id"
                            class="p-4 space-y-1.5 hover:bg-surface-raised/40 transition-colors"
                        >
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                    {{ item.subject }}
                                </span>
                                <span class="text-xs text-ink-muted">{{ item.date }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-ink">{{ item.topic }}</h4>
                            <p class="text-xs text-ink-muted leading-relaxed whitespace-pre-line">{{ item.summary }}</p>
                            <p class="text-[11px] text-ink-muted pt-1">
                                Guru Pengajar: {{ item.teacher }} &bull; Pertemuan ke-{{ item.meeting_number }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
