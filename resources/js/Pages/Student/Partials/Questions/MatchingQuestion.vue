<script setup>
import { computed } from 'vue';
import AppSelect from '@/Components/ui/AppSelect.vue';

const props = defineProps({
    question: { type: Object, required: true },
    modelValue: { type: Object, default: () => ({}) },
    childMode: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const pairs = computed(() => props.modelValue || {});

const updatePair = (optionId, value) => {
    const updated = { ...pairs.value, [optionId]: value };
    emit('update:modelValue', updated);
};
</script>

<template>
    <div class="space-y-4">
        <div class="px-3 py-2 bg-brand-50 border border-brand-200 rounded-control text-xs text-brand-800 flex items-center gap-2">
            <span class="font-bold">Petunjuk:</span>
            <span>Pilih pasangan yang paling sesuai untuk setiap premis pada kolom sebelah kanan.</span>
        </div>

        <div class="space-y-3">
            <div
                v-for="(option, idx) in question.options"
                :key="option.id"
                class="border border-line rounded-card bg-surface p-3.5 space-y-2.5 sm:space-y-0 sm:flex sm:items-center sm:justify-between sm:gap-4 shadow-sm"
            >
                <!-- Premise (Left) -->
                <div class="flex items-start gap-2.5 flex-1">
                    <span class="w-6 h-6 rounded-full bg-surface-raised border border-line text-xs font-mono font-bold flex items-center justify-center shrink-0 mt-0.5">
                        {{ idx + 1 }}
                    </span>
                    <div>
                        <span :class="['font-medium text-ink block', childMode ? 'text-base' : 'text-sm']">
                            {{ option.content }}
                        </span>
                        <img
                            v-if="option.image_url"
                            :src="option.image_url"
                            alt="Lampiran Premis"
                            class="max-w-xs max-h-32 rounded-control border border-line object-contain bg-white p-1 mt-1.5"
                        />
                    </div>
                </div>

                <!-- Match Dropdown (Right) - Touch Accessible -->
                <div class="sm:w-64 shrink-0">
                    <label class="sr-only">Pilih pasangan untuk {{ option.content }}</label>
                    <AppSelect
                        :model-value="pairs[option.id] || ''"
                        :options="question.pair_choices || []"
                        placeholder="-- Pilih Pasangan --"
                        @update:model-value="updatePair(option.id, $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
