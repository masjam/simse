<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';

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

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-4xl">
            <PageHeader
                title="Riwayat Notifikasi"
                :subtitle="`${unreadCount} notifikasi belum dibaca`"
            >
                <template #actions>
                    <AppButton
                        v-if="unreadCount > 0"
                        variant="primary"
                        size="sm"
                        @click="markAllRead"
                    >
                        Tandai Semua Dibaca
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 bg-surface p-1 rounded-control border border-line w-fit">
                <AppButton
                    size="sm"
                    :variant="filter === 'all' ? 'primary' : 'ghost'"
                    @click="applyFilter('all')"
                >
                    Semua
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="filter === 'unread' ? 'primary' : 'ghost'"
                    @click="applyFilter('unread')"
                >
                    Belum Dibaca ({{ unreadCount }})
                </AppButton>
            </div>

            <!-- Notifications List -->
            <AppCard padding="none">
                <div v-if="!notifications?.data || notifications.data.length === 0" class="p-8">
                    <EmptyState
                        title="Tidak ada notifikasi"
                        description="Tidak ada notifikasi pada kategori yang dipilih saat ini."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="item in notifications.data"
                        :key="item.id"
                        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 transition-colors"
                        :class="item.is_unread ? 'bg-brand-50/20' : ''"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="item.is_unread"
                                    class="w-2 h-2 rounded-full bg-brand-600 shrink-0"
                                ></span>
                                <h3 class="font-bold text-sm text-ink">{{ item.title }}</h3>
                                <StatusBadge
                                    v-if="item.is_unread"
                                    variant="warning"
                                    text="Baru"
                                    size="sm"
                                />
                            </div>
                            <p class="text-xs text-ink-muted leading-relaxed">{{ item.message }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.created_at }} ({{ item.created_at_human }})</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <Link v-if="item.url" :href="item.url">
                                <AppButton variant="secondary" size="sm">
                                    Buka
                                </AppButton>
                            </Link>
                            <AppButton
                                v-if="item.is_unread"
                                variant="ghost"
                                size="sm"
                                class="text-brand-700"
                                @click="markRead(item.id)"
                            >
                                Tandai Dibaca
                            </AppButton>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
