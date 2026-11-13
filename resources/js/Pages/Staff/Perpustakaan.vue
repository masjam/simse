<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import FormField from '@/Components/ui/FormField.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import Modal from '@/Components/ui/Modal.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    books: { type: Array, required: true },
    loans: { type: Array, required: true },
    eligibleBorrowers: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
});

const { confirm } = useConfirm();
const toast = useToast();

const search = ref(props.filters.search || '');
const loanModalOpen = ref(false);
const selectedBook = ref(null);

const loanForm = useForm({
    book_id: '',
    borrower_id: '',
    loan_duration_days: 7,
    notes: '',
});

const borrowerOptions = computed(() => {
    return props.eligibleBorrowers.map((u) => ({
        value: u.id,
        label: `${u.name} (${u.email})`,
    }));
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
            toast.success('Peminjaman buku berhasil dicatat!');
        },
    });
};

const processReturn = async (loanId) => {
    const ok = await confirm({
        title: 'Proses Pengembalian Buku',
        message: 'Proses pengembalian buku ini sekarang? Stok fisik buku akan bertambah kembali secara otomatis.',
        confirmLabel: 'Ya, Kembalikan Buku',
        variant: 'primary',
    });

    if (ok) {
        router.post(route('staf.perpustakaan.return', loanId), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success('Buku berhasil dikembalikan ke perpustakaan.'),
        });
    }
};

const loanColumns = [
    { key: 'book_title', label: 'Judul Buku' },
    { key: 'borrower_name', label: 'Peminjam' },
    { key: 'loan_date', label: 'Tgl Pinjam' },
    { key: 'due_date', label: 'Jatuh Tempo' },
    { key: 'status', label: 'Status', class: 'text-center' },
];
</script>

<template>
    <Head title="Manajemen Perpustakaan & Sirkulasi" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Manajemen Perpustakaan & Sirkulasi"
                subtitle="Pencatatan sirkulasi peminjaman, pengembalian buku, dan monitoring jatuh tempo SD Muhammadiyah Al Mujahidin"
            />

            <!-- Statistik Perpustakaan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Judul Buku"
                    :value="stats.total_titles"
                    subtitle="Koleksi Terdaftar"
                    icon-bg="brand"
                >
                    <template #icon>📚</template>
                </StatCard>
                <StatCard
                    title="Eksemplar Fisik"
                    :value="stats.total_stock"
                    subtitle="Total Buku Fisik"
                    icon-bg="success"
                >
                    <template #icon>📦</template>
                </StatCard>
                <StatCard
                    title="Sedang Dipinjam"
                    :value="stats.active_loans"
                    subtitle="Buku di Siswa/Guru"
                    icon-bg="accent"
                >
                    <template #icon>📖</template>
                </StatCard>
                <StatCard
                    title="Terlambat Kembali"
                    :value="stats.overdue_loans"
                    subtitle="Melewati Jatuh Tempo"
                    :icon-bg="stats.overdue_loans > 0 ? 'danger' : 'neutral'"
                >
                    <template #icon>⚠️</template>
                </StatCard>
            </div>

            <!-- Bagian 1: Sirkulasi Peminjaman Aktif -->
            <div class="space-y-3">
                <h3 class="text-base font-bold text-ink px-1">Sirkulasi Peminjaman Buku</h3>
                <DataTable
                    :columns="loanColumns"
                    :items="loans"
                    empty-title="Belum Ada Sirkulasi"
                    empty-message="Saat ini belum ada data peminjaman buku perpustakaan."
                >
                    <template #cell(book_title)="{ value }">
                        <strong class="text-xs font-bold text-ink">{{ value }}</strong>
                    </template>

                    <template #cell(borrower_name)="{ item }">
                        <div>
                            <p class="text-xs font-bold text-ink">{{ item.borrower_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.borrower_email }}</p>
                        </div>
                    </template>

                    <template #cell(loan_date)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(due_date)="{ item }">
                        <div class="space-y-0.5">
                            <span
                                class="text-xs font-bold"
                                :class="item.is_overdue ? 'text-status-danger-fg' : 'text-ink'"
                            >
                                {{ item.due_date }}
                            </span>
                            <span v-if="item.is_overdue" class="block text-[10px] text-status-danger-fg font-semibold">
                                Lewat jatuh tempo
                            </span>
                        </div>
                    </template>

                    <template #cell(status)="{ item }">
                        <div class="flex justify-center">
                            <StatusBadge
                                :variant="item.status === 'Dikembalikan' ? 'success' : (item.is_overdue ? 'danger' : 'accent')"
                                size="sm"
                            >
                                {{ item.status === 'Dipinjam' && item.is_overdue ? 'Terlambat' : item.status }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #actions="{ item }">
                        <AppButton
                            v-if="item.status === 'Dipinjam'"
                            variant="primary"
                            size="sm"
                            @click="processReturn(item.id)"
                        >
                            Kembalikan Buku
                        </AppButton>
                        <span v-else class="text-xs text-ink-muted">
                            Selesai ({{ item.return_date }})
                        </span>
                    </template>
                </DataTable>
            </div>

            <!-- Bagian 2: Katalog Buku & Pinjamkan -->
            <div class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-card">
                    <div>
                        <h3 class="text-base font-bold text-ink">Katalog Buku Perpustakaan</h3>
                        <p class="text-xs text-ink-muted">Cari buku dan catat peminjaman ke siswa, guru, atau staf</p>
                    </div>
                    <form class="flex gap-2 max-w-sm w-full" @submit.prevent="applySearch">
                        <div class="flex-1">
                            <SearchInput
                                v-model="search"
                                placeholder="Cari judul buku atau pengarang..."
                            />
                        </div>
                        <AppButton type="submit" variant="secondary" size="sm">
                            Cari
                        </AppButton>
                    </form>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <AppCard
                        v-for="book in books"
                        :key="book.id"
                        class="flex flex-col justify-between gap-3"
                    >
                        <div class="space-y-1.5">
                            <StatusBadge variant="brand" size="sm">
                                {{ book.category }}
                            </StatusBadge>
                            <h4 class="font-bold text-sm text-ink leading-tight line-clamp-2">
                                {{ book.title }}
                            </h4>
                            <p class="text-xs text-ink-muted">Penulis: {{ book.author }} • ISBN: {{ book.isbn }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-line">
                            <span
                                class="text-xs font-bold font-numeric"
                                :class="book.physical_stock > 0 ? 'text-status-success-fg' : 'text-status-danger-fg'"
                            >
                                Stok: {{ book.physical_stock }} buku
                            </span>
                            <AppButton
                                variant="primary"
                                size="sm"
                                :disabled="book.physical_stock <= 0"
                                @click="openLoanModal(book)"
                            >
                                + Pinjamkan
                            </AppButton>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Modal Peminjaman Buku -->
            <Modal
                :show="loanModalOpen"
                title="Form Peminjaman Buku Fisik"
                max-width="md"
                @close="loanModalOpen = false"
            >
                <form @submit.prevent="submitLoan" class="p-6 space-y-4">
                    <div v-if="selectedBook" class="p-3 bg-surface-raised rounded-control border border-line text-xs">
                        <p class="font-bold text-ink">{{ selectedBook.title }}</p>
                        <p class="text-ink-muted">Sisa stok fisik: <strong class="text-ink">{{ selectedBook.physical_stock }} eksemplar</strong></p>
                    </div>

                    <FormField label="Pilih Peminjam (Siswa / Guru / Staf)" :error="loanForm.errors.borrower_id" required>
                        <AppSelect
                            v-model="loanForm.borrower_id"
                            :options="borrowerOptions"
                            required
                        />
                    </FormField>

                    <FormField label="Durasi Peminjaman (Hari)" :error="loanForm.errors.loan_duration_days" required>
                        <AppInput
                            v-model.number="loanForm.loan_duration_days"
                            type="number"
                            min="1"
                            max="30"
                            class="font-numeric"
                            required
                        />
                    </FormField>

                    <FormField label="Catatan Tambahan (Opsional)" :error="loanForm.errors.notes">
                        <AppInput
                            v-model="loanForm.notes"
                            placeholder="Contoh: Kondisi buku mulus, halaman lengkap"
                        />
                    </FormField>

                    <div class="flex justify-end gap-2 pt-3 border-t border-line">
                        <AppButton type="button" variant="ghost" size="sm" @click="loanModalOpen = false">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" size="sm" :loading="loanForm.processing">
                            Catat Peminjaman
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
