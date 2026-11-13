<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import FileInput from '@/Components/ui/FileInput.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    activeChild: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    student_id: props.activeChild?.id || '',
    name: '',
    category: 'Akademik',
    level: 'Kabupaten/Kota',
    year: new Date().getFullYear(),
    organizer: '',
    achievement_date: '',
    description: '',
    certificate: null,
});

const handleFile = (event) => {
    form.certificate = event.target.files?.[0] || null;
};

const submit = () => {
    form.student_id = props.activeChild.id;
    form.post(route('ortu.achievements.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('name', 'organizer', 'achievement_date', 'description', 'certificate');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Ajukan Prestasi / Kejuaraan Siswa" max-width="lg" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-3">
            <FormField label="Nama Prestasi / Juara" :error="form.errors.name" required>
                <AppInput
                    v-model="form.name"
                    placeholder="Contoh: Juara 1 Olimpiade Matematika SD"
                    required
                />
            </FormField>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <FormField label="Bidang Kategori" :error="form.errors.category" required>
                    <AppSelect
                        v-model="form.category"
                        :options="[
                            { value: 'Akademik', label: 'Akademik' },
                            { value: 'Keagamaan / Tahfidz', label: 'Keagamaan / Tahfidz' },
                            { value: 'Olahraga', label: 'Olahraga' },
                            { value: 'Seni & Budaya', label: 'Seni & Budaya' },
                            { value: 'Lainnya', label: 'Lainnya' },
                        ]"
                    />
                </FormField>

                <FormField label="Tingkat Lomba" :error="form.errors.level" required>
                    <AppSelect
                        v-model="form.level"
                        :options="[
                            { value: 'Sekolah', label: 'Sekolah' },
                            { value: 'Kecamatan / Kapanewon', label: 'Kecamatan / Kapanewon' },
                            { value: 'Kabupaten/Kota', label: 'Kabupaten / Kota' },
                            { value: 'Provinsi', label: 'Provinsi' },
                            { value: 'Nasional', label: 'Nasional' },
                            { value: 'Internasional', label: 'Internasional' },
                        ]"
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <FormField label="Penyelenggara Lomba" :error="form.errors.organizer" required>
                    <AppInput
                        v-model="form.organizer"
                        placeholder="Contoh: Dinas Pendidikan / PDM"
                        required
                    />
                </FormField>

                <FormField label="Tanggal Perolehan" :error="form.errors.achievement_date" required>
                    <AppInput
                        v-model="form.achievement_date"
                        type="date"
                        required
                    />
                </FormField>
            </div>

            <FormField label="Deskripsi Singkat" :error="form.errors.description">
                <AppTextarea
                    v-model="form.description"
                    rows="2"
                    placeholder="Catatan tambahan mengenai prestasi ananda..."
                />
            </FormField>

            <FormField label="Unggah Piagam / Sertifikat (PDF/JPG/PNG, Max 2MB)" :error="form.errors.certificate">
                <FileInput
                    accept=".pdf,.jpg,.jpeg,.png"
                    @change="form.certificate = $event"
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" :loading="form.processing">
                    Kirim Pengajuan
                </AppButton>
            </div>
        </form>
    </Modal>
</template>
