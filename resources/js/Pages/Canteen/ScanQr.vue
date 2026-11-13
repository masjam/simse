<script setup>
import CanteenLayout from '@/Layouts/CanteenLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { QrcodeStream } from 'vue-qrcode-reader';
import { ref } from 'vue';

defineProps({
    stand: { type: Object, required: true },
    readyOrders: { type: Array, required: true },
});

const cameraActive = ref(false);
const cameraError = ref('');

const form = useForm({
    qr_code: '',
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

const submitCode = (code) => {
    if (code) {
        form.qr_code = code;
    }

    form.post(route('kantin.scan.validate'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('qr_code');
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

    <CanteenLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Validasi Pengambilan Pesanan (QR)</h1>
                <p class="text-xs text-slate-500">{{ stand.name }}</p>
            </div>
        </template>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-slate-800">Pindai QR Siswa</h2>
                    <button
                        type="button"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition"
                        :class="cameraActive ? 'bg-rose-100 text-rose-700' : 'bg-amber-500 text-slate-950 hover:bg-amber-400'"
                        @click="cameraActive = !cameraActive"
                    >
                        {{ cameraActive ? 'Matikan Kamera' : 'Buka Kamera QR' }}
                    </button>
                </div>

                <div v-if="cameraError" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800">
                    {{ cameraError }}
                </div>

                <div v-if="cameraActive" class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-950 aspect-video">
                    <QrcodeStream @detect="onDetect" @error="onCameraError" />
                </div>

                <form class="space-y-3 pt-2 border-t border-slate-100" @submit.prevent="submitCode()">
                    <label class="block text-xs font-bold text-slate-600">Atau masukkan kode pengambilan manual</label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.qr_code"
                            type="text"
                            class="flex-1 rounded-xl border-slate-300 text-sm font-mono uppercase"
                            placeholder="Contoh: KNT-ABCD123456"
                            required
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition"
                        >
                            Validasi
                        </button>
                    </div>
                    <p v-if="form.errors.qr_code" class="text-xs text-rose-600">{{ form.errors.qr_code }}</p>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-800">Pesanan Menunggu Diambil</h2>
                </div>

                <div v-if="readyOrders.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Tidak ada pesanan aktif yang menunggu pengambilan.
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div v-for="order in readyOrders" :key="order.id" class="p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-mono text-xs font-bold text-amber-700">{{ order.qr_code_pickup }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ order.student_name }}</p>
                            <p class="text-xs text-slate-500">{{ order.items_summary }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-black text-slate-900">{{ formatRupiah(order.total_price) }}</p>
                            <button
                                type="button"
                                class="mt-1 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold"
                                @click="submitCode(order.qr_code_pickup)"
                            >
                                Selesaikan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CanteenLayout>
</template>

