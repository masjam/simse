<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import ChildSwitcher from './Partials/ChildSwitcher.vue';
import HariIniSection from './Partials/HariIniSection.vue';
import AkademikTab from './Partials/AkademikTab.vue';
import PresensiTab from './Partials/PresensiTab.vue';
import KeuanganTab from './Partials/KeuanganTab.vue';
import PerilakuTab from './Partials/PerilakuTab.vue';
import PrestasiModal from './Partials/PrestasiModal.vue';
import PayInfoModal from './Partials/PayInfoModal.vue';
import NotificationPreferencesModal from './Partials/NotificationPreferencesModal.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    children: {
        type: Array,
        default: () => [],
    },
    activeChild: {
        type: Object,
        required: true,
    },
    activeTab: {
        type: String,
        default: 'akademik',
    },
    notificationPreferences: {
        type: Object,
        default: () => ({}),
    },
    overview: {
        type: Object,
        required: true,
    },
    akademik: {
        type: Object,
        required: true,
    },
    presensi: {
        type: Object,
        required: true,
    },
    keuangan: {
        type: Object,
        required: true,
    },
    perilaku: {
        type: Object,
        required: true,
    },
    prestasi: {
        type: Array,
        default: () => [],
    },
});

const currentTab = ref(props.activeTab || 'akademik');
const showAchievementModal = ref(false);
const showPaymentModal = ref(false);
const showNotificationModal = ref(false);

watch(
    () => props.activeTab,
    (val) => {
        if (val) currentTab.value = val;
    }
);

const tabs = [
    { id: 'akademik', label: 'Akademik & Nilai' },
    { id: 'presensi', label: 'Presensi Siswa' },
    { id: 'keuangan', label: 'Keuangan SPP' },
    { id: 'perilaku', label: 'Perilaku & BK' },
];
</script>

<template>
    <Head :title="`Portal Orang Tua - ${activeChild.name}`" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Portal Orang Tua"
                :description="`SD Muhammadiyah Al Mujahidin Wonosari • Pendampingan Ananda ${activeChild.name}`"
            >
                <template #actions>
                    <AppButton size="sm" variant="secondary" @click="showNotificationModal = true">
                        Preferensi Notifikasi
                    </AppButton>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-5">
            <!-- 1. Child Switcher (Always visible) -->
            <ChildSwitcher
                :children="children"
                :active-child="activeChild"
            />

            <!-- 2. "Hari Ini" Section (Prominent overview for active child) -->
            <HariIniSection
                :active-child="activeChild"
                :overview="overview"
                :presensi="presensi"
                :keuangan="keuangan"
                :akademik="akademik"
                @select-tab="currentTab = $event"
                @open-payment-modal="showPaymentModal = true"
            />

            <!-- 3. Tab Navigation -->
            <Tabs v-model="currentTab" :tabs="tabs" variant="pills" />

            <!-- 4. Active Tab Content -->
            <div class="pt-1">
                <AkademikTab
                    v-if="currentTab === 'akademik'"
                    :akademik="akademik"
                    :prestasi="prestasi"
                    :active-child="activeChild"
                    @open-achievement-modal="showAchievementModal = true"
                />

                <PresensiTab
                    v-else-if="currentTab === 'presensi'"
                    :presensi="presensi"
                />

                <KeuanganTab
                    v-else-if="currentTab === 'keuangan'"
                    :keuangan="keuangan"
                    :active-child="activeChild"
                    @open-payment-modal="showPaymentModal = true"
                />

                <PerilakuTab
                    v-else-if="currentTab === 'perilaku'"
                    :perilaku="perilaku"
                />
            </div>
        </div>

        <!-- Modals -->
        <PrestasiModal
            :show="showAchievementModal"
            :active-child="activeChild"
            @close="showAchievementModal = false"
        />

        <PayInfoModal
            :show="showPaymentModal"
            :active-child="activeChild"
            @close="showPaymentModal = false"
        />

        <NotificationPreferencesModal
            :show="showNotificationModal"
            :preferences="notificationPreferences"
            @close="showNotificationModal = false"
        />
    </PortalShell>
</template>
