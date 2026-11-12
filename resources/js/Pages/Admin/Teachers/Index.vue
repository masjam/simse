<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const fileInput = ref(null);

const importForm = useForm({
    file: null,
});

const updateFilters = () => {
    router.get(route('admin.teachers.index'), {
        search: search.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, () => {
    updateFilters();
});

const handleImport = () => {
    if (!importForm.file) return;
    importForm.post(route('admin.users.import'), {
        onSuccess: () => {
            alert('Berhasil mengimpor guru');
            importForm.reset();
            if(fileInput.value) fileInput.value.value = '';
        },
    });
};
</script>

<template>
    <Head title="Modul Guru" />
    <AdminLayout>
        <template #header-title><h1 class="text-2xl font-black text-white">Data Guru</h1></template>
        <template #header-subtitle><p class="text-slate-400">Kelola data tenaga pendidik</p></template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-900/50">
                <div class="flex gap-4 w-full md:w-auto flex-1">
                    <!-- Search -->
                    <div class="relative flex-1 md:max-w-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input v-model="search" type="text" class="w-full pl-10 pr-4 py-3 bg-slate-950/50 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 outline-none transition-all placeholder:text-slate-500" placeholder="Cari nama atau email..." />
                    </div>
                </div>

                <div class="flex gap-3 w-full md:w-auto">
                    <!-- Import Export -->
                    <div class="flex items-center gap-2 border-r border-white/10 pr-4 mr-2">
                        <input type="file" ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv" @change="e => { importForm.file = e.target.files[0]; handleImport(); }" />
                        <button @click="$refs.fileInput.click()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg border border-white/10 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import
                        </button>
                    </div>
                    
                    <button class="px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20 flex-1 md:flex-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Guru
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
                                    <button class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.data.length">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">
                                Data guru tidak ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-white/5 flex items-center justify-between bg-slate-900/50">
                <span class="text-sm text-slate-400">
                    Menampilkan <span class="font-bold text-white">{{ users.from || 0 }}</span> sampai <span class="font-bold text-white">{{ users.to || 0 }}</span> dari <span class="font-bold text-white">{{ users.total || 0 }}</span> guru
                </span>
                <div class="flex gap-1">
                    <Link v-for="link in users.links" :key="link.label" :href="link.url || '#'" 
                          class="px-3 py-1.5 rounded-lg text-sm transition-all"
                          :class="[
                              link.active ? 'bg-rose-500 text-white font-bold shadow-lg shadow-rose-500/20' : 'text-slate-400 hover:bg-white/5',
                              !link.url && 'opacity-50 cursor-not-allowed'
                          ]"
                          v-html="link.label">
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
