<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import Stepper from '@/Components/ui/Stepper.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import FileInput from '@/Components/ui/FileInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const currentStep = ref(1);

const steps = [
    { title: 'Data Siswa', description: 'Biodata calon peserta didik' },
    { title: 'Data Orang Tua', description: 'Kontak wali & verifikasi WA' },
    { title: 'Unggah Berkas', description: 'Dokumen KK & Akta' },
];

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const form = useForm({
    student_name: '',
    nik: '',
    nisn: '',
    gender: 'L',
    birth_place: 'Gunungkidul',
    birth_date: '2019-05-15',
    address: '',
    previous_school: '',
    parent_name: '',
    parent_phone: '',
    parent_email: '',
    parent_job: '',
    kk_file: null,
    birth_certificate_file: null,
    ijazah_file: null,
});

// Autosave non-file fields to localStorage
const STORAGE_KEY = 'simse_ppdb_draft';
onMounted(() => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const data = JSON.parse(saved);
            Object.keys(data).forEach((k) => {
                if (k in form && !k.endsWith('_file')) {
                    form[k] = data[k];
                }
            });
        }
    } catch {
        // ignore parse error
    }
});

watch(
    () => ({
        student_name: form.student_name,
        nik: form.nik,
        nisn: form.nisn,
        gender: form.gender,
        birth_place: form.birth_place,
        birth_date: form.birth_date,
        address: form.address,
        previous_school: form.previous_school,
        parent_name: form.parent_name,
        parent_phone: form.parent_phone,
        parent_email: form.parent_email,
        parent_job: form.parent_job,
    }),
    (val) => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(val));
        } catch {
            // ignore storage full
        }
    },
    { deep: true }
);

const onFileChange = (field, file) => {
    form[field] = file;
};

const goToStep = (step) => {
    if (step === 2) {
        if (!form.student_name || !form.nik || !form.gender || !form.birth_place || !form.birth_date || !form.address) {
            toast.error('Mohon lengkapi data calon siswa yang berbintang wajib terlebih dahulu.');
            return;
        }
    }
    if (step === 3) {
        if (!form.parent_name || !form.parent_phone) {
            toast.error('Mohon lengkapi nama orang tua dan nomor WhatsApp aktif.');
            return;
        }
    }
    currentStep.value = step;
};

const submit = () => {
    form.post(route('ppdb.store'), {
        preserveScroll: true,
        onSuccess: () => {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch {
                // ignore
            }
        },
    });
};
</script>

<template>
    <Head title="Pendaftaran Siswa Baru (PPDB Online) - SD Muhammadiyah Al Mujahidin" />

    <PortalShell variant="bare">
        <div class="min-h-screen bg-surface-sunken py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <Link :href="route('welcome')" class="inline-flex items-center gap-1.5 text-brand-700 hover:text-brand-800 text-xs font-bold mb-2">
                        <span>← Kembali ke Beranda Utama</span>
                    </Link>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink">
                        Penerimaan Peserta Didik Baru (PPDB)
                    </h1>
                    <p class="text-xs text-ink-muted">
                        SD Muhammadiyah Al Mujahidin Wonosari • Tahun Ajaran 2026/2027
                    </p>
                </div>

                <!-- Stepper -->
                <AppCard class="shadow-card">
                    <Stepper :current-step="currentStep" :steps="steps" />
                </AppCard>

                <!-- Form Card -->
                <AppCard class="shadow-card">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Step 1: Calon Siswa -->
                        <div v-show="currentStep === 1" class="space-y-4">
                            <div class="border-b border-line pb-3">
                                <h2 class="text-base font-bold text-ink">Biodata Calon Siswa</h2>
                                <p class="text-xs text-ink-muted">Masukkan data calon peserta didik sesuai Kartu Keluarga / Akta Kelahiran.</p>
                            </div>

                            <FormField label="Nama Lengkap Siswa" :error="form.errors.student_name" required>
                                <AppInput
                                    v-model="form.student_name"
                                    placeholder="Contoh: Muhammad Fatih Al-Mujahidin"
                                    required
                                />
                            </FormField>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <FormField label="NIK (16 Digit)" :error="form.errors.nik" required>
                                    <AppInput
                                        v-model="form.nik"
                                        maxlength="16"
                                        placeholder="3403xxxxxxxxxxxx"
                                        required
                                    />
                                </FormField>
                                <FormField label="NISN (Jika sudah ada dari TK)" :error="form.errors.nisn">
                                    <AppInput
                                        v-model="form.nisn"
                                        maxlength="10"
                                        placeholder="0123456789"
                                    />
                                </FormField>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <FormField label="Jenis Kelamin" :error="form.errors.gender" required>
                                    <AppSelect
                                        v-model="form.gender"
                                        :options="genderOptions"
                                        required
                                    />
                                </FormField>
                                <FormField label="Tempat Lahir" :error="form.errors.birth_place" required>
                                    <AppInput
                                        v-model="form.birth_place"
                                        placeholder="Gunungkidul"
                                        required
                                    />
                                </FormField>
                                <FormField label="Tanggal Lahir" :error="form.errors.birth_date" required>
                                    <AppInput
                                        v-model="form.birth_date"
                                        type="date"
                                        required
                                    />
                                </FormField>
                            </div>

                            <FormField label="Alamat Tempat Tinggal" :error="form.errors.address" required>
                                <AppTextarea
                                    v-model="form.address"
                                    :rows="2"
                                    placeholder="Jl. KH. Agus Salim No. 12, Wonosari"
                                    required
                                />
                            </FormField>

                            <FormField label="Asal Sekolah TK / PAUD" :error="form.errors.previous_school">
                                <AppInput
                                    v-model="form.previous_school"
                                    placeholder="Contoh: TK ABA Wonosari I"
                                />
                            </FormField>

                            <div class="flex justify-end pt-4 border-t border-line">
                                <AppButton
                                    type="button"
                                    variant="primary"
                                    @click="goToStep(2)"
                                >
                                    Lanjut ke Data Orang Tua →
                                </AppButton>
                            </div>
                        </div>

                        <!-- Step 2: Data Orang Tua -->
                        <div v-show="currentStep === 2" class="space-y-4">
                            <div class="border-b border-line pb-3">
                                <h2 class="text-base font-bold text-ink">Data Orang Tua / Wali</h2>
                                <p class="text-xs text-ink-muted">Informasi kontak ayah / ibu untuk koordinasi dan verifikasi WhatsApp.</p>
                            </div>

                            <FormField label="Nama Lengkap Orang Tua / Wali" :error="form.errors.parent_name" required>
                                <AppInput
                                    v-model="form.parent_name"
                                    placeholder="Contoh: Bambang Hermanto, S.T."
                                    required
                                />
                            </FormField>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <FormField label="No. WhatsApp Aktif (Penting)" help="Notifikasi nomor registrasi akan dikirim ke WhatsApp ini" :error="form.errors.parent_phone" required>
                                    <AppInput
                                        v-model="form.parent_phone"
                                        type="tel"
                                        placeholder="081234567890"
                                        required
                                    />
                                </FormField>
                                <FormField label="Email Orang Tua (Opsional)" :error="form.errors.parent_email">
                                    <AppInput
                                        v-model="form.parent_email"
                                        type="email"
                                        placeholder="ortu@gmail.com"
                                    />
                                </FormField>
                            </div>

                            <FormField label="Pekerjaan Orang Tua" :error="form.errors.parent_job">
                                <AppInput
                                    v-model="form.parent_job"
                                    placeholder="PNS / Swasta / Wiraswasta"
                                />
                            </FormField>

                            <div class="flex justify-between pt-4 border-t border-line">
                                <AppButton
                                    type="button"
                                    variant="secondary"
                                    @click="goToStep(1)"
                                >
                                    ← Kembali
                                </AppButton>
                                <AppButton
                                    type="button"
                                    variant="primary"
                                    @click="goToStep(3)"
                                >
                                    Lanjut ke Berkas Persyaratan →
                                </AppButton>
                            </div>
                        </div>

                        <!-- Step 3: Berkas Persyaratan -->
                        <div v-show="currentStep === 3" class="space-y-4">
                            <div class="border-b border-line pb-3">
                                <h2 class="text-base font-bold text-ink">Unggah Berkas Persyaratan</h2>
                                <p class="text-xs text-ink-muted">Format yang diterima: PDF, JPG, PNG (Maksimal 2 MB per berkas). Dokumen terproteksi disk privat.</p>
                            </div>

                            <FormField label="Scan Kartu Keluarga (KK)" help="Pastikan NIK dan nama calon siswa terbaca jelas">
                                <FileInput
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @change="(f) => onFileChange('kk_file', f)"
                                />
                            </FormField>

                            <FormField label="Scan Akta Kelahiran" help="Akta kelahiran resmi dari Dinas Dukcapil">
                                <FileInput
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @change="(f) => onFileChange('birth_certificate_file', f)"
                                />
                            </FormField>

                            <FormField label="Ijazah / Surat Keterangan TK (Opsional)" help="Dapat disusulkan jika belum diterbitkan oleh pihak TK">
                                <FileInput
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @change="(f) => onFileChange('ijazah_file', f)"
                                />
                            </FormField>

                            <div class="p-3 rounded-control bg-status-warning-bg text-status-warning-fg border border-status-warning-line text-xs leading-relaxed">
                                Dengan menekan tombol "Kirim Pendaftaran", saya menyatakan data yang diisi adalah benar dan sah. Panitia PPDB berhak memverifikasi keabsahan dokumen.
                            </div>

                            <div class="flex justify-between pt-4 border-t border-line">
                                <AppButton
                                    type="button"
                                    variant="secondary"
                                    @click="goToStep(2)"
                                >
                                    ← Kembali
                                </AppButton>
                                <AppButton
                                    type="submit"
                                    variant="primary"
                                    size="lg"
                                    :loading="form.processing"
                                >
                                    Kirim Pendaftaran Sekarang
                                </AppButton>
                            </div>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
