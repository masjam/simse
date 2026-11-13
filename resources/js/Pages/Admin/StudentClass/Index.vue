<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    rombels: { type: Object, required: true },
    tingkats: { type: Array, default: () => [] },
    academic_years: { type: Array, default: () => [] },
    teachers: { type: Array, default: () => [] },
});

const confirm = useConfirm();
const toast = useToast();

const isModalOpen = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
    level: '',
    tingkat_id: '',
    academic_year_id: '',
    teacher_id: '',
});

const tingkatOptions = props.tingkats.map((t) => ({ value: t.id, label: t.name }));
const academicYearOptions = props.academic_years.map((a) => ({ value: a.id, label: `${a.name} (${a.semester})` }));
const teacherOptions = [
    { value: '', label: 'Belum Ditentukan' },
    ...props.teachers.map((t) => ({ value: t.id, label: t.user?.name || 'Guru' })),
];

const openModal = (rombel = null) => {
    isModalOpen.value = true;
    if (rombel) {
        editMode.value = true;
        currentId.value = rombel.id;
        form.name = rombel.name;
        form.level = rombel.level;
        form.tingkat_id = rombel.tingkat_id;
        form.academic_year_id = rombel.academic_year_id;
        form.teacher_id = rombel.teacher_id || '';
    } else {
        editMode.value = false;
        currentId.value = null;
        form.reset();
        form.teacher_id = '';
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editMode.value) {
        form.put(route('admin.student-classes.update', currentId.value), {
            onSuccess: () => {
                toast.success('Data rombel berhasil diperbarui.');
                closeModal();
            },
        });
    } else {
        form.post(route('admin.student-classes.store'), {
            onSuccess: () => {
                toast.success('Rombel baru berhasil ditambahkan.');
                closeModal();
            },
        });
    }
};

const deleteRombel = async (id) => {
    const ok = await confirm({
        title: 'Hapus Rombongan Belajar',
        message: 'Apakah Anda yakin ingin menghapus data rombel ini? Pastikan tidak ada siswa yang terikat pada rombel ini.',
        confirmText: 'Hapus Rombel',
        variant: 'danger',
    });

    if (ok) {
        form.delete(route('admin.student-classes.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Rombel berhasil dihapus.'),
        });
    }
};

const columns = [
    { key: 'tingkat', label: 'Tingkat' },
    { key: 'name', label: 'Nama Rombel' },
    { key: 'teacher', label: 'Wali Kelas' },
];
</script>

<template>
    <Head title="Manajemen Rombel - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Manajemen Rombongan Belajar (Rombel)"
                subtitle="Kelola pembagian kelas dan penetapan wali kelas untuk jenjang SD"
            >
                <template #actions>
                    <AppButton variant="primary" size="sm" @click="openModal()">
                        + Tambah Rombel
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="rombels.data || []"
                :pagination="rombels"
                empty-title="Belum ada data rombel"
                empty-message="Belum ada rombongan belajar yang dibuat."
            >
                <template #cell(tingkat)="{ item }">
                    <span class="font-bold text-ink">
                        {{ item.tingkat?.name || item.level }}
                    </span>
                </template>

                <template #cell(name)="{ item }">
                    <span class="font-bold text-brand-700">
                        {{ item.name }}
                    </span>
                </template>

                <template #cell(teacher)="{ item }">
                    <span class="text-xs text-ink-muted">
                        {{ item.teacher?.user?.name || '-' }}
                    </span>
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            @click="openModal(item)"
                        >
                            Edit
                        </AppButton>
                        <AppButton
                            variant="danger"
                            size="sm"
                            @click="deleteRombel(item.id)"
                        >
                            Hapus
                        </AppButton>
                    </div>
                </template>
            </DataTable>

            <!-- Modal Form -->
            <Modal
                :show="isModalOpen"
                :title="editMode ? 'Edit Rombel' : 'Tambah Rombel Baru'"
                @close="closeModal"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Tingkat (Master Data)" :error="form.errors.tingkat_id" required>
                        <AppSelect
                            v-model="form.tingkat_id"
                            :options="tingkatOptions"
                            placeholder="Pilih Tingkat..."
                            required
                        />
                    </FormField>

                    <FormField label="Level Kelas (Teks)" help="Contoh: 1, 2, 3, 4, 5, 6" :error="form.errors.level" required>
                        <AppInput
                            v-model="form.level"
                            placeholder="Contoh: 1"
                            required
                        />
                    </FormField>

                    <FormField label="Tahun Ajaran" :error="form.errors.academic_year_id" required>
                        <AppSelect
                            v-model="form.academic_year_id"
                            :options="academicYearOptions"
                            placeholder="Pilih Tahun Ajaran..."
                            required
                        />
                    </FormField>

                    <FormField label="Nama Rombel" help="Contoh: 1A, 2B, 3 Khadijah" :error="form.errors.name" required>
                        <AppInput
                            v-model="form.name"
                            placeholder="Contoh: 1A"
                            required
                        />
                    </FormField>

                    <FormField label="Wali Kelas (Opsional)" :error="form.errors.teacher_id">
                        <AppSelect
                            v-model="form.teacher_id"
                            :options="teacherOptions"
                            placeholder="Pilih Wali Kelas..."
                        />
                    </FormField>

                    <div class="pt-4 border-t border-line flex justify-end gap-2">
                        <AppButton variant="ghost" @click="closeModal">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" :loading="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
