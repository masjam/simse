<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Konfirmasi Kata Sandi - SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Area Terproteksi</h2>
                    <p class="text-xs text-ink-muted">
                        Ini adalah area keamanan khusus. Mohon konfirmasikan kata sandi akun Anda untuk melanjutkan.
                    </p>
                </div>

                <AppCard>
                    <form @submit.prevent="submit" class="space-y-4">
                        <FormField label="Kata Sandi Akun" :error="form.errors.password" required>
                            <AppInput
                                v-model="form.password"
                                type="password"
                                required
                                autofocus
                            />
                        </FormField>

                        <div class="pt-2">
                            <AppButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                class="w-full shadow-card"
                                :loading="form.processing"
                            >
                                Konfirmasi & Lanjutkan
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
