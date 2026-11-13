<script setup>
defineProps({
    hasActiveFilters: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['reset']);
</script>

<template>
    <div class="p-3 sm:p-4 rounded-card border border-line bg-surface shadow-card flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
        <div class="flex-1 flex flex-col sm:flex-row sm:items-center flex-wrap gap-2.5">
            <slot />
        </div>

        <div v-if="hasActiveFilters || $slots.actions" class="flex items-center gap-2 flex-shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-line justify-end">
            <button
                v-if="hasActiveFilters"
                type="button"
                class="px-3 py-2 text-xs font-semibold text-ink-muted hover:text-ink transition flex items-center gap-1.5"
                @click="emit('reset')"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Reset Filter</span>
            </button>
            <slot name="actions" />
        </div>
    </div>
</template>

