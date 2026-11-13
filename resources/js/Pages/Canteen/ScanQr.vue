<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { QrcodeStream } from 'vue-qrcode-reader';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useToast } from '@/composables/useToast';

defineProps({
    stand: { type: Object, required: true },
    readyOrders: { type: Array, required: true },
});

const toast = useToast();
const cameraActive = ref(false);
const cameraError = ref('');

const form = useForm({
    qr_code: '',
});

const submitCode = (code) => {
    if (code) {
        form.qr_code = code;
    }

    form.post(route('kantin.scan.validate'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('qr_code');
            toast.success('Pesanan berhasil divalidasi dan diselesaikan!');
        },
        onError: () => {
            toast.error('Kode QR atau kode pickup tidak valid!');
        },
    });
};

const onDetect = (detectedCodes) => {
    if (detectedCodes && detectedCodes.length > 0) {
        const rawValue = detectedCodes[0].rawValue;
        cameraActive.value = false;
        submitCode(rawValue);
    }
};

const onCameraError = (err) => {
    cameraError.value = err?.message || 'Kamera tidak dapat diakses. Gunakan input kode manual di bawah.';
    cameraActive.value = false;
};
</script>

<template>
    <Head title="Scan QR Ambil Pesanan" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Validasi Pengambilan Pesanan (QR)"
                :subtitle="`Pindai QR bukti pemesanan siswa untuk ${stand.name}`"
            />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Scanner Section -->
                <AppCard>
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                        <h3 class="font-bold text-ink text-sm">Pindai QR Siswa</h3>
                        <AppButton
                            :variant="cameraActive ? 'danger' : 'primary'"
                            size="sm"
                            @click="cameraActive = !cameraActive"
                        >
                            {{ cameraActive ? 'Matikan Kamera' : '📷 Buka Kamera QR' }}
                        </AppButton>
                    </div>

                    <div v-if="cameraError" class="p-3 mb-4 rounded-control bg-status-warning-bg text-status-warning-fg border border-status-warning-line text-xs font-semibold">
                        {{ cameraError }}
                    </div>

                    <div v-if="cameraActive" class="rounded-card overflow-hidden border border-line bg-ink aspect-video mb-4">
                        <QrcodeStream @detect="onDetect" @error="onCameraError" />
                    </div>

                    <form class="space-y-3 pt-2" @submit.prevent="submitCode()">
                        <label class="block text-xs font-bold text-ink-muted">
                            Atau masukkan kode pengambilan manual:
                        </label>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <AppInput
                                    v-model="form.qr_code"
                                    placeholder="Contoh: KNT-ABCD123456"
                                    class="font-mono uppercase text-sm"
                                    required
                                />
                            </div>
                            <AppButton
                                type="submit"
                                variant="primary"
                                :loading="form.processing"
                            >
                                Validasi
                            </AppButton>
                        </div>
                        <p v-if="form.errors.qr_code" class="text-xs text-status-danger-fg">
                            {{ form.errors.qr_code }}
                        </p>
                    </form>
                </AppCard>

                <!-- Orders Ready for Pickup -->
                <AppCard>
                    <div class="pb-3 border-b border-line mb-4">
                        <h3 class="font-bold text-ink text-sm">
                            Pesanan Menunggu Diambil ({{ readyOrders.length }})
                        </h3>
                    </div>

                    <div v-if="readyOrders.length === 0">
                        <EmptyState
                            title="Tidak Ada Antrean"
                            description="Saat ini tidak ada pesanan aktif yang menunggu pengambilan."
                        />
                    </div>

                    <div v-else class="divide-y divide-line">
                        <div
                            v-for="order in readyOrders"
                            :key="order.id"
                            class="py-3 flex items-center justify-between gap-3"
                        >
                            <div class="space-y-0.5">
                                <p class="font-mono text-xs font-bold text-brand-700">
                                    {{ order.qr_code_pickup }}
                                </p>
                                <p class="text-xs font-bold text-ink">{{ order.student_name }}</p>
                                <p class="text-[11px] text-ink-muted">{{ order.items_summary }}</p>
                            </div>

                            <div class="text-right shrink-0 space-y-1">
                                <p class="text-xs font-black text-ink font-numeric">
                                    <MoneyText :amount="order.total_price" />
                                </p>
                                <AppButton
                                    variant="outline"
                                    size="sm"
                                    @click="submitCode(order.qr_code_pickup)"
                                >
                                    Selesaikan
                                </AppButton>
                            </div>
                        </div>
                    </div>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
