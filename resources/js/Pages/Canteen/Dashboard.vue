<script setup>
import CanteenLayout from '@/Layouts/CanteenLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stand: { type: Object, required: true },
    stats: { type: Object, required: true },
    orders: { type: Array, required: true },
});

const editStandOpen = ref(false);
const standForm = useForm({
    name: props.stand.name,
    description: props.stand.description || '',
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const toggleOpen = () => {
    router.post(route('kantin.toggle-open'), {}, { preserveScroll: true });
};

const submitStand = () => {
    standForm.post(route('kantin.stand.update'), {
        preserveScroll: true,
        onSuccess: () => {
            editStandOpen.value = false;
        },
    });
};

const changeStatus = (orderId, status) => {
    router.patch(route('kantin.orders.status', orderId), { status }, { preserveScroll: true });
};

const statusBadgeClass = (status) => {
    switch (status) {
        case 'Menunggu Konfirmasi':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'Sedang Dimasak':
            return 'bg-sky-100 text-sky-800 border-sky-200';
        case 'Siap Diambil':
            return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        case 'Selesai':
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        default:
            return 'bg-rose-100 text-rose-800 border-rose-200';
    }
};
</script>

<template>
    <Head title="Dashboard Stand Kantin" />

    <CanteenLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">{{ stand.name }}</h1>
                <p class="text-xs text-slate-500">Kelola pesanan siswa, status stand, dan saldo penjualan</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header Stand & Quick Actions -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"
                            :class="stand.is_open ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                        >
                            {{ stand.is_open ? 'Stand Buka' : 'Stand Tutup' }}
                        </span>
                        <h2 class="text-lg font-extrabold text-slate-800">{{ stand.name }}</h2>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">{{ stand.description || 'Belum ada deskripsi stand.' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold border transition"
                        :class="stand.is_open ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700'"
                        @click="toggleOpen"
                    >
                        {{ stand.is_open ? 'Tutup Stand Sementara' : 'Buka Stand Sekarang' }}
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition"
                        @click="editStandOpen = !editStandOpen"
                    >
                        Ubah Info Stand
                    </button>
                    <Link
                        :href="route('kantin.scan')"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 text-slate-950 hover:bg-amber-400 transition"
                    >
                        Scan QR Pesanan
                    </Link>
                </div>
            </div>

            <!-- Form Ubah Info Stand -->
            <form
                v-if="editStandOpen"
                class="bg-white rounded-2xl border border-amber-200 p-5 space-y-4 shadow-sm"
                @submit.prevent="submitStand"
            >
                <h3 class="font-bold text-slate-800">Ubah Profil Stand</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nama Stand</label>
                        <input v-model="standForm.name" type="text" class="w-full rounded-xl border-slate-300 text-sm" required />
                        <p v-if="standForm.errors.name" class="text-xs text-rose-600 mt-1">{{ standForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Deskripsi Singkat</label>
                        <input v-model="standForm.description" type="text" class="w-full rounded-xl border-slate-300 text-sm" />
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100" @click="editStandOpen = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="standForm.processing" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 text-slate-950">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Saldo Stand</p>
                    <p class="text-xl font-black text-emerald-700 mt-1">{{ formatRupiah(stand.balance) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Omzet Hari Ini</p>
                    <p class="text-xl font-black text-amber-700 mt-1">{{ formatRupiah(stats.today_revenue) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Pesanan Aktif</p>
                    <p class="text-xl font-black text-sky-700 mt-1">{{ stats.pending_orders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Total Menu</p>
                    <p class="text-xl font-black text-slate-800 mt-1">{{ stats.menu_count }} item</p>
                </div>
            </div>

            <!-- Daftar Pesanan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-800">Pesanan Masuk Terbaru</h3>
                        <p class="text-xs text-slate-500">Proses pesanan atau validasi pengambilan lewat Scan QR</p>
                    </div>
                    <Link :href="route('kantin.menus')" class="text-xs font-bold text-amber-700 hover:underline">
                        Kelola Menu &rarr;
                    </Link>
                </div>

                <div v-if="orders.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada pesanan masuk untuk stand ini.
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div v-for="order in orders" :key="order.id" class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                                    {{ order.qr_code_pickup }}
                                </span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full border" :class="statusBadgeClass(order.status)">
                                    {{ order.status }}
                                </span>
                                <span class="text-xs text-slate-400">{{ order.created_at }}</span>
                            </div>
                            <p class="font-bold text-slate-800 text-sm">
                                {{ order.student_name }} <span class="text-xs font-medium text-slate-500">(Kelas {{ order.class_name }})</span>
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                                <span v-for="item in order.items" :key="item.id" class="px-2 py-0.5 rounded bg-amber-50 border border-amber-100">
                                    {{ item.menu_name }} x{{ item.quantity }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
                            <p class="text-base font-black text-slate-900">{{ formatRupiah(order.total_price) }}</p>
                            <div v-if="order.status !== 'Selesai' && order.status !== 'Dibatalkan'" class="flex flex-wrap gap-1.5">
                                <button
                                    v-if="order.status === 'Menunggu Konfirmasi'"
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-sky-600 text-white hover:bg-sky-700"
                                    @click="changeStatus(order.id, 'Sedang Dimasak')"
                                >
                                    Masak
                                </button>
                                <button
                                    v-if="order.status === 'Menunggu Konfirmasi' || order.status === 'Sedang Dimasak'"
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700"
                                    @click="changeStatus(order.id, 'Siap Diambil')"
                                >
                                    Siap Diambil
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700"
                                    @click="changeStatus(order.id, 'Selesai')"
                                >
                                    Selesai
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100"
                                    @click="changeStatus(order.id, 'Dibatalkan')"
                                >
                                    Batalkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CanteenLayout>
</template>

