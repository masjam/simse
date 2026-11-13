<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedSemester: { type: String, default: 'Ganjil 2026/2027' },
    students: { type: Array, default: () => [] },
    teacher: { type: Object, default: () => ({ name: 'Siti Guru, S.Pd.I', nip: '198407152010012015' }) },
});

const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const selectedClass = ref(props.selectedClass || classList.value[0]);
const selectedSemester = ref(props.selectedSemester || 'Ganjil 2026/2027');

const onFilterChange = () => {
    router.get(route('guru.raport'), {
        class: selectedClass.value,
        semester: selectedSemester.value,
    }, { preserveState: true, replace: true });
};

const getPredikatColor = (p) => {
    if (p.startsWith('A')) return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (p.startsWith('B')) return 'bg-blue-100 text-blue-700 border-blue-200';
    if (p.startsWith('C')) return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-red-100 text-red-700 border-red-200';
};

// Modal for Printable Raport
const showPrintModal = ref(false);
const activeStudent = ref(null);

const openPrint = (student) => {
    activeStudent.value = student;
    showPrintModal.value = true;
};

const printDoc = () => {
    window.print();
};
</script>

<template>
    <Head title="e-Raport" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">e-Raport Hasil Belajar</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Penerbitan capaian kompetensi santri SD Muhammadiyah Al Mujahidin</p></template>

        <div class="space-y-6">
            <!-- Filter Bar -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="text-xs font-bold text-slate-600 mb-1 block">Rombongan Belajar (Kelas)</label>
                    <select v-model="selectedClass" @change="onFilterChange" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option v-for="cls in classList" :key="cls">{{ cls }}</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="text-xs font-bold text-slate-600 mb-1 block">Semester & Tahun Ajaran</label>
                    <select v-model="selectedSemester" @change="onFilterChange" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option>Ganjil 2026/2027</option>
                        <option>Genap 2025/2026</option>
                    </select>
                </div>
                <button v-if="students.length > 0" @click="openPrint(students[0])"
                        class="px-5 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Preview Format Cetak
                </button>
            </div>

            <!-- Raport Card List -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="student in students" :key="student.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    {{ student.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">{{ student.name }}</h4>
                                    <p class="text-xs text-slate-400">NISN: {{ student.nisn || '-' }}</p>
                                </div>
                            </div>
                            <span :class="getPredikatColor(student.predikat)" class="text-xs font-black px-3 py-1 rounded-lg border">
                                Predikat: {{ student.predikat }}
                            </span>
                        </div>

                        <div class="flex items-center gap-4 mb-3">
                            <div class="flex-1">
                                <p class="text-[11px] font-semibold text-slate-500 mb-1">Nilai Kumulatif Akhir</p>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-500"
                                         :class="student.nilaiAkhir >= 85 ? 'bg-emerald-500' : student.nilaiAkhir >= 75 ? 'bg-blue-500' : 'bg-amber-500'"
                                         :style="`width: ${student.nilaiAkhir}%`"></div>
                                </div>
                            </div>
                            <span class="text-2xl font-black text-slate-800">{{ student.nilaiAkhir }}</span>
                        </div>

                        <p class="text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-lg p-3 leading-relaxed">
                            <strong class="text-slate-700">Capaian Kompetensi:</strong> {{ student.catatan }}
                        </p>
                    </div>

                    <div class="border-t border-slate-100 px-5 py-3 flex justify-between items-center bg-slate-50/50">
                        <span class="text-[11px] font-semibold text-slate-400">{{ selectedSemester }}</span>
                        <div class="flex gap-2">
                            <button @click="openPrint(student)" class="px-3.5 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Cetak Raport
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Printable e-Raport Modal -->
            <Teleport to="body">
                <div v-if="showPrintModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto p-6 md:p-8">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-200 mb-6 print:hidden">
                            <h3 class="font-bold text-slate-800">Pratinjau Cetak e-Raport Santri</h3>
                            <div class="flex gap-2">
                                <button @click="printDoc" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition flex items-center gap-1.5">
                                    🖨️ Cetak / Simpan PDF
                                </button>
                                <button @click="showPrintModal = false" class="px-3 py-2 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg hover:bg-slate-200 transition">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <!-- Formal School Raport Layout -->
                        <div v-if="activeStudent" class="border border-slate-300 p-8 rounded-xl bg-white text-slate-800 font-sans text-xs space-y-6">
                            <!-- Kop Surat Resmi SD Muhammadiyah Al Mujahidin -->
                            <div class="border-b-2 border-double border-slate-800 pb-4 text-center">
                                <h4 class="text-xs uppercase font-bold tracking-widest text-slate-600">MAJELIS PENDIDIKAN DASAR DAN MENENGAH PDM GUNUNGKIDUL</h4>
                                <h2 class="text-lg font-black uppercase text-slate-900 tracking-wide mt-1">SD MUHAMMADIYAH AL MUJAHIDIN WONOSARI</h2>
                                <p class="text-[10px] text-slate-500 mt-1">Alamat: Jl. KH Agus Salim No. 35, Wonosari, Gunungkidul, D.I. Yogyakarta 55812 | Telp: (0274) 391234</p>
                            </div>

                            <div class="text-center">
                                <h3 class="font-bold text-sm uppercase underline tracking-wider">LAPORAN HASIL BELAJAR PESERTA DIDIK</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Tahun Ajaran: {{ selectedSemester }}</p>
                            </div>

                            <!-- Student Info -->
                            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <div class="space-y-1">
                                    <div class="flex"><span class="w-28 font-semibold text-slate-500">Nama Santri</span>: <span class="font-bold text-slate-800 ml-1">{{ activeStudent.name }}</span></div>
                                    <div class="flex"><span class="w-28 font-semibold text-slate-500">NISN</span>: <span class="font-mono ml-1">{{ activeStudent.nisn || '-' }}</span></div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex"><span class="w-28 font-semibold text-slate-500">Kelas / Rombel</span>: <span class="font-bold ml-1">{{ selectedClass }}</span></div>
                                    <div class="flex"><span class="w-28 font-semibold text-slate-500">Fase / Semester</span>: <span class="ml-1">Fase A / {{ selectedSemester }}</span></div>
                                </div>
                            </div>

                            <!-- Nilai Table -->
                            <table class="w-full border-collapse border border-slate-300 text-left">
                                <thead class="bg-slate-100 text-slate-700">
                                    <tr>
                                        <th class="border border-slate-300 p-2 text-center w-10">No</th>
                                        <th class="border border-slate-300 p-2">Mata Pelajaran</th>
                                        <th class="border border-slate-300 p-2 text-center w-20">Nilai Akhir</th>
                                        <th class="border border-slate-300 p-2 text-center w-20">Predikat</th>
                                        <th class="border border-slate-300 p-2">Capaian Kompetensi & Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300 p-2.5 text-center">1</td>
                                        <td class="border border-slate-300 p-2.5 font-semibold">Pendidikan Agama Islam dan Budi Pekerti</td>
                                        <td class="border border-slate-300 p-2.5 text-center font-bold text-sm">{{ activeStudent.nilaiAkhir }}</td>
                                        <td class="border border-slate-300 p-2.5 text-center font-bold">{{ activeStudent.predikat }}</td>
                                        <td class="border border-slate-300 p-2.5 text-slate-600 leading-relaxed">{{ activeStudent.catatan }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Catatan Prestasi Siswa -->
                            <div class="space-y-2">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">
                                    Catatan Prestasi &amp; Penghargaan Siswa
                                </h4>
                                <table class="w-full border-collapse border border-slate-300 text-left">
                                    <thead class="bg-slate-100 text-slate-700">
                                        <tr>
                                            <th class="border border-slate-300 p-2 text-center w-10">No</th>
                                            <th class="border border-slate-300 p-2">Nama Prestasi / Lomba</th>
                                            <th class="border border-slate-300 p-2 text-center">Kategori</th>
                                            <th class="border border-slate-300 p-2 text-center">Tingkat</th>
                                            <th class="border border-slate-300 p-2 text-center w-16">Tahun</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!activeStudent.achievements || activeStudent.achievements.length === 0">
                                            <td colspan="5" class="border border-slate-300 p-2.5 text-center text-slate-500 italic">
                                                Belum ada catatan prestasi terverifikasi pada semester ini.
                                            </td>
                                        </tr>
                                        <tr v-for="(ach, idx) in activeStudent.achievements" :key="ach.id">
                                            <td class="border border-slate-300 p-2 text-center">{{ idx + 1 }}</td>
                                            <td class="border border-slate-300 p-2 font-semibold">
                                                {{ ach.name }}
                                                <span v-if="ach.organizer && ach.organizer !== '-'" class="block text-[10px] font-normal text-slate-500">
                                                    Penyelenggara: {{ ach.organizer }}
                                                </span>
                                            </td>
                                            <td class="border border-slate-300 p-2 text-center">{{ ach.category }}</td>
                                            <td class="border border-slate-300 p-2 text-center">{{ ach.level }}</td>
                                            <td class="border border-slate-300 p-2 text-center">{{ ach.year }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Signatures -->
                            <div class="grid grid-cols-3 gap-4 pt-12 text-center text-xs">
                                <div>
                                    <p class="text-slate-500">Mengetahui,<br>Orang Tua / Wali Santri</p>
                                    <div class="h-16"></div>
                                    <p class="font-bold border-b border-slate-400 inline-block px-8 pb-0.5">( ........................................ )</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Wonosari, 25 Oktober 2026<br>Guru Pendidikan Agama Islam</p>
                                    <div class="h-16 flex items-center justify-center">
                                        <span class="text-[10px] text-emerald-600 font-bold border border-emerald-300 px-2 py-0.5 rounded bg-emerald-50">Tervalidasi Digital</span>
                                    </div>
                                    <p class="font-bold">{{ teacher.name }}</p>
                                    <p class="text-[10px] text-slate-500">NIP. {{ teacher.nip }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Kepala Sekolah<br>SD Muhammadiyah Al Mujahidin</p>
                                    <div class="h-16"></div>
                                    <p class="font-bold">Joko Kiswanto, S.Pd.I., M.Pd.</p>
                                    <p class="text-[10px] text-slate-500">NIP. 197805122005011008</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
