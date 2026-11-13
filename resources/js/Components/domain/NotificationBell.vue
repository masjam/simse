<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { usePolling } from '@/composables/usePolling';

const page = usePage();
const isOpen = ref(false);
const polledSummary = ref(null);

const summary = computed(() => {
    return polledSummary.value || page.props.notificationsSummary || { unread_count: 0, recent: [] };
});

const fetchNotifications = async () => {
    try {
        if (typeof route !== 'function') return;
        const response = await fetch(route('notifications.poll'), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (response.ok) {
            polledSummary.value = await response.json();
        }
    } catch {
        // Ignore network errors during polling
    }
};

usePolling(fetchNotifications, 30000, { immediate: false, autoStart: true });

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            fetchNotifications();
        },
    });
};

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            fetchNotifications();
        },
    });
};
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="relative p-2 rounded-control border border-line bg-surface text-ink hover:bg-surface-raised transition-colors flex items-center justify-center min-h-[44px] min-w-[44px] ring-focus focus:ring-2 outline-none"
            aria-label="Notifikasi"
            @click="isOpen = !isOpen"
        >
            <svg class="w-5 h-5 text-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="summary.unread_count > 0"
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-status-danger-fg text-white text-[10px] font-extrabold flex items-center justify-center shadow"
            >
                {{ summary.unread_count > 99 ? '99+' : summary.unread_count }}
            </span>
        </button>

        <div
            v-if="isOpen"
            class="fixed inset-0 z-40"
            @click="isOpen = false"
        />

        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-card bg-surface border border-line shadow-pop z-50 overflow-hidden text-ink"
        >
            <div class="px-4 py-3 border-b border-line flex items-center justify-between bg-surface-raised">
                <div>
                    <p class="text-xs font-extrabold text-ink">Notifikasi</p>
                    <p class="text-[11px] text-ink-muted">{{ summary.unread_count }} belum dibaca</p>
                </div>
                <button
                    v-if="summary.unread_count > 0"
                    type="button"
                    class="text-[11px] font-bold text-brand-600 hover:underline"
                    @click="markAllRead"
                >
                    Tandai semua dibaca
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-line">
                <div v-if="!summary.recent || summary.recent.length === 0" class="p-6 text-center text-xs text-ink-muted">
                    Belum ada notifikasi baru.
                </div>
                <div
                    v-for="item in summary.recent"
                    :key="item.id"
                    class="p-3.5 hover:bg-surface-raised transition-colors"
                    :class="!item.read_at ? 'bg-brand-50/40 dark:bg-brand-900/20' : ''"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-bold text-ink">{{ item.title }}</p>
                        <button
                            v-if="!item.read_at"
                            type="button"
                            class="text-[10px] font-bold text-brand-600 shrink-0 hover:underline"
                            @click="markRead(item.id)"
                        >
                            Baca
                        </button>
                    </div>
                    <p class="text-xs text-ink-muted mt-1 line-clamp-2">{{ item.message }}</p>
                    <p class="text-[10px] text-ink-muted/80 mt-1 font-numeric">{{ item.created_at_human }}</p>
                </div>
            </div>

            <div class="px-4 py-2.5 border-t border-line bg-surface-raised text-center">
                <Link
                    :href="route('notifications.index')"
                    class="text-xs font-bold text-brand-600 hover:text-brand-700"
                    @click="isOpen = false"
                >
                    Lihat Semua Riwayat Notifikasi →
                </Link>
            </div>
        </div>
    </div>
</template>

