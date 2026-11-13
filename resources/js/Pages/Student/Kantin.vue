<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    stands: { type: Array, required: true },
    wallet: { type: Object, required: true },
    orders: { type: Array, required: true },
});

const selectedStandId = ref(props.stands.find((s) => s.is_open)?.id || props.stands[0]?.id || null);
const cart = ref({});
const pinModalOpen = ref(false);
const balanceCheckPin = ref('');
const balanceCheckResult = ref(null);
const balanceCheckError = ref('');
const balanceChecking = ref(false);

const submitBalanceCheck = async () => {
    if (!balanceCheckPin.value) {
        return;
    }
    balanceChecking.value = true;
    balanceCheckError.value = '';
    try {
        const { data } = await axios.post(route('siswa.kantin.check-balance'), {
            pin: balanceCheckPin.value,
        });
        balanceCheckResult.value = data;
        balanceCheckPin.value = '';
    } catch (error) {
        balanceCheckResult.value = null;
        balanceCheckError.value = error.response?.data?.message || 'PIN tidak valid.';
    } finally {
        balanceChecking.value = false;
    }
};

const form = useForm({
    canteen_stand_id: '',
    items: [],
    pin: '',
});

const selectedStand = computed(() => props.stands.find((s) => s.id === selectedStandId.value) || null);

const selectStand = (standId) => {
    if (selectedStandId.value !== standId) {
        selectedStandId.value = standId;
        cart.value = {};
    }
};

const addToCart = (menu) => {
    if (!selectedStand.value?.is_open || menu.stock <= 0) {
        return;
    }

    const currentQty = cart.value[menu.id]?.quantity || 0;
    if (currentQty >= menu.stock) {
        return;
    }

    cart.value = {
        ...cart.value,
        [menu.id]: {
            menu,
            quantity: currentQty + 1,
        },
    };
};

const decrementCart = (menuId) => {
    const current = cart.value[menuId];
    if (!current) {
        return;
    }

    if (current.quantity <= 1) {
        const next = { ...cart.value };
        delete next[menuId];
        cart.value = next;
        return;
    }

    cart.value = {
        ...cart.value,
        [menuId]: {
            ...current,
            quantity: current.quantity - 1,
        },
    };
};

const cartItems = computed(() => Object.values(cart.value));
const cartTotal = computed(() => cartItems.value.reduce((sum, item) => sum + (item.menu.price * item.quantity), 0));

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const openCheckoutModal = () => {
    if (cartItems.value.length === 0 || !selectedStandId.value) {
        return;
    }
    form.clearErrors();
    form.pin = '';
    pinModalOpen.value = true;
};

const submitCheckout = () => {
    form.canteen_stand_id = selectedStandId.value;
    form.items = cartItems.value.map((entry) => ({
        menu_id: entry.menu.id,
        quantity: entry.quantity,
    }));

    form.post(route('siswa.kantin.checkout'), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = {};
            form.reset('pin');
            pinModalOpen.value = false;
        },
    });
};

const statusBadgeClass = (status) => {
    switch (status) {
        case 'Menunggu Konfirmasi':
            return 'bg-amber-100 text-amber-800';
        case 'Sedang Dimasak':
            return 'bg-sky-100 text-sky-800';
        case 'Siap Diambil':
            return 'bg-indigo-100 text-indigo-800';
        case 'Selesai':
            return 'bg-emerald-100 text-emerald-800';
        default:
            return 'bg-rose-100 text-rose-800';
    }
};
</script>

<template>
    <Head title="e-Kantin Digital" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">e-Kantin Digital</h1>
                <p class="text-xs text-slate-500">Pesan jajanan sehat &amp; bayar pakai Dompet Digital dengan PIN 6 digit</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info Dompet Siswa -->
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-teal-700 to-emerald-800 text-white rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-100">Saldo Dompet Digital</p>
                    <p class="text-2xl font-black mt-1">{{ formatRupiah(wallet.balance) }}</p>
                    <p class="text-[11px] text-emerald-100 mt-1">
                        {{ wallet.has_pin ? 'PIN 6 digit aktif' : 'PIN belum diatur (hubungi Bendahara)' }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs font-bold text-slate-500">Belanja Kantin Hari Ini</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ formatRupiah(wallet.today_spent) }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ wallet.daily_limit ? `Batas harian: ${formatRupiah(wallet.daily_limit)}` : 'Tanpa batas harian' }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500">Keranjang Aktif</p>
                        <p class="text-2xl font-black text-amber-600 mt-1">{{ formatRupiah(cartTotal) }}</p>
                    </div>
                    <button
                        type="button"
                        :disabled="cartItems.length === 0 || !wallet.has_pin"
                        class="mt-2 w-full py-2 rounded-xl text-xs font-extrabold transition"
                        :class="cartItems.length > 0 && wallet.has_pin ? 'bg-amber-500 hover:bg-amber-400 text-slate-950' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                        @click="openCheckoutModal"
                    >
                        Checkout Sekarang ({{ cartItems.length }} menu)
                    </button>
                </div>
            </div>

            <!-- Cek Saldo & Mutasi Tabungan via PIN -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-extrabold text-slate-800">Cek Saldo &amp; Mutasi Tabungan (PIN 6 Digit)</h2>
                        <p class="text-xs text-slate-500">Terhubung langsung dengan Kartu ATM RFID &amp; Tabungan Siswa</p>
                    </div>
                    <form class="flex items-center gap-2" @submit.prevent="submitBalanceCheck">
                        <input
                            v-model="balanceCheckPin"
                            type="password"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="PIN 6 digit"
                            class="w-36 rounded-xl border-slate-300 text-center text-sm font-mono tracking-widest"
                            required
                        />
                        <button
                            type="submit"
                            :disabled="balanceChecking || !wallet.has_pin"
                            class="px-4 py-2 rounded-xl bg-teal-700 hover:bg-teal-800 disabled:opacity-40 text-white text-xs font-bold"
                        >
                            {{ balanceChecking ? 'Mengecek...' : 'Cek Saldo' }}
                        </button>
                    </form>
                </div>

                <p v-if="balanceCheckError" class="text-xs font-semibold text-rose-600">{{ balanceCheckError }}</p>

                <div v-if="balanceCheckResult" class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs text-slate-500">Saldo Tabungan Aktif</p>
                            <p class="text-xl font-black text-emerald-700">{{ formatRupiah(balanceCheckResult.balance) }}</p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            <p>RFID: <strong class="font-mono text-slate-800">{{ balanceCheckResult.rfid_uid || 'Belum tertaut' }}</strong></p>
                            <p>Terpakai hari ini: <strong>{{ formatRupiah(balanceCheckResult.today_spent) }}</strong></p>
                        </div>
                    </div>
                    <div v-if="balanceCheckResult.transactions?.length" class="divide-y divide-slate-200 border-t border-slate-200 pt-2">
                        <div v-for="tx in balanceCheckResult.transactions" :key="tx.id" class="py-1.5 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-800">{{ tx.type }} &middot; {{ tx.description }}</p>
                                <p class="text-slate-400">{{ tx.created_at }}</p>
                            </div>
                            <span class="font-black" :class="tx.type === 'Setoran' ? 'text-emerald-700' : 'text-rose-700'">
                                {{ tx.type === 'Setoran' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilih Stand & Menu -->
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <!-- Tabs Stand -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="stand in stands"
                            :key="stand.id"
                            type="button"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold border transition"
                            :class="selectedStandId === stand.id
                                ? 'bg-teal-700 text-white border-teal-700 shadow-sm'
                                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                            @click="selectStand(stand.id)"
                        >
                            {{ stand.name }}
                            <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full" :class="stand.is_open ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                                {{ stand.is_open ? 'Buka' : 'Tutup' }}
                            </span>
                        </button>
                    </div>

                    <div v-if="!selectedStand" class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-sm text-slate-500">
                        Belum ada stand kantin yang tersedia saat ini.
                    </div>

                    <div v-else class="grid sm:grid-cols-2 gap-4">
                        <div
                            v-for="menu in selectedStand.menus"
                            :key="menu.id"
                            class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col justify-between gap-3 shadow-sm"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-extrabold text-slate-800">{{ menu.name }}</h3>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 shrink-0">
                                        Stok {{ menu.stock }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ menu.description || 'Menu sehat pilihan kantin sekolah' }}</p>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-base font-black text-teal-700">{{ formatRupiah(menu.price) }}</span>

                                <div v-if="cart[menu.id]" class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-slate-700"
                                        @click="decrementCart(menu.id)"
                                    >
                                        -
                                    </button>
                                    <span class="text-sm font-extrabold w-5 text-center">{{ cart[menu.id].quantity }}</span>
                                    <button
                                        type="button"
                                        class="w-7 h-7 rounded-lg bg-teal-600 hover:bg-teal-700 font-bold text-white"
                                        @click="addToCart(menu)"
                                    >
                                        +
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    :disabled="!selectedStand.is_open || menu.stock <= 0"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="selectedStand.is_open && menu.stock > 0
                                        ? 'bg-teal-50 text-teal-700 hover:bg-teal-100'
                                        : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                    @click="addToCart(menu)"
                                >
                                    {{ menu.stock > 0 ? '+ Tambah' : 'Habis' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Keranjang -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-fit space-y-4">
                    <h2 class="font-extrabold text-slate-800">Keranjang Belanja</h2>
                    <div v-if="cartItems.length === 0" class="text-xs text-slate-500 py-4 text-center">
                        Pilih menu dari stand untuk mulai memesan.
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="entry in cartItems" :key="entry.menu.id" class="flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-800">{{ entry.menu.name }}</p>
                                <p class="text-slate-500">{{ entry.quantity }} x {{ formatRupiah(entry.menu.price) }}</p>
                            </div>
                            <p class="font-extrabold text-slate-900">{{ formatRupiah(entry.menu.price * entry.quantity) }}</p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-600">Total</span>
                            <span class="text-lg font-black text-teal-700">{{ formatRupiah(cartTotal) }}</span>
                        </div>
                        <button
                            type="button"
                            :disabled="!wallet.has_pin"
                            class="w-full py-2.5 rounded-xl text-sm font-extrabold transition"
                            :class="wallet.has_pin ? 'bg-teal-700 hover:bg-teal-800 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                            @click="openCheckoutModal"
                        >
                            Bayar dengan Dompet Digital
                        </button>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pesanan & QR Code Pickup -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-800">Pesanan Saya &amp; QR Pengambilan</h2>
                </div>
                <div v-if="orders.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat pesanan kantin.
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div v-for="order in orders" :key="order.id" class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                    {{ order.qr_code_pickup }}
                                </span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full" :class="statusBadgeClass(order.status)">
                                    {{ order.status }}
                                </span>
                                <span class="text-xs text-slate-400">{{ order.created_at }}</span>
                            </div>
                            <p class="font-bold text-slate-800 text-sm">{{ order.stand_name }}</p>
                            <div class="flex flex-wrap gap-1.5 text-xs text-slate-600">
                                <span v-for="item in order.items" :key="item.id" class="px-2 py-0.5 rounded bg-slate-50 border border-slate-200">
                                    {{ item.menu_name }} x{{ item.quantity }}
                                </span>
                            </div>
                            <p class="text-sm font-black text-teal-700">{{ formatRupiah(order.total_price) }}</p>
                        </div>

                        <div v-if="order.qr_code_pickup && order.status !== 'Dibatalkan'" class="flex flex-col items-center p-2.5 rounded-xl border border-slate-200 bg-slate-50 shrink-0">
                            <QrcodeVue :value="order.qr_code_pickup" :size="84" level="M" />
                            <span class="text-[10px] font-bold text-slate-500 mt-1">Tunjukkan ke Stand</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi PIN 6 Digit -->
        <div v-if="pinModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <form class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl" @submit.prevent="submitCheckout">
                <h3 class="text-lg font-extrabold text-slate-800">Konfirmasi PIN Dompet</h3>
                <p class="text-xs text-slate-500">
                    Total pembayaran: <strong class="text-slate-900">{{ formatRupiah(cartTotal) }}</strong>
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Masukkan PIN 6 Digit</label>
                    <input
                        v-model="form.pin"
                        type="password"
                        inputmode="numeric"
                        maxlength="6"
                        class="w-full rounded-xl border-slate-300 text-center text-lg tracking-[0.4em] font-mono"
                        placeholder="••••••"
                        required
                    />
                    <p v-if="form.errors.pin" class="text-xs text-rose-600 mt-1">{{ form.errors.pin }}</p>
                    <p v-if="form.errors.items" class="text-xs text-rose-600 mt-1">{{ form.errors.items }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100" @click="pinModalOpen = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-teal-700 text-white hover:bg-teal-800">
                        Bayar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </StudentLayout>
</template>

