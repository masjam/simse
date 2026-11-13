<script setup>
defineProps({
    currentStep: {
        type: Number,
        required: true,
    },
    steps: {
        type: Array,
        required: true, // Array of { title, description }
    },
});
</script>

<template>
    <div class="w-full">
        <ol class="flex items-center w-full justify-between relative">
            <li
                v-for="(s, idx) in steps"
                :key="idx"
                class="flex-1 relative flex flex-col items-center text-center"
            >
                <!-- Line connecting to previous step -->
                <div
                    v-if="idx > 0"
                    class="absolute top-4 -left-1/2 w-full h-0.5 -z-0"
                    :class="currentStep > idx ? 'bg-brand-600' : 'bg-line'"
                />

                <!-- Step Circle -->
                <div
                    class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                    :class="[
                        currentStep > idx + 1
                            ? 'bg-brand-600 text-brand-fg'
                            : currentStep === idx + 1
                                ? 'bg-brand-600 text-brand-fg ring-4 ring-brand-100'
                                : 'bg-surface-sunken text-ink-muted border border-line',
                    ]"
                >
                    <span v-if="currentStep > idx + 1">✓</span>
                    <span v-else>{{ idx + 1 }}</span>
                </div>

                <!-- Label -->
                <div class="mt-2 text-center">
                    <p class="text-xs font-semibold text-ink line-clamp-1">
                        {{ s.title }}
                    </p>
                    <p v-if="s.description" class="hidden sm:block text-[11px] text-ink-muted">
                        {{ s.description }}
                    </p>
                </div>
            </li>
        </ol>
    </div>
</template>

