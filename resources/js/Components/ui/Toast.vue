<script setup>
import { computed } from 'vue';

const props = defineProps({
    toast: {
        type: Object,
        required: true, // { id, type, title, message }
    },
});

const emit = defineEmits(['close']);

const variantClasses = computed(() => {
    switch (props.toast.type) {
        case 'success':
            return 'bg-status-success-bg border-status-success-line text-status-success-fg';
        case 'danger':
            return 'bg-status-danger-bg border-status-danger-line text-status-danger-fg';
        case 'warning':
            return 'bg-status-warning-bg border-status-warning-line text-status-warning-fg';
        case 'info':
        default:
            return 'bg-status-info-bg border-status-info-line text-status-info-fg';
    }
});
</script>

<template>
    <div
        class="w-full max-w-sm rounded-card border shadow-pop p-3.5 flex items-start gap-3 transition-all transform pointer-events-auto"
        :class="variantClasses"
        role="alert"
    >
        <div class="flex-shrink-0 mt-0.5">
            <!-- Success icon -->
            <svg v-if="toast.type === 'success'" class="w-5 h-5 text-status-success-fg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Danger icon -->
            <svg v-else-if="toast.type === 'danger'" class="w-5 h-5 text-status-danger-fg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Warning icon -->
            <svg v-else-if="toast.type === 'warning'" class="w-5 h-5 text-status-warning-fg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <!-- Info icon -->
            <svg v-else class="w-5 h-5 text-status-info-fg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div class="flex-1 min-w-0">
            <h5 v-if="toast.title" class="text-xs font-bold leading-tight">
                {{ toast.title }}
            </h5>
            <p class="text-xs mt-0.5 leading-relaxed opacity-95">
                {{ toast.message }}
            </p>
        </div>

        <button
            type="button"
            class="flex-shrink-0 p-1 opacity-70 hover:opacity-100 transition rounded-control"
            aria-label="Tutup notifikasi"
            @click="emit('close')"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

