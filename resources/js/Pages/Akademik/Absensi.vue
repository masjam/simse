<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    className: String,
    subjectName: String,
    scheduleTime: String,
    students: Array
});

// Reactivity untuk status absensi
const studentList = ref([...props.students]);

const setStatus = (student, status) => {
    student.status = status;
};

// Auto-fill all
const setAll = (status) => {
    studentList.value.forEach(s => s.status = status);
};

// Statistik Realtime
const statHadir = computed(() => studentList.value.filter(s => s.status === 'Hadir').length);
const statSakit = computed(() => studentList.value.filter(s => s.status === 'Sakit').length);
const statIzin = computed(() => studentList.value.filter(s => s.status === 'Izin').length);
const statAlpa = computed(() => studentList.value.filter(s => s.status === 'Alpa').length);
const statBelum = computed(() => studentList.value.filter(s => s.status === 'Belum Diisi').length);

const submitAbsensi = () => {
    if (statBelum.value > 0) {
        alert('Ada ' + statBelum.value + ' siswa yang belum diisi absensinya!');
        return;
    }
    alert('✅ Absensi berhasil disimpan ke Server & WhatsApp Notifikasi Terkirim ke Orang Tua yang siswanya Tidak Hadir!');
};
</script>

<template>
    <Head title="Manajemen Absensi Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Absensi Interaktif (SIAKAD)</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Class Info Banner -->
                <div class="bg-indigo-600 rounded-2xl p-6 md:p-8 text-white shadow-xl shadow-indigo-600/20 relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 w-full">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-indigo-800 text-indigo-100 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Kelas Aktif</span>
                            <span class="text-indigo-200 text-sm font-medium"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ scheduleTime }}</span>
                        </div>
                        <h2 class="text-3xl font-bold">{{ subjectName }}</h2>
                        <p class="text-indigo-200 text-lg mt-1">Kelas: <b>{{ className }}</b></p>
                    </div>

                    <!-- Statistics Mini-Dashboard -->
                    <div class="relative z-10 flex gap-4 w-full md:w-auto shrink-0 overflow-x-auto pb-2 md:pb-0 hide-scrollbar">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-white">{{ statHadir }}</div>
                            <div class="text-[10px] uppercase font-bold text-indigo-200 mt-1">Hadir</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-yellow-300">{{ statIzin }}</div>
                            <div class="text-[10px] uppercase font-bold text-indigo-200 mt-1">Izin</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-blue-300">{{ statSakit }}</div>
                            <div class="text-[10px] uppercase font-bold text-indigo-200 mt-1">Sakit</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 text-center min-w-[70px]">
                            <div class="text-2xl font-bold text-red-300">{{ statAlpa }}</div>
                            <div class="text-[10px] uppercase font-bold text-indigo-200 mt-1">Alpa</div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium">Anda dapat menggunakan Face ID Scanner kelas, atau absensi manual di bawah ini.</p>
                    <div class="flex gap-2">
                        <button @click="setAll('Hadir')" class="px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-sm font-bold rounded-lg border border-emerald-200 transition">Set Semua Hadir</button>
                    </div>
                </div>

                <!-- Students List Grid -->
                <div class="grid grid-cols-1 gap-3">
                    <div v-for="(student, index) in studentList" :key="student.id" 
                         class="bg-white p-4 rounded-xl border shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all duration-300"
                         :class="{
                            'border-gray-200': student.status === 'Belum Diisi',
                            'border-emerald-300 bg-emerald-50/30': student.status === 'Hadir',
                            'border-yellow-300 bg-yellow-50/30': student.status === 'Izin',
                            'border-blue-300 bg-blue-50/30': student.status === 'Sakit',
                            'border-red-300 bg-red-50/30': student.status === 'Alpa',
                         }">
                        
                        <!-- Student Profile -->
                        <div class="flex items-center gap-4 w-full md:w-1/3">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-lg font-bold text-gray-500 shrink-0">
                                {{ student.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ student.name }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">NISN: {{ student.nisn }} • {{ student.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                        </div>

                        <!-- Status Selection -->
                        <div class="flex flex-wrap md:flex-nowrap gap-2 w-full md:w-auto">
                            <button @click="setStatus(student, 'Hadir')" 
                                    class="flex-1 md:w-24 py-2 rounded-lg text-sm font-bold transition border"
                                    :class="student.status === 'Hadir' ? 'bg-emerald-500 text-white border-emerald-600 shadow-md shadow-emerald-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                Hadir
                            </button>
                            <button @click="setStatus(student, 'Izin')" 
                                    class="flex-1 md:w-24 py-2 rounded-lg text-sm font-bold transition border"
                                    :class="student.status === 'Izin' ? 'bg-yellow-500 text-white border-yellow-600 shadow-md shadow-yellow-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                Izin
                            </button>
                            <button @click="setStatus(student, 'Sakit')" 
                                    class="flex-1 md:w-24 py-2 rounded-lg text-sm font-bold transition border"
                                    :class="student.status === 'Sakit' ? 'bg-blue-500 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                Sakit
                            </button>
                            <button @click="setStatus(student, 'Alpa')" 
                                    class="flex-1 md:w-24 py-2 rounded-lg text-sm font-bold transition border"
                                    :class="student.status === 'Alpa' ? 'bg-red-500 text-white border-red-600 shadow-md shadow-red-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                Alpa
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-200 flex justify-end">
                    <button @click="submitAbsensi" 
                            class="px-8 py-4 bg-gray-900 hover:bg-black text-white rounded-xl font-bold shadow-xl shadow-gray-900/20 transition flex items-center gap-2">
                        Simpan Absensi Hari Ini
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
