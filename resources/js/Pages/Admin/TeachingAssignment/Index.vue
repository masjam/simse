<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    assignments: Object,
    teachers: Array,
    student_classes: Array,
    subjects: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref(null);

const form = useForm({
    teacher_id: '',
    subject_id: '',
    student_class_id: '',
    student_class_ids: [],
    jpl: '',
});

const toggleClass = (id) => {
    const index = form.student_class_ids.indexOf(id);
    if (index === -1) {
        form.student_class_ids.push(id);
    } else {
        form.student_class_ids.splice(index, 1);
    }
};

const openModal = () => {
    isEditMode.value = false;
    isModalOpen.value = true;
    form.reset();
};

const openEditModal = (assignment) => {
    isEditMode.value = true;
    isModalOpen.value = true;
    editingId.value = assignment.id;
    form.teacher_id = assignment.teacher_id;
    form.subject_id = assignment.subject_id;
    form.student_class_id = assignment.student_class_id;
    form.jpl = assignment.jpl;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('admin.teaching-assignments.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.teaching-assignments.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAssignment = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus tugas mengajar ini?')) {
        form.delete(route('admin.teaching-assignments.destroy', id));
    }
};
</script>

<template>
    <Head title="Pembagian Tugas Mengajar" />
    <AdminLayout>
        <template #header-title>
            <h2 class="text-xl font-bold text-white">Pembagian Tugas Mengajar</h2>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-400">Plotting guru untuk mata pelajaran dan kelas</p>
        </template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-900/50">
                <div class="text-white font-bold">Daftar Penugasan</div>
                <button @click="openModal()" class="px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Penugasan
                </button>
            </div>

            <!-- Error message display -->
            <div v-if="form.errors.message" class="mx-6 mt-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-sm flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ form.errors.message }}
            </div>

            <!-- Table -->
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-y border-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-4">Guru</th>
                            <th scope="col" class="px-6 py-4">Mata Pelajaran</th>
                            <th scope="col" class="px-6 py-4">Kelas / Rombel</th>
                            <th scope="col" class="px-6 py-4">JPL / Minggu</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="assignment in assignments.data" :key="assignment.id" class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 font-bold text-blue-400">{{ assignment.teacher?.user?.name || '-' }}</td>
                            <td class="px-6 py-4">{{ assignment.subject?.name || '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-700/50 border border-slate-600 px-3 py-1 rounded-full text-xs">
                                    {{ assignment.student_class?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold">{{ assignment.jpl || '-' }} Jam</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(assignment)" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteAssignment(assignment.id)" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!assignments.data.length">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data penugasan mengajar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ assignments.from || 0 }} sampai {{ assignments.to || 0 }} dari {{ assignments.total }} data</span>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-slate-900 border border-white/10 rounded-2xl w-full max-w-md shadow-2xl max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-white/10 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
                    <h3 class="text-lg font-bold text-white">{{ isEditMode ? 'Edit Tugas Mengajar' : 'Tambah Tugas Mengajar' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Guru Pengajar</label>
                            <select v-model="form.teacher_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required>
                                <option value="" disabled>Pilih Guru</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.user?.name || 'Guru' }}</option>
                            </select>
                            <div v-if="form.errors.teacher_id" class="text-rose-500 text-xs mt-1">{{ form.errors.teacher_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Mata Pelajaran</label>
                            <select v-model="form.subject_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required>
                                <option value="" disabled>Pilih Mata Pelajaran</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <div v-if="form.errors.subject_id" class="text-rose-500 text-xs mt-1">{{ form.errors.subject_id }}</div>
                        </div>

                        <div v-if="!isEditMode">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Rombongan Belajar (Pilih satu atau lebih)</label>
                            <div class="grid grid-cols-2 gap-3 max-h-48 overflow-y-auto custom-scrollbar p-1">
                                <label v-for="c in student_classes" :key="c.id" class="flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-slate-950/50 hover:bg-slate-800 transition cursor-pointer" :class="{'border-rose-500/50 bg-rose-500/5': form.student_class_ids.includes(c.id)}">
                                    <input type="checkbox" :value="c.id" @change="toggleClass(c.id)" :checked="form.student_class_ids.includes(c.id)" class="w-4 h-4 rounded text-rose-500 bg-slate-900 border-white/20 focus:ring-rose-500/50 focus:ring-offset-slate-900" />
                                    <span class="text-sm font-medium text-slate-300">{{ c.name }}</span>
                                </label>
                            </div>
                            <div v-if="form.errors.student_class_ids" class="text-rose-500 text-xs mt-1">{{ form.errors.student_class_ids }}</div>
                        </div>

                        <div v-else>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Rombongan Belajar (Kelas)</label>
                            <select v-model="form.student_class_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required>
                                <option value="" disabled>Pilih Kelas</option>
                                <option v-for="c in student_classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <div v-if="form.errors.student_class_id" class="text-rose-500 text-xs mt-1">{{ form.errors.student_class_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Jumlah Jam Pelajaran (JPL) per Minggu</label>
                            <input v-model="form.jpl" type="number" min="1" max="40" placeholder="Misal: 4" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required />
                            <div v-if="form.errors.jpl" class="text-rose-500 text-xs mt-1">{{ form.errors.jpl }}</div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-white/10 flex justify-end gap-3 bg-slate-800/50 rounded-b-2xl sticky bottom-0">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-bold text-slate-300 hover:text-white transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-rose-500/20 disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
