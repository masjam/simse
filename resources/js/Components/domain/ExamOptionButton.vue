<script setup>
defineProps({
    selected: {
        type: Boolean,
        default: false,
    },
    letter: {
        type: String,
        required: true,
    },
    text: {
        type: String,
        required: true,
    },
    fontSize: {
        type: String,
        default: 'md',
    },
});

const emit = defineEmits(['click']);
</script>

<template>
    <button
        type="button"
        @click="emit('click')"
        class="w-full text-left p-3.5 rounded-control border transition-all flex items-center gap-3 cursor-pointer"
        :class="selected
            ? 'border-brand-600 bg-brand-50 text-brand-900 font-bold ring-2 ring-brand-500/20'
            : 'border-line bg-surface hover:bg-surface-raised text-ink'"
    >
        <span
            class="w-7 h-7 rounded-control flex items-center justify-center text-xs font-black shrink-0 font-mono"
            :class="selected
                ? 'bg-brand-600 text-brand-fg'
                : 'bg-surface-raised text-ink-muted border border-line'"
        >
            {{ letter }}
        </span>
        <span
            :class="{
                'text-xs': fontSize === 'sm',
                'text-sm': fontSize === 'md',
                'text-base': fontSize === 'lg',
            }"
        >
            {{ text }}
        </span>
    </button>
</template>

