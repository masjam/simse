<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedSubject: { type: String, default: '' },
    students: { type: Array, default: () => [] },
});

const page = usePage();
const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const subjectList = computed(() => props.subjects?.length ? props.subjects : ['Pendidikan Agama Islam dan Budi Pekerti']);

const selectedClass = ref(props.selectedClass || classList.value[0]);
const selectedSubject = ref(props.selectedSubject || subjectList.value[0]);
const selectedType = ref('tugas');

const onFilterChange = () => {
    router.get(route('guru.nilai'), {
        class: selectedClass.value,
        subject: selectedSubject.value,
    }, { preserveState: true, replace: true });
};

const students = ref(props.students.map(s => ({ ...s })));
watch(() => props.students, (newVal) => {
    students.value = newVal.map(s => ({ ...s }));
}, { deep: true });

const getAverage = (arr) => {
    if (!arr || arr.length === 0) return '-';
    const valid = arr.filter(v => v !== null && v !== undefined);
    if (valid.length === 0) return '-';
    return (valid.reduce((a, b) => a + b, 0) / valid.length).toFixed(1);
};

const getGradeColor = (val) => {
    if (val === null || val === undefined || val === '-') return 'text-slate-400';
    const n = typeof val === 'string' ? parseFloat(val) : val;
    if (n >= 85) return 'text-emerald-600 font-bold';
    if (n >= 75) return 'text-blue-600 font-bold';
    if (n >= 60) return 'text-amber-600 font-bold';
    return 'text-red-600 font-bold';
};

const getGradeBg = (val) => {
    if (val === null || val === undefined || val === '-') return '';
    const n = typeof val === 'string' ? parseFloat(val) : val;
    if (n >= 85) return 'bg-emerald-50';
    if (n >= 75) return 'bg-blue-50';
    if (n >= 60) return 'bg-amber-50';
    return 'bg-red-50';
};

const editingCell = ref(null);
const editValue = ref('');

const startEdit = (studentId, type, idx) => {
    editingCell.value = `${studentId}-${type}-${idx}`;
    const student = students.value.find(s => s.id === studentId);
    if (type === 'uts' || type === 'uas') {
        editValue.value = student[type] ?? '';
    } else {
        editValue.value = student[type]?.[idx] ?? '';
    }
};

const saveEdit = (studentId, type, idx) => {
    const student = students.value.find(s => s.id === studentId);
    const val = editValue.value === '' ? null : parseInt(editValue.value);
    if (type === 'uts' || type === 'uas') {
        student[type] = val;
    } else {
        if (student[type]) student[type][idx] = val;
    }
    editingCell.value = null;
};

const isEditing = (studentId, type, idx) => editingCell.value === `${studentId}-${type}-${idx}`;

const showSaved = ref(false);
const saveAll = () => {
    router.post(route('guru.nilai.store'), {
        class_name: selectedClass.value,
        subject_name: selectedSubject.value,
        type: selectedType.value,
        students: students.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showSaved.value = true;
            setTimeout(() => showSaved.value = false, 3000);
        }
    });
};

const fileInput = ref(null);

const exportExcel = () => {
    window.location.href = route('guru.nilai.export', {
        class_name: selectedClass.value,
        subject_name: selectedSubject.value
    });
};

const importExcel = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    router.post(route('guru.nilai.import'), { 
        file: file,
        subject_name: selectedSubject.value
    }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            alert('Nilai berhasil diimpor!');
        }
    });
};
</script>

<template>
    <Head title="Input Nilai" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Input Nilai</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Kelola nilai tugas, kuis, UTS, dan UAS siswa</p></template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-600 mb-1 block">Kelas</label>
                        <select v-model="selectedClass" @change="onFilterChange" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option v-for="cls in classList" :key="cls">{{ cls }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-600 mb-1 block">Mata Pelajaran</label>
                        <select v-model="selectedSubject" @change="onFilterChange" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option v-for="sub in subjectList" :key="sub">{{ sub }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-600 mb-1 block">Jenis Nilai</label>
                        <div class="flex gap-1">
                            <button v-for="gt in gradeTypes" :key="gt.value" @click="selectedType = gt.value"
                                    class="flex-1 py-2.5 text-xs font-bold rounded-lg transition"
                                    :class="selectedType === gt.value ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
                                {{ gt.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Table -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-slate-800">Tabel Nilai — {{ selectedClass }} — {{ selectedSubject }}</h3>
                    </div>
                    <div class="flex gap-2">
                        <button @click="exportExcel" class="px-3 py-1.5 text-xs font-bold border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Ekspor Excel
                        </button>
                        <button @click="fileInput.click()" class="px-3 py-1.5 text-xs font-bold border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Impor Excel
                        </button>
                        <input type="file" ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv" @change="importExcel" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-4 py-3 font-bold text-slate-600 w-10">#</th>
                                <th class="text-left px-4 py-3 font-bold text-slate-600 min-w-[200px]">Nama Siswa</th>
                                <th v-if="selectedType === 'tugas'" v-for="i in 4" :key="i" class="text-center px-3 py-3 font-bold text-slate-600 w-20">T{{ i }}</th>
                                <th v-if="selectedType === 'kuis'" v-for="i in 2" :key="'k'+i" class="text-center px-3 py-3 font-bold text-slate-600 w-20">K{{ i }}</th>
                                <th v-if="selectedType === 'uts'" class="text-center px-3 py-3 font-bold text-slate-600 w-24">UTS</th>
                                <th v-if="selectedType === 'uas'" class="text-center px-3 py-3 font-bold text-slate-600 w-24">UAS</th>
                                <th class="text-center px-4 py-3 font-bold text-slate-600 w-24">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, idx) in students" :key="student.id"
                                class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3 text-slate-400 font-bold text-xs">{{ idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                             :class="student.nisn.endsWith('3') || student.nisn.endsWith('5') || student.nisn.endsWith('7') || student.nisn.endsWith('9') || student.nisn.endsWith('1')  ? 'bg-pink-100 text-pink-600' : 'bg-blue-100 text-blue-600'">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm">{{ student.name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ student.nisn }}</p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Tugas Cells -->
                                <td v-if="selectedType === 'tugas'" v-for="i in 4" :key="i"
                                    class="text-center px-3 py-3 cursor-pointer" @dblclick="startEdit(student.id, 'tugas', i-1)">
                                    <template v-if="isEditing(student.id, 'tugas', i-1)">
                                        <input v-model="editValue" type="number" min="0" max="100"
                                               @blur="saveEdit(student.id, 'tugas', i-1)" @keyup.enter="saveEdit(student.id, 'tugas', i-1)"
                                               class="w-16 text-center border border-indigo-300 rounded-lg py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" autofocus />
                                    </template>
                                    <template v-else>
                                        <span :class="[getGradeColor(student.tugas[i-1]), getGradeBg(student.tugas[i-1])]"
                                              class="inline-block px-2.5 py-1 rounded-lg text-sm min-w-[40px]">
                                            {{ student.tugas[i-1] ?? '-' }}
                                        </span>
                                    </template>
                                </td>
                                <!-- Kuis Cells -->
                                <td v-if="selectedType === 'kuis'" v-for="i in 2" :key="'k'+i"
                                    class="text-center px-3 py-3 cursor-pointer" @dblclick="startEdit(student.id, 'kuis', i-1)">
                                    <template v-if="isEditing(student.id, 'kuis', i-1)">
                                        <input v-model="editValue" type="number" min="0" max="100"
                                               @blur="saveEdit(student.id, 'kuis', i-1)" @keyup.enter="saveEdit(student.id, 'kuis', i-1)"
                                               class="w-16 text-center border border-indigo-300 rounded-lg py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" autofocus />
                                    </template>
                                    <template v-else>
                                        <span :class="[getGradeColor(student.kuis[i-1]), getGradeBg(student.kuis[i-1])]"
                                              class="inline-block px-2.5 py-1 rounded-lg text-sm min-w-[40px]">
                                            {{ student.kuis[i-1] ?? '-' }}
                                        </span>
                                    </template>
                                </td>
                                <!-- UTS Cell -->
                                <td v-if="selectedType === 'uts'" class="text-center px-3 py-3 cursor-pointer" @dblclick="startEdit(student.id, 'uts', 0)">
                                    <template v-if="isEditing(student.id, 'uts', 0)">
                                        <input v-model="editValue" type="number" min="0" max="100"
                                               @blur="saveEdit(student.id, 'uts', 0)" @keyup.enter="saveEdit(student.id, 'uts', 0)"
                                               class="w-16 text-center border border-indigo-300 rounded-lg py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" autofocus />
                                    </template>
                                    <template v-else>
                                        <span :class="[getGradeColor(student.uts), getGradeBg(student.uts)]"
                                              class="inline-block px-2.5 py-1 rounded-lg text-sm min-w-[40px]">
                                            {{ student.uts ?? '-' }}
                                        </span>
                                    </template>
                                </td>
                                <!-- UAS Cell -->
                                <td v-if="selectedType === 'uas'" class="text-center px-3 py-3 cursor-pointer" @dblclick="startEdit(student.id, 'uas', 0)">
                                    <template v-if="isEditing(student.id, 'uas', 0)">
                                        <input v-model="editValue" type="number" min="0" max="100"
                                               @blur="saveEdit(student.id, 'uas', 0)" @keyup.enter="saveEdit(student.id, 'uas', 0)"
                                               class="w-16 text-center border border-indigo-300 rounded-lg py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" autofocus />
                                    </template>
                                    <template v-else>
                                        <span :class="[getGradeColor(student.uas), getGradeBg(student.uas)]"
                                              class="inline-block px-2.5 py-1 rounded-lg text-sm min-w-[40px]">
                                            {{ student.uas ?? '-' }}
                                        </span>
                                    </template>
                                </td>
                                <!-- Average -->
                                <td class="text-center px-4 py-3">
                                    <span :class="getGradeColor(getAverage(selectedType === 'uts' ? [student.uts] : selectedType === 'uas' ? [student.uas] : student[selectedType]))"
                                          class="inline-block px-2.5 py-1 rounded-lg text-sm font-bold bg-slate-50 min-w-[50px]">
                                        {{ getAverage(selectedType === 'uts' ? [student.uts] : selectedType === 'uas' ? [student.uas] : student[selectedType]) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tip -->
                <div class="px-6 py-3 bg-indigo-50/50 border-t border-slate-100 flex justify-between items-center">
                    <p class="text-xs text-slate-500">💡 Klik dua kali (double-click) pada sel nilai untuk mengedit langsung.</p>
                    <button @click="saveAll" class="px-5 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Nilai
                    </button>
                </div>
            </div>

            <!-- Success Toast -->
            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-y-4 opacity-0" enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition ease-in duration-200" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-4 opacity-0">
                <div v-if="showSaved" class="fixed bottom-6 right-6 bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-2xl shadow-emerald-600/30 flex items-center gap-2 z-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold text-sm">Nilai berhasil disimpan!</span>
                </div>
            </Transition>
        </div>
    </TeacherLayout>
</template>
