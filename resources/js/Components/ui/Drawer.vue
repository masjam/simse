<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    position: {
        type: String,
        default: 'right', // 'right' | 'left'
    },
    title: {
        type: String,
        default: null,
    },
    closeable: {
        type: Boolean,
        default: true,
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
            document.body.style.overflow = val ? 'hidden' : null;
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
            <div v-if="show" class="fixed inset-0 z-50 overflow-hidden">
                <div
                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                    aria-hidden="true"
                    @click="close"
                />

                <div
                    class="fixed inset-y-0 max-w-full flex"
                    :class="position === 'left' ? 'left-0' : 'right-0'"
                >
                    <div class="w-screen max-w-md bg-surface border-line shadow-pop flex flex-col p-5 sm:p-6 overflow-y-auto" :class="position === 'left' ? 'border-r' : 'border-l'">
                        <header class="flex items-center justify-between pb-3 mb-4 border-b border-line">
                            <slot name="title">
                                <h3 class="font-display font-semibold text-lg text-ink">
                                    {{ title }}
                                </h3>
                            </slot>
                            <button
                                v-if="closeable"
                                type="button"
                                class="p-1.5 rounded-control text-ink-muted hover:text-ink hover:bg-surface-raised transition"
                                aria-label="Tutup"
                                @click="close"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </header>

                        <div class="flex-1">
                            <slot />
                        </div>

                        <footer v-if="$slots.footer" class="mt-6 pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <slot name="footer" />
                        </footer>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

