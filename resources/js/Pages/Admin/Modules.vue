<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    modules: Array,
});

// Create a local reactive copy for immediate UI interaction
const localModules = ref(props.modules.map(m => ({ ...m })));
const isSaving = ref(false);

const toggleModule = (module) => {
    module.status = !module.status;
    isSaving.value = true;
    
    // Simulate API call to save settings
    setTimeout(() => {
        isSaving.value = false;
        // In a real app, we'd use router.post() to update the DB
    }, 800);
};
</script>

<template>
    <Head title="Manajemen Modul" />
    <AdminLayout>
        <template #header-title><h1 class="text-2xl font-black text-white">Manajemen Modul Aplikasi</h1></template>
        <template #header-subtitle><p class="text-slate-400">Aktifkan atau nonaktifkan fitur sesuai kebutuhan sekolah</p></template>

        <div class="mb-6 flex justify-between items-center bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 shadow-2xl">
            <div>
                <h3 class="text-white font-bold text-lg">Pusat Pengendali Modul (Microservices)</h3>
                <p class="text-sm text-slate-400 mt-1">SIMSE menggunakan arsitektur modular. Mematikan modul yang tidak digunakan akan meningkatkan performa server secara signifikan.</p>
            </div>
            <div class="flex items-center gap-3">
                <span v-if="isSaving" class="text-xs text-rose-400 font-bold animate-pulse flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menyimpan...
                </span>
                <span v-else class="text-xs text-emerald-400 font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Tersimpan Otomatis
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="mod in localModules" :key="mod.id" 
                 class="relative overflow-hidden bg-slate-800/50 backdrop-blur-xl border rounded-2xl p-6 transition-all duration-300 group"
                 :class="mod.status ? 'border-rose-500/50 shadow-[0_0_30px_rgba(244,63,94,0.15)]' : 'border-white/5'">
                
                <!-- Background Glow for Active State -->
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full blur-3xl transition-opacity duration-500"
                     :class="mod.status ? 'bg-rose-500/20 opacity-100' : 'opacity-0'"></div>

                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center transition-colors duration-300"
                         :class="mod.status ? 'bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-lg shadow-rose-500/30' : 'bg-slate-900 text-slate-500'">
                        <!-- Dynamic Icon -->
                        <svg v-if="mod.icon === 'akademik'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <svg v-else-if="mod.icon === 'lms'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <svg v-else-if="mod.icon === 'cbt'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <svg v-else-if="mod.icon === 'bk'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <svg v-else-if="mod.icon === 'absensi'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                        <svg v-else-if="mod.icon === 'keuangan'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg v-else-if="mod.icon === 'kantin'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <svg v-else-if="mod.icon === 'payroll'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <svg v-else-if="mod.icon === 'ppdb'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>

                    <!-- Custom Toggle Switch -->
                    <button @click="toggleModule(mod)" 
                            class="w-14 h-8 rounded-full p-1 transition-colors duration-300 relative focus:outline-none focus:ring-2 focus:ring-rose-500/50"
                            :class="mod.status ? 'bg-gradient-to-r from-rose-500 to-orange-400' : 'bg-slate-700'">
                        <div class="w-6 h-6 bg-white rounded-full shadow-md transform transition-transform duration-300"
                             :class="mod.status ? 'translate-x-6' : 'translate-x-0'"></div>
                    </button>
                </div>

                <div class="relative z-10">
                    <h2 class="text-xl font-bold text-white mb-2" :class="mod.status ? 'text-white' : 'text-slate-300'">{{ mod.name }}</h2>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ mod.description }}</p>
                </div>
                
                <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-4 relative z-10">
                    <span class="text-xs font-bold px-3 py-1 rounded-full transition-colors duration-300" 
                          :class="mod.status ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-500'">
                        {{ mod.status ? 'AKTIF' : 'NONAKTIF' }}
                    </span>
                    <button class="text-xs font-bold text-slate-400 hover:text-white transition">Konfigurasi</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
