<script setup>
defineProps({
    title: {
        type: String,
        default: 'Cetak Dokumen',
    },
    orientation: {
        type: String,
        default: 'portrait', // 'portrait' | 'landscape'
    },
});
</script>

<template>
    <div class="print-container w-full bg-surface-sunken p-4 sm:p-8 flex justify-center">
        <!-- A4 Paper Representation -->
        <article
            class="print-sheet bg-white text-slate-900 shadow-pop border border-line rounded-card sm:rounded-none p-6 sm:p-12 w-full max-w-[210mm] min-h-[297mm] transition"
            :class="{ 'max-w-[297mm] min-h-[210mm]': orientation === 'landscape' }"
        >
            <slot />
        </article>
    </div>
</template>

<style scoped>
@media print {
    .print-container {
        padding: 0 !important;
        background: transparent !important;
    }
    .print-sheet {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: auto !important;
    }
}
</style>

