<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StaffLayout from '@/Layouts/StaffLayout.vue';
import TeacherLayout from '@/Layouts/TeacherLayout.vue';

const props = defineProps({
    achievements: {
        type: Array,
        default: () => [],
    },
    statusFilter: {
        type: String,
        default: 'Menunggu Verifikasi',
    },
    counts: {
        type: Object,
        default: () => ({ pending: 0, approved: 0, rejected: 0 }),
    },
});

const page = usePage();
const roles = computed(() => page.props.auth?.user?.roles || []);
const flash = computed(() => page.props.flash || {});

const activeLayout = computed(() => {
    if (roles.value.includes('Guru BK') || roles.value.includes('Guru Pengajar')) {
        return TeacherLayout;
    }
    if (roles.value.includes('Staf Sekolah')) {
        return StaffLayout;
    }
    if (roles.value.includes('Super Admin') || roles.value.includes('Admin Tata Usaha')) {
        return AdminLayout;
    }

    return null;
});

const selectedItem = ref(null);
const decisionStatus = ref('Disetujui');

const verifyForm = useForm({
    status: 'Disetujui',
    verification_notes: '',
});

const openVerifyModal = (item, status) => {
    selectedItem.value = item;
    decisionStatus.value = status;
    verifyForm.status = status;
    verifyForm.verification_notes = item.verification_notes || '';
    verifyForm.clearErrors();
};

const closeVerifyModal = () => {
    selectedItem.value = null;
    verifyForm.reset();
};

const submitVerify = () => {
    if (!selectedItem.value) {
        return;
    }

    verifyForm.post(route('achievements.verification.verify', selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeVerifyModal();
        },
    });
};

const filterByStatus = (status) => {
    router.get(
        route('achievements.verification.index'),
        { status },
        { preserveState: true, replace: true }
    );
};

const badgeClass = (status) => {
    if (status === 'Disetujui') {
        return 'bg-emerald-100 text-emerald-800';
    }
    if (status === 'Ditolak') {
        return 'bg-rose-100 text-rose-800';
    }

    return 'bg-amber-100 text-amber-800';
};

const filterTabs = [
    { id: 'Menunggu Verifikasi', label: 'Menunggu Verifikasi' },
    { id: 'Disetujui', label: 'Disetujui' },
    { id: 'Ditolak', label: 'Ditolak' },
    { id: 'Semua', label: 'Semua' },
];
</script>

<template>
    <Head title="Verifikasi Prestasi Siswa" />

    <component :is="activeLayout" v-if="activeLayout">
        <template #header-title>
            <h1 class="text-lg font-bold text-slate-800">Verifikasi Prestasi & Lomba Siswa</h1>
        </template>
        <template #header-subtitle>
            <p class="text-xs text-slate-500">Validasi pengajuan prestasi oleh Guru BK, Tata Usaha, atau Bidang Lomba untuk tampil di e-Raport</p>
        </template>

        <div class="space-y-6">
            <div v-if="flash.success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold">
                {{ flash.success }}
            </div>

            <!-- Summary KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
                    <p class="text-xs font-bold text-amber-700">Menunggu Verifikasi</p>
                    <p class="text-2xl font-black text-amber-950 mt-1">{{ counts.pending }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                    <p class="text-xs font-bold text-emerald-700">Disetujui (Tampil di e-Raport)</p>
                    <p class="text-2xl font-black text-emerald-950 mt-1">{{ counts.approved }}</p>
                </div>
                <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                    <p class="text-xs font-bold text-rose-700">Ditolak</p>
                    <p class="text-2xl font-black text-rose-950 mt-1">{{ counts.rejected }}</p>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm w-fit">
                <button
                    v-for="tab in filterTabs"
                    :key="tab.id"
                    type="button"
                    @click="filterByStatus(tab.id)"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                    :class="statusFilter === tab.id ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- List -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div v-if="achievements.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Tidak ada pengajuan prestasi pada status "{{ statusFilter }}".
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="item in achievements"
                        :key="item.id"
                        class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
                    >
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">
                                    {{ item.student_name }} ({{ item.class_name }})
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold" :class="badgeClass(item.status)">
                                    {{ item.status }}
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">{{ item.name }}</h3>
                            <p class="text-xs text-slate-600">
                                {{ item.category }} • Tingkat {{ item.level }} • Tahun {{ item.year }}
                                <span v-if="item.organizer && item.organizer !== '-'"> • Penyelenggara: {{ item.organizer }}</span>
                            </p>
                            <p v-if="item.description" class="text-xs text-slate-500">{{ item.description }}</p>
                            <p class="text-[11px] text-slate-400">
                                Diajukan oleh {{ item.submitter_name }} pada {{ item.created_at }}
                                <span v-if="item.verifier_name"> • Diverifikasi oleh {{ item.verifier_name }} ({{ item.verified_at }})</span>
                            </p>
                            <p v-if="item.verification_notes" class="text-xs text-slate-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                                <span class="font-bold">Catatan:</span> {{ item.verification_notes }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a
                                v-if="item.has_certificate"
                                :href="route('achievements.verification.certificate', item.id)"
                                target="_blank"
                                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors"
                            >
                                Cek Sertifikat
                            </a>
                            <button
                                type="button"
                                @click="openVerifyModal(item, 'Disetujui')"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors"
                            >
                                Setujui
                            </button>
                            <button
                                type="button"
                                @click="openVerifyModal(item, 'Ditolak')"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors"
                            >
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Verifikasi -->
        <Teleport to="body">
            <div v-if="selectedItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                    <h3 class="text-base font-extrabold text-slate-900">
                        {{ decisionStatus === 'Disetujui' ? 'Setujui Prestasi Siswa' : 'Tolak Pengajuan Prestasi' }}
                    </h3>
                    <p class="text-xs text-slate-600">
                        <span class="font-bold">{{ selectedItem.student_name }}</span> — {{ selectedItem.name }} ({{ selectedItem.level }}, {{ selectedItem.year }})
                    </p>

                    <form @submit.prevent="submitVerify" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Catatan Verifikasi {{ decisionStatus === 'Ditolak' ? '(Disarankan diisi)' : '(Opsional)' }}
                            </label>
                            <textarea
                                v-model="verifyForm.verification_notes"
                                rows="3"
                                placeholder="Contoh: Sertifikat sesuai dan sah dari penyelenggara resmi."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                            <p v-if="verifyForm.errors.verification_notes" class="text-xs text-rose-600 mt-1">
                                {{ verifyForm.errors.verification_notes }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="closeVerifyModal"
                                class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="verifyForm.processing"
                                class="px-5 py-2 rounded-xl text-white text-xs font-bold transition-colors disabled:opacity-50"
                                :class="decisionStatus === 'Disetujui' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                            >
                                {{ verifyForm.processing ? 'Menyimpan...' : `Konfirmasi ${decisionStatus}` }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </component>

    <div v-else class="min-h-screen bg-slate-50 text-slate-800">
        <header class="bg-emerald-700 text-white px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="font-extrabold text-base">Verifikasi Prestasi Siswa</h1>
                <p class="text-xs text-emerald-100">Bidang Lomba & Kesiswaan SIMSE</p>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="px-3 py-1.5 rounded-lg bg-white/15 text-xs font-bold">
                Keluar
            </Link>
        </header>
        <main class="max-w-5xl mx-auto p-6 space-y-4">
            <div v-if="achievements.length === 0" class="p-8 bg-white rounded-2xl border border-slate-200 text-center text-sm text-slate-500">
                Tidak ada pengajuan prestasi pada status "{{ statusFilter }}".
            </div>
            <div v-for="item in achievements" :key="item.id" class="p-5 bg-white rounded-2xl border border-slate-200 flex items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-slate-900">{{ item.student_name }} — {{ item.name }}</p>
                    <p class="text-xs text-slate-500">{{ item.category }} • {{ item.level }} • {{ item.year }} • Status: {{ item.status }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="openVerifyModal(item, 'Disetujui')" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Setujui</button>
                    <button type="button" @click="openVerifyModal(item, 'Ditolak')" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-bold">Tolak</button>
                </div>
            </div>
        </main>
    </div>
</template>

