<script setup>
defineProps({
    question: { type: Object, required: true },
    modelValue: { type: [String, Number], default: null },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-3">
        <div class="text-xs text-ink-muted">
            Tentukan apakah pernyataan pada soal di atas bernilai <strong>Benar</strong> atau <strong>Salah</strong>:
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
                v-for="option in question.options"
                :key="option.id"
                @click="emit('update:modelValue', option.id)"
                :class="[
                    'border rounded-card cursor-pointer transition-all flex items-center justify-center gap-3 select-none text-center font-bold',
                    childMode ? 'p-6 text-xl' : 'p-4 text-base',
                    modelValue === option.id
                        ? (option.content.toLowerCase().includes('benar')
                            ? 'border-status-success-line bg-status-success-bg text-status-success-fg ring-2 ring-focus'
                            : 'border-status-danger-line bg-status-danger-bg text-status-danger-fg ring-2 ring-focus')
                        : 'border-line bg-surface hover:bg-surface-raised/60 text-ink'
                ]"
            >
                <span class="text-xl">
                    {{ option.content.toLowerCase().includes('benar') ? '👍' : '👎' }}
                </span>
                <span>{{ option.content }}</span>
            </div>
        </div>
    </div>
</template>
