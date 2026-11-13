<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    as: {
        type: String,
        default: 'button',
    },
    href: {
        type: String,
        default: null,
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'ghost', 'danger', 'accent'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    type: {
        type: String,
        default: 'button',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    iconOnly: {
        type: Boolean,
        default: false,
    },
    ariaLabel: {
        type: String,
        default: null,
    },
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-surface border border-line text-ink hover:bg-surface-raised active:bg-surface-sunken';
        case 'ghost':
            return 'bg-transparent text-ink hover:bg-surface-raised active:bg-surface-sunken';
        case 'danger':
            return 'bg-status-danger-bg border border-status-danger-line text-status-danger-fg hover:opacity-90 active:opacity-95';
        case 'accent':
            return 'bg-accent-600 text-accent-fg hover:bg-accent-700 active:bg-accent-800';
        case 'primary':
        default:
            return 'bg-brand-600 text-brand-fg hover:bg-brand-700 active:bg-brand-800 shadow-sm';
    }
});

const sizeClasses = computed(() => {
    if (props.iconOnly) {
        switch (props.size) {
            case 'sm':
                return 'w-9 h-9 p-1.5 text-xs';
            case 'lg':
                return 'w-12 h-12 p-3 text-base';
            case 'md':
            default:
                return 'w-11 h-11 p-2.5 text-sm min-h-[44px] min-w-[44px]';
        }
    }

    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs font-medium min-h-[36px]';
        case 'lg':
            return 'px-6 py-3.5 text-base font-semibold min-h-[48px]';
        case 'md':
        default:
            return 'px-4 py-2.5 text-sm font-medium min-h-[44px]';
    }
});

const isLink = computed(() => props.as === 'Link' || Boolean(props.href));
const isDisabled = computed(() => props.disabled || props.loading);
</script>

<template>
    <component
        :is="isLink ? Link : 'button'"
        :href="isLink ? href : undefined"
        :type="!isLink ? type : undefined"
        :disabled="!isLink && isDisabled ? true : undefined"
        :aria-label="ariaLabel"
        :aria-disabled="isDisabled ? 'true' : undefined"
        class="inline-flex items-center justify-center gap-2 rounded-control transition-all duration-150 outline-none ring-focus focus-visible:ring-2 disabled:opacity-50 disabled:cursor-not-allowed select-none"
        :class="[variantClasses, sizeClasses]"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-1 h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>

        <slot name="icon-left" />
        <slot />
        <slot name="icon-right" />
    </component>
</template>

