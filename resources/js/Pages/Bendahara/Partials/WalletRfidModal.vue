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
    rfid_uid: '',
});

watch(() => props.show, (newVal) => {
    if (newVal && props.student) {
        form.rfid_uid = props.student.rfid_uid || '';
        form.clearErrors();
    }
});

const submit = () => {
    if (!props.student) return;

    form.post(route('bendahara.dompet.rfid', props.student.id), {
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
        title="Tautkan Kartu RFID Siswa"
        max-width="sm"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="p-6 space-y-4">
            <p v-if="student" class="text-xs text-ink-muted">
                Siswa: <strong class="text-ink">{{ student.name }}</strong> (NISN: {{ student.nisn }})
            </p>

            <FormField
                label="UID Kartu RFID"
                help="Tempelkan kartu pada scanner atau ketikkan UID kartu. Kosongkan untuk melepas kartu."
                :error="form.errors.rfid_uid"
            >
                <AppInput
                    v-model="form.rfid_uid"
                    placeholder="Contoh: A1B2C3D4"
                    class="font-mono text-center uppercase"
                />
            </FormField>

            <div class="flex justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Kartu RFID
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

