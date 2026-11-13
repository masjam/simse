<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    studentUser: { type: Object, required: true },
});

const toast = useToast();
const student = props.studentUser.student || {};

const form = useForm({
    alamat_jalan: student.alamat_jalan || '',
    rt: student.rt || '',
    rw: student.rw || '',
    dusun: student.dusun || '',
    desa_kelurahan: student.desa_kelurahan || '',
    kode_pos: student.kode_pos || '',
    tempat_tinggal: student.tempat_tinggal || 'Bersama orang tua',
    moda_transportasi: student.moda_transportasi || 'Sepeda motor',
    anak_ke: student.anak_ke || '',

    nama_ayah: student.nama_ayah || '',
    nik_ayah: student.nik_ayah || '',
    tahun_lahir_ayah: student.tahun_lahir_ayah || '',
    pendidikan_ayah: student.pendidikan_ayah || 'SMA / sederajat',
    pekerjaan_ayah: student.pekerjaan_ayah || '',
    penghasilan_ayah: student.penghasilan_ayah || '',

    status_hidup_ibu: student.status_hidup_ibu || 'Hidup',
    nama_ibu: student.nama_ibu || '',
    nik_ibu: student.nik_ibu || '',
    tahun_lahir_ibu: student.tahun_lahir_ibu || '',
    pendidikan_ibu: student.pendidikan_ibu || 'SMA / sederajat',
    pekerjaan_ibu: student.pekerjaan_ibu || '',
    penghasilan_ibu: student.penghasilan_ibu || '',

    mempunyai_wali: Boolean(student.mempunyai_wali),
    nama_wali: student.nama_wali || '',
    nik_wali: student.nik_wali || '',
    tahun_lahir_wali: student.tahun_lahir_wali || '',
    pendidikan_wali: student.pendidikan_wali || '',
    pekerjaan_wali: student.pekerjaan_wali || '',
    penghasilan_wali: student.penghasilan_wali || '',

    no_telepon_rumah: student.no_telepon_rumah || '',
    no_hp: student.no_hp || '',
});

const submitForm = () => {
    form.put(route('admin.students.update', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Data orang tua & alamat berhasil disimpan.'),
    });
};
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-8">
        <!-- Alamat -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-ink">Alamat Tempat Tinggal</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <FormField label="Alamat Jalan" :error="form.errors.alamat_jalan" required>
                        <AppInput v-model="form.alamat_jalan" required />
                    </FormField>
                </div>
                <FormField label="RT" :error="form.errors.rt">
                    <AppInput v-model="form.rt" class="font-numeric" />
                </FormField>
                <FormField label="RW" :error="form.errors.rw">
                    <AppInput v-model="form.rw" class="font-numeric" />
                </FormField>
                <FormField label="Dusun" :error="form.errors.dusun">
                    <AppInput v-model="form.dusun" />
                </FormField>
                <FormField label="Desa / Kelurahan" :error="form.errors.desa_kelurahan" required>
                    <AppInput v-model="form.desa_kelurahan" required />
                </FormField>
                <FormField label="Kode Pos" :error="form.errors.kode_pos">
                    <AppInput v-model="form.kode_pos" class="font-numeric" />
                </FormField>
                <FormField label="Tempat Tinggal" :error="form.errors.tempat_tinggal" required>
                    <AppInput v-model="form.tempat_tinggal" required />
                </FormField>
                <FormField label="Moda Transportasi" :error="form.errors.moda_transportasi" required>
                    <AppInput v-model="form.moda_transportasi" required />
                </FormField>
                <FormField label="Anak ke- (berdasarkan KK)" :error="form.errors.anak_ke" required>
                    <AppInput v-model="form.anak_ke" type="number" class="font-numeric" required />
                </FormField>
            </div>
        </div>

        <!-- Data Ayah -->
        <div class="space-y-4 pt-4 border-t border-line">
            <h3 class="text-sm font-bold text-ink">Data Ayah Kandung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField label="Nama Ayah" :error="form.errors.nama_ayah">
                    <AppInput v-model="form.nama_ayah" />
                </FormField>
                <FormField label="NIK Ayah" :error="form.errors.nik_ayah">
                    <AppInput v-model="form.nik_ayah" maxlength="16" class="font-numeric" />
                </FormField>
                <FormField label="Tahun Lahir Ayah" :error="form.errors.tahun_lahir_ayah">
                    <AppInput v-model="form.tahun_lahir_ayah" class="font-numeric" />
                </FormField>
                <FormField label="Pekerjaan Ayah" :error="form.errors.pekerjaan_ayah">
                    <AppInput v-model="form.pekerjaan_ayah" />
                </FormField>
            </div>
        </div>

        <!-- Data Ibu -->
        <div class="space-y-4 pt-4 border-t border-line">
            <h3 class="text-sm font-bold text-ink">Data Ibu Kandung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField label="Nama Ibu" :error="form.errors.nama_ibu" required>
                    <AppInput v-model="form.nama_ibu" required />
                </FormField>
                <FormField label="NIK Ibu" :error="form.errors.nik_ibu">
                    <AppInput v-model="form.nik_ibu" maxlength="16" class="font-numeric" />
                </FormField>
                <FormField label="Tahun Lahir Ibu" :error="form.errors.tahun_lahir_ibu">
                    <AppInput v-model="form.tahun_lahir_ibu" class="font-numeric" />
                </FormField>
                <FormField label="Pekerjaan Ibu" :error="form.errors.pekerjaan_ibu">
                    <AppInput v-model="form.pekerjaan_ibu" />
                </FormField>
            </div>
        </div>

        <!-- Kontak -->
        <div class="space-y-4 pt-4 border-t border-line">
            <h3 class="text-sm font-bold text-ink">Kontak Komunikasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField label="Nomor Telepon Rumah" :error="form.errors.no_telepon_rumah">
                    <AppInput v-model="form.no_telepon_rumah" class="font-numeric" />
                </FormField>
                <FormField label="Nomor WhatsApp / HP" :error="form.errors.no_hp">
                    <AppInput v-model="form.no_hp" class="font-numeric" />
                </FormField>
            </div>
        </div>

        <div class="pt-4 border-t border-line flex justify-end gap-3">
            <Link :href="route('admin.students.index')">
                <AppButton variant="ghost">Batal</AppButton>
            </Link>
            <AppButton type="submit" variant="primary" :loading="form.processing">
                Simpan Data Ortu & Alamat
            </AppButton>
        </div>
    </form>
</template>

