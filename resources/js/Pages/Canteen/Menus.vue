<script setup>
import CanteenLayout from '@/Layouts/CanteenLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stand: { type: Object, required: true },
    menus: { type: Array, required: true },
});

const editingId = ref(null);
const form = useForm({
    name: '',
    price: 5000,
    stock: 20,
    description: '',
    photo: '',
    is_available: true,
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
}).format(amount || 0);

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
    form.is_available = menu.is_available;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('kantin.menus.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        });
        return;
    }

    form.post(route('kantin.menus.store'), {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
};

const deleteMenu = (menuId) => {
    if (!confirm('Hapus menu ini dari daftar stand?')) {
        return;
    }

    router.delete(route('kantin.menus.destroy', menuId), { preserveScroll: true });
};
</script>

<template>
    <Head title="Kelola Menu & Stok Kantin" />

    <CanteenLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Kelola Menu & Stok</h1>
                <p class="text-xs text-slate-500">{{ stand.name }}</p>
            </div>
        </template>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form Tambah / Edit Menu -->
            <form class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 shadow-sm h-fit" @submit.prevent="submit">
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-slate-800">
                        {{ editingId ? 'Edit Menu' : 'Tambah Menu Baru' }}
                    </h2>
                    <button v-if="editingId" type="button" class="text-xs font-bold text-slate-500 hover:underline" @click="resetForm">
                        Batal Edit
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Nama Menu</label>
                    <input v-model="form.name" type="text" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Contoh: Nasi Soto Ayam" required />
                    <p v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Harga (Rp)</label>
                        <input v-model.number="form.price" type="number" min="500" step="500" class="w-full rounded-xl border-slate-300 text-sm" required />
                        <p v-if="form.errors.price" class="text-xs text-rose-600 mt-1">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Stok</label>
                        <input v-model.number="form.stock" type="number" min="0" class="w-full rounded-xl border-slate-300 text-sm" required />
                        <p v-if="form.errors.stock" class="text-xs text-rose-600 mt-1">{{ form.errors.stock }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Deskripsi (Opsional)</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Porsi anak SD, bebas MSG"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                    <input v-model="form.is_available" type="checkbox" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500" />
                    <span>Tampilkan menu ke siswa</span>
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm transition"
                >
                    {{ editingId ? 'Simpan Perubahan Menu' : 'Tambah Menu' }}
                </button>
            </form>

            <!-- Daftar Menu -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-extrabold text-slate-800">Daftar Menu Stand ({{ menus.length }})</h2>
                </div>

                <div v-if="menus.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada menu di stand Anda. Tambahkan menu pertama di sebelah kiri.
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div v-for="menu in menus" :key="menu.id" class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800">{{ menu.name }}</h3>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                    :class="menu.is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ menu.is_available ? 'Tersedia' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ menu.description || 'Tanpa deskripsi' }}</p>
                            <div class="flex items-center gap-4 mt-2 text-xs font-bold">
                                <span class="text-amber-700">{{ formatRupiah(menu.price) }}</span>
                                <span :class="menu.stock <= 5 ? 'text-rose-600' : 'text-slate-600'">Stok: {{ menu.stock }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700"
                                @click="editMenu(menu)"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-xs font-bold text-rose-700"
                                @click="deleteMenu(menu.id)"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CanteenLayout>
</template>

