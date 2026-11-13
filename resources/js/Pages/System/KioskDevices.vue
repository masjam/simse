<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    devices: { type: Array, required: true },
});

const form = useForm({
    name: '',
    mac_address: '',
    location: '',
});

const submit = () => {
    form.post(route('sistem.kiosk-devices.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const toggleDevice = (id) => {
    router.patch(route('sistem.kiosk-devices.toggle', id), {}, { preserveScroll: true });
};

const deleteDevice = (id) => {
    if (!confirm('Hapus MAC Address perangkat kiosk ini?')) {
        return;
    }
    router.delete(route('sistem.kiosk-devices.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Perangkat Kiosk Presensi (MAC)" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-black text-slate-800">Perangkat Kiosk Presensi Siswa (Multi MAC Address)</h1>
                <p class="text-sm text-slate-500">
                    Kiosk Presensi RFID hanya dapat dibuka oleh PC dengan MAC Address terdaftar &amp; aktif di bawah ini.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <form class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 shadow-sm h-fit" @submit.prevent="submit">
                    <h2 class="font-extrabold text-slate-800">Tambah PC Kiosk</h2>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nama Perangkat</label>
                        <input v-model="form.name" type="text" class="w-full rounded-xl border-slate-300 text-sm" placeholder="PC Kiosk Gerbang Utama" required />
                        <p v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">MAC Address PC</label>
                        <input v-model="form.mac_address" type="text" class="w-full rounded-xl border-slate-300 text-sm font-mono uppercase" placeholder="AA:BB:CC:DD:EE:FF" required />
                        <p v-if="form.errors.mac_address" class="text-xs text-rose-600 mt-1">{{ form.errors.mac_address }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Lokasi (Opsional)</label>
                        <input v-model="form.location" type="text" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Lobby Depan Sekolah" />
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-extrabold">
                        Daftarkan MAC Address
                    </button>
                </form>

                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-extrabold text-slate-800">Daftar MAC Address Terotorisasi ({{ devices.length }})</h2>
                    </div>

                    <div v-if="devices.length === 0" class="p-8 text-center text-sm text-slate-500">
                        Belum ada PC Kiosk yang didaftarkan.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="device in devices" :key="device.id" class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800">{{ device.name }}</h3>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                        :class="device.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                    >
                                        {{ device.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <p class="font-mono text-xs font-bold text-indigo-700">{{ device.mac_address }}</p>
                                <p class="text-xs text-slate-500">Lokasi: {{ device.location }} &middot; Terakhir aktif: {{ device.last_seen_at }}</p>
                                <a
                                    v-if="device.is_active"
                                    :href="device.kiosk_url"
                                    target="_blank"
                                    class="inline-block text-[11px] font-bold text-sky-700 hover:underline mt-1"
                                >
                                    Buka Kiosk ({{ device.mac_address }}) &rarr;
                                </a>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700"
                                    @click="toggleDevice(device.id)"
                                >
                                    {{ device.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700"
                                    @click="deleteDevice(device.id)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

