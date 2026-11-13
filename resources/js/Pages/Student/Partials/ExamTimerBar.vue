<script setup>
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    formattedTimer: { type: String, required: true },
    answeredCount: { type: Number, required: true },
    totalQuestions: { type: Number, required: true },
    savingState: { type: String, default: 'Siap' },
    violationCount: { type: Number, default: 0 },
    maxViolations: { type: Number, default: 5 },
    isFullscreen: { type: Boolean, default: false },
    submitting: { type: Boolean, default: false },
    fontSize: { type: String, default: 'md' }, // 'sm' | 'md' | 'lg'
});

const emit = defineEmits(['toggle-fullscreen', 'submit-exam', 'change-font-size']);
</script>

<template>
    <div class="bg-surface rounded-card border border-line p-3.5 shadow-card flex flex-wrap items-center justify-between gap-3 sticky top-4 z-30">
        <!-- Timer & Answered -->
        <div class="flex items-center gap-3">
            <div class="px-3.5 py-1.5 rounded-control bg-brand-600 text-brand-fg font-mono font-black text-sm sm:text-base tracking-wider shadow-sm">
                {{ formattedTimer }}
            </div>
            <div class="text-xs">
                <p class="font-bold text-ink">
                    Terjawab: <span class="font-mono text-brand-600">{{ answeredCount }}</span> / {{ totalQuestions }}
                </p>
                <p class="text-[11px] text-ink-muted">
                    Status: <span class="font-semibold text-ink">{{ savingState }}</span>
                </p>
            </div>
        </div>

        <!-- Controls: Font Size, Violations, Fullscreen, Submit -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Font Size Toggle -->
            <div class="flex items-center bg-surface-raised rounded-control border border-line p-0.5 text-xs">
                <AppButton
                    size="sm"
                    :variant="fontSize === 'sm' ? 'primary' : 'ghost'"
                    class="!px-2 !py-0.5 !min-h-0 text-xs font-bold"
                    title="Huruf Kecil"
                    @click="emit('change-font-size', 'sm')"
                >
                    A-
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="fontSize === 'md' ? 'primary' : 'ghost'"
                    class="!px-2 !py-0.5 !min-h-0 text-xs font-bold"
                    title="Huruf Standar"
                    @click="emit('change-font-size', 'md')"
                >
                    A
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="fontSize === 'lg' ? 'primary' : 'ghost'"
                    class="!px-2 !py-0.5 !min-h-0 text-xs font-bold"
                    title="Huruf Besar"
                    @click="emit('change-font-size', 'lg')"
                >
                    A+
                </AppButton>
            </div>

            <!-- Violation Badge -->
            <span
                class="px-2.5 py-1 rounded-control text-xs font-bold"
                :class="violationCount > 0 ? 'bg-status-danger-bg text-status-danger-fg border border-status-danger-line' : 'bg-status-success-bg text-status-success-fg border border-status-success-line'"
            >
                Pelanggaran: {{ violationCount }} / {{ maxViolations }}
            </span>

            <!-- Fullscreen -->
            <AppButton
                v-if="!isFullscreen"
                size="sm"
                variant="secondary"
                @click="emit('toggle-fullscreen')"
            >
                Layar Penuh
            </AppButton>

            <!-- Submit Button -->
            <AppButton
                size="sm"
                variant="primary"
                :loading="submitting"
                @click="emit('submit-exam')"
            >
                Kumpulkan
            </AppButton>
        </div>
    </div>
</template>
