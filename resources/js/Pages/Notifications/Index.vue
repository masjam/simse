<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    notifications: {
        type: Object,
        required: true,
    },
    filter: {
        type: String,
        default: 'all',
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

const applyFilter = (filter) => {
    router.get(route('notifications.index'), { filter }, { preserveState: true, replace: true });
};

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Riwayat Notifikasi" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        href="/dashboard"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700"
                    >
                        ← Kembali
                    </Link>
                    <div>
                        <h1 class="font-extrabold text-sm sm:text-base text-slate-900">Riwayat Notifikasi</h1>
                        <p class="text-[11px] text-slate-500">{{ unreadCount }} notifikasi belum dibaca</p>
                    </div>
                </div>

                <button
                    v-if="unreadCount > 0"
                    type="button"
                    @click="markAllRead"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold"
                >
                    Tandai Semua Dibaca
                </button>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-5">
            <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 w-fit">
                <button
                    type="button"
                    @click="applyFilter('all')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-colors"
                    :class="filter === 'all' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Semua
                </button>
                <button
                    type="button"
                    @click="applyFilter('unread')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-colors"
                    :class="filter === 'unread' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Belum Dibaca ({{ unreadCount }})
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div v-if="notifications.data.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Tidak ada notifikasi pada kategori ini.
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="item in notifications.data"
                        :key="item.id"
                        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        :class="item.is_unread ? 'bg-amber-50/40' : ''"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="item.is_unread"
                                    class="w-2 h-2 rounded-full bg-amber-500 shrink-0"
                                ></span>
                                <h2 class="font-bold text-sm text-slate-900">{{ item.title }}</h2>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600">{{ item.message }}</p>
                            <p class="text-[11px] text-slate-400">{{ item.created_at }} ({{ item.created_at_human }})</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                v-if="item.url"
                                :href="item.url"
                                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700"
                            >
                                Buka
                            </Link>
                            <button
                                v-if="item.is_unread"
                                type="button"
                                @click="markRead(item.id)"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-xs font-bold text-emerald-700"
                            >
                                Tandai Dibaca
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

