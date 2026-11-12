<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import QrcodeVue from 'qrcode.vue';

defineProps({
    student: {
        type: Object,
        default: () => ({
            name: 'Ahmad Fulan',
            nisn: '0031234567',
            nik: '3201123456789012',
            major: 'Rekayasa Perangkat Lunak',
            level: '11',
            photoUrl: 'https://ui-avatars.com/api/?name=Ahmad+Fulan&background=random&size=200'
        })
    }
});
</script>

<template>
    <Head title="Kartu Tanda Siswa (KTS) Digital" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kartu Tanda Siswa (KTS) Digital</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-center">
                
                <!-- ID Card Container -->
                <div class="relative w-[350px] bg-gradient-to-br from-indigo-900 to-purple-800 rounded-2xl shadow-2xl overflow-hidden text-white border border-indigo-400/30 group transition-all duration-300 hover:shadow-indigo-500/50">
                    
                    <!-- Decorative Background Elements -->
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-40 h-40 bg-purple-500 opacity-20 rounded-full blur-2xl"></div>
                    
                    <!-- Card Header -->
                    <div class="relative px-6 py-5 border-b border-white/20 bg-black/10 backdrop-blur-sm flex items-center justify-between">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-indigo-200 font-bold">KARTU PELAJAR</h3>
                            <h2 class="text-sm font-semibold tracking-wide mt-1">SD Terpadu Nusantara</h2>
                        </div>
                        <!-- Logo Placeholder -->
                        <div class="h-10 w-10 bg-white/20 rounded-full flex items-center justify-center p-1 border border-white/40">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="relative px-6 py-6 flex flex-col items-center">
                        <!-- Student Photo -->
                        <div class="relative p-1 bg-gradient-to-r from-purple-400 to-indigo-400 rounded-full mb-4 shadow-lg">
                            <img :src="student.photoUrl" alt="Foto Siswa" class="w-24 h-24 rounded-full border-2 border-white object-cover">
                        </div>

                        <!-- Student Data -->
                        <div class="text-center w-full">
                            <h1 class="text-xl font-bold tracking-wide">{{ student.name }}</h1>
                            <p class="text-indigo-200 text-sm mt-1 font-medium">{{ student.major }} (Kelas {{ student.level }})</p>
                            
                            <div class="mt-4 bg-black/20 rounded-lg p-3 text-left backdrop-blur-sm border border-white/10">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-indigo-200 uppercase tracking-wider">NISN</span>
                                    <span class="text-sm font-mono font-bold">{{ student.nisn }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-indigo-200 uppercase tracking-wider">NIK</span>
                                    <span class="text-sm font-mono font-bold">{{ student.nik }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer / QR Code -->
                    <div class="relative px-6 py-5 bg-white flex items-center justify-between group-hover:bg-gray-50 transition-colors">
                        <div class="text-gray-800 text-xs w-2/3 pr-2">
                            <p class="font-bold mb-1 text-indigo-900">Validasi Identitas</p>
                            <p class="text-[10px] leading-tight text-gray-500">Scan QR Code ini untuk absensi digital dan transaksi e-Kantin di area sekolah.</p>
                        </div>
                        
                        <!-- QR Code Component -->
                        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-lg shrink-0">
                            <qrcode-vue :value="student.nisn" :size="70" level="H" foreground="#312e81" />
                        </div>
                    </div>

                </div>

            </div>

            <!-- Print Button (Hidden on Print) -->
            <div class="flex justify-center mt-8 print:hidden">
                <button onclick="window.print()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak KTS Digital
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .relative.w-\[350px\], .relative.w-\[350px\] * {
        visibility: visible;
    }
    .relative.w-\[350px\] {
        position: absolute;
        left: 0;
        top: 0;
        transform: scale(1.1);
        margin: 0;
        box-shadow: none !important;
    }
}
</style>
