<script setup>
import { computed } from 'vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';

const props = defineProps({
    question: { type: Object, required: true },
    modelValue: { type: String, default: '' },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const wordCount = computed(() => {
    const text = (props.modelValue || '').trim();
    return text ? text.split(/\s+/).length : 0;
});

const charCount = computed(() => {
    return (props.modelValue || '').length;
});
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between text-xs text-ink-muted">
            <span>Uraikan jawaban Anda secara lengkap dan jelas:</span>
            <div class="flex items-center gap-3 font-mono">
                <span>{{ wordCount }} kata</span>
                <span>{{ charCount }} / 5000 karakter</span>
            </div>
        </div>

        <AppTextarea
            :model-value="modelValue"
            :rows="childMode ? 8 : 6"
            placeholder="Tuliskan jawaban uraian/esai di sini..."
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>
