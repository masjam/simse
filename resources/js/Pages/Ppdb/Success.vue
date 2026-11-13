<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
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

    <div class="min-h-screen bg-slate-950 text-slate-100 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-xl w-full bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-center">
            <!-- SUCCESS BADGE -->
            <div class="w-20 h-20 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 mx-auto flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">
                Pendaftaran Berhasil Terkirim!
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mb-6">
                Alhamdulillah, formulir PPDB online calon peserta didik telah diterima oleh sistem kami.
            </p>

            <!-- REGISTRATION CARD DETAILS -->
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 mb-6 text-left space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-xs text-slate-400 font-medium">Nomor Pendaftaran</span>
                    <span class="text-base font-mono font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                        {{ registration.registration_number }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Nama Calon Siswa:</span>
                    <span class="font-bold text-white">{{ registration.student_name }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Nama Orang Tua / Wali:</span>
                    <span class="text-white font-medium">{{ registration.parent_name }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">No. WhatsApp Notifikasi:</span>
                    <span class="text-white font-medium">{{ registration.parent_phone }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Status Saat Ini:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        {{ registration.status }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-400 leading-relaxed text-left mb-6">
                💡 <strong class="text-slate-200">Langkah Selanjutnya:</strong><br />
                1. Simpan atau cetak nomor pendaftaran ini sebagai bukti pendaftaran.<br />
                2. Notifikasi konfirmasi dan pengumuman seleksi berkas akan dikirim melalui WhatsApp ke nomor <strong class="text-emerald-400">{{ registration.parent_phone }}</strong>.<br />
                3. Jika ada pertanyaan, hubungi panitia PPDB Kampus Al Mujahidin Wonosari.
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button 
                    @click="printSlip"
                    type="button" 
                    class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <span>Cetak Bukti Pendaftaran</span>
                </button>
                <Link 
                    :href="route('home')"
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition-all flex items-center justify-center gap-2"
                >
                    <span>Kembali ke Beranda</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </Link>
            </div>
        </div>
    </div>
</template>

