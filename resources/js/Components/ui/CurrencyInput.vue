<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: 0,
    },
    placeholder: {
        type: String,
        default: '0',
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

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const displayValue = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
        return '';
    }
    const cleanNum = Number(String(props.modelValue).replace(/\D/g, ''));
    if (isNaN(cleanNum) || cleanNum === 0) return '';
    return new Intl.NumberFormat('id-ID').format(cleanNum);
});

function handleInput(e) {
    const rawVal = e.target.value.replace(/\D/g, '');
    const num = rawVal ? Number(rawVal) : 0;
    emit('update:modelValue', num);
}

const borderClasses = computed(() => {
    if (props.invalid) {
        return 'border-status-danger-line focus:border-status-danger-fg ring-status-danger-fg';
    }
    return 'border-line focus:border-brand-600 ring-focus';
});
</script>

<template>
    <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-semibold text-ink-muted">
            Rp
        </div>

        <input
            :id="id"
            type="text"
            inputmode="numeric"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="[
                'w-full min-h-[44px] rounded-control bg-surface text-ink placeholder:text-ink-muted border pl-11 pr-3.5 transition-all duration-150 outline-none focus:ring-2 disabled:bg-surface-sunken disabled:cursor-not-allowed text-sm font-numeric',
                borderClasses,
            ]"
            @input="handleInput"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
        />
    </div>
</template>

