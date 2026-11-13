<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
    form.is_published = post.is_published;
    showModal.value = true;
};

const submitForm = () => {
    if (editingPost.value) {
        form.put(route('sistem.website.update', editingPost.value.id), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post(route('sistem.website.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const togglePublish = (post) => {
    router.patch(route('sistem.website.toggle', post.id), {}, {
        preserveScroll: true,
    });
};

const deletePost = (post) => {
    if (confirm(`Hapus artikel "${post.title}"?`)) {
        router.delete(route('sistem.website.destroy', post.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="CMS Website & Artikel - Admin SIMSE" />

    <AdminLayout>
        <template #header-title>CMS Website & Berita Sekolah</template>
        <template #header-subtitle>Kelola publikasi warta, pengumuman, dan artikel untuk landing page resmi</template>

        <div class="space-y-6">
            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                    <p class="text-xs text-slate-400 font-medium">Total Seluruh Konten</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.total }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                    <p class="text-xs text-emerald-400 font-medium">Tayang di Website (Published)</p>
                    <p class="text-2xl font-bold text-emerald-300 mt-1">{{ stats.published }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                    <p class="text-xs text-amber-400 font-medium">Draf (Belum Tayang)</p>
                    <p class="text-2xl font-bold text-amber-300 mt-1">{{ stats.draft }}</p>
                </div>
            </div>

            <!-- ACTIONS & FILTERS -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <button 
                        @click="category = 'all'; applyFilter()"
                        type="button"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="category === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        Semua Kategori
                    </button>
                    <button 
                        v-for="cat in categories" 
                        :key="cat"
                        @click="category = cat; applyFilter()"
                        type="button"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="category === cat ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        {{ cat }}
                    </button>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="flex items-center gap-2">
                        <input 
                            v-model="search"
                            @keyup.enter="applyFilter"
                            type="text" 
                            placeholder="Cari judul artikel..."
                            class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                        <button 
                            @click="applyFilter" 
                            type="button"
                            class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>

                    <button 
                        @click="openCreateModal"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Tulis Artikel</span>
                    </button>
                </div>
            </div>

            <!-- POSTS TABLE -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3.5">Judul Artikel</th>
                                <th class="px-4 py-3.5">Kategori</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5">Terakhir Update</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-bold text-white text-sm line-clamp-1">{{ post.title }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">/{{ post.slug }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                        :class="{
                                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': post.category === 'Berita',
                                            'bg-teal-500/10 text-teal-400 border-teal-500/20': post.category === 'Pengumuman',
                                            'bg-amber-500/10 text-amber-400 border-amber-500/20': post.category === 'Prestasi',
                                            'bg-purple-500/10 text-purple-400 border-purple-500/20': post.category === 'Artikel',
                                        }"
                                    >
                                        {{ post.category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                        @click="togglePublish(post)"
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all"
                                        :class="post.is_published ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-red-500/10 hover:text-red-400 hover:border-red-500/30' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-emerald-500/10 hover:text-emerald-400'"
                                        title="Klik untuk ubah status"
                                    >
                                        {{ post.is_published ? '✓ Tayang' : 'Draft' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[11px]">
                                    {{ post.updated_at }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="openEditModal(post)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition-colors"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            @click="deletePost(post)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs transition-colors"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="posts.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada artikel yang ditambahkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CREATE / EDIT MODAL -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">
                        {{ editingPost ? 'Edit Artikel Website' : 'Tulis Artikel / Berita Baru' }}
                    </h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Artikel *</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            required 
                            placeholder="Contoh: Siswa SDAM Raih Juara 1 Olimpiade Sains Nasional"
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.title" class="text-xs text-red-400 mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori *</label>
                            <select 
                                v-model="form.category" 
                                required 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Status Publikasi</label>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input 
                                    v-model="form.is_published" 
                                    type="checkbox" 
                                    class="w-4 h-4 rounded text-emerald-600 bg-slate-950 border-slate-800 focus:ring-emerald-500"
                                />
                                <span class="text-xs text-slate-300">Tayangkan langsung ke publik</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">URL Gambar Cover (Opsional)</label>
                        <input 
                            v-model="form.image" 
                            type="url" 
                            placeholder="https://images.unsplash.com/..."
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Konten / Isi Artikel *</label>
                        <textarea 
                            v-model="form.content" 
                            rows="6" 
                            required 
                            placeholder="Tulis narasi lengkap berita, prestasi, atau pengumuman sekolah..."
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                        <p v-if="form.errors.content" class="text-xs text-red-400 mt-1">{{ form.errors.content }}</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button 
                            @click="showModal = false" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-medium hover:bg-slate-700"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : (editingPost ? 'Simpan Perubahan' : 'Terbitkan Artikel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

