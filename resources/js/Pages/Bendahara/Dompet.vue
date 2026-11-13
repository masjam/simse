<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    students: { type: Array, default: () => [] },
    transactions: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            total_balance: 0,
            total_deposits: 0,
            total_withdrawals: 0,
            active_wallets: 0,
        }),
    },
});

const page = usePage();
const searchQuery = ref('');
const showTxModal = ref(false);
const showPinModal = ref(false);
const selectedStudent = ref(null);

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

const txForm = useForm({
    student_id: '',
    type: 'Setoran',
    amount: '',
    description: '',
});

const pinForm = useForm({
    pin: '',
});

const openTransactionModal = (student, defaultType = 'Setoran') => {
    selectedStudent.value = student;
    txForm.reset();
    txForm.clearErrors();
    txForm.student_id = student.id;
    txForm.type = defaultType;
    showTxModal.value = true;
};

const submitTransaction = () => {
    txForm.post(route('bendahara.dompet.transaction'), {
        preserveScroll: true,
        onSuccess: () => {
            showTxModal.value = false;
            txForm.reset();
        },
    });
};

const updateRfidCard = (student) => {
    const uid = window.prompt(`Masukkan UID Kartu RFID untuk ${student.name} (kosongkan untuk melepas):`, student.rfid_uid || '');
    if (uid === null) {
        return;
    }

    router.post(route('bendahara.dompet.rfid', student.id), {
        rfid_uid: uid.trim() || null,
    }, { preserveScroll: true });
};

const openPinModal = (student) => {
    selectedStudent.value = student;
    pinForm.reset();
    pinForm.clearErrors();
    showPinModal.value = true;
};

const submitPin = () => {
    if (!selectedStudent.value) {
        return;
    }

    pinForm.post(route('bendahara.dompet.pin', selectedStudent.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showPinModal.value = false;
            pinForm.reset();
        },
    });
};

const formatCurrency = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <Head title="Dompet Digital Siswa" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Tabungan & Dompet Digital Siswa</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Kelola setoran, penarikan, PIN transaksi kantin, dan mutasi saldo siswa</p></template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
                ✅ {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-semibold">
                ⚠️ {{ page.props.flash.error }}
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Saldo Mengendap</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ formatCurrency(summary.total_balance) }}</h3>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Setoran Masuk</p>
                    <h3 class="text-2xl font-black text-blue-600 mt-1">{{ formatCurrency(summary.total_deposits) }}</h3>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Penarikan & Belanja</p>
                    <h3 class="text-2xl font-black text-amber-600 mt-1">{{ formatCurrency(summary.total_withdrawals) }}</h3>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Dompet Aktif</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ summary.active_wallets }} Akun</h3>
                </div>
            </div>

            <!-- Student Wallets Table -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Rekening Tabungan / Dompet Siswa</h3>
                        <p class="text-xs text-slate-500">Pilih siswa untuk melakukan setoran, penarikan, atau pengaturan PIN</p>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, NISN, atau kelas..."
                        class="w-full sm:w-72 px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Kelas</th>
                                <th class="py-4 px-6">Status PIN</th>
                                <th class="py-4 px-6 text-right">Saldo Aktif</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ student.name }}</p>
                                    <p class="text-xs text-slate-500">NISN: {{ student.nisn }}</p>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-600">{{ student.class }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                          :class="student.has_pin ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                        {{ student.has_pin ? 'PIN Aktif' : 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right font-black text-slate-800">{{ formatCurrency(student.balance) }}</td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openTransactionModal(student, 'Setoran')" class="px-3 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                                            + Setor
                                        </button>
                                        <button @click="openTransactionModal(student, 'Penarikan')" class="px-3 py-1.5 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                            - Tarik
                                        </button>
                                        <button @click="openPinModal(student)" class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">
                                            Atur PIN
                                        </button>
                                        <button @click="updateRfidCard(student)" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                                            {{ student.rfid_uid ? 'RFID Aktif' : 'Tautkan RFID' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada siswa yang sesuai pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mutation History -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Riwayat Mutasi Dompet Digital</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-6">Referensi</th>
                                <th class="py-3.5 px-6">Siswa</th>
                                <th class="py-3.5 px-6">Jenis</th>
                                <th class="py-3.5 px-6">Keterangan</th>
                                <th class="py-3.5 px-6">Waktu</th>
                                <th class="py-3.5 px-6 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-50">
                                <td class="py-3.5 px-6 font-mono text-xs font-bold text-slate-600">{{ tx.reference_id }}</td>
                                <td class="py-3.5 px-6">
                                    <p class="font-bold text-slate-800">{{ tx.student_name }}</p>
                                    <p class="text-xs text-slate-500">{{ tx.class }}</p>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                          :class="tx.type === 'Setoran' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                        {{ tx.type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-slate-600">{{ tx.description }}</td>
                                <td class="py-3.5 px-6 text-slate-500 text-xs">{{ tx.date }}</td>
                                <td class="py-3.5 px-6 text-right font-black text-slate-800">{{ formatCurrency(tx.amount) }}</td>
                            </tr>
                            <tr v-if="transactions.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada mutasi transaksi dompet digital.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transaction Modal -->
            <Teleport to="body">
                <div v-if="showTxModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">Transaksi Dompet Digital</h3>
                            <button @click="showTxModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitTransaction" class="space-y-4">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="font-bold text-slate-800 text-sm">{{ selectedStudent?.name }}</p>
                                <p class="text-xs text-slate-500">Saldo saat ini: {{ formatCurrency(selectedStudent?.balance) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Transaksi</label>
                                <select v-model="txForm.type" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl">
                                    <option value="Setoran">Setoran (Top-Up)</option>
                                    <option value="Penarikan">Penarikan Tunai</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp)</label>
                                <input v-model="txForm.amount" type="number" min="100" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                <p v-if="txForm.errors.amount" class="text-xs text-rose-600 mt-1">{{ txForm.errors.amount }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                                <input v-model="txForm.description" type="text" placeholder="Contoh: Setoran tabungan pekanan" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showTxModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-sm">Batal</button>
                                <button type="submit" :disabled="txForm.processing" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm">
                                    {{ txForm.processing ? 'Memproses...' : 'Simpan Transaksi' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- PIN Modal -->
            <Teleport to="body">
                <div v-if="showPinModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">Atur PIN Dompet (6 Digit)</h3>
                            <button @click="showPinModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitPin" class="space-y-4">
                            <p class="text-xs text-slate-500">Siswa: <strong>{{ selectedStudent?.name }}</strong></p>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">PIN Baru (6 Angka)</label>
                                <input v-model="pinForm.pin" type="password" maxlength="6" pattern="[0-9]{6}" required placeholder="123456" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl tracking-widest text-center font-mono" />
                                <p v-if="pinForm.errors.pin" class="text-xs text-rose-600 mt-1">{{ pinForm.errors.pin }}</p>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showPinModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-sm">Batal</button>
                                <button type="submit" :disabled="pinForm.processing" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-sm">Simpan PIN</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </BendaharaLayout>
</template>

