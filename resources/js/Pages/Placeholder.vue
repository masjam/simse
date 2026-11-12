<script setup>
import { Head, Link } from '@inertiajs/vue3';
defineProps({ title: String });
</script>

<template>
    <Head :title="title" />
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-500 flex flex-col items-center justify-center p-12 text-center">
                <div class="w-24 h-24 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Modul {{ title }}</h2>
                <p class="text-gray-500 max-w-lg mb-8">Antarmuka (UI) untuk modul ini sedang dalam tahap pengembangan iteratif selanjutnya. Rute, *Controller*, dan tabel database penyokongnya telah aktif.</p>
                <Link :href="route('dashboard')" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition shadow-lg shadow-indigo-200">
                    Kembali ke Dashboard Utama
                </Link>
            </div>
        </div>
    </div>
</template>
