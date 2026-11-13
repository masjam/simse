<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    stands: { type: Array, required: true },
    recentOrders: { type: Array, required: true },
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const applyFilter = () => {
    router.get(route('bendahara.kantin'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Laporan Penjualan e-Kantin" />

    <BendaharaLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Laporan Penjualan e-Kantin</h1>
                    <p class="text-sm text-slate-500">Rekapitulasi transaksi dan omzet per stand kantin sekolah</p>
                </div>

                <form class="flex flex-wrap items-center gap-2" @submit.prevent="applyFilter">
                    <input v-model="startDate" type="date" class="rounded-xl border-slate-300 text-xs font-semibold" />
                    <span class="text-xs text-slate-400">s/d</span>
                    <input v-model="endDate" type="date" class="rounded-xl border-slate-300 text-xs font-semibold" />
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Jumlah Stand</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ summary.total_stands }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Pesanan Periode Ini</p>
                    <p class="text-2xl font-black text-sky-700 mt-1">{{ summary.total_orders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Total Omzet Periode</p>
                    <p class="text-2xl font-black text-emerald-700 mt-1">{{ formatRupiah(summary.total_revenue) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Total Saldo Stand</p>
                    <p class="text-2xl font-black text-amber-700 mt-1">{{ formatRupiah(summary.total_stand_balance) }}</p>
                </div>
            </div>

            <!-- Tabel Per Stand -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-800">Rekapitulasi Per Stand</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Nama Stand</th>
                                <th class="px-6 py-3">Pengelola</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Pesanan</th>
                                <th class="px-6 py-3 text-right">Selesai</th>
                                <th class="px-6 py-3 text-right">Omzet Periode</th>
                                <th class="px-6 py-3 text-right">Saldo Stand</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="stand in stands" :key="stand.id" class="hover:bg-slate-50/70">
                                <td class="px-6 py-3.5 font-bold text-slate-800">{{ stand.name }}</td>
                                <td class="px-6 py-3.5 text-slate-600">{{ stand.owner_name }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="stand.is_open ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                                        {{ stand.is_open ? 'Buka' : 'Tutup' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-semibold">{{ stand.total_orders }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold">{{ stand.completed_orders }}</td>
                                <td class="px-6 py-3.5 text-right font-black text-emerald-700">{{ formatRupiah(stand.total_revenue) }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-slate-800">{{ formatRupiah(stand.balance) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Pesanan Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-800">Transaksi Kantin Terbaru</h2>
                </div>
                <div v-if="recentOrders.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada transaksi kantin pada rentang tanggal ini.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Siswa</th>
                                <th class="px-6 py-3">Stand</th>
                                <th class="px-6 py-3">Rincian</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="order in recentOrders" :key="order.id">
                                <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ order.qr_code_pickup }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-800">{{ order.student_name }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ order.stand_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ order.items_summary }}</td>
                                <td class="px-6 py-3 text-xs font-bold">{{ order.status }}</td>
                                <td class="px-6 py-3 text-right font-black text-slate-900">{{ formatRupiah(order.total_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BendaharaLayout>
</template>

