<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

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
    inputEl.value?.focus();
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
</script>

<template>
    <Head title="Kiosk Presensi Siswa (RFID)" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-teal-950 to-emerald-950 text-white p-6 sm:p-10 flex flex-col justify-between" @click="focusInput">
        <header class="max-w-6xl w-full mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/15 pb-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-300">SD Muhammadiyah Al Mujahidin Wonosari</p>
                <h1 class="text-2xl sm:text-3xl font-black mt-1">Kiosk Presensi Siswa RFID</h1>
                <p class="text-xs text-slate-300 mt-1">
                    Perangkat: <strong>{{ device.name }}</strong> ({{ device.mac_address }}) &middot; {{ device.location }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-3xl sm:text-4xl font-black font-mono text-amber-300">{{ nowString }}</p>
                <p class="text-xs text-slate-300">
                    {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                </p>
            </div>
        </header>

        <main class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 my-8">
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-3xl p-6 sm:p-8 flex flex-col justify-center space-y-5">
                <div>
                    <h2 class="text-xl font-extrabold">Tempelkan Kartu Siswa (RFID)</h2>
                    <p class="text-sm text-emerald-100/80 mt-1">Reader USB otomatis membaca kartu saat ditempelkan.</p>
                </div>

                <form class="flex gap-2" @submit.prevent="submitTap">
                    <input
                        ref="inputEl"
                        v-model="rfidInput"
                        type="text"
                        autofocus
                        class="flex-1 rounded-2xl bg-slate-900/80 border-white/20 text-white font-mono text-lg px-4 py-3 focus:ring-emerald-400"
                        placeholder="Tap kartu RFID di sini..."
                    />
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm transition"
                    >
                        Tap
                    </button>
                </form>

                <div v-if="errorMessage" class="p-4 rounded-2xl bg-rose-500/20 border border-rose-400/40 text-rose-100 text-sm font-bold">
                    {{ errorMessage }}
                </div>

                <div v-if="lastResult" class="p-5 rounded-2xl bg-emerald-500/20 border border-emerald-400/40 space-y-1.5">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">
                        Presensi {{ lastResult.record?.mode }} Berhasil
                    </p>
                    <p class="text-xl font-black">{{ lastResult.record?.student_name }}</p>
                    <p class="text-sm text-emerald-100">
                        Kelas {{ lastResult.record?.class_name }} &middot; Status: <strong>{{ lastResult.record?.attendance_status }}</strong>
                    </p>
                    <p class="text-xs text-emerald-200">{{ lastResult.message }}</p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-3xl p-6 space-y-4">
                <h2 class="text-lg font-extrabold">Presensi Terbaru Hari Ini</h2>
                <div v-if="attendances.length === 0" class="py-10 text-center text-sm text-slate-300">
                    Belum ada siswa yang tap kartu hari ini.
                </div>
                <div v-else class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    <div
                        v-for="item in attendances"
                        :key="item.id"
                        class="p-3.5 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between gap-3"
                    >
                        <div>
                            <p class="font-bold text-sm">{{ item.student_name }}</p>
                            <p class="text-xs text-slate-300">Kelas {{ item.class_name }} &middot; {{ item.attendance_status || item.status }}</p>
                        </div>
                        <div class="text-right text-xs font-mono">
                            <p class="text-emerald-300">Masuk: {{ item.time_in }}</p>
                            <p v-if="item.time_out" class="text-amber-300">Pulang: {{ item.time_out }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="max-w-6xl w-full mx-auto text-center text-xs text-slate-400">
            SIMSE &middot; Kiosk Terkunci Perangkat Terotorisasi
        </footer>
    </div>
</template>

