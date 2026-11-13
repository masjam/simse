<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: '',
    },
    options: {
        type: Array,
        default: () => [], // Array of { value, label } or primitive strings
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...',
    },
    disabled: {
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

const emit = defineEmits(['update:modelValue', 'change']);

const normalizedOptions = computed(() => {
    return props.options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
            return {
                value: opt.value ?? opt.id,
                label: opt.label ?? opt.name ?? opt.value,
            };
        }
        return { value: opt, label: opt };
    });
});

const borderClasses = computed(() => {
    if (props.invalid) {
        return 'border-status-danger-line focus:border-status-danger-fg ring-status-danger-fg';
    }
    return 'border-line focus:border-brand-600 ring-focus';
});
</script>

<template>
    <div class="relative w-full">
        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :class="[
                'w-full min-h-[44px] rounded-control bg-surface text-ink border pl-3.5 pr-10 transition-all duration-150 outline-none focus:ring-2 disabled:bg-surface-sunken disabled:cursor-not-allowed text-sm appearance-none',
                borderClasses,
            ]"
            @change="emit('update:modelValue', $event.target.value); emit('change', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled :selected="!modelValue">
                {{ placeholder }}
            </option>
            <option
                v-for="opt in normalizedOptions"
                :key="String(opt.value)"
                :value="opt.value"
            >
                {{ opt.label }}
            </option>
        </select>

        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-ink-muted">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</template>

