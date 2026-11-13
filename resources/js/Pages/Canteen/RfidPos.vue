<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import FormField from '@/Components/ui/FormField.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    stand: { type: Object, required: true },
    menus: { type: Array, required: true },
});

const toast = useToast();
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

const checkCardBalance = async () => {
    if (!rfidInput.value.trim()) return;

    checking.value = true;
    checkError.value = '';
    cardInfo.value = null;

    try {
        const { data } = await axios.post(route('kantin.rfid.check-balance'), {
            rfid_uid: rfidInput.value.trim(),
        });
        cardInfo.value = data;
        toast.success(`Kartu ${data.student.name} berhasil terdeteksi!`);
    } catch (error) {
        checkError.value = error.response?.data?.message || 'Kartu RFID tidak ditemukan atau belum ditautkan.';
        toast.error(checkError.value);
    } finally {
        checking.value = false;
    }
};

const addMenu = (menu) => {
    if (menu.stock <= 0) return;
    const current = cart.value[menu.id]?.quantity || 0;
    if (current >= menu.stock) return;
    cart.value = {
        ...cart.value,
        [menu.id]: { menu, quantity: current + 1 },
    };
};

const removeMenu = (menuId) => {
    const current = cart.value[menuId];
    if (!current) return;
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
    if (!cardInfo.value?.student?.rfid_uid || cartItems.value.length === 0) return;

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
            toast.success('Pembayaran e-Kantin RFID Berhasil!');
            checkCardBalance();
        },
        onError: () => {
            toast.error('Gagal memproses pembayaran RFID. Periksa PIN atau sisa saldo.');
        },
    });
};
</script>

<template>
    <Head title="Kasir ATM RFID Kantin" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Kasir ATM RFID Kantin"
                :subtitle="`${stand.name} — Tempelkan kartu siswa untuk transaksi instan tanpa tunai`"
            />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 1. Panel Tap Kartu & Info Siswa -->
                <div class="space-y-4">
                    <AppCard>
                        <form @submit.prevent="checkCardBalance" class="space-y-3">
                            <h3 class="font-bold text-ink text-sm">1. Tap / Input Kartu RFID</h3>
                            <AppInput
                                v-model="rfidInput"
                                autofocus
                                placeholder="Tempelkan kartu RFID siswa..."
                                class="font-mono text-center text-sm"
                                required
                            />
                            <AppButton
                                type="submit"
                                variant="primary"
                                class="w-full"
                                :loading="checking"
                            >
                                {{ checking ? 'Memeriksa Kartu...' : '🔍 Cek Saldo Kartu' }}
                            </AppButton>
                            <p v-if="checkError" class="text-xs text-status-danger-fg font-semibold">
                                {{ checkError }}
                            </p>
                        </form>
                    </AppCard>

                    <!-- Kartu ATM Siswa Digital -->
                    <div
                        v-if="cardInfo"
                        class="bg-brand-800 text-white rounded-card p-5 space-y-3 shadow-card border border-brand-700"
                    >
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-accent-400 font-bold">
                                Kartu Siswa Terdeteksi
                            </span>
                            <h4 class="text-base font-bold mt-0.5 text-white">{{ cardInfo.student.name }}</h4>
                            <p class="text-xs text-white/80">
                                Kelas {{ cardInfo.student.class_name }} • NISN: {{ cardInfo.student.nisn }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-white/20">
                            <p class="text-xs text-white/80">Saldo Dompet Tersedia</p>
                            <p class="text-2xl font-black text-accent-300 font-numeric">
                                <MoneyText :amount="cardInfo.wallet.balance" />
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-white/20">
                            <div>
                                <p class="text-white/70">Terpakai Hari Ini</p>
                                <p class="font-bold font-numeric"><MoneyText :amount="cardInfo.wallet.today_spent" /></p>
                            </div>
                            <div>
                                <p class="text-white/70">Batas Harian</p>
                                <p class="font-bold font-numeric">
                                    {{ cardInfo.wallet.daily_limit ? `Rp ${Number(cardInfo.wallet.daily_limit).toLocaleString('id-ID')}` : 'Tanpa batas' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Panel Pilih Menu -->
                <AppCard class="space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-line">
                        <h3 class="font-bold text-ink text-sm">2. Pilih Menu Pesanan</h3>
                        <span class="text-xs text-ink-muted">{{ menus.length }} item</span>
                    </div>

                    <div v-if="menus.length === 0" class="text-xs text-ink-muted py-6 text-center">
                        Belum ada menu aktif di stan ini.
                    </div>

                    <div v-else class="space-y-2.5 max-h-[460px] overflow-y-auto pr-1">
                        <div
                            v-for="menu in menus"
                            :key="menu.id"
                            class="p-3 rounded-control border border-line bg-surface flex items-center justify-between gap-2"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs text-ink truncate">{{ menu.name }}</p>
                                <p class="text-xs text-brand-700 font-bold font-numeric">
                                    <MoneyText :amount="menu.price" />
                                    <span class="text-ink-muted font-normal ml-1">(Stok {{ menu.stock }})</span>
                                </p>
                            </div>

                            <div v-if="cart[menu.id]" class="flex items-center gap-1.5 shrink-0">
                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    class="w-7 h-7 !p-0 font-bold"
                                    @click="removeMenu(menu.id)"
                                >
                                    -
                                </AppButton>
                                <span class="text-xs font-black w-6 text-center font-numeric text-ink">
                                    {{ cart[menu.id].quantity }}
                                </span>
                                <AppButton
                                    variant="primary"
                                    size="sm"
                                    class="w-7 h-7 !p-0 font-bold"
                                    @click="addMenu(menu)"
                                >
                                    +
                                </AppButton>
                            </div>

                            <AppButton
                                v-else
                                variant="outline"
                                size="sm"
                                :disabled="menu.stock <= 0"
                                @click="addMenu(menu)"
                            >
                                + Pilih
                            </AppButton>
                        </div>
                    </div>
                </AppCard>

                <!-- 3. Panel Konfirmasi PIN & Bayar -->
                <AppCard class="h-fit space-y-4">
                    <h3 class="font-bold text-ink text-sm pb-2 border-b border-line">
                        3. Pembayaran RFID + PIN
                    </h3>

                    <div v-if="cartItems.length === 0" class="text-xs text-ink-muted py-4 text-center">
                        Pilih menu terlebih dahulu dari kolom tengah.
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="entry in cartItems"
                            :key="entry.menu.id"
                            class="flex justify-between text-xs text-ink"
                        >
                            <span class="truncate max-w-[150px]">{{ entry.menu.name }} x{{ entry.quantity }}</span>
                            <span class="font-bold font-numeric">
                                <MoneyText :amount="entry.menu.price * entry.quantity" />
                            </span>
                        </div>

                        <div class="pt-2 border-t border-line flex justify-between items-center">
                            <span class="text-xs font-bold text-ink">Total Tagihan:</span>
                            <span class="text-base font-black text-brand-700 font-numeric">
                                <MoneyText :amount="cartTotal" />
                            </span>
                        </div>
                    </div>

                    <form @submit.prevent="submitRfidPayment" class="space-y-4 pt-2 border-t border-line">
                        <FormField
                            label="Masukkan PIN 6 Digit Siswa"
                            :error="payForm.errors.pin || payForm.errors.rfid_uid || payForm.errors.items"
                            required
                        >
                            <AppInput
                                v-model="payForm.pin"
                                type="password"
                                inputmode="numeric"
                                maxlength="6"
                                placeholder="••••••"
                                class="text-center text-lg tracking-[0.4em] font-mono"
                                required
                            />
                        </FormField>

                        <AppButton
                            type="submit"
                            variant="primary"
                            size="lg"
                            class="w-full shadow-card"
                            :disabled="payForm.processing || !cardInfo || cartItems.length === 0"
                            :loading="payForm.processing"
                        >
                            💳 BAYAR DENGAN KARTU RFID
                        </AppButton>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
