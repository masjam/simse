<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
    modules: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();

const settingsForm = useForm({
    school_name: props.settings.school_name || '',
    school_address: props.settings.school_address || '',
    school_phone: props.settings.school_phone || '',
    school_email: props.settings.school_email || '',
    active_semester: props.settings.active_semester || '',
    school_latitude: props.settings.school_latitude || '-7.9664',
    school_longitude: props.settings.school_longitude || '110.6035',
    school_radius_meters: props.settings.school_radius_meters || '150',
});

const saveSettings = () => {
    settingsForm.post(route('sistem.pengaturan.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Pengaturan sistem berhasil disimpan.'),
    });
};

const toggleModule = (moduleId) => {
    router.patch(route('sistem.modules.toggle', moduleId), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Status modul berhasil diperbarui.'),
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem & Manajemen Modul" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Pengaturan Sistem & Manajemen Modul"
                subtitle="Kelola identitas sekolah, koordinat radius presensi, serta aktifkan atau nonaktifkan modul SIMSE"
            />

            <!-- Identitas Sekolah -->
            <AppCard class="space-y-4">
                <h2 class="text-sm font-bold text-ink">Identitas & Periode Sekolah</h2>

                <form @submit.prevent="saveSettings" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormField label="Nama Sekolah" :error="settingsForm.errors.school_name" required>
                            <AppInput
                                v-model="settingsForm.school_name"
                                required
                            />
                        </FormField>

                        <FormField label="Semester Aktif" :error="settingsForm.errors.active_semester" required>
                            <AppInput
                                v-model="settingsForm.active_semester"
                                placeholder="Contoh: Ganjil 2026/2027"
                                required
                            />
                        </FormField>

                        <FormField label="No. Telepon Sekolah" :error="settingsForm.errors.school_phone">
                            <AppInput
                                v-model="settingsForm.school_phone"
                                placeholder="(0274) 391147"
                            />
                        </FormField>

                        <FormField label="Email Resmi" :error="settingsForm.errors.school_email">
                            <AppInput
                                v-model="settingsForm.school_email"
                                type="email"
                                placeholder="admin@sdmujahidin-wns.sch.id"
                            />
                        </FormField>
                    </div>

                    <FormField label="Alamat Sekolah" :error="settingsForm.errors.school_address" required>
                        <AppInput
                            v-model="settingsForm.school_address"
                            required
                        />
                    </FormField>

                    <!-- Radius Presensi Geolokasi Pegawai -->
                    <div class="pt-4 border-t border-line space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700">
                            Konfigurasi Radius Presensi GPS Pegawai
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <FormField label="Latitude Sekolah" :error="settingsForm.errors.school_latitude">
                                <AppInput
                                    v-model="settingsForm.school_latitude"
                                    placeholder="-7.9664"
                                    class="font-numeric"
                                />
                            </FormField>

                            <FormField label="Longitude Sekolah" :error="settingsForm.errors.school_longitude">
                                <AppInput
                                    v-model="settingsForm.school_longitude"
                                    placeholder="110.6035"
                                    class="font-numeric"
                                />
                            </FormField>

                            <FormField label="Radius Toleransi (Meter)" :error="settingsForm.errors.school_radius_meters">
                                <AppInput
                                    v-model="settingsForm.school_radius_meters"
                                    type="number"
                                    min="10"
                                    max="5000"
                                    placeholder="150"
                                    class="font-numeric"
                                />
                            </FormField>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <AppButton
                            type="submit"
                            variant="primary"
                            :loading="settingsForm.processing"
                        >
                            Simpan Pengaturan
                        </AppButton>
                    </div>
                </form>
            </AppCard>

            <!-- Toggle Modul -->
            <AppCard class="space-y-4">
                <h2 class="text-sm font-bold text-ink">Status Modul SIMSE</h2>
                <p class="text-xs text-ink-muted">Modul nonaktif otomatis tersembunyi dari navigasi dan route-nya diblokir.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        class="p-4 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-4"
                    >
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-sm text-ink truncate">{{ mod.name }}</p>
                                <span class="px-2 py-0.5 rounded-control bg-surface border border-line text-[10px] font-numeric text-ink-muted">
                                    {{ mod.code }}
                                </span>
                            </div>
                            <p class="text-xs text-ink-muted line-clamp-1">{{ mod.description }}</p>
                        </div>

                        <AppButton
                            size="sm"
                            :variant="mod.is_active ? 'primary' : 'secondary'"
                            class="shrink-0"
                            @click="toggleModule(mod.id)"
                        >
                            {{ mod.is_active ? '✓ Aktif' : 'Nonaktif' }}
                        </AppButton>
                    </div>
                </div>
            </AppCard>
        </div>
    </PortalShell>
</template>
