<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Modal from '@/Components/ui/Modal.vue';

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const event = ref(props.filters.event || '');
const userId = ref(props.filters.user_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const selectedLog = ref(null);
const isModalOpen = ref(false);

const eventOptions = [
    { value: '', label: 'Semua Aksi' },
    { value: 'created', label: 'Created (Dibuat)' },
    { value: 'updated', label: 'Updated (Diubah)' },
    { value: 'deleted', label: 'Deleted (Dihapus)' },
    { value: 'restored', label: 'Restored (Dipulihkan)' },
];

const userOptions = [
    { value: '', label: 'Semua Pengguna' },
    ...props.users.map((u) => ({ value: u.id, label: u.name })),
];

const updateFilters = () => {
    router.get(route('admin.audit.index'), {
        search: search.value,
        event: event.value,
        user_id: userId.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch([event, userId, startDate, endDate], () => {
    updateFilters();
});

const viewDetails = (log) => {
    selectedLog.value = log;
    isModalOpen.value = true;
};

const columns = [
    { key: 'created_at', label: 'Waktu Kejadian' },
    { key: 'user', label: 'Aktor / Pengguna' },
    { key: 'event', label: 'Aksi' },
    { key: 'model', label: 'Entitas Model' },
    { key: 'ip_address', label: 'Alamat IP' },
    { key: 'actions', label: 'Detail' },
];

const getEventVariant = (evt) => {
    return matchEvent(evt);
};

function matchEvent(evt) {
    if (evt === 'created') return 'success';
    if (evt === 'updated') return 'info';
    if (evt === 'deleted') return 'danger';
    if (evt === 'restored') return 'warning';
    return 'neutral';
}
</script>

<template>
    <Head title="Audit Log Sistem - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Log Audit & Jejak Aktivitas"
                subtitle="Rekaman transparan seluruh perubahan data kritis: nilai, keuangan, dompet, dan akun"
            />

            <!-- Filter Card -->
            <AppCard class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <SearchInput
                        v-model="search"
                        placeholder="Cari ID/Model/IP..."
                        @search="updateFilters"
                    />

                    <AppSelect
                        v-model="event"
                        :options="eventOptions"
                        placeholder="Filter Aksi"
                    />

                    <AppSelect
                        v-model="userId"
                        :options="userOptions"
                        placeholder="Filter Aktor"
                    />

                    <div class="flex items-center gap-2">
                        <AppInput
                            v-model="startDate"
                            type="date"
                            placeholder="Mulai"
                        />
                        <span class="text-ink-muted text-xs">s/d</span>
                        <AppInput
                            v-model="endDate"
                            type="date"
                            placeholder="Selesai"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="logs.data || []"
                :pagination="logs"
                empty-title="Belum ada catatan audit"
                empty-message="Tidak ditemukan aktivitas yang sesuai dengan filter pencarian."
            >
                <template #cell(user)="{ item }">
                    <div class="font-medium text-ink">{{ item.user.name }}</div>
                </template>

                <template #cell(event)="{ item }">
                    <StatusBadge
                        :label="item.event.toUpperCase()"
                        :variant="getEventVariant(item.event)"
                    />
                </template>

                <template #cell(model)="{ item }">
                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-surface-sunken border border-line">
                        {{ item.model }}
                    </span>
                </template>

                <template #cell(ip_address)="{ item }">
                    <span class="font-mono text-xs text-ink-muted">{{ item.ip_address || '-' }}</span>
                </template>

                <template #cell(actions)="{ item }">
                    <AppButton variant="secondary" size="sm" @click="viewDetails(item)">
                        🔍 Detail
                    </AppButton>
                </template>
            </DataTable>
        </div>

        <!-- Modal Detail Perubahan -->
        <Modal
            :show="isModalOpen"
            title="Rincian Perubahan Data"
            max-width="2xl"
            @close="isModalOpen = false"
        >
            <div v-if="selectedLog" class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-2 p-3 rounded-control bg-surface-sunken">
                    <div><strong>Aktor:</strong> {{ selectedLog.user.name }}</div>
                    <div><strong>Waktu:</strong> {{ selectedLog.created_at }}</div>
                    <div><strong>Entitas:</strong> {{ selectedLog.auditable_type }}</div>
                    <div><strong>ID Entitas:</strong> <span class="font-mono">{{ selectedLog.auditable_id }}</span></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nilai Sebelum -->
                    <div>
                        <h4 class="font-bold text-ink mb-1 flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-status-danger-line inline-block"></span>
                            Sebelum Perubahan (Old Values)
                        </h4>
                        <pre class="p-3 rounded-control bg-surface-sunken border border-line overflow-x-auto text-[11px] font-mono leading-relaxed max-h-60">{{ JSON.stringify(selectedLog.old_values, null, 2) || '(Kosong / Data Baru)' }}</pre>
                    </div>

                    <!-- Nilai Sesudah -->
                    <div>
                        <h4 class="font-bold text-ink mb-1 flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-status-success-line inline-block"></span>
                            Sesudah Perubahan (New Values)
                        </h4>
                        <pre class="p-3 rounded-control bg-surface-sunken border border-line overflow-x-auto text-[11px] font-mono leading-relaxed max-h-60">{{ JSON.stringify(selectedLog.new_values, null, 2) || '(Dihapus)' }}</pre>
                    </div>
                </div>

                <div v-if="selectedLog.user_agent" class="text-ink-muted text-[10px] break-all pt-2 border-t border-line">
                    User Agent: {{ selectedLog.user_agent }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end">
                    <AppButton variant="secondary" size="sm" @click="isModalOpen = false">
                        Tutup
                    </AppButton>
                </div>
            </template>
        </Modal>
    </PortalShell>
</template>

