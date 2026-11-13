<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import KantinCartDrawer from './Partials/KantinCartDrawer.vue';

const props = defineProps({
    stands: { type: Array, required: true },
    wallet: { type: Object, required: true },
    orders: { type: Array, required: true },
});

const selectedStandId = ref(props.stands.find((s) => s.is_open)?.id || props.stands[0]?.id || null);
const cart = ref({});
const showCartDrawer = ref(false);

const pinModalOpen = ref(false);
const balanceCheckPin = ref('');
const balanceCheckResult = ref(null);
const balanceCheckError = ref('');
const balanceChecking = ref(false);

const selectedStand = computed(() => props.stands.find((s) => s.id === selectedStandId.value) || null);

const selectStand = (standId) => {
    if (selectedStandId.value !== standId) {
        selectedStandId.value = standId;
        cart.value = {};
    }
};

const addToCart = (menu) => {
    if (!selectedStand.value?.is_open || menu.stock <= 0) return;
    const currentQty = cart.value[menu.id]?.quantity || 0;
    if (currentQty >= menu.stock) return;

    cart.value = {
        ...cart.value,
        [menu.id]: { menu, quantity: currentQty + 1 },
    };
};

const decrementCart = (menuId) => {
    const current = cart.value[menuId];
    if (!current) return;
    if (current.quantity <= 1) {
        const next = { ...cart.value };
        delete next[menuId];
        cart.value = next;
    } else {
        cart.value = {
            ...cart.value,
            [menuId]: { ...current, quantity: current.quantity - 1 },
        };
    }
};

const cartCount = computed(() => Object.values(cart.value).reduce((acc, i) => acc + i.quantity, 0));

const submitBalanceCheck = async () => {
    if (!balanceCheckPin.value) return;
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
</script>

<template>
    <Head title="e-Kantin Digital Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="e-Kantin Sekolah"
                description="Pesan Makanan & Minuman Sehat SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- 1. Header Balance & Check Button -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">
                        Dompet Digital Siswa
                    </span>
                    <p class="text-xs text-ink-muted mt-0.5">
                        Gunakan PIN dompet untuk bertransaksi online atau tap kartu RFID di stan kasir kantin.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <AppButton size="sm" variant="secondary" @click="pinModalOpen = true">
                        Cek Saldo Dompet
                    </AppButton>
                    <AppButton
                        size="sm"
                        variant="primary"
                        @click="showCartDrawer = true"
                    >
                        Keranjang ({{ cartCount }})
                    </AppButton>
                </div>
            </div>

            <!-- 2. Stands Selector -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-line">
                <AppButton
                    v-for="stand in stands"
                    :key="stand.id"
                    size="sm"
                    :variant="selectedStandId === stand.id ? 'primary' : 'secondary'"
                    @click="selectStand(stand.id)"
                    class="whitespace-nowrap flex items-center gap-2"
                >
                    <span>{{ stand.name }}</span>
                    <span
                        class="w-2 h-2 rounded-full"
                        :class="stand.is_open ? 'bg-status-success-fg' : 'bg-status-danger-fg'"
                    ></span>
                </AppButton>
            </div>

            <!-- 3. Menus Grid -->
            <div v-if="!selectedStand || !selectedStand.menus || selectedStand.menus.length === 0">
                <EmptyState
                    title="Menu Belum Tersedia"
                    description="Belum ada menu yang didaftarkan pada stan kantin ini."
                />
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <div
                    v-for="menu in selectedStand.menus"
                    :key="menu.id"
                    class="bg-surface rounded-card border border-line shadow-card overflow-hidden flex flex-col justify-between"
                >
                    <div class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-sm text-ink">{{ menu.name }}</h4>
                            <span class="px-2 py-0.5 rounded-control bg-surface-raised text-ink-muted text-[10px] font-bold uppercase">
                                {{ menu.category }}
                            </span>
                        </div>
                        <p v-if="menu.description" class="text-xs text-ink-muted leading-relaxed line-clamp-2">
                            {{ menu.description }}
                        </p>
                        <div class="flex items-center justify-between pt-1">
                            <span class="font-bold font-mono text-sm text-brand-600">
                                Rp {{ Number(menu.price).toLocaleString('id-ID') }}
                            </span>
                            <span class="text-xs font-semibold text-ink-muted">
                                Stok: {{ menu.stock }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 bg-surface-raised border-t border-line flex items-center justify-between">
                        <span class="text-[11px] text-ink-muted">
                            {{ cart[menu.id] ? `Dipilih: ${cart[menu.id].quantity}` : 'Belum dipilih' }}
                        </span>
                        <AppButton
                            size="sm"
                            :variant="cart[menu.id] ? 'secondary' : 'primary'"
                            :disabled="!selectedStand.is_open || menu.stock <= 0"
                            @click="addToCart(menu)"
                        >
                            {{ cart[menu.id] ? '+ Tambah' : 'Pesan' }}
                        </AppButton>
                    </div>
                </div>
            </div>

            <!-- 4. Riwayat Pesanan Siswa -->
            <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden mt-6">
                <div class="p-4 border-b border-line bg-surface-raised">
                    <h4 class="text-sm font-bold text-ink">Riwayat Pesanan e-Kantin</h4>
                    <p class="text-xs text-ink-muted">Daftar transaksi pesanan ananda di kantin sekolah.</p>
                </div>

                <div v-if="orders.length === 0" class="p-6">
                    <EmptyState
                        title="Belum Ada Pesanan"
                        description="Belum ada riwayat pesanan kantin yang dibuat."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-surface-raised/40 transition-colors"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold text-ink">{{ order.code }}</span>
                                <span class="text-xs text-ink-muted">&bull; Stan: {{ order.stand_name }}</span>
                            </div>
                            <p class="text-xs text-ink mt-1 font-semibold">
                                {{ order.items?.map((i) => `${i.menu_name} (x${i.quantity})`).join(', ') }}
                            </p>
                            <p class="text-[11px] text-ink-muted mt-0.5">{{ order.created_at }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto">
                            <MoneyText :amount="order.total_price" class="text-xs sm:text-sm font-bold font-mono text-brand-600" />
                            <StatusBadge :status="order.status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Drawer -->
        <KantinCartDrawer
            :show="showCartDrawer"
            :cart="cart"
            :selected-stand-id="selectedStandId"
            :wallet-balance="wallet?.balance || 0"
            @close="showCartDrawer = false"
            @increment="addToCart"
            @decrement="decrementCart"
            @clear-cart="cart = {}"
        />

        <!-- Balance Check Modal -->
        <Modal :show="pinModalOpen" title="Cek Saldo Dompet Digital" max-width="sm" @close="pinModalOpen = false">
            <div class="space-y-4 text-xs">
                <FormField label="Masukkan PIN Dompet (6 Digit)" :error="balanceCheckError" required>
                    <AppInput
                        v-model="balanceCheckPin"
                        type="password"
                        maxlength="6"
                        placeholder="Contoh: 123456"
                    />
                </FormField>

                <div v-if="balanceCheckResult" class="p-3 bg-brand-50 border border-brand-200 rounded-control space-y-1">
                    <p class="text-ink-muted">Saldo Tersedia:</p>
                    <p class="text-xl font-black font-mono text-brand-700">
                        Rp {{ Number(balanceCheckResult.balance).toLocaleString('id-ID') }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-line">
                    <AppButton size="sm" variant="ghost" @click="pinModalOpen = false">Tutup</AppButton>
                    <AppButton size="sm" variant="primary" :loading="balanceChecking" @click="submitBalanceCheck">
                        Periksa Saldo
                    </AppButton>
                </div>
            </div>
        </Modal>
    </PortalShell>
</template>
