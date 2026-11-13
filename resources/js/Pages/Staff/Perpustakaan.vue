<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    books: { type: Array, required: true },
    loans: { type: Array, required: true },
    eligibleBorrowers: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
});

const search = ref(props.filters.search || '');
const loanModalOpen = ref(false);
const selectedBook = ref(null);

const loanForm = useForm({
    book_id: '',
    borrower_id: '',
    loan_duration_days: 7,
    notes: '',
});

const applySearch = () => {
    router.get(route('staf.perpustakaan'), { search: search.value }, { preserveState: true });
};

const openLoanModal = (book) => {
    selectedBook.value = book;
    loanForm.book_id = book.id;
    loanForm.borrower_id = props.eligibleBorrowers[0]?.id || '';
    loanForm.loan_duration_days = 7;
    loanForm.notes = '';
    loanModalOpen.value = true;
};

const submitLoan = () => {
    loanForm.post(route('staf.perpustakaan.borrow'), {
        preserveScroll: true,
        onSuccess: () => {
            loanModalOpen.value = false;
        },
    });
};

const processReturn = (loanId) => {
    if (!confirm('Proses pengembalian buku ini sekarang? Stok fisik buku akan bertambah kembali.')) {
        return;
    }
    router.post(route('staf.perpustakaan.return', loanId), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Manajemen Perpustakaan & Sirkulasi" />

    <StaffLayout>
        <template #header-title>
            <h1 class="text-2xl font-black text-slate-800">Manajemen Perpustakaan &amp; Sirkulasi</h1>
        </template>
        <template #header-subtitle>
            <p class="text-slate-500 font-medium">Pencatatan peminjaman, pengembalian buku nyata, dan monitoring jatuh tempo</p>
        </template>

        <div class="space-y-6">
            <!-- Statistik Perpustakaan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Judul Buku</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ stats.total_titles }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Total Eksemplar Fisik</p>
                    <p class="text-2xl font-black text-emerald-700 mt-1">{{ stats.total_stock }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Buku Sedang Dipinjam</p>
                    <p class="text-2xl font-black text-indigo-700 mt-1">{{ stats.active_loans }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Terlambat Kembali</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">{{ stats.overdue_loans }}</p>
                </div>
            </div>

            <!-- Bagian 1: Sirkulasi Peminjaman Aktif -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-800">Sirkulasi Peminjaman Buku</h2>
                        <p class="text-xs text-slate-500">Daftar buku yang sedang dipinjam atau telah dikembalikan</p>
                    </div>
                </div>

                <div v-if="loans.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat peminjaman buku.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Judul Buku</th>
                                <th class="px-6 py-3">Peminjam</th>
                                <th class="px-6 py-3">Tgl Pinjam</th>
                                <th class="px-6 py-3">Jatuh Tempo</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="loan in loans" :key="loan.id" class="hover:bg-slate-50/70">
                                <td class="px-6 py-3.5 font-bold text-slate-800">{{ loan.book_title }}</td>
                                <td class="px-6 py-3.5">
                                    <p class="font-semibold text-slate-800">{{ loan.borrower_name }}</p>
                                    <p class="text-xs text-slate-400">{{ loan.borrower_email }}</p>
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-600">{{ loan.loan_date }}</td>
                                <td class="px-6 py-3.5 text-xs font-semibold" :class="loan.is_overdue ? 'text-rose-600 font-bold' : 'text-slate-600'">
                                    {{ loan.due_date }}
                                    <span v-if="loan.is_overdue" class="block text-[10px] text-rose-500">Lewat jatuh tempo</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                        :class="loan.status === 'Dikembalikan' ? 'bg-emerald-50 text-emerald-700' : (loan.is_overdue ? 'bg-rose-50 text-rose-700' : 'bg-indigo-50 text-indigo-700')"
                                    >
                                        {{ loan.status === 'Dipinjam' && loan.is_overdue ? 'Terlambat' : loan.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <button
                                        v-if="loan.status === 'Dipinjam'"
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm"
                                        @click="processReturn(loan.id)"
                                    >
                                        Kembalikan Buku
                                    </button>
                                    <span v-else class="text-xs text-slate-400">Selesai ({{ loan.return_date }})</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bagian 2: Katalog Buku & Pinjamkan -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-extrabold text-slate-800">Katalog Buku Perpustakaan</h2>
                        <p class="text-xs text-slate-500">Cari buku dan proses peminjaman langsung ke siswa/guru/staf</p>
                    </div>
                    <form class="flex gap-2 max-w-sm w-full" @submit.prevent="applySearch">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul buku atau pengarang..."
                            class="flex-1 rounded-xl border-slate-300 text-xs"
                        />
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="book in books"
                        :key="book.id"
                        class="p-4 rounded-2xl border border-slate-200 flex flex-col justify-between gap-3 hover:border-indigo-300 transition"
                    >
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                {{ book.category }}
                            </span>
                            <h3 class="font-bold text-sm text-slate-800 mt-2">{{ book.title }}</h3>
                            <p class="text-xs text-slate-500">Penulis: {{ book.author }} &middot; ISBN: {{ book.isbn }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                            <span class="text-xs font-bold" :class="book.physical_stock > 0 ? 'text-emerald-700' : 'text-rose-600'">
                                Stok: {{ book.physical_stock }} eksemplar
                            </span>
                            <button
                                type="button"
                                :disabled="book.physical_stock <= 0"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
                                :class="book.physical_stock > 0 ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                @click="openLoanModal(book)"
                            >
                                + Pinjamkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Peminjaman Buku -->
        <div v-if="loanModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <form class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl" @submit.prevent="submitLoan">
                <h3 class="text-lg font-black text-slate-800">Form Peminjaman Buku</h3>
                <p class="text-xs text-slate-500">
                    Buku: <strong class="text-slate-900">{{ selectedBook?.title }}</strong> (Sisa stok: {{ selectedBook?.physical_stock }})
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Peminjam (Siswa / Guru / Staf)</label>
                    <select v-model="loanForm.borrower_id" class="w-full rounded-xl border-slate-300 text-sm" required>
                        <option v-for="user in eligibleBorrowers" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ user.email }})
                        </option>
                    </select>
                    <p v-if="loanForm.errors.borrower_id" class="text-xs text-rose-600 mt-1">{{ loanForm.errors.borrower_id }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Durasi Peminjaman (Hari)</label>
                    <input v-model.number="loanForm.loan_duration_days" type="number" min="1" max="30" class="w-full rounded-xl border-slate-300 text-sm" required />
                    <p v-if="loanForm.errors.loan_duration_days" class="text-xs text-rose-600 mt-1">{{ loanForm.errors.loan_duration_days }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                    <input v-model="loanForm.notes" type="text" placeholder="Kondisi buku baik, dsb." class="w-full rounded-xl border-slate-300 text-sm" />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100" @click="loanModalOpen = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="loanForm.processing" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white">
                        Catat Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </StaffLayout>
</template>
