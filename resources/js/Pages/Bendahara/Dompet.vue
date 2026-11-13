<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useToast } from '@/composables/useToast';
import WalletTransactionModal from './Partials/WalletTransactionModal.vue';
import WalletPinModal from './Partials/WalletPinModal.vue';
import WalletRfidModal from './Partials/WalletRfidModal.vue';

const props = defineProps({
    students: { type: Array, default: () => [] },
    transactions: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({
            total_balance: 0,
            total_deposits: 0,
            total_withdrawals: 0,
            active_wallets: 0,
        }),
    },
});

const toast = useToast();
const searchQuery = ref('');
const showTxModal = ref(false);
const showPinModal = ref(false);
const showRfidModal = ref(false);
const selectedStudent = ref(null);
const txDefaultType = ref('Setoran');

const filteredStudents = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.students;
    return props.students.filter((s) =>
        s.name.toLowerCase().includes(q) ||
        s.nisn.toLowerCase().includes(q) ||
        s.class.toLowerCase().includes(q)
    );
});

const openTransactionModal = (student, type = 'Setoran') => {
    selectedStudent.value = student;
    txDefaultType.value = type;
    showTxModal.value = true;
};

const openPinModal = (student) => {
    selectedStudent.value = student;
    showPinModal.value = true;
};

const openRfidModal = (student) => {
    selectedStudent.value = student;
    showRfidModal.value = true;
};

const studentColumns = [
    { key: 'name', label: 'Siswa' },
    { key: 'class', label: 'Kelas' },
    { key: 'has_pin', label: 'Status PIN', class: 'text-center' },
    { key: 'balance', label: 'Saldo Aktif', class: 'text-right' },
];

const mutationColumns = [
    { key: 'reference_id', label: 'Referensi' },
    { key: 'student_name', label: 'Siswa' },
    { key: 'type', label: 'Jenis' },
    { key: 'description', label: 'Keterangan' },
    { key: 'date', label: 'Waktu' },
    { key: 'amount', label: 'Nominal', class: 'text-right' },
];
</script>

<template>
    <Head title="Dompet Digital Siswa" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Tabungan & Dompet Digital Siswa"
                subtitle="Kelola setoran, penarikan tunai, PIN transaksi kantin, dan mutasi saldo siswa SD Muhammadiyah Al Mujahidin"
            />

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Total Saldo Mengendap"
                    :value="summary.total_balance"
                    subtitle="Dana Mengendap Siswa"
                    icon-bg="brand"
                >
                    <template #icon>👛</template>
                </StatCard>
                <StatCard
                    title="Total Setoran Masuk"
                    :value="summary.total_deposits"
                    subtitle="Akumulasi Top-Up"
                    icon-bg="success"
                >
                    <template #icon>📥</template>
                </StatCard>
                <StatCard
                    title="Penarikan & Belanja"
                    :value="summary.total_withdrawals"
                    subtitle="Pengeluaran Siswa"
                    icon-bg="warning"
                >
                    <template #icon>🛒</template>
                </StatCard>
                <StatCard
                    title="Dompet Aktif"
                    :value="`${summary.active_wallets} Akun`"
                    subtitle="Rekening Siswa Terdaftar"
                    icon-bg="accent"
                >
                    <template #icon>👥</template>
                </StatCard>
            </div>

            <!-- Student Wallets Section -->
            <div class="space-y-4">
                <div class="bg-surface rounded-card border border-line p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-card">
                    <div>
                        <h3 class="text-base font-bold text-ink">Rekening Tabungan / Dompet Siswa</h3>
                        <p class="text-xs text-ink-muted">Kelola saldo kas siswa, setoran, penarikan, dan otorisasi kartu</p>
                    </div>
                    <div class="w-full sm:w-72">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Cari nama, NISN, kelas..."
                        />
                    </div>
                </div>

                <DataTable
                    :columns="studentColumns"
                    :items="filteredStudents"
                    empty-title="Siswa Tidak Ditemukan"
                    empty-message="Tidak ada rekening siswa yang sesuai dengan filter pencarian."
                >
                    <template #cell(name)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.name }}</p>
                            <p class="text-[11px] text-ink-muted">NISN: {{ item.nisn }}</p>
                        </div>
                    </template>

                    <template #cell(class)="{ value }">
                        <span class="text-xs font-semibold text-ink">{{ value }}</span>
                    </template>

                    <template #cell(has_pin)="{ value }">
                        <div class="flex justify-center">
                            <StatusBadge :variant="value ? 'success' : 'neutral'" size="sm">
                                {{ value ? 'PIN Aktif' : 'Belum Diatur' }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #cell(balance)="{ value }">
                        <span class="font-black text-xs text-ink font-numeric">
                            <MoneyText :amount="value" />
                        </span>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                            <AppButton
                                variant="outline"
                                size="sm"
                                @click="openTransactionModal(item, 'Setoran')"
                            >
                                + Setor
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="openTransactionModal(item, 'Penarikan')"
                            >
                                - Tarik
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="openPinModal(item)"
                            >
                                Atur PIN
                            </AppButton>
                            <AppButton
                                variant="ghost"
                                size="sm"
                                @click="openRfidModal(item)"
                            >
                                {{ item.rfid_uid ? '💳 RFID Aktif' : 'Tautkan RFID' }}
                            </AppButton>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Mutation History -->
            <div class="space-y-3">
                <h3 class="text-base font-bold text-ink px-1">Riwayat Mutasi Dompet Digital</h3>
                <DataTable
                    :columns="mutationColumns"
                    :items="transactions"
                    empty-title="Belum Ada Riwayat Mutasi"
                    empty-message="Mutasi transaksi dompet digital siswa akan dicatat di sini."
                >
                    <template #cell(reference_id)="{ value }">
                        <span class="font-mono text-xs font-bold text-brand-700">{{ value }}</span>
                    </template>

                    <template #cell(student_name)="{ item }">
                        <div>
                            <p class="font-bold text-ink text-xs">{{ item.student_name }}</p>
                            <p class="text-[11px] text-ink-muted">{{ item.class }}</p>
                        </div>
                    </template>

                    <template #cell(type)="{ value }">
                        <StatusBadge :variant="value === 'Setoran' ? 'success' : 'warning'" size="sm">
                            {{ value }}
                        </StatusBadge>
                    </template>

                    <template #cell(description)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value || '-' }}</span>
                    </template>

                    <template #cell(date)="{ value }">
                        <span class="text-xs text-ink-muted">{{ value }}</span>
                    </template>

                    <template #cell(amount)="{ item }">
                        <span
                            class="font-black text-xs font-numeric"
                            :class="item.type === 'Setoran' ? 'text-status-success-fg' : 'text-status-warning-fg'"
                        >
                            <MoneyText :amount="item.amount" />
                        </span>
                    </template>
                </DataTable>
            </div>

            <!-- Modals -->
            <WalletTransactionModal
                :show="showTxModal"
                :student="selectedStudent"
                :default-type="txDefaultType"
                @close="showTxModal = false"
                @saved="toast.success('Transaksi dompet siswa berhasil dicatat!')"
            />

            <WalletPinModal
                :show="showPinModal"
                :student="selectedStudent"
                @close="showPinModal = false"
                @saved="toast.success('PIN transaksi dompet berhasil diperbarui!')"
            />

            <WalletRfidModal
                :show="showRfidModal"
                :student="selectedStudent"
                @close="showRfidModal = false"
                @saved="toast.success('Data kartu RFID siswa berhasil disimpan!')"
            />
        </div>
    </PortalShell>
</template>
