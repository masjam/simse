<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    role: { type: Object, required: true },
    canResetPassword: { type: Boolean, default: true },
    status: { type: String, default: '' },
    otpSentPhone: { type: String, default: '' },
});

const showPassword = ref(false);
const parentAuthMode = ref('password'); // 'password' | 'otp'
const otpStepRequested = ref(Boolean(props.otpSentPhone));

const form = useForm({
    email: '',
    password: '',
    remember: false,
    selected_role: props.role.id,
});

const otpRequestForm = useForm({
    phone_number: props.otpSentPhone || '',
    birth_date: '',
});

const otpVerifyForm = useForm({
    phone_number: props.otpSentPhone || '',
    otp_code: '',
});

const submitPassword = () => {
    form.selected_role = props.role.id;
    const postUrl = route().has('role.login.store')
        ? route('role.login.store', { role: props.role.id })
        : route('login');

    form.post(postUrl, {
        onFinish: () => form.reset('password'),
    });
};

const submitOtpRequest = () => {
    otpRequestForm.post(route('ortu.otp.request'), {
        preserveScroll: true,
        onSuccess: () => {
            otpVerifyForm.phone_number = otpRequestForm.phone_number;
            otpVerifyForm.clearErrors();
            otpStepRequested.value = true;
        },
    });
};

const submitOtpVerify = () => {
    otpVerifyForm.post(route('ortu.otp.verify'), {
        preserveScroll: true,
        onFinish: () => otpVerifyForm.reset('otp_code'),
    });
};

const resetOtpStep = () => {
    otpStepRequested.value = false;
    otpVerifyForm.reset('otp_code');
    otpVerifyForm.clearErrors();
};
</script>

<template>
    <div class="bg-surface rounded-card border border-line p-6 sm:p-8 shadow-card flex flex-col justify-between">
        <div>
            <!-- Status Alert -->
            <div v-if="status" class="mb-4 p-3 rounded-control bg-status-success-bg text-status-success-fg border border-status-success-line text-xs font-semibold">
                {{ status }}
            </div>

            <!-- Role Header in Card -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-ink">Masuk ke SIMSE</h3>
                <p class="text-xs text-ink-muted mt-1">
                    Masuk sebagai <strong class="text-brand-700">{{ role.title }}</strong>
                </p>
            </div>

            <!-- Parent OTP Switcher if Role is Ortu -->
            <div v-if="role.id === 'ortu'" class="mb-5 p-1 bg-surface-raised rounded-control border border-line flex gap-1">
                <AppButton
                    size="sm"
                    :variant="parentAuthMode === 'password' ? 'primary' : 'ghost'"
                    class="flex-1"
                    @click="parentAuthMode = 'password'"
                >
                    Email & Password
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="parentAuthMode === 'otp' ? 'primary' : 'ghost'"
                    class="flex-1"
                    @click="parentAuthMode = 'otp'"
                >
                    OTP WhatsApp
                </AppButton>
            </div>

            <!-- Standard Password Form -->
            <form v-if="role.id !== 'ortu' || parentAuthMode === 'password'" @submit.prevent="submitPassword" class="space-y-4">
                <FormField :label="role.id === 'siswa' ? 'NISN / Pos-el Siswa' : (role.id === 'guru' ? 'NIP / Pos-el Guru' : 'Pos-el / Nama Pengguna')" :error="form.errors.email" required>
                    <AppInput
                        v-model="form.email"
                        type="text"
                        :placeholder="role.placeholder"
                        autofocus
                        required
                    />
                </FormField>

                <FormField label="Kata Sandi" :error="form.errors.password" required>
                    <div class="relative">
                        <AppInput
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            required
                        />
                        <AppButton
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="absolute right-1 top-1/2 -translate-y-1/2 text-xs font-bold text-ink-muted hover:text-ink select-none h-7 px-2"
                            @click="showPassword = !showPassword"
                        >
                            {{ showPassword ? 'Sembunyikan' : 'Lihat' }}
                        </AppButton>
                    </div>
                </FormField>

                <div class="flex items-center justify-between text-xs pt-1">
                    <AppCheckbox
                        v-model="form.remember"
                        label="Ingat saya"
                    />

                    <Link
                        v-if="canResetPassword && route().has('password.request')"
                        :href="route('password.request')"
                        class="text-brand-700 font-bold hover:underline"
                    >
                        Lupa kata sandi?
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
                        Masuk ke Akun {{ role.shortTitle }}
                    </AppButton>
                </div>
            </form>

            <!-- Parent OTP Request Step -->
            <form v-else-if="!otpStepRequested" @submit.prevent="submitOtpRequest" class="space-y-4">
                <FormField label="Nomor WhatsApp Wali Santri" help="Format: 08123456789 atau 628123456789" :error="otpRequestForm.errors.phone_number" required>
                    <AppInput
                        v-model="otpRequestForm.phone_number"
                        type="tel"
                        placeholder="08xxxxxxxxxx"
                        required
                    />
                </FormField>

                <FormField label="Tanggal Lahir Siswa / Ananda" help="Untuk verifikasi keamanan identitas anak" :error="otpRequestForm.errors.birth_date" required>
                    <AppInput
                        v-model="otpRequestForm.birth_date"
                        type="date"
                        required
                    />
                </FormField>

                <div class="pt-2">
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full shadow-card"
                        :loading="otpRequestForm.processing"
                    >
                        Kirim Kode OTP via WhatsApp
                    </AppButton>
                </div>
            </form>

            <!-- Parent OTP Verification Step -->
            <form v-else @submit.prevent="submitOtpVerify" class="space-y-4">
                <div class="p-3 rounded-control bg-brand-50 border border-brand-200 text-xs text-brand-900">
                    Kode OTP 6-digit telah dikirim ke nomor <strong>{{ otpVerifyForm.phone_number }}</strong>.
                </div>

                <FormField label="Masukkan Kode OTP (6 Digit)" :error="otpVerifyForm.errors.otp_code" required>
                    <AppInput
                        v-model="otpVerifyForm.otp_code"
                        type="text"
                        maxlength="6"
                        placeholder="123456"
                        class="text-center font-mono text-lg tracking-widest"
                        required
                    />
                </FormField>

                <div class="flex items-center justify-between text-xs">
                    <AppButton
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="resetOtpStep"
                    >
                        ← Ubah Nomor WhatsApp
                    </AppButton>
                    <AppButton
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="text-brand-700"
                        :loading="otpRequestForm.processing"
                        @click="submitOtpRequest"
                    >
                        Kirim Ulang OTP
                    </AppButton>
                </div>

                <div class="pt-2">
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full shadow-card"
                        :loading="otpVerifyForm.processing"
                    >
                        Verifikasi & Masuk
                    </AppButton>
                </div>
            </form>
        </div>

        <div class="pt-6 border-t border-line text-center text-xs text-ink-muted">
            Butuh bantuan akses login? Hubungi Helpdesk ICT SD Muhammadiyah Al Mujahidin.
        </div>
    </div>
</template>
