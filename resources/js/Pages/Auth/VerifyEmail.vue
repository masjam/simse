<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Verifikasi Pos-el - SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Verifikasi Alamat Pos-el</h2>
                    <p class="text-xs text-ink-muted">
                        Terima kasih telah mendaftar! Mohon verifikasi alamat pos-el Anda dengan mengeklik tautan yang baru saja kami kirimkan ke kotak masuk Anda.
                    </p>
                </div>

                <AppCard>
                    <div
                        v-if="verificationLinkSent"
                        class="mb-4 p-3 rounded-control bg-status-success-bg text-status-success-fg border border-status-success-line text-xs font-semibold"
                    >
                        Tautan verifikasi baru telah berhasil dikirimkan ke alamat pos-el yang Anda cantumkan saat pendaftaran.
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="lg"
                            class="w-full shadow-card"
                            :loading="form.processing"
                        >
                            Kirim Ulang Tautan Verifikasi
                        </AppButton>

                        <div class="text-center pt-2">
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-xs text-status-danger-fg font-bold hover:underline"
                            >
                                Keluar dari Akun (Log Out)
                            </Link>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
