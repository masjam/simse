<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import { usePwaInstall } from '@/composables/usePwaInstall';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
    twoFactorEnabled: {
        type: Boolean,
        default: false,
    },
});

const { canInstall, isInstalled, installPwa } = usePwaInstall();
</script>

<template>
    <Head title="Pengaturan Profil" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-4xl">
            <PageHeader
                title="Profil Pengguna"
                subtitle="Kelola informasi akun, kata sandi, dan preferensi keamanan Anda"
            />

            <!-- PWA Installation Card -->
            <AppCard>
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-ink">Aplikasi SIMSE di Perangkat Anda</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Pasang SIMSE SD Muhammadiyah Al Mujahidin ke layar utama ponsel atau desktop untuk akses cepat dan hemat kuota.
                        </p>
                    </div>
                    <div>
                        <AppButton
                            v-if="canInstall"
                            variant="primary"
                            size="sm"
                            @click="installPwa"
                        >
                            Pasang Aplikasi
                        </AppButton>
                        <span v-else-if="isInstalled" class="text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1.5 rounded-control border border-brand-200">
                            Aplikasi Terpasang
                        </span>
                        <AppButton
                            v-else
                            variant="secondary"
                            size="sm"
                            @click="installPwa"
                        >
                            Pasang Aplikasi
                        </AppButton>
                    </div>
                </div>
            </AppCard>

            <AppCard>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </AppCard>

            <AppCard>
                <UpdatePasswordForm class="max-w-xl" />
            </AppCard>

            <AppCard>
                <TwoFactorAuthenticationForm
                    :two-factor-enabled="twoFactorEnabled"
                    class="max-w-xl"
                />
            </AppCard>

            <AppCard>
                <DeleteUserForm class="max-w-xl" />
            </AppCard>
        </div>
    </PortalShell>
</template>
