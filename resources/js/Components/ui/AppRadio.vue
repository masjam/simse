<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: null,
    },
    value: {
        type: [String, Number, Boolean],
        required: true,
    },
    name: {
        type: String,
        default: null,
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

function handleChange() {
    emit('update:modelValue', props.value);
    emit('change', props.value);
}
</script>

<template>
    <label
        :for="id"
        class="inline-flex items-center gap-2 cursor-pointer select-none text-xs text-ink"
        :class="{ 'opacity-60 cursor-not-allowed': disabled }"
    >
        <input
            :id="id"
            type="radio"
            :name="name"
            :value="value"
            :checked="modelValue === value"
            :disabled="disabled"
            class="w-4 h-4 text-brand-600 bg-surface border-line focus:ring-brand-500 focus:ring-2 focus:ring-offset-1 rounded-full cursor-pointer disabled:cursor-not-allowed transition"
            @change="handleChange"
        />
        <slot>
            <span v-if="label">{{ label }}</span>
        </slot>
    </label>
</template>

