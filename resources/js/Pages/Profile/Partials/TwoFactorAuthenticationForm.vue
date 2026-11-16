<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    twoFactorEnabled: {
        type: Boolean,
        default: false,
    },
});

const toast = useToast();
const enabling = ref(false);
const qrCode = ref('');
const setupKey = ref('');
const recoveryCodes = ref([]);
const confirming = ref(false);
const disabling = ref(false);

const confirmForm = useForm({
    code: '',
});

const disableForm = useForm({
    password: '',
});

const enableTwoFactor = async () => {
    enabling.value = true;
    try {
        const response = await axios.post(route('two-factor.enable'));
        qrCode.value = response.data.qr_code;
        setupKey.value = response.data.secret;
        recoveryCodes.value = response.data.recovery_codes || [];
        confirming.value = true;
    } catch (error) {
        toast.error('Gagal memulai konfigurasi 2FA. Silakan coba lagi.');
    } finally {
        enabling.value = false;
    }
};

const confirmTwoFactor = () => {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => {
            confirming.value = false;
            confirmForm.reset();
            toast.success('Autentikasi dua faktor berhasil diaktifkan.');
            router.reload({ only: ['twoFactorEnabled'] });
        },
        onError: () => {
            toast.error('Kode verifikasi salah.');
        },
    });
};

const disableTwoFactor = () => {
    disableForm.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            disableForm.reset();
            toast.success('Autentikasi dua faktor telah dinonaktifkan.');
            router.reload({ only: ['twoFactorEnabled'] });
        },
        onError: () => {
            toast.error('Kata sandi tidak sesuai.');
        },
    });
};
</script>

<template>
    <section>
        <header>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-ink">
                    Autentikasi Dua Faktor (2FA)
                </h2>
                <span
                    v-if="twoFactorEnabled"
                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-status-success-bg text-status-success-fg border border-status-success-line"
                >
                    Aktif
                </span>
                <span
                    v-else
                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-ink-subtle text-ink-muted border border-border"
                >
                    Nonaktif
                </span>
            </div>
            <p class="mt-1 text-xs text-ink-muted">
                Tambahkan lapisan keamanan tambahan pada akun Anda menggunakan standar TOTP (Google Authenticator, Aegis, atau 1Password). Sangat dianjurkan untuk akun pengelola keuangan dan pimpinan.
            </p>
        </header>

        <div class="mt-6">
            <div v-if="!twoFactorEnabled">
                <AppButton
                    variant="primary"
                    size="sm"
                    :loading="enabling"
                    @click="enableTwoFactor"
                >
                    Aktifkan 2FA
                </AppButton>
            </div>

            <div v-else class="space-y-4">
                <p class="text-xs text-ink-muted">
                    Akun Anda saat ini dilindungi dengan kode verifikasi 6-digit setiap kali masuk.
                </p>
                <AppButton
                    variant="danger"
                    size="sm"
                    @click="disabling = true"
                >
                    Nonaktifkan 2FA
                </AppButton>
            </div>
        </div>

        <!-- Modal Konfirmasi / Setup 2FA -->
        <Modal
            :show="confirming"
            title="Konfigurasi Autentikasi Dua Faktor"
            @close="confirming = false"
        >
            <div class="space-y-4 text-xs">
                <p class="text-ink">
                    Pindai kode QR di bawah ini menggunakan aplikasi authenticator Anda (seperti Google Authenticator, Microsoft Authenticator, atau Aegis):
                </p>

                <div v-if="qrCode" class="flex justify-center p-3 bg-white rounded-control border border-border">
                    <img :src="qrCode" alt="QR Code 2FA" class="w-48 h-48" />
                </div>

                <div v-if="setupKey" class="p-2.5 bg-paper-sunken rounded-control border border-border">
                    <span class="block text-[11px] font-bold text-ink-muted">Kunci Manual (Setup Key):</span>
                    <span class="font-mono font-bold text-ink select-all break-all text-xs">{{ setupKey }}</span>
                </div>

                <div v-if="recoveryCodes.length > 0" class="p-3 bg-status-warning-bg border border-status-warning-line rounded-control text-status-warning-fg space-y-1.5">
                    <span class="font-bold block">Simpan Kode Pemulihan Darurat Anda:</span>
                    <p class="text-[11px] leading-relaxed">
                        Jika Anda kehilangan akses ke aplikasi authenticator, gunakan salah satu kode ini untuk masuk. Setiap kode hanya dapat dipakai satu kali.
                    </p>
                    <div class="grid grid-cols-2 gap-1 font-mono text-[11px] pt-1">
                        <span v-for="c in recoveryCodes" :key="c" class="font-bold select-all bg-white/70 px-1.5 py-0.5 rounded border border-status-warning-line/40">
                            {{ c }}
                        </span>
                    </div>
                </div>

                <form @submit.prevent="confirmTwoFactor" class="space-y-3 pt-2">
                    <FormField label="Masukkan Kode 6-Digit dari Aplikasi" :error="confirmForm.errors.code" required>
                        <AppInput
                            v-model="confirmForm.code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="123456"
                            class="text-center font-mono font-bold tracking-widest text-base"
                            required
                        />
                    </FormField>

                    <div class="flex justify-end gap-2 pt-2">
                        <AppButton
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="confirming = false"
                        >
                            Batal
                        </AppButton>
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="sm"
                            :loading="confirmForm.processing"
                        >
                            Verifikasi & Simpan
                        </AppButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Nonaktifkan 2FA -->
        <Modal
            :show="disabling"
            title="Nonaktifkan Autentikasi Dua Faktor"
            @close="disabling = false"
        >
            <form @submit.prevent="disableTwoFactor" class="space-y-4 text-xs">
                <p class="text-ink">
                    Demi keamanan, silakan masukkan kata sandi Anda saat ini untuk mengonfirmasi penonaktifan 2FA.
                </p>

                <FormField label="Kata Sandi Saat Ini" :error="disableForm.errors.password" required>
                    <AppInput
                        v-model="disableForm.password"
                        type="password"
                        required
                    />
                </FormField>

                <div class="flex justify-end gap-2 pt-2">
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="disabling = false"
                    >
                        Batal
                    </AppButton>
                    <AppButton
                        type="submit"
                        variant="danger"
                        size="sm"
                        :loading="disableForm.processing"
                    >
                        Nonaktifkan
                    </AppButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
