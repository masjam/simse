<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    logs: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();

const driverOptions = [
    { value: 'log', label: 'Log (Simulasi Lokal / Dev)' },
    { value: 'fonnte', label: 'Fonnte API' },
    { value: 'wablas', label: 'Wablas API' },
];

const targetOptions = [
    { value: 'Orang Tua', label: 'Semua Orang Tua / Wali' },
    { value: 'Guru Pengajar', label: 'Semua Guru Pengajar' },
    { value: 'Siswa', label: 'Semua Siswa' },
    { value: 'all', label: 'Seluruh Pengguna Sistem' },
];

const configForm = useForm({
    wa_driver: props.config.wa_driver || 'log',
    wa_is_active: Boolean(props.config.wa_is_active),
    wa_fonnte_token: props.config.wa_fonnte_token || '',
    wa_fonnte_endpoint: props.config.wa_fonnte_endpoint || 'https://api.fonnte.com/send',
    wa_wablas_token: props.config.wa_wablas_token || '',
    wa_wablas_endpoint: props.config.wa_wablas_endpoint || 'https://solo.wablas.com/api/send-message',
});

const announcementForm = useForm({
    target_role: 'Orang Tua',
    title: '',
    message: '',
    send_whatsapp: true,
});

const saveConfig = () => {
    configForm.post(route('sistem.wa.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Konfigurasi WhatsApp Gateway berhasil disimpan.'),
    });
};

const sendAnnouncement = () => {
    announcementForm.post(route('sistem.wa.announcement'), {
        preserveScroll: true,
        onSuccess: () => {
            announcementForm.reset('title', 'message');
            toast.success('Pengumuman berhasil disebarkan.');
        },
    });
};

const retryLog = (logId) => {
    router.post(route('sistem.wa.retry', logId), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Pesan WhatsApp sedang dicoba kirim ulang.'),
    });
};

const statusVariant = (status) => {
    if (status === 'Terkirim') return 'success';
    if (status === 'Gagal') return 'danger';
    return 'warning';
};
</script>

<template>
    <Head title="WhatsApp Gateway & Pengumuman" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="WhatsApp Gateway & Broadcast Pengumuman"
                subtitle="Konfigurasi provider WhatsApp (Log, Fonnte, Wablas), pantau antrean pengiriman, dan kirim pengumuman"
            />

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <StatCard
                    title="Terkirim"
                    :value="stats.sent"
                    subtitle="Pesan berhasil sampai"
                    icon-bg="success"
                />
                <StatCard
                    title="Menunggu Antrean"
                    :value="stats.pending"
                    subtitle="Pesan dalam antrean"
                    icon-bg="warning"
                />
                <StatCard
                    title="Gagal"
                    :value="stats.failed"
                    subtitle="Pesan perlu dicek"
                    icon-bg="danger"
                />
            </div>

            <!-- Gateway Config & Announcement Form -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gateway Config -->
                <AppCard class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-ink">Konfigurasi Gateway</h2>
                        <AppCheckbox
                            v-model="configForm.wa_is_active"
                            label="Gateway Aktif"
                        />
                    </div>

                    <form @submit.prevent="saveConfig" class="space-y-4">
                        <FormField label="Driver Aktif" :error="configForm.errors.wa_driver">
                            <AppSelect
                                v-model="configForm.wa_driver"
                                :options="driverOptions"
                            />
                        </FormField>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <FormField label="Token Fonnte" :error="configForm.errors.wa_fonnte_token">
                                <AppInput
                                    v-model="configForm.wa_fonnte_token"
                                    type="password"
                                    placeholder="Masukkan token Fonnte"
                                />
                            </FormField>
                            <FormField label="Endpoint Fonnte" :error="configForm.errors.wa_fonnte_endpoint">
                                <AppInput
                                    v-model="configForm.wa_fonnte_endpoint"
                                    type="url"
                                />
                            </FormField>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <FormField label="Token Wablas" :error="configForm.errors.wa_wablas_token">
                                <AppInput
                                    v-model="configForm.wa_wablas_token"
                                    type="password"
                                    placeholder="Masukkan token Wablas"
                                />
                            </FormField>
                            <FormField label="Endpoint Wablas" :error="configForm.errors.wa_wablas_endpoint">
                                <AppInput
                                    v-model="configForm.wa_wablas_endpoint"
                                    type="url"
                                />
                            </FormField>
                        </div>

                        <div class="pt-2">
                            <AppButton
                                type="submit"
                                variant="primary"
                                class="w-full"
                                :loading="configForm.processing"
                            >
                                Simpan Konfigurasi Gateway
                            </AppButton>
                        </div>
                    </form>
                </AppCard>

                <!-- Announcement Broadcast -->
                <AppCard class="space-y-4">
                    <h2 class="text-sm font-bold text-ink">Kirim Pengumuman Sekolah</h2>

                    <form @submit.prevent="sendAnnouncement" class="space-y-4">
                        <FormField label="Target Penerima" :error="announcementForm.errors.target_role">
                            <AppSelect
                                v-model="announcementForm.target_role"
                                :options="targetOptions"
                            />
                        </FormField>

                        <FormField label="Judul Pengumuman" :error="announcementForm.errors.title" required>
                            <AppInput
                                v-model="announcementForm.title"
                                placeholder="Contoh: Jadwal Libur Awal Ramadhan"
                                required
                            />
                        </FormField>

                        <FormField label="Isi Pesan Pengumuman" :error="announcementForm.errors.message" required>
                            <AppTextarea
                                v-model="announcementForm.message"
                                :rows="3"
                                placeholder="Tuliskan isi pengumuman resmi sekolah..."
                                required
                            />
                        </FormField>

                        <AppCheckbox
                            v-model="announcementForm.send_whatsapp"
                            label="Antrekan pengiriman ke WhatsApp selain notifikasi in-app"
                        />

                        <div class="pt-2">
                            <AppButton
                                type="submit"
                                variant="accent"
                                class="w-full"
                                :loading="announcementForm.processing"
                            >
                                Kirim Pengumuman Sekarang
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>

            <!-- Riwayat Log WA -->
            <AppCard padding="none">
                <div class="p-4 sm:p-5 border-b border-line">
                    <h2 class="text-sm font-bold text-ink">Log Pengiriman WhatsApp (50 Terakhir)</h2>
                </div>

                <div v-if="logs.length === 0" class="p-8">
                    <EmptyState
                        title="Belum ada log pesan"
                        description="Belum ada riwayat pengiriman pesan WhatsApp tercatat."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="log in logs"
                        :key="log.id"
                        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-surface-raised transition-colors"
                    >
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-ink">{{ log.recipient_name }}</span>
                                <span class="font-numeric text-xs text-ink-muted">({{ log.phone_number }})</span>
                                <StatusBadge
                                    :variant="statusVariant(log.status)"
                                    :text="log.status"
                                    size="sm"
                                />
                            </div>
                            <p class="text-xs text-ink-muted line-clamp-2 leading-relaxed">{{ log.message }}</p>
                            <p class="text-[11px] text-ink-muted font-numeric">{{ log.created_at }}</p>
                        </div>

                        <div v-if="log.status === 'Gagal'" class="shrink-0">
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="retryLog(log.id)"
                            >
                                Kirim Ulang
                            </AppButton>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
