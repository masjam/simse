<script setup>
import { computed } from 'vue';
import AppInput from '@/Components/ui/AppInput.vue';

const props = defineProps({
    question: { type: Object, required: true },
    modelValue: { type: [Array, Object], default: () => [] },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const blanksCount = computed(() => {
    return Math.max(1, props.question.metadata?.blanks_count || 1);
});

const values = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue;
    }
    return Object.values(props.modelValue || {});
});

const updateBlank = (index, val) => {
    const updated = [...values.value];
    while (updated.length < blanksCount.value) {
        updated.push('');
    }
    updated[index] = val;
    emit('update:modelValue', updated);
};
</script>

<template>
    <div class="space-y-4">
        <div class="px-3 py-2 bg-brand-50 border border-brand-200 rounded-control text-xs text-brand-800 flex items-center gap-2">
            <span class="font-bold">Petunjuk:</span>
            <span>Ketikkan kata atau angka yang tepat untuk melengkapi bagian yang rumpang di bawah ini.</span>
        </div>

        <div class="space-y-3">
            <div
                v-for="idx in blanksCount"
                :key="idx"
                class="border border-line rounded-card bg-surface p-3.5 space-y-2"
            >
                <div class="flex items-center gap-2 text-xs font-bold text-ink">
                    <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center font-mono text-[10px]">
                        {{ idx }}
                    </span>
                    <span>Jawaban Bagian Rumpang [{{ idx }}]</span>
                </div>

                <AppInput
                    :model-value="values[idx - 1] || ''"
                    placeholder="Ketik jawaban di sini..."
                    @update:model-value="updateBlank(idx - 1, $event)"
                />
            </div>
        </div>
    </div>
</template>
