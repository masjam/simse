<script setup>
defineProps({
    title: {
        type: String,
        default: 'Tidak Ada Data',
    },
    description: {
        type: String,
        default: 'Belum ada data untuk ditampilkan saat ini.',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 text-center max-w-sm mx-auto">
        <div class="w-16 h-16 rounded-full bg-surface-sunken flex items-center justify-center text-ink-muted mb-4 border border-line">
            <slot name="icon">
                <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </slot>
        </div>

        <h4 class="font-display font-semibold text-base text-ink">
            {{ title }}
        </h4>
        <p class="text-xs text-ink-muted mt-1 leading-relaxed">
            {{ description }}
        </p>

        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>

