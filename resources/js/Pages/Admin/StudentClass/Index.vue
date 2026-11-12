<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rombels: Object,
    tingkats: Array,
    academic_years: Array,
    teachers: Array,
});

const isModalOpen = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
    level: '',
    tingkat_id: '',
    academic_year_id: '',
    teacher_id: '',
});

const openModal = (rombel = null) => {
    isModalOpen.value = true;
    if (rombel) {
        editMode.value = true;
        currentId.value = rombel.id;
        form.name = rombel.name;
        form.level = rombel.level;
        form.tingkat_id = rombel.tingkat_id;
        form.academic_year_id = rombel.academic_year_id;
        form.teacher_id = rombel.teacher_id;
    } else {
        editMode.value = false;
        currentId.value = null;
        form.reset();
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editMode.value) {
        form.put(route('admin.student-classes.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.student-classes.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRombel = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        form.delete(route('admin.student-classes.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen Rombel" />
    <AdminLayout>
        <template #header-title>
            <h2 class="text-xl font-bold text-white">Manajemen Rombongan Belajar (Rombel)</h2>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-400">Data kelas dan pembagian rombel</p>
        </template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-900/50">
                <div class="text-white font-bold">Daftar Rombel</div>
                <button @click="openModal()" class="px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Rombel
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-4">Tingkat</th>
                            <th scope="col" class="px-6 py-4">Nama Rombel</th>
                            <th scope="col" class="px-6 py-4">Wali Kelas</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="rombel in rombels.data" :key="rombel.id" class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 font-bold">{{ rombel.tingkat?.name || rombel.level }}</td>
                            <td class="px-6 py-4">{{ rombel.name }}</td>
                            <td class="px-6 py-4">{{ rombel.teacher?.user?.name || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(rombel)" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteRombel(rombel.id)" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rombels.data.length">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data rombel.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ rombels.from || 0 }} sampai {{ rombels.to || 0 }} dari {{ rombels.total }} data</span>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-slate-900 border border-white/10 rounded-2xl w-full max-w-md shadow-2xl max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-white/10 flex justify-between items-center sticky top-0 bg-slate-900">
                    <h3 class="text-lg font-bold text-white">{{ editMode ? 'Edit Rombel' : 'Tambah Rombel' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Tingkat (Master Data)</label>
                            <select v-model="form.tingkat_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required>
                                <option value="" disabled>Pilih Tingkat</option>
                                <option v-for="t in tingkats" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                            <div v-if="form.errors.tingkat_id" class="text-rose-500 text-xs mt-1">{{ form.errors.tingkat_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Level Kelas (Teks)</label>
                            <input v-model="form.level" type="text" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" placeholder="Contoh: 10, 11, 12" required />
                            <div v-if="form.errors.level" class="text-rose-500 text-xs mt-1">{{ form.errors.level }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Tahun Ajaran</label>
                            <select v-model="form.academic_year_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" required>
                                <option value="" disabled>Pilih Tahun Ajaran</option>
                                <option v-for="a in academic_years" :key="a.id" :value="a.id">{{ a.name }} ({{ a.semester }})</option>
                            </select>
                            <div v-if="form.errors.academic_year_id" class="text-rose-500 text-xs mt-1">{{ form.errors.academic_year_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nama Rombel</label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" placeholder="Contoh: 10 RPL 1" required />
                            <div v-if="form.errors.name" class="text-rose-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Wali Kelas (Opsional)</label>
                            <select v-model="form.teacher_id" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition">
                                <option value="" disabled>Pilih Wali Kelas</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.user?.name || 'Guru' }}</option>
                            </select>
                            <div v-if="form.errors.teacher_id" class="text-rose-500 text-xs mt-1">{{ form.errors.teacher_id }}</div>
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
