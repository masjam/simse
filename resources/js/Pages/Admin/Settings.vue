<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const activeTab = ref('info');

const form = ref({
    school_name: 'SD Muhammadiyah Al Mujahidin Wonosari',
    npsn: '20402170',
    address: 'Jl. Mayang, Gadungsari, Wonosari, Gunungkidul, D.I. Yogyakarta 55812',
    phone: '(0274) 391147',
    email: 'admin@sdmujahidin-wns.sch.id',
    website: 'https://www.sdmujahidin-wns.sch.id',
});

const saveSettings = () => {
    toast.success('Pengaturan sistem berhasil disimpan.');
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Pengaturan Sistem & Profil Lembaga"
                subtitle="Konfigurasi identitas sekolah dan integrasi sistem aplikasi SIMSE"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Navigation Tabs -->
                <AppCard class="p-3 space-y-1 h-fit" padding="none">
                    <AppButton
                        class="w-full justify-start text-left"
                        :variant="activeTab === 'info' ? 'primary' : 'ghost'"
                        @click="activeTab = 'info'"
                    >
                        🏫 Informasi Sekolah
                    </AppButton>
                    <Link :href="route('sistem.wa')" class="block">
                        <AppButton
                            class="w-full justify-start text-left"
                            variant="ghost"
                        >
                            📱 WhatsApp Gateway
                        </AppButton>
                    </Link>
                    <Link :href="route('sistem.kiosk-devices.index')" class="block">
                        <AppButton
                            class="w-full justify-start text-left"
                            variant="ghost"
                        >
                            💻 Perangkat Kiosk (MAC)
                        </AppButton>
                    </Link>
                    <Link :href="route('sistem.website')" class="block">
                        <AppButton
                            class="w-full justify-start text-left"
                            variant="ghost"
                        >
                            🌐 CMS Website & Berita
                        </AppButton>
                    </Link>
                </AppCard>

                <!-- Content Area -->
                <div class="md:col-span-2">
                    <AppCard class="space-y-5 shadow-card">
                        <div class="border-b border-line pb-3">
                            <h2 class="text-base font-bold text-ink">
                                Informasi Sekolah & NPSN
                            </h2>
                            <p class="text-xs text-ink-muted">Identitas resmi yang tercetak pada kartu siswa, raport, dan kop surat.</p>
                        </div>

                        <form @submit.prevent="saveSettings" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <FormField label="Nama Sekolah" required>
                                    <AppInput
                                        v-model="form.school_name"
                                        required
                                    />
                                </FormField>
                                <FormField label="NPSN" required>
                                    <AppInput
                                        v-model="form.npsn"
                                        class="font-numeric"
                                        required
                                    />
                                </FormField>
                            </div>

                            <FormField label="Alamat Lengkap Sekolah" required>
                                <AppTextarea
                                    v-model="form.address"
                                    :rows="2"
                                    required
                                />
                            </FormField>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <FormField label="Nomor Telepon">
                                    <AppInput
                                        v-model="form.phone"
                                        class="font-numeric"
                                    />
                                </FormField>
                                <FormField label="Email Resmi">
                                    <AppInput
                                        v-model="form.email"
                                        type="email"
                                    />
                                </FormField>
                                <FormField label="Website Resmi">
                                    <AppInput
                                        v-model="form.website"
                                        type="url"
                                    />
                                </FormField>
                            </div>

                            <div class="pt-4 border-t border-line flex justify-end">
                                <AppButton type="submit" variant="primary">
                                    Simpan Perubahan
                                </AppButton>
                            </div>
                        </form>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
