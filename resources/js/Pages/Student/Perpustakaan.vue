<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    student: { type: Object, required: true },
    books: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    selectedCategory: { type: String, default: '' },
    searchQuery: { type: String, default: '' },
    loans: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            borrowed_count: 0,
            returned_count: 0,
            total_loans: 0,
            total_available_books: 0,
        }),
    },
});

const activeTab = ref('katalog');
const categoryFilter = ref(props.selectedCategory);
const searchInput = ref(props.searchQuery);

const tabs = [
    { id: 'katalog', label: `Katalog Buku (${props.books.length})` },
    { id: 'pinjaman', label: `Peminjaman Saya (${props.loans.length})` },
];

const categoryOptions = [
    { value: '', label: 'Semua Kategori Buku' },
    ...props.categories.map((c) => ({ value: c, label: c })),
];

const applyFilter = () => {
    router.get(
        route('siswa.perpustakaan'),
        {
            category: categoryFilter.value,
            search: searchInput.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};

const resetFilter = () => {
    categoryFilter.value = '';
    searchInput.value = '';
    applyFilter();
};
</script>

<template>
    <Head title="Perpustakaan Sekolah & e-Book" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Perpustakaan & Pojok Baca"
                :description="`Koleksi Buku & Literasi Digital Siswa • ${student.class_name}`"
            />
        </template>

        <div class="space-y-4">
            <!-- Ringkasan Aktivitas Literasi -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <AppCard class="p-3 text-center">
                    <span class="text-[11px] text-ink-muted">Sedang Dipinjam</span>
                    <p class="text-lg font-bold text-brand-700 mt-0.5">
                        {{ summary.borrowed_count }} Buku
                    </p>
                </AppCard>

                <AppCard class="p-3 text-center">
                    <span class="text-[11px] text-ink-muted">Telah Selesai</span>
                    <p class="text-lg font-bold text-status-success-fg mt-0.5">
                        {{ summary.returned_count }} Buku
                    </p>
                </AppCard>

                <AppCard class="p-3 text-center">
                    <span class="text-[11px] text-ink-muted">Total Riwayat Baca</span>
                    <p class="text-lg font-bold text-ink mt-0.5">
                        {{ summary.total_loans }} Kali
                    </p>
                </AppCard>

                <AppCard class="p-3 text-center">
                    <span class="text-[11px] text-ink-muted">Koleksi Pustaka</span>
                    <p class="text-lg font-bold text-ink mt-0.5">
                        {{ summary.total_available_books }} Judul
                    </p>
                </AppCard>
            </div>

            <!-- Tab Navigasi -->
            <Tabs v-model="activeTab" :tabs="tabs" variant="pills" />

            <!-- ================= TAB 1: KATALOG & E-BOOK ================= -->
            <div v-show="activeTab === 'katalog'" class="space-y-4">
                <!-- Bar Pencarian & Filter Kategori -->
                <div class="bg-surface rounded-card border border-line p-3.5 shadow-card flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <AppInput
                            v-model="searchInput"
                            placeholder="Cari judul buku, penulis, atau penerbit..."
                            @keydown.enter="applyFilter"
                        />
                    </div>
                    <div class="w-full sm:w-56">
                        <AppSelect
                            v-model="categoryFilter"
                            :options="categoryOptions"
                            @change="applyFilter"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <AppButton size="sm" variant="primary" @click="applyFilter">
                            Cari
                        </AppButton>
                        <AppButton size="sm" variant="secondary" @click="resetFilter">
                            Reset
                        </AppButton>
                    </div>
                </div>

                <!-- Grid Buku -->
                <div v-if="books.length === 0">
                    <EmptyState
                        title="Buku Tidak Ditemukan"
                        description="Tidak ada koleksi buku yang sesuai dengan kriteria pencarian Anda."
                    />
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <AppCard
                        v-for="book in books"
                        :key="book.id"
                        class="p-4 flex flex-col justify-between gap-3 relative overflow-hidden"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2 py-0.5 rounded-control bg-brand-50 border border-brand-200 text-brand-800 text-[10px] font-bold">
                                    {{ book.category }}
                                </span>
                                <span
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    :class="book.is_available ? 'bg-status-success-bg text-status-success-fg' : 'bg-surface-sunken text-ink-muted'"
                                >
                                    {{ book.is_available ? `Stok: ${book.physical_stock}` : 'Habis Dipinjam' }}
                                </span>
                            </div>

                            <div class="flex items-start gap-3 mt-3">
                                <div class="w-12 h-16 rounded bg-surface-sunken border border-line flex items-center justify-center text-2xl shrink-0">
                                    {{ book.cover_photo && book.cover_photo.length < 5 ? book.cover_photo : '📖' }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-ink line-clamp-2 leading-snug">{{ book.title }}</h4>
                                    <p class="text-[11px] text-ink-muted mt-0.5 truncate">Penulis: {{ book.author || '-' }}</p>
                                    <p class="text-[10px] text-ink-muted truncate">Penerbit: {{ book.publisher || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-line flex items-center justify-between text-xs">
                            <span class="text-ink-muted text-[11px] font-mono">
                                {{ book.isbn ? `ISBN: ${book.isbn}` : 'Buku Fisik' }}
                            </span>

                            <a
                                v-if="book.has_ebook && book.read_url"
                                :href="book.read_url"
                                target="_blank"
                                class="inline-flex items-center gap-1 font-bold text-brand-600 hover:text-brand-700 hover:underline"
                            >
                                <span>📱 Baca e-Book</span>
                            </a>
                            <span v-else class="text-[11px] text-ink-muted italic">
                                Tersedia di Perpustakaan
                            </span>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- ================= TAB 2: RIWAYAT PEMINJAMAN ================= -->
            <div v-show="activeTab === 'pinjaman'" class="space-y-4">
                <div v-if="loans.length === 0">
                    <EmptyState
                        title="Belum Ada Peminjaman Buku"
                        description="Anda belum memiliki riwayat peminjaman buku fisik di perpustakaan sekolah."
                    />
                </div>
                <div v-else class="space-y-3">
                    <AppCard
                        v-for="loan in loans"
                        :key="loan.id"
                        class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-ink">{{ loan.book_title }}</h4>
                                <StatusBadge
                                    :variant="loan.status === 'Dikembalikan' ? 'success' : (loan.is_overdue ? 'danger' : 'warning')"
                                >
                                    {{ loan.status }}
                                </StatusBadge>
                            </div>
                            <p class="text-xs text-ink-muted mt-1">
                                Dipinjam: {{ loan.loan_date }} &bull; Batas Pengembalian: <strong>{{ loan.due_date }}</strong>
                            </p>
                            <p v-if="loan.return_date" class="text-[11px] text-status-success-fg mt-0.5">
                                ✓ Dikembalikan pada {{ loan.return_date }}
                            </p>
                            <p v-else-if="loan.is_overdue" class="text-[11px] text-status-danger-fg font-bold mt-0.5">
                                ⚠ Masa peminjaman telah melewati batas waktu, mohon segera dikembalikan ke pustakawan.
                            </p>
                        </div>

                        <div class="text-xs text-ink-muted self-start sm:self-auto shrink-0 font-medium">
                            {{ loan.category }}
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>

