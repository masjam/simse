<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    billTypes: { type: Array, default: () => [] },
    bills: { type: Array, default: () => [] },
    arrears: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
});

const page = usePage();
const activeTab = ref('tagihan');
const billStatusFilter = ref('Semua');
const billPeriodFilter = ref('Semua');
const billSearch = ref('');

const monthsList = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const showBillTypeModal = ref(false);
const editingBillType = ref(null);
const showGenerateModal = ref(false);

const billTypeForm = useForm({
    name: '',
    amount: '',
    period: 'Bulanan',
});

const generateForm = useForm({
    bill_type_id: props.billTypes[0]?.id || '',
    student_class_id: '',
    month: 'November',
    year: String(new Date().getFullYear()),
    amount_due: '',
});

const selectedBillType = computed(() => {
    return props.billTypes.find((bt) => bt.id === generateForm.bill_type_id) || null;
});

const updateMonthByPeriod = (billTypeId) => {
    const selected = props.billTypes.find((bt) => bt.id === billTypeId);
    if (selected?.period === 'Semester') {
        generateForm.month = 'Semester Ganjil';
    } else if (selected?.period === 'Bulanan') {
        const curMonthIdx = new Date().getMonth();
        generateForm.month = monthsList[curMonthIdx] || 'Januari';
    } else {
        generateForm.month = '';
    }
};

watch(() => generateForm.bill_type_id, (newId) => {
    updateMonthByPeriod(newId);
});

const filteredBills = computed(() => {
    const q = billSearch.value.trim().toLowerCase();
    return props.bills.filter((b) => {
        const matchStatus = billStatusFilter.value === 'Semua' || b.status === billStatusFilter.value;
        const matchPeriod = billPeriodFilter.value === 'Semua' || b.period === billPeriodFilter.value;
        const matchQuery = !q ||
            b.student_name.toLowerCase().includes(q) ||
            b.nisn.toLowerCase().includes(q) ||
            b.bill_name.toLowerCase().includes(q) ||
            b.class.toLowerCase().includes(q) ||
            (b.month && b.month.toLowerCase().includes(q));
        return matchStatus && matchPeriod && matchQuery;
    });
});

const openCreateBillType = () => {
    editingBillType.value = null;
    billTypeForm.reset();
    billTypeForm.period = 'Bulanan';
    billTypeForm.clearErrors();
    showBillTypeModal.value = true;
};

const openEditBillType = (item) => {
    editingBillType.value = item;
    billTypeForm.name = item.name;
    billTypeForm.amount = String(item.amount);
    billTypeForm.period = item.period;
    billTypeForm.clearErrors();
    showBillTypeModal.value = true;
};

const submitBillType = () => {
    if (editingBillType.value) {
        billTypeForm.put(route('bendahara.spp.bill-types.update', editingBillType.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showBillTypeModal.value = false;
            },
        });
    } else {
        billTypeForm.post(route('bendahara.spp.bill-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showBillTypeModal.value = false;
                billTypeForm.reset();
            },
        });
    }
};

const deleteBillType = (id) => {
    if (confirm('Yakin menghapus pos tagihan master ini? Semua tagihan siswa terkait ikut terhapus.')) {
        router.delete(route('bendahara.spp.bill-types.destroy', id), { preserveScroll: true });
    }
};

const openGenerateModal = (billTypeId = null) => {
    generateForm.bill_type_id = billTypeId || props.billTypes[0]?.id || '';
    generateForm.amount_due = '';
    updateMonthByPeriod(generateForm.bill_type_id);
    generateForm.clearErrors();
    showGenerateModal.value = true;
};

const submitGenerate = () => {
    generateForm.post(route('bendahara.spp.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            showGenerateModal.value = false;
            activeTab.value = 'daftar';
        },
    });
};

const deleteBill = (id) => {
    if (confirm('Yakin menghapus tagihan siswa ini?')) {
        router.delete(route('bendahara.spp.bills.destroy', id), { preserveScroll: true });
    }
};

const sendReminder = (studentId = null) => {
    router.post(route('bendahara.spp.remind'), { student_id: studentId }, { preserveScroll: true });
};

const formatCurrency = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <Head title="Manajemen SPP" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Manajemen SPP & Tagihan</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Pengaturan pos biaya, distribusi tagihan massal, dan laporan tunggakan</p></template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
                ✅ {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-semibold">
                ⚠️ {{ page.props.flash.error }}
            </div>

            <!-- Tabs -->
            <div class="flex flex-wrap gap-4 border-b border-slate-200">
                <button @click="activeTab = 'tagihan'"
                        class="px-5 py-3.5 font-bold text-sm transition relative"
                        :class="activeTab === 'tagihan' ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600'">
                    Pos Tagihan Master
                    <div v-if="activeTab === 'tagihan'" class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 rounded-t-full"></div>
                </button>
                <button @click="activeTab = 'daftar'"
                        class="px-5 py-3.5 font-bold text-sm transition relative"
                        :class="activeTab === 'daftar' ? 'text-blue-600' : 'text-slate-500 hover:text-blue-600'">
                    Daftar Tagihan Siswa ({{ bills.length }})
                    <div v-if="activeTab === 'daftar'" class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 rounded-t-full"></div>
                </button>
                <button @click="activeTab = 'tunggakan'"
                        class="px-5 py-3.5 font-bold text-sm transition relative"
                        :class="activeTab === 'tunggakan' ? 'text-rose-600' : 'text-slate-500 hover:text-rose-600'">
                    Laporan Tunggakan ({{ arrears.length }})
                    <div v-if="activeTab === 'tunggakan'" class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 rounded-t-full"></div>
                </button>
            </div>

            <!-- TAB 1: POS TAGIHAN MASTER -->
            <div v-if="activeTab === 'tagihan'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3 bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Daftar Pos Tagihan Master</h3>
                    <div class="flex gap-2">
                        <button @click="openGenerateModal()" :disabled="billTypes.length === 0"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold rounded-xl text-sm transition">
                            Distribusi Tagihan Massal
                        </button>
                        <button @click="openCreateBillType"
                                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition text-sm">
                            + Buat Pos Tagihan
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Nama Pos Tagihan</th>
                                <th class="py-4 px-6">Nominal Baku</th>
                                <th class="py-4 px-6">Periode</th>
                                <th class="py-4 px-6">Tagihan Terbit</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in billTypes" :key="item.id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6 font-bold text-slate-800">{{ item.name }}</td>
                                <td class="py-4 px-6 text-slate-700 font-bold">{{ formatCurrency(item.amount) }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': item.period === 'Bulanan',
                                              'bg-sky-100 text-sky-700': item.period === 'Semester',
                                              'bg-purple-100 text-purple-700': item.period === 'Tahunan',
                                              'bg-amber-100 text-amber-700': item.period === 'Sekali Bayar',
                                          }">
                                        {{ item.period }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">{{ item.bills_count }} Siswa</td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openGenerateModal(item.id)" class="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg">
                                            Terbitkan
                                        </button>
                                        <button @click="openEditBillType(item)" class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                            Edit
                                        </button>
                                        <button @click="deleteBillType(item.id)" class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="billTypes.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada pos tagihan master. Silakan buat pos tagihan baru.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: DAFTAR TAGIHAN SISWA -->
            <div v-if="activeTab === 'daftar'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3 bg-slate-50">
                    <div class="flex flex-wrap gap-3 flex-1">
                        <input v-model="billSearch" type="text" placeholder="Cari siswa, kelas, atau nama tagihan..."
                               class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white w-full sm:w-64" />
                        <select v-model="billStatusFilter" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white font-semibold">
                            <option value="Semua">Semua Status</option>
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Angsuran">Angsuran</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                        <select v-model="billPeriodFilter" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white font-semibold">
                            <option value="Semua">Semua Periode</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Semester">Semester</option>
                            <option value="Tahunan">Tahunan</option>
                            <option value="Sekali Bayar">Sekali Bayar</option>
                        </select>
                    </div>
                    <button @click="openGenerateModal()" :disabled="billTypes.length === 0"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl text-sm">
                        + Terbitkan Tagihan Massal
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Tagihan</th>
                                <th class="py-4 px-6">Periode</th>
                                <th class="py-4 px-6 text-right">Tagihan</th>
                                <th class="py-4 px-6 text-right">Sisa</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="b in filteredBills" :key="b.id" class="hover:bg-slate-50">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ b.student_name }}</p>
                                    <p class="text-xs text-slate-500">{{ b.class }} • {{ b.nisn }}</p>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-700">{{ b.bill_name }}</td>
                                <td class="py-4 px-6 text-slate-600">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-200': b.period === 'Bulanan',
                                                  'bg-sky-50 text-sky-700 border border-sky-200': b.period === 'Semester',
                                                  'bg-purple-50 text-purple-700 border border-purple-200': b.period === 'Tahunan',
                                                  'bg-amber-50 text-amber-700 border border-amber-200': b.period === 'Sekali Bayar',
                                              }">
                                            {{ b.period }}
                                        </span>
                                        <span>{{ [b.month, b.year].filter(Boolean).join(' ') || '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right font-semibold">{{ formatCurrency(b.amount_due) }}</td>
                                <td class="py-4 px-6 text-right font-black text-slate-800">{{ formatCurrency(b.remaining) }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': b.status === 'Lunas',
                                              'bg-amber-100 text-amber-700': b.status === 'Angsuran',
                                              'bg-rose-100 text-rose-700': b.status === 'Belum Lunas',
                                          }">
                                        {{ b.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link v-if="b.status !== 'Lunas'" :href="route('bendahara.kasir', { student_id: b.student_id })"
                                              class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                            Bayar
                                        </Link>
                                        <button v-if="b.paid_amount === 0" @click="deleteBill(b.id)"
                                                class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredBills.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data tagihan siswa.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: TUNGGAKAN -->
            <div v-if="activeTab === 'tunggakan'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3 bg-rose-50/50">
                    <h3 class="text-lg font-black text-slate-800">Daftar Tunggakan Siswa</h3>
                    <button @click="sendReminder()" :disabled="arrears.length === 0"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md shadow-rose-500/20 transition text-sm">
                        Kirim Pengingat WA Massal
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Kelas</th>
                                <th class="py-4 px-6">Rincian Tunggakan</th>
                                <th class="py-4 px-6 text-right">Total Tunggakan</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="siswa in arrears" :key="siswa.student_id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ siswa.nama }}</p>
                                    <p class="text-xs text-slate-500">{{ siswa.nisn }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-bold">{{ siswa.kelas }}</td>
                                <td class="py-4 px-6 text-rose-600 font-medium text-xs">{{ siswa.bulan }}</td>
                                <td class="py-4 px-6 text-right text-slate-800 font-black">{{ formatCurrency(siswa.tunggakan) }}</td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="sendReminder(siswa.student_id)" class="px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100">
                                            Ingatkan WA
                                        </button>
                                        <Link :href="route('bendahara.kasir', { student_id: siswa.student_id })"
                                              class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                                            Bayar Sekarang
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="arrears.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada siswa yang menunggak.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Pos Tagihan Master -->
            <Teleport to="body">
                <div v-if="showBillTypeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">{{ editingBillType ? 'Edit Pos Tagihan' : 'Buat Pos Tagihan Baru' }}</h3>
                            <button @click="showBillTypeModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitBillType" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tagihan</label>
                                <input v-model="billTypeForm.name" type="text" required placeholder="Contoh: SPP Bulanan" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                <p v-if="billTypeForm.errors.name" class="text-xs text-rose-600 mt-1">{{ billTypeForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Baku (Rp)</label>
                                <input v-model="billTypeForm.amount" type="number" min="0" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Periode</label>
                                <select v-model="billTypeForm.period" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl">
                                    <option value="Bulanan">Bulanan</option>
                                    <option value="Semester">Semester</option>
                                    <option value="Tahunan">Tahunan</option>
                                    <option value="Sekali Bayar">Sekali Bayar</option>
                                </select>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showBillTypeModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-sm">Batal</button>
                                <button type="submit" :disabled="billTypeForm.processing" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Modal Generate Tagihan Massal -->
            <Teleport to="body">
                <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">Distribusi Tagihan Siswa Massal</h3>
                            <button @click="showGenerateModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitGenerate" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pos Tagihan</label>
                                <select v-model="generateForm.bill_type_id" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl">
                                    <option v-for="bt in billTypes" :key="bt.id" :value="bt.id">{{ bt.name }} ({{ formatCurrency(bt.amount) }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Target Kelas</label>
                                <select v-model="generateForm.student_class_id" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl">
                                    <option value="">Semua Kelas</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-if="selectedBillType?.period === 'Semester'">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Semester</label>
                                    <select v-model="generateForm.month" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl font-medium">
                                        <option value="Semester Ganjil">Semester Ganjil</option>
                                        <option value="Semester Genap">Semester Genap</option>
                                        <option value="Semester 1">Semester 1</option>
                                        <option value="Semester 2">Semester 2</option>
                                    </select>
                                </div>
                                <div v-else-if="selectedBillType?.period === 'Bulanan'">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bulan</label>
                                    <select v-model="generateForm.month" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl font-medium">
                                        <option v-for="m in monthsList" :key="m" :value="m">{{ m }}</option>
                                    </select>
                                </div>
                                <div v-else>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Sub-Periode</label>
                                    <input v-model="generateForm.month" type="text" placeholder="Contoh: Tahap 1 / T.A. 2026/2027" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun</label>
                                    <input v-model="generateForm.year" type="text" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Khusus (Kosongkan untuk nominal baku)</label>
                                <input v-model="generateForm.amount_due" type="number" min="1" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showGenerateModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-sm">Batal</button>
                                <button type="submit" :disabled="generateForm.processing" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm">Terbitkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </BendaharaLayout>
</template>
