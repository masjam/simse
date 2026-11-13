<script setup>
import { computed } from 'vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    date: {
        type: [String, Date, Number],
        default: null,
    },
    styleType: {
        type: String,
        default: 'panjang', // 'panjang' | 'pendek' | 'lengkap' | 'waktu' | 'bulanTahun'
    },
});

const { formatDate } = useFormat();
const formatted = computed(() => formatDate(props.date, props.styleType));
</script>

<template>
    <span class="text-ink">
        {{ formatted }}
    </span>
</template>

