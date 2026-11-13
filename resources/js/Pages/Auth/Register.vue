<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Pendaftaran Akun SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <!-- Brand Header -->
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Pendaftaran Akun Baru</h2>
                    <p class="text-xs text-ink-muted">Sistem Informasi Manajemen Sekolah SD Muhammadiyah Al Mujahidin</p>
                </div>

                <AppCard>
                    <form @submit.prevent="submit" class="space-y-4">
                        <FormField label="Nama Lengkap" :error="form.errors.name" required>
                            <AppInput
                                v-model="form.name"
                                placeholder="Contoh: Ahmad Fauzan"
                                required
                                autofocus
                            />
                        </FormField>

                        <FormField label="Alamat Pos-el (Email)" :error="form.errors.email" required>
                            <AppInput
                                v-model="form.email"
                                type="email"
                                placeholder="nama@sdmujahidin-wns.sch.id"
                                required
                            />
                        </FormField>

                        <FormField label="Kata Sandi" :error="form.errors.password" required>
                            <AppInput
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                required
                            />
                        </FormField>

                        <FormField label="Konfirmasi Kata Sandi" :error="form.errors.password_confirmation" required>
                            <AppInput
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ulangi kata sandi"
                                required
                            />
                        </FormField>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <Link :href="route('login')" class="text-brand-700 font-bold hover:underline">
                                Sudah punya akun? Masuk
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
                                Daftarkan Akun
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
