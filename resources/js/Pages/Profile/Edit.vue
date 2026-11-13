<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <Head title="Pengaturan Profil" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-4xl">
            <PageHeader
                title="Profil Pengguna"
                subtitle="Kelola informasi akun, kata sandi, dan preferensi keamanan Anda"
            />

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
                <DeleteUserForm class="max-w-xl" />
            </AppCard>
        </div>
    </PortalShell>
</template>
