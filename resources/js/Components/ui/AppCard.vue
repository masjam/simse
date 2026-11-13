<script setup>
defineProps({
    title: {
        type: String,
        default: null,
    },
    subtitle: {
        type: String,
        default: null,
    },
    variant: {
        type: String,
        default: 'surface',
        validator: (v) => ['surface', 'raised', 'sunken'].includes(v),
    },
    padding: {
        type: String,
        default: 'md',
        validator: (v) => ['none', 'sm', 'md', 'lg'].includes(v),
    },
});

const paddingClasses = {
    none: 'p-0',
    sm: 'p-3 sm:p-4',
    md: 'p-4 sm:p-6',
    lg: 'p-6 sm:p-8',
};

const variantClasses = {
    surface: 'bg-surface border border-line shadow-card',
    raised: 'bg-surface-raised border border-line shadow-pop',
    sunken: 'bg-surface-sunken border border-line',
};
</script>

<template>
    <div class="rounded-card transition-all" :class="[variantClasses[variant], paddingClasses[padding]]">
        <header v-if="title || subtitle || $slots.header || $slots.actions" class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-line">
            <div>
                <slot name="header">
                    <h3 v-if="title" class="font-display font-semibold text-base text-ink">
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-ink-muted mt-0.5">
                        {{ subtitle }}
                    </p>
                </slot>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </header>

        <div>
            <slot />
        </div>

        <footer v-if="$slots.footer" class="mt-4 pt-3 border-t border-line">
            <slot name="footer" />
        </footer>
    </div>
</template>

