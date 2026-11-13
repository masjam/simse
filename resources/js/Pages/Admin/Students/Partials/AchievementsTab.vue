<script setup>
import { useForm } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    studentUser: { type: Object, required: true },
});

const toast = useToast();

const achForm = useForm({
    name: '',
    level: 'Sekolah',
    year: new Date().getFullYear(),
    description: '',
});

const levelOptions = [
    { value: 'Sekolah', label: 'Sekolah' },
    { value: 'Kecamatan', label: 'Kecamatan' },
    { value: 'Kabupaten/Kota', label: 'Kabupaten/Kota' },
    { value: 'Provinsi', label: 'Provinsi' },
    { value: 'Nasional', label: 'Nasional' },
    { value: 'Internasional', label: 'Internasional' },
];

const submitAchievement = () => {
    achForm.post(route('admin.students.achievements.store', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Prestasi berhasil ditambahkan!');
            achForm.reset();
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-sm font-bold text-ink mb-3">Daftar Prestasi Siswa</h3>

            <div v-if="!studentUser.student?.achievements || studentUser.student.achievements.length === 0">
                <EmptyState
                    title="Belum ada prestasi"
                    description="Siswa ini belum memiliki catatan prestasi lomba yang tercatat."
                />
            </div>
            <div v-else class="divide-y divide-line border border-line rounded-card overflow-hidden">
                <div
                    v-for="ach in studentUser.student.achievements"
                    :key="ach.id"
                    class="p-4 flex items-center justify-between gap-4 bg-surface"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-sm text-ink">{{ ach.name }}</h4>
                            <StatusBadge variant="brand" :text="ach.level" size="sm" />
                            <span class="text-xs font-numeric text-ink-muted">Tahun {{ ach.year }}</span>
                        </div>
                        <p class="text-xs text-ink-muted mt-1">{{ ach.description || '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambah Prestasi Form -->
        <div class="p-5 rounded-card bg-surface-raised border border-line space-y-4">
            <h4 class="font-bold text-sm text-ink">Tambah Prestasi Baru</h4>

            <form @submit.prevent="submitAchievement" class="space-y-4">
                <FormField label="Nama Prestasi / Lomba" :error="achForm.errors.name" required>
                    <AppInput
                        v-model="achForm.name"
                        placeholder="Contoh: Juara 1 Olimpiade Matematika"
                        required
                    />
                </FormField>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <FormField label="Tingkat" :error="achForm.errors.level" required>
                        <AppSelect
                            v-model="achForm.level"
                            :options="levelOptions"
                            required
                        />
                    </FormField>
                    <FormField label="Tahun" :error="achForm.errors.year" required>
                        <AppInput
                            v-model="achForm.year"
                            type="number"
                            class="font-numeric"
                            required
                        />
                    </FormField>
                </div>

                <FormField label="Keterangan Tambahan" :error="achForm.errors.description">
                    <AppTextarea
                        v-model="achForm.description"
                        :rows="2"
                        placeholder="Penyelenggara lomba atau info detail..."
                    />
                </FormField>

                <div class="flex justify-end pt-2">
                    <AppButton type="submit" variant="primary" :loading="achForm.processing">
                        Simpan Prestasi
                    </AppButton>
                </div>
            </form>
        </div>
    </div>
</template>

