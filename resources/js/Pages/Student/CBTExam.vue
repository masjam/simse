<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import ExamOptionButton from '@/Components/domain/ExamOptionButton.vue';
import ExamFinishedCard from './Partials/ExamFinishedCard.vue';
import ExamTimerBar from './Partials/ExamTimerBar.vue';

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
const fontSize = ref('md'); // 'sm' | 'md' | 'lg'

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
    if (props.attempt.is_finished || submitting.value) return;

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
const onEssayInput = (questionId, value) => {
    answers[questionId].text_answer = value;
    clearTimeout(essayTimer);
    essayTimer = setTimeout(() => {
        saveAnswerToServer(questionId);
    }, 500);
};

const submitFinalExam = (isForced = false) => {
    if (submitting.value || props.attempt.is_finished) return;

    submitting.value = true;
    router.post(
        route('siswa.cbt.submit', props.exam.id),
        { answers, is_forced_submit: isForced },
        {
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
};

const recordViolation = async (type, message) => {
    if (props.attempt.is_finished || submitting.value) return;

    antiCheatWarning.value = message;
    try {
        const response = await window.axios.post(route('siswa.cbt.violation', props.exam.id), { type });
        violationCount.value = response.data.violation_count;

        if (response.data.forced_submit) {
            antiCheatWarning.value = 'Batas pelanggaran tercapai. Ujian dikumpulkan otomatis!';
            router.reload();
        }
    } catch {
        // ignore network error
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

const handleContextMenu = (e) => {
    if (!props.attempt.is_finished) {
        e.preventDefault();
        antiCheatWarning.value = 'Klik kanan dinonaktifkan selama ujian berlangsung.';
    }
};

const handleVisibilityChange = () => {
    if (document.hidden && !props.attempt.is_finished) {
        recordViolation('Tab Switch', 'Peringatan Anti-Cheat: Anda terdeteksi berpindah tab atau aplikasi!');
    }
};

const handleFullscreenChange = () => {
    const active = Boolean(document.fullscreenElement);
    if (isFullscreen.value && !active && !props.attempt.is_finished) {
        recordViolation('Keluar Layar Penuh', 'Peringatan Anti-Cheat: Anda keluar dari mode layar penuh!');
    }
    isFullscreen.value = active;
};

let countdownInterval = null;

onMounted(() => {
    if (props.attempt.is_finished) return;

    document.addEventListener('contextmenu', handleContextMenu);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    document.addEventListener('fullscreenchange', handleFullscreenChange);

    countdownInterval = setInterval(() => {
        if (secondsLeft.value > 0) secondsLeft.value -= 1;
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
    <Head :title="`Ujian CBT: ${exam.title}`" />

    <PortalShell variant="bare">
        <div class="max-w-5xl mx-auto px-4 py-4 space-y-4 select-none">
            <!-- 1. Finished View -->
            <ExamFinishedCard
                v-if="attempt.is_finished"
                :exam="exam"
                :attempt="attempt"
            />

            <!-- 2. Active Exam Runner -->
            <div v-else class="space-y-4">
                <!-- Timer Bar with Controls -->
                <ExamTimerBar
                    :formatted-timer="formattedTimer"
                    :answered-count="answeredCount"
                    :total-questions="questions.length"
                    :saving-state="savingState"
                    :violation-count="violationCount"
                    :max-violations="exam.max_violations"
                    :is-fullscreen="isFullscreen"
                    :submitting="submitting"
                    :font-size="fontSize"
                    @toggle-fullscreen="enterFullscreen"
                    @submit-exam="submitFinalExam(false)"
                    @change-font-size="fontSize = $event"
                />

                <!-- Anti-Cheat Alert -->
                <div
                    v-if="antiCheatWarning"
                    class="p-3 rounded-control bg-status-danger-bg border border-status-danger-line text-status-danger-fg text-xs font-bold"
                >
                    {{ antiCheatWarning }}
                </div>

                <!-- Question Area & Navigation Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <!-- Main Question Panel -->
                    <div
                        v-if="currentQuestion"
                        class="lg:col-span-3 bg-surface rounded-card border border-line p-5 sm:p-6 shadow-card space-y-5"
                    >
                        <div class="flex items-center justify-between border-b border-line pb-3">
                            <span class="px-2.5 py-1 rounded-control bg-brand-50 text-brand-700 text-xs font-bold font-mono">
                                Soal Nomor {{ currentIndex + 1 }} &bull; {{ currentQuestion.type }}
                            </span>
                            <span class="text-xs text-ink-muted font-bold">
                                Bobot: {{ currentQuestion.marks }} Poin
                            </span>
                        </div>

                        <!-- Question Content with Dynamic Font Size -->
                        <div
                            class="text-ink leading-relaxed whitespace-pre-line font-medium"
                            :class="{
                                'text-xs sm:text-sm': fontSize === 'sm',
                                'text-sm sm:text-base': fontSize === 'md',
                                'text-base sm:text-lg': fontSize === 'lg',
                            }"
                        >
                            {{ currentQuestion.content }}
                        </div>

                        <!-- Options for Pilihan Ganda / Benar Salah -->
                        <div
                            v-if="currentQuestion.type === 'Pilihan Ganda' || currentQuestion.type === 'Benar Salah'"
                            class="space-y-2.5 pt-2"
                        >
                            <ExamOptionButton
                                v-for="(option, optIndex) in currentQuestion.options"
                                :key="option.id"
                                :selected="answers[currentQuestion.id]?.option_id === option.id"
                                :letter="String.fromCharCode(65 + optIndex)"
                                :text="option.content"
                                :font-size="fontSize"
                                @click="selectOption(currentQuestion.id, option.id)"
                            />
                        </div>

                        <!-- Essay Input Box -->
                        <div v-else class="space-y-2 pt-2">
                            <label class="block text-xs font-bold text-ink-muted">Jawaban Esai Anda:</label>
                            <AppTextarea
                                :model-value="answers[currentQuestion.id].text_answer"
                                :rows="6"
                                placeholder="Tuliskan jawaban esai Anda secara lengkap dan jelas..."
                                @update:model-value="onEssayInput(currentQuestion.id, $event)"
                                @blur="saveAnswerToServer(currentQuestion.id)"
                            />
                        </div>

                        <!-- Prev / Next Navigation Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t border-line">
                            <AppButton
                                size="sm"
                                variant="secondary"
                                :disabled="currentIndex === 0"
                                @click="currentIndex = Math.max(0, currentIndex - 1)"
                            >
                                &larr; Sebelumnya
                            </AppButton>
                            <AppButton
                                size="sm"
                                variant="primary"
                                :disabled="currentIndex >= questions.length - 1"
                                @click="currentIndex = Math.min(questions.length - 1, currentIndex + 1)"
                            >
                                Selanjutnya &rarr;
                            </AppButton>
                        </div>
                    </div>

                    <!-- Right Column: Question Numbers Grid -->
                    <div class="bg-surface rounded-card border border-line p-4 shadow-card space-y-3 h-fit">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                            Daftar Nomor Soal
                        </h4>
                        <div class="grid grid-cols-5 gap-1.5">
                            <AppButton
                                v-for="(q, idx) in questions"
                                :key="q.id"
                                size="sm"
                                :variant="Boolean(answers[q.id]?.option_id || (answers[q.id]?.text_answer && answers[q.id].text_answer.trim() !== '')) ? 'primary' : 'secondary'"
                                class="!h-9 !p-0 justify-center font-mono font-bold text-xs"
                                :class="{
                                    'ring-2 ring-brand-500 font-black': currentIndex === idx,
                                }"
                                @click="currentIndex = idx"
                            >
                                {{ idx + 1 }}
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
