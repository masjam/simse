<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

function toggle() {
    if (props.disabled) return;
    const newVal = !props.modelValue;
    emit('update:modelValue', newVal);
    emit('change', newVal);
}
</script>

<template>
    <div
        class="inline-flex items-center gap-3 select-none"
        :class="{ 'opacity-50 cursor-not-allowed': disabled, 'cursor-pointer': !disabled }"
        @click="toggle"
    >
        <button
            :id="id"
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out outline-none ring-focus focus-visible:ring-2 disabled:cursor-not-allowed"
            :class="modelValue ? 'bg-brand-600' : 'bg-surface-sunken border-line'"
        >
            <span
                aria-hidden="true"
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
            />
        </button>
        <span v-if="label || $slots.default" class="text-sm font-medium text-ink">
            <slot>{{ label }}</slot>
        </span>
    </div>
</template>

