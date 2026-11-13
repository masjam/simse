<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    student: { type: Object, required: true },
    raportSubjects: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    achievements: { type: Array, default: () => [] },
});

const activeTab = ref('raport');

const printRaport = () => {
    window.print();
};
</script>

<template>
    <Head title="Nilai & e-Raport Siswa" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800">Nilai & e-Raport</h1>
                <p class="text-xs text-slate-500">{{ student.name }} &bull; Kelas {{ student.class_name }}</p>
            </div>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
                <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 w-fit">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition"
                        :class="activeTab === 'raport' ? 'bg-teal-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100'"
                        @click="activeTab = 'raport'"
                    >
                        Rekap e-Raport
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition"
                        :class="activeTab === 'detail' ? 'bg-teal-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100'"
                        @click="activeTab = 'detail'"
                    >
                        Rincian Nilai ({{ grades.length }})
                    </button>
                </div>

                <button
                    type="button"
                    @click="printRaport"
                    class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-colors"
                >
                    Cetak e-Raport
                </button>
            </div>

            <!-- Kop Surat Khusus Cetak -->
            <div class="hidden print:block border-b-2 border-double border-slate-800 pb-3 text-center mb-4">
                <h2 class="text-base font-black uppercase text-slate-900">SD MUHAMMADIYAH AL MUJAHIDIN WONOSARI</h2>
                <p class="text-xs text-slate-600">Laporan Hasil Belajar &amp; Catatan Prestasi Siswa</p>
                <p class="text-xs font-bold text-slate-800 mt-1">{{ student.name }} &bull; NISN: {{ student.nisn }} &bull; Kelas {{ student.class_name }}</p>
            </div>

            <!-- Rekap e-Raport Read-Only -->
            <div v-if="activeTab === 'raport'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-800 text-sm sm:text-base">Capaian Nilai per Mata Pelajaran</h2>
                        <p class="text-xs text-slate-500">Bobot: Tugas 30%, Kuis 20%, UTS 25%, UAS 25% (Hanya Baca)</p>
                    </div>
                </div>

                <div v-if="raportSubjects.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada nilai yang dipublikasikan oleh guru.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold">Mata Pelajaran</th>
                                <th class="px-3 py-3 text-center font-bold">Tugas</th>
                                <th class="px-3 py-3 text-center font-bold">Kuis</th>
                                <th class="px-3 py-3 text-center font-bold">UTS</th>
                                <th class="px-3 py-3 text-center font-bold">UAS</th>
                                <th class="px-3 py-3 text-center font-bold">Nilai Akhir</th>
                                <th class="px-3 py-3 text-center font-bold">Predikat</th>
                                <th class="px-4 py-3 text-left font-bold">Catatan Capaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in raportSubjects" :key="item.subject_id" class="hover:bg-slate-50">
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-slate-800">{{ item.subject_name }}</p>
                                    <p class="text-xs text-slate-500">{{ item.teacher_name }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-center">{{ item.tugas_avg ?? '-' }}</td>
                                <td class="px-3 py-3.5 text-center">{{ item.kuis_avg ?? '-' }}</td>
                                <td class="px-3 py-3.5 text-center">{{ item.uts ?? '-' }}</td>
                                <td class="px-3 py-3.5 text-center">{{ item.uas ?? '-' }}</td>
                                <td class="px-3 py-3.5 text-center font-black text-teal-700">{{ item.nilai_akhir }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="px-2.5 py-1 rounded-full bg-teal-100 text-teal-800 text-xs font-extrabold">
                                        {{ item.predikat }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-600 max-w-xs">{{ item.catatan }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Catatan Prestasi Siswa Terverifikasi -->
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    <h3 class="text-sm font-extrabold text-slate-800 mb-3">Catatan Prestasi Siswa (Terverifikasi)</h3>
                    <div v-if="achievements.length === 0" class="text-xs text-slate-500 italic">
                        Belum ada catatan prestasi lomba terverifikasi.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm bg-white rounded-xl overflow-hidden border border-slate-200">
                            <thead class="bg-slate-100 text-slate-600 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-2.5 text-center w-12 font-bold">No</th>
                                    <th class="px-4 py-2.5 text-left font-bold">Nama Prestasi / Lomba</th>
                                    <th class="px-4 py-2.5 text-center font-bold">Kategori</th>
                                    <th class="px-4 py-2.5 text-center font-bold">Tingkat</th>
                                    <th class="px-4 py-2.5 text-center font-bold">Tahun</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(ach, idx) in achievements" :key="ach.id">
                                    <td class="px-4 py-2.5 text-center">{{ idx + 1 }}</td>
                                    <td class="px-4 py-2.5">
                                        <p class="font-bold text-slate-800">{{ ach.name }}</p>
                                        <p v-if="ach.organizer && ach.organizer !== '-'" class="text-xs text-slate-500">{{ ach.organizer }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">{{ ach.category }}</td>
                                    <td class="px-4 py-2.5 text-center">{{ ach.level }}</td>
                                    <td class="px-4 py-2.5 text-center font-semibold">{{ ach.year }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detailed Grades -->
            <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div v-if="grades.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada rincian nilai tercatat.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold">Mata Pelajaran</th>
                                <th class="px-4 py-3 text-left font-bold">Jenis</th>
                                <th class="px-4 py-3 text-left font-bold">Penilaian</th>
                                <th class="px-4 py-3 text-center font-bold">Skor</th>
                                <th class="px-4 py-3 text-left font-bold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="grade in grades" :key="grade.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ grade.subject }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 text-xs font-bold">{{ grade.type }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ grade.name }}</td>
                                <td class="px-4 py-3 text-center font-black text-slate-800">{{ grade.score }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ grade.date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

