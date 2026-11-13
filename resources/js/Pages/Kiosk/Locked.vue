<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    attemptedMac: { type: String, default: null },
});
</script>

<template>
    <Head title="Kiosk Terkunci" />

    <div class="min-h-screen bg-slate-950 text-white flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-slate-900 border border-rose-500/30 rounded-3xl p-8 text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-xl font-black text-white">Kiosk Presensi Terkunci</h1>
            <p class="text-sm text-slate-300">
                Perangkat PC ini belum diotorisasi atau dinonaktifkan oleh Super Admin.
            </p>
            <p v-if="attemptedMac" class="font-mono text-xs px-3 py-1.5 rounded-lg bg-slate-800 text-rose-300 inline-block">
                MAC Terdeteksi: {{ attemptedMac }}
            </p>
            <p class="text-xs text-slate-400">
                Hubungi Super Admin untuk mendaftarkan MAC Address PC ini pada menu <strong>Perangkat Kiosk (MAC)</strong>.
            </p>
            <div class="pt-2">
                <Link href="/" class="inline-block px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition">
                    Kembali ke Beranda
                </Link>
            </div>
        </div>
    </div>
</template>

