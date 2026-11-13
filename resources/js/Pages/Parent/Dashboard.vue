<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ParentLayout from '@/Layouts/ParentLayout.vue';

const props = defineProps({
    children: {
        type: Array,
        default: () => [],
    },
    activeChild: {
        type: Object,
        required: true,
    },
    activeTab: {
        type: String,
        default: 'akademik',
    },
    overview: {
        type: Object,
        required: true,
    },
    akademik: {
        type: Object,
        required: true,
    },
    presensi: {
        type: Object,
        required: true,
    },
    keuangan: {
        type: Object,
        required: true,
    },
    perilaku: {
        type: Object,
        required: true,
    },
    prestasi: {
        type: Array,
        default: () => [],
    },
});

const currentTab = ref(props.activeTab || 'akademik');
const showAchievementForm = ref(false);

const achievementForm = useForm({
    student_id: props.activeChild?.id || '',
    name: '',
    category: 'Akademik',
    level: 'Kabupaten/Kota',
    year: new Date().getFullYear(),
    organizer: '',
    achievement_date: '',
    description: '',
    certificate: null,
});

const walletLimitForm = useForm({
    student_id: props.activeChild?.id || '',
    daily_limit: props.keuangan?.wallet?.daily_limit ?? '',
});

const submitWalletLimit = () => {
    walletLimitForm.student_id = props.activeChild?.id || '';
    walletLimitForm.post(route('ortu.wallet.limit'), {
        preserveScroll: true,
    });
};

watch(
    () => props.activeChild?.id,
    (id) => {
        if (id) {
            achievementForm.student_id = id;
            walletLimitForm.student_id = id;
            walletLimitForm.daily_limit = props.keuangan?.wallet?.daily_limit ?? '';
        }
    }
);

const submitAchievement = () => {
    achievementForm.student_id = props.activeChild.id;
    achievementForm.post(route('ortu.achievements.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            achievementForm.reset('name', 'organizer', 'achievement_date', 'description', 'certificate');
            showAchievementForm.value = false;
        },
    });
};

watch(
    () => props.activeTab,
    (value) => {
        if (value) {
            currentTab.value = value;
        }
    }
);

const switchChild = (studentId) => {
    if (!studentId || studentId === props.activeChild?.id) {
        return;
    }

    router.post(route('ortu.switch-child'), { student_id: studentId }, { preserveScroll: true });
};

const formatRupiah = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount || 0);
};

const statusBadge = (status) => {
    const map = {
        Hadir: 'bg-emerald-100 text-emerald-800',
        Terlambat: 'bg-amber-100 text-amber-800',
        Izin: 'bg-sky-100 text-sky-800',
        Sakit: 'bg-indigo-100 text-indigo-800',
        Alpa: 'bg-rose-100 text-rose-800',
        Lunas: 'bg-emerald-100 text-emerald-800',
        Berhasil: 'bg-emerald-100 text-emerald-800',
        Disetujui: 'bg-emerald-100 text-emerald-800',
        'Menunggu Verifikasi': 'bg-amber-100 text-amber-800',
        Ditolak: 'bg-rose-100 text-rose-800',
        'Belum Lunas': 'bg-rose-100 text-rose-800',
        Aman: 'bg-emerald-100 text-emerald-800',
        Pembinaan: 'bg-sky-100 text-sky-800',
        'SP 1': 'bg-amber-100 text-amber-800',
        'SP 2': 'bg-orange-100 text-orange-800',
        'SP 3': 'bg-rose-100 text-rose-800',
    };

    return map[status] || 'bg-slate-100 text-slate-700';
};
</script>

<template>
    <Head :title="`Portal Orang Tua - ${activeChild.name}`" />

    <ParentLayout
        :children="children"
        :active-child="activeChild"
        :active-tab="currentTab"
        @select-tab="currentTab = $event"
    >
        <div class="space-y-6">
            <!-- Child Hero Banner & Quick Switcher -->
            <section class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">
                                Kelas {{ activeChild.class_name }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium">
                                {{ activeChild.academic_year }} • Semester {{ activeChild.semester }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">
                            {{ activeChild.name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            NISN: {{ activeChild.nisn }} • Wali Kelas: {{ activeChild.wali_kelas }}
                        </p>
                    </div>

                    <div v-if="children.length > 1" class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500">Pilih Ananda:</span>
                        <button
                            v-for="child in children"
                            :key="child.id"
                            type="button"
                            @click="switchChild(child.id)"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border transition-all"
                            :class="activeChild.id === child.id
                                ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                                : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                        >
                            {{ child.name }} ({{ child.class_name }})
                        </button>
                    </div>
                </div>

                <!-- 4 KPI Overview Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-5">
                    <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200/80 p-4">
                        <p class="text-xs font-bold text-emerald-700">Rata-rata Nilai</p>
                        <p class="text-2xl font-black text-emerald-950 mt-1">{{ overview.average_score }}</p>
                    </div>
                    <div class="rounded-2xl bg-sky-50/70 border border-sky-200/80 p-4">
                        <p class="text-xs font-bold text-sky-700">Kehadiran</p>
                        <p class="text-2xl font-black text-sky-950 mt-1">{{ overview.attendance_pct }}%</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50/70 border border-amber-200/80 p-4">
                        <p class="text-xs font-bold text-amber-700">Tagihan Belum Lunas</p>
                        <p class="text-lg sm:text-xl font-black text-amber-950 mt-1">
                            {{ formatRupiah(overview.unpaid_bills_total) }}
                        </p>
                        <p class="text-[11px] text-amber-700 mt-0.5">{{ overview.unpaid_bills_count }} tagihan</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs font-bold text-slate-600">Status Perilaku / BK</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-2xl font-black text-slate-900">{{ overview.violation_points }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold" :class="statusBadge(overview.sp_status)">
                                {{ overview.sp_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 1: AKADEMIK -->
            <section v-if="currentTab === 'akademik'" class="space-y-6">
                <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Ringkasan Nilai & e-Raport</h2>

                    <div v-if="akademik.raportSubjects.length === 0" class="p-6 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                        Belum ada data nilai raport untuk ananda pada semester ini.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
                                    <th class="py-3 pr-4">Mata Pelajaran</th>
                                    <th class="py-3 px-2 text-center">Tugas</th>
                                    <th class="py-3 px-2 text-center">Kuis</th>
                                    <th class="py-3 px-2 text-center">UTS</th>
                                    <th class="py-3 px-2 text-center">UAS</th>
                                    <th class="py-3 px-2 text-center">Akhir</th>
                                    <th class="py-3 pl-2 text-center">Predikat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="subject in akademik.raportSubjects" :key="subject.subject_id">
                                    <td class="py-3 pr-4">
                                        <p class="font-bold text-slate-900">{{ subject.subject_name }}</p>
                                        <p class="text-xs text-slate-500">{{ subject.teacher_name }}</p>
                                    </td>
                                    <td class="py-3 px-2 text-center">{{ subject.tugas_avg ?? '-' }}</td>
                                    <td class="py-3 px-2 text-center">{{ subject.kuis_avg ?? '-' }}</td>
                                    <td class="py-3 px-2 text-center">{{ subject.uts ?? '-' }}</td>
                                    <td class="py-3 px-2 text-center">{{ subject.uas ?? '-' }}</td>
                                    <td class="py-3 px-2 text-center font-extrabold text-emerald-700">{{ subject.nilai_akhir }}</td>
                                    <td class="py-3 pl-2 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                            {{ subject.predikat }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Jadwal Pelajaran -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Jadwal Pelajaran Kelas</h2>
                        <div v-if="akademik.schedules.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                            Jadwal pelajaran belum tersedia.
                        </div>
                        <div v-else class="space-y-2.5">
                            <div
                                v-for="item in akademik.schedules"
                                :key="item.id"
                                class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                            >
                                <div>
                                    <span class="text-[11px] font-bold text-amber-700 uppercase">{{ item.day_name }}</span>
                                    <p class="font-bold text-sm text-slate-900">{{ item.subject }}</p>
                                    <p class="text-xs text-slate-500">{{ item.teacher }} • Ruang {{ item.room }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-extrabold text-slate-800">{{ item.time_start }} - {{ item.time_end }}</p>
                                    <p class="text-[11px] text-slate-500">{{ item.jpl }} JPL</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Jurnal / Materi Mingguan -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Ringkasan Materi Mingguan</h2>
                        <div v-if="akademik.weeklyJournals.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                            Belum ada jurnal materi mingguan dari guru kelas.
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="week in akademik.weeklyJournals" :key="week.week_key" class="space-y-2">
                                <p class="text-xs font-extrabold text-amber-700">{{ week.week_label }}</p>
                                <div
                                    v-for="entry in week.entries"
                                    :key="entry.id"
                                    class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-100"
                                >
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span class="font-bold text-amber-900">{{ entry.subject_name }}</span>
                                        <span class="text-slate-500">{{ entry.date }}</span>
                                    </div>
                                    <p class="font-bold text-sm text-slate-900 mt-1">{{ entry.topic }}</p>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ entry.summary }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengajuan & Daftar Prestasi Lomba Ananda -->
                <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Prestasi & Hasil Lomba Ananda</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Ajukan sertifikat hasil lomba ananda untuk diverifikasi Guru BK / TU / Bidang Lomba dan dicantumkan pada e-Raport.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="showAchievementForm = !showAchievementForm"
                            class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-colors shrink-0"
                        >
                            {{ showAchievementForm ? 'Tutup Form' : '+ Ajukan Prestasi Lomba' }}
                        </button>
                    </div>

                    <form
                        v-if="showAchievementForm"
                        @submit.prevent="submitAchievement"
                        class="mb-6 p-4 sm:p-5 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-4"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Prestasi / Juara Lomba *</label>
                                <input
                                    v-model="achievementForm.name"
                                    type="text"
                                    required
                                    placeholder="Contoh: Juara 1 Lomba Tahfidz Juz 30"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                                <p v-if="achievementForm.errors.name" class="text-xs text-rose-600 mt-1">{{ achievementForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Lomba *</label>
                                <select
                                    v-model="achievementForm.category"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                >
                                    <option value="Akademik">Akademik</option>
                                    <option value="Keagamaan">Keagamaan</option>
                                    <option value="Olahraga">Olahraga</option>
                                    <option value="Seni & Budaya">Seni & Budaya</option>
                                    <option value="Sains & Teknologi">Sains & Teknologi</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <p v-if="achievementForm.errors.category" class="text-xs text-rose-600 mt-1">{{ achievementForm.errors.category }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Kejuaraan *</label>
                                <select
                                    v-model="achievementForm.level"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                >
                                    <option value="Sekolah">Sekolah</option>
                                    <option value="Kecamatan">Kecamatan</option>
                                    <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                    <option value="Provinsi">Provinsi</option>
                                    <option value="Nasional">Nasional</option>
                                    <option value="Internasional">Internasional</option>
                                </select>
                                <p v-if="achievementForm.errors.level" class="text-xs text-rose-600 mt-1">{{ achievementForm.errors.level }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun *</label>
                                    <input
                                        v-model="achievementForm.year"
                                        type="number"
                                        min="2015"
                                        max="2035"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                    />
                                    <p v-if="achievementForm.errors.year" class="text-xs text-rose-600 mt-1">{{ achievementForm.errors.year }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lomba</label>
                                    <input
                                        v-model="achievementForm.achievement_date"
                                        type="date"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Penyelenggara</label>
                                <input
                                    v-model="achievementForm.organizer"
                                    type="text"
                                    placeholder="Contoh: Dinas Pendidikan Kab. Gunungkidul"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Piagam / Sertifikat (PDF/JPG/PNG, maks 3 MB)</label>
                                <input
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @change="achievementForm.certificate = $event.target.files[0] || null"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-700"
                                />
                                <p v-if="achievementForm.errors.certificate" class="text-xs text-rose-600 mt-1">{{ achievementForm.errors.certificate }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                            <textarea
                                v-model="achievementForm.description"
                                rows="2"
                                placeholder="Tuliskan detail singkat lomba atau penghargaan..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="showAchievementForm = false"
                                class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="achievementForm.processing"
                                class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-colors disabled:opacity-50"
                            >
                                {{ achievementForm.processing ? 'Mengirim...' : 'Kirim Pengajuan Prestasi' }}
                            </button>
                        </div>
                    </form>

                    <div v-if="prestasi.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                        Belum ada pengajuan atau catatan prestasi lomba untuk ananda.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="item in prestasi"
                            :key="item.id"
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-bold text-sm text-slate-900">{{ item.name }}</p>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold" :class="statusBadge(item.status)">
                                        {{ item.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ item.category }} • Tingkat {{ item.level }} • Tahun {{ item.year }}
                                    <span v-if="item.organizer && item.organizer !== '-'"> • {{ item.organizer }}</span>
                                </p>
                                <p v-if="item.description" class="text-xs text-slate-600 mt-1">{{ item.description }}</p>
                                <p v-if="item.verification_notes" class="text-xs text-slate-700 mt-1.5 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                                    <span class="font-bold">Catatan Verifikator:</span> {{ item.verification_notes }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a
                                    v-if="item.has_certificate"
                                    :href="route('ortu.achievements.certificate', item.id)"
                                    target="_blank"
                                    class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100"
                                >
                                    Lihat Sertifikat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 2: PRESENSI -->
            <section v-if="currentTab === 'presensi'" class="space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
                        <p class="text-xs font-bold text-emerald-700">Hadir</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ presensi.summary.hadir }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
                        <p class="text-xs font-bold text-amber-700">Terlambat</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ presensi.summary.terlambat }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
                        <p class="text-xs font-bold text-sky-700">Izin</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ presensi.summary.izin }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
                        <p class="text-xs font-bold text-indigo-700">Sakit</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ presensi.summary.sakit }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center col-span-2 sm:col-span-1">
                        <p class="text-xs font-bold text-rose-700">Alpa</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ presensi.summary.alpa }}</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Riwayat Kehadiran Ananda</h2>
                    <div v-if="presensi.records.length === 0" class="p-6 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                        Belum ada catatan presensi.
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="item in presensi.records"
                            :key="item.id"
                            class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p class="font-bold text-sm text-slate-900">{{ item.day_name }}, {{ item.date }}</p>
                                <p class="text-xs text-slate-500">
                                    Masuk: {{ item.time_in }} • Pulang: {{ item.time_out }}
                                    <span v-if="item.leave_reason"> • Ket: {{ item.leave_reason }}</span>
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0" :class="statusBadge(item.status)">
                                {{ item.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 3: KEUANGAN SPP -->
            <section v-if="currentTab === 'keuangan'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                        <p class="text-xs font-bold text-rose-700">Total Sisa Tagihan</p>
                        <p class="text-xl font-black text-rose-950 mt-1">{{ formatRupiah(keuangan.total_due_unpaid) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                        <p class="text-xs font-bold text-emerald-700">Total Sudah Dibayar</p>
                        <p class="text-xl font-black text-emerald-950 mt-1">{{ formatRupiah(keuangan.total_paid_verified) }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
                        <p class="text-xs font-bold text-amber-700">Menunggu Verifikasi</p>
                        <p class="text-xl font-black text-amber-950 mt-1">{{ keuangan.pending_verification_count }} transaksi</p>
                    </div>
                </div>

                <!-- Dompet Digital & Batas Pengeluaran Harian Kantin -->
                <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Dompet Digital &amp; Kontrol Jajan Kantin</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Saldo ananda: <strong class="text-emerald-700">{{ formatRupiah(keuangan.wallet?.balance || 0) }}</strong>
                            &middot; Terpakai hari ini: <strong class="text-slate-800">{{ formatRupiah(keuangan.wallet?.today_spent || 0) }}</strong>
                        </p>
                    </div>
                    <form class="flex flex-wrap items-center gap-2" @submit.prevent="submitWalletLimit">
                        <input
                            v-model="walletLimitForm.daily_limit"
                            type="number"
                            min="0"
                            step="1000"
                            placeholder="Kosongkan / 0 = tanpa batas"
                            class="w-52 rounded-xl border-slate-300 text-xs font-semibold"
                        />
                        <button
                            type="submit"
                            :disabled="walletLimitForm.processing"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition"
                        >
                            Simpan Batas Harian
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Daftar Tagihan SPP -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Daftar Tagihan SPP & Sekolah</h2>
                        <div v-if="keuangan.bills.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                            Tidak ada tagihan untuk ananda.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="bill in keuangan.bills"
                                :key="bill.id"
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="font-bold text-sm text-slate-900">{{ bill.title }}</p>
                                    <p class="text-xs text-slate-500">Periode: {{ bill.period }}</p>
                                    <p class="text-xs text-slate-600 mt-1">
                                        Tagihan: {{ formatRupiah(bill.amount_due) }}
                                        <span v-if="bill.remaining > 0" class="text-rose-700 font-semibold">
                                            (Sisa: {{ formatRupiah(bill.remaining) }})
                                        </span>
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0" :class="statusBadge(bill.status)">
                                    {{ bill.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Pembayaran -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Riwayat Pembayaran</h2>
                        <div v-if="keuangan.payments.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                            Belum ada riwayat pembayaran tercatat.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="pay in keuangan.payments"
                                :key="pay.id"
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="font-bold text-sm text-slate-900">{{ pay.bill_title }} ({{ pay.bill_period }})</p>
                                    <p class="text-xs text-slate-500">{{ pay.payment_date }} • {{ pay.payment_method }} • {{ pay.cashier_name }}</p>
                                    <p class="text-sm font-extrabold text-emerald-700 mt-1">{{ formatRupiah(pay.amount_paid) }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0" :class="statusBadge(pay.status)">
                                    {{ pay.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 4: PERILAKU & BK -->
            <section v-if="currentTab === 'perilaku'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Catatan Pelanggaran -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Catatan Kedisiplinan</h2>
                            <span class="px-3 py-1 rounded-full text-xs font-bold" :class="statusBadge(perilaku.sp_status)">
                                {{ perilaku.total_points }} Poin ({{ perilaku.sp_status }})
                            </span>
                        </div>

                        <div v-if="perilaku.violations.length === 0" class="p-5 rounded-2xl bg-emerald-50 text-center text-sm text-emerald-800 font-medium">
                            Alhamdulillah, tidak ada catatan pelanggaran tata tertib.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="v in perilaku.violations"
                                :key="v.id"
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-100"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-sm text-slate-900">{{ v.title }}</p>
                                    <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">
                                        +{{ v.points }} poin
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ v.date }} • {{ v.category }} • Dicatat oleh {{ v.teacher_name }}</p>
                                <p v-if="v.description" class="text-xs text-slate-700 mt-1.5">{{ v.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sesi Konseling BK -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-4">Riwayat Konseling & Bimbingan</h2>
                        <div v-if="perilaku.counseling_sessions.length === 0" class="p-5 rounded-2xl bg-slate-50 text-center text-sm text-slate-500">
                            Belum ada sesi konseling yang tercatat.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="session in perilaku.counseling_sessions"
                                :key="session.id"
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-100"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-sm text-slate-900">{{ session.type }}</p>
                                    <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 text-xs font-bold">
                                        {{ session.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ session.date }} • {{ session.teacher_name }}</p>
                                <p class="text-xs text-slate-700 mt-2"><span class="font-bold">Topik:</span> {{ session.problem }}</p>
                                <p v-if="session.solution" class="text-xs text-emerald-800 mt-1">
                                    <span class="font-bold">Tindak Lanjut:</span> {{ session.solution }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </ParentLayout>
</template>
