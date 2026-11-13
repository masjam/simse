<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Drawer from '@/Components/ui/Drawer.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    cart: { type: Object, default: () => ({}) },
    selectedStandId: { type: String, default: null },
    walletBalance: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'increment', 'decrement', 'clear-cart']);

const cartItems = computed(() => Object.values(props.cart));

const totalPrice = computed(() => {
    return cartItems.value.reduce((acc, item) => acc + item.menu.price * item.quantity, 0);
});

const checkoutForm = useForm({
    canteen_stand_id: props.selectedStandId,
    items: [],
    pin: '',
});

const submitCheckout = () => {
    checkoutForm.canteen_stand_id = props.selectedStandId;
    checkoutForm.items = cartItems.value.map((item) => ({
        menu_id: item.menu.id,
        quantity: item.quantity,
    }));

    checkoutForm.post(route('siswa.kantin.checkout'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('clear-cart');
            emit('close');
            checkoutForm.reset();
        },
    });
};
</script>

<template>
    <Drawer :show="show" title="Keranjang Pesanan e-Kantin" @close="emit('close')">
        <div class="h-full flex flex-col justify-between">
            <!-- Items List -->
            <div class="space-y-3 overflow-y-auto pr-1">
                <div v-if="cartItems.length === 0" class="text-xs text-ink-muted text-center py-10">
                    Keranjang masih kosong. Pilih menu makanan/minuman yang tersedia.
                </div>
                <div
                    v-for="item in cartItems"
                    :key="item.menu.id"
                    class="p-3 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3 text-xs"
                >
                    <div class="min-w-0">
                        <p class="font-bold text-ink truncate">{{ item.menu.name }}</p>
                        <p class="text-ink-muted">Rp {{ Number(item.menu.price).toLocaleString('id-ID') }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <AppButton
                            size="sm"
                            variant="secondary"
                            class="!w-7 !h-7 !p-0 justify-center font-bold"
                            @click="emit('decrement', item.menu.id)"
                        >
                            -
                        </AppButton>
                        <span class="font-bold font-mono text-ink text-xs w-5 text-center">
                            {{ item.quantity }}
                        </span>
                        <AppButton
                            size="sm"
                            variant="secondary"
                            class="!w-7 !h-7 !p-0 justify-center font-bold"
                            @click="emit('increment', item.menu)"
                        >
                            +
                        </AppButton>
                    </div>
                </div>
            </div>

            <!-- Footer: Total & Checkout Form -->
            <div class="pt-4 border-t border-line space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-ink-muted">Total Pembayaran:</span>
                    <MoneyText :amount="totalPrice" class="text-base font-black text-brand-600" />
                </div>

                <div v-if="cartItems.length > 0" class="space-y-3">
                    <FormField label="PIN Dompet Digital Siswa (6 Angka)" :error="checkoutForm.errors.pin" required>
                        <AppInput
                            v-model="checkoutForm.pin"
                            type="password"
                            maxlength="6"
                            placeholder="Ketik 6 digit PIN Anda"
                            required
                        />
                    </FormField>

                    <AppButton
                        variant="primary"
                        class="w-full justify-center"
                        :loading="checkoutForm.processing"
                        :disabled="totalPrice > walletBalance || totalPrice <= 0"
                        @click="submitCheckout"
                    >
                        Bayar Sekarang
                    </AppButton>
                    <p v-if="totalPrice > walletBalance" class="text-[11px] text-status-danger-fg text-center font-bold">
                        Saldo dompet Anda tidak mencukupi untuk pesanan ini.
                    </p>
                </div>
            </div>
        </div>
    </Drawer>
</template>
