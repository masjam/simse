<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: {
        type: Number,
        default: 0,
    },
    max: {
        type: Number,
        default: 100,
    },
    color: {
        type: String,
        default: 'brand', // 'brand' | 'accent' | 'success' | 'danger'
    },
    height: {
        type: String,
        default: 'h-2',
    },
    showLabel: {
        type: Boolean,
        default: false,
    },
});

const percent = computed(() => {
    if (props.max <= 0) return 0;
    return Math.min(100, Math.max(0, Math.round((props.value / props.max) * 100)));
});

const barClasses = {
    brand: 'bg-brand-600',
    accent: 'bg-accent-600',
    success: 'bg-status-success-fg',
    danger: 'bg-status-danger-fg',
};
</script>

<template>
    <div class="w-full">
        <div v-if="showLabel" class="flex justify-between items-center text-xs font-semibold text-ink-muted mb-1">
            <span>Progress</span>
            <span class="font-numeric">{{ percent }}%</span>
        </div>
        <div class="w-full rounded-full bg-surface-sunken overflow-hidden border border-line" :class="height">
            <div
                class="h-full transition-all duration-300 rounded-full"
                :class="barClasses[color] || barClasses.brand"
                :style="{ width: `${percent}%` }"
            />
        </div>
    </div>
</template>

