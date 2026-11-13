<script setup>
import { computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
    modules: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const settingsForm = useForm({
    school_name: props.settings.school_name || '',
    school_address: props.settings.school_address || '',
    school_phone: props.settings.school_phone || '',
    school_email: props.settings.school_email || '',
    active_semester: props.settings.active_semester || '',
    school_latitude: props.settings.school_latitude || '-7.9664',
    school_longitude: props.settings.school_longitude || '110.6035',
    school_radius_meters: props.settings.school_radius_meters || '150',
});

const saveSettings = () => {
    settingsForm.post(route('sistem.pengaturan.update'), { preserveScroll: true });
};

const toggleModule = (moduleId) => {
    router.patch(route('sistem.modules.toggle', moduleId), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Pengaturan Sistem & Manajemen Modul" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-black text-white">Pengaturan Sistem &amp; Manajemen Modul</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Kelola identitas sekolah serta aktif/nonaktifkan modul. Modul nonaktif otomatis tersembunyi di menu dan route-nya diblokir.
                </p>
            </div>

            <div v-if="flash.success" class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-sm font-semibold">
                {{ flash.success }}
            </div>

            <!-- Identitas Sekolah -->
            <form @submit.prevent="saveSettings" class="p-6 rounded-3xl bg-slate-950/60 border border-white/10 space-y-4">
                <h2 class="text-base font-extrabold text-white">Identitas &amp; Periode Sekolah</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Sekolah *</label>
                        <input v-model="settingsForm.school_name" type="text" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Semester Aktif *</label>
                        <input v-model="settingsForm.active_semester" type="text" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">No. Telepon Sekolah</label>
                        <input v-model="settingsForm.school_phone" type="text" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email Resmi</label>
                        <input v-model="settingsForm.school_email" type="email" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Alamat Sekolah *</label>
                    <input v-model="settingsForm.school_address" type="text" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                </div>

                <!-- Radius Presensi Geolokasi Pegawai -->
                <div class="pt-4 border-t border-white/10">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-3">Konfigurasi Radius Presensi GPS Pegawai</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Latitude Sekolah</label>
                            <input v-model="settingsForm.school_latitude" type="text" placeholder="-7.9664" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm font-mono text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Longitude Sekolah</label>
                            <input v-model="settingsForm.school_longitude" type="text" placeholder="110.6035" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm font-mono text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Radius Toleransi (Meter)</label>
                            <input v-model="settingsForm.school_radius_meters" type="number" min="10" max="5000" placeholder="150" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm font-mono text-white" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="settingsForm.processing"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors disabled:opacity-50"
                    >
                        {{ settingsForm.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>

            <!-- Toggle Modul -->
            <div class="p-6 rounded-3xl bg-slate-950/60 border border-white/10 space-y-4">
                <h2 class="text-base font-extrabold text-white">Status Modul SIMSE</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 flex items-center justify-between gap-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-sm text-white">{{ mod.name }}</p>
                                <span class="px-2 py-0.5 rounded-md bg-white/5 text-[10px] font-mono text-slate-400">
                                    {{ mod.code }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">{{ mod.description }}</p>
                        </div>

                        <button
                            type="button"
                            @click="toggleModule(mod.id)"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors shrink-0"
                            :class="mod.is_active
                                ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30'
                                : 'bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500/30'"
                        >
                            {{ mod.is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

