<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: {
        type: String,
        default: 'User',
    },
    src: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md', // 'sm' | 'md' | 'lg' | 'xl'
    },
});

const initials = computed(() => {
    if (!props.name) return 'U';
    const parts = props.name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const sizeClasses = {
    sm: 'w-7 h-7 text-xs',
    md: 'w-10 h-10 text-sm',
    lg: 'w-12 h-12 text-base',
    xl: 'w-16 h-16 text-xl',
};
</script>

<template>
    <div
        class="rounded-full flex-shrink-0 flex items-center justify-center font-bold uppercase select-none overflow-hidden bg-brand-100 text-brand-800 border border-brand-200"
        :class="sizeClasses[size] || sizeClasses.md"
    >
        <img
            v-if="src"
            :src="src"
            :alt="name"
            class="w-full h-full object-cover"
        />
        <span v-else>{{ initials }}</span>
    </div>
</template>

