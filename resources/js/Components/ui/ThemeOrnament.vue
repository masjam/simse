<script setup>
import { computed } from 'vue';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    position: {
        type: String,
        default: 'top-right', // 'top-right' | 'top-left' | 'bottom-right' | 'header'
    },
    opacity: {
        type: String,
        default: 'opacity-[0.06]',
    },
});

const { currentTheme } = useTheme();

const positionClasses = computed(() => {
    switch (props.position) {
        case 'top-left':
            return 'top-0 left-0 -translate-x-6 -translate-y-6';
        case 'bottom-right':
            return 'bottom-0 right-0 translate-x-6 translate-y-6';
        case 'header':
            return 'top-0 right-0 h-full w-auto';
        case 'top-right':
        default:
            return 'top-0 right-0 translate-x-8 -translate-y-8';
    }
});
</script>

<template>
    <div
        class="absolute pointer-events-none select-none z-0 overflow-hidden transition-opacity duration-300"
        :class="[positionClasses, opacity]"
        aria-hidden="true"
    >
        <!-- 1. Tema Madinah: Geometri Arabesque Islami 8-Point Star -->
        <svg
            v-if="currentTheme === 'madinah' || currentTheme === 'madinah-gelap'"
            class="w-48 h-48 md:w-64 md:h-64 text-brand-700 dark:text-brand-400"
            viewBox="0 0 200 200"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
        >
            <polygon points="100,10 120,55 165,35 145,80 190,100 145,120 165,165 120,145 100,190 80,145 35,165 55,120 10,100 55,80 35,35 80,55" />
            <circle cx="100" cy="100" r="50" stroke-width="1" />
            <circle cx="100" cy="100" r="30" stroke-width="1" stroke-dasharray="3 3" />
        </svg>

        <!-- 2. Tema Taman Belajar: Awan & Bintang Ceria -->
        <svg
            v-else-if="currentTheme === 'taman-belajar'"
            class="w-48 h-48 md:w-64 md:h-64 text-brand-600"
            viewBox="0 0 200 200"
            fill="currentColor"
        >
            <!-- Awan Ceria -->
            <path d="M50 120 A25 25 0 0 1 95 105 A35 35 0 0 1 150 115 A25 25 0 0 1 155 150 L50 150 A20 20 0 0 1 50 120 Z" />
            <!-- Bintang Kecil -->
            <polygon points="40,40 45,52 58,52 47,60 51,72 40,64 29,72 33,60 22,52 35,52" />
            <polygon points="160,50 163,58 172,58 165,63 167,71 160,66 153,71 155,63 148,58 157,58" />
        </svg>

        <!-- 3. Tema Kertas dan Tinta: Garis Cetak Rambut & Cap Perpus -->
        <svg
            v-else-if="currentTheme === 'kertas-tinta'"
            class="w-48 h-48 md:w-64 md:h-64 text-brand-700"
            viewBox="0 0 200 200"
            fill="none"
            stroke="currentColor"
            stroke-width="1.2"
        >
            <circle cx="100" cy="100" r="75" stroke-dasharray="4 2" />
            <circle cx="100" cy="100" r="65" />
            <line x1="25" y1="100" x2="175" y2="100" />
            <line x1="100" y1="25" x2="100" y2="175" />
            <rect x="70" y="70" width="60" height="60" rx="4" />
        </svg>

        <!-- 4. Tema Ledger Tegas: Kisi Kotak Presisi Akuntansi -->
        <svg
            v-else-if="currentTheme === 'ledger-tegas'"
            class="w-48 h-48 md:w-64 md:h-64 text-ink"
            viewBox="0 0 200 200"
            fill="none"
            stroke="currentColor"
            stroke-width="1"
        >
            <defs>
                <pattern id="grid-ledger" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="currentColor" stroke-width="0.75" />
                </pattern>
            </defs>
            <rect width="200" height="200" fill="url(#grid-ledger)" />
            <line x1="0" y1="60" x2="200" y2="60" stroke-width="2" />
            <line x1="0" y1="140" x2="200" y2="140" stroke-width="2" />
        </svg>

        <!-- 5. Tema Senja Kaca: Ornamen Gelombang Lengkung Kaca Halus -->
        <svg
            v-else-if="currentTheme === 'senja-kaca'"
            class="w-48 h-48 md:w-64 md:h-64 text-brand-600"
            viewBox="0 0 200 200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M 20 180 Q 80 40 180 80 T 190 190" stroke-linecap="round" />
            <path d="M 40 190 Q 110 90 190 120" stroke-width="1" stroke-dasharray="4 4" />
            <circle cx="140" cy="70" r="16" fill="currentColor" fill-opacity="0.1" />
        </svg>

        <!-- 6. Tema Pasar Kantin: Motif Buah & Daun Kuliner -->
        <svg
            v-else-if="currentTheme === 'pasar-kantin'"
            class="w-48 h-48 md:w-64 md:h-64 text-brand-600"
            viewBox="0 0 200 200"
            fill="currentColor"
        >
            <path d="M100 40 C140 40 170 70 170 110 C170 150 140 180 100 180 C60 180 30 150 30 110 C30 70 60 40 100 40 Z" fill-opacity="0.2" />
            <path d="M100 20 Q120 40 100 60 Q80 40 100 20 Z" />
            <circle cx="100" cy="110" r="25" fill-opacity="0.1" />
        </svg>

        <!-- 7. Tema Fokus Ujian: Kosong / Bersih tanpa ornamen -->
        <div v-else-if="currentTheme === 'fokus-ujian'" class="hidden" />
    </div>
</template>

