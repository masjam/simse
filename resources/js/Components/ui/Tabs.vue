<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    tabs: {
        type: Array,
        required: true, // Array of { id, label, icon, badge }
    },
    variant: {
        type: String,
        default: 'line', // 'line' | 'pills'
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

function selectTab(id) {
    emit('update:modelValue', id);
    emit('change', id);
}
</script>

<template>
    <div class="w-full">
        <div
            v-if="variant === 'pills'"
            class="flex items-center gap-1.5 p-1 rounded-control bg-surface-sunken overflow-x-auto no-scrollbar"
        >
            <button
                v-for="t in tabs"
                :key="t.id"
                type="button"
                :class="[
                    'flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-control transition whitespace-nowrap min-h-[38px]',
                    modelValue === t.id
                        ? 'bg-surface text-ink shadow-sm'
                        : 'text-ink-muted hover:text-ink',
                ]"
                @click="selectTab(t.id)"
            >
                <component :is="t.icon" v-if="t.icon" class="w-4 h-4" />
                <span>{{ t.label }}</span>
                <span
                    v-if="t.badge !== undefined"
                    class="px-1.5 py-0.5 text-xs font-bold rounded-full"
                    :class="modelValue === t.id ? 'bg-brand-50 text-brand-700' : 'bg-surface text-ink-muted'"
                >
                    {{ t.badge }}
                </span>
            </button>
        </div>

        <div
            v-else
            class="flex items-center gap-4 sm:gap-6 border-b border-line overflow-x-auto no-scrollbar"
        >
            <button
                v-for="t in tabs"
                :key="t.id"
                type="button"
                :class="[
                    'flex items-center gap-2 pb-3 pt-1 text-sm font-semibold transition border-b-2 whitespace-nowrap min-h-[44px]',
                    modelValue === t.id
                        ? 'border-brand-600 text-brand-600'
                        : 'border-transparent text-ink-muted hover:text-ink',
                ]"
                @click="selectTab(t.id)"
            >
                <component :is="t.icon" v-if="t.icon" class="w-4 h-4" />
                <span>{{ t.label }}</span>
                <span
                    v-if="t.badge !== undefined"
                    class="px-1.5 py-0.5 text-xs font-bold rounded-full bg-surface-sunken text-ink-muted"
                >
                    {{ t.badge }}
                </span>
            </button>
        </div>

        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>

