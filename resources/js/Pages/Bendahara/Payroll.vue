<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payrolls: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            draft_total: 0,
            pending_total: 0,
            paid_total: 0,
        }),
    },
    selectedMonth: { type: String, default: 'November' },
    selectedYear: { type: String, default: '2026' },
});

const page = usePage();
const filterMonth = ref(props.selectedMonth);
const filterYear = ref(props.selectedYear);

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const showGenerateModal = ref(false);
const showComponentModal = ref(false);
const showSlipModal = ref(false);
const activePayroll = ref(null);

const generateForm = useForm({
    month: props.selectedMonth,
    year: props.selectedYear,
    base_salary: 3500000,
});

const componentForm = useForm({
    type: 'Penerimaan',
    name: '',
    amount: '',
});

const applyFilter = () => {
    router.get(route('bendahara.payroll'), {
        month: filterMonth.value,
        year: filterYear.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const submitGenerate = () => {
    generateForm.post(route('bendahara.payroll.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            showGenerateModal.value = false;
        },
    });
};

const submitAllDrafts = () => {
    router.post(route('bendahara.payroll.submit-approval'), {
        month: filterMonth.value,
        year: filterYear.value,
    }, { preserveScroll: true });
};

const updateStatus = (payrollId, status) => {
    router.patch(route('bendahara.payroll.status', payrollId), { status }, { preserveScroll: true });
};

const openComponentModal = (payroll) => {
    activePayroll.value = payroll;
    componentForm.reset();
    componentForm.type = 'Penerimaan';
    showComponentModal.value = true;
};

const submitComponent = () => {
    if (!activePayroll.value) {
        return;
    }

    componentForm.post(route('bendahara.payroll.components.store', activePayroll.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            componentForm.reset();
            componentForm.type = 'Penerimaan';
            const refreshed = props.payrolls.find((p) => p.id === activePayroll.value.id);
            if (refreshed) {
                activePayroll.value = refreshed;
            }
        },
    });
};

const deleteComponent = (componentId) => {
    router.delete(route('bendahara.payroll.components.destroy', componentId), {
        preserveScroll: true,
        onSuccess: () => {
            if (activePayroll.value) {
                const refreshed = props.payrolls.find((p) => p.id === activePayroll.value.id);
                if (refreshed) {
                    activePayroll.value = refreshed;
                }
            }
        },
    });
};

const openSlipModal = (payroll) => {
    activePayroll.value = payroll;
    showSlipModal.value = true;
};

const printSlip = () => {
    window.print();
};

const formatCurrency = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <Head title="Payroll & Gaji" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Payroll & Penggajian</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Kalkulasi honor mengajar, tunjangan kehadiran, potongan, dan pencairan slip gaji</p></template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
                ✅ {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-semibold">
                ⚠️ {{ page.props.flash.error }}
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <p class="text-amber-700 text-xs font-bold uppercase tracking-wider mb-1">Total Draft Gaji</p>
                    <h3 class="text-2xl font-black text-amber-600">{{ formatCurrency(summary.draft_total) }}</h3>
                    <p class="text-amber-600/70 text-xs mt-2 font-medium">Periode {{ selectedMonth }} {{ selectedYear }}</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
                    <p class="text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">Menunggu Persetujuan</p>
                    <h3 class="text-2xl font-black text-blue-600">{{ formatCurrency(summary.pending_total) }}</h3>
                    <p class="text-blue-600/70 text-xs mt-2 font-medium">Siap disetujui & dicairkan</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                    <p class="text-emerald-700 text-xs font-bold uppercase tracking-wider mb-1">Selesai Dibayar</p>
                    <h3 class="text-2xl font-black text-emerald-600">{{ formatCurrency(summary.paid_total) }}</h3>
                    <p class="text-emerald-600/70 text-xs mt-2 font-medium">Bulan {{ selectedMonth }} {{ selectedYear }}</p>
                </div>
            </div>

            <!-- Filter & Action Table -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-wrap justify-between items-center gap-4 bg-slate-50">
                    <div class="flex flex-wrap items-center gap-3">
                        <select v-model="filterMonth" @change="applyFilter" class="px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white font-bold">
                            <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
                        </select>
                        <input v-model="filterYear" @change="applyFilter" type="text" class="w-24 px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white font-bold" />
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button @click="showGenerateModal = true" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold rounded-xl shadow-sm transition text-sm">
                            Generate Gaji Massal
                        </button>
                        <button @click="submitAllDrafts" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-500/20 transition text-sm">
                            Ajukan Semua Draft
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Nama Pegawai</th>
                                <th class="py-4 px-6">Gaji Pokok</th>
                                <th class="py-4 px-6">Tunjangan</th>
                                <th class="py-4 px-6 text-rose-500">Potongan</th>
                                <th class="py-4 px-6 text-emerald-600">Total Diterima</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pegawai in payrolls" :key="pegawai.id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ pegawai.nama }}</p>
                                    <p class="text-xs text-slate-500">NIP: {{ pegawai.nip }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-600">{{ formatCurrency(pegawai.gapok) }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ formatCurrency(pegawai.tunjangan) }}</td>
                                <td class="py-4 px-6 text-rose-500 font-medium">- {{ formatCurrency(pegawai.potongan) }}</td>
                                <td class="py-4 px-6 text-slate-800 font-black">{{ formatCurrency(pegawai.total) }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-amber-100 text-amber-700': pegawai.status === 'Draft',
                                              'bg-blue-100 text-blue-700': pegawai.status === 'Menunggu Persetujuan',
                                              'bg-emerald-100 text-emerald-700': pegawai.status === 'Dibayar',
                                          }">
                                        {{ pegawai.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button v-if="pegawai.status !== 'Dibayar'" @click="openComponentModal(pegawai)"
                                                class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                            Komponen
                                        </button>
                                        <button v-if="pegawai.status === 'Draft'" @click="updateStatus(pegawai.id, 'Menunggu Persetujuan')"
                                                class="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg">
                                            Ajukan
                                        </button>
                                        <button v-if="pegawai.status !== 'Dibayar'" @click="updateStatus(pegawai.id, 'Dibayar')"
                                                class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">
                                            Cairkan
                                        </button>
                                        <button @click="openSlipModal(pegawai)"
                                                class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">
                                            Slip
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="payrolls.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada payroll periode {{ selectedMonth }} {{ selectedYear }}. Klik "Generate Gaji Massal".</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Generate Payroll -->
            <Teleport to="body">
                <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">Generate Gaji Massal</h3>
                            <button @click="showGenerateModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <form @submit.prevent="submitGenerate" class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Bulan</label>
                                    <select v-model="generateForm.month" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl">
                                        <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun</label>
                                    <input v-model="generateForm.year" type="text" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Pokok Standar (Rp)</label>
                                <input v-model="generateForm.base_salary" type="number" min="0" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl" />
                                <p class="text-[11px] text-slate-500 mt-1">Tunjangan mengajar dihitung otomatis dari total JPL jadwal & absensi kehadiran.</p>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showGenerateModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-sm">Batal</button>
                                <button type="submit" :disabled="generateForm.processing" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm">Generate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Modal Komponen Payroll -->
            <Teleport to="body">
                <div v-if="showComponentModal && activePayroll" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h3 class="font-black text-slate-800 text-lg">Komponen Gaji: {{ activePayroll.nama }}</h3>
                                <p class="text-xs text-slate-500">{{ activePayroll.month }} {{ activePayroll.year }} • Bersih: {{ formatCurrency(activePayroll.total) }}</p>
                            </div>
                            <button @click="showComponentModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>

                        <div class="space-y-2 mb-5">
                            <div v-for="comp in activePayroll.components" :key="comp.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-sm">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold mr-2"
                                          :class="comp.type === 'Penerimaan' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                        {{ comp.type }}
                                    </span>
                                    <span class="font-bold text-slate-800">{{ comp.name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-black" :class="comp.type === 'Penerimaan' ? 'text-emerald-700' : 'text-rose-600'">
                                        {{ comp.type === 'Potongan' ? '-' : '+' }} {{ formatCurrency(comp.amount) }}
                                    </span>
                                    <button @click="deleteComponent(comp.id)" class="text-xs text-rose-600 hover:underline font-bold">Hapus</button>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitComponent" class="space-y-3 border-t border-slate-100 pt-4">
                            <p class="text-xs font-black uppercase text-slate-500">Tambah Komponen Baru (Bonus / Kasbon / Tunjangan)</p>
                            <div class="grid grid-cols-3 gap-2">
                                <select v-model="componentForm.type" class="px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold">
                                    <option value="Penerimaan">Penerimaan (+)</option>
                                    <option value="Potongan">Potongan (-)</option>
                                </select>
                                <input v-model="componentForm.name" type="text" required placeholder="Nama komponen" class="col-span-2 px-3 py-2 text-xs border border-slate-200 rounded-xl" />
                            </div>
                            <div class="flex gap-2">
                                <input v-model="componentForm.amount" type="number" min="1" required placeholder="Nominal (Rp)" class="flex-1 px-3 py-2 text-xs border border-slate-200 rounded-xl" />
                                <button type="submit" :disabled="componentForm.processing" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs">
                                    + Tambah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Modal Slip Gaji -->
            <Teleport to="body">
                <div v-if="showSlipModal && activePayroll" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                            <h3 class="font-black text-slate-800 text-lg">Slip Gaji Digital</h3>
                            <button @click="showSlipModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                        </div>
                        <div class="space-y-4 text-sm">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                                <p class="font-black text-slate-800">{{ activePayroll.nama }}</p>
                                <p class="text-xs text-slate-500">NIP: {{ activePayroll.nip }} • Periode {{ activePayroll.month }} {{ activePayroll.year }}</p>
                                <p class="text-xs font-bold text-emerald-700 mt-1">Status: {{ activePayroll.status }}</p>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <div v-for="comp in activePayroll.components" :key="comp.id" class="py-2 flex justify-between">
                                    <span class="text-slate-600">{{ comp.name }} ({{ comp.type }})</span>
                                    <span class="font-bold" :class="comp.type === 'Potongan' ? 'text-rose-600' : 'text-slate-800'">
                                        {{ comp.type === 'Potongan' ? '-' : '' }} {{ formatCurrency(comp.amount) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex justify-between items-center">
                                <span class="font-bold text-emerald-900">Gaji Bersih Diterima</span>
                                <span class="text-lg font-black text-emerald-700">{{ formatCurrency(activePayroll.total) }}</span>
                            </div>
                            <button type="button" @click="printSlip" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-sm">
                                Cetak Slip Gaji
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </BendaharaLayout>
</template>
