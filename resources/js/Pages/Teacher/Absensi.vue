<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useAutosave } from '@/composables/useAutosave';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    selectedClass: { type: String, default: '' },
    selectedDate: { type: String, default: '' },
    students: { type: Array, default: () => [] },
});

const confirm = useConfirm();
const toast = useToast();

const classList = computed(() => props.classes?.length ? props.classes : ['1 Brilliant', 'I OLYMPIAD']);
const selectedClass = ref(props.selectedClass || classList.value[0] || '1 Brilliant');
const selectedDate = ref(props.selectedDate || new Date().toISOString().slice(0, 10));
const searchQuery = ref('');

const studentList = ref(props.students.map(s => ({ ...s, status: s.status || null, leave_reason: s.leave_reason || '' })));

watch(() => props.students, (newStudents) => {
    studentList.value = newStudents.map(s => ({ ...s, status: s.status || null, leave_reason: s.leave_reason || '' }));
}, { deep: true });

const onFilterChange = () => {
    router.get(route('guru.absensi'), {
        class: selectedClass.value,
        date: selectedDate.value,
    }, { preserveState: true, replace: true });
};

const classOptions = computed(() => classList.value.map(c => ({ value: c, label: `Kelas ${c}` })));

const filteredStudents = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return studentList.value.filter(s =>
        s.name.toLowerCase().includes(q) || (s.nisn && s.nisn.includes(q))
    );
});

// One-tap attendance change
const setStatus = (student, status) => {
    student.status = status;
    triggerAutosave();
};

const setAllHadir = () => {
    studentList.value.forEach(s => s.status = 'Hadir');
    toast.info('Semua siswa ditandai Hadir.');
    triggerAutosave();
};

const statHadir = computed(() => studentList.value.filter(s => s.status === 'Hadir').length);
const statIzin = computed(() => studentList.value.filter(s => s.status === 'Izin').length);
const statSakit = computed(() => studentList.value.filter(s => s.status === 'Sakit').length);
const statAlpa = computed(() => studentList.value.filter(s => s.status === 'Alpa').length);
const statBelum = computed(() => studentList.value.filter(s => !s.status).length);

// Auto-save integration
const { status: autosaveStatus, save: triggerAutosave } = useAutosave(async () => {
    await window.axios.post(route('guru.absensi.store'), {
        class_name: selectedClass.value,
        date: selectedDate.value,
        students: studentList.value.map(s => ({
            id: s.id,
            status: s.status || 'Hadir',
            leave_reason: s.leave_reason || null,
        })),
    });
}, { debounceMs: 1200 });

const submitAbsensi = async () => {
    if (statBelum.value > 0) {
        const agreed = await confirm.ask({
            title: 'Konfirmasi Presensi',
            message: `Terdapat ${statBelum.value} siswa yang belum dipilih statusnya. Siswa tanpa status akan otomatis ditandai Hadir. Simpan sekarang?`,
            confirmLabel: 'Ya, Simpan',
            variant: 'warning',
        });
        if (!agreed) return;
    }

    router.post(route('guru.absensi.store'), {
        class_name: selectedClass.value,
        date: selectedDate.value,
        students: studentList.value.map(s => ({
            id: s.id,
            status: s.status || 'Hadir',
            leave_reason: s.leave_reason || null,
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Presensi siswa berhasil disimpan.');
        },
    });
};

const statusOptions = [
    { label: 'Hadir', value: 'Hadir', variant: 'success' },
    { label: 'Izin', value: 'Izin', variant: 'info' },
    { label: 'Sakit', value: 'Sakit', variant: 'warning' },
    { label: 'Alpa', value: 'Alpa', variant: 'danger' },
];
</script>

<template>
    <Head title="Presensi Siswa" />

    <PortalShell variant="sidebar" :autosave-status="autosaveStatus">
        <template #header>
            <PageHeader
                title="Presensi Kehadiran Siswa"
                description="Pencatatan Kehadiran Harian Kelas SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="space-y-4">
            <!-- 1. Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <StatCard title="Hadir" :value="statHadir" unit="Siswa" />
                <StatCard title="Izin" :value="statIzin" unit="Siswa" />
                <StatCard title="Sakit" :value="statSakit" unit="Siswa" />
                <StatCard title="Alpa" :value="statAlpa" unit="Siswa" />
                <StatCard title="Belum Diisi" :value="statBelum" unit="Siswa" />
            </div>

            <!-- 2. Filter & Bulk Action Bar -->
            <div class="bg-surface rounded-card border border-line p-4 shadow-card flex flex-col md:flex-row items-end justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full md:w-auto flex-1">
                    <div>
                        <label for="select-class" class="block text-xs font-bold text-ink-muted mb-1">Pilih Rombel / Kelas</label>
                        <AppSelect
                            id="select-class"
                            v-model="selectedClass"
                            :options="classOptions"
                            @change="onFilterChange"
                        />
                    </div>
                    <div>
                        <label for="select-date" class="block text-xs font-bold text-ink-muted mb-1">Tanggal Presensi</label>
                        <AppInput
                            id="select-date"
                            v-model="selectedDate"
                            type="date"
                            @change="onFilterChange"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink-muted mb-1">Cari Siswa</label>
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Ketik nama atau NISN..."
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                    <AppButton
                        size="sm"
                        variant="secondary"
                        @click="setAllHadir"
                    >
                        ✓ Semua Hadir
                    </AppButton>
                    <AppButton
                        size="sm"
                        variant="primary"
                        @click="submitAbsensi"
                    >
                        Simpan Presensi
                    </AppButton>
                </div>
            </div>

            <!-- 3. Students Attendance List -->
            <div class="bg-surface rounded-card border border-line shadow-card overflow-hidden">
                <div class="p-4 border-b border-line bg-surface-raised flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Daftar Kehadiran Siswa Kelas {{ selectedClass }}</h3>
                        <p class="text-xs text-ink-muted">Satu ketukan tombol untuk mengubah status kehadiran siswa secara instan.</p>
                    </div>
                    <span class="text-xs font-bold font-mono text-ink">
                        {{ filteredStudents.length }} Siswa
                    </span>
                </div>

                <div v-if="filteredStudents.length === 0" class="p-6">
                    <EmptyState
                        title="Tidak Ada Siswa"
                        description="Tidak ada data siswa yang cocok dengan filter atau pencarian saat ini."
                    />
                </div>
                <div v-else class="divide-y divide-line">
                    <div
                        v-for="(student, idx) in filteredStudents"
                        :key="student.id"
                        class="p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-surface-raised/40 transition-colors"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-6 text-center font-mono text-xs font-bold text-ink-muted shrink-0">
                                {{ idx + 1 }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-sm font-bold text-ink truncate">{{ student.name }}</h4>
                                <p class="text-[11px] text-ink-muted">NISN: {{ student.nisn || '-' }}</p>
                            </div>
                        </div>

                        <!-- One-tap buttons for Hadir, Izin, Sakit, Alpa -->
                        <div class="flex flex-wrap items-center gap-1.5 self-start sm:self-auto">
                            <AppButton
                                v-for="opt in statusOptions"
                                :key="opt.value"
                                size="sm"
                                :variant="student.status === opt.value ? 'primary' : 'secondary'"
                                class="!px-3 !py-1 text-xs font-bold"
                                @click="setStatus(student, opt.value)"
                            >
                                {{ opt.label }}
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalShell>
</template>
