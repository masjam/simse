<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    assignments: { type: Object, required: true },
    teachers: { type: Array, default: () => [] },
    student_classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
});

const confirm = useConfirm();
const toast = useToast();

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref(null);

const form = useForm({
    teacher_id: '',
    subject_id: '',
    student_class_id: '',
    student_class_ids: [],
    jpl: '',
});

const teacherOptions = props.teachers.map((t) => ({ value: t.id, label: t.user?.name || 'Guru' }));
const subjectOptions = props.subjects.map((s) => ({ value: s.id, label: s.name }));
const classOptions = props.student_classes.map((c) => ({ value: c.id, label: c.name }));

const toggleClass = (id) => {
    const index = form.student_class_ids.indexOf(id);
    if (index === -1) {
        form.student_class_ids.push(id);
    } else {
        form.student_class_ids.splice(index, 1);
    }
};

const openModal = () => {
    isEditMode.value = false;
    isModalOpen.value = true;
    form.reset();
    form.student_class_ids = [];
};

const openEditModal = (assignment) => {
    isEditMode.value = true;
    isModalOpen.value = true;
    editingId.value = assignment.id;
    form.teacher_id = assignment.teacher_id;
    form.subject_id = assignment.subject_id;
    form.student_class_id = assignment.student_class_id;
    form.jpl = assignment.jpl;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('admin.teaching-assignments.update', editingId.value), {
            onSuccess: () => {
                toast.success('Tugas mengajar berhasil diperbarui.');
                closeModal();
            },
        });
    } else {
        form.post(route('admin.teaching-assignments.store'), {
            onSuccess: () => {
                toast.success('Tugas mengajar baru berhasil ditambahkan.');
                closeModal();
            },
        });
    }
};

const deleteAssignment = async (id) => {
    const ok = await confirm({
        title: 'Hapus Tugas Mengajar',
        message: 'Apakah Anda yakin ingin menghapus data tugas mengajar ini?',
        confirmText: 'Hapus Tugas',
        variant: 'danger',
    });

    if (ok) {
        form.delete(route('admin.teaching-assignments.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Tugas mengajar berhasil dihapus.'),
        });
    }
};

const columns = [
    { key: 'teacher', label: 'Guru Pengajar' },
    { key: 'subject', label: 'Mata Pelajaran' },
    { key: 'class', label: 'Kelas / Rombel' },
    { key: 'jpl', label: 'JPL / Minggu' },
];
</script>

<template>
    <Head title="Pembagian Tugas Mengajar - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Pembagian Tugas Mengajar"
                subtitle="Plotting guru pengajar untuk mata pelajaran, rombongan belajar, dan beban JPL"
            >
                <template #actions>
                    <AppButton variant="primary" size="sm" @click="openModal()">
                        + Tambah Penugasan
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="assignments.data || []"
                :pagination="assignments"
                empty-title="Belum ada penugasan mengajar"
                empty-message="Belum ada data tugas mengajar guru yang ditambahkan."
            >
                <template #cell(teacher)="{ item }">
                    <span class="font-bold text-ink">
                        {{ item.teacher?.user?.name || '-' }}
                    </span>
                </template>

                <template #cell(subject)="{ item }">
                    <span class="font-bold text-brand-700">
                        {{ item.subject?.name || '-' }}
                    </span>
                </template>

                <template #cell(class)="{ item }">
                    <span class="px-2.5 py-0.5 rounded-control bg-surface-raised border border-line text-xs font-semibold text-ink">
                        {{ item.student_class?.name || '-' }}
                    </span>
                </template>

                <template #cell(jpl)="{ item }">
                    <span class="font-numeric text-xs font-bold text-ink">
                        {{ item.jpl || '-' }} Jam / Minggu
                    </span>
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            @click="openEditModal(item)"
                        >
                            Edit
                        </AppButton>
                        <AppButton
                            variant="danger"
                            size="sm"
                            @click="deleteAssignment(item.id)"
                        >
                            Hapus
                        </AppButton>
                    </div>
                </template>
            </DataTable>

            <!-- Modal Form -->
            <Modal
                :show="isModalOpen"
                :title="isEditMode ? 'Edit Tugas Mengajar' : 'Tambah Tugas Mengajar'"
                @close="closeModal"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Guru Pengajar" :error="form.errors.teacher_id" required>
                        <AppSelect
                            v-model="form.teacher_id"
                            :options="teacherOptions"
                            placeholder="Pilih Guru..."
                            required
                        />
                    </FormField>

                    <FormField label="Mata Pelajaran" :error="form.errors.subject_id" required>
                        <AppSelect
                            v-model="form.subject_id"
                            :options="subjectOptions"
                            placeholder="Pilih Mata Pelajaran..."
                            required
                        />
                    </FormField>

                    <!-- Multiple Class Select when Create -->
                    <div v-if="!isEditMode" class="space-y-2">
                        <label class="block text-xs font-bold text-ink">
                            Rombongan Belajar (Pilih satu atau lebih) <span class="text-status-danger-fg">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-1 border border-line rounded-control bg-surface-raised">
                            <div
                                v-for="c in student_classes"
                                :key="c.id"
                                class="p-2 rounded-control border border-line bg-surface hover:bg-surface-raised cursor-pointer transition"
                                @click="toggleClass(c.id)"
                            >
                                <AppCheckbox
                                    :model-value="form.student_class_ids.includes(c.id)"
                                    :label="c.name"
                                    @change="() => toggleClass(c.id)"
                                />
                            </div>
                        </div>
                        <p v-if="form.errors.student_class_ids" class="text-xs text-status-danger-fg">
                            {{ form.errors.student_class_ids }}
                        </p>
                    </div>

                    <!-- Single Class Select when Edit -->
                    <FormField v-else label="Rombongan Belajar (Kelas)" :error="form.errors.student_class_id" required>
                        <AppSelect
                            v-model="form.student_class_id"
                            :options="classOptions"
                            placeholder="Pilih Kelas..."
                            required
                        />
                    </FormField>

                    <FormField label="Jumlah Jam Pelajaran (JPL) per Minggu" help="Misal: 4 jam" :error="form.errors.jpl" required>
                        <AppInput
                            v-model="form.jpl"
                            type="number"
                            min="1"
                            max="40"
                            placeholder="Misal: 4"
                            class="font-numeric"
                            required
                        />
                    </FormField>

                    <div class="pt-4 border-t border-line flex justify-end gap-2">
                        <AppButton variant="ghost" @click="closeModal">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" :loading="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Tugas' }}
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
