<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    studentUser: Object,
    tingkats: Array,
    studentClasses: Array,
});

const student = props.studentUser.student || {};

const form = useForm({
    name: props.studentUser.name || '',
    student_class_id: student.student_class_id || '',
    gender: student.gender || '',
    nisn: student.nisn || '',
    kewarganegaraan: student.kewarganegaraan || 'ID',
    nik: student.nik || '',
    no_kk: student.no_kk || '',
    tempat_lahir: student.tempat_lahir || '',
    tanggal_lahir: student.tanggal_lahir || '',
    no_registrasi_akta_lahir: student.no_registrasi_akta_lahir || '',
    berkebutuhan_khusus: student.berkebutuhan_khusus || '',
    agama: student.agama || 'Islam',
    alamat_jalan: student.alamat_jalan || '',
    rt: student.rt || '',
    rw: student.rw || '',
    dusun: student.dusun || '',
    desa_kelurahan: student.desa_kelurahan || '',
    kode_pos: student.kode_pos || '',
    lintang: student.lintang || '',
    bujur: student.bujur || '',
    tempat_tinggal: student.tempat_tinggal || 'Bersama orang tua',
    moda_transportasi: student.moda_transportasi || 'Sepeda motor',
    anak_ke: student.anak_ke || '',

    nama_ayah: student.nama_ayah || '',
    nik_ayah: student.nik_ayah || '',
    tahun_lahir_ayah: student.tahun_lahir_ayah || '',
    pendidikan_ayah: student.pendidikan_ayah || 'SMA / sederajat',
    pekerjaan_ayah: student.pekerjaan_ayah || '',
    berkebutuhan_khusus_ayah: student.berkebutuhan_khusus_ayah || '',
    penghasilan_ayah: student.penghasilan_ayah || '',

    status_hidup_ibu: student.status_hidup_ibu || 'Hidup',
    nama_ibu: student.nama_ibu || '',
    nik_ibu: student.nik_ibu || '',
    tahun_lahir_ibu: student.tahun_lahir_ibu || '',
    pendidikan_ibu: student.pendidikan_ibu || 'SMA / sederajat',
    pekerjaan_ibu: student.pekerjaan_ibu || '',
    berkebutuhan_khusus_ibu: student.berkebutuhan_khusus_ibu || '',
    penghasilan_ibu: student.penghasilan_ibu || '',

    mempunyai_wali: student.mempunyai_wali || false,
    nama_wali: student.nama_wali || '',
    nik_wali: student.nik_wali || '',
    tahun_lahir_wali: student.tahun_lahir_wali || '',
    pendidikan_wali: student.pendidikan_wali || '',
    pekerjaan_wali: student.pekerjaan_wali || '',
    penghasilan_wali: student.penghasilan_wali || '',

    no_telepon_rumah: student.no_telepon_rumah || '',
    no_hp: student.no_hp || '',
});

const submitForm = () => {
    form.put(route('admin.students.update', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => alert('Data berhasil disimpan!'),
    });
};

const achForm = useForm({
    name: '',
    level: 'Sekolah',
    year: new Date().getFullYear(),
    description: '',
});

const submitAchievement = () => {
    achForm.post(route('admin.students.achievements.store', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Prestasi berhasil ditambahkan!');
            achForm.reset();
        },
    });
};

const perForm = useForm({
    tinggi_badan: '',
    berat_badan: '',
    lingkar_kepala: '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const submitPeriodic = () => {
    perForm.post(route('admin.students.periodics.store', props.studentUser.id), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Data periodik berhasil ditambahkan!');
            perForm.reset();
        },
    });
};

const activeTab = ref('pribadi');

</script>

<template>
    <Head :title="`Edit Peserta Didik: ${studentUser.name}`" />
    <AdminLayout>
        <template #header-title>
            <h2 class="text-xl font-bold text-white uppercase tracking-wider">
                Edit Peserta Didik : {{ studentUser.name }}
            </h2>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-400">Lengkapi data pribadi, orang tua, prestasi, dan data periodik siswa</p>
        </template>

        <div class="mb-6 flex flex-wrap gap-2 items-center justify-between">
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin flex-1">
                <button @click="activeTab = 'pribadi'" :class="['px-6 py-3 font-bold rounded-xl text-sm transition whitespace-nowrap', activeTab === 'pribadi' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white']">
                    Data Pribadi
                </button>
                <button @click="activeTab = 'ortu'" :class="['px-6 py-3 font-bold rounded-xl text-sm transition whitespace-nowrap', activeTab === 'ortu' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white']">
                    Data Ortu & Alamat
                </button>
                <button @click="activeTab = 'prestasi'" :class="['px-6 py-3 font-bold rounded-xl text-sm transition whitespace-nowrap', activeTab === 'prestasi' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white']">
                    Prestasi Siswa
                </button>
                <button @click="activeTab = 'periodik'" :class="['px-6 py-3 font-bold rounded-xl text-sm transition whitespace-nowrap', activeTab === 'periodik' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white']">
                    Data Periodik
                </button>
            </div>
            <a :href="route('admin.students.print', studentUser.id)" target="_blank" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-emerald-500/20 mb-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Profil Siswa
            </a>
        </div>

        <!-- TAB: DATA PRIBADI -->
        <div v-show="activeTab === 'pribadi'" class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 shadow-2xl space-y-8">
            <form @submit.prevent="submitForm" class="space-y-8">
                
                <!-- Data Pribadi -->
                <fieldset class="border border-white/10 rounded-xl p-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Data Pribadi
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama: <span class="text-rose-500">*</span></label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Kelas / Rombel: <span class="text-rose-500">*</span></label>
                            <select v-model="form.student_class_id" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required>
                                <option v-for="c in studentClasses" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Jenis Kelamin: <span class="text-rose-500">*</span></label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.gender" value="L" name="gender" class="text-rose-500 focus:ring-rose-500 bg-slate-900 border-white/10" required> Laki-laki</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.gender" value="P" name="gender" class="text-rose-500 focus:ring-rose-500 bg-slate-900 border-white/10" required> Perempuan</label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">NISN:</label>
                            <input v-model="form.nisn" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Kewarganegaraan: <span class="text-rose-500">*</span></label>
                            <input v-model="form.kewarganegaraan" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">NIK:</label>
                            <input v-model="form.nik" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">No KK:</label>
                            <input v-model="form.no_kk" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tempat Lahir:</label>
                            <input v-model="form.tempat_lahir" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tanggal Lahir:</label>
                            <input v-model="form.tanggal_lahir" type="date" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">No Registrasi Akta Lahir:</label>
                            <input v-model="form.no_registrasi_akta_lahir" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Agama & Kepercayaan: <span class="text-rose-500">*</span></label>
                            <select v-model="form.agama" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                <option>Islam</option><option>Kristen</option><option>Katolik</option><option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Data Pribadi Submit -->
                <div class="pt-6 border-t border-white/10 flex justify-end gap-4">
                    <Link :href="route('admin.students.index')" class="px-6 py-2.5 text-sm font-bold text-slate-300 hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-blue-500/30 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data Pribadi' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB: DATA ORTU & ALAMAT -->
        <div v-show="activeTab === 'ortu'" class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 shadow-2xl space-y-8">
            <form @submit.prevent="submitForm" class="space-y-8">
                
                <!-- Alamat -->
                <fieldset class="border border-white/10 rounded-xl p-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Alamat
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 mb-1">Alamat Jalan: <span class="text-rose-500">*</span></label>
                            <input v-model="form.alamat_jalan" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">RT:</label>
                            <input v-model="form.rt" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">RW:</label>
                            <input v-model="form.rw" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Dusun:</label>
                            <input v-model="form.dusun" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Desa/Kelurahan: <span class="text-rose-500">*</span></label>
                            <input v-model="form.desa_kelurahan" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Kode Pos:</label>
                            <input v-model="form.kode_pos" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Lintang:</label>
                                <input v-model="form.lintang" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Bujur:</label>
                                <input v-model="form.bujur" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Buka Peta Koordinat
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tempat Tinggal: <span class="text-rose-500">*</span></label>
                            <input v-model="form.tempat_tinggal" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Moda Transportasi: <span class="text-rose-500">*</span></label>
                            <input v-model="form.moda_transportasi" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Anak ke-berapa (berdasarkan KK): <span class="text-rose-500">*</span></label>
                            <input v-model="form.anak_ke" type="number" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                    </div>
                </fieldset>

                <!-- Data Ayah -->
                <fieldset class="border border-white/10 rounded-xl p-6 mt-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Data Ayah Kandung
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama ayah:</label>
                            <input v-model="form.nama_ayah" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">NIK Ayah:</label>
                            <input v-model="form.nik_ayah" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tahun lahir ayah:</label>
                            <input v-model="form.tahun_lahir_ayah" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Pendidikan ayah:</label>
                            <select v-model="form.pendidikan_ayah" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                <option>Tidak sekolah</option><option>SD / sederajat</option><option>SMP / sederajat</option><option>SMA / sederajat</option><option>D1-D3</option><option>D4 / S1</option><option>S2 / S3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Pekerjaan ayah: <span class="text-rose-500">*</span></label>
                            <input v-model="form.pekerjaan_ayah" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Penghasilan ayah: <span class="text-rose-500">*</span></label>
                            <select v-model="form.penghasilan_ayah" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                <option>Tidak Berpenghasilan</option><option>Kurang dari Rp1.000.000</option><option>Rp1.000.001 - Rp3.000.000</option><option>Rp3.000.001 - Rp5.000.000</option><option>Lebih dari Rp5.000.000</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Data Ibu -->
                <fieldset class="border border-white/10 rounded-xl p-6 mt-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Data Ibu Kandung
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 mb-1">Status Hidup:</label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.status_hidup_ibu" value="Hidup" name="status_hidup_ibu" class="text-rose-500 bg-slate-900 border-white/10"> Hidup</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.status_hidup_ibu" value="Wafat" name="status_hidup_ibu" class="text-rose-500 bg-slate-900 border-white/10"> Wafat</label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama ibu: <span class="text-rose-500">*</span></label>
                            <input v-model="form.nama_ibu" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">NIK Ibu:</label>
                            <input v-model="form.nik_ibu" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tahun lahir ibu: <span class="text-rose-500">*</span></label>
                            <input v-model="form.tahun_lahir_ibu" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Pendidikan ibu: <span class="text-rose-500">*</span></label>
                            <select v-model="form.pendidikan_ibu" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                <option>Tidak sekolah</option><option>SD / sederajat</option><option>SMP / sederajat</option><option>SMA / sederajat</option><option>D1-D3</option><option>D4 / S1</option><option>S2 / S3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Pekerjaan ibu: <span class="text-rose-500">*</span></label>
                            <input v-model="form.pekerjaan_ibu" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Penghasilan ibu: <span class="text-rose-500">*</span></label>
                            <select v-model="form.penghasilan_ibu" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                <option>Tidak Berpenghasilan</option><option>Kurang dari Rp1.000.000</option><option>Rp1.000.001 - Rp3.000.000</option><option>Rp3.000.001 - Rp5.000.000</option><option>Lebih dari Rp5.000.000</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- Data Wali -->
                <fieldset class="border border-white/10 rounded-xl p-6 mt-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Data Wali
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 mb-1">Mempunyai Wali:</label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.mempunyai_wali" :value="true" class="text-rose-500 bg-slate-900 border-white/10"> Ya</label>
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="radio" v-model="form.mempunyai_wali" :value="false" class="text-rose-500 bg-slate-900 border-white/10"> Tidak</label>
                            </div>
                        </div>
                        <template v-if="form.mempunyai_wali">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Nama wali:</label>
                                <input v-model="form.nama_wali" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">NIK Wali:</label>
                                <input v-model="form.nik_wali" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Tahun lahir wali:</label>
                                <input v-model="form.tahun_lahir_wali" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Pendidikan wali:</label>
                                <select v-model="form.pendidikan_wali" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                    <option>Tidak sekolah</option><option>SD / sederajat</option><option>SMP / sederajat</option><option>SMA / sederajat</option><option>D1-D3</option><option>D4 / S1</option><option>S2 / S3</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Pekerjaan wali:</label>
                                <input v-model="form.pekerjaan_wali" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Penghasilan wali:</label>
                                <select v-model="form.penghasilan_wali" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none">
                                    <option>Tidak Berpenghasilan</option><option>Kurang dari Rp1.000.000</option><option>Rp1.000.001 - Rp3.000.000</option><option>Rp3.000.001 - Rp5.000.000</option><option>Lebih dari Rp5.000.000</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </fieldset>

                <!-- Kontak -->
                <fieldset class="border border-white/10 rounded-xl p-6 mt-6">
                    <legend class="text-sm font-bold text-slate-300 px-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        Kontak
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nomor Telepon Rumah:</label>
                            <input v-model="form.no_telepon_rumah" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nomor HP:</label>
                            <input v-model="form.no_hp" type="text" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                        </div>
                    </div>
                </fieldset>

                <div class="pt-6 border-t border-white/10 flex justify-end gap-4">
                    <Link :href="route('admin.students.index')" class="px-6 py-2.5 text-sm font-bold text-slate-300 hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-blue-500/30 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data Ortu & Alamat' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB: PRESTASI -->
        <div v-show="activeTab === 'prestasi'" class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 shadow-2xl space-y-8">
            <h3 class="text-xl font-bold text-white mb-4">Daftar Prestasi Siswa</h3>
            <div class="overflow-x-auto rounded-xl border border-white/10 mb-8">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th class="px-4 py-3">Nama Prestasi / Lomba</th>
                            <th class="px-4 py-3">Tingkat</th>
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ach in studentUser.student?.achievements" :key="ach.id" class="border-b border-white/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-bold text-white">{{ ach.name }}</td>
                            <td class="px-4 py-3">{{ ach.level }}</td>
                            <td class="px-4 py-3">{{ ach.year }}</td>
                            <td class="px-4 py-3">{{ ach.description || '-' }}</td>
                        </tr>
                        <tr v-if="!studentUser.student?.achievements?.length">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 italic">Belum ada data prestasi.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form @submit.prevent="submitAchievement" class="border border-white/10 rounded-xl p-6 bg-slate-900/50">
                <h4 class="font-bold text-white mb-4">Tambah Prestasi Baru</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 mb-1">Nama Prestasi: <span class="text-rose-500">*</span></label>
                        <input v-model="achForm.name" type="text" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Tingkat: <span class="text-rose-500">*</span></label>
                        <select v-model="achForm.level" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required>
                            <option>Sekolah</option><option>Kabupaten/Kota</option><option>Provinsi</option><option>Nasional</option><option>Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Tahun: <span class="text-rose-500">*</span></label>
                        <input v-model="achForm.year" type="number" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required />
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan:</label>
                        <textarea v-model="achForm.description" rows="2" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" :disabled="achForm.processing" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition disabled:opacity-50">
                        {{ achForm.processing ? 'Menyimpan...' : 'Simpan Prestasi' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB: DATA PERIODIK -->
        <div v-show="activeTab === 'periodik'" class="bg-slate-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 shadow-2xl space-y-8">
            <h3 class="text-xl font-bold text-white mb-4">Riwayat Data Periodik</h3>
            <div class="overflow-x-auto rounded-xl border border-white/10 mb-8">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold border-b border-white/5">
                        <tr>
                            <th class="px-4 py-3">Tanggal Catat</th>
                            <th class="px-4 py-3 text-center">Tinggi Badan (cm)</th>
                            <th class="px-4 py-3 text-center">Berat Badan (kg)</th>
                            <th class="px-4 py-3 text-center">Lingkar Kepala (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="per in studentUser.student?.periodics" :key="per.id" class="border-b border-white/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3">{{ per.recorded_at ? new Date(per.recorded_at).toLocaleDateString('id-ID') : '-' }}</td>
                            <td class="px-4 py-3 text-center">{{ per.tinggi_badan || '-' }}</td>
                            <td class="px-4 py-3 text-center">{{ per.berat_badan || '-' }}</td>
                            <td class="px-4 py-3 text-center">{{ per.lingkar_kepala || '-' }}</td>
                        </tr>
                        <tr v-if="!studentUser.student?.periodics?.length">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 italic">Belum ada data periodik.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form @submit.prevent="submitPeriodic" class="border border-white/10 rounded-xl p-6 bg-slate-900/50">
                <h4 class="font-bold text-white mb-4">Tambah Pencatatan Periodik</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Tanggal Catat:</label>
                        <input v-model="perForm.recorded_at" type="date" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Tinggi Badan (cm):</label>
                        <input v-model="perForm.tinggi_badan" type="number" step="0.1" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Berat Badan (kg):</label>
                        <input v-model="perForm.berat_badan" type="number" step="0.1" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Lingkar Kepala (cm):</label>
                        <input v-model="perForm.lingkar_kepala" type="number" step="0.1" class="w-full bg-slate-950 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:ring-2 focus:ring-rose-500 outline-none" />
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" :disabled="perForm.processing" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition disabled:opacity-50">
                        {{ perForm.processing ? 'Menyimpan...' : 'Simpan Data Periodik' }}
                    </button>
                </div>
            </form>
        </div>

    </AdminLayout>
</template>
