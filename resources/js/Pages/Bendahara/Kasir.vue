<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    students: { type: Array, default: () => [] },
    pendingPayments: { type: Array, default: () => [] },
    recentPayments: { type: Array, default: () => [] },
    initialStudentId: { type: String, default: null },
});

const page = usePage();
const activeTab = ref('kasir');
const searchQuery = ref('');
const selectedStudentId = ref(props.initialStudentId || props.students[0]?.id || '');
const receiptInput = ref(null);

const filteredStudents = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) {
        return props.students;
    }

    return props.students.filter((s) =>
        s.name.toLowerCase().includes(q) ||
        s.nisn.toLowerCase().includes(q) ||
        s.class.toLowerCase().includes(q)
    );
});

const selectedStudent = computed(() => {
    return props.students.find((s) => s.id === selectedStudentId.value) || null;
});

const unpaidBills = computed(() => {
    return (selectedStudent.value?.bills || []).filter((b) => b.status !== 'Lunas' && b.remaining > 0);
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

const formattedNominal = computed({
    get() {
        if (!form.amount_paid) {
            return '';
        }
        const val = form.amount_paid.toString().replace(/[^0-9]/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },
    set(val) {
        form.amount_paid = val.replace(/[^0-9]/g, '');
    },
});

const numberToIndonesianWords = (num) => {
    const satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    if (num < 12) {
        return satuan[num];
    }
    if (num < 20) {
        return numberToIndonesianWords(num - 10) + ' Belas';
    }
    if (num < 100) {
        return numberToIndonesianWords(Math.floor(num / 10)) + ' Puluh' + (num % 10 ? ' ' + numberToIndonesianWords(num % 10) : '');
    }
    if (num < 200) {
        return 'Seratus' + (num - 100 ? ' ' + numberToIndonesianWords(num - 100) : '');
    }
    if (num < 1000) {
        return numberToIndonesianWords(Math.floor(num / 100)) + ' Ratus' + (num % 100 ? ' ' + numberToIndonesianWords(num % 100) : '');
    }
    if (num < 2000) {
        return 'Seribu' + (num - 1000 ? ' ' + numberToIndonesianWords(num - 1000) : '');
    }
    if (num < 1000000) {
        return numberToIndonesianWords(Math.floor(num / 1000)) + ' Ribu' + (num % 1000 ? ' ' + numberToIndonesianWords(num % 1000) : '');
    }
    if (num < 1000000000) {
        return numberToIndonesianWords(Math.floor(num / 1000000)) + ' Juta' + (num % 1000000 ? ' ' + numberToIndonesianWords(num % 1000000) : '');
    }
    return num.toLocaleString('id-ID');
};

const terbilang = computed(() => {
    const num = parseInt(form.amount_paid || '0', 10);
    if (!num || isNaN(num)) {
        return '';
    }
    return numberToIndonesianWords(num) + ' Rupiah';
});

const submitPayment = () => {
    form.post(route('bendahara.kasir.pay'), {
        preserveScroll: true,
        onSuccess: () => {
            form.receipt_photo = null;
            form.is_pending_verification = false;
            if (receiptInput.value) {
                receiptInput.value.value = '';
            }
        },
    });
};

const verifyPayment = (paymentId, decision) => {
    router.post(route('bendahara.payments.verify', paymentId), { decision }, {
        preserveScroll: true,
    });
};

const printReceipt = () => {
    window.print();
};

const formatCurrency = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <Head title="Kasir Pembayaran" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Mesin Kasir & Verifikasi Pembayaran</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Penerimaan pembayaran SPP, verifikasi bukti transfer, dan pencetakan kwitansi</p></template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
                ✅ {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-semibold">
                ⚠️ {{ page.props.flash.error }}
            </div>

            <!-- Tabs -->
            <div class="flex gap-4 border-b border-slate-200">
                <button @click="activeTab = 'kasir'"
                        class="px-6 py-3.5 font-bold text-sm transition relative"
                        :class="activeTab === 'kasir' ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600'">
                    Kasir Pembayaran
                    <div v-if="activeTab === 'kasir'" class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 rounded-t-full"></div>
                </button>
                <button @click="activeTab = 'verifikasi'"
                        class="px-6 py-3.5 font-bold text-sm transition relative flex items-center gap-2"
                        :class="activeTab === 'verifikasi' ? 'text-blue-600' : 'text-slate-500 hover:text-blue-600'">
                    <span>Verifikasi Transfer</span>
                    <span v-if="pendingPayments.length > 0" class="px-2 py-0.5 text-xs rounded-full bg-rose-500 text-white">{{ pendingPayments.length }}</span>
                    <div v-if="activeTab === 'verifikasi'" class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 rounded-t-full"></div>
                </button>
                <button @click="activeTab = 'riwayat'"
                        class="px-6 py-3.5 font-bold text-sm transition relative"
                        :class="activeTab === 'riwayat' ? 'text-slate-800' : 'text-slate-500 hover:text-slate-800'">
                    Riwayat Kwitansi
                    <div v-if="activeTab === 'riwayat'" class="absolute bottom-0 left-0 w-full h-1 bg-slate-700 rounded-t-full"></div>
                </button>
            </div>

            <!-- TAB 1: KASIR -->
            <div v-if="activeTab === 'kasir'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Side: Cari Siswa & Input Kasir -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Search Student -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Pilih Siswa Pembayar
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input v-model="searchQuery" type="text" placeholder="Ketik Nama Siswa, NISN, atau Kelas..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none" />
                            <select v-model="selectedStudentId"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                                <option value="">-- Pilih Siswa --</option>
                                <option v-for="s in filteredStudents" :key="s.id" :value="s.id">
                                    {{ s.name }} ({{ s.class }}) - {{ s.nisn }}
                                </option>
                            </select>
                        </div>

                        <div v-if="selectedStudent" class="p-4 border-2 border-emerald-500 rounded-xl bg-emerald-50/50 flex flex-wrap items-center gap-4">
                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(selectedStudent.name)}&background=10b981&color=fff`" class="w-14 h-14 rounded-xl shadow-sm border-2 border-white" />
                            <div class="flex-1 min-w-[180px]">
                                <h4 class="text-lg font-black text-slate-800">{{ selectedStudent.name }}</h4>
                                <p class="text-slate-600 font-medium text-xs">NISN: {{ selectedStudent.nisn }} • Kelas: {{ selectedStudent.class }}</p>
                                <p class="text-emerald-700 font-bold text-xs mt-1">Saldo Dompet Digital: {{ formatCurrency(selectedStudent.wallet_balance) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-500 uppercase">Tagihan Aktif</p>
                                <p :class="selectedStudent.unpaid_count > 0 ? 'text-rose-600' : 'text-emerald-600'" class="font-black text-base">
                                    {{ selectedStudent.unpaid_count > 0 ? `${selectedStudent.unpaid_count} Tagihan Belum Lunas` : 'Lunas Semua' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Input Payment -->
                    <form @submit.prevent="submitPayment" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
                        <h3 class="text-lg font-black text-slate-800">Detail Pembayaran</h3>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Pilih Tagihan Siswa</label>
                            <select v-model="form.bill_id" required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-bold focus:border-emerald-500 outline-none">
                                <option value="">-- Pilih Tagihan Belum Lunas --</option>
                                <option v-for="bill in unpaidBills" :key="bill.id" :value="bill.id">
                                    {{ bill.name }} {{ bill.period ? `(${bill.period})` : '' }} — Sisa: {{ formatCurrency(bill.remaining) }}
                                </option>
                            </select>
                            <p v-if="form.errors.bill_id" class="text-xs text-rose-600 mt-1">{{ form.errors.bill_id }}</p>
                            <p v-if="selectedStudent && unpaidBills.length === 0" class="text-xs text-emerald-600 font-semibold mt-1">
                                Siswa ini tidak memiliki tagihan yang belum lunas. Terbitkan tagihan di menu Manajemen SPP jika diperlukan.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Nominal Pembayaran</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-black text-xl">Rp</span>
                                </div>
                                <input v-model="formattedNominal" type="text" placeholder="0" required
                                       class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-2xl font-black text-slate-800 focus:border-emerald-500 outline-none text-right" />
                            </div>
                            <p v-if="form.errors.amount_paid" class="text-xs text-rose-600 mt-1">{{ form.errors.amount_paid }}</p>
                            <p v-if="terbilang" class="mt-2 text-emerald-600 font-bold italic text-xs text-right">
                                Terbilang: {{ terbilang }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input v-model="form.payment_method" value="Tunai" type="radio" class="peer sr-only" />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 text-sm font-bold transition">
                                        Tunai
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input v-model="form.payment_method" value="Transfer Bank" type="radio" class="peer sr-only" />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 text-slate-500 text-sm font-bold transition">
                                        Transfer Bank
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input v-model="form.payment_method" value="Dompet Digital" type="radio" class="peer sr-only" />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 text-slate-500 text-sm font-bold transition">
                                        Dompet Digital
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.payment_method" class="text-xs text-rose-600 mt-1">{{ form.errors.payment_method }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Bayar</label>
                                <input v-model="form.payment_date" type="date" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Bukti Transfer / Berkas (Privat, Opsional)</label>
                                <input ref="receiptInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @input="form.receipt_photo = $event.target.files[0] ?? null"
                                       class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl bg-slate-50" />
                                <p v-if="form.errors.receipt_photo" class="text-xs text-rose-600 mt-1">{{ form.errors.receipt_photo }}</p>
                            </div>
                        </div>

                        <label v-if="form.payment_method === 'Transfer Bank'" class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                            <input v-model="form.is_pending_verification" type="checkbox" class="rounded border-slate-300 text-blue-600" />
                            <span>Simpan sebagai "Menunggu Verifikasi" untuk diperiksa terpisah</span>
                        </label>

                        <button type="submit" :disabled="form.processing || !form.bill_id"
                                class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black rounded-xl text-sm shadow-lg shadow-emerald-600/20 transition">
                            {{ form.processing ? 'Memproses Transaksi...' : 'SIMPAN & CATAT PEMBAYARAN' }}
                        </button>
                    </form>
                </div>

                <!-- Right Side: Live Thermal Receipt -->
                <div class="lg:col-span-1">
                    <div class="bg-slate-800 rounded-2xl shadow-xl overflow-hidden sticky top-24 flex flex-col">
                        <div class="flex-1 p-6 bg-slate-50 m-4 rounded-xl font-mono text-xs shadow-inner flex flex-col">
                            <div class="text-center mb-5">
                                <h4 class="font-bold text-sm text-slate-800">SD MUHAMMADIYAH AL MUJAHIDIN</h4>
                                <p class="text-slate-500">Wonosari • Gunungkidul</p>
                                <p class="text-slate-500 border-b border-dashed border-slate-300 pb-3 mb-3">Kwitansi Pembayaran Resmi</p>
                            </div>

                            <div class="space-y-2 text-slate-700 flex-1">
                                <div class="flex justify-between"><span>Tanggal:</span><span>{{ form.payment_date }}</span></div>
                                <div class="flex justify-between"><span>Kasir:</span><span>{{ page.props.auth?.user?.name }}</span></div>
                                <div class="flex justify-between"><span>Siswa:</span><span class="font-bold">{{ selectedStudent?.name || '-' }}</span></div>
                                <div class="flex justify-between"><span>Metode:</span><span>{{ form.payment_method }}</span></div>

                                <div class="border-t border-dashed border-slate-300 my-4 pt-3 space-y-1">
                                    <div class="flex justify-between font-bold">
                                        <span>{{ selectedBill ? selectedBill.name : 'Item Tagihan' }}</span>
                                        <span>Rp {{ formattedNominal || '0' }}</span>
                                    </div>
                                    <p v-if="selectedBill?.period" class="text-[11px] text-slate-500">Periode: {{ selectedBill.period }}</p>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-300 pt-3 mt-4">
                                <div class="flex justify-between text-base font-black text-slate-800">
                                    <span>TOTAL</span>
                                    <span>Rp {{ formattedNominal || '0' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <button type="button" @click="printReceipt" class="w-full py-3 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl text-sm transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                CETAK KWITANSI
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: VERIFIKASI TRANSFER -->
            <div v-if="activeTab === 'verifikasi'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Daftar Pembayaran Menunggu Verifikasi</h3>
                    <p class="text-xs text-slate-500">Periksa bukti transfer pada penyimpanan privat sebelum menyetujui</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Tagihan</th>
                                <th class="py-4 px-6">Tanggal</th>
                                <th class="py-4 px-6 text-right">Nominal</th>
                                <th class="py-4 px-6">Bukti</th>
                                <th class="py-4 px-6 text-right">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pay in pendingPayments" :key="pay.id" class="hover:bg-slate-50">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ pay.student_name }}</p>
                                    <p class="text-xs text-slate-500">{{ pay.class }} • {{ pay.nisn }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-semibold text-slate-800">{{ pay.bill_name }}</p>
                                    <p class="text-xs text-slate-500">{{ pay.period || '-' }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-500">{{ pay.payment_date }}</td>
                                <td class="py-4 px-6 text-right font-black text-slate-800">{{ formatCurrency(pay.amount_paid) }}</td>
                                <td class="py-4 px-6">
                                    <a v-if="pay.receipt_url" :href="pay.receipt_url" class="text-xs font-bold text-blue-600 hover:underline">
                                        Unduh Bukti
                                    </a>
                                    <span v-else class="text-xs text-slate-400">Tanpa berkas</span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="verifyPayment(pay.id, 'Berhasil')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                                            Setujui
                                        </button>
                                        <button @click="verifyPayment(pay.id, 'Ditolak')" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg">
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pendingPayments.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada pembayaran yang menunggu verifikasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: RIWAYAT KWITANSI -->
            <div v-if="activeTab === 'riwayat'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Riwayat Pembayaran Terakhir</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-4 px-6">No. Transaksi</th>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Tagihan</th>
                                <th class="py-4 px-6">Metode</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="trx in recentPayments" :key="trx.id" class="hover:bg-slate-50">
                                <td class="py-4 px-6 font-mono text-xs font-bold text-slate-700">{{ trx.trx_code }}</td>
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ trx.student_name }}</p>
                                    <p class="text-xs text-slate-500">{{ trx.class }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-700">{{ trx.bill_name }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ trx.payment_method }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': trx.status === 'Berhasil',
                                              'bg-amber-100 text-amber-700': trx.status === 'Menunggu Verifikasi',
                                              'bg-rose-100 text-rose-700': trx.status === 'Ditolak',
                                          }">
                                        {{ trx.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right font-black text-slate-800">{{ formatCurrency(trx.amount_paid) }}</td>
                            </tr>
                            <tr v-if="recentPayments.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada riwayat pembayaran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BendaharaLayout>
</template>
