<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Kata Sandi - SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Pemulihan Kata Sandi</h2>
                    <p class="text-xs text-ink-muted">
                        Masukkan alamat pos-el terdaftar Anda untuk menerima tautan atur ulang kata sandi.
                    </p>
                </div>

                <AppCard>
                    <div v-if="status" class="mb-4 p-3 rounded-control bg-status-success-bg text-status-success-fg border border-status-success-line text-xs font-semibold">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <FormField label="Alamat Pos-el (Email)" :error="form.errors.email" required>
                            <AppInput
                                v-model="form.email"
                                type="email"
                                placeholder="nama@sdmujahidin-wns.sch.id"
                                required
                                autofocus
                            />
                        </FormField>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <Link :href="route('login')" class="text-brand-700 font-bold hover:underline">
                                ← Kembali ke Login
                            </Link>
                        </div>

                        <div class="pt-2">
                            <AppButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                class="w-full shadow-card"
                                :loading="form.processing"
                            >
                                Kirim Tautan Atur Ulang
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
