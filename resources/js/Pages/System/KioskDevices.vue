<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

defineProps({
    devices: { type: Array, required: true },
});

const confirm = useConfirm();
const toast = useToast();

const form = useForm({
    name: '',
    mac_address: '',
    location: '',
});

const submit = () => {
    form.post(route('sistem.kiosk-devices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Perangkat PC Kiosk berhasil didaftarkan.');
        },
    });
};

const toggleDevice = (id) => {
    router.patch(route('sistem.kiosk-devices.toggle', id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Status perangkat berhasil diubah.'),
    });
};

const deleteDevice = async (id) => {
    const ok = await confirm({
        title: 'Hapus Perangkat Kiosk',
        message: 'Apakah Anda yakin ingin menghapus MAC Address perangkat kiosk ini? PC tersebut tidak lagi dapat membuka kiosk presensi.',
        confirmText: 'Hapus Perangkat',
        variant: 'danger',
    });

    if (ok) {
        router.delete(route('sistem.kiosk-devices.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Perangkat kiosk berhasil dihapus.'),
        });
    }
};
</script>

<template>
    <Head title="Perangkat Kiosk Presensi (MAC)" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Perangkat Kiosk Presensi Siswa (MAC Address)"
                subtitle="Kiosk Presensi RFID hanya dapat dibuka oleh PC dengan MAC Address terdaftar & aktif"
            />

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Form Tambah Perangkat -->
                <AppCard class="space-y-4 h-fit">
                    <h2 class="font-bold text-sm text-ink">Tambah PC Kiosk</h2>

                    <form class="space-y-4" @submit.prevent="submit">
                        <FormField label="Nama Perangkat" :error="form.errors.name" required>
                            <AppInput
                                v-model="form.name"
                                placeholder="PC Kiosk Gerbang Utama"
                                required
                            />
                        </FormField>

                        <FormField label="MAC Address PC" help="Format: AA:BB:CC:DD:EE:FF" :error="form.errors.mac_address" required>
                            <AppInput
                                v-model="form.mac_address"
                                placeholder="AA:BB:CC:DD:EE:FF"
                                class="font-numeric uppercase"
                                required
                            />
                        </FormField>

                        <FormField label="Lokasi (Opsional)" :error="form.errors.location">
                            <AppInput
                                v-model="form.location"
                                placeholder="Lobby Depan Sekolah"
                            />
                        </FormField>

                        <AppButton
                            type="submit"
                            variant="primary"
                            class="w-full"
                            :loading="form.processing"
                        >
                            Daftarkan MAC Address
                        </AppButton>
                    </form>
                </AppCard>

                <!-- Daftar Perangkat -->
                <div class="lg:col-span-2">
                    <AppCard padding="none">
                        <div class="px-5 py-4 border-b border-line">
                            <h2 class="font-bold text-sm text-ink">
                                Daftar MAC Address Terotorisasi ({{ devices.length }})
                            </h2>
                        </div>

                        <div v-if="devices.length === 0" class="p-8">
                            <EmptyState
                                title="Belum ada perangkat"
                                description="Belum ada PC Kiosk yang didaftarkan dalam sistem."
                            />
                        </div>

                        <div v-else class="divide-y divide-line">
                            <div
                                v-for="device in devices"
                                :key="device.id"
                                class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-surface-raised transition-colors"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-sm text-ink">{{ device.name }}</h3>
                                        <StatusBadge
                                            :variant="device.is_active ? 'success' : 'danger'"
                                            :text="device.is_active ? 'Aktif' : 'Nonaktif'"
                                            size="sm"
                                        />
                                    </div>
                                    <p class="font-numeric text-xs font-bold text-brand-700">{{ device.mac_address }}</p>
                                    <p class="text-xs text-ink-muted">Lokasi: {{ device.location }} &middot; Terakhir aktif: {{ device.last_seen_at }}</p>
                                    <a
                                        v-if="device.is_active"
                                        :href="device.kiosk_url"
                                        target="_blank"
                                        class="inline-block text-xs font-bold text-brand-700 hover:underline mt-1"
                                    >
                                        Buka Kiosk ({{ device.mac_address }}) &rarr;
                                    </a>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <AppButton
                                        variant="secondary"
                                        size="sm"
                                        @click="toggleDevice(device.id)"
                                    >
                                        {{ device.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </AppButton>
                                    <AppButton
                                        variant="danger"
                                        size="sm"
                                        @click="deleteDevice(device.id)"
                                    >
                                        Hapus
                                    </AppButton>
                                </div>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
