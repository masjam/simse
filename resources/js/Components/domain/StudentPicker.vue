<script setup>
import { ref, computed } from 'vue';
import AppInput from '@/Components/ui/AppInput.vue';
import Avatar from '@/Components/ui/Avatar.vue';

const props = defineProps({
    modelValue: {
        type: [String, Object],
        default: null,
    },
    students: {
        type: Array,
        default: () => [], // Array of { id, name, nisn, class }
    },
    placeholder: {
        type: String,
        default: 'Cari nama atau NISN siswa...',
    },
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref('');
const isOpen = ref(false);

const selectedStudent = computed(() => {
    if (!props.modelValue) return null;
    if (typeof props.modelValue === 'object') return props.modelValue;
    return props.students.find((s) => s.id === props.modelValue) || null;
});

const filteredStudents = computed(() => {
    if (!query.value.trim()) return props.students.slice(0, 8);
    const q = query.value.toLowerCase();
    return props.students
        .filter((s) => {
            const name = (s.name || s.user?.name || '').toLowerCase();
            const nisn = (s.nisn || '').toLowerCase();
            const cls = (s.class || s.student_class?.name || '').toLowerCase();
            return name.includes(q) || nisn.includes(q) || cls.includes(q);
        })
        .slice(0, 10);
});

function selectStudent(student) {
    emit('update:modelValue', student.id);
    emit('select', student);
    isOpen.value = false;
    query.value = '';
}

function clear() {
    emit('update:modelValue', null);
    emit('select', null);
}
</script>

<template>
    <div class="relative w-full">
        <!-- Selected Pill View -->
        <div
            v-if="selectedStudent"
            class="flex items-center justify-between p-2 pl-3 rounded-control border border-line bg-surface min-h-[44px]"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <Avatar :name="selectedStudent.name || selectedStudent.user?.name" size="sm" />
                <div class="truncate">
                    <p class="text-xs font-bold text-ink truncate">
                        {{ selectedStudent.name || selectedStudent.user?.name }}
                    </p>
                    <p class="text-[11px] text-ink-muted">
                        NISN: {{ selectedStudent.nisn || '-' }} • {{ selectedStudent.class || selectedStudent.student_class?.name || '-' }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                class="p-1 rounded-control text-ink-muted hover:text-status-danger-fg transition"
                aria-label="Hapus pilihan"
                @click="clear"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Search Input -->
        <div v-else>
            <AppInput
                v-model="query"
                :placeholder="placeholder"
                @focus="isOpen = true"
            >
                <template #prefix>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </template>
            </AppInput>

            <!-- Dropdown Results -->
            <div
                v-if="isOpen && filteredStudents.length > 0"
                class="absolute z-30 mt-1 w-full rounded-control border border-line bg-surface shadow-pop max-h-60 overflow-y-auto p-1 divide-y divide-line/60"
            >
                <button
                    v-for="s in filteredStudents"
                    :key="s.id"
                    type="button"
                    class="w-full text-left p-2 rounded-control hover:bg-surface-raised flex items-center gap-2.5 transition text-xs"
                    @click="selectStudent(s)"
                >
                    <Avatar :name="s.name || s.user?.name" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink truncate">
                            {{ s.name || s.user?.name }}
                        </p>
                        <p class="text-[11px] text-ink-muted">
                            NISN: {{ s.nisn || '-' }} • Kelas {{ s.class || s.student_class?.name || '-' }}
                        </p>
                    </div>
                </button>
            </div>
        </div>

        <div v-if="isOpen" class="fixed inset-0 z-20" @click="isOpen = false" />
    </div>
</template>

