<script setup>
import BendaharaLayout from '@/Layouts/BendaharaLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const searchQuery = ref('');
const nominal = ref('');

// Computed property to format nominal to Rupiah automatically
const formattedNominal = computed({
    get() {
        if (!nominal.value) return '';
        // Format to Rupiah
        let val = nominal.value.toString().replace(/[^0-9]/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    set(val) {
        nominal.value = val.replace(/[^0-9]/g, '');
    }
});

// Simple terbilang converter logic for UX
const terbilang = computed(() => {
    if (!nominal.value) return '';
    const num = parseInt(nominal.value);
    if (isNaN(num)) return '';
    // A simplified terbilang text just for visual effect in this mockup
    return num.toLocaleString('id-ID') + ' Rupiah';
});

</script>

<template>
    <Head title="Kasir Pembayaran" />
    <BendaharaLayout>
        <template #header-title><h1 class="text-2xl font-black text-slate-800">Mesin Kasir Sekolah</h1></template>
        <template #header-subtitle><p class="text-slate-500 font-medium">Penerimaan pembayaran SPP dan biaya administrasi lainnya</p></template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            <!-- Left Side: Cari Siswa & Input Kasir -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Search Student -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Pencarian Siswa
                    </h3>
                    
                    <div class="relative group">
                        <input v-model="searchQuery" type="text" placeholder="Masukkan Nama Siswa, NISN, atau tap Kartu KTS..." 
                               class="w-full pl-5 pr-12 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-lg font-bold text-slate-800 placeholder-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition outline-none" />
                        <button class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-slate-800 text-white rounded-lg hover:bg-emerald-600 transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>

                    <!-- Mock Selected Student Card -->
                    <div class="mt-6 p-4 border-2 border-emerald-500 rounded-xl bg-emerald-50/50 flex items-center gap-5">
                        <img src="https://ui-avatars.com/api/?name=Budi+Santoso&background=10b981&color=fff" class="w-16 h-16 rounded-xl shadow-md border-2 border-white" />
                        <div class="flex-1">
                            <h4 class="text-xl font-black text-slate-800">Budi Santoso</h4>
                            <p class="text-slate-600 font-medium text-sm">NISN: 0012345678 • Kelas: 6A</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-500 uppercase">Status SPP</p>
                            <p class="text-rose-600 font-black text-lg">Menunggak 1 Bln</p>
                        </div>
                    </div>
                </div>

                <!-- Input Payment -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 mb-6">Detail Pembayaran</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Pilih Jenis Tagihan</label>
                            <select class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-800 font-bold focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition outline-none">
                                <option>SPP Bulan September 2026</option>
                                <option>Uang Gedung / SPI</option>
                                <option>Uang Seragam & Atribut</option>
                                <option>Iuran Ekstrakurikuler</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Nominal Pembayaran</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-black text-xl">Rp</span>
                                </div>
                                <input v-model="formattedNominal" type="text" placeholder="0" 
                                       class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-2xl font-black text-slate-800 placeholder-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition outline-none text-right" />
                            </div>
                            <p v-if="nominal" class="mt-2 text-emerald-600 font-bold italic text-sm text-right flex justify-end items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Terbilang: {{ terbilang }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" class="peer sr-only" checked />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 font-bold transition">
                                        Tunai
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" class="peer sr-only" />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 text-slate-500 font-bold transition">
                                        Transfer
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" class="peer sr-only" />
                                    <div class="p-3 text-center border-2 border-slate-200 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 text-slate-500 font-bold transition">
                                        Tabungan Siswa
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Struk & Tombol Aksi -->
            <div class="lg:col-span-1">
                <div class="bg-slate-800 rounded-2xl shadow-xl overflow-hidden sticky top-24 flex flex-col h-[calc(100vh-8rem)]">
                    <!-- Thermal Receipt Mockup -->
                    <div class="flex-1 p-6 bg-slate-50 m-4 rounded-t-xl font-mono text-sm shadow-inner relative flex flex-col">
                        <!-- jagged edge top -->
                        <div class="absolute top-0 left-0 right-0 h-2 bg-repeat-x" style="background-image: radial-gradient(circle at 50% 0, transparent 50%, #f8fafc 51%); background-size: 10px 10px; transform: translateY(-100%);"></div>
                        
                        <div class="text-center mb-6">
                            <h4 class="font-bold text-lg text-slate-800">SD NEGERI 1 INOVASI</h4>
                            <p class="text-slate-500 text-xs">Jl. Pendidikan No. 1</p>
                            <p class="text-slate-500 text-xs border-b border-dashed border-slate-300 pb-4 mb-4">Telp: (021) 1234567</p>
                        </div>
                        
                        <div class="space-y-2 text-slate-700 flex-1">
                            <div class="flex justify-between"><span>No:</span><span>TRX-10293</span></div>
                            <div class="flex justify-between"><span>Kasir:</span><span>Admin Keuangan</span></div>
                            <div class="flex justify-between"><span>Siswa:</span><span class="font-bold">Budi Santoso</span></div>
                            
                            <div class="border-t border-dashed border-slate-300 my-4 pt-4">
                                <div class="flex justify-between font-bold">
                                    <span>SPP Sept 2026</span>
                                    <span>Rp {{ formattedNominal || '0' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-slate-300 pt-4 mt-auto">
                            <div class="flex justify-between text-lg font-black text-slate-800">
                                <span>TOTAL</span>
                                <span>Rp {{ formattedNominal || '0' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 bg-slate-800">
                        <button class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl text-lg shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            PROSES & CETAK STRUK
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </BendaharaLayout>
</template>
