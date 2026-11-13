<script setup>
import { computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    logs: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const configForm = useForm({
    wa_driver: props.config.wa_driver || 'log',
    wa_is_active: Boolean(props.config.wa_is_active),
    wa_fonnte_token: props.config.wa_fonnte_token || '',
    wa_fonnte_endpoint: props.config.wa_fonnte_endpoint || 'https://api.fonnte.com/send',
    wa_wablas_token: props.config.wa_wablas_token || '',
    wa_wablas_endpoint: props.config.wa_wablas_endpoint || 'https://solo.wablas.com/api/send-message',
});

const announcementForm = useForm({
    target_role: 'Orang Tua',
    title: '',
    message: '',
    send_whatsapp: true,
});

const saveConfig = () => {
    configForm.post(route('sistem.wa.update'), { preserveScroll: true });
};

const sendAnnouncement = () => {
    announcementForm.post(route('sistem.wa.announcement'), {
        preserveScroll: true,
        onSuccess: () => announcementForm.reset('title', 'message'),
    });
};

const retryLog = (logId) => {
    router.post(route('sistem.wa.retry', logId), {}, { preserveScroll: true });
};

const statusBadge = (status) => {
    if (status === 'Terkirim') {
        return 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30';
    }
    if (status === 'Gagal') {
        return 'bg-rose-500/15 text-rose-300 border-rose-500/30';
    }

    return 'bg-amber-500/15 text-amber-300 border-amber-500/30';
};
</script>

<template>
    <Head title="WhatsApp Gateway & Pengumuman" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-black text-white">WhatsApp Gateway &amp; Broadcast Pengumuman</h1>
                <p class="text-sm text-slate-400 mt-1">
                    Konfigurasi provider WhatsApp (Log, Fonnte, Wablas), pantau antrean pengiriman, dan kirim pengumuman sekolah.
                </p>
            </div>

            <div v-if="flash.success" class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-sm font-semibold">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-sm font-semibold">
                {{ flash.error }}
            </div>

            <!-- KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-slate-950/60 border border-white/10">
                    <p class="text-xs font-bold text-emerald-400 uppercase">Terkirim</p>
                    <p class="text-3xl font-black text-white mt-1">{{ stats.sent }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-950/60 border border-white/10">
                    <p class="text-xs font-bold text-amber-400 uppercase">Menunggu Antrean</p>
                    <p class="text-3xl font-black text-white mt-1">{{ stats.pending }}</p>
                </div>
                <div class="p-5 rounded-2xl bg-slate-950/60 border border-white/10">
                    <p class="text-xs font-bold text-rose-400 uppercase">Gagal</p>
                    <p class="text-3xl font-black text-white mt-1">{{ stats.failed }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Konfigurasi Provider -->
                <form @submit.prevent="saveConfig" class="p-6 rounded-3xl bg-slate-950/60 border border-white/10 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-white">Konfigurasi Gateway</h2>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                            <input v-model="configForm.wa_is_active" type="checkbox" class="rounded text-emerald-500" />
                            <span>Gateway Aktif</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Driver Aktif</label>
                        <select v-model="configForm.wa_driver" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white">
                            <option value="log">Log (Simulasi Lokal / Development)</option>
                            <option value="fonnte">Fonnte API</option>
                            <option value="wablas">Wablas API</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Token Fonnte</label>
                            <input v-model="configForm.wa_fonnte_token" type="password" placeholder="Masukkan token Fonnte" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Endpoint Fonnte</label>
                            <input v-model="configForm.wa_fonnte_endpoint" type="url" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Token Wablas</label>
                            <input v-model="configForm.wa_wablas_token" type="password" placeholder="Masukkan token Wablas" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Endpoint Wablas</label>
                            <input v-model="configForm.wa_wablas_endpoint" type="url" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="configForm.processing"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors disabled:opacity-50"
                    >
                        {{ configForm.processing ? 'Menyimpan...' : 'Simpan Konfigurasi Gateway' }}
                    </button>
                </form>

                <!-- Broadcast Pengumuman -->
                <form @submit.prevent="sendAnnouncement" class="p-6 rounded-3xl bg-slate-950/60 border border-white/10 space-y-4">
                    <h2 class="text-base font-extrabold text-white">Kirim Pengumuman Sekolah</h2>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Target Penerima</label>
                        <select v-model="announcementForm.target_role" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white">
                            <option value="Orang Tua">Semua Orang Tua / Wali</option>
                            <option value="Guru Pengajar">Semua Guru Pengajar</option>
                            <option value="Siswa">Semua Siswa</option>
                            <option value="all">Seluruh Pengguna Sistem</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Judul Pengumuman</label>
                        <input v-model="announcementForm.title" type="text" required placeholder="Contoh: Jadwal Libur Awal Ramadhan" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Isi Pengumuman</label>
                        <textarea v-model="announcementForm.message" rows="3" required placeholder="Tuliskan isi pengumuman resmi sekolah..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-sm text-white"></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                        <input v-model="announcementForm.send_whatsapp" type="checkbox" class="rounded text-amber-500" />
                        <span>Antrekan pengiriman ke WhatsApp selain notifikasi in-app</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="announcementForm.processing"
                        class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition-colors disabled:opacity-50"
                    >
                        {{ announcementForm.processing ? 'Mengirim...' : 'Kirim Pengumuman Sekarang' }}
                    </button>
                </form>
            </div>

            <!-- Riwayat Log WA -->
            <div class="p-6 rounded-3xl bg-slate-950/60 border border-white/10">
                <h2 class="text-base font-extrabold text-white mb-4">Log Pengiriman WhatsApp (50 Terakhir)</h2>
                <div v-if="logs.length === 0" class="text-sm text-slate-400">Belum ada log pengiriman WhatsApp.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400 uppercase">
                                <th class="py-2.5 pr-4">Penerima</th>
                                <th class="py-2.5 pr-4">No. WA</th>
                                <th class="py-2.5 pr-4">Pesan</th>
                                <th class="py-2.5 pr-4">Status</th>
                                <th class="py-2.5 pr-4">Waktu</th>
                                <th class="py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="log in logs" :key="log.id">
                                <td class="py-3 pr-4 font-bold text-white">{{ log.recipient_name }}</td>
                                <td class="py-3 pr-4 font-mono text-slate-300">{{ log.phone_number }}</td>
                                <td class="py-3 pr-4 text-slate-300 max-w-md truncate">{{ log.message }}</td>
                                <td class="py-3 pr-4">
                                    <span class="px-2.5 py-0.5 rounded-full border font-bold" :class="statusBadge(log.status)">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-slate-400 whitespace-nowrap">{{ log.created_at }}</td>
                                <td class="py-3 text-right">
                                    <button
                                        v-if="log.status === 'Gagal'"
                                        type="button"
                                        @click="retryLog(log.id)"
                                        class="px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold"
                                    >
                                        Kirim Ulang
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

