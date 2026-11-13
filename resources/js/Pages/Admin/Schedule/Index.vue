<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    schedules: { type: Object, required: true },
    student_classes: { type: Array, default: () => [] },
    current_class_id: { type: [String, Number], default: '' },
});

const confirm = useConfirm();
const toast = useToast();

const isModalOpen = ref(false);
const currentId = ref(null);
const selectedClass = ref(props.current_class_id || '');
const isGenerating = ref(false);

const form = useForm({
    day: '',
    time_start: '',
    time_end: '',
    room: '',
});

const days = [
    { value: 1, label: 'Senin' },
    { value: 2, label: 'Selasa' },
    { value: 3, label: 'Rabu' },
    { value: 4, label: 'Kamis' },
    { value: 5, label: 'Jumat' },
    { value: 6, label: 'Sabtu' },
];

const classOptions = [
    { value: '', label: 'Semua Kelas' },
    ...props.student_classes.map((c) => ({ value: c.id, label: c.name })),
];

const openModal = (schedule) => {
    isModalOpen.value = true;
    currentId.value = schedule.id;
    form.day = schedule.day || '';
    form.time_start = schedule.time_start ? schedule.time_start.substring(0, 5) : '';
    form.time_end = schedule.time_end ? schedule.time_end.substring(0, 5) : '';
    form.room = schedule.room || '';
};

const closeModal = () => {
    isModalOpen.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    form.put(route('admin.schedules.update', currentId.value), {
        onSuccess: () => {
            toast.success('Jadwal pelajaran berhasil diperbarui.');
            closeModal();
        },
    });
};

const autoGenerate = async () => {
    const ok = await confirm({
        title: 'Generate Jadwal Otomatis',
        message: 'Apakah Anda yakin ingin melakukan generate jadwal otomatis? Jadwal yang belum diatur akan diisi secara acak dengan menghindari bentrokan guru dan rombel.',
        confirmText: 'Generate Jadwal',
        variant: 'primary',
    });

    if (ok) {
        isGenerating.value = true;
        router.post(route('admin.schedules.generate'), {}, {
            preserveState: true,
            onSuccess: () => toast.success('Jadwal otomatis berhasil digenerate.'),
            onFinish: () => {
                isGenerating.value = false;
            },
        });
    }
};

watch(selectedClass, (val) => {
    router.get(route('admin.schedules.index'), { class_id: val }, { preserveState: true, replace: true });
});

const getDayName = (dayNumber) => {
    const day = days.find((d) => d.value == dayNumber);
    return day ? day.label : '-';
};

const columns = [
    { key: 'class', label: 'Kelas' },
    { key: 'subject', label: 'Mata Pelajaran' },
    { key: 'teacher', label: 'Guru Pengajar' },
    { key: 'day', label: 'Hari' },
    { key: 'time', label: 'Jam' },
    { key: 'room', label: 'Ruangan' },
];
</script>

<template>
    <Head title="Jadwal Pelajaran - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Jadwal Pelajaran"
                subtitle="Pengaturan jadwal hari dan jam untuk setiap penugasan mengajar guru"
            >
                <template #actions>
                    <AppButton
                        variant="accent"
                        size="sm"
                        :loading="isGenerating"
                        @click="autoGenerate"
                    >
                        ⚡ Generate Jadwal Otomatis
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Filter Bar -->
            <AppCard class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-ink">Filter Kelas:</span>
                    <div class="w-48">
                        <AppSelect
                            v-model="selectedClass"
                            :options="classOptions"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="schedules.data || []"
                :pagination="schedules"
                empty-title="Belum ada data jadwal"
                empty-message="Belum ada jadwal yang sesuai dengan filter atau penugasan mengajar."
            >
                <template #cell(class)="{ item }">
                    <span class="font-bold text-ink">
                        {{ item.student_class?.name || '-' }}
                    </span>
                </template>

                <template #cell(subject)="{ item }">
                    <span class="font-bold text-brand-700">
                        {{ item.subject?.name || '-' }}
                    </span>
                </template>

                <template #cell(teacher)="{ item }">
                    <span class="text-xs text-ink-muted">
                        {{ item.teacher?.user?.name || '-' }}
                    </span>
                </template>

                <template #cell(day)="{ item }">
                    <StatusBadge
                        v-if="item.day"
                        variant="brand"
                        :text="getDayName(item.day)"
                        size="sm"
                    />
                    <span v-else class="text-xs text-ink-muted italic">Belum diatur</span>
                </template>

                <template #cell(time)="{ item }">
                    <span v-if="item.time_start && item.time_end" class="text-xs font-numeric font-semibold text-ink">
                        {{ item.time_start.substring(0, 5) }} - {{ item.time_end.substring(0, 5) }}
                    </span>
                    <span v-else class="text-xs text-ink-muted italic">Belum diatur</span>
                </template>

                <template #cell(room)="{ item }">
                    <span class="text-xs text-ink-muted">
                        {{ item.room || '-' }}
                    </span>
                </template>

                <template #actions="{ item }">
                    <AppButton
                        variant="secondary"
                        size="sm"
                        @click="openModal(item)"
                    >
                        Atur Jadwal
                    </AppButton>
                </template>
            </DataTable>

            <!-- Modal Form -->
            <Modal
                :show="isModalOpen"
                title="Atur Waktu & Hari Jadwal"
                @close="closeModal"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Hari" :error="form.errors.day" required>
                        <AppSelect
                            v-model="form.day"
                            :options="days"
                            placeholder="Pilih Hari..."
                            required
                        />
                    </FormField>

                    <div class="grid grid-cols-2 gap-4">
                        <FormField label="Jam Mulai" :error="form.errors.time_start" required>
                            <AppInput
                                v-model="form.time_start"
                                type="time"
                                required
                            />
                        </FormField>
                        <FormField label="Jam Selesai" :error="form.errors.time_end" required>
                            <AppInput
                                v-model="form.time_end"
                                type="time"
                                required
                            />
                        </FormField>
                    </div>

                    <FormField label="Ruangan (Opsional)" :error="form.errors.room">
                        <AppInput
                            v-model="form.room"
                            placeholder="Contoh: Lab Komputer, Kelas 1A"
                        />
                    </FormField>

                    <div class="pt-4 border-t border-line flex justify-end gap-2">
                        <AppButton variant="ghost" @click="closeModal">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" :loading="form.processing">
                            Simpan Jadwal
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
