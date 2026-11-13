<script setup>
import { ref, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => {
        passwordInput.value?.$el?.focus?.() || passwordInput.value?.focus?.();
    });
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => {
            passwordInput.value?.$el?.focus?.() || passwordInput.value?.focus?.();
        },
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header>
            <h2 class="text-base font-bold text-ink">
                Hapus Akun
            </h2>
            <p class="mt-1 text-xs text-ink-muted">
                Setelah akun Anda dihapus, semua data dan sumber daya yang terkait akan dihapus secara permanen. Sebelum menghapus akun, silakan unduh data yang ingin Anda simpan.
            </p>
        </header>

        <div>
            <AppButton variant="danger" @click="confirmUserDeletion">
                Hapus Akun Pengguna
            </AppButton>
        </div>

        <Modal :show="confirmingUserDeletion" title="Konfirmasi Penghapusan Akun" @close="closeModal">
            <div class="space-y-4">
                <p class="text-xs text-ink-muted leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun ini? Setelah akun dihapus, seluruh data tidak dapat dipulihkan. Masukkan kata sandi Anda untuk mengonfirmasi tindakan ini.
                </p>

                <FormField label="Kata Sandi Akun" :error="form.errors.password" required>
                    <AppInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Masukkan kata sandi untuk verifikasi"
                        @keyup.enter="deleteUser"
                    />
                </FormField>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <AppButton variant="ghost" @click="closeModal">
                        Batal
                    </AppButton>
                    <AppButton
                        variant="danger"
                        :loading="form.processing"
                        @click="deleteUser"
                    >
                        Hapus Akun
                    </AppButton>
                </div>
            </template>
        </Modal>
    </section>
</template>
