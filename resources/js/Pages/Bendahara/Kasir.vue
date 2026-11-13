<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppRadio from '@/Components/ui/AppRadio.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import FormField from '@/Components/ui/FormField.vue';
import FileInput from '@/Components/ui/FileInput.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import ReceiptPreview from './Partials/ReceiptPreview.vue';

const props = defineProps({
    students: { type: Array, default: () => [] },
    pendingPayments: { type: Array, default: () => [] },
    recentPayments: { type: Array, default: () => [] },
    initialStudentId: { type: String, default: null },
});

const { confirm } = useConfirm();
const toast = useToast();

const activeTab = ref('kasir');
const searchQuery = ref('');
const selectedStudentId = ref(props.initialStudentId || props.students[0]?.id || '');

const filteredStudents = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.students;
    return props.students.filter((s) =>
        s.name.toLowerCase().includes(q) ||
        s.nisn.toLowerCase().includes(q) ||
        s.class.toLowerCase().includes(q)
    );
});

const studentOptions = computed(() => {
    return [
        { value: '', label: '-- Pilih Siswa --' },
        ...filteredStudents.value.map((s) => ({
            value: s.id,
            label: `${s.name} (${s.class}) - NISN: ${s.nisn}`,
        })),
    ];
});

const selectedStudent = computed(() => {
    return props.students.find((s) => s.id === selectedStudentId.value) || null;
});

const unpaidBills = computed(() => {
    return (selectedStudent.value?.bills || []).filter((b) => b.status !== 'Lunas' && b.remaining > 0);
});

const billOptions = computed(() => {
    return [
        { value: '', label: '-- Pilih Tagihan Belum Lunas --' },
        ...unpaidBills.value.map((bill) => ({
            value: bill.id,
            label: `${bill.name} ${bill.period ? `(${bill.period})` : ''} — Sisa: Rp ${Number(bill.remaining).toLocaleString('id-ID')}`,
        })),
    ];
});

const form = useForm({
    bill_id: '',
    amount_paid: '',
    payment_method: 'Tunai',
    payment_date: new Date().toISOString().slice(0, 10),
    receipt_photo: null,
    is_pending_verification: false,
});

const selectedBill = computed(() => {
    return unpaidBills.value.find((b) => b.id === form.bill_id) || null;
});

watch(selectedStudent, (newStudent) => {
    const firstBill = (newStudent?.bills || []).find((b) => b.status !== 'Lunas' && b.remaining > 0);
    form.bill_id = firstBill?.id || '';
    form.amount_paid = firstBill ? String(Math.round(firstBill.remaining)) : '';
    form.clearErrors();
}, { immediate: true });

watch(() => form.bill_id, (newBillId) => {
    const bill = unpaidBills.value.find((b) => b.id === newBillId);
    if (bill) {
        form.amount_paid = String(Math.round(bill.remaining));
    }
});

onMounted(() => {
    if (props.initialStudentId) {
        selectedStudentId.value = props.initialStudentId;
    }
});

const numberToIndonesianWords = (num) => {
    const satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    if (num < 12) return satuan[num];
    if (num < 20) return numberToIndonesianWords(num - 10) + ' Belas';
    if (num < 100) return numberToIndonesianWords(Math.floor(num / 10)) + ' Puluh' + (num % 10 ? ' ' + numberToIndonesianWords(num % 10) : '');
    if (num < 200) return 'Seratus' + (num - 100 ? ' ' + numberToIndonesianWords(num - 100) : '');
    if (num < 1000) return numberToIndonesianWords(Math.floor(num / 100)) + ' Ratus' + (num % 100 ? ' ' + numberToIndonesianWords(num % 100) : '');
    if (num < 2000) return 'Seribu' + (num - 1000 ? ' ' + numberToIndonesianWords(num - 1000) : '');
    if (num < 1000000) return numberToIndonesianWords(Math.floor(num / 1000)) + ' Ribu' + (num % 1000 ? ' ' + numberToIndonesianWords(num % 1000) : '');
    if (num < 1000000000) return numberToIndonesianWords(Math.floor(num / 1000000)) + ' Juta' + (num % 1000000 ? ' ' + numberToIndonesianWords(num % 1000000) : '');
    return num.toLocaleString('id-ID');
};

const terbilang = computed(() => {
    const num = parseInt(form.amount_paid || '0', 10);
    if (!num || isNaN(num)) return '';
    return numberToIndonesianWords(num) + ' Rupiah';
});

const submitPayment = () => {
    form.post(route('bendahara.kasir.pay'), {
        preserveScroll: true,
        onSuccess: () => {
            form.receipt_photo = null;
            form.is_pending_verification = false;
            toast.success('Transaksi pembayaran kasir berhasil disimpan!');
        },
    });
};

const verifyPayment = async (paymentId, decision) => {
    const ok = await confirm({
        title: `${decision === 'Berhasil' ? 'Setujui' : 'Tolak'} Pembayaran Transfer`,
        message: `Apakah Anda yakin ingin memutuskan pembayaran ini sebagai "${decision}"?`,
        confirmLabel: decision === 'Berhasil' ? 'Ya, Setujui' : 'Tolak Pembayaran',
        variant: decision === 'Berhasil' ? 'primary' : 'danger',
    });
    if (ok) {
        router.post(route('bendahara.payments.verify', paymentId), { decision }, {
            preserveScroll: true,
            onSuccess: () => toast.success(`Pembayaran berhasil di-${decision.toLowerCase()}`),
        });
    }
};

const pendingColumns = [
    { key: 'student_name', label: 'Siswa' },
    { key: 'bill_name', label: 'Tagihan' },
    { key: 'payment_date', label: 'Tanggal' },
    { key: 'amount_paid', label: 'Nominal', class: 'text-right' },
    { key: 'receipt_url', label: 'Bukti Bayar' },
];

const recentColumns = [
    { key: 'trx_code', label: 'No. Transaksi' },
    { key: 'student_name', label: 'Siswa' },
    { key: 'bill_name', label: 'Tagihan' },
    { key: 'payment_method', label: 'Metode' },
    { key: 'status', label: 'Status', class: 'text-center' },
    { key: 'amount_paid', label: 'Nominal', class: 'text-right' },
];
</script>

<template>
    <Head title="Kasir Pembayaran" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Mesin Kasir & Verifikasi Pembayaran"
                subtitle="Penerimaan pembayaran SPP, verifikasi bukti transfer, dan pencetakan kuitansi resmi"
            />

            <!-- Tabs -->
            <div class="flex flex-wrap gap-2 border-b border-line pb-2">
                <AppButton
                    size="sm"
                    :variant="activeTab === 'kasir' ? 'primary' : 'secondary'"
                    @click="activeTab = 'kasir'"
                >
                    💳 Kasir Pembayaran
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'verifikasi' ? 'primary' : 'secondary'"
                    @click="activeTab = 'verifikasi'"
                >
                    <span>📑 Verifikasi Transfer</span>
                    <span v-if="pendingPayments.length > 0" class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full bg-status-danger-bg text-status-danger-fg font-black">
                        {{ pendingPayments.length }}
                    </span>
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'riwayat' ? 'primary' : 'secondary'"
                    @click="activeTab = 'riwayat'"
                >
                    📜 Riwayat Kuitansi
                </AppButton>
            </div>

            <!-- TAB 1: KASIR -->
            <div v-if="activeTab === 'kasir'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Side: Cari Siswa & Input Kasir -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Search Student Card -->
                    <AppCard>
                        <h3 class="text-base font-bold text-ink mb-3 flex items-center gap-2">
                            <span>🔍</span> Pilih Siswa Pembayar
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <SearchInput
                                v-model="searchQuery"
                                placeholder="Cari Nama Siswa, NISN, atau Kelas..."
                                autofocus
                            />
                            <AppSelect
                                v-model="selectedStudentId"
                                :options="studentOptions"
                            />
                        </div>

                        <div v-if="selectedStudent" class="p-4 border-2 border-brand-500 rounded-card bg-brand-50/50 flex flex-wrap items-center gap-4">
                            <Avatar :name="selectedStudent.name" size="lg" />
                            <div class="flex-1 min-w-[180px]">
                                <h4 class="text-base font-bold text-ink">{{ selectedStudent.name }}</h4>
                                <p class="text-xs text-ink-muted">NISN: {{ selectedStudent.nisn }} • Kelas: {{ selectedStudent.class }}</p>
                                <p class="text-xs text-brand-700 font-bold mt-1">
                                    Saldo Dompet: <MoneyText :amount="selectedStudent.wallet_balance" />
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-ink-muted uppercase">Tagihan Aktif</p>
                                <StatusBadge
                                    :variant="selectedStudent.unpaid_count > 0 ? 'danger' : 'success'"
                                    size="sm"
                                >
                                    {{ selectedStudent.unpaid_count > 0 ? `${selectedStudent.unpaid_count} Tagihan Belum Lunas` : 'Lunas Semua' }}
                                </StatusBadge>
                            </div>
                        </div>
                    </AppCard>

                    <!-- Input Payment Form -->
                    <AppCard>
                        <form @submit.prevent="submitPayment" class="space-y-4">
                            <h3 class="text-base font-bold text-ink pb-2 border-b border-line">
                                Rincian Pembayaran
                            </h3>

                            <FormField label="Pilih Tagihan Siswa" :error="form.errors.bill_id" required>
                                <AppSelect
                                    v-model="form.bill_id"
                                    :options="billOptions"
                                    required
                                />
                                <p v-if="selectedStudent && unpaidBills.length === 0" class="text-xs text-status-success-fg font-semibold mt-1">
                                    Siswa ini tidak memiliki tagihan yang belum lunas.
                                </p>
                            </FormField>

                            <FormField label="Nominal Pembayaran (Rp)" :error="form.errors.amount_paid" required>
                                <AppInput
                                    v-model="form.amount_paid"
                                    type="number"
                                    min="1"
                                    placeholder="Contoh: 350000"
                                    class="text-lg font-black font-numeric"
                                    required
                                />
                                <p v-if="terbilang" class="mt-1 text-brand-700 font-medium italic text-xs">
                                    Terbilang: {{ terbilang }}
                                </p>
                            </FormField>

                            <FormField label="Metode Pembayaran" :error="form.errors.payment_method" required>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="p-2.5 rounded-control border border-line bg-surface-raised flex items-center gap-2">
                                        <AppRadio v-model="form.payment_method" value="Tunai" name="pay-method" label="Tunai" />
                                    </div>
                                    <div class="p-2.5 rounded-control border border-line bg-surface-raised flex items-center gap-2">
                                        <AppRadio v-model="form.payment_method" value="Transfer Bank" name="pay-method" label="Transfer" />
                                    </div>
                                    <div class="p-2.5 rounded-control border border-line bg-surface-raised flex items-center gap-2">
                                        <AppRadio v-model="form.payment_method" value="Dompet Digital" name="pay-method" label="Dompet" />
                                    </div>
                                </div>
                            </FormField>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <FormField label="Tanggal Bayar" :error="form.errors.payment_date" required>
                                    <AppInput
                                        v-model="form.payment_date"
                                        type="date"
                                        required
                                    />
                                </FormField>
                                <FormField label="Bukti Transfer / Berkas (Opsional)" :error="form.errors.receipt_photo">
                                    <FileInput
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        @update:modelValue="form.receipt_photo = $event"
                                    />
                                </FormField>
                            </div>

                            <div v-if="form.payment_method === 'Transfer Bank'" class="pt-1">
                                <AppCheckbox
                                    v-model="form.is_pending_verification"
                                    label="Simpan sebagai 'Menunggu Verifikasi' untuk diperiksa terpisah"
                                />
                            </div>

                            <div class="pt-2">
                                <AppButton
                                    type="submit"
                                    variant="primary"
                                    size="lg"
                                    class="w-full shadow-card"
                                    :loading="form.processing"
                                    :disabled="!form.bill_id"
                                >
                                    💾 SIMPAN & CATAT PEMBAYARAN KASIR
                                </AppButton>
                            </div>
                        </form>
                    </AppCard>
                </div>

                <!-- Right Side: Live Thermal Receipt -->
                <div class="lg:col-span-1">
                    <ReceiptPreview
                        :student="selectedStudent"
                        :bill="selectedBill"
                        :amount="form.amount_paid"
                        :payment-method="form.payment_method"
                        :payment-date="form.payment_date"
                    />
                </div>
            </div>

            <!-- TAB 2: VERIFIKASI TRANSFER -->
            <div v-if="activeTab === 'verifikasi'" class="space-y-4">
                <DataTable
                    :columns="pendingColumns"
                    :items="pendingPayments"
                    empty-title="Tidak Ada Pembayaran Menunggu"
                    empty-message="Seluruh bukti transfer telah diverifikasi bagian keuangan."
                >
                    <template #cell(student_name)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.student_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.class }} • NISN: {{ item.nisn }}</p>
                        </div>
                    </template>

                    <template #cell(bill_name)="{ item }">
                        <div>
                            <p class="font-semibold text-ink text-xs">{{ item.bill_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.period || '-' }}</p>
                        </div>
                    </template>

                    <template #cell(payment_date)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(amount_paid)="{ value }">
                        <span class="font-black text-xs text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #cell(receipt_url)="{ value }">
                        <a
                            v-if="value"
                            :href="value"
                            target="_blank"
                            class="text-xs font-bold text-brand-600 hover:underline"
                        >
                            📎 Unduh Bukti
                        </a>
                        <span v-else class="text-xs text-ink-muted">Tanpa berkas</span>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5">
                            <AppButton
                                variant="primary"
                                size="sm"
                                @click="verifyPayment(item.id, 'Berhasil')"
                            >
                                Setujui
                            </AppButton>
                            <AppButton
                                variant="danger"
                                size="sm"
                                @click="verifyPayment(item.id, 'Ditolak')"
                            >
                                Tolak
                            </AppButton>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- TAB 3: RIWAYAT KWITANSI -->
            <div v-if="activeTab === 'riwayat'" class="space-y-4">
                <DataTable
                    :columns="recentColumns"
                    :items="recentPayments"
                    empty-title="Belum Ada Riwayat Pembayaran"
                    empty-message="Catatan pembayaran kasir akan muncul di sini."
                >
                    <template #cell(trx_code)="{ value }">
                        <span class="font-mono text-xs font-bold text-brand-700">{{ value }}</span>
                    </template>

                    <template #cell(student_name)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.student_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.class }}</p>
                        </div>
                    </template>

                    <template #cell(bill_name)="{ value }">
                        <span class="text-xs font-semibold text-ink">{{ value }}</span>
                    </template>

                    <template #cell(payment_method)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(status)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge
                                :variant="value === 'Berhasil' ? 'success' : (value === 'Menunggu Verifikasi' ? 'warning' : 'danger')"
                                size="sm"
                            >
                                {{ value }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #cell(amount_paid)="{ value }">
                        <span class="font-black text-xs text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>
                </DataTable>
            </div>
        </div>
    </PortalShell>
</template>
