<script setup>
import QrcodeVue from 'qrcode.vue';

defineProps({
    value: {
        type: String,
        required: true,
    },
    size: {
        type: Number,
        default: 160,
    },
    level: {
        type: String,
        default: 'M', // 'L' | 'M' | 'Q' | 'H'
    },
    caption: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="inline-flex flex-col items-center p-3 rounded-card bg-white border border-line shadow-sm">
        <QrcodeVue
            :value="value"
            :size="size"
            :level="level"
            render-as="svg"
            class="rounded-control"
        />
        <p v-if="caption" class="text-xs font-mono font-bold text-slate-800 mt-2 text-center select-all">
            {{ caption }}
        </p>
    </div>
</template>

