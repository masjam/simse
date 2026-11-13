<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
import QrcodeVue from 'qrcode.vue';

defineProps({
    student: { type: Object, required: true },
});

const printKts = () => {
    window.print();
};
</script>

<template>
    <Head title="Kartu Tanda Siswa (KTS) Digital" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Kartu Tanda Siswa (KTS) Digital</h1>
                <p class="text-xs text-slate-500">Identitas Resmi Siswa SD Muhammadiyah Al Mujahidin Wonosari</p>
            </div>
        </template>

        <div class="py-4 sm:py-8">
            <div class="flex justify-center">
                <!-- ID Card Container -->
                <div class="kts-card relative w-[350px] bg-gradient-to-br from-sky-800 via-teal-800 to-emerald-900 rounded-2xl shadow-2xl overflow-hidden text-white border border-teal-400/30">
                    <!-- Decorative Background -->
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-40 h-40 bg-amber-400 opacity-15 rounded-full blur-2xl"></div>

                    <!-- Card Header -->
                    <div class="relative px-6 py-4 border-b border-white/20 bg-black/15 flex items-center justify-between">
                        <div>
                            <h3 class="text-[10px] uppercase tracking-widest text-amber-300 font-extrabold">KARTU TANDA SISWA</h3>
                            <h2 class="text-xs font-bold tracking-wide mt-0.5">SD Muhammadiyah Al Mujahidin</h2>
                            <p class="text-[10px] text-sky-200">Wonosari &bull; TA {{ student.academic_year }}</p>
                        </div>
                        <div class="h-10 w-10 bg-white/20 rounded-xl flex items-center justify-center border border-white/40 font-black text-sm">
                            SD
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="relative px-6 py-6 flex flex-col items-center">
                        <div class="relative p-1 bg-gradient-to-r from-amber-300 to-emerald-400 rounded-full mb-4 shadow-lg">
                            <img
                                :src="student.photo_url"
                                alt="Foto Siswa"
                                class="w-24 h-24 rounded-full border-2 border-white object-cover"
                            />
                        </div>

                        <div class="text-center w-full">
                            <h1 class="text-lg font-black tracking-wide">{{ student.name }}</h1>
                            <p class="text-sky-100 text-xs mt-1 font-semibold">
                                Kelas {{ student.class_name }} ({{ student.tingkat }})
                            </p>

                            <div class="mt-4 bg-black/25 rounded-xl p-3 text-left border border-white/10 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-sky-200 uppercase tracking-wider">NISN</span>
                                    <span class="text-xs font-mono font-bold">{{ student.nisn }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-sky-200 uppercase tracking-wider">NIK</span>
                                    <span class="text-xs font-mono font-bold">{{ student.nik }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-sky-200 uppercase tracking-wider">Jenis Kelamin</span>
                                    <span class="text-xs font-semibold">{{ student.gender }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer / QR Code -->
                    <div class="relative px-6 py-4 bg-white flex items-center justify-between">
                        <div class="text-slate-800 text-xs w-2/3 pr-2">
                            <p class="font-extrabold mb-1 text-teal-900">QR Identitas Siswa</p>
                            <p class="text-[10px] leading-tight text-slate-500">
                                Pindai QR Code ini untuk presensi masuk/pulang, peminjaman perpustakaan, dan transaksi e-Kantin.
                            </p>
                        </div>

                        <div class="p-1.5 bg-white border border-slate-200 shadow-sm rounded-lg shrink-0">
                            <qrcode-vue :value="student.qr_value" :size="72" level="H" foreground="#0f766e" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mt-6 print:hidden">
                <button
                    type="button"
                    class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-xl shadow-lg transition flex items-center gap-2"
                    @click="printKts"
                >
                    <span>Cetak KTS Digital</span>
                </button>
            </div>
        </div>
    </StudentLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .kts-card,
    .kts-card * {
        visibility: visible;
    }
    .kts-card {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        box-shadow: none !important;
    }
}
</style>
