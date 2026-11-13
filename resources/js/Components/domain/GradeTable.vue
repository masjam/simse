<script setup>
import { ref } from 'vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    students: { type: Array, required: true },
    selectedType: { type: String, default: 'tugas' },
    kkm: { type: Number, default: 75 },
});

const emit = defineEmits(['cell-change']);

const editingCell = ref(null);
const editValue = ref('');

const isBelowKkm = (val) => {
    if (val === null || val === undefined || val === '') return false;
    const num = Number(val);
    return !isNaN(num) && num < props.kkm;
};

const startEdit = (studentId, type, idx) => {
    editingCell.value = `${studentId}-${type}-${idx}`;
    const student = props.students.find(s => s.id === studentId);
    if (type === 'uts' || type === 'uas') {
        editValue.value = student[type] ?? '';
    } else {
        editValue.value = student[type]?.[idx] ?? '';
    }
};

const saveEdit = (studentId, type, idx) => {
    const val = editValue.value === '' ? null : parseInt(editValue.value);
    emit('cell-change', { studentId, type, idx, value: val });
    editingCell.value = null;
};

const cancelEdit = () => {
    editingCell.value = null;
};

const onKeydown = (e, studentId, type, idx) => {
    if (e.key === 'Enter' || e.key === 'Tab') {
        e.preventDefault();
        saveEdit(studentId, type, idx);
    } else if (e.key === 'Escape') {
        cancelEdit();
    }
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-surface-raised border-b border-line text-ink-muted uppercase font-bold text-[11px]">
                <tr>
                    <th class="px-4 py-3">Nama Siswa</th>
                    <th v-if="selectedType === 'tugas'" class="px-3 py-3 text-center">Tugas 1</th>
                    <th v-if="selectedType === 'tugas'" class="px-3 py-3 text-center">Tugas 2</th>
                    <th v-if="selectedType === 'tugas'" class="px-3 py-3 text-center">Tugas 3</th>
                    <th v-if="selectedType === 'kuis'" class="px-3 py-3 text-center">Kuis 1</th>
                    <th v-if="selectedType === 'kuis'" class="px-3 py-3 text-center">Kuis 2</th>
                    <th v-if="selectedType === 'uts'" class="px-3 py-3 text-center">Nilai UTS</th>
                    <th v-if="selectedType === 'uas'" class="px-3 py-3 text-center">Nilai UAS</th>
                    <th class="px-3 py-3 text-center">Rata-rata</th>
                    <th class="px-4 py-3 text-center">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line text-ink">
                <tr v-for="student in students" :key="student.id" class="hover:bg-surface-raised/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-bold text-xs">{{ student.name }}</p>
                        <p class="text-[11px] text-ink-muted">NISN: {{ student.nisn || '-' }}</p>
                    </td>

                    <!-- Tugas 1-3 -->
                    <template v-if="selectedType === 'tugas'">
                        <td v-for="idx in [0, 1, 2]" :key="idx" class="px-3 py-3 text-center">
                            <div v-if="editingCell === `${student.id}-tugas-${idx}`" class="flex justify-center">
                                <input
                                    v-model="editValue"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-16 text-center text-xs font-mono font-bold border border-brand-500 rounded-control p-1 bg-surface text-ink focus:outline-none focus:ring-2 focus:ring-focus"
                                    @blur="saveEdit(student.id, 'tugas', idx)"
                                    @keydown="onKeydown($event, student.id, 'tugas', idx)"
                                    autofocus
                                />
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="startEdit(student.id, 'tugas', idx)"
                                class="w-14 py-1 rounded-control text-xs font-mono font-bold transition-all border border-transparent hover:border-line"
                                :class="isBelowKkm(student.tugas?.[idx])
                                    ? 'bg-status-danger-bg text-status-danger-fg font-black border-status-danger-line'
                                    : (student.tugas?.[idx] !== null && student.tugas?.[idx] !== undefined ? 'bg-surface-raised text-ink' : 'text-ink-muted')"
                            >
                                {{ student.tugas?.[idx] ?? '-' }}
                            </button>
                        </td>
                    </template>

                    <!-- Kuis 1-2 -->
                    <template v-if="selectedType === 'kuis'">
                        <td v-for="idx in [0, 1]" :key="idx" class="px-3 py-3 text-center">
                            <div v-if="editingCell === `${student.id}-kuis-${idx}`" class="flex justify-center">
                                <input
                                    v-model="editValue"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-16 text-center text-xs font-mono font-bold border border-brand-500 rounded-control p-1 bg-surface text-ink focus:outline-none focus:ring-2 focus:ring-focus"
                                    @blur="saveEdit(student.id, 'kuis', idx)"
                                    @keydown="onKeydown($event, student.id, 'kuis', idx)"
                                    autofocus
                                />
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="startEdit(student.id, 'kuis', idx)"
                                class="w-14 py-1 rounded-control text-xs font-mono font-bold transition-all border border-transparent hover:border-line"
                                :class="isBelowKkm(student.kuis?.[idx])
                                    ? 'bg-status-danger-bg text-status-danger-fg font-black border-status-danger-line'
                                    : (student.kuis?.[idx] !== null && student.kuis?.[idx] !== undefined ? 'bg-surface-raised text-ink' : 'text-ink-muted')"
                            >
                                {{ student.kuis?.[idx] ?? '-' }}
                            </button>
                        </td>
                    </template>

                    <!-- UTS -->
                    <template v-if="selectedType === 'uts'">
                        <td class="px-3 py-3 text-center">
                            <div v-if="editingCell === `${student.id}-uts-0`" class="flex justify-center">
                                <input
                                    v-model="editValue"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-16 text-center text-xs font-mono font-bold border border-brand-500 rounded-control p-1 bg-surface text-ink focus:outline-none focus:ring-2 focus:ring-focus"
                                    @blur="saveEdit(student.id, 'uts', 0)"
                                    @keydown="onKeydown($event, student.id, 'uts', 0)"
                                    autofocus
                                />
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="startEdit(student.id, 'uts', 0)"
                                class="w-14 py-1 rounded-control text-xs font-mono font-bold transition-all border border-transparent hover:border-line"
                                :class="isBelowKkm(student.uts)
                                    ? 'bg-status-danger-bg text-status-danger-fg font-black border-status-danger-line'
                                    : (student.uts !== null && student.uts !== undefined ? 'bg-surface-raised text-ink' : 'text-ink-muted')"
                            >
                                {{ student.uts ?? '-' }}
                            </button>
                        </td>
                    </template>

                    <!-- UAS -->
                    <template v-if="selectedType === 'uas'">
                        <td class="px-3 py-3 text-center">
                            <div v-if="editingCell === `${student.id}-uas-0`" class="flex justify-center">
                                <input
                                    v-model="editValue"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-16 text-center text-xs font-mono font-bold border border-brand-500 rounded-control p-1 bg-surface text-ink focus:outline-none focus:ring-2 focus:ring-focus"
                                    @blur="saveEdit(student.id, 'uas', 0)"
                                    @keydown="onKeydown($event, student.id, 'uas', 0)"
                                    autofocus
                                />
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="startEdit(student.id, 'uas', 0)"
                                class="w-14 py-1 rounded-control text-xs font-mono font-bold transition-all border border-transparent hover:border-line"
                                :class="isBelowKkm(student.uas)
                                    ? 'bg-status-danger-bg text-status-danger-fg font-black border-status-danger-line'
                                    : (student.uas !== null && student.uas !== undefined ? 'bg-surface-raised text-ink' : 'text-ink-muted')"
                            >
                                {{ student.uas ?? '-' }}
                            </button>
                        </td>
                    </template>

                    <!-- Rata-rata -->
                    <td class="px-3 py-3 text-center">
                        <span class="font-mono font-bold text-xs" :class="isBelowKkm(student.avg) ? 'text-status-danger-fg' : 'text-brand-600'">
                            {{ student.avg ?? '-' }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="text-[11px] text-ink-muted">Tekan Tab/Enter untuk lanjut</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

