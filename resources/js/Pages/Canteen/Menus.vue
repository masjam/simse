<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import FormField from '@/Components/ui/FormField.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    stand: { type: Object, required: true },
    menus: { type: Array, required: true },
});

const { confirm } = useConfirm();
const toast = useToast();

const editingId = ref(null);
const form = useForm({
    name: '',
    price: 5000,
    stock: 20,
    description: '',
    photo: '',
    is_available: true,
});

const resetForm = () => {
    editingId.value = null;
    form.reset();
    form.price = 5000;
    form.stock = 20;
    form.is_available = true;
};

const editMenu = (menu) => {
    editingId.value = menu.id;
    form.name = menu.name;
    form.price = menu.price;
    form.stock = menu.stock;
    form.description = menu.description || '';
    form.photo = menu.photo || '';
    form.is_available = Boolean(menu.is_available);
};

const submit = () => {
    if (editingId.value) {
        form.put(route('kantin.menus.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                toast.success('Menu berhasil diperbarui!');
            },
        });
        return;
    }

    form.post(route('kantin.menus.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            toast.success('Menu baru berhasil ditambahkan!');
        },
    });
};

const deleteMenu = async (menuId) => {
    const ok = await confirm({
        title: 'Hapus Menu Kantin',
        message: 'Apakah Anda yakin ingin menghapus menu ini dari daftar stan?',
        confirmLabel: 'Ya, Hapus Menu',
        variant: 'danger',
    });

    if (ok) {
        router.delete(route('kantin.menus.destroy', menuId), {
            preserveScroll: true,
            onSuccess: () => toast.success('Menu berhasil dihapus.'),
        });
    }
};
</script>

<template>
    <Head title="Kelola Menu & Stok Kantin" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Kelola Menu & Stok Kantin"
                :subtitle="`Katalog menu dan ketersediaan makanan/minuman ${stand.name}`"
            />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Tambah / Edit Menu -->
                <AppCard class="h-fit">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-line">
                            <h3 class="font-bold text-ink text-sm">
                                {{ editingId ? 'Edit Menu' : 'Tambah Menu Baru' }}
                            </h3>
                            <AppButton
                                v-if="editingId"
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="resetForm"
                            >
                                Batal Edit
                            </AppButton>
                        </div>

                        <FormField label="Nama Menu" :error="form.errors.name" required>
                            <AppInput
                                v-model="form.name"
                                placeholder="Contoh: Nasi Soto Ayam / Jus Buah"
                                required
                            />
                        </FormField>

                        <div class="grid grid-cols-2 gap-3">
                            <FormField label="Harga (Rp)" :error="form.errors.price" required>
                                <AppInput
                                    v-model.number="form.price"
                                    type="number"
                                    min="500"
                                    step="500"
                                    class="font-numeric"
                                    required
                                />
                            </FormField>
                            <FormField label="Stok Harian" :error="form.errors.stock" required>
                                <AppInput
                                    v-model.number="form.stock"
                                    type="number"
                                    min="0"
                                    class="font-numeric"
                                    required
                                />
                            </FormField>
                        </div>

                        <FormField label="Deskripsi (Opsional)" :error="form.errors.description">
                            <AppTextarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Porsi anak SD, bebas MSG, higienis"
                            />
                        </FormField>

                        <div class="pt-1">
                            <AppCheckbox
                                v-model="form.is_available"
                                label="Tampilkan menu ke katalog siswa"
                            />
                        </div>

                        <div class="pt-2">
                            <AppButton
                                type="submit"
                                variant="primary"
                                class="w-full shadow-card"
                                :loading="form.processing"
                            >
                                {{ editingId ? 'Simpan Perubahan Menu' : '+ Tambah Menu' }}
                            </AppButton>
                        </div>
                    </form>
                </AppCard>

                <!-- Daftar Menu -->
                <div class="lg:col-span-2">
                    <AppCard>
                        <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                            <h3 class="font-bold text-ink text-sm">
                                Daftar Menu Stand ({{ menus.length }})
                            </h3>
                        </div>

                        <div v-if="menus.length === 0">
                            <EmptyState
                                title="Belum Ada Menu"
                                description="Belum ada menu di stan Anda. Tambahkan menu pertama melalui formulir di samping."
                            />
                        </div>

                        <div v-else class="divide-y divide-line">
                            <div
                                v-for="menu in menus"
                                :key="menu.id"
                                class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-ink text-sm">{{ menu.name }}</h4>
                                        <StatusBadge :variant="menu.is_available ? 'success' : 'neutral'" size="sm">
                                            {{ menu.is_available ? 'Tersedia' : 'Nonaktif' }}
                                        </StatusBadge>
                                    </div>
                                    <p class="text-xs text-ink-muted">{{ menu.description || 'Tanpa deskripsi' }}</p>
                                    <div class="flex items-center gap-4 text-xs font-bold pt-1">
                                        <span class="text-brand-700 font-numeric">
                                            <MoneyText :amount="menu.price" />
                                        </span>
                                        <span
                                            class="font-numeric"
                                            :class="menu.stock <= 5 ? 'text-status-danger-fg' : 'text-ink-muted'"
                                        >
                                            Stok: {{ menu.stock }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <AppButton
                                        variant="secondary"
                                        size="sm"
                                        @click="editMenu(menu)"
                                    >
                                        Edit
                                    </AppButton>
                                    <AppButton
                                        variant="ghost"
                                        size="sm"
                                        class="text-status-danger-fg"
                                        @click="deleteMenu(menu.id)"
                                    >
                                        Hapus
                                    </AppButton>
                                </div>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
