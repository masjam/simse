<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    exam: { type: Object, required: true },
    attempt: { type: Object, required: true },
    remainingSeconds: { type: Number, required: true },
    questions: { type: Array, default: () => [] },
});

const currentIndex = ref(0);
const secondsLeft = ref(props.remainingSeconds);
const violationCount = ref(props.attempt.violations_count || 0);
const isFullscreen = ref(false);
const savingState = ref('Siap');
const antiCheatWarning = ref('');
const submitting = ref(false);

const answers = reactive({});
props.questions.forEach((question) => {
    const saved = props.attempt.answers?.[question.id] || {};
    answers[question.id] = {
        option_id: saved.option_id || null,
        text_answer: saved.text_answer || '',
    };
});

const currentQuestion = computed(() => props.questions[currentIndex.value] || null);

const answeredCount = computed(() => {
    return props.questions.filter((q) => {
        const ans = answers[q.id];
        return Boolean(ans?.option_id || (ans?.text_answer && ans.text_answer.trim() !== ''));
    }).length;
});

const formattedTimer = computed(() => {
    const total = Math.max(0, secondsLeft.value);
    const hrs = Math.floor(total / 3600);
    const mins = Math.floor((total % 3600) / 60);
    const secs = total % 60;
    const pad = (val) => String(val).padStart(2, '0');
    return hrs > 0 ? `${pad(hrs)}:${pad(mins)}:${pad(secs)}` : `${pad(mins)}:${pad(secs)}`;
});

const saveAnswerToServer = async (questionId) => {
    if (props.attempt.is_finished || submitting.value) {
        return;
    }

    savingState.value = 'Menyimpan...';
    try {
        const response = await window.axios.post(route('siswa.cbt.answer', props.exam.id), {
            question_id: questionId,
            option_id: answers[questionId]?.option_id || null,
            text_answer: answers[questionId]?.text_answer || null,
        });

        if (typeof response.data.remaining_seconds === 'number') {
            secondsLeft.value = response.data.remaining_seconds;
        }
        savingState.value = 'Tersimpan';
    } catch (error) {
        savingState.value = 'Gagal simpan';
        if (error.response?.data?.forced_submit) {
            router.reload();
        }
    }
};

const selectOption = (questionId, optionId) => {
    answers[questionId].option_id = optionId;
    saveAnswerToServer(questionId);
};

let essayTimer = null;
const onEssayInput = (questionId) => {
    clearTimeout(essayTimer);
    essayTimer = setTimeout(() => {
        saveAnswerToServer(questionId);
    }, 500);
};

const submitFinalExam = (isForced = false) => {
    if (submitting.value || props.attempt.is_finished) {
        return;
    }

    submitting.value = true;
    router.post(
        route('siswa.cbt.submit', props.exam.id),
        {
            answers,
            is_forced_submit: isForced,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
};

const recordViolation = async (type, message) => {
    if (props.attempt.is_finished || submitting.value) {
        return;
    }

    antiCheatWarning.value = message;

    try {
        const response = await window.axios.post(route('siswa.cbt.violation', props.exam.id), { type });
        violationCount.value = response.data.violation_count;

        if (response.data.forced_submit) {
            antiCheatWarning.value = 'Batas pelanggaran tercapai. Ujian dikumpulkan otomatis!';
            router.reload();
        }
    } catch {
        // Ignore transient network errors
    }
};

const enterFullscreen = async () => {
    try {
        if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
            isFullscreen.value = true;
        }
    } catch {
        isFullscreen.value = false;
    }
};

const handleContextMenu = (event) => {
    if (!props.attempt.is_finished) {
        event.preventDefault();
        antiCheatWarning.value = 'Klik kanan dinonaktifkan selama ujian berlangsung.';
    }
};

const handleVisibilityChange = () => {
    if (document.hidden && !props.attempt.is_finished) {
        recordViolation(
            'Tab Switch',
            'Peringatan Anti-Cheat: Anda terdeteksi berpindah tab atau meminimalkan browser!'
        );
    }
};

const handleFullscreenChange = () => {
    const active = Boolean(document.fullscreenElement);
    if (isFullscreen.value && !active && !props.attempt.is_finished) {
        recordViolation(
            'Keluar Layar Penuh',
            'Peringatan Anti-Cheat: Anda keluar dari mode layar penuh!'
        );
    }
    isFullscreen.value = active;
};

let countdownInterval = null;

onMounted(() => {
    if (props.attempt.is_finished) {
        return;
    }

    document.addEventListener('contextmenu', handleContextMenu);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    document.addEventListener('fullscreenchange', handleFullscreenChange);

    countdownInterval = setInterval(() => {
        if (secondsLeft.value > 0) {
            secondsLeft.value -= 1;
        }
        if (secondsLeft.value <= 0) {
            clearInterval(countdownInterval);
            submitFinalExam(true);
        }
    }, 1000);
});

onBeforeUnmount(() => {
    clearInterval(countdownInterval);
    clearTimeout(essayTimer);
    document.removeEventListener('contextmenu', handleContextMenu);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});
</script>

<template>
    <Head :title="`CBT: ${exam.title}`" />

    <StudentLayout>
        <template #header>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-800 truncate">{{ exam.title }}</h1>
                <p class="text-xs text-slate-500">{{ exam.subject }} &bull; {{ student.name }}</p>
            </div>
        </template>

        <div class="space-y-5 select-none">
            <!-- Finished Summary View -->
            <div v-if="attempt.is_finished" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-2xl">
                    &#10003;
                </div>
                <h2 class="text-xl font-black text-slate-900">Ujian Telah Diselesaikan</h2>
                <p class="text-sm text-slate-600">
                    Dikumpulkan pada {{ attempt.finished_at }}
                    <span v-if="attempt.is_forced_submit" class="text-rose-600 font-bold">
                        (Pengumpulan Otomatis / Paksa)
                    </span>
                </p>

                <div class="max-w-md mx-auto grid grid-cols-2 gap-3 pt-2">
                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                        <p class="text-xs text-indigo-700 font-semibold">Nilai PG & Benar/Salah</p>
                        <p class="text-3xl font-black text-indigo-900 mt-1">{{ attempt.score ?? 0 }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <p class="text-xs text-slate-500 font-semibold">Catatan Pelanggaran</p>
                        <p class="text-3xl font-black text-slate-800 mt-1">
                            {{ attempt.violations_count }} / {{ exam.max_violations }}
                        </p>
                    </div>
                </div>

                <p v-if="exam.has_essay" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3 max-w-md mx-auto">
                    Ujian ini memiliki soal Esai. Nilai akhir akan disesuaikan setelah diperiksa oleh guru mata pelajaran.
                </p>

                <div class="pt-2">
                    <Link
                        :href="route('siswa.cbt')"
                        class="inline-block px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold"
                    >
                        Kembali ke Daftar Ujian
                    </Link>
                </div>
            </div>

            <!-- Active CBT Runner -->
            <div v-else class="space-y-4">
                <!-- Status & Timer Bar -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-3 sticky top-16 z-20">
                    <div class="flex items-center gap-3">
                        <div class="px-3.5 py-2 rounded-xl bg-indigo-600 text-white font-mono font-black text-base">
                            {{ formattedTimer }}
                        </div>
                        <div class="text-xs">
                            <p class="font-bold text-slate-800">Terjawab: {{ answeredCount }} / {{ questions.length }}</p>
                            <p class="text-slate-500">Status Simpan: {{ savingState }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="px-3 py-1.5 rounded-xl text-xs font-extrabold"
                            :class="violationCount > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                        >
                            Pelanggaran: {{ violationCount }} / {{ exam.max_violations }}
                        </span>

                        <button
                            v-if="!isFullscreen"
                            type="button"
                            class="px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs font-bold"
                            @click="enterFullscreen"
                        >
                            Mode Layar Penuh
                        </button>

                        <button
                            type="button"
                            :disabled="submitting"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow disabled:opacity-60"
                            @click="submitFinalExam(false)"
                        >
                            {{ submitting ? 'Mengumpulkan...' : 'Selesai & Kumpulkan' }}
                        </button>
                    </div>
                </div>

                <!-- Anti-Cheat Warning Alert -->
                <div
                    v-if="antiCheatWarning"
                    class="p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs sm:text-sm font-bold"
                >
                    {{ antiCheatWarning }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                    <!-- Question Panel -->
                    <div v-if="currentQuestion" class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <span class="px-3 py-1 rounded-lg bg-sky-100 text-sky-800 text-xs font-extrabold">
                                Soal Nomor {{ currentIndex + 1 }} ({{ currentQuestion.type }})
                            </span>
                            <span class="text-xs font-bold text-slate-500">Bobot: {{ currentQuestion.marks }} poin</span>
                        </div>

                        <div class="text-sm sm:text-base font-semibold text-slate-900 leading-relaxed whitespace-pre-line">
                            {{ currentQuestion.content }}
                        </div>

                        <!-- Pilihan Ganda / Benar Salah -->
                        <div
                            v-if="currentQuestion.type === 'Pilihan Ganda' || currentQuestion.type === 'Benar Salah'"
                            class="space-y-2.5"
                        >
                            <button
                                v-for="(option, optIndex) in currentQuestion.options"
                                :key="option.id"
                                type="button"
                                class="w-full text-left p-3.5 rounded-xl border transition flex items-center gap-3"
                                :class="answers[currentQuestion.id]?.option_id === option.id
                                    ? 'border-indigo-600 bg-indigo-50 text-indigo-950 font-bold ring-2 ring-indigo-200'
                                    : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                @click="selectOption(currentQuestion.id, option.id)"
                            >
                                <span
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0"
                                    :class="answers[currentQuestion.id]?.option_id === option.id
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-slate-100 text-slate-600'"
                                >
                                    {{ String.fromCharCode(65 + optIndex) }}
                                </span>
                                <span class="text-sm">{{ option.content }}</span>
                            </button>
                        </div>

                        <!-- Esai -->
                        <div v-else class="space-y-2">
                            <label class="block text-xs font-bold text-slate-600">Jawaban Esai Anda:</label>
                            <textarea
                                v-model="answers[currentQuestion.id].text_answer"
                                rows="6"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ketik jawaban esai Anda dengan jelas..."
                                @input="onEssayInput(currentQuestion.id)"
                                @blur="saveAnswerToServer(currentQuestion.id)"
                            ></textarea>
                        </div>

                        <!-- Prev / Next -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                :disabled="currentIndex === 0"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                                @click="currentIndex -= 1"
                            >
                                &larr; Soal Sebelumnya
                            </button>

                            <button
                                v-if="currentIndex < questions.length - 1"
                                type="button"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold"
                                @click="currentIndex += 1"
                            >
                                Soal Berikutnya &rarr;
                            </button>
                            <button
                                v-else
                                type="button"
                                :disabled="submitting"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold"
                                @click="submitFinalExam(false)"
                            >
                                Kumpulkan Ujian
                            </button>
                        </div>
                    </div>

                    <!-- Question Navigation Grid -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm h-fit space-y-3">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Navigasi Nomor Soal</h3>
                        <div class="grid grid-cols-5 gap-2">
                            <button
                                v-for="(q, idx) in questions"
                                :key="q.id"
                                type="button"
                                class="h-10 rounded-xl text-xs font-extrabold transition border"
                                :class="[
                                    idx === currentIndex ? 'ring-2 ring-indigo-500 border-indigo-600' : 'border-slate-200',
                                    answers[q.id]?.option_id || (answers[q.id]?.text_answer && answers[q.id].text_answer.trim() !== '')
                                        ? 'bg-emerald-500 text-white border-emerald-600'
                                        : 'bg-slate-50 text-slate-700'
                                ]"
                                @click="currentIndex = idx"
                            >
                                {{ idx + 1 }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

