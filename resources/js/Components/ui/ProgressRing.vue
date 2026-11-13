<script setup>
import { computed } from 'vue';

const props = defineProps({
    percent: {
        type: Number,
        default: 0,
    },
    size: {
        type: Number,
        default: 48,
    },
    strokeWidth: {
        type: Number,
        default: 4,
    },
    color: {
        type: String,
        default: 'brand', // 'brand' | 'accent' | 'success' | 'danger'
    },
});

const radius = computed(() => (props.size - props.strokeWidth) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const strokeDashoffset = computed(() => {
    const p = Math.min(100, Math.max(0, props.percent));
    return circumference.value - (p / 100) * circumference.value;
});

const strokeClasses = {
    brand: 'text-brand-600',
    accent: 'text-accent-600',
    success: 'text-status-success-fg',
    danger: 'text-status-danger-fg',
};
</script>

<template>
    <div class="relative inline-flex items-center justify-center flex-shrink-0" :style="{ width: `${size}px`, height: `${size}px` }">
        <svg class="transform -rotate-90" :width="size" :height="size">
            <circle
                class="text-surface-sunken"
                stroke="currentColor"
                :stroke-width="strokeWidth"
                fill="transparent"
                :r="radius"
                :cx="size / 2"
                :cy="size / 2"
            />
            <circle
                class="transition-all duration-500 ease-out"
                :class="strokeClasses[color] || strokeClasses.brand"
                stroke="currentColor"
                :stroke-width="strokeWidth"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="strokeDashoffset"
                stroke-linecap="round"
                fill="transparent"
                :r="radius"
                :cx="size / 2"
                :cy="size / 2"
            />
        </svg>
        <span class="absolute text-[11px] font-bold font-numeric text-ink">
            {{ Math.round(percent) }}%
        </span>
    </div>
</template>

