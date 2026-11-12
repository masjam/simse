<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedDate: { type: String, default: '' },
    students: { type: Array, default: () => [] },
});

const page = usePage();
const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const selectedClass = ref(props.selectedClass || classList.value[0] || '1 Brilliant');
const selectedDate = ref(props.selectedDate || new Date().toISOString().slice(0, 10));
const searchQuery = ref('');

const studentList = ref(props.students.map(s => ({ ...s, status: s.status || null, leave_reason: s.leave_reason || '' })));

watch(() => props.students, (newStudents) => {
    studentList.value = newStudents.map(s => ({ ...s, status: s.status || null, leave_reason: s.leave_reason || '' }));
}, { deep: true });

const onFilterChange = () => {
    router.get(route('guru.absensi'), {
        class: selectedClass.value,
        date: selectedDate.value,
    }, { preserveState: true, replace: true });
};

const filteredStudents = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return studentList.value.filter(s =>
        s.name.toLowerCase().includes(q) || (s.nisn && s.nisn.includes(q))
    );
});

const setStatus = (student, status) => { 
    student.status = status; 
};

const setAll = (status) => { 
    studentList.value.forEach(s => s.status = status); 
};

const statHadir = computed(() => studentList.value.filter(s => s.status === 'Hadir').length);
const statIzin = computed(() => studentList.value.filter(s => s.status === 'Izin').length);
const statSakit = computed(() => studentList.value.filter(s => s.status === 'Sakit').length);
const statAlpa = computed(() => studentList.value.filter(s => s.status === 'Alpa').length);
const statBelum = computed(() => studentList.value.filter(s => !s.status).length);

const isSaving = ref(false);

const submitAbsensi = () => {
    if (statBelum.value > 0) {
        if (!confirm(`⚠️ Ada ${statBelum.value} siswa yang belum dipilih statusnya. Tetap simpan? (Siswa tanpa status akan ditandai Hadir)`)) {
            return;
        }
    }

    isSaving.value = true;
    router.post(route('guru.absensi.store'), {
        class_name: selectedClass.value,
        date: selectedDate.value,
        students: studentList.value.map(s => ({
            id: s.id,
            status: s.status || 'Hadir',
            leave_reason: s.leave_reason || null,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
        }
    });
};

const statusBtns = [
    { label: 'Hadir', value: 'Hadir', active: 'bg-emerald-500 text-white border-emerald-600 shadow-md shadow-emerald-200', inactive: 'bg-white text-slate-600 border-slate-200 hover:bg-emerald-50 hover:border-emerald-300' },
    { label: 'Izin', value: 'Izin', active: 'bg-amber-500 text-white border-amber-600 shadow-md shadow-amber-200', inactive: 'bg-white text-slate-600 border-slate-200 hover:bg-amber-50 hover:border-amber-300' },
    { label: 'Sakit', value: 'Sakit', active: 'bg-blue-500 text-white border-blue-600 shadow-md shadow-blue-200', inactive: 'bg-white text-slate-600 border-slate-200 hover:bg-blue-50 hover:border-blue-300' },
    { label: 'Alpa', value: 'Alpa', active: 'bg-red-500 text-white border-red-600 shadow-md shadow-red-200', inactive: 'bg-white text-slate-600 border-slate-200 hover:bg-red-50 hover:border-red-300' },
];
</script>

<template>
    <Head title="Absensi Kelas" />
    <TeacherLayout>
        <template #header-title><h1 class="text-lg font-bold text-slate-800">Presensi Kehadiran Siswa</h1></template>
        <template #header-subtitle><p class="text-xs text-slate-500">SD Muhammadiyah Al Mujahidin Wonosari</p></template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✅</span>
                    <span class="font-semibold">{{ page.props.flash.success }}</span>
                </div>
            </div>

            <!-- Class & Date Selector + Stats Card -->
            <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 rounded-2xl p-6 text-white shadow-xl shadow-emerald-600/15 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <div>
                            <span class="inline-block px-2.5 py-1 bg-white/20 backdrop-blur-md rounded-md text-[11px] font-bold uppercase tracking-wider mb-2">
                                Presensi Harian
                            </span>
                            <h2 class="text-2xl font-black">Rombel: {{ selectedClass }}</h2>
                            <p class="text-emerald-100 text-xs mt-1">
                                Tanggal: {{ new Date(selectedDate).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2.5 bg-white/10 backdrop-blur-md p-2 rounded-xl border border-white/20">
                            <div>
                                <label class="text-[10px] text-white/80 block font-semibold mb-0.5">Pilih Kelas</label>
                                <select v-model="selectedClass" @change="onFilterChange" class="bg-white text-slate-800 font-bold text-xs rounded-lg px-3 py-1.5 border-0 focus:ring-2 focus:ring-emerald-400">
                                    <option v-for="cls in classList" :key="cls" :value="cls">{{ cls }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/80 block font-semibold mb-0.5">Tanggal</label>
                                <input type="date" v-model="selectedDate" @change="onFilterChange" class="bg-white text-slate-800 font-bold text-xs rounded-lg px-3 py-1.5 border-0 focus:ring-2 focus:ring-emerald-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center">
                            <div class="text-2xl font-black">{{ statHadir }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-100">Hadir</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center">
                            <div class="text-2xl font-black text-amber-200">{{ statIzin }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-100">Izin</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center">
                            <div class="text-2xl font-black text-blue-200">{{ statSakit }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-100">Sakit</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center">
                            <div class="text-2xl font-black text-rose-200">{{ statAlpa }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-100">Alpa</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center col-span-2 sm:col-span-1">
                            <div class="text-2xl font-black text-slate-200">{{ statBelum }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-100">Belum Diisi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar (Quick Mark & Search) -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <span class="text-xs font-bold text-slate-500 whitespace-nowrap">Tandai Cepat:</span>
                    <button @click="setAll('Hadir')" class="px-3 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                        Semua Hadir
                    </button>
                    <button @click="setAll(null)" class="px-3 py-1.5 text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                        Reset
                    </button>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <input type="text" v-model="searchQuery" placeholder="Cari nama santri / NISN..."
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button @click="submitAbsensi" :disabled="isSaving"
                            class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 whitespace-nowrap flex items-center gap-2 disabled:opacity-50">
                        <svg v-if="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span v-if="isSaving">Menyimpan...</span>
                        <span v-else>Simpan Absensi</span>
                    </button>
                </div>
            </div>

            <!-- Student Attendance Table -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Nama Siswa / Santri</th>
                                <th class="py-3.5 px-4">NISN</th>
                                <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Keterangan (Opsional)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="(student, index) in filteredStudents" :key="student.id" class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400 text-xs">{{ index + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 block text-xs md:text-sm">{{ student.name }}</span>
                                            <span class="text-[10px] text-slate-400 font-semibold">Jenis Kelamin: {{ student.gender === 'P' ? 'Perempuan' : 'Laki-laki' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ student.nisn || '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex justify-center gap-1.5">
                                        <button v-for="btn in statusBtns" :key="btn.value"
                                                @click="setStatus(student, btn.value)"
                                                :class="[
                                                    student.status === btn.value ? btn.active : btn.inactive,
                                                    'px-2.5 py-1 text-xs font-bold rounded-lg border transition'
                                                ]">
                                            {{ btn.label }}
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <input v-if="student.status && student.status !== 'Hadir'" 
                                           type="text" v-model="student.leave_reason" 
                                           placeholder="Alasan izin / sakit..." 
                                           class="w-full text-xs px-2.5 py-1 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                                    <span v-else class="text-xs text-slate-300 italic">-</span>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data siswa ditemukan untuk kelas ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Save Action -->
                <div class="p-4 bg-slate-50/80 border-t border-slate-200/80 flex justify-between items-center">
                    <p class="text-xs text-slate-500 font-medium">
                        Total santri: <strong class="text-slate-800">{{ studentList.length }}</strong>
                    </p>
                    <button @click="submitAbsensi" :disabled="isSaving"
                            class="px-6 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 flex items-center gap-2 disabled:opacity-50">
                        <svg v-if="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span v-if="isSaving">Menyimpan...</span>
                        <span v-else>Simpan & Kirim Laporan</span>
                    </button>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
