<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('tagihan');

// Mock Data
const tagihanList = [
    { id: 1, nama: 'SPP September 2026', nominal: 450000, target: 'Semua Kelas', status: 'Berjalan' },
    { id: 2, nama: 'Uang Gedung (Angsuran 1)', nominal: 1500000, target: 'Kelas 1', status: 'Berjalan' },
    { id: 3, nama: 'Seragam & Atribut', nominal: 850000, target: 'Kelas 1', status: 'Selesai' },
];

const siswaNunggak = [
    { nisn: '0012345678', nama: 'Budi Santoso', kelas: 'Kelas 6A', tunggakan: 450000, bulan: 'September 2026' },
    { nisn: '0012345679', nama: 'Siti Aminah', kelas: 'Kelas 4B', tunggakan: 900000, bulan: 'Agustus, September 2026' },
];

</script>

<template>
    <Head title="Manajemen SPP" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Manajemen SPP & Tagihan</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Pengaturan pos biaya, tagihan siswa, dan laporan tunggakan</p></template>

        <div class="space-y-6">
            <!-- Tabs -->
            <div class="flex gap-4 border-b border-slate-200">
                <button @click="activeTab = 'tagihan'" 
                        class="px-6 py-4 font-bold text-sm transition-all duration-300 relative"
                        :class="activeTab === 'tagihan' ? 'text-emerald-600' : 'text-slate-500 hover:text-emerald-600'">
                    Pos Tagihan Master
                    <div v-if="activeTab === 'tagihan'" class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 rounded-t-full"></div>
                </button>
                <button @click="activeTab = 'tunggakan'" 
                        class="px-6 py-4 font-bold text-sm transition-all duration-300 relative"
                        :class="activeTab === 'tunggakan' ? 'text-rose-600' : 'text-slate-500 hover:text-rose-600'">
                    Laporan Tunggakan
                    <div v-if="activeTab === 'tunggakan'" class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 rounded-t-full"></div>
                </button>
            </div>

            <!-- Tab Content: Tagihan -->
            <div v-if="activeTab === 'tagihan'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden animate-fade-in">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800">Daftar Tagihan Aktif</h3>
                    <button class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Tagihan Baru
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Nama Tagihan</th>
                                <th class="py-4 px-6">Nominal</th>
                                <th class="py-4 px-6">Target Siswa</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tagihan in tagihanList" :key="tagihan.id" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6 font-bold text-slate-800">{{ tagihan.nama }}</td>
                                <td class="py-4 px-6 text-slate-600 font-bold">Rp {{ tagihan.nominal.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ tagihan.target }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold" 
                                          :class="tagihan.status === 'Berjalan' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                        {{ tagihan.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="p-2 text-slate-400 hover:text-blue-600 bg-slate-50 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Tunggakan -->
            <div v-if="activeTab === 'tunggakan'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden animate-fade-in">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-rose-50/50">
                    <h3 class="text-lg font-black text-slate-800">Daftar Tunggakan Siswa</h3>
                    <button class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 transition flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Kirim Tagihan WA Massal
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500 uppercase font-bold border-b border-slate-100 bg-white">
                            <tr>
                                <th class="py-4 px-6">Siswa</th>
                                <th class="py-4 px-6">Kelas</th>
                                <th class="py-4 px-6">Bulan Tunggakan</th>
                                <th class="py-4 px-6">Total Nominal</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="siswa in siswaNunggak" :key="siswa.nisn" class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ siswa.nama }}</p>
                                    <p class="text-xs text-slate-500">{{ siswa.nisn }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-bold">{{ siswa.kelas }}</td>
                                <td class="py-4 px-6 text-rose-500 font-medium">{{ siswa.bulan }}</td>
                                <td class="py-4 px-6 text-slate-800 font-black">Rp {{ siswa.tunggakan.toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-4 py-2 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                                        Bayar Sekarang
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

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
