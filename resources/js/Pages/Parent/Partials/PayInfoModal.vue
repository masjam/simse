<script setup>
import Modal from '@/Components/ui/Modal.vue';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    activeChild: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);
</script>

<template>
    <Modal :show="show" title="Informasi Pembayaran Biaya Pendidikan" max-width="md" @close="emit('close')">
        <div class="space-y-4 text-xs text-ink">
            <div class="p-3 bg-brand-50 rounded-control border border-brand-200">
                <p class="font-bold text-brand-800">SD Muhammadiyah Al Mujahidin Wonosari</p>
                <p class="text-brand-700 text-[11px] mt-0.5">
                    Pembayaran SPP dan uang kegiatan dapat dilakukan melalui transfer bank atau loket Kasir Bendahara Sekolah.
                </p>
            </div>

            <div class="space-y-2">
                <h5 class="font-bold text-ink uppercase tracking-wider text-[11px]">Rekening Resmi Sekolah:</h5>
                <div class="p-3 bg-surface-raised rounded-control border border-line space-y-1">
                    <p class="text-ink-muted text-[11px]">Bank Syariah Indonesia (BSI):</p>
                    <p class="font-mono text-sm font-extrabold text-ink">714 568 9012</p>
                    <p class="text-[11px] text-ink-muted">a.n. SD Muhammadiyah Al Mujahidin</p>
                </div>

                <div class="p-3 bg-surface-raised rounded-control border border-line space-y-1">
                    <p class="text-ink-muted text-[11px]">Bank BPD DIY Syariah:</p>
                    <p class="font-mono text-sm font-extrabold text-ink">801 231 0045</p>
                    <p class="text-[11px] text-ink-muted">a.n. SD Muh Al Mujahidin Wonosari</p>
                </div>
            </div>

            <div class="p-3 rounded-control bg-surface-raised border border-line space-y-1">
                <p class="font-bold text-ink">Format Berita Transfer:</p>
                <p class="font-mono text-xs font-semibold text-brand-600">
                    SPP_{{ activeChild.nisn }}_{{ activeChild.name?.split(' ')[0] }}
                </p>
                <p class="text-[11px] text-ink-muted">
                    Contoh: SPP_26001_Andi
                </p>
            </div>

            <p class="text-[11px] text-ink-muted">
                Setelah melakukan transfer, silakan konfirmasi bukti setoran ke nomor WhatsApp Bendahara Sekolah atau tunjukkan saat pembayaran langsung di loket kasir.
            </p>

            <div class="flex justify-end pt-2 border-t border-line">
                <AppButton variant="primary" size="sm" @click="emit('close')">
                    Mengerti & Tutup
                </AppButton>
            </div>
        </div>
    </Modal>
</template>

