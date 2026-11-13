<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import FileInput from '@/Components/ui/FileInput.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const toast = useToast();
const search = ref(props.filters.search || '');
const fileInput = ref(null);

const importForm = useForm({
    file: null,
});

const updateFilters = () => {
    router.get(route('admin.teachers.index'), {
        search: search.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, () => {
    updateFilters();
});

// Ctrl+K shortcut
const searchInputRef = ref(null);
const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchInputRef.value?.$el?.querySelector('input')?.focus() || searchInputRef.value?.focus?.();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleFileChange = (file) => {
    if (file) {
        importForm.file = file;
        importForm.post(route('admin.users.import'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Data guru berhasil diimpor.');
                importForm.reset();
                fileInput.value?.reset?.();
            },
            onError: () => {
                toast.error('Gagal mengimpor file data guru.');
            },
        });
    }
};

const columns = [
    { key: 'user', label: 'Tenaga Pendidik / Guru' },
    { key: 'roles', label: 'Role Akses' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Tgl Terdaftar' },
];
</script>

<template>
    <Head title="Data Guru - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Data Guru & Tenaga Pendidik"
                subtitle="Kelola master data guru pengajar, wali kelas, dan pembina kesiswaan"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <a :href="route('admin.users.template', { tab: 'guru' })">
                            <AppButton variant="ghost" size="sm">
                                📥 Template Excel
                            </AppButton>
                        </a>
                        <FileInput
                            ref="fileInput"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="handleFileChange"
                        />
                        <AppButton
                            variant="secondary"
                            size="sm"
                            :loading="importForm.processing"
                            @click="fileInput?.click()"
                        >
                            📤 Impor Data
                        </AppButton>
                    </div>
                </template>
            </PageHeader>

            <!-- Search Bar -->
            <AppCard>
                <div class="w-full md:w-80">
                    <SearchInput
                        ref="searchInputRef"
                        v-model="search"
                        placeholder="Cari guru... (Ctrl+K)"
                        @search="updateFilters"
                    />
                </div>
            </AppCard>

            <!-- Data Table -->
            <DataTable
                :columns="columns"
                :items="users.data || []"
                :pagination="users"
                empty-title="Belum ada data guru"
                empty-message="Tidak ada guru yang sesuai dengan pencarian saat ini."
            >
                <template #cell(user)="{ item }">
                    <div class="flex items-center gap-3">
                        <Avatar :name="item.name" size="md" />
                        <div>
                            <p class="font-bold text-sm text-ink">{{ item.name }}</p>
                            <p class="text-xs text-ink-muted">{{ item.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell(roles)="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <StatusBadge
                            v-for="r in item.roles"
                            :key="r.id"
                            variant="brand"
                            :text="r.name"
                            size="sm"
                        />
                    </div>
                </template>

                <template #cell(status)>
                    <StatusBadge
                        variant="success"
                        text="Aktif"
                        size="sm"
                    />
                </template>

                <template #cell(created_at)="{ item }">
                    <span class="text-xs text-ink-muted font-numeric">
                        {{ new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </span>
                </template>
            </DataTable>
        </div>
    </PortalShell>
</template>
