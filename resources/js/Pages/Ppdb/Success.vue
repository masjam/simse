<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';

defineProps({
    registration: {
        type: Object,
        required: true,
    },
});

const printSlip = () => {
    window.print();
};
</script>

<template>
    <Head title="Pendaftaran Berhasil - PPDB SD Muhammadiyah Al Mujahidin" />

    <PortalShell variant="bare">
        <div class="min-h-screen bg-surface-sunken py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
            <AppCard class="max-w-xl w-full shadow-card text-center space-y-6">
                <!-- Success Icon -->
                <div class="w-16 h-16 rounded-control bg-status-success-bg text-status-success-fg border border-status-success-line mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl font-bold text-ink">
                        Pendaftaran Berhasil Terkirim!
                    </h1>
                    <p class="text-xs text-ink-muted">
                        Alhamdulillah, formulir PPDB online calon peserta didik telah diterima oleh sistem.
                    </p>
                </div>

                <!-- Registration Card Details -->
                <div class="bg-surface-raised border border-line rounded-card p-5 text-left space-y-3">
                    <div class="flex items-center justify-between border-b border-line pb-3">
                        <span class="text-xs text-ink-muted font-medium">Nomor Pendaftaran</span>
                        <span class="text-base font-numeric font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-control border border-brand-200">
                            {{ registration.registration_number }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-muted">Nama Calon Siswa:</span>
                        <span class="font-bold text-ink">{{ registration.student_name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-muted">Nama Orang Tua / Wali:</span>
                        <span class="font-medium text-ink">{{ registration.parent_name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-muted">No. WhatsApp Notifikasi:</span>
                        <span class="font-medium text-ink">{{ registration.parent_phone }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-muted">Status Saat Ini:</span>
                        <StatusBadge
                            variant="warning"
                            :text="registration.status"
                            size="sm"
                        />
                    </div>
                </div>

                <!-- Instructions -->
                <div class="p-4 rounded-control bg-surface-raised border border-line text-xs text-ink-muted leading-relaxed text-left space-y-1">
                    <strong class="text-ink font-bold block mb-1">💡 Langkah Selanjutnya:</strong>
                    <p>1. Simpan atau cetak bukti nomor pendaftaran ini.</p>
                    <p>2. Konfirmasi verifikasi dokumen akan dikirim via WhatsApp ke nomor <strong class="text-ink">{{ registration.parent_phone }}</strong>.</p>
                    <p>3. Jika ada kendala, hubungi panitia PPDB Kampus Al Mujahidin Wonosari.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="md"
                        @click="printSlip"
                    >
                        🖨️ Cetak Bukti Pendaftaran
                    </AppButton>
                    <Link :href="route('welcome')">
                        <AppButton
                            variant="primary"
                            size="md"
                        >
                            Kembali ke Beranda →
                        </AppButton>
                    </Link>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
