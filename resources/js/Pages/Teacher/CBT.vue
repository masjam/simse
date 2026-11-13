<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import CreateExamModal from './Partials/CreateExamModal.vue';
import AddQuestionModal from './Partials/AddQuestionModal.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    exams: { type: Array, default: () => [] },
});

const toast = useToast();
const showCreateExam = ref(false);
const showAddQuestion = ref(false);
const activeExam = ref(null);

const toggleStatus = (examId) => {
    router.patch(route('guru.cbt.exam.toggle', examId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Status aktif ujian CBT berhasil diperbarui.');
        },
    });
};

const openAddQuestion = (exam) => {
    activeExam.value = exam;
    showAddQuestion.value = true;
};
</script>

<template>
    <Head title="Bank Soal & CBT" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="Bank Soal & Ujian CBT"
                description="Computer Based Test dengan Anti-Cheat Engine SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Header Action Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-ink">Daftar Paket Ujian Online CBT</h3>
                    <p class="text-xs text-ink-muted">Kelola butir soal, aktifkan sesi ujian, dan pantau pengawasan anti-kecurangan.</p>
                </div>
                <AppButton size="sm" variant="primary" @click="showCreateExam = true">
                    + Jadwalkan Ujian Baru
                </AppButton>
            </div>

            <!-- Exams Grid / Empty State -->
            <div v-if="exams.length === 0">
                <EmptyState
                    title="Belum Ada Paket Ujian"
                    description="Belum ada ujian CBT yang dijadwalkan. Silakan buat paket ujian baru."
                    action-label="+ Jadwalkan Ujian Baru"
                    @action="showCreateExam = true"
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                    v-for="exam in exams"
                    :key="exam.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col justify-between gap-3"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-control bg-brand-50 text-brand-700 text-xs font-bold">
                                {{ exam.subject }}
                            </span>
                            <StatusBadge :status="exam.status === 'active' ? 'Aktif' : (exam.status === 'completed' ? 'Selesai' : 'Terjadwal')" />
                        </div>

                        <h4 class="text-sm font-bold text-ink">{{ exam.title }}</h4>
                        <p v-if="exam.description" class="text-xs text-ink-muted leading-relaxed line-clamp-2">
                            {{ exam.description }}
                        </p>

                        <div class="grid grid-cols-3 gap-2 pt-1 text-xs">
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Durasi</span>
                                <span class="font-bold font-mono text-ink">{{ exam.duration_minutes }} Mnt</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Soal</span>
                                <span class="font-bold font-mono text-ink">{{ exam.questions_count || 0 }} Butir</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Max Pelanggaran</span>
                                <span class="font-bold font-mono text-ink">{{ exam.max_violations }}x</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-ink-muted pt-1">
                            Akses: {{ exam.start_time }} s/d {{ exam.end_time }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-line flex items-center justify-between">
                        <AppButton size="sm" variant="secondary" @click="openAddQuestion(exam)">
                            + Butir Soal
                        </AppButton>
                        <AppButton
                            size="sm"
                            :variant="exam.is_active ? 'danger' : 'primary'"
                            @click="toggleStatus(exam.id)"
                        >
                            {{ exam.is_active ? 'Tutup Ujian' : 'Buka Ujian' }}
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <CreateExamModal
            :show="showCreateExam"
            :subjects="subjects"
            @close="showCreateExam = false"
        />

        <AddQuestionModal
            :show="showAddQuestion"
            :exam="activeExam"
            @close="showAddQuestion = false"
        />
    </PortalShell>
</template>
