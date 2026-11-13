<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Cari data...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'clear']);

function clear() {
    emit('update:modelValue', '');
    emit('clear');
}
</script>

<template>
    <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-ink-muted">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <input
            type="search"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full min-h-[44px] rounded-control bg-surface text-ink placeholder:text-ink-muted border border-line pl-10 pr-9 transition-all duration-150 outline-none focus:border-brand-600 focus:ring-2 ring-focus text-sm disabled:bg-surface-sunken"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <button
            v-if="modelValue"
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-muted hover:text-ink"
            aria-label="Bersihkan pencarian"
            @click="clear"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

