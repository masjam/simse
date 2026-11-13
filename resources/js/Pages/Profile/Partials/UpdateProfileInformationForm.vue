<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const toast = useToast();
const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Profil informasi akun berhasil diperbarui!'),
    });
};
</script>

<template>
    <section class="space-y-4">
        <div>
            <h3 class="text-base font-bold text-ink">Informasi Profil Akun</h3>
            <p class="text-xs text-ink-muted">Perbarui nama pengguna dan alamat pos-el resmi akun Anda.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 max-w-xl">
            <FormField label="Nama Lengkap" :error="form.errors.name" required>
                <AppInput
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
            </FormField>

            <FormField label="Alamat Pos-el (Email)" :error="form.errors.email" required>
                <AppInput
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                />
            </FormField>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-3 bg-status-warning-bg border border-status-warning-line rounded-control text-xs text-status-warning-fg space-y-1">
                <p>Alamat pos-el Anda belum diverifikasi.</p>
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="font-bold underline hover:text-ink"
                >
                    Klik di sini untuk mengirim ulang tautan verifikasi pos-el.
                </Link>
                <div v-if="status === 'verification-link-sent'" class="text-status-success-fg font-semibold mt-1">
                    Tautan verifikasi baru telah berhasil dikirim ke alamat pos-el Anda.
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <AppButton
                    type="submit"
                    variant="primary"
                    :loading="form.processing"
                >
                    Simpan Perubahan
                </AppButton>
            </div>
        </form>
    </section>
</template>
