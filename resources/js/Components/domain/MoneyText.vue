<script setup>
import { computed } from 'vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    amount: {
        type: [Number, String],
        default: 0,
    },
    showSymbol: {
        type: Boolean,
        default: true,
    },
});

const { formatRupiah } = useFormat();
const formatted = computed(() => formatRupiah(props.amount, { showSymbol: props.showSymbol }));
</script>

<template>
    <span class="font-numeric font-semibold text-ink whitespace-nowrap">
        {{ formatted }}
    </span>
</template>

