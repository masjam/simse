<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const reloadPage = () => {
    window.location.reload();
};

const goHome = () => {
    window.location.href = '/';
};
</script>

<template>
    <Head title="Koneksi Terputus - SIMSE SD Muhammadiyah Al Mujahidin" />

    <PortalShell variant="bare">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-surface rounded-card border border-line p-6 shadow-card text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-50 border border-brand-200 text-brand-700 flex items-center justify-center mx-auto text-2xl font-bold">
                    📶
                </div>

                <div class="space-y-1.5">
                    <h1 class="text-lg font-bold text-ink">
                        Koneksi Internet Terputus
                    </h1>
                    <p class="text-xs text-ink-muted leading-relaxed">
                        Perangkat Anda sedang tidak terhubung ke jaringan internet. Silakan periksa koneksi Wi-Fi atau paket data seluler Anda.
                    </p>
                </div>

                <div class="p-3 rounded-control bg-surface-raised border border-line text-xs text-ink-muted">
                    <p class="font-semibold text-ink">Mode Luring Aman (Offline Safe Mode)</p>
                    <p class="text-[11px] mt-0.5">
                        Data siswa dan akun Anda tetap aman terlindungi dan tidak disimpan di perangkat publik.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-2 pt-2">
                    <AppButton size="sm" variant="secondary" @click="goHome">
                        Ke Halaman Awal
                    </AppButton>
                    <AppButton size="sm" variant="primary" @click="reloadPage">
                        Coba Hubungkan Lagi
                    </AppButton>
                </div>
            </div>
        </div>
    </PortalShell>
</template>

