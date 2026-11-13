<script setup>
import { ref } from 'vue';

defineProps({
    content: {
        type: String,
        default: '',
    },
    position: {
        type: String,
        default: 'top', // 'top' | 'bottom' | 'left' | 'right'
    },
});

const isVisible = ref(false);
</script>

<template>
    <div
        class="relative inline-flex"
        @mouseenter="isVisible = true"
        @mouseleave="isVisible = false"
        @focusin="isVisible = true"
        @focusout="isVisible = false"
    >
        <slot />

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isVisible && content"
                role="tooltip"
                class="absolute z-50 px-2 py-1 text-[11px] font-medium text-white bg-slate-900 rounded-control shadow-pop whitespace-nowrap pointer-events-none"
                :class="{
                    'bottom-full left-1/2 -translate-x-1/2 mb-1.5': position === 'top',
                    'top-full left-1/2 -translate-x-1/2 mt-1.5': position === 'bottom',
                    'right-full top-1/2 -translate-y-1/2 mr-1.5': position === 'left',
                    'left-full top-1/2 -translate-y-1/2 ml-1.5': position === 'right',
                }"
            >
                {{ content }}
            </div>
        </Transition>
    </div>
</template>

