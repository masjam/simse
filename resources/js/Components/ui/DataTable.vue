<script setup>
import { Link } from '@inertiajs/vue3';
import EmptyState from './EmptyState.vue';
import Skeleton from './Skeleton.vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true, // Array of { key, label, sortable, class }
    },
    items: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: null, // { current_page, last_page, from, to, total, links: [{ url, label, active }] }
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyTitle: {
        type: String,
        default: 'Belum Ada Data',
    },
    emptyMessage: {
        type: String,
        default: 'Tidak ada data yang sesuai dengan filter atau pencarian saat ini.',
    },
    sortBy: {
        type: String,
        default: null,
    },
    sortDirection: {
        type: String,
        default: 'asc', // 'asc' | 'desc'
    },
});

const emit = defineEmits(['sort']);

function handleSort(col) {
    if (!col.sortable) return;
    const direction = props.sortBy === col.key && props.sortDirection === 'asc' ? 'desc' : 'asc';
    emit('sort', { key: col.key, direction });
}
</script>

<template>
    <div class="w-full">
        <!-- Loading State -->
        <div v-if="loading" class="p-4">
            <Skeleton type="table" :rows="5" />
        </div>

        <!-- Empty State -->
        <div v-else-if="items.length === 0" class="py-12 bg-surface rounded-card border border-line">
            <slot name="empty">
                <EmptyState :title="emptyTitle" :description="emptyMessage" />
            </slot>
        </div>

        <!-- Data Content -->
        <div v-else>
            <!-- Desktop Table View (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto rounded-card border border-line bg-surface shadow-card">
                <table class="w-full text-left text-sm text-ink border-collapse">
                    <thead class="bg-surface-raised border-b border-line text-xs uppercase font-semibold text-ink-muted">
                        <tr>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                class="px-4 py-3.5 select-none"
                                :class="[col.class, col.sortable ? 'cursor-pointer hover:text-ink' : '']"
                                @click="handleSort(col)"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>{{ col.label }}</span>
                                    <span v-if="col.sortable && sortBy === col.key" class="text-brand-600 font-bold">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th v-if="$slots.actions" class="px-4 py-3.5 text-right">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr
                            v-for="(item, index) in items"
                            :key="item.id ?? index"
                            class="hover:bg-surface-raised/60 transition"
                        >
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-4 py-3.5 text-ink align-middle"
                                :class="col.class"
                            >
                                <slot :name="`cell(${col.key})`" :item="item" :value="item[col.key]">
                                    {{ item[col.key] ?? '-' }}
                                </slot>
                            </td>
                            <td v-if="$slots.actions" class="px-4 py-3.5 text-right align-middle">
                                <slot name="actions" :item="item" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View (< 640px) -->
            <div class="sm:hidden space-y-3">
                <div
                    v-for="(item, index) in items"
                    :key="item.id ?? index"
                    class="rounded-card border border-line bg-surface p-4 shadow-card space-y-2.5"
                >
                    <slot name="card" :item="item">
                        <div
                            v-for="col in columns"
                            :key="col.key"
                            class="flex items-start justify-between text-xs gap-2"
                        >
                            <span class="font-semibold text-ink-muted uppercase tracking-wider flex-shrink-0">
                                {{ col.label }}:
                            </span>
                            <div class="text-ink text-right">
                                <slot :name="`cell(${col.key})`" :item="item" :value="item[col.key]">
                                    {{ item[col.key] ?? '-' }}
                                </slot>
                            </div>
                        </div>
                    </slot>

                    <div v-if="$slots.actions" class="pt-2 border-t border-line flex items-center justify-end gap-2">
                        <slot name="actions" :item="item" />
                    </div>
                </div>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="pagination && pagination.links && pagination.links.length > 3"
                class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ink-muted px-1"
            >
                <p>
                    Menampilkan <span class="font-semibold text-ink">{{ pagination.from ?? 0 }}</span> s.d.
                    <span class="font-semibold text-ink">{{ pagination.to ?? 0 }}</span> dari
                    <span class="font-semibold text-ink">{{ pagination.total ?? 0 }}</span> data
                </p>

                <nav class="flex items-center gap-1 flex-wrap" aria-label="Paginasi">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, idx) in pagination.links"
                        :key="idx"
                        :href="link.url ?? undefined"
                        :class="[
                            'px-3 py-1.5 rounded-control font-medium transition text-xs min-h-[32px] flex items-center justify-center',
                            link.active
                                ? 'bg-brand-600 text-brand-fg font-bold'
                                : link.url
                                    ? 'bg-surface border border-line text-ink hover:bg-surface-raised'
                                    : 'text-ink-muted/40 cursor-not-allowed border border-transparent',
                        ]"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </div>
</template>

