<script setup>
import { ref } from 'vue';

defineProps({
    accept: {
        type: String,
        default: '*/*',
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

const emit = defineEmits(['change', 'update:modelValue']);
const inputRef = ref(null);

function handleFileChange(event) {
    const file = event.target.files?.[0] || null;
    emit('change', file);
    emit('update:modelValue', file);
}

function click() {
    inputRef.value?.click();
}

function reset() {
    if (inputRef.value) {
        inputRef.value.value = '';
    }
}

defineExpose({
    click,
    reset,
    input: inputRef,
});
</script>

<template>
    <div class="relative w-full">
        <input
            :id="id"
            ref="inputRef"
            type="file"
            :accept="accept"
            :disabled="disabled"
            @change="handleFileChange"
            class="block w-full text-xs text-ink-muted file:mr-3 file:py-2 file:px-3 file:rounded-control file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer border border-line rounded-control p-1 bg-surface-raised focus:ring-2 focus:ring-focus focus:outline-none"
        />
    </div>
</template>

