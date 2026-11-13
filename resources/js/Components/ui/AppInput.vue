<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
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
    <div class="relative w-full">
        <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-ink-muted">
            <slot name="prefix" />
        </div>

        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :class="[
                'w-full min-h-[44px] rounded-control bg-surface text-ink placeholder:text-ink-muted border transition-all duration-150 outline-none focus:ring-2 disabled:bg-surface-sunken disabled:cursor-not-allowed text-sm',
                $slots.prefix ? 'pl-10' : 'pl-3.5',
                $slots.suffix ? 'pr-10' : 'pr-3.5',
                borderClasses,
            ]"
            @input="emit('update:modelValue', $event.target.value)"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
        />

        <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3 flex items-center text-ink-muted">
            <slot name="suffix" />
        </div>
    </div>
</template>

