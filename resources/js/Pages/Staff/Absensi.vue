<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const isCheckIn = ref(false);

const mockLocation = "Terkoneksi - Radius Sekolah (15m)";
</script>

<template>
    <Head title="Absensi Kehadiran" />
    <StaffLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Absensi Harian</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Lakukan swafoto dan bagikan lokasi Anda</p></template>

        <div class="max-w-2xl mx-auto mt-8">
            <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl text-center">
                <div class="w-64 h-64 bg-slate-100 rounded-2xl mx-auto mb-6 flex flex-col items-center justify-center border-2 border-dashed border-slate-300 relative overflow-hidden group cursor-pointer">
                    <svg class="w-16 h-16 text-slate-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <p class="text-slate-500 font-bold mt-4">Klik untuk Buka Kamera</p>
                </div>

                <div class="flex items-center justify-center gap-2 text-emerald-600 font-bold bg-emerald-50 py-2 px-4 rounded-full w-max mx-auto mb-8">
                    <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    {{ mockLocation }}
                </div>

                <button class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl text-lg shadow-lg shadow-indigo-500/30 transition-all">
                    CATAT KEHADIRAN SEKARANG
                </button>
            </div>
        </div>
    </StaffLayout>
</template>
