<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    device: { type: Object, required: true },
    recentAttendances: { type: Array, required: true },
});

const rfidInput = ref('');
const inputEl = ref(null);
const attendances = ref([...props.recentAttendances]);
const lastResult = ref(null);
const errorMessage = ref('');
const processing = ref(false);
const nowString = ref('');

let clockTimer = null;

const updateClock = () => {
    nowString.value = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const focusInput = () => {
    inputEl.value?.$el?.focus?.() || inputEl.value?.focus?.();
};

onMounted(() => {
    updateClock();
    clockTimer = setInterval(updateClock, 1000);
    focusInput();
});

onUnmounted(() => {
    if (clockTimer) {
        clearInterval(clockTimer);
    }
});

const submitTap = async () => {
    const uid = rfidInput.value.trim();
    if (!uid || processing.value) {
        return;
    }

    processing.value = true;
    errorMessage.value = '';

    try {
        const { data } = await axios.post(route('kiosk.presensi.tap'), { rfid_uid: uid });
        lastResult.value = data;
        if (data.record && data.record.id) {
            attendances.value = [
                data.record,
                ...attendances.value.filter((item) => item.id !== data.record.id),
            ].slice(0, 12);
        }
    } catch (error) {
        lastResult.value = null;
        errorMessage.value = error.response?.data?.message || 'Gagal memproses kartu RFID.';
    } finally {
        rfidInput.value = '';
        processing.value = false;
        focusInput();
    }
};

const statusVariant = (status) => {
    if (status === 'Hadir') return 'success';
    if (status === 'Terlambat') return 'warning';
    return 'danger';
};
</script>

<template>
    <Head title="Kiosk Presensi Siswa (RFID)" />

    <PortalShell variant="bare">
        <div class="min-h-screen bg-surface-sunken p-6 sm:p-10 flex flex-col justify-between" @click="focusInput">
            <!-- Header -->
            <header class="max-w-6xl w-full mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-line pb-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-brand-700">SD Muhammadiyah Al Mujahidin Wonosari</p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink mt-1">Kiosk Presensi Siswa RFID</h1>
                    <p class="text-xs text-ink-muted mt-1">
                        Perangkat: <strong class="text-ink">{{ device.name }}</strong> ({{ device.mac_address }}) &middot; {{ device.location }}
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-3xl sm:text-4xl font-extrabold font-numeric text-brand-700">{{ nowString }}</p>
                    <p class="text-xs text-ink-muted">
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>
            </header>

            <!-- Main Workspace -->
            <main class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 my-8">
                <!-- Reader Card -->
                <AppCard class="flex flex-col justify-center space-y-5">
                    <div>
                        <h2 class="text-xl font-bold text-ink">Tempelkan Kartu Siswa (RFID)</h2>
                        <p class="text-xs text-ink-muted mt-1">Reader USB otomatis membaca kartu saat ditempelkan.</p>
                    </div>

                    <form class="flex gap-2" @submit.prevent="submitTap">
                        <AppInput
                            ref="inputEl"
                            v-model="rfidInput"
                            type="text"
                            autofocus
                            placeholder="Tap kartu RFID di sini..."
                            class="flex-1 font-numeric text-lg tracking-wider"
                        />
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="lg"
                            :loading="processing"
                        >
                            Tap
                        </AppButton>
                    </form>

                    <div v-if="errorMessage" class="p-4 rounded-control bg-status-danger-bg text-status-danger-fg border border-status-danger-line text-xs font-bold">
                        {{ errorMessage }}
                    </div>

                    <div v-if="lastResult" class="p-5 rounded-control bg-status-success-bg border border-status-success-line text-status-success-fg space-y-1.5">
                        <p class="text-xs font-bold uppercase tracking-wider">
                            Presensi {{ lastResult.record?.mode }} Berhasil
                        </p>
                        <p class="text-xl font-bold">{{ lastResult.record?.student_name }}</p>
                        <p class="text-xs">
                            Kelas {{ lastResult.record?.class_name }} &middot; Status: <strong>{{ lastResult.record?.attendance_status }}</strong>
                        </p>
                        <p class="text-xs opacity-90">{{ lastResult.message }}</p>
                    </div>
                </AppCard>

                <!-- Recent Attendance List -->
                <AppCard class="space-y-4">
                    <h2 class="text-base font-bold text-ink">Presensi Terbaru Hari Ini</h2>
                    <div v-if="attendances.length === 0" class="py-10">
                        <EmptyState
                            title="Belum ada presensi"
                            description="Belum ada siswa yang tap kartu presensi hari ini."
                        />
                    </div>
                    <div v-else class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                        <div
                            v-for="item in attendances"
                            :key="item.id"
                            class="p-3.5 rounded-control bg-surface-raised border border-line flex items-center justify-between gap-3"
                        >
                            <div>
                                <p class="font-bold text-sm text-ink">{{ item.student_name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-ink-muted">Kelas {{ item.class_name }}</span>
                                    <StatusBadge
                                        :variant="statusVariant(item.attendance_status || item.status)"
                                        :text="item.attendance_status || item.status"
                                        size="sm"
                                    />
                                </div>
                            </div>
                            <div class="text-right text-xs font-numeric text-ink-muted">
                                <p class="text-status-success-fg font-semibold">Masuk: {{ item.time_in }}</p>
                                <p v-if="item.time_out" class="text-status-warning-fg font-semibold">Pulang: {{ item.time_out }}</p>
                            </div>
                        </div>
                    </div>
                </AppCard>
            </main>

            <!-- Footer -->
            <footer class="max-w-6xl w-full mx-auto text-center text-xs text-ink-muted">
                SIMSE &middot; Kiosk Presensi Mandiri SD Muhammadiyah Al Mujahidin
            </footer>
        </div>
    </PortalShell>
</template>
