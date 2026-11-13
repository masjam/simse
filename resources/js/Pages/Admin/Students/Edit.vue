<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import PersonalDataTab from './Partials/PersonalDataTab.vue';
import ParentAddressTab from './Partials/ParentAddressTab.vue';
import AchievementsTab from './Partials/AchievementsTab.vue';
import PeriodicDataTab from './Partials/PeriodicDataTab.vue';

const props = defineProps({
    studentUser: {
        type: Object,
        required: true,
    },
    tingkats: {
        type: Array,
        default: () => [],
    },
    studentClasses: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('pribadi');

const tabs = [
    { id: 'pribadi', label: 'Data Pribadi' },
    { id: 'ortu', label: 'Data Ortu & Alamat' },
    { id: 'prestasi', label: 'Prestasi Siswa' },
    { id: 'periodik', label: 'Data Periodik' },
];
</script>

<template>
    <Head :title="`Edit Siswa: ${studentUser.name}`" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-5xl">
            <PageHeader
                :title="`Edit Siswa: ${studentUser.name}`"
                subtitle="Lengkapi data pribadi, orang tua, prestasi lomba, dan data periodik fisik"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.students.index')">
                            <AppButton variant="secondary" size="sm">
                                ← Kembali
                            </AppButton>
                        </Link>
                        <a :href="route('admin.students.print', studentUser.id)" target="_blank">
                            <AppButton variant="primary" size="sm">
                                🖨️ Cetak Profil
                            </AppButton>
                        </a>
                    </div>
                </template>
            </PageHeader>

            <!-- Tabs Navigation -->
            <Tabs v-model="activeTab" :tabs="tabs" variant="pills" />

            <!-- Tab Content -->
            <AppCard class="shadow-card">
                <PersonalDataTab
                    v-if="activeTab === 'pribadi'"
                    :student-user="studentUser"
                    :student-classes="studentClasses"
                />

                <ParentAddressTab
                    v-else-if="activeTab === 'ortu'"
                    :student-user="studentUser"
                />

                <AchievementsTab
                    v-else-if="activeTab === 'prestasi'"
                    :student-user="studentUser"
                />

                <PeriodicDataTab
                    v-else-if="activeTab === 'periodik'"
                    :student-user="studentUser"
                />
            </AppCard>
        </div>
    </PortalShell>
</template>
