<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: 'Hadir', // 'Hadir' | 'Izin' | 'Sakit' | 'Alpa'
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const statuses = [
    { key: 'Hadir', short: 'H', label: 'Hadir', activeClass: 'bg-status-success-bg text-status-success-fg border-status-success-line ring-1 ring-status-success-fg' },
    { key: 'Izin', short: 'I', label: 'Izin', activeClass: 'bg-status-warning-bg text-status-warning-fg border-status-warning-line ring-1 ring-status-warning-fg' },
    { key: 'Sakit', short: 'S', label: 'Sakit', activeClass: 'bg-status-info-bg text-status-info-fg border-status-info-line ring-1 ring-status-info-fg' },
    { key: 'Alpa', short: 'A', label: 'Alpa', activeClass: 'bg-status-danger-bg text-status-danger-fg border-status-danger-line ring-1 ring-status-danger-fg' },
];

function setStatus(key) {
    if (props.disabled) return;
    emit('update:modelValue', key);
    emit('change', key);
}
</script>

<template>
    <div class="inline-flex items-center gap-1 p-0.5 rounded-control bg-surface-sunken border border-line">
        <button
            v-for="s in statuses"
            :key="s.key"
            type="button"
            :disabled="disabled"
            :aria-label="s.label"
            class="px-2.5 py-1 text-xs font-bold rounded-control transition border border-transparent min-h-[32px] select-none"
            :class="[
                modelValue === s.key
                    ? s.activeClass
                    : 'text-ink-muted hover:text-ink hover:bg-surface',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
            @click="setStatus(s.key)"
        >
            <span class="sm:hidden">{{ s.short }}</span>
            <span class="hidden sm:inline">{{ s.label }}</span>
        </button>
    </div>
</template>

