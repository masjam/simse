<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl'].includes(v),
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    title: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

function close() {
    if (props.closeable) {
        emit('close');
    }
}

function handleKeydown(e) {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
}

watch(
    () => props.show,
    (val) => {
        if (typeof document !== 'undefined') {
            if (val) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = null;
            }
        }
    }
);

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    if (typeof document !== 'undefined') {
        document.body.style.overflow = null;
    }
});

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth];
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                    aria-hidden="true"
                    @click="close"
                />

                <!-- Dialog Box -->
                <div
                    class="relative w-full rounded-card bg-surface border border-line shadow-pop overflow-hidden transform transition-all p-5 sm:p-6"
                    :class="maxWidthClass"
                    role="dialog"
                    aria-modal="true"
                >
                    <header v-if="title || $slots.title" class="flex items-center justify-between pb-3 mb-4 border-b border-line">
                        <slot name="title">
                            <h3 class="font-display font-semibold text-lg text-ink">
                                {{ title }}
                            </h3>
                        </slot>
                        <button
                            v-if="closeable"
                            type="button"
                            class="p-1 rounded-control text-ink-muted hover:text-ink hover:bg-surface-raised transition"
                            aria-label="Tutup"
                            @click="close"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </header>

                    <div>
                        <slot />
                    </div>

                    <footer v-if="$slots.footer" class="mt-6 pt-4 border-t border-line flex items-center justify-end gap-2.5">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

