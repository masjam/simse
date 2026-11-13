<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    role: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="h-full bg-brand-800 text-white rounded-card p-6 sm:p-8 flex flex-col justify-between shadow-card relative overflow-hidden">
        <div class="relative z-10 space-y-6">
            <!-- Brand & School Logo -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/10 rounded-control flex items-center justify-center p-2 backdrop-blur-sm border border-white/20">
                    <ApplicationLogo class="w-full h-full text-white" />
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide text-white">SD Muhammadiyah Al Mujahidin</h3>
                    <p class="text-xs text-white/70">Sistem Informasi Manajemen Sekolah (SIMSE)</p>
                </div>
            </div>

            <!-- Role Badge & Heading -->
            <div class="space-y-2 pt-2">
                <span class="inline-block px-3 py-1 text-[11px] font-bold rounded-full bg-white/15 text-accent-300 border border-white/20 uppercase tracking-wider">
                    {{ role.badge }}
                </span>
                <h2 class="text-2xl font-black text-white leading-tight">
                    {{ role.portalName }}
                </h2>
                <p class="text-xs text-white/80 leading-relaxed">
                    {{ role.desc }}
                </p>
            </div>

            <!-- Features Checklist -->
            <div class="space-y-2.5 pt-2">
                <p class="text-[11px] font-bold uppercase tracking-wider text-white/60">
                    Layanan & Fasilitas Terintegrasi:
                </p>
                <ul class="space-y-2 text-xs text-white/90">
                    <li v-for="(feat, idx) in role.features" :key="idx" class="flex items-start gap-2">
                        <span class="text-accent-300 font-bold shrink-0">✓</span>
                        <span>{{ feat }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="relative z-10 pt-6 border-t border-white/10 text-[11px] text-white/60">
            <p>Terhubung dengan Ekosistem Digital Pendidikan Muhammadiyah Wonosari.</p>
        </div>
    </div>
</template>

