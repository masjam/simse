<script setup>
defineProps({
    label: {
        type: String,
        default: null,
    },
    htmlFor: {
        type: String,
        default: null,
    },
    hint: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="w-full flex flex-col gap-1.5">
        <label
            v-if="label || $slots.label"
            :for="htmlFor"
            class="text-xs font-semibold tracking-wide uppercase text-ink-muted flex items-center gap-1 select-none"
        >
            <slot name="label">{{ label }}</slot>
            <span v-if="required" class="text-status-danger-fg" aria-hidden="true">*</span>
        </label>

        <slot :invalid="Boolean(error)" />

        <p v-if="error" class="text-xs font-medium text-status-danger-fg flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ error }}</span>
        </p>
        <p v-else-if="hint || $slots.hint" class="text-xs text-ink-muted">
            <slot name="hint">{{ hint }}</slot>
        </p>
    </div>
</template>

