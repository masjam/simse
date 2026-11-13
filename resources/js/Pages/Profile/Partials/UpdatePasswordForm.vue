<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Kata sandi berhasil diperbarui.');
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.$el?.focus?.() || passwordInput.value?.focus?.();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.$el?.focus?.() || currentPasswordInput.value?.focus?.();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-bold text-ink">
                Perbarui Kata Sandi
            </h2>
            <p class="mt-1 text-xs text-ink-muted">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan aman.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
            <FormField label="Kata Sandi Saat Ini" :error="form.errors.current_password" required>
                <AppInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                />
            </FormField>

            <FormField label="Kata Sandi Baru" :error="form.errors.password" required>
                <AppInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                />
            </FormField>

            <FormField label="Konfirmasi Kata Sandi Baru" :error="form.errors.password_confirmation" required>
                <AppInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
            </FormField>

            <div class="flex items-center gap-4 pt-2">
                <AppButton type="submit" variant="primary" :loading="form.processing">
                    Simpan Perubahan
                </AppButton>
            </div>
        </form>
    </section>
</template>
