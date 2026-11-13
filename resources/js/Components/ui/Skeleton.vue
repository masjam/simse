<script setup>
defineProps({
    type: {
        type: String,
        default: 'block', // 'block' | 'row' | 'table' | 'circle'
    },
    rows: {
        type: Number,
        default: 3,
    },
    height: {
        type: String,
        default: 'h-4',
    },
    width: {
        type: String,
        default: 'w-full',
    },
});
</script>

<template>
    <!-- Circle Skeleton -->
    <div
        v-if="type === 'circle'"
        class="animate-pulse rounded-full bg-surface-sunken flex-shrink-0"
        :class="[height, width]"
    />

    <!-- Table Skeleton -->
    <div v-else-if="type === 'table'" class="w-full space-y-3">
        <div class="h-10 bg-surface-sunken rounded-control animate-pulse w-full border border-line" />
        <div
            v-for="i in rows"
            :key="i"
            class="h-12 bg-surface-sunken/60 rounded-control animate-pulse w-full border border-line"
        />
    </div>

    <!-- Rows Skeleton -->
    <div v-else-if="type === 'row'" class="w-full space-y-2.5">
        <div
            v-for="i in rows"
            :key="i"
            class="animate-pulse rounded-control bg-surface-sunken"
            :class="[height, i === rows ? 'w-2/3' : 'w-full']"
        />
    </div>

    <!-- Default Block Skeleton -->
    <div
        v-else
        class="animate-pulse rounded-control bg-surface-sunken"
        :class="[height, width]"
    />
</template>

