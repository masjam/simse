<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right', // 'right' | 'left'
    },
    width: {
        type: String,
        default: '48', // '48' | '56' | '64'
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-surface',
    },
});

const open = ref(false);

function closeOnEscape(e) {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const alignmentClasses = {
    left: 'origin-top-left left-0',
    right: 'origin-top-right right-0',
}[props.align] || 'origin-top-right right-0';

const widthClasses = {
    48: 'w-48',
    56: 'w-56',
    64: 'w-64',
}[props.width] || 'w-48';
</script>

<template>
    <div class="relative inline-block text-left">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Full screen background click listener -->
        <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 rounded-control shadow-pop border border-line"
                :class="[alignmentClasses, widthClasses]"
                @click="open = false"
            >
                <div class="rounded-control ring-1 ring-black ring-opacity-5" :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

