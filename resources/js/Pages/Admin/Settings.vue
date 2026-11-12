<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Pengaturan Sistem" />
    <AdminLayout>
        <template #header-title><h1 class="text-2xl font-black text-white">Pengaturan Sistem</h1></template>
        <template #header-subtitle><p class="text-slate-400">Konfigurasi web dan pengaturan aplikasi SIMSE</p></template>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sidebar Navigation -->
            <div class="col-span-1 space-y-2">
                <button class="w-full text-left px-5 py-4 rounded-xl bg-rose-500 text-white font-bold shadow-lg shadow-rose-500/20 flex items-center justify-between">
                    Informasi Sekolah
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <button class="w-full text-left px-5 py-4 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition flex items-center justify-between">
                    Konfigurasi Email
                </button>
                <button class="w-full text-left px-5 py-4 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition flex items-center justify-between">
                    WhatsApp Gateway
                </button>
                <button class="w-full text-left px-5 py-4 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition flex items-center justify-between">
                    Tampilan (Tema & Logo)
                </button>
            </div>

            <!-- Content Area -->
            <div class="col-span-1 md:col-span-2">
                <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-8 shadow-2xl">
                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        Informasi Sekolah
                    </h2>

                    <form @submit.prevent="" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">Nama Sekolah</label>
                                <input type="text" value="SD Negeri 1 Inovasi" class="w-full px-4 py-3 rounded-xl bg-slate-900/50 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">NPSN</label>
                                <input type="text" value="20601234" class="w-full px-4 py-3 rounded-xl bg-slate-900/50 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Alamat Lengkap</label>
                            <textarea rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-900/50 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none">Jl. Pendidikan No. 1, Kota Inovasi, Provinsi Teknologi</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">Nomor Telepon</label>
                                <input type="text" value="(021) 1234567" class="w-full px-4 py-3 rounded-xl bg-slate-900/50 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">Email Resmi</label>
                                <input type="email" value="info@smkn1inovasi.sch.id" class="w-full px-4 py-3 rounded-xl bg-slate-900/50 border border-white/10 text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500 transition-all outline-none" />
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/5 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
