<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
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
        default: () => ({ search: '', tingkat_id: '', rombel_id: '' }),
    },
    tingkats: {
        type: Array,
        default: () => [],
    },
    rombels: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();
const search = ref(props.filters.search || '');
const tingkat_id = ref(props.filters.tingkat_id || '');
const rombel_id = ref(props.filters.rombel_id || '');
const fileInput = ref(null);

const importForm = useForm({
    file: null,
});

const tingkatOptions = [
    { value: '', label: 'Semua Tingkat' },
    ...props.tingkats.map((t) => ({ value: t.id, label: t.name })),
];

const rombelOptions = [
    { value: '', label: 'Semua Rombel' },
    ...props.rombels.map((r) => ({ value: r.id, label: r.name })),
];

const updateFilters = () => {
    router.get(route('admin.students.index'), {
        search: search.value,
        tingkat_id: tingkat_id.value,
        rombel_id: rombel_id.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch([tingkat_id, rombel_id], () => {
    updateFilters();
});

// Ctrl+K shortcut for search
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
                toast.success('Data siswa berhasil diimpor.');
                importForm.reset();
                fileInput.value?.reset?.();
            },
            onError: () => {
                toast.error('Gagal mengimpor file data siswa.');
            },
        });
    }
};

const columns = [
    { key: 'user', label: 'Peserta Didik' },
    { key: 'roles', label: 'Role Akses' },
    { key: 'class', label: 'Tingkat & Rombel' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Tgl Terdaftar' },
];
</script>

<template>
    <Head title="Modul Siswa - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Modul Siswa"
                subtitle="Kelola master data peserta didik, kelas, dan import/export data dapodik"
            >
                <template #actions>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a :href="route('admin.users.template', { tab: 'siswa' })">
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
                        <a :href="route('admin.users.export')">
                            <AppButton variant="secondary" size="sm">
                                📄 Ekspor Data
                            </AppButton>
                        </a>
                    </div>
                </template>
            </PageHeader>

            <!-- Filters Bar -->
            <AppCard class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-80">
                    <SearchInput
                        ref="searchInputRef"
                        v-model="search"
                        placeholder="Cari siswa... (Ctrl+K)"
                        @search="updateFilters"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="w-36">
                        <AppSelect
                            v-model="tingkat_id"
                            :options="tingkatOptions"
                            placeholder="Semua Tingkat"
                        />
                    </div>
                    <div class="w-36">
                        <AppSelect
                            v-model="rombel_id"
                            :options="rombelOptions"
                            placeholder="Semua Rombel"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Data Table -->
            <DataTable
                :columns="columns"
                :items="users.data || []"
                :pagination="users"
                empty-title="Belum ada data siswa"
                empty-message="Tidak ada siswa yang sesuai dengan filter atau pencarian saat ini."
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
                            variant="neutral"
                            :text="r.name"
                            size="sm"
                        />
                    </div>
                </template>

                <template #cell(class)="{ item }">
                    <span v-if="item.student?.student_class" class="text-xs font-bold text-brand-700">
                        {{ item.student.student_class.tingkat?.name || '-' }} / {{ item.student.student_class.name }}
                    </span>
                    <span v-else class="text-xs text-ink-muted italic">Belum diatur</span>
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

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('admin.students.edit', item.id)">
                            <AppButton variant="secondary" size="sm">
                                Edit Profil
                            </AppButton>
                        </Link>
                    </div>
                </template>
            </DataTable>
        </div>
    </PortalShell>
</template>
