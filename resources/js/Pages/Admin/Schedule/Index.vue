<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    schedules: Object,
    student_classes: Array,
    current_class_id: [String, Number],
});

const isModalOpen = ref(false);
const currentId = ref(null);
const selectedClass = ref(props.current_class_id || '');

const form = useForm({
    day: '',
    time_start: '',
    time_end: '',
    room: '',
});

const days = [
    { value: 1, label: 'Senin' },
    { value: 2, label: 'Selasa' },
    { value: 3, label: 'Rabu' },
    { value: 4, label: 'Kamis' },
    { value: 5, label: 'Jumat' },
    { value: 6, label: 'Sabtu' },
];

const openModal = (schedule) => {
    isModalOpen.value = true;
    currentId.value = schedule.id;
    form.day = schedule.day || '';
    form.time_start = schedule.time_start ? schedule.time_start.substring(0, 5) : '';
    form.time_end = schedule.time_end ? schedule.time_end.substring(0, 5) : '';
    form.room = schedule.room || '';
};

const closeModal = () => {
    isModalOpen.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    form.put(route('admin.schedules.update', currentId.value), {
        onSuccess: () => closeModal(),
    });
};

const isGenerating = ref(false);
const autoGenerate = () => {
    if (confirm('Apakah Anda yakin ingin melakukan generate jadwal otomatis? Jadwal yang belum diatur akan diisi secara acak dengan menghindari bentrokan guru dan kelas.')) {
        isGenerating.value = true;
        router.post(route('admin.schedules.generate'), {}, {
            preserveState: true,
            onFinish: () => {
                isGenerating.value = false;
            }
        });
    }
};

watch(selectedClass, (newValue) => {
    router.get(route('admin.schedules.index'), { class_id: newValue }, { preserveState: true, replace: true });
});

const getDayName = (dayNumber) => {
    const day = days.find(d => d.value == dayNumber);
    return day ? day.label : '-';
};
</script>

<template>
    <Head title="Jadwal Pelajaran" />
    <AdminLayout>
        <template #header-title>
            <h2 class="text-xl font-bold text-white">Jadwal Pelajaran</h2>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-400">Pengaturan jadwal hari dan jam untuk setiap penugasan mengajar</p>
        </template>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Toolbar -->
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-center bg-slate-900/50 gap-4">
                <div class="text-white font-bold">Daftar Jadwal (Berdasarkan Pembagian Tugas)</div>
                
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-400">Filter Kelas:</label>
                    <select v-model="selectedClass" class="bg-slate-950 border border-white/10 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-rose-500/50 outline-none">
                        <option value="">Semua Kelas</option>
                        <option v-for="c in student_classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <button @click="autoGenerate" :disabled="isGenerating" class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-indigo-500/20 ml-2">
                        <svg v-if="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        {{ isGenerating ? 'Memproses...' : 'Generate Otomatis' }}
                    </button>
                </div>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.success" class="mx-6 mt-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ $page.props.flash.success }}
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-4">Kelas</th>
                            <th scope="col" class="px-6 py-4">Mata Pelajaran</th>
                            <th scope="col" class="px-6 py-4">Guru Pengajar</th>
                            <th scope="col" class="px-6 py-4">Hari</th>
                            <th scope="col" class="px-6 py-4">Jam</th>
                            <th scope="col" class="px-6 py-4">Ruangan</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in schedules.data" :key="schedule.id" class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 font-bold">{{ schedule.student_class?.name || '-' }}</td>
                            <td class="px-6 py-4 text-blue-400">{{ schedule.subject?.name || '-' }}</td>
                            <td class="px-6 py-4">{{ schedule.teacher?.user?.name || '-' }}</td>
                            <td class="px-6 py-4">
                                <span v-if="schedule.day" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-full text-xs font-bold">
                                    {{ getDayName(schedule.day) }}
                                </span>
                                <span v-else class="text-slate-500 italic text-xs">Belum diatur</span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="schedule.time_start && schedule.time_end" class="text-xs font-mono bg-slate-900 px-2 py-1 rounded border border-white/10">
                                    {{ schedule.time_start.substring(0, 5) }} - {{ schedule.time_end.substring(0, 5) }}
                                </span>
                                <span v-else class="text-slate-500 italic text-xs">Belum diatur</span>
                            </td>
                            <td class="px-6 py-4">{{ schedule.room || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openModal(schedule)" class="px-3 py-1.5 bg-blue-500/10 hover:bg-blue-500 hover:text-white text-blue-400 text-xs font-bold rounded-lg transition border border-blue-500/20">
                                    Atur Jadwal
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!schedules.data.length">
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data penugasan. Silakan isi Pembagian Tugas terlebih dahulu.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ schedules.from || 0 }} sampai {{ schedules.to || 0 }} dari {{ schedules.total }} data</span>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-slate-900 border border-white/10 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Atur Waktu & Hari</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Hari</label>
                            <select v-model="form.day" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 outline-none transition" required>
                                <option value="" disabled>Pilih Hari</option>
                                <option v-for="d in days" :key="d.value" :value="d.value">{{ d.label }}</option>
                            </select>
                            <div v-if="form.errors.day" class="text-rose-500 text-xs mt-1">{{ form.errors.day }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Jam Mulai</label>
                                <input v-model="form.time_start" type="time" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 outline-none transition" required />
                                <div v-if="form.errors.time_start" class="text-rose-500 text-xs mt-1">{{ form.errors.time_start }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Jam Selesai</label>
                                <input v-model="form.time_end" type="time" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 outline-none transition" required />
                                <div v-if="form.errors.time_end" class="text-rose-500 text-xs mt-1">{{ form.errors.time_end }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Ruangan (Opsional)</label>
                            <input v-model="form.room" type="text" placeholder="Contoh: Lab Komputer" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-rose-500/50 outline-none transition" />
                            <div v-if="form.errors.room" class="text-rose-500 text-xs mt-1">{{ form.errors.room }}</div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-white/10 flex justify-end gap-3 bg-slate-800/50 rounded-b-2xl">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-bold text-slate-300 hover:text-white transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-blue-500/20 disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Jadwal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
