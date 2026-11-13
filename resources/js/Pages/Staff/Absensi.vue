<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    todayAttendance: { type: Object, default: null },
    schoolGeo: { type: Object, required: true },
    history: { type: Array, required: true },
});

const toast = useToast();
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
    if (currentLat.value === null || currentLng.value === null) return null;
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
    if (distanceMeters.value === null) return false;
    return distanceMeters.value <= props.schoolGeo.radius_meters;
});

const detectLocation = () => {
    if (!navigator.geolocation) {
        locationError.value = 'Peramban Anda tidak mendukung deteksi geolokasi (GPS).';
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
        () => {
            detectingLocation.value = false;
            locationError.value = 'Izin lokasi ditolak atau tidak dapat mendeteksi GPS. Mohon izinkan akses lokasi pada peramban.';
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
            toast.success(type === 'check_in' ? 'Presensi masuk berhasil dicatat!' : 'Presensi pulang berhasil dicatat!');
        },
    });
};

const historyColumns = [
    { key: 'date', label: 'Tanggal' },
    { key: 'time_in', label: 'Jam Masuk' },
    { key: 'time_out', label: 'Jam Pulang' },
    { key: 'status', label: 'Status Kehadiran', class: 'text-center' },
    { key: 'coordinates', label: 'Koordinat GPS', class: 'text-right' },
];
</script>

<template>
    <Head title="Presensi Pegawai (Geolokasi)" />
    <PortalShell variant="sidebar">
        <div class="space-y-6">
            <PageHeader
                title="Presensi Pegawai Berbasis Lokasi (GPS)"
                :subtitle="`${schoolGeo.school_name} • Batas radius kehadiran: ${schoolGeo.radius_meters} meter`"
            />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kartu Presensi Hari Ini -->
                <AppCard class="space-y-4 h-fit">
                    <div class="text-center space-y-1 pb-2 border-b border-line">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                            Presensi Staf • Hari Ini
                        </p>
                        <h3 class="text-lg font-bold text-ink">
                            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </h3>
                    </div>

                    <!-- Status Radius GPS -->
                    <div
                        class="rounded-control border p-4 space-y-2 text-center"
                        :class="isWithinRadius ? 'bg-status-success-bg border-status-success-line text-status-success-fg' : 'bg-status-danger-bg border-status-danger-line text-status-danger-fg'"
                    >
                        <div class="flex items-center justify-center gap-2 font-black text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :class="isWithinRadius ? 'bg-status-success-fg animate-pulse' : 'bg-status-danger-fg'"></span>
                            <span>{{ isWithinRadius ? 'Dalam Radius Sekolah' : 'Di Luar Radius Sekolah' }}</span>
                        </div>
                        <p class="text-xs">
                            Jarak ke sekolah: <strong>{{ distanceMeters !== null ? `${distanceMeters} m` : 'Mendeteksi...' }}</strong> (Maks. {{ schoolGeo.radius_meters }} m)
                        </p>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            class="!p-0 underline text-[11px]"
                            @click="detectLocation"
                        >
                            {{ detectingLocation ? 'Memperbarui GPS...' : 'Segarkan Posisi GPS' }}
                        </AppButton>
                    </div>

                    <div v-if="locationError" class="p-3 rounded-control bg-status-warning-bg text-status-warning-fg border border-status-warning-line text-xs font-semibold">
                        {{ locationError }}
                    </div>

                    <!-- Info Kehadiran Saat Ini -->
                    <div class="p-3.5 rounded-control bg-surface-raised border border-line text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-ink-muted">Status Masuk:</span>
                            <strong class="text-ink">{{ todayAttendance?.time_in ? `Hadir (${todayAttendance.time_in})` : 'Belum Masuk' }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-muted">Status Pulang:</span>
                            <strong class="text-ink">{{ todayAttendance?.time_out ? `Pulang (${todayAttendance.time_out})` : 'Belum Pulang' }}</strong>
                        </div>
                    </div>

                    <!-- Tombol Aksi Masuk / Pulang -->
                    <div class="space-y-2 pt-2">
                        <AppButton
                            variant="primary"
                            size="lg"
                            class="w-full shadow-card"
                            :disabled="form.processing || !isWithinRadius || (todayAttendance && Boolean(todayAttendance.time_in))"
                            :loading="form.processing && form.type === 'check_in'"
                            @click="submitAttendance('check_in')"
                        >
                            {{ todayAttendance?.time_in ? 'Sudah Presensi Masuk' : '📍 Presensi Masuk (Check-In)' }}
                        </AppButton>

                        <AppButton
                            variant="secondary"
                            size="lg"
                            class="w-full shadow-card"
                            :disabled="form.processing || !isWithinRadius || !todayAttendance || !todayAttendance.time_in || Boolean(todayAttendance.time_out)"
                            :loading="form.processing && form.type === 'check_out'"
                            @click="submitAttendance('check_out')"
                        >
                            {{ todayAttendance?.time_out ? 'Sudah Presensi Pulang' : '🏠 Presensi Pulang (Check-Out)' }}
                        </AppButton>
                    </div>
                </AppCard>

                <!-- Tabel Riwayat Presensi -->
                <div class="lg:col-span-2 space-y-3">
                    <h3 class="text-base font-bold text-ink px-1">
                        Riwayat Presensi Pegawai (30 Hari Terakhir)
                    </h3>
                    <DataTable
                        :columns="historyColumns"
                        :items="history"
                        empty-title="Belum Ada Riwayat"
                        empty-message="Belum ada catatan presensi pegawai yang terekam pada 30 hari terakhir."
                    >
                        <template #cell(date)="{ value }">
                            <strong class="font-bold text-xs text-ink">{{ value }}</strong>
                        </template>

                        <template #cell(time_in)="{ value }">
                            <span class="text-xs text-ink font-numeric">{{ value || '-' }}</span>
                        </template>

                        <template #cell(time_out)="{ value }">
                            <span class="text-xs text-ink font-numeric">{{ value || '-' }}</span>
                        </template>

                        <template #cell(status)="{ value }">
                            <div class="flex justify-center">
                                <StatusBadge :variant="value === 'Hadir' ? 'success' : 'warning'" size="sm">
                                    {{ value }}
                                </StatusBadge>
                            </div>
                        </template>

                        <template #cell(coordinates)="{ item }">
                            <span class="font-mono text-xs text-ink-muted">
                                {{ item.location_lat && item.location_lng ? `${Number(item.location_lat).toFixed(4)}, ${Number(item.location_lng).toFixed(4)}` : '-' }}
                            </span>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
