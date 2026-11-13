<script setup>
import StaffLayout from '@/Layouts/StaffLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    todayAttendance: { type: Object, default: null },
    schoolGeo: { type: Object, required: true },
    history: { type: Array, required: true },
});

const currentLat = ref(null);
const currentLng = ref(null);
const locationAccuracy = ref(null);
const locationError = ref('');
const detectingLocation = ref(false);

const form = useForm({
    type: 'check_in',
    latitude: '',
    longitude: '',
    photo_proof: null,
});

// Hitung jarak Haversine (meter)
const distanceMeters = computed(() => {
    if (currentLat.value === null || currentLng.value === null) {
        return null;
    }
    const R = 6371000;
    const dLat = (props.schoolGeo.latitude - currentLat.value) * (Math.PI / 180);
    const dLon = (props.schoolGeo.longitude - currentLng.value) * (Math.PI / 180);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(currentLat.value * (Math.PI / 180)) *
        Math.cos(props.schoolGeo.latitude * (Math.PI / 180)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
});

const isWithinRadius = computed(() => {
    if (distanceMeters.value === null) {
        return false;
    }
    return distanceMeters.value <= props.schoolGeo.radius_meters;
});

const detectLocation = () => {
    if (!navigator.geolocation) {
        locationError.value = 'Browser Anda tidak mendukung deteksi lokasi (Geolocation).';
        return;
    }
    detectingLocation.value = true;
    locationError.value = '';

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            currentLat.value = pos.coords.latitude;
            currentLng.value = pos.coords.longitude;
            locationAccuracy.value = Math.round(pos.coords.accuracy);
            form.latitude = pos.coords.latitude;
            form.longitude = pos.coords.longitude;
            detectingLocation.value = false;
        },
        (err) => {
            detectingLocation.value = false;
            locationError.value = 'Izin lokasi ditolak atau tidak dapat mendeteksi GPS. Mohon izinkan akses lokasi di browser.';
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
};

onMounted(() => {
    detectLocation();
});

const submitAttendance = (type) => {
    form.type = type;
    form.latitude = currentLat.value;
    form.longitude = currentLng.value;

    form.post(route('staf.absensi.store'), {
        preserveScroll: true,
        onSuccess: () => {
            detectLocation();
        },
    });
};
</script>

<template>
    <Head title="Presensi Pegawai (Geolokasi)" />

    <StaffLayout>
        <template #header-title>
            <h1 class="text-2xl font-black text-slate-800">Presensi Pegawai Berbasis Lokasi (GPS)</h1>
        </template>
        <template #header-subtitle>
            <p class="text-slate-500 font-medium">{{ schoolGeo.school_name }} &middot; Batas radius: {{ schoolGeo.radius_meters }} meter</p>
        </template>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Kartu Presensi Hari Ini -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5 h-fit">
                <div class="text-center space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Presensi Pegawai &middot; Hari Ini</p>
                    <h2 class="text-2xl font-black text-slate-800">
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </h2>
                </div>

                <!-- Status Radius GPS -->
                <div class="rounded-2xl border p-4 space-y-2 text-center" :class="isWithinRadius ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                    <div class="flex items-center justify-center gap-2 font-black text-sm">
                        <span class="w-2.5 h-2.5 rounded-full" :class="isWithinRadius ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                        <span>{{ isWithinRadius ? 'Dalam Radius Sekolah' : 'Di Luar Radius Sekolah' }}</span>
                    </div>
                    <p class="text-xs">
                        Jarak ke sekolah: <strong>{{ distanceMeters !== null ? `${distanceMeters} m` : 'Mendeteksi...' }}</strong> (Maks. {{ schoolGeo.radius_meters }} m)
                    </p>
                    <button type="button" class="text-[11px] font-bold text-slate-600 underline hover:text-slate-900" @click="detectLocation">
                        {{ detectingLocation ? 'Memperbarui GPS...' : 'Segarkan Posisi GPS' }}
                    </button>
                </div>

                <div v-if="locationError" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800">
                    {{ locationError }}
                </div>

                <!-- Info Kehadiran Saat Ini -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status Masuk:</span>
                        <strong class="text-slate-800">{{ todayAttendance?.time_in ? `Hadir (${todayAttendance.time_in})` : 'Belum Masuk' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status Pulang:</span>
                        <strong class="text-slate-800">{{ todayAttendance?.time_out ? `Pulang (${todayAttendance.time_out})` : 'Belum Pulang' }}</strong>
                    </div>
                </div>

                <!-- Tombol Aksi Masuk / Pulang -->
                <div class="space-y-2">
                    <button
                        type="button"
                        :disabled="form.processing || !isWithinRadius || (todayAttendance && todayAttendance.time_in)"
                        class="w-full py-3.5 rounded-xl font-extrabold text-sm transition text-white"
                        :class="isWithinRadius && (!todayAttendance || !todayAttendance.time_in) ? 'bg-emerald-600 hover:bg-emerald-700 shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        @click="submitAttendance('check_in')"
                    >
                        {{ todayAttendance?.time_in ? 'Sudah Presensi Masuk' : 'Presensi Masuk (Check-In)' }}
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing || !isWithinRadius || !todayAttendance || !todayAttendance.time_in || todayAttendance.time_out"
                        class="w-full py-3.5 rounded-xl font-extrabold text-sm transition text-white"
                        :class="isWithinRadius && todayAttendance && todayAttendance.time_in && !todayAttendance.time_out ? 'bg-indigo-600 hover:bg-indigo-700 shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        @click="submitAttendance('check_out')"
                    >
                        {{ todayAttendance?.time_out ? 'Sudah Presensi Pulang' : 'Presensi Pulang (Check-Out)' }}
                    </button>
                </div>
            </div>

            <!-- Tabel Riwayat Presensi -->
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-extrabold text-slate-800">Riwayat Presensi Pegawai (30 Hari Terakhir)</h2>
                    <span class="text-xs text-slate-400 font-semibold">{{ history.length }} Catatan</span>
                </div>

                <div v-if="history.length === 0" class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat presensi yang tercatat.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Jam Masuk</th>
                                <th class="px-6 py-3">Jam Pulang</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Koordinat GPS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in history" :key="item.id" class="hover:bg-slate-50/70">
                                <td class="px-6 py-3.5 font-bold text-slate-800">{{ item.date }}</td>
                                <td class="px-6 py-3.5 font-semibold text-slate-700">{{ item.time_in }}</td>
                                <td class="px-6 py-3.5 font-semibold text-slate-700">{{ item.time_out }}</td>
                                <td class="px-6 py-3.5">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                        :class="item.status === 'Hadir' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs text-slate-400">
                                    {{ item.location_lat && item.location_lng ? `${Number(item.location_lat).toFixed(4)}, ${Number(item.location_lng).toFixed(4)}` : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StaffLayout>
</template>
