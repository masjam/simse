<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    theme: {
        type: String,
        default: 'light',
    },
});

const page = usePage();
const isOpen = ref(false);
const polledSummary = ref(null);
let pollTimer = null;

const summary = computed(() => {
    return polledSummary.value || page.props.notificationsSummary || { unread_count: 0, recent: [] };
});

const fetchNotifications = async () => {
    try {
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
        // Abaikan error jaringan sementara saat polling
    }
};

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

onMounted(() => {
    pollTimer = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
});

const buttonClass = computed(() => {
    if (props.theme === 'dark') {
        return 'bg-white/10 hover:bg-white/20 text-white border-white/20';
    }

    return 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200';
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="relative p-2 rounded-xl border transition-colors flex items-center justify-center"
            :class="buttonClass"
            aria-label="Notifikasi"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="summary.unread_count > 0"
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] font-extrabold flex items-center justify-center shadow"
            >
                {{ summary.unread_count > 99 ? '99+' : summary.unread_count }}
            </span>
        </button>

        <div
            v-if="isOpen"
            class="fixed inset-0 z-40"
            @click="isOpen = false"
        ></div>

        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white border border-slate-200 shadow-2xl z-50 overflow-hidden text-slate-800"
        >
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <p class="text-xs font-extrabold text-slate-900">Notifikasi</p>
                    <p class="text-[11px] text-slate-500">{{ summary.unread_count }} belum dibaca</p>
                </div>
                <button
                    v-if="summary.unread_count > 0"
                    type="button"
                    @click="markAllRead"
                    class="text-[11px] font-bold text-emerald-700 hover:underline"
                >
                    Tandai semua dibaca
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                <div v-if="!summary.recent || summary.recent.length === 0" class="p-6 text-center text-xs text-slate-500">
                    Belum ada notifikasi.
                </div>
                <div
                    v-for="item in summary.recent"
                    :key="item.id"
                    class="p-3.5 hover:bg-slate-50 transition-colors"
                    :class="!item.read_at ? 'bg-amber-50/40' : ''"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-bold text-slate-900">{{ item.title }}</p>
                        <button
                            v-if="!item.read_at"
                            type="button"
                            @click="markRead(item.id)"
                            class="text-[10px] font-bold text-emerald-700 shrink-0 hover:underline"
                        >
                            Baca
                        </button>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ item.message }}</p>
                    <p class="text-[10px] text-slate-400 mt-1">{{ item.created_at_human }}</p>
                </div>
            </div>

            <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50 text-center">
                <Link
                    :href="route('notifications.index')"
                    @click="isOpen = false"
                    class="text-xs font-bold text-emerald-700 hover:text-emerald-800"
                >
                    Lihat Semua Riwayat Notifikasi →
                </Link>
            </div>
        </div>
    </div>
</template>

