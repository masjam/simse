<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    student: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    pin: '',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
    }
});

const submit = () => {
    if (!props.student) return;

    form.post(route('bendahara.dompet.pin', props.student.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal
        :show="show"
        title="Atur PIN Transaksi Dompet (6 Digit)"
        max-width="sm"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <p v-if="student" class="text-xs text-ink-muted">
                Siswa: <strong class="text-ink">{{ student.name }}</strong>
            </p>

            <FormField
                label="PIN Baru (6 Angka)"
                help="Gunakan 6 digit angka rahasia untuk otorisasi belanja kantin."
                :error="form.errors.pin"
                required
            >
                <AppInput
                    v-model="form.pin"
                    type="password"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="123456"
                    class="tracking-widest text-center font-mono"
                    required
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan PIN
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

