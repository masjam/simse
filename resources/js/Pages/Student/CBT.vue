<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    student: { type: Object, required: true },
    exams: { type: Array, default: () => [] },
});

const statusBadge = (status) => {
    switch (status) {
        case 'Tersedia':
            return 'bg-emerald-100 text-emerald-700';
        case 'Sedang Dikerjakan':
            return 'bg-amber-100 text-amber-800';
        case 'Akan Datang':
            return 'bg-sky-100 text-sky-700';
        case 'Selesai':
            return 'bg-indigo-100 text-indigo-700';
        default:
            return 'bg-slate-200 text-slate-600';
    }
};
</script>

<template>
    <Head title="Ujian Online (CBT) Siswa" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Ujian Online (CBT)</h1>
                <p class="text-xs text-slate-500">{{ student.name }} &bull; Kelas {{ student.class_name }}</p>
            </div>
        </template>

        <div class="space-y-5">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs sm:text-sm text-amber-900">
                <p class="font-extrabold">Peraturan Anti-Kecurangan CBT:</p>
                <p class="mt-1">
                    Ujian berlangsung dengan pengawasan layar penuh, pemblokiran klik kanan, dan pencatatan perpindahan tab otomatis.
                    Jika batas pelanggaran yang ditentukan guru tercapai, ujian akan dikumpulkan secara otomatis.
                </p>
            </div>

            <div v-if="exams.length === 0" class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-500">
                Belum ada ujian CBT yang dijadwalkan saat ini.
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="exam in exams"
                    :key="exam.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between gap-4"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 text-xs font-bold">
                                {{ exam.subject }}
                            </span>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-extrabold"
                                :class="statusBadge(exam.status)"
                            >
                                {{ exam.status }}
                            </span>
                        </div>

                        <h2 class="text-base font-extrabold text-slate-900">{{ exam.title }}</h2>
                        <p v-if="exam.description" class="text-xs text-slate-600">{{ exam.description }}</p>

                        <div class="grid grid-cols-2 gap-2 pt-2 text-xs text-slate-600">
                            <div class="p-2.5 rounded-xl bg-slate-50">
                                <span class="text-slate-400 block">Durasi</span>
                                <span class="font-bold text-slate-800">{{ exam.duration_minutes }} Menit</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50">
                                <span class="text-slate-400 block">Jumlah Soal</span>
                                <span class="font-bold text-slate-800">{{ exam.questions_count }} Butir</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 col-span-2">
                                <span class="text-slate-400 block">Jadwal Akses</span>
                                <span class="font-bold text-slate-800">{{ exam.start_time }} - {{ exam.end_time }}</span>
                            </div>
                        </div>

                        <div v-if="exam.attempt && exam.attempt.finished_at" class="p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-xs space-y-1">
                            <p class="font-extrabold text-indigo-900">
                                Nilai Otomatis PG/Benar-Salah: {{ exam.attempt.score ?? 0 }}
                            </p>
                            <p class="text-indigo-700">
                                Dikumpulkan: {{ exam.attempt.finished_at }} &bull; Pelanggaran tercatat: {{ exam.attempt.violations_count }} / {{ exam.max_violations }}
                            </p>
                            <p v-if="exam.attempt.is_forced_submit" class="font-bold text-rose-600">
                                Status: Dikumpulkan paksa otomatis (batas waktu / pelanggaran).
                            </p>
                        </div>
                    </div>

                    <div>
                        <Link
                            v-if="exam.can_enter"
                            :href="route('siswa.cbt.start', exam.id)"
                            class="block w-full text-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow transition"
                        >
                            {{ exam.status === 'Sedang Dikerjakan' ? 'Lanjutkan Ujian' : 'Mulai Kerjakan Ujian' }}
                        </Link>
                        <Link
                            v-else-if="exam.attempt && exam.attempt.finished_at"
                            :href="route('siswa.cbt.start', exam.id)"
                            class="block w-full text-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                        >
                            Lihat Ringkasan Hasil
                        </Link>
                        <button
                            v-else
                            type="button"
                            disabled
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed"
                        >
                            {{ exam.status === 'Akan Datang' ? 'Belum Dibuka' : 'Ujian Tidak Aktif' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

