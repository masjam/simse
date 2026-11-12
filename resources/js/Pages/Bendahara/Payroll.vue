<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head } from '@inertiajs/vue3';

// Mock Data
const payrollList = [
    { id: 1, nama: 'Budi Raharjo, S.Pd', posisi: 'Guru Tetap', gapok: 4500000, tunjangan: 1500000, potongan: 200000, total: 5800000, status: 'Draft' },
    { id: 2, nama: 'Siti Aminah, M.Kom', posisi: 'Kepala Lab', gapok: 5000000, tunjangan: 2000000, potongan: 250000, total: 6750000, status: 'Disetujui Kepsek' },
    { id: 3, nama: 'Ahmad Faisal', posisi: 'Staf TU', gapok: 3000000, tunjangan: 500000, potongan: 100000, total: 3400000, status: 'Selesai Dibayar' },
];

</script>

<template>
    <Head title="Payroll & Gaji" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Payroll & Penggajian</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Sistem penggajian guru dan pegawai terpadu</p></template>

        <div class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <p class="text-amber-700 text-xs font-bold uppercase tracking-wider mb-1">Total Draft Gaji</p>
                    <h3 class="text-2xl font-black text-amber-600">Rp 45.500.000</h3>
                    <p class="text-amber-600/70 text-xs mt-2 font-medium">Menunggu review Bendahara</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
                    <p class="text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">Menunggu Persetujuan</p>
                    <h3 class="text-2xl font-black text-blue-600">Rp 15.000.000</h3>
                    <p class="text-blue-600/70 text-xs mt-2 font-medium">Tertunda di Kepala Sekolah / Admin</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                    <p class="text-emerald-700 text-xs font-bold uppercase tracking-wider mb-1">Selesai Dibayar</p>
                    <h3 class="text-2xl font-black text-emerald-600">Rp 85.000.000</h3>
                    <p class="text-emerald-600/70 text-xs mt-2 font-medium">Bulan September 2026</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Draft Penggajian Bulan Ini</h3>
                    <div class="flex gap-3">
                        <button class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl shadow-sm transition text-sm">
                            Generate Gaji Massal
                        </button>
                        <button class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Ajukan Persetujuan
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Nama Pegawai</th>
                                <th class="py-4 px-6">Gaji Pokok</th>
                                <th class="py-4 px-6">Tunjangan</th>
                                <th class="py-4 px-6 text-rose-500">Potongan (Kasbon/BPJS)</th>
                                <th class="py-4 px-6 text-emerald-600">Total Diterima</th>
                                <th class="py-4 px-6">Status Approval</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pegawai in payrollList" :key="pegawai.id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ pegawai.nama }}</p>
                                    <p class="text-xs text-slate-500">{{ pegawai.posisi }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-600">Rp {{ pegawai.gapok.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-slate-600">Rp {{ pegawai.tunjangan.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-rose-500 font-medium">- Rp {{ pegawai.potongan.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-slate-800 font-black">Rp {{ pegawai.total.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 w-max"
                                          :class="{'bg-amber-100 text-amber-700': pegawai.status === 'Draft',
                                                   'bg-blue-100 text-blue-700': pegawai.status === 'Disetujui Kepsek',
                                                   'bg-emerald-100 text-emerald-700': pegawai.status === 'Selesai Dibayar'}">
                                        <div class="w-1.5 h-1.5 rounded-full" 
                                             :class="{'bg-amber-500': pegawai.status === 'Draft',
                                                      'bg-blue-500': pegawai.status === 'Disetujui Kepsek',
                                                      'bg-emerald-500': pegawai.status === 'Selesai Dibayar'}"></div>
                                        {{ pegawai.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button v-if="pegawai.status === 'Disetujui Kepsek'" class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition shadow-md shadow-emerald-500/20">
                                        Cairkan & Buat Slip
                                    </button>
                                    <button v-else-if="pegawai.status === 'Selesai Dibayar'" class="px-3 py-1.5 text-xs font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center gap-1 ml-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak Slip
                                    </button>
                                    <button v-else class="p-2 text-slate-400 hover:text-blue-600 bg-slate-50 hover:bg-blue-50 rounded-lg transition ml-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </BendaharaLayout>
</template>
