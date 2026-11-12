<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const widgets = [
    { title: 'Siswa Aktif', value: '1,248', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', color: 'bg-blue-500' },
    { title: 'Kehadiran Hari Ini', value: '98.5%', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'bg-emerald-500' },
    { title: 'Transaksi Kantin', value: 'Rp 4.2Jt', icon: 'M13 10V3L4 14h7v7l9-11h-7z', color: 'bg-amber-500' },
    { title: 'Ujian Aktif (CBT)', value: '3 Sesi', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', color: 'bg-purple-500' },
];
</script>

<template>
    <Head title="Dashboard Utama" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-indigo-800 to-purple-700 rounded-2xl shadow-xl overflow-hidden mb-8 relative border border-indigo-500/30">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="px-8 py-10 relative z-10 flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-white mb-2">Selamat Datang, Super Admin! 👋</h2>
                            <p class="text-indigo-200">21 Modul Sistem Manajemen Sekolah Anda siap untuk beroperasi. Semua database telah termigrasi.</p>
                        </div>
                        <div class="hidden md:block flex gap-4">
                            <Link v-if="$page.props.auth.user.roles?.includes('Super Admin') || $page.props.auth.user.roles?.includes('Guru Pengajar')" :href="route('guru.dashboard')" class="mr-4 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white border border-amber-400 rounded-xl transition shadow-lg flex items-center gap-2 inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Masuk Portal Guru
                            </Link>

                            <Link :href="route('kts')" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl transition backdrop-blur-sm flex items-center gap-2 inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Pratinjau KTS Digital
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Widgets Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div v-for="widget in widgets" :key="widget.title" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-4">
                            <div :class="`w-12 h-12 rounded-xl flex items-center justify-center text-white ${widget.color}`">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="widget.icon"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg">+12%</span>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">{{ widget.title }}</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ widget.value }}</p>
                    </div>
                </div>

                <!-- Central Modules Map -->
                <h3 class="text-lg font-bold text-gray-800 mb-4">Pusat Navigasi Modul Terpadu</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link :href="route('learning.cbt')" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-indigo-100 hover:border-indigo-200 transition">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h4 class="font-bold text-gray-800">Ujian Online (CBT)</h4>
                        <p class="text-sm text-gray-500 mt-1">Kelola sesi ujian, deteksi kecurangan otomatis.</p>
                    </Link>
                    
                    <Link :href="route('finansial.kantin')" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-indigo-100 hover:border-indigo-200 transition">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <h4 class="font-bold text-gray-800">e-Kantin Digital</h4>
                        <p class="text-sm text-gray-500 mt-1">Pemesanan online dan pembayaran via dompet digital.</p>
                    </Link>

                    <Link :href="route('sistem.wa')" class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-indigo-100 hover:border-indigo-200 transition">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <h4 class="font-bold text-gray-800">WhatsApp Gateway</h4>
                        <p class="text-sm text-gray-500 mt-1">Notifikasi absen realtime dan pengumuman massal.</p>
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
