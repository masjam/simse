<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);
const toast = useToast();

const categoryOptions = [
    { value: 'Belajar', label: '1. Semangat Belajar & Kesulitan Pelajaran' },
    { value: 'Teman Sebaya', label: '2. Pertemanan di Kelas / Sekolah' },
    { value: 'Keluarga', label: '3. Suasana di Rumah / Keluarga' },
    { value: 'Kebiasaan', label: '4. Kebiasaan Diri & Minat Bakat' },
    { value: 'Lainnya', label: '5. Hal Lain yang Ingin Diceritakan' },
];

const form = useForm({
    category: 'Belajar',
    preferred_schedule: 'Waktu istirahat sekolah',
    notes: '',
});

const submit = () => {
    form.post(route('siswa.konseling.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pesanmu sudah terkirim ke Guru BK.');
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Bicara Santai dengan Guru BK" max-width="md" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="p-3 rounded-control bg-brand-50 border border-brand-200 text-xs text-brand-900 space-y-1">
                <p class="font-bold flex items-center gap-1.5">
                    <span>🌱</span>
                    <span>Ruang Curhat Ramah Anak</span>
                </p>
                <p class="text-[11px] text-ink-muted leading-relaxed">
                    Guru BK adalah sahabat anak-anak di sekolah. Jangan ragu menceritakan apa pun yang membuatmu gundah, bingung, atau butuh teman mengobrol.
                </p>
            </div>

            <FormField label="Hal Apa yang Ingin Kamu Ceritakan?" :error="form.errors.category" required>
                <AppSelect
                    v-model="form.category"
                    :options="categoryOptions"
                />
            </FormField>

            <FormField label="Waktu yang Kamu Harapkan (Opsional)" :error="form.errors.preferred_schedule">
                <AppInput
                    v-model="form.preferred_schedule"
                    placeholder="Contoh: Saat jam istirahat pertama / setelah sholat dzuhur"
                />
            </FormField>

            <FormField label="Tuliskan Sedikit Cerita atau Pesanmu" :error="form.errors.notes" required>
                <AppTextarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Halo Ustadz/Ustadzah, saya ingin ngobrol tentang..."
                    required
                />
            </FormField>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Kirim ke Guru BK
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

