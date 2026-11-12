<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    exams: Array
});

// Format Waktu ke Indonesia
const formatDate = (isoString) => {
    return new Date(isoString).toLocaleString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dashboard CBT (Ujian Online)" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ujian Online (CBT)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Anti-Cheat Warning Banner -->
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm mb-8 flex items-start gap-4">
                    <div class="bg-red-100 p-2 rounded-full text-red-600 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-red-800">Sistem Pengawasan Ketat (Anti-Cheat Engine Aktif)</h3>
                        <p class="text-red-700 text-sm mt-1">Selama ujian berlangsung, sistem mendeteksi perpindahan tab (Tab Switch), membuka aplikasi lain, dan keluar dari layar penuh. Jawaban akan <b>dikumpulkan otomatis (Forced Submit)</b> jika batas peringatan dilanggar.</p>
                    </div>
                </div>

                <div class="mb-6 flex justify-between items-end">
                    <h3 class="text-xl font-bold text-gray-800">Daftar Ujian Anda</h3>
                    <div class="text-sm bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg font-medium border border-indigo-100">
                        Waktu Server: <b>{{ new Date().toLocaleTimeString('id-ID') }}</b>
                    </div>
                </div>

                <!-- Exams List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="exam in exams" :key="exam.id" 
                         class="bg-white rounded-2xl p-6 border shadow-sm transition-all duration-300 hover:shadow-lg relative overflow-hidden"
                         :class="exam.status === 'Berlangsung' ? 'border-indigo-300' : 'border-gray-200 opacity-90'">
                        
                        <!-- Status Ribbon -->
                        <div v-if="exam.status === 'Berlangsung'" class="absolute top-4 right-4 bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Sedang Berlangsung
                        </div>
                        <div v-else class="absolute top-4 right-4 bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">
                            Belum Dimulai
                        </div>

                        <div class="mt-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ exam.subject }}</span>
                            <h4 class="text-lg font-bold text-gray-900 mt-1 mb-2">{{ exam.title }}</h4>
                            <p class="text-sm text-gray-500 line-clamp-2">{{ exam.description }}</p>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div class="flex items-center text-sm text-gray-600 gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Durasi: <b>{{ exam.duration_minutes }} Menit</b></span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Jadwal: {{ formatDate(exam.start_time) }}</span>
                            </div>
                            <div class="flex items-center text-sm text-red-600 gap-3">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span>Batas Peringatan Curang: <b>Maksimal {{ exam.max_violations }} Kali</b></span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-between">
                            <div class="text-sm font-medium" :class="exam.status === 'Berlangsung' ? 'text-indigo-600' : 'text-gray-400'">
                                {{ exam.status === 'Berlangsung' ? 'Ujian dapat diakses sekarang' : 'Menunggu jadwal...' }}
                            </div>
                            <button 
                                :disabled="exam.status !== 'Berlangsung'"
                                class="px-5 py-2.5 rounded-lg font-bold transition flex items-center gap-2"
                                :class="exam.status === 'Berlangsung' ? 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-lg shadow-indigo-200' : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                Masuk Ruang Ujian
                                <svg v-if="exam.status === 'Berlangsung'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
