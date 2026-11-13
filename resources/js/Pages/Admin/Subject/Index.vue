<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    subjects: { type: Object, required: true },
});

const confirm = useConfirm();
const toast = useToast();

const isModalOpen = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    code: '',
    name: '',
});

const openModal = (subject = null) => {
    isModalOpen.value = true;
    if (subject) {
        editMode.value = true;
        currentId.value = subject.id;
        form.code = subject.code;
        form.name = subject.name;
    } else {
        editMode.value = false;
        currentId.value = null;
        form.reset();
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editMode.value) {
        form.put(route('admin.subjects.update', currentId.value), {
            onSuccess: () => {
                toast.success('Mata pelajaran berhasil diperbarui.');
                closeModal();
            },
        });
    } else {
        form.post(route('admin.subjects.store'), {
            onSuccess: () => {
                toast.success('Mata pelajaran baru berhasil ditambahkan.');
                closeModal();
            },
        });
    }
};

const deleteSubject = async (id) => {
    const ok = await confirm({
        title: 'Hapus Mata Pelajaran',
        message: 'Apakah Anda yakin ingin menghapus data mata pelajaran ini?',
        confirmText: 'Hapus Mapel',
        variant: 'danger',
    });

    if (ok) {
        form.delete(route('admin.subjects.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Mata pelajaran berhasil dihapus.'),
        });
    }
};

const columns = [
    { key: 'code', label: 'Kode Mapel' },
    { key: 'name', label: 'Nama Mata Pelajaran' },
];
</script>

<template>
    <Head title="Mata Pelajaran - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Mata Pelajaran"
                subtitle="Data master mata pelajaran Kurikulum Nasional & Kemuhammadiyahan SD"
            >
                <template #actions>
                    <AppButton variant="primary" size="sm" @click="openModal()">
                        + Tambah Mapel
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="subjects.data || []"
                :pagination="subjects"
                empty-title="Belum ada data mata pelajaran"
                empty-message="Belum ada mata pelajaran yang dibuat."
            >
                <template #cell(code)="{ item }">
                    <span class="font-numeric font-bold text-brand-700">
                        {{ item.code }}
                    </span>
                </template>

                <template #cell(name)="{ item }">
                    <span class="font-bold text-ink">
                        {{ item.name }}
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
                            @click="deleteSubject(item.id)"
                        >
                            Hapus
                        </AppButton>
                    </div>
                </template>
            </DataTable>

            <!-- Modal Form -->
            <Modal
                :show="isModalOpen"
                :title="editMode ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran Baru'"
                @close="closeModal"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Kode Mapel" help="Contoh: MAT, BIND, IPAS, ISMUBA" :error="form.errors.code" required>
                        <AppInput
                            v-model="form.code"
                            class="font-numeric uppercase"
                            placeholder="Contoh: MAT"
                            required
                        />
                    </FormField>

                    <FormField label="Nama Mata Pelajaran" :error="form.errors.name" required>
                        <AppInput
                            v-model="form.name"
                            placeholder="Contoh: Matematika"
                            required
                        />
                    </FormField>

                    <div class="pt-4 border-t border-line flex justify-end gap-2">
                        <AppButton variant="ghost" @click="closeModal">
                            Batal
                        </AppButton>
                        <AppButton type="submit" variant="primary" :loading="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Mapel' }}
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
