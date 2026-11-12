<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
    tingkats: Array,
    rombels: Array,
});

const search = ref(props.filters.search || '');
const tingkat_id = ref(props.filters.tingkat_id || '');
const rombel_id = ref(props.filters.rombel_id || '');
const fileInput = ref(null);

const importForm = useForm({
    file: null,
});

// Watchers to trigger reload on filter change
const updateFilters = () => {
    router.get(route('admin.students.index'), {
        search: search.value,
        tingkat_id: tingkat_id.value,
        rombel_id: rombel_id.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch([search, tingkat_id, rombel_id], () => {
    updateFilters();
});

const handleImport = () => {
    if (!importForm.file) return;
    importForm.post(route('admin.users.import'), {
        onSuccess: () => {
            alert('Berhasil mengimpor siswa');
            importForm.reset();
            fileInput.value.value = '';
        },
        onError: (err) => {
            console.error(err);
            alert('Gagal mengimpor file');
        }
    });
};
</script>

<template>
    <Head title="Modul Siswa" />
    <AdminLayout>
        <template #header-title><h1 class="text-2xl font-black text-white">Modul Siswa</h1></template>
        <template #header-subtitle><p class="text-slate-400">Kelola data peserta didik</p></template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-900/50">
                <div class="flex gap-4 w-full md:w-auto flex-1">
                    <div class="relative w-full md:w-64 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-rose-500 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input v-model="search" type="text" class="block w-full pl-11 pr-4 py-3 bg-slate-950/50 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none" placeholder="Cari nama, email..." />
                    </div>

                    <!-- Siswa Filters -->
                    <template v-if="true">
                        <select v-model="tingkat_id" class="px-4 py-3 bg-slate-950/50 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none w-32">
                            <option value="">Semua Tingkat</option>
                            <option v-for="t in tingkats" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                        <select v-model="rombel_id" class="px-4 py-3 bg-slate-950/50 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none w-32">
                            <option value="">Semua Rombel</option>
                            <option v-for="r in rombels" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                    </template>
                </div>

                <div class="flex gap-3 w-full md:w-auto">
                    <!-- Import Export -->
                    <div class="flex items-center gap-2 border-r border-white/10 pr-4 mr-2">
                        <a :href="route('admin.users.template', { tab: 'siswa' })" class="text-xs text-blue-400 hover:underline px-2">Unduh Template</a>
                        <input type="file" ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv" @change="e => { importForm.file = e.target.files[0]; handleImport(); }" />
                        <button @click="$refs.fileInput.click()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg border border-white/10 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import
                        </button>
                        <a :href="route('admin.users.export')" class="px-3 py-2 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-400 border border-emerald-500/30 text-xs font-bold rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export
                        </a>
                    </div>
                    
                    <button class="px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20 flex-1 md:flex-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Siswa
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-4">Pengguna</th>
                            <th scope="col" class="px-6 py-4">Role Akses</th>
                            <th scope="col" class="px-6 py-4">Tingkat & Rombel</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Tgl Terdaftar</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users.data" :key="user.id" class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=random`" class="w-10 h-10 rounded-full shadow-md" />
                                    <div>
                                        <div class="font-bold text-white text-base">{{ user.name }}</div>
                                        <div class="text-xs text-slate-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="role in user.roles" :key="role.id" 
                                          class="px-2.5 py-1 text-[10px] uppercase tracking-wider font-bold rounded-md bg-white/10 text-slate-300 border border-white/10">
                                        {{ role.name }}
                                    </span>
                                    <span v-if="!user.roles.length" class="text-slate-500 italic text-xs">Belum ada role</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="user.student?.student_class" class="text-xs font-bold text-indigo-400">
                                    {{ user.student.student_class.tingkat?.name || '-' }} / {{ user.student.student_class.name }}
                                </div>
                                <span v-else class="text-slate-500 italic text-xs">Belum diatur</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="text-emerald-400 font-medium text-xs">Aktif</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.students.edit', user.id)" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition" title="Edit Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </Link>
                                    <button class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan 1 dari {{ users.total }} pengguna</span>
                <div class="flex gap-1">
                    <button class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition">&laquo; Prev</button>
                    <button class="px-3 py-1.5 rounded-lg bg-rose-500 text-white font-bold transition">1</button>
                    <button class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition">Next &raquo;</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
