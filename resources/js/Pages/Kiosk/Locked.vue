<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    attemptedMac: { type: String, default: null },
});
</script>

<template>
    <Head title="Kiosk Terkunci" />

    <PortalShell variant="bare">
        <div class="min-h-screen flex items-center justify-center p-6 bg-surface-sunken">
            <AppCard class="max-w-md w-full border-status-danger-line text-center space-y-4 shadow-card">
                <div class="w-16 h-16 mx-auto rounded-control bg-status-danger-bg text-status-danger-fg flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-ink">Kiosk Presensi Terkunci</h1>
                <p class="text-xs text-ink-muted leading-relaxed">
                    Perangkat PC ini belum diotorisasi atau dinonaktifkan oleh Administrator.
                </p>
                <div v-if="attemptedMac" class="font-numeric text-xs px-3 py-1.5 rounded-control bg-surface-raised text-status-danger-fg border border-line inline-block">
                    MAC Terdeteksi: {{ attemptedMac }}
                </div>
                <p class="text-xs text-ink-muted">
                    Hubungi Administrator ICT untuk mendaftarkan MAC Address PC ini pada menu Perangkat Kiosk.
                </p>
                <div class="pt-2">
                    <Link :href="route('welcome')">
                        <AppButton variant="secondary" size="sm">
                            Kembali ke Beranda
                        </AppButton>
                    </Link>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
