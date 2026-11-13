<script setup>
import { useToast } from '@/composables/useToast';
import Toast from './Toast.vue';

const { toasts, removeToast } = useToast();
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed top-4 right-4 z-50 flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0"
            aria-live="polite"
        >
            <TransitionGroup
                enter-active-class="transform ease-out duration-200 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <Toast
                    v-for="t in toasts"
                    :key="t.id"
                    :toast="t"
                    @close="removeToast(t.id)"
                />
            </TransitionGroup>
        </div>
    </Teleport>
</template>

