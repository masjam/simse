<script setup>
import { useForm } from '@inertiajs/vue3';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    studentUser: { type: Object, required: true },
});

const toast = useToast();

const perForm = useForm({
    tinggi_badan: '',
    berat_badan: '',
    lingkar_kepala: '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const submitPeriodic = () => {
    perForm.post(route('admin.students.periodics.store', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Data periodik berhasil dicatat!');
            perForm.reset();
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-sm font-bold text-ink mb-3">Riwayat Data Periodik Fisik Siswa</h3>

            <div v-if="!studentUser.student?.periodics || studentUser.student.periodics.length === 0">
                <EmptyState
                    title="Belum ada data periodik"
                    description="Belum ada riwayat pengukuran fisik (tinggi/berat) untuk siswa ini."
                />
            </div>
            <div v-else class="divide-y divide-line border border-line rounded-card overflow-hidden">
                <div
                    v-for="per in studentUser.student.periodics"
                    :key="per.id"
                    class="p-4 flex items-center justify-between gap-4 bg-surface"
                >
                    <div>
                        <span class="text-xs font-numeric font-bold text-brand-700">
                            {{ per.recorded_at ? new Date(per.recorded_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                        </span>
                        <div class="flex items-center gap-4 text-xs text-ink mt-1">
                            <span>Tinggi: <strong>{{ per.tinggi_badan || '-' }} cm</strong></span>
                            <span>•</span>
                            <span>Berat: <strong>{{ per.berat_badan || '-' }} kg</strong></span>
                            <span>•</span>
                            <span>Lingkar Kepala: <strong>{{ per.lingkar_kepala || '-' }} cm</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambah Pencatatan Periodik -->
        <div class="p-5 rounded-card bg-surface-raised border border-line space-y-4">
            <h4 class="font-bold text-sm text-ink">Tambah Pengukuran Periodik Baru</h4>

            <form @submit.prevent="submitPeriodic" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <FormField label="Tanggal Pencatatan" :error="perForm.errors.recorded_at" required>
                        <AppInput
                            v-model="perForm.recorded_at"
                            type="date"
                            required
                        />
                    </FormField>
                    <FormField label="Tinggi Badan (cm)" :error="perForm.errors.tinggi_badan">
                        <AppInput
                            v-model="perForm.tinggi_badan"
                            type="number"
                            step="0.1"
                            class="font-numeric"
                            placeholder="Contoh: 125.5"
                        />
                    </FormField>
                    <FormField label="Berat Badan (kg)" :error="perForm.errors.berat_badan">
                        <AppInput
                            v-model="perForm.berat_badan"
                            type="number"
                            step="0.1"
                            class="font-numeric"
                            placeholder="Contoh: 26.0"
                        />
                    </FormField>
                    <FormField label="Lingkar Kepala (cm)" :error="perForm.errors.lingkar_kepala">
                        <AppInput
                            v-model="perForm.lingkar_kepala"
                            type="number"
                            step="0.1"
                            class="font-numeric"
                            placeholder="Contoh: 51.0"
                        />
                    </FormField>
                </div>

                <div class="flex justify-end pt-2">
                    <AppButton type="submit" variant="primary" :loading="perForm.processing">
                        Simpan Pengukuran
                    </AppButton>
                </div>
            </form>
        </div>
    </div>
</template>

