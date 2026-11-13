<script setup>
import AppSelect from '@/Components/ui/AppSelect.vue';

defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    subjects: {
        type: Array,
        default: () => [], // Array of { id, name, code }
    },
    placeholder: {
        type: String,
        default: 'Pilih Mata Pelajaran...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    invalid: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);
</script>

<template>
    <AppSelect
        :model-value="modelValue"
        :options="subjects"
        :placeholder="placeholder"
        :disabled="disabled"
        :invalid="invalid"
        @update:model-value="emit('update:modelValue', $event)"
        @change="emit('change', $event)"
    />
</template>

