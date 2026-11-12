<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    violations: { type: Array, default: () => [] },
    counselings: { type: Array, default: () => [] },
    violationPoints: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});

const page = usePage();
const activeTab = ref('pelanggaran');

const severityColors = {
    Ringan: 'bg-blue-100 text-blue-700 border-blue-200',
    Sedang: 'bg-amber-100 text-amber-700 border-amber-200',
    Berat: 'bg-red-100 text-red-700 border-red-200',
    'Sangat Berat': 'bg-red-200 text-red-800 border-red-300',
};

const counselingTypeColors = {
    Pribadi: 'bg-purple-100 text-purple-700',
    Akademik: 'bg-blue-100 text-blue-700',
    Karir: 'bg-emerald-100 text-emerald-700',
    Kelompok: 'bg-amber-100 text-amber-700',
};

// Report Violation Modal
const showReportModal = ref(false);
const violationForm = useForm({
    student_id: props.students[0]?.id || '',
    violation_point_id: props.violationPoints[0]?.id || '',
    date: new Date().toISOString().slice(0, 10),
    description: '',
});

const submitViolation = () => {
    violationForm.post(route('guru.bk.violation.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showReportModal.value = false;
            violationForm.reset();
        }
    });
};

// Counseling Modal
const showCounselingModal = ref(false);
const counselingForm = useForm({
    student_id: props.students[0]?.id || '',
    type: 'Pribadi',
    date: new Date().toISOString().slice(0, 10),
    problem: '',
    solution: '',
    status: 'Selesai',
});

const submitCounseling = () => {
    counselingForm.post(route('guru.bk.counseling.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCounselingModal.value = false;
            counselingForm.reset();
        }
    });
};

// Printable BAP Modal
const showBapModal = ref(false);
const activeViolation = ref(null);

const printBap = (violation) => {
    activeViolation.value = violation;
    showBapModal.value = true;
};
</script>

<template>
    <Head title="Bimbingan Konseling" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Bimbingan & Konseling (BK)</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">Pencatatan kedisiplinan dan pembinaan santri SD Muhammadiyah Al Mujahidin</p></template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                <span>✅</span>
                <span class="font-semibold">{{ page.props.flash.success }}</span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-slate-800">{{ violations.length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Total Catatan Pelanggaran</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-rose-600">{{ violations.filter(v => v.sp).length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Surat Peringatan (SP)</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-purple-600">{{ counselings.length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Sesi Pembinaan / Konseling</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4">
                    <p class="text-2xl font-black text-emerald-600">{{ counselings.filter(c => c.status === 'done').length }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pembinaan Tuntas</p>
                </div>
            </div>

            <!-- Tabs + Actions -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="flex gap-1 bg-slate-100 p-1 rounded-lg">
                    <button @click="activeTab = 'pelanggaran'" class="px-4 py-2 text-xs font-bold rounded-md transition"
                            :class="activeTab === 'pelanggaran' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-500'">
                        ⚠️ Catatan Pelanggaran
                    </button>
                    <button @click="activeTab = 'konseling'" class="px-4 py-2 text-xs font-bold rounded-md transition"
                            :class="activeTab === 'konseling' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-500'">
                        💜 Sesi Pembinaan Santri
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showCounselingModal = true" class="px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold rounded-lg hover:bg-purple-100 transition">
                        + Catat Konseling
                    </button>
                    <button @click="showReportModal = true" class="px-4 py-2 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition shadow-md shadow-rose-600/20">
                        + Lapor Pelanggaran
                    </button>
                </div>
            </div>

            <!-- Violations Tab -->
            <div v-if="activeTab === 'pelanggaran'" class="space-y-3">
                <div v-for="v in violations" :key="v.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="flex">
                        <div class="w-1.5 shrink-0" :class="v.type === 'Berat' ? 'bg-rose-500' : v.type === 'Sedang' ? 'bg-amber-500' : 'bg-blue-400'"></div>
                        <div class="flex-1 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-600 shrink-0">
                                        {{ v.student.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <h3 class="font-bold text-slate-800 text-sm">{{ v.student }}</h3>
                                            <span class="text-xs font-bold px-2 py-0.5 rounded border" :class="severityColors[v.type]">
                                                {{ v.type }} (+{{ v.points }} Poin)
                                            </span>
                                            <span v-if="v.sp" class="text-xs font-bold px-2 py-0.5 rounded bg-rose-600 text-white">
                                                {{ v.sp }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ v.description }}</p>
                                        <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400">
                                            <span>Kelas: <strong>{{ v.class }}</strong></span>
                                            <span>NISN: {{ v.nisn }}</span>
                                            <span>📅 {{ v.date }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button @click="printBap(v)" class="px-3.5 py-1.5 text-xs font-bold border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak BAP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="violations.length === 0" class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Tidak ada catatan pelanggaran santri. Alhamdulillah kedisiplinan terjaga dengan baik.
                </div>
            </div>

            <!-- Counseling Tab -->
            <div v-if="activeTab === 'konseling'" class="space-y-3">
                <div v-for="c in counselings" :key="c.id"
                     class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                                💜
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h3 class="font-bold text-slate-800 text-sm">{{ c.student }}</h3>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded" :class="counselingTypeColors[c.type] || 'bg-slate-100 text-slate-600'">
                                        Bidang: {{ c.type }}
                                    </span>
                                    <span :class="c.status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'" class="text-xs font-bold px-2 py-0.5 rounded">
                                        {{ c.status === 'scheduled' ? 'Terjadwal' : 'Selesai' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-700 font-semibold mb-1">Masalah / Topik: {{ c.topic }}</p>
                                <p v-if="c.solution" class="text-xs text-emerald-700 bg-emerald-50 p-2 rounded border border-emerald-100 mb-2">
                                    <strong>Rekomendasi / Solusi:</strong> {{ c.solution }}
                                </p>
                                <div class="flex items-center gap-3 text-xs text-slate-400">
                                    <span>Kelas: <strong>{{ c.class }}</strong></span>
                                    <span>📅 {{ c.date }}</span>
                                    <span>👤 Pembimbing: <strong>{{ c.guru }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="counselings.length === 0" class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    Belum ada riwayat sesi konseling santri.
                </div>
            </div>

            <!-- Report Violation Modal -->
            <Teleport to="body">
                <div v-if="showReportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-base">Catat Pelanggaran Santri</h3>
                            <button @click="showReportModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>
                        <form @submit.prevent="submitViolation" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Pilih Siswa / Santri</label>
                                <select v-model="violationForm.student_id" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500">
                                    <option v-for="stu in students" :key="stu.id" :value="stu.id">{{ stu.name }} ({{ stu.class }} - {{ stu.nisn }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Jenis Pelanggaran & Bobot Poin</label>
                                <select v-model="violationForm.violation_point_id" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500">
                                    <option v-for="vp in violationPoints" :key="vp.id" :value="vp.id">{{ vp.name }} ({{ vp.category }} - {{ vp.points }} Poin)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Tanggal Kejadian</label>
                                <input type="date" v-model="violationForm.date" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500" />
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Kronologi Kejadian</label>
                                <textarea v-model="violationForm.description" required rows="3" placeholder="Jelaskan secara ringkas pelanggaran yang dilakukan..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500"></textarea>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showReportModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="violationForm.processing" class="flex-1 py-2 text-xs font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition shadow-md shadow-rose-600/20 disabled:opacity-50">
                                    {{ violationForm.processing ? 'Menyimpan...' : 'Simpan Laporan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Counseling Modal -->
            <Teleport to="body">
                <div v-if="showCounselingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-base">Catat Sesi Bimbingan & Konseling</h3>
                            <button @click="showCounselingModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                        </div>
                        <form @submit.prevent="submitCounseling" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Pilih Siswa / Santri</label>
                                <select v-model="counselingForm.student_id" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option v-for="stu in students" :key="stu.id" :value="stu.id">{{ stu.name }} ({{ stu.class }})</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Bidang Bimbingan</label>
                                    <select v-model="counselingForm.type" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500">
                                        <option>Pribadi</option>
                                        <option>Akademik</option>
                                        <option>Sosial / Kelompok</option>
                                        <option>Karir</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Tanggal</label>
                                    <input type="date" v-model="counselingForm.date" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Uraian Masalah / Topik Konseling</label>
                                <textarea v-model="counselingForm.problem" required rows="3" placeholder="Uraikan keluhan, hambatan belajar atau masalah santri..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Rekomendasi / Tindak Lanjut Solusi</label>
                                <textarea v-model="counselingForm.solution" rows="2" placeholder="Solusi yang disepakati..."
                                          class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Status Bimbingan</label>
                                <select v-model="counselingForm.status" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option>Selesai</option>
                                    <option>Berlangsung</option>
                                    <option>Menunggu</option>
                                </select>
                            </div>
                            <div class="flex gap-2.5 pt-3">
                                <button type="button" @click="showCounselingModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="counselingForm.processing" class="flex-1 py-2 text-xs font-bold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition shadow-md shadow-purple-600/20 disabled:opacity-50">
                                    {{ counselingForm.processing ? 'Menyimpan...' : 'Simpan Sesi Bimbingan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>

            <!-- Printable BAP Modal -->
            <Teleport to="body">
                <div v-if="showBapModal && activeViolation" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 max-h-[90vh] overflow-y-auto space-y-6">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                            <h3 class="font-bold text-slate-800 text-sm">Berita Acara Pelanggaran (BAP) Santri</h3>
                            <div class="flex gap-2">
                                <button onclick="window.print()" class="px-3.5 py-1.5 bg-rose-600 text-white font-bold text-xs rounded-lg hover:bg-rose-700 transition">
                                    🖨️ Cetak Dokumen BAP
                                </button>
                                <button @click="showBapModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <!-- Formal BAP Letter -->
                        <div class="border border-slate-300 p-6 rounded-xl space-y-4 text-xs font-sans text-slate-800">
                            <div class="border-b-2 border-double border-slate-800 pb-3 text-center">
                                <h4 class="text-[11px] font-bold uppercase text-slate-600">SD MUHAMMADIYAH AL MUJAHIDIN WONOSARI</h4>
                                <h2 class="text-sm font-black uppercase text-slate-900 mt-0.5">BERITA ACARA PELANGGARAN TATA TERTIB SEKOLAH</h2>
                                <p class="text-[10px] text-slate-400 mt-0.5">Nomor: BAP/BK-SDAM/{{ new Date().getFullYear() }}/0{{ activeViolation.id }}</p>
                            </div>

                            <p class="leading-relaxed">
                                Pada hari ini, tanggal <strong>{{ activeViolation.date }}</strong>, telah dicatat dan dilakukan pembinaan terhadap peserta didik:
                            </p>

                            <div class="bg-slate-50 p-3 rounded border border-slate-200 space-y-1">
                                <div class="flex"><span class="w-28 text-slate-500 font-semibold">Nama Santri</span>: <strong class="ml-1">{{ activeViolation.student }}</strong></div>
                                <div class="flex"><span class="w-28 text-slate-500 font-semibold">Kelas / Rombel</span>: <span class="ml-1">{{ activeViolation.class }}</span></div>
                                <div class="flex"><span class="w-28 text-slate-500 font-semibold">NISN</span>: <span class="ml-1 font-mono">{{ activeViolation.nisn }}</span></div>
                                <div class="flex"><span class="w-28 text-slate-500 font-semibold">Tingkat / Poin</span>: <span class="ml-1 font-bold text-rose-600">{{ activeViolation.type }} (+{{ activeViolation.points }} Poin)</span></div>
                            </div>

                            <div>
                                <p class="font-bold text-slate-700 mb-1">Uraian Pelanggaran:</p>
                                <p class="bg-slate-50 p-2.5 rounded border border-slate-200 text-slate-700 leading-relaxed">
                                    {{ activeViolation.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-8 text-center text-xs">
                                <div>
                                    <p class="text-slate-500">Santri yang bersangkutan,</p>
                                    <div class="h-14"></div>
                                    <p class="font-bold">( {{ activeViolation.student }} )</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Guru BK / Pembimbing,</p>
                                    <div class="h-14"></div>
                                    <p class="font-bold">( Siti Guru, S.Pd.I )</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </TeacherLayout>
</template>
