<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Slip Gaji Saya" />
    <StaffLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Slip Gaji Bulanan</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Riwayat penerimaan gaji Anda</p></template>

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="bg-indigo-600 rounded-2xl p-8 text-white flex justify-between items-center shadow-lg shadow-indigo-500/30">
                <div>
                    <p class="text-indigo-200 font-bold mb-1">Gaji Terakhir Diterima</p>
                    <h2 class="text-3xl font-black">Rp 3.400.000</h2>
                    <p class="text-sm font-medium mt-1 opacity-90">Periode: September 2026</p>
                </div>
                <button class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl shadow-md transition hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh PDF
                </button>
            </div>

            <!-- Rincian Slip Gaji UI Mockup -->
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                <div class="text-center mb-8 border-b border-dashed border-slate-300 pb-8">
                    <h3 class="text-xl font-black text-slate-800">Rincian Slip Gaji</h3>
                    <p class="text-slate-500">Staf Pendukung Operasional</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-bold text-emerald-600 mb-4 uppercase text-sm tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Penerimaan
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-slate-600"><span>Gaji Pokok</span><span class="font-bold">Rp 3.000.000</span></div>
                            <div class="flex justify-between text-slate-600"><span>Tunjangan Kinerja</span><span class="font-bold">Rp 500.000</span></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-rose-600 mb-4 uppercase text-sm tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            Potongan
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-slate-600"><span>BPJS Kesehatan</span><span class="font-bold">Rp 50.000</span></div>
                            <div class="flex justify-between text-slate-600"><span>Koperasi</span><span class="font-bold">Rp 50.000</span></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-200 flex justify-between items-center bg-slate-50 p-4 rounded-xl">
                    <span class="font-bold text-slate-600">Total Diterima Bersih (Take Home Pay)</span>
                    <span class="text-2xl font-black text-slate-800">Rp 3.400.000</span>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
