<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Modal from '@/Components/ui/Modal.vue';
import FormField from '@/Components/ui/FormField.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ category: 'all', search: '' }),
    },
    categories: {
        type: Array,
        default: () => ['Berita', 'Pengumuman', 'Prestasi', 'Artikel'],
    },
    stats: {
        type: Object,
        required: true,
    },
});

const confirm = useConfirm();
const toast = useToast();

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || 'all');

const applyFilter = () => {
    router.get(route('sistem.website'), {
        category: category.value,
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const showModal = ref(false);
const editingPost = ref(null);

const form = useForm({
    title: '',
    category: 'Berita',
    content: '',
    image: '',
    is_published: true,
});

const categoryOptions = props.categories.map((c) => ({ value: c, label: c }));

const openCreateModal = () => {
    editingPost.value = null;
    form.reset();
    form.clearErrors();
    form.category = 'Berita';
    form.is_published = true;
    showModal.value = true;
};

const openEditModal = (post) => {
    editingPost.value = post;
    form.clearErrors();
    form.title = post.title;
    form.category = post.category || 'Berita';
    form.content = post.content;
    form.image = post.image || '';
    form.is_published = Boolean(post.is_published);
    showModal.value = true;
};

const submitForm = () => {
    if (editingPost.value) {
        form.put(route('sistem.website.update', editingPost.value.id), {
            onSuccess: () => {
                toast.success('Artikel berhasil diperbarui.');
                showModal.value = false;
            },
        });
    } else {
        form.post(route('sistem.website.store'), {
            onSuccess: () => {
                toast.success('Artikel baru berhasil diterbitkan.');
                showModal.value = false;
            },
        });
    }
};

const togglePublish = (post) => {
    router.patch(route('sistem.website.toggle', post.id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Status publikasi berhasil diubah.'),
    });
};

const deletePost = async (post) => {
    const ok = await confirm({
        title: 'Hapus Artikel',
        message: `Apakah Anda yakin ingin menghapus artikel "${post.title}"? Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Hapus Artikel',
        variant: 'danger',
    });

    if (ok) {
        router.delete(route('sistem.website.destroy', post.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Artikel berhasil dihapus.'),
        });
    }
};
</script>

<template>
    <Head title="CMS Website & Artikel - Admin SIMSE" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="CMS Website & Berita Sekolah"
                subtitle="Kelola publikasi warta, pengumuman, dan artikel untuk landing page resmi"
            >
                <template #actions>
                    <AppButton variant="primary" size="sm" @click="openCreateModal">
                        + Tulis Artikel Baru
                    </AppButton>
                </template>
            </PageHeader>

            <!-- Stats KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <StatCard
                    title="Total Seluruh Konten"
                    :value="stats.total"
                    subtitle="Semua kategori"
                    icon-bg="brand"
                />
                <StatCard
                    title="Tayang (Published)"
                    :value="stats.published"
                    subtitle="Tampil di landing page"
                    icon-bg="success"
                />
                <StatCard
                    title="Draf (Belum Tayang)"
                    :value="stats.draft"
                    subtitle="Menunggu publikasi"
                    icon-bg="warning"
                />
            </div>

            <!-- Filters Bar -->
            <AppCard class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
                    <AppButton
                        size="sm"
                        :variant="category === 'all' ? 'primary' : 'ghost'"
                        @click="category = 'all'; applyFilter()"
                    >
                        Semua Kategori
                    </AppButton>
                    <AppButton
                        v-for="cat in categories"
                        :key="cat"
                        size="sm"
                        :variant="category === cat ? 'primary' : 'ghost'"
                        @click="category = cat; applyFilter()"
                    >
                        {{ cat }}
                    </AppButton>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-72">
                    <SearchInput
                        v-model="search"
                        placeholder="Cari judul artikel..."
                        @search="applyFilter"
                    />
                </div>
            </AppCard>

            <!-- Posts List -->
            <AppCard padding="none">
                <div v-if="!posts.data || posts.data.length === 0" class="p-8">
                    <EmptyState
                        title="Belum ada artikel"
                        description="Belum ada artikel atau berita yang ditambahkan untuk filter ini."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-surface-raised transition-colors"
                    >
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-ink truncate">{{ post.title }}</span>
                                <StatusBadge
                                    variant="brand"
                                    :text="post.category"
                                    size="sm"
                                />
                                <StatusBadge
                                    :variant="post.is_published ? 'success' : 'warning'"
                                    :text="post.is_published ? 'Tayang' : 'Draf'"
                                    size="sm"
                                />
                            </div>
                            <p class="text-xs text-ink-muted font-numeric">/{{ post.slug }} • Diperbarui {{ post.updated_at }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <AppButton
                                variant="ghost"
                                size="sm"
                                @click="togglePublish(post)"
                            >
                                {{ post.is_published ? 'Tarik Draf' : 'Tayangkan' }}
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                size="sm"
                                @click="openEditModal(post)"
                            >
                                Edit
                            </AppButton>
                            <AppButton
                                variant="danger"
                                size="sm"
                                @click="deletePost(post)"
                            >
                                Hapus
                            </AppButton>
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Create / Edit Modal -->
            <Modal
                :show="showModal"
                :title="editingPost ? 'Edit Artikel Website' : 'Tulis Artikel / Berita Baru'"
                @close="showModal = false"
            >
                <form @submit.prevent="submitForm" class="space-y-4">
                    <FormField label="Judul Artikel" :error="form.errors.title" required>
                        <AppInput
                            v-model="form.title"
                            placeholder="Contoh: Siswa SDAM Raih Juara 1 Olimpiade Sains"
                            required
                        />
                    </FormField>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormField label="Kategori" :error="form.errors.category" required>
                            <AppSelect
                                v-model="form.category"
                                :options="categoryOptions"
                                required
                            />
                        </FormField>

                        <div class="pt-6">
                            <AppCheckbox
                                v-model="form.is_published"
                                label="Tayangkan langsung ke publik"
                            />
                        </div>
                    </div>

                    <FormField label="URL Gambar Cover (Opsional)" :error="form.errors.image">
                        <AppInput
                            v-model="form.image"
                            type="url"
                            placeholder="https://images.unsplash.com/..."
                        />
                    </FormField>

                    <FormField label="Konten / Isi Artikel" :error="form.errors.content" required>
                        <AppTextarea
                            v-model="form.content"
                            :rows="6"
                            placeholder="Tulis narasi lengkap berita atau artikel..."
                            required
                        />
                    </FormField>

                    <div class="flex justify-end gap-2 pt-3 border-t border-line">
                        <AppButton variant="ghost" @click="showModal = false">
                            Batal
                        </AppButton>
                        <AppButton
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                        >
                            {{ editingPost ? 'Simpan Perubahan' : 'Terbitkan Artikel' }}
                        </AppButton>
                    </div>
                </form>
            </Modal>
        </div>
    </PortalShell>
</template>
