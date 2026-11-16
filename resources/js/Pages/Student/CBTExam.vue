<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import ExamFinishedCard from './Partials/ExamFinishedCard.vue';
import ExamTimerBar from './Partials/ExamTimerBar.vue';
import MultipleChoiceQuestion from './Partials/Questions/MultipleChoiceQuestion.vue';
import ComplexChoiceQuestion from './Partials/Questions/ComplexChoiceQuestion.vue';
import TrueFalseQuestion from './Partials/Questions/TrueFalseQuestion.vue';
import MatchingQuestion from './Partials/Questions/MatchingQuestion.vue';
import FillInBlankQuestion from './Partials/Questions/FillInBlankQuestion.vue';
import EssayQuestion from './Partials/Questions/EssayQuestion.vue';

const props = defineProps({
    student: { type: Object, required: true },
    studentLevel: { type: Number, default: 4 },
    exam: { type: Object, required: true },
    attempt: { type: Object, required: true },
    remainingSeconds: { type: Number, required: true },
    serverNow: { type: String, default: null },
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
const childMode = ref(props.studentLevel <= 3);

// Initialize answers reactive state
const answers = reactive({});
props.questions.forEach((q) => {
    const saved = props.attempt.answers?.[q.id] || {};
    answers[q.id] = {
        option_id: saved.option_id || null,
        option_ids: saved.option_ids || [],
        pairs: saved.pairs || {},
        blanks: saved.blanks || [],
        text_answer: saved.text_answer || '',
    };
});

const currentQuestion = computed(() => props.questions[currentIndex.value] || null);

const isQuestionAnswered = (qId) => {
    const ans = answers[qId];
    if (!ans) return false;
    if (ans.option_id) return true;
    if (Array.isArray(ans.option_ids) && ans.option_ids.length > 0) return true;
    if (ans.pairs && Object.keys(ans.pairs).length > 0 && Object.values(ans.pairs).some(Boolean)) return true;
    if (Array.isArray(ans.blanks) && ans.blanks.some((b) => b && String(b).trim() !== '')) return true;
    if (ans.text_answer && ans.text_answer.trim() !== '') return true;
    return false;
};

const answeredCount = computed(() => {
    return props.questions.filter((q) => isQuestionAnswered(q.id)).length;
});

const formattedTimer = computed(() => {
    const total = Math.max(0, secondsLeft.value);
    const hrs = Math.floor(total / 3600);
    const mins = Math.floor((total % 3600) / 60);
    const secs = total % 60;
    const pad = (val) => String(val).padStart(2, '0');
    return hrs > 0 ? `${pad(hrs)}:${pad(mins)}:${pad(secs)}` : `${pad(mins)}:${pad(secs)}`;
});

// Offline resilience & LocalStorage caching
const localCacheKey = `simse_cbt_attempt_${props.attempt.id}`;

const saveToLocalStorage = () => {
    try {
        localStorage.setItem(localCacheKey, JSON.stringify({
            answers,
            timestamp: new Date().toISOString(),
        }));
    } catch {
        // storage quota fallback
    }
};

const syncPendingAnswers = async () => {
    if (!navigator.onLine || props.attempt.is_finished) return;
    try {
        const cached = localStorage.getItem(localCacheKey);
        if (cached) {
            savingState.value = 'Sinkronisasi...';
            // Trigger auto-save of current state
            if (currentQuestion.value) {
                await saveAnswerToServer(currentQuestion.value.id);
            }
        }
    } catch {
        // ignore
    }
};

let debounceSaveTimer = null;
const saveAnswerToServer = async (questionId) => {
    if (props.attempt.is_finished || submitting.value) return;

    saveToLocalStorage();
    savingState.value = 'Menyimpan...';

    if (!navigator.onLine) {
        savingState.value = 'Tersimpan lokal (Offline)';
        return;
    }

    try {
        const payload = {
            question_id: questionId,
            option_id: answers[questionId]?.option_id || null,
            option_ids: answers[questionId]?.option_ids || null,
            pairs: answers[questionId]?.pairs || null,
            blanks: answers[questionId]?.blanks || null,
            text_answer: answers[questionId]?.text_answer || null,
        };

        const response = await window.axios.post(route('siswa.cbt.answer', props.exam.id), payload);

        if (typeof response.data.remaining_seconds === 'number') {
            secondsLeft.value = response.data.remaining_seconds;
        }
        savingState.value = 'Tersimpan';
    } catch (error) {
        if (error.response?.data?.forced_submit) {
            router.reload();
        } else {
            savingState.value = 'Menunggu jaringan';
        }
    }
};

const triggerDebouncedSave = (questionId) => {
    clearTimeout(debounceSaveTimer);
    debounceSaveTimer = setTimeout(() => {
        saveAnswerToServer(questionId);
    }, 400);
};

// Handlers per question type
const onSingleSelect = (questionId, optionId) => {
    answers[questionId].option_id = optionId;
    saveAnswerToServer(questionId);
};

const onComplexSelect = (questionId, optionIds) => {
    answers[questionId].option_ids = optionIds;
    saveAnswerToServer(questionId);
};

const onMatchingChange = (questionId, pairsData) => {
    answers[questionId].pairs = pairsData;
    saveAnswerToServer(questionId);
};

const onBlanksChange = (questionId, blanksData) => {
    answers[questionId].blanks = blanksData;
    triggerDebouncedSave(questionId);
};

const onEssayChange = (questionId, text) => {
    answers[questionId].text_answer = text;
    triggerDebouncedSave(questionId);
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
                try {
                    localStorage.removeItem(localCacheKey);
                } catch {
                    // ignore
                }
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
        // ignore
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
    window.addEventListener('online', syncPendingAnswers);

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
    clearTimeout(debounceSaveTimer);
    document.removeEventListener('contextmenu', handleContextMenu);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    window.removeEventListener('online', syncPendingAnswers);
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
                    :child-mode="childMode"
                    @toggle-fullscreen="enterFullscreen"
                    @submit-exam="submitFinalExam(false)"
                    @change-font-size="fontSize = $event"
                    @toggle-child-mode="childMode = !childMode"
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
                        <div class="flex items-center justify-between border-b border-line pb-3 flex-wrap gap-2">
                            <span class="px-2.5 py-1 rounded-control bg-brand-50 text-brand-700 text-xs font-bold font-mono">
                                Soal Nomor {{ currentIndex + 1 }} &bull; {{ currentQuestion.type }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span v-if="childMode" class="px-2 py-0.5 rounded-full bg-status-warning-bg text-status-warning-fg border border-status-warning-line text-[10px] font-bold">
                                    Mode Anak
                                </span>
                                <span class="text-xs text-ink-muted font-bold font-mono">
                                    Bobot: {{ currentQuestion.marks }} Poin
                                </span>
                            </div>
                        </div>

                        <!-- Question Image Attachment -->
                        <div v-if="currentQuestion.image_url" class="rounded-card border border-line p-2 bg-surface-raised flex justify-center">
                            <img
                                :src="currentQuestion.image_url"
                                alt="Gambar Soal"
                                class="max-h-64 rounded-control object-contain"
                            />
                        </div>

                        <!-- Question Content with Dynamic Font Size -->
                        <div
                            class="text-ink leading-relaxed whitespace-pre-line font-medium"
                            :class="{
                                'text-xs sm:text-sm': fontSize === 'sm' && !childMode,
                                'text-sm sm:text-base': fontSize === 'md' && !childMode,
                                'text-base sm:text-lg': fontSize === 'lg' || childMode,
                            }"
                        >
                            {{ currentQuestion.content }}
                        </div>

                        <!-- Interactive Question Renderers -->
                        <div class="pt-2">
                            <!-- 1. Pilihan Ganda -->
                            <MultipleChoiceQuestion
                                v-if="currentQuestion.type === 'Pilihan Ganda'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.option_id"
                                :child-mode="childMode"
                                @update:model-value="onSingleSelect(currentQuestion.id, $event)"
                            />

                            <!-- 2. Ganda Kompleks -->
                            <ComplexChoiceQuestion
                                v-else-if="currentQuestion.type === 'Ganda Kompleks'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.option_ids || []"
                                :child-mode="childMode"
                                @update:model-value="onComplexSelect(currentQuestion.id, $event)"
                            />

                            <!-- 3. Benar Salah -->
                            <TrueFalseQuestion
                                v-else-if="currentQuestion.type === 'Benar Salah'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.option_id"
                                :child-mode="childMode"
                                @update:model-value="onSingleSelect(currentQuestion.id, $event)"
                            />

                            <!-- 4. Menjodohkan -->
                            <MatchingQuestion
                                v-else-if="currentQuestion.type === 'Menjodohkan'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.pairs || {}"
                                :child-mode="childMode"
                                @update:model-value="onMatchingChange(currentQuestion.id, $event)"
                            />

                            <!-- 5. Isian Rumpang -->
                            <FillInBlankQuestion
                                v-else-if="currentQuestion.type === 'Isian Rumpang'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.blanks || []"
                                :child-mode="childMode"
                                @update:model-value="onBlanksChange(currentQuestion.id, $event)"
                            />

                            <!-- 6. Esai -->
                            <EssayQuestion
                                v-else-if="currentQuestion.type === 'Esai'"
                                :question="currentQuestion"
                                :model-value="answers[currentQuestion.id]?.text_answer || ''"
                                :child-mode="childMode"
                                @update:model-value="onEssayChange(currentQuestion.id, $event)"
                            />
                        </div>

                        <!-- Prev / Next Navigation Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t border-line">
                            <AppButton
                                :size="childMode ? 'md' : 'sm'"
                                variant="secondary"
                                :disabled="currentIndex === 0"
                                @click="currentIndex = Math.max(0, currentIndex - 1)"
                            >
                                &larr; Sebelumnya
                            </AppButton>
                            <AppButton
                                :size="childMode ? 'md' : 'sm'"
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
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-ink-muted">
                                Nomor Soal
                            </h4>
                            <span class="text-xs font-mono font-bold text-ink">
                                {{ answeredCount }}/{{ questions.length }}
                            </span>
                        </div>

                        <div class="grid grid-cols-5 gap-1.5 max-h-72 overflow-y-auto p-0.5">
                            <AppButton
                                v-for="(q, idx) in questions"
                                :key="q.id"
                                size="sm"
                                :variant="isQuestionAnswered(q.id) ? 'primary' : 'secondary'"
                                class="!h-9 !p-0 justify-center font-mono font-bold text-xs"
                                :class="{ 'ring-2 ring-brand-500 ring-offset-1 font-black': currentIndex === idx }"
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
