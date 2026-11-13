<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import GradeTable from '@/Components/domain/GradeTable.vue';
import { useToast } from '@/composables/useToast';
import { useAutosave } from '@/composables/useAutosave';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedSubject: { type: String, default: '' },
    students: { type: Array, default: () => [] },
});

const toast = useToast();

const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const subjectList = computed(() => props.subjects?.length ? props.subjects : ['Pendidikan Agama Islam dan Budi Pekerti']);

const selectedClass = ref(props.selectedClass || classList.value[0]);
const selectedSubject = ref(props.selectedSubject || subjectList.value[0]);
const selectedType = ref('tugas');
const kkm = 75;

const onFilterChange = () => {
    router.get(route('guru.nilai'), {
        class: selectedClass.value,
        subject: selectedSubject.value,
    }, { preserveState: true, replace: true });
};

const classOptions = computed(() => classList.value.map(c => ({ value: c, label: `Kelas ${c}` })));
const subjectOptions = computed(() => subjectList.value.map(s => ({ value: s, label: s })));

const typeTabs = [
    { id: 'tugas', label: 'Tugas Harian (1-3)' },
    { id: 'kuis', label: 'Kuis (1-2)' },
    { id: 'uts', label: 'Penilaian Tengah Semester (UTS)' },
    { id: 'uas', label: 'Penilaian Akhir Semester (UAS)' },
];

const students = ref(props.students.map(s => ({ ...s })));
watch(() => props.students, (newVal) => {
    students.value = newVal.map(s => ({ ...s }));
}, { deep: true });

// Auto-save integration
const { status: autosaveStatus, save: triggerAutosave } = useAutosave(async () => {
    await window.axios.post(route('guru.nilai.store'), {
        class_name: selectedClass.value,
        subject_name: selectedSubject.value,
        type: selectedType.value,
        students: students.value,
    });
}, { debounceMs: 1000 });

const handleCellChange = ({ studentId, type, idx, value }) => {
    const student = students.value.find(s => s.id === studentId);
    if (!student) return;

    if (type === 'uts' || type === 'uas') {
        student[type] = value;
    } else {
        if (!student[type]) student[type] = [];
        student[type][idx] = value;
    }

    // recalculate local average
    const allVals = [
        ...(student.tugas || []),
        ...(student.kuis || []),
        student.uts,
        student.uas,
    ].filter(v => v !== null && v !== undefined && v !== '');

    student.avg = allVals.length > 0 ? (allVals.reduce((a, b) => a + Number(b), 0) / allVals.length).toFixed(1) : null;

    triggerAutosave();
};

const saveAll = () => {
    router.post(route('guru.nilai.store'), {
        class_name: selectedClass.value,
        subject_name: selectedSubject.value,
        type: selectedType.value,
        students: students.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Nilai siswa berhasil disimpan.');
        },
    });
};
</script>

<template>
    <Head title="Input Nilai Siswa" />

    <PortalShell variant="sidebar" :autosave-status="autosaveStatus">
        <template #header>
            <PageHeader
                title="Input Nilai Siswa"
                description="Pengelolaan Nilai Tugas, Kuis, UTS, dan UAS Siswa SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Filter Bar & KKM Indicator -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col md:flex-row items-end justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full md:w-auto flex-1">
                    <div>
                        <label for="select-class" class="block text-xs font-bold text-ink-muted mb-1">Rombongan Belajar</label>
                        <AppSelect
                            id="select-class"
                            v-model="selectedClass"
                            :options="classOptions"
                            @change="onFilterChange"
                        />
                    </div>
                    <div>
                        <label for="select-sub" class="block text-xs font-bold text-ink-muted mb-1">Mata Pelajaran</label>
                        <AppSelect
                            id="select-sub"
                            v-model="selectedSubject"
                            :options="subjectOptions"
                            @change="onFilterChange"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                    <span class="px-2.5 py-1.5 rounded-control bg-brand-50 border border-brand-200 text-xs font-bold text-brand-800">
                        KKM Standar: {{ kkm }}
                    </span>
                    <a :href="route('guru.nilai.export', { class: selectedClass, subject: selectedSubject })" class="inline-block">
                        <AppButton size="sm" variant="secondary">
                            Ekspor Excel
                        </AppButton>
                    </a>
                    <AppButton size="sm" variant="primary" @click="saveAll">
                        Simpan Nilai
                    </AppButton>
                </div>
            </div>

            <!-- Type Tabs -->
            <Tabs v-model="selectedType" :tabs="typeTabs" variant="pills" />

            <!-- Grade Table Section -->
            <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
                <div class="p-4 border-b border-line bg-surface-raised flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Lembar Penilaian: Kelas {{ selectedClass }}</h3>
                        <p class="text-xs text-ink-muted">
                            Navigasi cepat keyboard dengan Tab / Enter. Nilai di bawah KKM ({{ kkm }}) disorot otomatis.
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold text-ink">
                        {{ students.length }} Siswa
                    </span>
                </div>

                <div v-if="students.length === 0" class="p-6">
                    <EmptyState
                        title="Belum Ada Siswa"
                        description="Tidak ada data siswa pada rombongan belajar ini."
                    />
                </div>
                <div v-else>
                    <GradeTable
                        :students="students"
                        :selected-type="selectedType"
                        :kkm="kkm"
                        @cell-change="handleCellChange"
                    />
                </div>
            </div>
        </div>
    </PortalShell>
</template>
