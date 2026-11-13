<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: null, // manual override: 'success' | 'warning' | 'danger' | 'info' | 'neutral'
    },
    size: {
        type: String,
        default: 'md', // 'sm' | 'md'
    },
});

const resolvedVariant = computed(() => {
    if (props.variant) return props.variant;

    const s = String(props.status).toLowerCase().trim();

    // Success mappings
    if (['lunas', 'berhasil', 'hadir', 'disetujui', 'selesai', 'aktif', 'sukses', 'completed', 'verified', 'diterima'].includes(s)) {
        return 'success';
    }

    // Warning mappings
    if (['angsuran', 'menunggu verifikasi', 'menunggu persetujuan', 'sedang dikerjakan', 'izin', 'pending', 'proses'].includes(s)) {
        return 'warning';
    }

    // Danger mappings
    if (['belum lunas', 'ditolak', 'dibatalkan', 'alfa', 'alpa', 'sakit', 'tidak aktif', 'failed', 'gagal', 'expired'].includes(s)) {
        return 'danger';
    }

    // Info mappings
    if (['draft', 'akan datang', 'baru', 'info'].includes(s)) {
        return 'info';
    }

    return 'neutral';
});

const variantClasses = computed(() => {
    switch (resolvedVariant.value) {
        case 'success':
            return 'bg-status-success-bg text-status-success-fg border-status-success-line';
        case 'warning':
            return 'bg-status-warning-bg text-status-warning-fg border-status-warning-line';
        case 'danger':
            return 'bg-status-danger-bg text-status-danger-fg border-status-danger-line';
        case 'info':
            return 'bg-status-info-bg text-status-info-fg border-status-info-line';
        case 'neutral':
        default:
            return 'bg-surface-sunken text-ink-muted border-line';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 font-semibold rounded-full border leading-none select-none"
        :class="[
            variantClasses,
            size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs',
        ]"
    >
        <span
            class="w-1.5 h-1.5 rounded-full"
            :class="{
                'bg-status-success-fg': resolvedVariant === 'success',
                'bg-status-warning-fg': resolvedVariant === 'warning',
                'bg-status-danger-fg': resolvedVariant === 'danger',
                'bg-status-info-fg': resolvedVariant === 'info',
                'bg-ink-muted': resolvedVariant === 'neutral',
            }"
        />
        <span><slot>{{ status }}</slot></span>
    </span>
</template>

