<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import Modal from '@/Components/ui/Modal.vue';
import ScoreChip from '@/Components/domain/ScoreChip.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Avatar from '@/Components/ui/Avatar.vue';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedSemester: { type: String, default: 'Ganjil 2026/2027' },
    students: { type: Array, default: () => [] },
    teacher: { type: Object, default: () => ({ name: 'Siti Guru, S.Pd.I', nip: '198407152010012015' }) },
});

const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const selectedClass = ref(props.selectedClass || classList.value[0]);
const selectedSemester = ref(props.selectedSemester || 'Ganjil 2026/2027');

const onFilterChange = () => {
    router.get(route('guru.raport'), {
        class: selectedClass.value,
        semester: selectedSemester.value,
    }, { preserveState: true, replace: true });
};

const classOptions = computed(() => classList.value.map(c => ({ value: c, label: `Kelas ${c}` })));
const semesterOptions = [
    { value: 'Ganjil 2026/2027', label: 'Ganjil 2026/2027' },
    { value: 'Genap 2025/2026', label: 'Genap 2025/2026' },
];

const showPrintModal = ref(false);
const activeStudent = ref(null);

const openPrint = (student) => {
    activeStudent.value = student;
    showPrintModal.value = true;
};

const printDoc = () => {
    window.print();
};
</script>

<template>
    <Head title="e-Raport Siswa" />

    <PortalShell variant="sidebar">
        <template #header>
            <PageHeader
                title="e-Raport Hasil Belajar"
                description="Penerbitan Capaian Kompetensi Siswa SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- Filter Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col sm:flex-row gap-3 items-end justify-between">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:w-auto flex-1">
                    <div>
                        <label for="rombel-select" class="block text-xs font-bold text-ink-muted mb-1">Rombongan Belajar</label>
                        <AppSelect
                            id="rombel-select"
                            v-model="selectedClass"
                            :options="classOptions"
                            @change="onFilterChange"
                        />
                    </div>
                    <div>
                        <label for="sem-select" class="block text-xs font-bold text-ink-muted mb-1">Semester &amp; Tahun Ajaran</label>
                        <AppSelect
                            id="sem-select"
                            v-model="selectedSemester"
                            :options="semesterOptions"
                            @change="onFilterChange"
                        />
                    </div>
                </div>

                <div v-if="students.length > 0" class="shrink-0 self-start sm:self-auto">
                    <AppButton
                        size="sm"
                        variant="primary"
                        @click="openPrint(students[0])"
                    >
                        Pratinjau Cetak e-Raport
                    </AppButton>
                </div>
            </div>

            <!-- Students Raport Cards List -->
            <div v-if="students.length === 0">
                <EmptyState
                    title="Tidak Ada Siswa"
                    description="Belum ada data siswa terdaftar pada kelas yang dipilih."
                />
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                    v-for="student in students"
                    :key="student.id"
                    class="bg-surface rounded-card border border-line p-4 shadow-card hover:bg-surface-raised/40 transition-all flex flex-col justify-between gap-3"
                >
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <Avatar :name="student.name" size="md" />
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-ink truncate">{{ student.name }}</h4>
                                    <p class="text-xs text-ink-muted">NISN: {{ student.nisn || '-' }}</p>
                                </div>
                            </div>
                            <ScoreChip :score="student.predikat" :show-score="false" />
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs pt-1">
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Nilai Akhir</span>
                                <span class="font-bold font-mono text-sm text-brand-600">{{ student.nilai_akhir ?? '-' }}</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Kehadiran</span>
                                <span class="font-bold font-mono text-sm text-ink">{{ student.kehadiran ?? '100%' }}</span>
                            </div>
                            <div class="p-2 rounded-control bg-surface-raised border border-line text-center">
                                <span class="text-[10px] text-ink-muted block">Predikat</span>
                                <span class="font-black text-sm text-ink">{{ student.predikat ?? 'A' }}</span>
                            </div>
                        </div>

                        <p v-if="student.catatan" class="text-xs text-ink-muted italic leading-relaxed line-clamp-2">
                            "{{ student.catatan }}"
                        </p>
                    </div>

                    <div class="pt-2 border-t border-line flex justify-end">
                        <AppButton size="sm" variant="secondary" @click="openPrint(student)">
                            Lihat Dokumen Raport &rarr;
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Printable Preview Modal -->
        <Modal :show="showPrintModal" title="Lembar e-Raport Resmi" max-width="2xl" @close="showPrintModal = false">
            <div v-if="activeStudent" class="space-y-4 text-xs text-ink">
                <KopSurat />

                <div class="text-center border-b border-line pb-3">
                    <h3 class="text-sm font-bold uppercase text-ink">LAPORAN HASIL BELAJAR SISWA</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Semester {{ selectedSemester }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs p-3 bg-surface-raised rounded-control border border-line">
                    <div>
                        <p><span class="text-ink-muted">Nama Siswa:</span> <strong class="text-ink">{{ activeStudent.name }}</strong></p>
                        <p><span class="text-ink-muted">NISN:</span> <strong class="text-ink font-mono">{{ activeStudent.nisn || '-' }}</strong></p>
                    </div>
                    <div>
                        <p><span class="text-ink-muted">Kelas:</span> <strong class="text-ink">{{ selectedClass }}</strong></p>
                        <p><span class="text-ink-muted">Wali Kelas:</span> <strong class="text-ink">{{ teacher.name }}</strong></p>
                    </div>
                </div>

                <div class="p-4 bg-surface rounded-control border border-line space-y-2">
                    <p class="font-bold text-xs">Capaian Akademik:</p>
                    <p class="text-xs leading-relaxed">
                        Ananda <strong>{{ activeStudent.name }}</strong> telah menuntaskan seluruh tujuan pembelajaran dengan perolehan nilai rata-rata <strong>{{ activeStudent.nilai_akhir ?? 88 }}</strong> (Predikat <strong>{{ activeStudent.predikat ?? 'A' }}</strong>).
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-line print:hidden">
                    <AppButton size="sm" variant="ghost" @click="showPrintModal = false">Tutup</AppButton>
                    <AppButton size="sm" variant="primary" @click="printDoc">Cetak Raport</AppButton>
                </div>
            </div>
        </Modal>
    </PortalShell>
</template>
