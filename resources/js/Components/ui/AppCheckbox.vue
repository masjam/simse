<script setup>
const props = defineProps({
    modelValue: {
        type: [Boolean, Array],
        default: false,
    },
    value: {
        type: [String, Number, Boolean],
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

function handleChange(e) {
    if (Array.isArray(props.modelValue)) {
        const set = new Set(props.modelValue);
        if (e.target.checked) {
            set.add(props.value);
        } else {
            set.delete(props.value);
        }
        const updated = Array.from(set);
        emit('update:modelValue', updated);
        emit('change', updated);
    } else {
        emit('update:modelValue', e.target.checked);
        emit('change', e.target.checked);
    }
}

function isChecked() {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value);
    }
    return Boolean(props.modelValue);
}
</script>

<template>
    <label :for="id" class="inline-flex items-center gap-2.5 cursor-pointer select-none text-sm text-ink" :class="{ 'opacity-50 cursor-not-allowed': disabled }">
        <input
            :id="id"
            type="checkbox"
            :checked="isChecked()"
            :disabled="disabled"
            class="w-5 h-5 rounded-control text-brand-600 border-line bg-surface ring-focus focus:ring-2 transition cursor-pointer disabled:cursor-not-allowed"
            @change="handleChange"
        />
        <span v-if="label || $slots.default">
            <slot>{{ label }}</slot>
        </span>
    </label>
</template>

