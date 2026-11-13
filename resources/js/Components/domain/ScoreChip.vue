<script setup>
import { computed } from 'vue';

const props = defineProps({
    score: {
        type: [Number, String],
        required: true,
    },
    predicate: {
        type: String,
        default: null, // 'A' | 'B' | 'C' | 'D'
    },
    showPredicate: {
        type: Boolean,
        default: true,
    },
});

const numScore = computed(() => Number(props.score));

const calculatedPredicate = computed(() => {
    if (props.predicate) return props.predicate;
    const n = numScore.value;
    if (n >= 90) return 'A';
    if (n >= 80) return 'B';
    if (n >= 70) return 'C';
    return 'D';
});

const badgeClasses = computed(() => {
    const p = calculatedPredicate.value;
    switch (p) {
        case 'A':
            return 'bg-status-success-bg text-status-success-fg border-status-success-line';
        case 'B':
            return 'bg-brand-50 text-brand-800 border-brand-200';
        case 'C':
            return 'bg-status-warning-bg text-status-warning-fg border-status-warning-line';
        case 'D':
        default:
            return 'bg-status-danger-bg text-status-danger-fg border-status-danger-line';
    }
});
</script>

<template>
    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-control border text-xs font-bold font-numeric select-none" :class="badgeClasses">
        <span>{{ score }}</span>
        <span v-if="showPredicate" class="px-1 py-0.2 rounded bg-surface text-ink text-[10px] uppercase">
            {{ calculatedPredicate }}
        </span>
    </div>
</template>

