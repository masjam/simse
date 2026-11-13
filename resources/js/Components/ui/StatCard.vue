<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
    trend: {
        type: [String, Number],
        default: null,
    },
    trendUp: {
        type: Boolean,
        default: true,
    },
    iconBg: {
        type: String,
        default: 'brand', // 'brand' | 'accent' | 'success' | 'warning' | 'danger'
    },
});

const iconBgClasses = {
    brand: 'bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300',
    accent: 'bg-accent-50 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300',
    success: 'bg-status-success-bg text-status-success-fg',
    warning: 'bg-status-warning-bg text-status-warning-fg',
    danger: 'bg-status-danger-bg text-status-danger-fg',
};
</script>

<template>
    <div class="rounded-card bg-surface border border-line shadow-card p-4 sm:p-5 flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold tracking-wider uppercase text-ink-muted truncate">
                {{ title }}
            </p>
            <p class="mt-2 text-2xl font-bold font-numeric text-ink truncate">
                {{ value }}
            </p>
            <div v-if="subtitle || trend" class="mt-1.5 flex items-center gap-2 text-xs">
                <span
                    v-if="trend !== null"
                    :class="trendUp ? 'text-status-success-fg font-semibold' : 'text-status-danger-fg font-semibold'"
                    class="inline-flex items-center"
                >
                    {{ trendUp ? '↑' : '↓' }} {{ trend }}
                </span>
                <span v-if="subtitle" class="text-ink-muted truncate">
                    {{ subtitle }}
                </span>
            </div>
        </div>

        <div
            v-if="$slots.icon"
            class="flex-shrink-0 w-12 h-12 rounded-control flex items-center justify-center p-2.5"
            :class="iconBgClasses[iconBg] || iconBgClasses.brand"
        >
            <slot name="icon" />
        </div>
    </div>
</template>

