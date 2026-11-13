<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="w-full pb-4 mb-6 border-b-2 border-ink" :class="{ 'text-xs': compact }">
        <div class="flex items-center justify-between gap-4">
            <!-- Left Logo -->
            <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center">
                <ApplicationLogo class="w-full h-full text-brand-700" />
            </div>

            <!-- Letterhead Center Text -->
            <div class="flex-1 text-center font-serif text-ink">
                <h4 class="text-xs sm:text-sm font-semibold tracking-wider uppercase">
                    Pimpinan Cabang Muhammadiyah Wonosari
                </h4>
                <h3 class="text-xs sm:text-sm font-semibold tracking-wider uppercase">
                    Majelis Pendidikan Dasar dan Menengah
                </h3>
                <h2 class="text-base sm:text-xl font-bold uppercase tracking-wide text-brand-700 mt-0.5">
                    SD Muhammadiyah Al Mujahidin
                </h2>
                <p class="text-[11px] sm:text-xs text-ink-muted mt-1 leading-tight">
                    Jl. KH. Agus Salim No. 35, Ledoksari, Kepek, Wonosari, Gunungkidul, D.I. Yogyakarta 55813<br />
                    Telp: (0274) 391234 • Pos-el: info@sdmujahidin-wns.sch.id • Laman: sdmujahidin-wns.sch.id
                </p>
            </div>

            <!-- Right Spacer / Secondary Logo if needed -->
            <div class="flex-shrink-0 w-16 sm:w-20 hidden sm:block" />
        </div>

        <!-- Double Divider Line -->
        <div class="mt-3 border-t border-ink/40 pt-0.5" />
    </div>
</template>

