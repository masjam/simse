<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Modal from '@/Components/ui/Modal.vue';

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
        const matchSearch = !q || b.title.toLowerCase().includes(q) || b.author.toLowerCase().includes(q) || (b.isbn && b.isbn.includes(q));
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
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Perpustakaan Digital"
                subtitle="Katalog koleksi pustaka & e-Book SD Muhammadiyah Al Mujahidin"
            />

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <StatCard
                    title="Koleksi Judul"
                    :value="books.length"
                    subtitle="Judul Terdaftar"
                    icon-bg="brand"
                >
                    <template #icon>📚</template>
                </StatCard>
                <StatCard
                    title="E-Book Digital"
                    :value="books.filter(b => b.hasDigital).length"
                    subtitle="Dapat Dibaca Online"
                    icon-bg="success"
                >
                    <template #icon>📱</template>
                </StatCard>
                <StatCard
                    title="Eksemplar Fisik"
                    :value="books.reduce((a, b) => a + (b.stock || 0), 0)"
                    subtitle="Total Buku Fisik"
                    icon-bg="accent"
                >
                    <template #icon>📖</template>
                </StatCard>
                <StatCard
                    title="Kategori Pustaka"
                    :value="categories.length - 1"
                    subtitle="Klasifikasi Buku"
                    icon-bg="warning"
                >
                    <template #icon>🏷️</template>
                </StatCard>
            </div>

            <!-- Filter Bar -->
            <div class="bg-surface rounded-card border border-line shadow-card p-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                    <AppButton
                        v-for="cat in categories"
                        :key="cat"
                        size="sm"
                        :variant="selectedCategory === cat ? 'primary' : 'secondary'"
                        class="whitespace-nowrap"
                        @click="selectedCategory = cat"
                    >
                        {{ cat }}
                    </AppButton>
                </div>
                <div class="w-full sm:w-72">
                    <SearchInput
                        v-model="searchQuery"
                        placeholder="Cari judul buku atau penulis..."
                    />
                </div>
            </div>

            <!-- Book Cards Grid -->
            <div v-if="filteredBooks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <AppCard
                    v-for="b in filteredBooks"
                    :key="b.id"
                    hover
                    class="flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-start gap-4 mb-3">
                            <div class="w-14 h-20 bg-brand-50 border border-brand-200 text-brand-700 rounded-control flex items-center justify-center text-3xl shrink-0 shadow-card">
                                {{ b.cover || '📖' }}
                            </div>
                            <div class="space-y-1 min-w-0 flex-1">
                                <StatusBadge variant="brand" size="sm">
                                    {{ b.category }}
                                </StatusBadge>
                                <h3 class="font-bold text-ink text-sm leading-tight line-clamp-2">
                                    {{ b.title }}
                                </h3>
                                <p class="text-xs text-ink-muted truncate">Penulis: {{ b.author }}</p>
                                <p class="text-[11px] text-ink-muted truncate">Penerbit: {{ b.publisher }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-between mt-2">
                        <div class="text-xs text-ink-muted">
                            Stok: <strong class="text-ink">{{ b.available }} / {{ b.stock }}</strong>
                        </div>
                        <div class="flex gap-2">
                            <AppButton
                                v-if="b.hasDigital"
                                size="sm"
                                variant="outline"
                                @click="openBook(b)"
                            >
                                📖 Baca E-Book
                            </AppButton>
                            <span
                                v-else
                                class="px-2.5 py-1 text-xs font-medium text-ink-muted bg-surface-raised border border-line rounded-control"
                            >
                                Buku Fisik
                            </span>
                        </div>
                    </div>
                </AppCard>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-surface rounded-card border border-line p-8 shadow-card">
                <EmptyState
                    title="Koleksi Buku Tidak Ditemukan"
                    description="Tidak ada koleksi pustaka atau e-Book yang sesuai dengan kriteria filter atau pencarian Anda."
                />
            </div>

            <!-- E-Book Reader Modal -->
            <Modal
                :show="showReaderModal"
                max-width="2xl"
                @close="showReaderModal = false"
            >
                <div v-if="activeBook" class="p-6 space-y-5">
                    <div class="flex justify-between items-start pb-4 border-b border-line">
                        <div class="space-y-1">
                            <StatusBadge variant="brand" size="sm">
                                Pembaca Digital E-Pustaka
                            </StatusBadge>
                            <h3 class="font-bold text-ink text-lg">{{ activeBook.title }}</h3>
                            <p class="text-xs text-ink-muted">{{ activeBook.author }} • {{ activeBook.publisher }}</p>
                        </div>
                    </div>

                    <!-- Reader Content Simulation -->
                    <div class="border border-line rounded-control p-6 bg-surface-raised text-ink text-sm leading-relaxed space-y-4 font-serif">
                        <h4 class="font-bold text-center text-base font-sans uppercase tracking-wider text-brand-700">
                            BAB 1 : PENDAHULUAN & TELADAN UTAMA
                        </h4>
                        <p>
                            Pendidikan karakter islami berakar pada penanaman nilai kejujuran, kasih sayang, dan amanah sejak usia dini. Di lingkungan SD Muhammadiyah Al Mujahidin, pembiasaan akhlaqul karimah senantiasa diintegrasikan dalam setiap mata pelajaran dan interaksi sehari-hari.
                        </p>
                        <p>
                            Rasulullah SAW bersabda: <em>"Sesungguhnya aku diutus hanyalah untuk menyempurnakan akhlak yang mulia."</em> (HR. Ahmad). Buku ini menyajikan panduan aplikatif bagi para pendidik dan siswa dalam menerapkan nilai-nilai teladan tersebut.
                        </p>
                        <div class="p-4 bg-brand-50 rounded-control border border-brand-200 text-xs text-brand-900 font-sans">
                            💡 <strong>Catatan Pustakawan:</strong> Dokumen digital ini tersedia penuh untuk seluruh civitas akademika SD Muhammadiyah Al Mujahidin Wonosari.
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <AppButton
                            variant="primary"
                            @click="showReaderModal = false"
                        >
                            Selesai Membaca
                        </AppButton>
                    </div>
                </div>
            </Modal>
        </div>
    </PortalShell>
</template>
