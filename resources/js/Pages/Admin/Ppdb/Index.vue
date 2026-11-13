<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    registrations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'all' }),
    },
    availableClasses: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

const applyFilter = () => {
    router.get(route('admin.ppdb.index'), {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const selectedReg = ref(null);
const showModal = ref(false);

const form = useForm({
    status: 'Diterima',
    admin_notes: '',
    class_id: props.availableClasses.length > 0 ? props.availableClasses[0].id : '',
});

const openVerifyModal = (reg) => {
    selectedReg.value = reg;
    form.status = reg.status === 'Menunggu Verifikasi' ? 'Diterima' : reg.status;
    form.admin_notes = '';
    form.class_id = props.availableClasses.length > 0 ? props.availableClasses[0].id : '';
    showModal.value = true;
};

const submitVerification = () => {
    if (!selectedReg.value) return;

    form.post(route('admin.ppdb.status', selectedReg.value.id), {
        onSuccess: () => {
            showModal.value = false;
            selectedReg.value = null;
        },
    });
};
</script>

<template>
    <Head title="Verifikasi PPDB Siswa Baru - Admin SIMSE" />

    <AdminLayout>
        <template #header-title>Penerimaan Peserta Didik Baru (PPDB)</template>
        <template #header-subtitle>Verifikasi berkas calon siswa dan konversi otomatis menjadi siswa aktif</template>

        <div class="space-y-6">
            <!-- STATS CARDS -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                    <p class="text-xs text-slate-400 font-medium">Total Pendaftar</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ stats.total }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                    <p class="text-xs text-amber-400 font-medium">Menunggu Verifikasi</p>
                    <p class="text-2xl font-bold text-amber-300 mt-1">{{ stats.pending }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                    <p class="text-xs text-emerald-400 font-medium">Diterima (Siswa Aktif)</p>
                    <p class="text-2xl font-bold text-emerald-300 mt-1">{{ stats.accepted }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-red-500/10 border border-red-500/20">
                    <p class="text-xs text-red-400 font-medium">Ditolak</p>
                    <p class="text-2xl font-bold text-red-300 mt-1">{{ stats.rejected }}</p>
                </div>
            </div>

            <!-- FILTERS & SEARCH -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <button 
                        @click="status = 'all'; applyFilter()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="status === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        Semua ({{ stats.total }})
                    </button>
                    <button 
                        @click="status = 'Menunggu Verifikasi'; applyFilter()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="status === 'Menunggu Verifikasi' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        Pending ({{ stats.pending }})
                    </button>
                    <button 
                        @click="status = 'Diterima'; applyFilter()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="status === 'Diterima' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        Diterima ({{ stats.accepted }})
                    </button>
                    <button 
                        @click="status = 'Ditolak'; applyFilter()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
                        :class="status === 'Ditolak' ? 'bg-red-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    >
                        Ditolak ({{ stats.rejected }})
                    </button>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-80">
                    <input 
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text" 
                        placeholder="Cari nama, NIK, no reg, ortu..."
                        class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                    <button 
                        @click="applyFilter" 
                        type="button"
                        class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white"
                        title="Cari"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </div>
            </div>

            <!-- REGISTRATIONS TABLE -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3.5">No. Reg</th>
                                <th class="px-4 py-3.5">Calon Siswa</th>
                                <th class="px-4 py-3.5">TTL / Asal TK</th>
                                <th class="px-4 py-3.5">Orang Tua & Kontak</th>
                                <th class="px-4 py-3.5 text-center">Berkas</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <tr v-for="item in registrations.data" :key="item.id" class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-400">
                                    {{ item.registration_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-white">{{ item.student_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ item.gender }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ item.birth_place_date }}</p>
                                    <p class="text-[11px] text-slate-400">TK: {{ item.previous_school }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-white font-medium">{{ item.parent_name }}</p>
                                    <p class="text-[11px] text-emerald-400/90 font-mono">{{ item.parent_phone }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a 
                                            v-if="item.has_kk" 
                                            :href="route('admin.ppdb.document', { registration: item.id, type: 'kk' })"
                                            target="_blank"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20"
                                            title="Download KK"
                                        >
                                            KK
                                        </a>
                                        <a 
                                            v-if="item.has_akta" 
                                            :href="route('admin.ppdb.document', { registration: item.id, type: 'akta' })"
                                            target="_blank"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500/20"
                                            title="Download Akta"
                                        >
                                            Akta
                                        </a>
                                        <a 
                                            v-if="item.has_ijazah" 
                                            :href="route('admin.ppdb.document', { registration: item.id, type: 'ijazah' })"
                                            target="_blank"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-400 border border-teal-500/20 hover:bg-teal-500/20"
                                            title="Download Ijazah TK"
                                        >
                                            Ijazah
                                        </a>
                                        <span v-if="!item.has_kk && !item.has_akta && !item.has_ijazah" class="text-slate-500 text-[11px]">-</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span 
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                        :class="{
                                            'bg-amber-500/10 text-amber-400 border-amber-500/20': item.status === 'Menunggu Verifikasi',
                                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': item.status === 'Diterima',
                                            'bg-red-500/10 text-red-400 border-red-500/20': item.status === 'Ditolak',
                                        }"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button 
                                        @click="openVerifyModal(item)"
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 font-semibold text-xs transition-all"
                                    >
                                        Verifikasi
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="registrations.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    Tidak ada data pendaftaran PPDB yang cocok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VERIFICATION MODAL -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-white">Verifikasi Pendaftaran PPDB</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div v-if="selectedReg" class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs space-y-1.5">
                    <p><span class="text-slate-400">Calon Siswa:</span> <strong class="text-white">{{ selectedReg.student_name }}</strong> ({{ selectedReg.registration_number }})</p>
                    <p><span class="text-slate-400">Orang Tua / Wali:</span> {{ selectedReg.parent_name }} ({{ selectedReg.parent_phone }})</p>
                    <p><span class="text-slate-400">TTL:</span> {{ selectedReg.birth_place_date }}</p>
                </div>

                <form @submit.prevent="submitVerification" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Keputusan Status *</label>
                        <select 
                            v-model="form.status" 
                            required 
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="Diterima">Diterima (Konversi ke Siswa Aktif)</option>
                            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div v-if="form.status === 'Diterima'">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Rombongan Belajar (Kelas 1) *</label>
                        <select 
                            v-model="form.class_id" 
                            required 
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option v-for="c in availableClasses" :key="c.id" :value="c.id">
                                Kelas {{ c.name }}
                            </option>
                        </select>
                        <p class="text-[11px] text-emerald-400 mt-1">
                            ✓ Otomatis membuat akun User Siswa, akun User Orang Tua, record Siswa, dompet digital, dan notifikasi WA.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Admin / Verifikator (Opsional)</label>
                        <textarea 
                            v-model="form.admin_notes" 
                            rows="2" 
                            placeholder="Catatan untuk arsip atau dikirim ke orang tua..."
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button 
                            @click="showModal = false" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-medium hover:bg-slate-700"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Keputusan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

