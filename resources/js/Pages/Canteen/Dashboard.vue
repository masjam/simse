<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    stand: { type: Object, required: true },
    stats: { type: Object, required: true },
    orders: { type: Array, required: true },
});

const { confirm } = useConfirm();
const toast = useToast();

const editStandModal = ref(false);
const standForm = useForm({
    name: props.stand.name,
    description: props.stand.description || '',
});

const toggleOpen = async () => {
    const nextState = !props.stand.is_open;
    const ok = await confirm({
        title: nextState ? 'Buka Stan Kantin' : 'Tutup Stan Sementara',
        message: nextState
            ? 'Stan akan ditampilkan sebagai Buka dan siswa dapat melakukan pemesanan.'
            : 'Stan akan ditutup sementara dan tidak menerima pesanan baru.',
        confirmLabel: nextState ? 'Buka Sekarang' : 'Tutup Sementara',
        variant: nextState ? 'primary' : 'warning',
    });

    if (ok) {
        router.post(route('kantin.toggle-open'), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success(nextState ? 'Stan berhasil dibuka!' : 'Stan berhasil ditutup sementara.'),
        });
    }
};

const submitStand = () => {
    standForm.post(route('kantin.stand.update'), {
        preserveScroll: true,
        onSuccess: () => {
            editStandModal.value = false;
            toast.success('Informasi profil stan berhasil diperbarui!');
        },
    });
};

const changeStatus = (orderId, status) => {
    router.patch(route('kantin.orders.status', orderId), { status }, {
        preserveScroll: true,
        onSuccess: () => toast.success(`Status pesanan diperbarui menjadi: ${status}`),
    });
};

const mapOrderStatusVariant = (status) => {
    switch (status) {
        case 'Menunggu Konfirmasi': return 'warning';
        case 'Sedang Dimasak': return 'brand';
        case 'Siap Diambil': return 'accent';
        case 'Selesai': return 'success';
        default: return 'danger';
    }
};
</script>

<template>
    <Head title="Dashboard Stand Kantin" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                :title="stand.name"
                subtitle="Kelola pesanan siswa, status operasional stan, dan saldo penjualan e-Kantin"
            />

            <!-- Header Stand & Quick Actions -->
            <AppCard>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <StatusBadge :variant="stand.is_open ? 'success' : 'danger'">
                                {{ stand.is_open ? 'Stand Buka' : 'Stand Tutup' }}
                            </StatusBadge>
                            <h2 class="text-lg font-bold text-ink">{{ stand.name }}</h2>
                        </div>
                        <p class="text-xs text-ink-muted">
                            {{ stand.description || 'Stan e-Kantin SD Muhammadiyah Al Mujahidin' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <AppButton
                            :variant="stand.is_open ? 'danger' : 'primary'"
                            size="sm"
                            @click="toggleOpen"
                        >
                            {{ stand.is_open ? 'Tutup Stand Sementara' : 'Buka Stand Sekarang' }}
                        </AppButton>
                        <AppButton
                            variant="secondary"
                            size="sm"
                            @click="editStandModal = true"
                        >
                            Ubah Info Stand
                        </AppButton>
                        <Link :href="route('kantin.scan')">
                            <AppButton variant="secondary" size="sm">
                                📷 Scan QR
                            </AppButton>
                        </Link>
                        <Link :href="route('kantin.rfid')">
                            <AppButton variant="primary" size="sm">
                                💳 RFID POS
                            </AppButton>
                        </Link>
                    </div>
                </div>
            </AppCard>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    title="Saldo Stand"
                    :value="stand.balance"
                    subtitle="Dapat Dicairkan"
                    icon-bg="success"
                >
                    <template #icon>💰</template>
                </StatCard>
                <StatCard
                    title="Omzet Hari Ini"
                    :value="stats.today_revenue"
                    subtitle="Penjualan Berjalan"
                    icon-bg="brand"
                >
                    <template #icon>📈</template>
                </StatCard>
                <StatCard
                    title="Pesanan Aktif"
                    :value="stats.pending_orders"
                    subtitle="Perlu Diproses"
                    icon-bg="warning"
                >
                    <template #icon>⏳</template>
                </StatCard>
                <StatCard
                    title="Total Menu"
                    :value="`${stats.menu_count} Item`"
                    subtitle="Katalog Makanan/Minuman"
                    icon-bg="accent"
                >
                    <template #icon>🍱</template>
                </StatCard>
            </div>

            <!-- Daftar Pesanan Masuk -->
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <div>
                        <h3 class="text-base font-bold text-ink">Pesanan Masuk Terbaru</h3>
                        <p class="text-xs text-ink-muted">Proses tahapan pesanan siswa secara langsung</p>
                    </div>
                    <Link :href="route('kantin.menus')">
                        <AppButton variant="ghost" size="sm">
                            Kelola Katalog Menu &rarr;
                        </AppButton>
                    </Link>
                </div>

                <div v-if="orders.length === 0" class="bg-surface rounded-card border border-line p-8 shadow-card">
                    <EmptyState
                        title="Belum Ada Pesanan"
                        description="Belum ada pesanan siswa yang masuk ke stan ini hari ini."
                    />
                </div>

                <div v-else class="space-y-3">
                    <AppCard
                        v-for="order in orders"
                        :key="order.id"
                        class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"
                    >
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-control bg-surface-raised border border-line text-ink">
                                    {{ order.qr_code_pickup }}
                                </span>
                                <StatusBadge :variant="mapOrderStatusVariant(order.status)" size="sm">
                                    {{ order.status }}
                                </StatusBadge>
                                <span class="text-xs text-ink-muted">{{ order.created_at }}</span>
                            </div>

                            <p class="font-bold text-ink text-sm">
                                {{ order.student_name }}
                                <span class="text-xs font-normal text-ink-muted">(Kelas {{ order.class_name }})</span>
                            </p>

                            <div class="flex flex-wrap gap-1.5 text-xs">
                                <span
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="px-2 py-0.5 rounded-control bg-surface-raised border border-line text-ink"
                                >
                                    {{ item.menu_name }} <strong class="font-bold text-brand-700">x{{ item.quantity }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
                            <span class="text-base font-black text-ink font-numeric">
                                <MoneyText :amount="order.total_price" />
                            </span>

                            <div v-if="order.status !== 'Selesai' && order.status !== 'Dibatalkan'" class="flex flex-wrap gap-1.5">
                                <AppButton
                                    v-if="order.status === 'Menunggu Konfirmasi'"
                                    variant="outline"
                                    size="sm"
                                    @click="changeStatus(order.id, 'Sedang Dimasak')"
                                >
                                    🍳 Masak
                                </AppButton>
                                <AppButton
                                    v-if="order.status === 'Menunggu Konfirmasi' || order.status === 'Sedang Dimasak'"
                                    variant="secondary"
                                    size="sm"
                                    @click="changeStatus(order.id, 'Siap Diambil')"
                                >
                                    🔔 Siap Diambil
                                </AppButton>
                                <AppButton
                                    variant="primary"
                                    size="sm"
                                    @click="changeStatus(order.id, 'Selesai')"
                                >
                                    ✅ Selesai
                                </AppButton>
                                <AppButton
                                    variant="ghost"
                                    size="sm"
                                    class="text-status-danger-fg"
                                    @click="changeStatus(order.id, 'Dibatalkan')"
                                >
                                    Batalkan
                                </AppButton>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Modal Ubah Info Stand -->
            <Modal
                :show="editStandModal"
                title="Ubah Profil Stand Kantin"
                max-width="md"
                @close="editStandModal = false"
            >
                <form @submit.prevent="submitStand" class="p-6 space-y-4">
                    <FormField label="Nama Stand" :error="standForm.errors.name" required>
                        <AppInput
                            v-model="standForm.name"
                            placeholder="Contoh: Stand Barokah"
                            required
                        />
                    </FormField>

                    <FormField label="Deskripsi Singkat" :error="standForm.errors.description">
                        <AppInput
                            v-model="standForm.description"
                            placeholder="Contoh: Menyediakan aneka nasi, snack sehat, dan jus"
                        />
                    </FormField>

                    <div class="flex justify-end gap-2 pt-3 border-t border-line">
                        <AppButton type="button" variant="ghost" size="sm" @click="editStandModal = false">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" size="sm" :loading="standForm.processing">
                            Simpan Perubahan
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
