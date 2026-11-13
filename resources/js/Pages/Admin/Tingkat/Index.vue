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
    tingkats: { type: Object, required: true },
});

const confirm = useConfirm();
const toast = useToast();

const isModalOpen = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
});

const openModal = (tingkat = null) => {
    isModalOpen.value = true;
    if (tingkat) {
        editMode.value = true;
        currentId.value = tingkat.id;
        form.name = tingkat.name;
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
        form.put(route('admin.tingkat.update', currentId.value), {
            onSuccess: () => {
                toast.success('Data tingkat berhasil diperbarui.');
                closeModal();
            },
        });
    } else {
        form.post(route('admin.tingkat.store'), {
            onSuccess: () => {
                toast.success('Tingkat baru berhasil ditambahkan.');
                closeModal();
            },
        });
    }
};

const deleteTingkat = async (id) => {
    const ok = await confirm({
        title: 'Hapus Tingkat',
        message: 'Apakah Anda yakin ingin menghapus data tingkat ini?',
        confirmText: 'Hapus Tingkat',
        variant: 'danger',
    });

    if (ok) {
        form.delete(route('admin.tingkat.destroy', id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Tingkat berhasil dihapus.'),
        });
    }
};

const columns = [
    { key: 'name', label: 'Nama Tingkat / Jenjang' },
];
</script>

<template>
    <Head title="Manajemen Tingkat - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Manajemen Tingkat (Kelas 1 - 6)"
                subtitle="Kelola data tingkat jenjang pendidikan sekolah dasar"
            >
                <template #actions>
                    <AppButton variant="primary" size="sm" @click="openModal()">
                        + Tambah Tingkat
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :items="tingkats.data || []"
                :pagination="tingkats"
                empty-title="Belum ada data tingkat"
                empty-message="Belum ada jenjang tingkat kelas yang dibuat."
            >
                <template #cell(name)="{ item }">
                    <span class="font-bold text-ink text-sm">
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
                            @click="deleteTingkat(item.id)"
                        >
                            Hapus
                        </AppButton>
                    </div>
                </template>
            </DataTable>

            <!-- Modal Form -->
            <Modal
                :show="isModalOpen"
                :title="editMode ? 'Edit Tingkat' : 'Tambah Tingkat Baru'"
                @close="closeModal"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Nama Tingkat" help="Contoh: Kelas 1, Kelas 2, Tingkat 1" :error="form.errors.name" required>
                        <AppInput
                            v-model="form.name"
                            placeholder="Contoh: Kelas 1"
                            required
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
