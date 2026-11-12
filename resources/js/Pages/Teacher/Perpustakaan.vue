<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    books: { type: Array, default: () => [] },
    categories: { type: Array, default: () => ['Semua', 'Agama Islam', 'Ibadah', 'Kemuhammadiyahan', 'IPAS', 'Bahasa', 'Umum'] },
});

const searchQuery = ref('');
const selectedCategory = ref('Semua');

const filteredBooks = computed(() => {
    return props.books.filter(b => {
        const matchCat = selectedCategory.value === 'Semua' || b.category === selectedCategory.value;
        const q = searchQuery.value.toLowerCase();
        const matchSearch = !q || b.title.toLowerCase().includes(q) || b.author.toLowerCase().includes(q) || b.isbn.includes(q);
        return matchCat && matchSearch;
    });
});

const activeBook = ref(null);
const showReaderModal = ref(false);

const openBook = (book) => {
    activeBook.value = book;
    showReaderModal.value = true;
};
</script>

<template>
    <Head title="Perpustakaan Digital" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Perpustakaan Digital</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Katalog koleksi pustaka & e-Book SD Muhammadiyah Al Mujahidin</p></template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-lg">📚</div>
                    <div>
                        <p class="text-2xl font-black text-slate-800">{{ books.length }}</p>
                        <p class="text-xs text-slate-500">Koleksi Judul Buku</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-lg">📱</div>
                    <div>
                        <p class="text-2xl font-black text-blue-600">{{ books.filter(b => b.hasDigital).length }}</p>
                        <p class="text-xs text-slate-500">E-Book Digital</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-lg">📖</div>
                    <div>
                        <p class="text-2xl font-black text-amber-600">{{ books.reduce((a, b) => a + (b.stock || 0), 0) }}</p>
                        <p class="text-xs text-slate-500">Total Eksemplar Fisik</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-lg">🏷️</div>
                    <div>
                        <p class="text-2xl font-black text-purple-600">{{ categories.length - 1 }}</p>
                        <p class="text-xs text-slate-500">Kategori Pustaka</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                    <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition whitespace-nowrap"
                            :class="selectedCategory === cat ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        {{ cat }}
                    </button>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" v-model="searchQuery" placeholder="Cari judul buku / penulis..."
                           class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Book Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="b in filteredBooks" :key="b.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-start gap-4 mb-3">
                            <div class="w-14 h-18 bg-gradient-to-br from-emerald-100 to-teal-50 border border-emerald-200 rounded-lg flex items-center justify-center text-2xl shrink-0 shadow-sm">
                                {{ b.cover }}
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ b.category }}
                                </span>
                                <h3 class="font-bold text-slate-800 text-sm leading-tight line-clamp-2">{{ b.title }}</h3>
                                <p class="text-xs text-slate-500">Penulis: {{ b.author }}</p>
                                <p class="text-[11px] text-slate-400">Penerbit: {{ b.publisher }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-2">
                        <div class="text-xs text-slate-500">
                            Stok: <strong class="text-slate-800">{{ b.available }} / {{ b.stock }}</strong>
                        </div>
                        <div class="flex gap-2">
                            <button v-if="b.hasDigital" @click="openBook(b)" class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition flex items-center gap-1">
                                📖 Baca E-Book
                            </button>
                            <button v-else class="px-3 py-1.5 text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-200 rounded-lg">
                                Buku Fisik
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-Book Reader Modal -->
            <Teleport to="body">
                <div v-if="showReaderModal && activeBook" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 md:p-8 max-h-[90vh] overflow-y-auto space-y-5">
                        <div class="flex justify-between items-start pb-4 border-b border-slate-100">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded">Pembaca Digital E-Pustaka</span>
                                <h3 class="font-bold text-slate-800 text-lg">{{ activeBook.title }}</h3>
                                <p class="text-xs text-slate-500">{{ activeBook.author }} • {{ activeBook.publisher }}</p>
                            </div>
                            <button @click="showReaderModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>

                        <!-- Reader Content Simulation -->
                        <div class="border border-slate-200 rounded-xl p-6 bg-amber-50/20 text-slate-800 text-sm leading-relaxed space-y-4 font-serif">
                            <h4 class="font-bold text-center text-base font-sans uppercase tracking-wider text-slate-700">BAB 1 : PENDAHULUAN & TELADAN UTAMA</h4>
                            <p>
                                Pendidikan karakter islami berakar pada penanaman nilai kejujuran, kasih sayang, dan amanah sejak usia dini. Di lingkungan SD Muhammadiyah Al Mujahidin, pembiasaan akhlaqul karimah senantiasa diintegrasikan dalam setiap mata pelajaran dan interaksi sehari-hari.
                            </p>
                            <p>
                                Rasulullah SAW bersabda: <em>"Sesungguhnya aku diutus hanyalah untuk menyempurnakan akhlak yang mulia."</em> (HR. Ahmad). Buku ini menyajikan panduan aplikatif bagi para pendidik dan santri dalam menerapkan nilai-nilai teladan tersebut.
                            </p>
                            <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200 text-xs text-emerald-800 font-sans">
                                💡 <strong>Catatan Pustakawan:</strong> Dokumen digital ini tersedia penuh untuk seluruh civitas akademika SD Muhammadiyah Al Mujahidin Wonosari.
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button @click="showReaderModal = false" class="px-5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition">
                                Selesai Membaca
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
