<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subjects: Object,
});

const isModalOpen = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    code: '',
    name: '',
});

const openModal = (subject = null) => {
    isModalOpen.value = true;
    if (subject) {
        editMode.value = true;
        currentId.value = subject.id;
        form.code = subject.code;
        form.name = subject.name;
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
        form.put(route('admin.subjects.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.subjects.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSubject = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        form.delete(route('admin.subjects.destroy', id));
    }
};
</script>

<template>
    <Head title="Mata Pelajaran" />
    <AdminLayout>
        <template #header-title>
            <h2 class="text-xl font-bold text-white">Mata Pelajaran</h2>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-400">Data master mata pelajaran Kurikulum Merdeka SD</p>
        </template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-slate-900/50">
                <div class="text-white font-bold">Daftar Mata Pelajaran</div>
                <button @click="openModal()" class="px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Mapel
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-4 w-24">Kode</th>
                            <th scope="col" class="px-6 py-4">Nama Mata Pelajaran</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subject in subjects.data" :key="subject.id" class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 font-bold text-blue-400">{{ subject.code }}</td>
                            <td class="px-6 py-4">{{ subject.name }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(subject)" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteSubject(subject.id)" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!subjects.data.length">
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ subjects.from || 0 }} sampai {{ subjects.to || 0 }} dari {{ subjects.total }} data</span>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-slate-900 border border-white/10 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">{{ editMode ? 'Edit Mapel' : 'Tambah Mapel' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Kode Mapel</label>
                            <input v-model="form.code" type="text" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" placeholder="Contoh: PAI, IPAS, MTK" required />
                            <div v-if="form.errors.code" class="text-rose-500 text-xs mt-1">{{ form.errors.code }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nama Mata Pelajaran</label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition" placeholder="Contoh: Ilmu Pengetahuan Alam dan Sosial" required />
                            <div v-if="form.errors.name" class="text-rose-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-white/10 flex justify-end gap-3 bg-slate-800/50 rounded-b-2xl">
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
