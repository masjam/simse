<script setup>
defineProps({
    question: { type: Object, required: true },
    modelValue: { type: [String, Number], default: null },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(option, idx) in question.options"
            :key="option.id"
            @click="emit('update:modelValue', option.id)"
            :class="[
                'border rounded-card cursor-pointer transition-all flex items-start gap-3.5 select-none',
                childMode ? 'p-4 text-base' : 'p-3.5 text-sm',
                modelValue === option.id
                    ? 'border-brand-600 bg-brand-50/60 ring-2 ring-brand-600/30'
                    : 'border-line bg-surface hover:bg-surface-raised/60'
            ]"
        >
            <div
                :class="[
                    'rounded-full flex items-center justify-center font-bold font-mono shrink-0 transition-colors',
                    childMode ? 'w-10 h-10 text-base' : 'w-8 h-8 text-xs',
                    modelValue === option.id
                        ? 'bg-brand-600 text-white shadow-sm'
                        : 'bg-surface-raised text-ink-muted border border-line'
                ]"
            >
                {{ letters[idx] || (idx + 1) }}
            </div>

            <div class="flex-1 space-y-2 pt-0.5">
                <span :class="['font-medium block leading-relaxed', modelValue === option.id ? 'text-brand-900 font-semibold' : 'text-ink']">
                    {{ option.content }}
                </span>
                <img
                    v-if="option.image_url"
                    :src="option.image_url"
                    alt="Lampiran Opsi"
                    class="max-w-xs max-h-40 rounded-control border border-line object-contain bg-white p-1"
                />
            </div>
        </div>
    </div>
</template>

