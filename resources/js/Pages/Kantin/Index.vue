<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    stands: Array,
    walletBalance: Number
});

const cart = ref([]);

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const addToCart = (menu) => {
    const existing = cart.value.find(item => item.id === menu.id);
    if (existing) {
        if (existing.qty < menu.stock) existing.qty++;
    } else {
        cart.value.push({ ...menu, qty: 1 });
    }
};

const decreaseQty = (item) => {
    if (item.qty > 1) {
        item.qty--;
    } else {
        cart.value = cart.value.filter(i => i.id !== item.id);
    }
};

const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.qty), 0);
});
</script>

<template>
    <Head title="e-Kantin Digital" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-12 bg-gray-50 min-h-screen pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header & Wallet Info -->
                <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">E-Kantin Digital 🍔</h2>
                        <p class="text-gray-500 text-sm mt-1">Pesan makanan favoritmu tanpa harus antri.</p>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-4 rounded-xl text-white shadow-lg shadow-emerald-500/30 min-w-[200px] flex items-center justify-between">
                        <div>
                            <p class="text-xs text-emerald-100 font-medium tracking-wide">SALDO DOMPET DIGITAL</p>
                            <p class="text-xl font-bold mt-0.5">{{ formatRupiah(walletBalance) }}</p>
                        </div>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Stands & Menus Grid -->
                <div class="space-y-10">
                    <div v-for="stand in stands" :key="stand.id">
                        <div class="flex items-center gap-3 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">{{ stand.name }}</h3>
                            <span v-if="!stand.is_open" class="text-xs bg-red-100 text-red-600 px-2.5 py-1 rounded-full font-bold uppercase tracking-wide">Tutup</span>
                        </div>

                        <div v-if="stand.is_open" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="menu in stand.menus" :key="menu.id" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition group flex flex-col">
                                <div class="relative h-48 overflow-hidden bg-gray-200">
                                    <img :src="menu.photo" :alt="menu.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur text-gray-800 text-xs font-bold px-2 py-1 rounded-md shadow">
                                        Stok: {{ menu.stock }}
                                    </div>
                                </div>
                                <div class="p-5 flex flex-col flex-grow">
                                    <h4 class="font-bold text-gray-800 text-lg line-clamp-1">{{ menu.name }}</h4>
                                    <p class="text-indigo-600 font-bold mt-1 text-lg">{{ formatRupiah(menu.price) }}</p>
                                    
                                    <button @click="addToCart(menu)" 
                                            class="mt-auto pt-4 w-full bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 font-medium py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-gray-100 border border-gray-200 rounded-xl p-6 text-center text-gray-500 border-dashed">
                            Stand ini sedang tutup atau kehabisan bahan.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Floating Cart Bar (Mobile-First approach) -->
        <div v-if="cartTotal > 0" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.1)] z-50 transform transition-transform duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Cart Items Summary -->
                <div class="flex items-center gap-4 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0 hide-scrollbar">
                    <div v-for="item in cart" :key="item.id" class="flex items-center gap-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 shrink-0">
                        <img :src="item.photo" class="w-10 h-10 rounded-md object-cover">
                        <div>
                            <p class="text-xs font-bold text-gray-800 line-clamp-1 w-24">{{ item.name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <button @click="decreaseQty(item)" class="w-5 h-5 bg-gray-200 rounded text-gray-600 flex items-center justify-center hover:bg-red-100 hover:text-red-600 transition">-</button>
                                <span class="text-xs font-bold">{{ item.qty }}</span>
                                <button @click="addToCart(item)" class="w-5 h-5 bg-gray-200 rounded text-gray-600 flex items-center justify-center hover:bg-indigo-100 hover:text-indigo-600 transition">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Action -->
                <div class="flex items-center gap-6 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-0 border-gray-100 pt-3 sm:pt-0">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Pembayaran</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatRupiah(cartTotal) }}</p>
                    </div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                        Bayar Sekarang
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
