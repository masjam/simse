<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: null,
    },
    breadcrumbs: {
        type: Array,
        default: () => [], // Array of { label, href }
    },
});
</script>

<template>
    <div class="mb-6 pb-4 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <!-- Breadcrumbs -->
            <nav v-if="breadcrumbs.length > 0" class="flex items-center gap-2 text-xs text-ink-muted mb-2">
                <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
                    <span v-if="idx > 0" class="text-ink-muted/50">/</span>
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="hover:text-ink transition"
                    >
                        {{ crumb.label }}
                    </Link>
                    <span v-else class="text-ink font-medium">
                        {{ crumb.label }}
                    </span>
                </template>
            </nav>

            <h1 class="text-2xl sm:text-3xl font-bold font-display tracking-tight text-ink">
                {{ title }}
            </h1>
            <p v-if="description || $slots.description" class="text-sm text-ink-muted mt-1 max-w-2xl">
                <slot name="description">{{ description }}</slot>
            </p>
        </div>

        <div v-if="$slots.actions" class="flex items-center flex-wrap gap-2.5">
            <slot name="actions" />
        </div>
    </div>
</template>

