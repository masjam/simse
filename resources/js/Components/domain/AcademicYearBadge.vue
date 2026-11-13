<script setup>
defineProps({
    year: {
        type: String,
        default: '2026/2027',
    },
    semester: {
        type: String,
        default: 'Ganjil',
    },
});
</script>

<template>
    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-800 border border-brand-200 select-none">
        <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse" />
        <span>TP {{ year }} • Semester {{ semester }}</span>
    </div>
</template>

