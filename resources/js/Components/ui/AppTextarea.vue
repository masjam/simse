<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    rows: {
        type: Number,
        default: 3,
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    invalid: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const borderClasses = computed(() => {
    if (props.invalid) {
        return 'border-status-danger-line focus:border-status-danger-fg ring-status-danger-fg';
    }
    return 'border-line focus:border-brand-600 ring-focus';
});
</script>

<template>
    <textarea
        :id="id"
        :rows="rows"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :class="[
            'w-full rounded-control bg-surface text-ink placeholder:text-ink-muted border p-3 transition-all duration-150 outline-none focus:ring-2 disabled:bg-surface-sunken disabled:cursor-not-allowed text-sm',
            borderClasses,
        ]"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
    />
</template>

