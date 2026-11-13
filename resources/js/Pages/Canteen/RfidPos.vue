<script setup>
import CanteenLayout from '@/Layouts/CanteenLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps({
    stand: { type: Object, required: true },
    menus: { type: Array, required: true },
});

const rfidInput = ref('');
const checking = ref(false);
const checkError = ref('');
const cardInfo = ref(null);
const cart = ref({});

const payForm = useForm({
    rfid_uid: '',
    pin: '',
    items: [],
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const checkCardBalance = async () => {
    if (!rfidInput.value.trim()) {
        return;
    }
    checking.value = true;
    checkError.value = '';
    cardInfo.value = null;

    try {
        const { data } = await axios.post(route('kantin.rfid.check-balance'), {
            rfid_uid: rfidInput.value.trim(),
        });
        cardInfo.value = data;
    } catch (error) {
        checkError.value = error.response?.data?.message || 'Kartu RFID tidak ditemukan.';
    } finally {
        checking.value = false;
    }
};

const addMenu = (menu) => {
    if (menu.stock <= 0) {
        return;
    }
    const current = cart.value[menu.id]?.quantity || 0;
    if (current >= menu.stock) {
        return;
    }
    cart.value = {
        ...cart.value,
        [menu.id]: { menu, quantity: current + 1 },
    };
};

const removeMenu = (menuId) => {
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
        [menuId]: { ...current, quantity: current.quantity - 1 },
    };
};

const cartItems = computed(() => Object.values(cart.value));
const cartTotal = computed(() => cartItems.value.reduce((sum, item) => sum + (item.menu.price * item.quantity), 0));

const submitRfidPayment = () => {
    if (!cardInfo.value?.student?.rfid_uid || cartItems.value.length === 0) {
        return;
    }
    payForm.rfid_uid = cardInfo.value.student.rfid_uid;
    payForm.items = cartItems.value.map((entry) => ({
        menu_id: entry.menu.id,
        quantity: entry.quantity,
    }));

    payForm.post(route('kantin.rfid.pay'), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = {};
            payForm.reset('pin');
            checkCardBalance();
        },
    });
};
</script>

<template>
    <Head title="Kasir ATM RFID Kantin" />

    <CanteenLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Kasir ATM RFID Kantin</h1>
                <p class="text-xs text-slate-500">{{ stand.name }} &middot; Cek saldo kartu siswa &amp; bayar non-tunai</p>
            </div>
        </template>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Panel Tap Kartu & Cek Saldo -->
            <div class="space-y-4">
                <form class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3 shadow-sm" @submit.prevent="checkCardBalance">
                    <h2 class="font-extrabold text-slate-800">1. Tap / Input Kartu RFID Siswa</h2>
                    <input
                        v-model="rfidInput"
                        type="text"
                        autofocus
                        class="w-full rounded-xl border-slate-300 font-mono text-sm"
                        placeholder="Tempelkan kartu RFID..."
                        required
                    />
                    <button
                        type="submit"
                        :disabled="checking"
                        class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs transition"
                    >
                        {{ checking ? 'Memeriksa Kartu...' : 'Cek Saldo Kartu' }}
                    </button>
                    <p v-if="checkError" class="text-xs font-semibold text-rose-600">{{ checkError }}</p>
                </form>

                <div v-if="cardInfo" class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 space-y-3 shadow-md">
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-amber-300 font-bold">Kartu ATM Siswa Terdeteksi</p>
                        <h3 class="text-lg font-extrabold mt-0.5">{{ cardInfo.student.name }}</h3>
                        <p class="text-xs text-slate-300">Kelas {{ cardInfo.student.class_name }} &middot; NISN {{ cardInfo.student.nisn }}</p>
                    </div>
                    <div class="pt-3 border-t border-white/10">
                        <p class="text-xs text-slate-300">Saldo Tabungan</p>
                        <p class="text-2xl font-black text-emerald-400">{{ formatRupiah(cardInfo.wallet.balance) }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-white/10">
                        <div>
                            <p class="text-slate-400">Terpakai Hari Ini</p>
                            <p class="font-bold">{{ formatRupiah(cardInfo.wallet.today_spent) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400">Batas Harian</p>
                            <p class="font-bold">{{ cardInfo.wallet.daily_limit ? formatRupiah(cardInfo.wallet.daily_limit) : 'Tanpa batas' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Pilih Menu -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h2 class="font-extrabold text-slate-800">2. Pilih Menu Pesanan</h2>
                <div v-if="menus.length === 0" class="text-sm text-slate-500 py-6 text-center">
                    Belum ada menu aktif di stand ini.
                </div>
                <div v-else class="space-y-2.5 max-h-[420px] overflow-y-auto pr-1">
                    <div
                        v-for="menu in menus"
                        :key="menu.id"
                        class="p-3 rounded-xl border border-slate-200 flex items-center justify-between gap-2"
                    >
                        <div>
                            <p class="font-bold text-sm text-slate-800">{{ menu.name }}</p>
                            <p class="text-xs text-amber-700 font-bold">{{ formatRupiah(menu.price) }} <span class="text-slate-400 font-normal">(Stok {{ menu.stock }})</span></p>
                        </div>
                        <div v-if="cart[menu.id]" class="flex items-center gap-1.5">
                            <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 font-bold" @click="removeMenu(menu.id)">-</button>
                            <span class="text-xs font-extrabold w-5 text-center">{{ cart[menu.id].quantity }}</span>
                            <button type="button" class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 font-bold" @click="addMenu(menu)">+</button>
                        </div>
                        <button
                            v-else
                            type="button"
                            :disabled="menu.stock <= 0"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 disabled:opacity-40"
                            @click="addMenu(menu)"
                        >
                            + Pilih
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel Konfirmasi PIN & Bayar -->
            <form class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 h-fit" @submit.prevent="submitRfidPayment">
                <h2 class="font-extrabold text-slate-800">3. Pembayaran Kartu + PIN</h2>
                <div v-if="cartItems.length === 0" class="text-xs text-slate-500 py-4 text-center">
                    Pilih menu terlebih dahulu.
                </div>
                <div v-else class="space-y-2">
                    <div v-for="entry in cartItems" :key="entry.menu.id" class="flex justify-between text-xs">
                        <span>{{ entry.menu.name }} x{{ entry.quantity }}</span>
                        <span class="font-bold">{{ formatRupiah(entry.menu.price * entry.quantity) }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold">Total Tagihan</span>
                        <span class="text-lg font-black text-emerald-700">{{ formatRupiah(cartTotal) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Masukkan PIN 6 Digit Siswa</label>
                    <input
                        v-model="payForm.pin"
                        type="password"
                        inputmode="numeric"
                        maxlength="6"
                        class="w-full rounded-xl border-slate-300 text-center text-lg tracking-[0.4em] font-mono"
                        placeholder="••••••"
                        required
                    />
                    <p v-if="payForm.errors.pin" class="text-xs text-rose-600 mt-1">{{ payForm.errors.pin }}</p>
                    <p v-if="payForm.errors.rfid_uid" class="text-xs text-rose-600 mt-1">{{ payForm.errors.rfid_uid }}</p>
                    <p v-if="payForm.errors.items" class="text-xs text-rose-600 mt-1">{{ payForm.errors.items }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="payForm.processing || !cardInfo || cartItems.length === 0"
                    class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-extrabold text-sm transition"
                >
                    Proses Pembayaran RFID
                </button>
            </form>
        </div>
    </CanteenLayout>
</template>

