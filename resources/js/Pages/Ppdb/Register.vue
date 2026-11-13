<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const currentStep = ref(1);

const form = useForm({
    student_name: '',
    nik: '',
    nisn: '',
    gender: 'L',
    birth_place: 'Gunungkidul',
    birth_date: '2019-05-15',
    address: '',
    previous_school: '',
    parent_name: '',
    parent_phone: '',
    parent_email: '',
    parent_job: '',
    kk_file: null,
    birth_certificate_file: null,
    ijazah_file: null,
});

const onFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        form[field] = file;
    }
};

const goToStep = (step) => {
    if (step === 2) {
        if (!form.student_name || !form.nik || !form.gender || !form.birth_place || !form.birth_date || !form.address) {
            alert('Mohon lengkapi data calon siswa yang berbintang merah terlebih dahulu.');
            return;
        }
    }
    if (step === 3) {
        if (!form.parent_name || !form.parent_phone) {
            alert('Mohon lengkapi nama orang tua dan nomor WhatsApp aktif.');
            return;
        }
    }
    currentStep.value = step;
};

const submit = () => {
    form.post(route('ppdb.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pendaftaran Siswa Baru (PPDB Online) - SD Muhammadiyah Al Mujahidin" />

    <div class="min-h-screen bg-slate-950 text-slate-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- HEADER -->
            <div class="text-center mb-8">
                <Link :href="route('home')" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span>Kembali ke Beranda Utama</span>
                </Link>
                <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">
                    Penerimaan Peserta Didik Baru (PPDB)
                </h1>
                <p class="mt-2 text-sm text-slate-400">
                    SD Muhammadiyah Al Mujahidin Wonosari • Tahun Ajaran 2026/2027
                </p>
            </div>

            <!-- STEP WIZARD INDICATOR -->
            <div class="mb-8 bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div 
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                            :class="currentStep === 1 ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20' : (currentStep > 1 ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-400')"
                        >
                            1
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs text-slate-400">Langkah 1</p>
                            <p class="text-sm font-semibold text-white">Data Calon Siswa</p>
                        </div>
                    </div>

                    <div class="h-0.5 w-12 sm:w-20 bg-slate-800" :class="currentStep >= 2 ? '!bg-emerald-500' : ''"></div>

                    <div class="flex items-center gap-3">
                        <div 
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                            :class="currentStep === 2 ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20' : (currentStep > 2 ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-400')"
                        >
                            2
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs text-slate-400">Langkah 2</p>
                            <p class="text-sm font-semibold text-white">Data Orang Tua</p>
                        </div>
                    </div>

                    <div class="h-0.5 w-12 sm:w-20 bg-slate-800" :class="currentStep >= 3 ? '!bg-emerald-500' : ''"></div>

                    <div class="flex items-center gap-3">
                        <div 
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                            :class="currentStep === 3 ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20' : 'bg-slate-800 text-slate-400'"
                        >
                            3
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-xs text-slate-400">Langkah 3</p>
                            <p class="text-sm font-semibold text-white">Unggah Berkas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM CARD -->
            <form @submit.prevent="submit" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <!-- STEP 1: CALON SISWA -->
                <div v-show="currentStep === 1" class="space-y-5">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-white">Biodata Calon Siswa</h2>
                        <p class="text-xs text-slate-400">Masukkan data calon peserta didik sesuai Kartu Keluarga / Akta Kelahiran.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap Siswa *</label>
                        <input 
                            v-model="form.student_name" 
                            type="text" 
                            required 
                            placeholder="Contoh: Muhammad Fatih Al-Mujahidin"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.student_name" class="mt-1 text-xs text-red-400">{{ form.errors.student_name }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">NIK (16 Digit) *</label>
                            <input 
                                v-model="form.nik" 
                                type="text" 
                                maxlength="16" 
                                required 
                                placeholder="3403xxxxxxxxxxxx"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                            <p v-if="form.errors.nik" class="mt-1 text-xs text-red-400">{{ form.errors.nik }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">NISN (Jika sudah ada dari TK)</label>
                            <input 
                                v-model="form.nisn" 
                                type="text" 
                                maxlength="10" 
                                placeholder="0123456789"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Jenis Kelamin *</label>
                            <select 
                                v-model="form.gender" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Tempat Lahir *</label>
                            <input 
                                v-model="form.birth_place" 
                                type="text" 
                                required 
                                placeholder="Gunungkidul"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Lahir *</label>
                            <input 
                                v-model="form.birth_date" 
                                type="date" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Tempat Tinggal *</label>
                        <textarea 
                            v-model="form.address" 
                            rows="2" 
                            required 
                            placeholder="Jl. KH. Agus Salim No. 12, Gadungsari, Wonosari"
                            class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Asal Sekolah TK / PAUD</label>
                        <input 
                            v-model="form.previous_school" 
                            type="text" 
                            placeholder="Contoh: TK ABA Wonosari I"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex justify-end pt-4">
                        <button 
                            type="button" 
                            @click="goToStep(2)"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2"
                        >
                            <span>Lanjut ke Data Orang Tua</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: DATA ORANG TUA -->
                <div v-show="currentStep === 2" class="space-y-5">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-white">Data Orang Tua / Wali</h2>
                        <p class="text-xs text-slate-400">Informasi kontak ayah / ibu untuk koordinasi dan verifikasi WhatsApp.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap Orang Tua / Wali *</label>
                        <input 
                            v-model="form.parent_name" 
                            type="text" 
                            required 
                            placeholder="Contoh: Bambang Hermanto, S.T."
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">No. WhatsApp Aktif * (Penting)</label>
                            <input 
                                v-model="form.parent_phone" 
                                type="text" 
                                required 
                                placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                            <p class="text-[11px] text-emerald-400/80 mt-1">Notifikasi nomor pendaftaran dan hasil seleksi akan dikirim ke nomor ini.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Email Orang Tua (Opsional)</label>
                            <input 
                                v-model="form.parent_email" 
                                type="email" 
                                placeholder="ortu@gmail.com"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Pekerjaan Orang Tua</label>
                        <input 
                            v-model="form.parent_job" 
                            type="text" 
                            placeholder="Contoh: PNS / Wiraswasta / Karyawan Swasta"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex justify-between pt-4">
                        <button 
                            type="button" 
                            @click="goToStep(1)"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-sm transition-all"
                        >
                            Kembali
                        </button>
                        <button 
                            type="button" 
                            @click="goToStep(3)"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2"
                        >
                            <span>Lanjut ke Berkas Persyaratan</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: BERKAS PERSYARATAN -->
                <div v-show="currentStep === 3" class="space-y-5">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-white">Unggah Berkas Persyaratan</h2>
                        <p class="text-xs text-slate-400">Format yang diterima: PDF, JPG, PNG (Maksimal 2 MB per berkas). Dokumen disimpan secara terproteksi.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                            <label class="block text-xs font-bold text-white mb-1">1. Scan Kartu Keluarga (KK)</label>
                            <p class="text-[11px] text-slate-400 mb-2">Pastikan NIK dan nama calon siswa terbaca jelas.</p>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                @change="onFileChange('kk_file', $event)"
                                class="text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500"
                            />
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                            <label class="block text-xs font-bold text-white mb-1">2. Scan Akta Kelahiran</label>
                            <p class="text-[11px] text-slate-400 mb-2">Akta kelahiran resmi dari Dinas Dukcapil.</p>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                @change="onFileChange('birth_certificate_file', $event)"
                                class="text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500"
                            />
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                            <label class="block text-xs font-bold text-white mb-1">3. Ijazah / Surat Keterangan TK (Opsional)</label>
                            <p class="text-[11px] text-slate-400 mb-2">Dapat disusulkan jika belum diterbitkan oleh pihak TK.</p>
                            <input 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                @change="onFileChange('ijazah_file', $event)"
                                class="text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500"
                            />
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 leading-relaxed">
                        Dengan menekan tombol "Kirim Pendaftaran", saya menyatakan data yang diisi adalah benar dan sah. Panitia PPDB berhak memverifikasi keabsahan dokumen yang diunggah.
                    </div>

                    <div class="flex justify-between pt-4">
                        <button 
                            type="button" 
                            @click="goToStep(2)"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-sm transition-all"
                        >
                            Kembali
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 transition-all flex items-center gap-2 disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>{{ form.processing ? 'Mengirim Pendaftaran...' : 'Kirim Pendaftaran Sekarang' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

