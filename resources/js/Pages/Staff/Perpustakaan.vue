<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const books = ref([
    { id: 1, title: 'Fisika Dasar 1', author: 'Prof. Yohanes Surya', category: 'Sains', stok: 12, cover: 'bg-blue-200' },
    { id: 2, title: 'Sejarah Indonesia', author: 'Kemendikbud', category: 'Sejarah', stok: 5, cover: 'bg-amber-200' },
    { id: 3, title: 'Pemrograman Web', author: 'Sandhika Galih', category: 'Teknologi', stok: 8, cover: 'bg-emerald-200' },
]);
</script>

<template>
    <Head title="Katalog Perpustakaan" />
    <StaffLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Perpustakaan Digital</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Pengelolaan katalog buku dan peminjaman</p></template>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 mb-6 flex gap-4">
            <div class="relative flex-1">
                <input type="text" placeholder="Cari Judul Buku, Pengarang, atau ISBN..." 
                       class="w-full pl-5 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition outline-none" />
            </div>
            <button class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                + Tambah Buku
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div v-for="book in books" :key="book.id" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all group">
                <div class="h-48 flex items-center justify-center p-4 relative" :class="book.cover">
                    <!-- Placeholder cover design -->
                    <div class="w-24 h-32 bg-white shadow-xl flex flex-col justify-between p-2 rounded-sm border-l-4 border-slate-800">
                        <div class="h-2 bg-slate-200 rounded w-full"></div>
                        <div class="h-1 bg-slate-200 rounded w-1/2"></div>
                        <div class="mt-auto h-2 bg-slate-200 rounded w-3/4"></div>
                    </div>
                </div>
                <div class="p-5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">{{ book.category }}</span>
                    <h3 class="text-lg font-black text-slate-800 mt-2 line-clamp-1">{{ book.title }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ book.author }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-700">{{ book.stok }} Buku Tersedia</span>
                        <button class="text-indigo-600 hover:text-indigo-800 p-2 bg-indigo-50 rounded-lg transition" title="Pinjamkan">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
