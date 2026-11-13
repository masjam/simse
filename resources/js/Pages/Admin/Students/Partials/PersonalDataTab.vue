<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    studentUser: { type: Object, required: true },
    studentClasses: { type: Array, default: () => [] },
});

const toast = useToast();
const student = props.studentUser.student || {};

const form = useForm({
    name: props.studentUser.name || '',
    student_class_id: student.student_class_id || '',
    gender: student.gender || 'L',
    nisn: student.nisn || '',
    kewarganegaraan: student.kewarganegaraan || 'ID',
    nik: student.nik || '',
    no_kk: student.no_kk || '',
    tempat_lahir: student.tempat_lahir || '',
    tanggal_lahir: student.tanggal_lahir || '',
    no_registrasi_akta_lahir: student.no_registrasi_akta_lahir || '',
    agama: student.agama || 'Islam',
});

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const agamaOptions = [
    { value: 'Islam', label: 'Islam' },
    { value: 'Kristen', label: 'Kristen' },
    { value: 'Katolik', label: 'Katolik' },
    { value: 'Hindu', label: 'Hindu' },
    { value: 'Buddha', label: 'Buddha' },
    { value: 'Konghucu', label: 'Konghucu' },
];

const classOptions = props.studentClasses.map((c) => ({ value: c.id, label: c.name }));

const submitForm = () => {
    form.put(route('admin.students.update', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Data pribadi siswa berhasil disimpan.'),
    });
};
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormField label="Nama Lengkap Siswa" :error="form.errors.name" required>
                <AppInput v-model="form.name" required />
            </FormField>

            <FormField label="Kelas / Rombel" :error="form.errors.student_class_id" required>
                <AppSelect
                    v-model="form.student_class_id"
                    :options="classOptions"
                    placeholder="Pilih Kelas..."
                    required
                />
            </FormField>

            <FormField label="Jenis Kelamin" :error="form.errors.gender" required>
                <AppSelect
                    v-model="form.gender"
                    :options="genderOptions"
                    required
                />
            </FormField>

            <FormField label="NISN" :error="form.errors.nisn">
                <AppInput v-model="form.nisn" class="font-numeric" />
            </FormField>

            <FormField label="Kewarganegaraan" :error="form.errors.kewarganegaraan" required>
                <AppInput v-model="form.kewarganegaraan" />
            </FormField>

            <FormField label="NIK (16 Digit)" :error="form.errors.nik">
                <AppInput v-model="form.nik" maxlength="16" class="font-numeric" />
            </FormField>

            <FormField label="No. Kartu Keluarga (KK)" :error="form.errors.no_kk">
                <AppInput v-model="form.no_kk" maxlength="16" class="font-numeric" />
            </FormField>

            <FormField label="Tempat Lahir" :error="form.errors.tempat_lahir">
                <AppInput v-model="form.tempat_lahir" />
            </FormField>

            <FormField label="Tanggal Lahir" :error="form.errors.tanggal_lahir">
                <AppInput v-model="form.tanggal_lahir" type="date" />
            </FormField>

            <FormField label="No. Registrasi Akta Lahir" :error="form.errors.no_registrasi_akta_lahir">
                <AppInput v-model="form.no_registrasi_akta_lahir" />
            </FormField>

            <FormField label="Agama & Kepercayaan" :error="form.errors.agama" required>
                <AppSelect
                    v-model="form.agama"
                    :options="agamaOptions"
                    required
                />
            </FormField>
        </div>

        <div class="pt-4 border-t border-line flex justify-end gap-3">
            <Link :href="route('admin.students.index')">
                <AppButton variant="ghost">Batal</AppButton>
            </Link>
            <AppButton type="submit" variant="primary" :loading="form.processing">
                Simpan Data Pribadi
            </AppButton>
        </div>
    </form>
</template>

