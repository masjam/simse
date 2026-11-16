<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.clearErrors();
    form.code = '';
    form.recovery_code = '';
};

const submit = () => {
    form.post(route('two-factor.verify'), {
        onFinish: () => {
            form.reset('code', 'recovery_code');
        },
    });
};
</script>

<template>
    <Head title="Verifikasi Dua Faktor (2FA) - SIMSE" />
    <PortalShell variant="bare">
        <div class="min-h-screen py-12 px-4 flex flex-col justify-center items-center">
            <div class="w-full max-w-md space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 bg-brand-50 border border-brand-200 rounded-control flex items-center justify-center p-2.5 mx-auto shadow-card">
                        <ApplicationLogo class="w-full h-full text-brand-700" />
                    </div>
                    <h2 class="text-xl font-bold text-ink">Verifikasi Dua Faktor</h2>
                    <p class="text-xs text-ink-muted">
                        <template v-if="!recovery">
                            Buka aplikasi authenticator (Google Authenticator / Aegis) Anda dan masukkan kode verifikasi 6-digit.
                        </template>
                        <template v-else>
                            Masukkan salah satu kode pemulihan darurat Anda untuk mengakses portal.
                        </template>
                    </p>
                </div>

                <AppCard>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div v-if="!recovery">
                            <FormField label="Kode Autentikasi 6-Digit" :error="form.errors.code" required>
                                <AppInput
                                    v-model="form.code"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="6"
                                    placeholder="123456"
                                    class="text-center tracking-widest text-lg font-mono font-bold"
                                    required
                                    autofocus
                                />
                            </FormField>
                        </div>

                        <div v-else>
                            <FormField label="Kode Pemulihan Darurat" :error="form.errors.code || form.errors.recovery_code" required>
                                <AppInput
                                    v-model="form.recovery_code"
                                    type="text"
                                    placeholder="XXXX-XXXX-XXXX"
                                    class="text-center tracking-wider font-mono font-bold uppercase"
                                    required
                                    autofocus
                                />
                            </FormField>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <AppButton
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="!px-0 text-brand-700 font-semibold hover:underline"
                                @click="toggleRecovery"
                            >
                                <template v-if="!recovery">
                                    Gunakan kode pemulihan darurat
                                </template>
                                <template v-else>
                                    Gunakan kode autentikator 6-digit
                                </template>
                            </AppButton>

                            <Link :href="route('login')" class="text-ink-muted hover:text-ink">
                                Batal
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
                                Verifikasi & Lanjutkan
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
