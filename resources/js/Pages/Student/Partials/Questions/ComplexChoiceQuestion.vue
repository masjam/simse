<script setup>
const props = defineProps({
    question: { type: Object, required: true },
    modelValue: { type: Array, default: () => [] },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const toggleOption = (id) => {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const index = current.indexOf(id);
    if (index > -1) {
        current.splice(index, 1);
    } else {
        current.push(id);
    }
    emit('update:modelValue', current);
};

const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
</script>

<template>
    <div class="space-y-3">
        <div class="px-3 py-2 bg-brand-50 border border-brand-200 rounded-control text-xs text-brand-800 flex items-center gap-2">
            <span class="font-bold">Info:</span>
            <span>Anda dapat memilih lebih dari satu jawaban yang benar (Ganda Kompleks).</span>
        </div>

        <div
            v-for="(option, idx) in question.options"
            :key="option.id"
            @click="toggleOption(option.id)"
            :class="[
                'border rounded-card cursor-pointer transition-all flex items-start gap-3.5 select-none',
                childMode ? 'p-4 text-base' : 'p-3.5 text-sm',
                modelValue.includes(option.id)
                    ? 'border-brand-600 bg-brand-50/60 ring-2 ring-brand-600/30'
                    : 'border-line bg-surface hover:bg-surface-raised/60'
            ]"
        >
            <div
                :class="[
                    'rounded-control flex items-center justify-center font-bold shrink-0 transition-colors',
                    childMode ? 'w-8 h-8 text-sm' : 'w-6 h-6 text-xs',
                    modelValue.includes(option.id)
                        ? 'bg-brand-600 text-white shadow-sm'
                        : 'border border-line bg-surface-raised text-ink-muted'
                ]"
            >
                <span v-if="modelValue.includes(option.id)">✓</span>
                <span v-else class="text-[10px] font-mono">{{ letters[idx] }}</span>
            </div>

            <div class="flex-1 space-y-2 pt-0.5">
                <span :class="['font-medium block leading-relaxed', modelValue.includes(option.id) ? 'text-brand-900 font-semibold' : 'text-ink']">
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

