<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Atur Ulang Kata Sandi - SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Atur Ulang Kata Sandi</h2>
                    <p class="text-xs text-ink-muted">Buat kata sandi baru untuk akun SIMSE Anda</p>
                </div>

                <AppCard>
                    <form @submit.prevent="submit" class="space-y-4">
                        <FormField label="Alamat Pos-el (Email)" :error="form.errors.email" required>
                            <AppInput
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                            />
                        </FormField>

                        <FormField label="Kata Sandi Baru" :error="form.errors.password" required>
                            <AppInput
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                required
                            />
                        </FormField>

                        <FormField label="Konfirmasi Kata Sandi Baru" :error="form.errors.password_confirmation" required>
                            <AppInput
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ulangi kata sandi"
                                required
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
                                Simpan Kata Sandi Baru
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
