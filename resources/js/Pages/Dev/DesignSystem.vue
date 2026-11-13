<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useTheme } from '@/composables/useTheme';

// UI Components
import AppButton from '@/Components/ui/AppButton.vue';
import AppInput from '@/Components/ui/AppInput.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';
import AppTextarea from '@/Components/ui/AppTextarea.vue';
import AppCheckbox from '@/Components/ui/AppCheckbox.vue';
import AppSwitch from '@/Components/ui/AppSwitch.vue';
import FormField from '@/Components/ui/FormField.vue';
import CurrencyInput from '@/Components/ui/CurrencyInput.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import Stepper from '@/Components/ui/Stepper.vue';
import DataTable from '@/Components/ui/DataTable.vue';
import FilterBar from '@/Components/ui/FilterBar.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Skeleton from '@/Components/ui/Skeleton.vue';
import Modal from '@/Components/ui/Modal.vue';
import Drawer from '@/Components/ui/Drawer.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import ProgressRing from '@/Components/ui/ProgressRing.vue';
import Tooltip from '@/Components/ui/Tooltip.vue';
import Dropdown from '@/Components/ui/Dropdown.vue';

// Domain Components
import StudentPicker from '@/Components/domain/StudentPicker.vue';
import ClassSelect from '@/Components/domain/ClassSelect.vue';
import SubjectSelect from '@/Components/domain/SubjectSelect.vue';
import AcademicYearBadge from '@/Components/domain/AcademicYearBadge.vue';
import AttendanceStatusToggle from '@/Components/domain/AttendanceStatusToggle.vue';
import MoneyText from '@/Components/domain/MoneyText.vue';
import DateText from '@/Components/domain/DateText.vue';
import ScoreChip from '@/Components/domain/ScoreChip.vue';
import KopSurat from '@/Components/domain/KopSurat.vue';
import QrDisplay from '@/Components/domain/QrDisplay.vue';
import PrintSheet from '@/Components/domain/PrintSheet.vue';

defineProps({
    sampleStudents: { type: Array, default: () => [] },
    sampleClasses: { type: Array, default: () => [] },
    sampleSubjects: { type: Array, default: () => [] },
});

const toast = useToast();
const { confirm } = useConfirm();
const { currentTheme, applyTheme } = useTheme();

// Interactive states
const activeTab = ref('primitif');
const textValue = ref('Contoh Teks');
const selectedClass = ref('');
const selectedSubject = ref('');
const selectedStudent = ref(null);
const currencyVal = ref(150000);
const switchVal = ref(true);
const checkVal = ref(true);
const attendanceVal = ref('Hadir');
const isModalOpen = ref(false);
const isDrawerOpen = ref(false);
const showPrintPreview = ref(false);

const tableColumns = [
    { key: 'name', label: 'Nama Lengkap', sortable: true },
    { key: 'nisn', label: 'NISN' },
    { key: 'class', label: 'Kelas' },
    { key: 'status', label: 'Status' },
];

const tableData = [
    { id: '1', name: 'Ahmad Dahlan', nisn: '0012345671', class: '6A', status: 'Aktif' },
    { id: '2', name: 'Siti Walidah', nisn: '0012345672', class: '5B', status: 'Hadir' },
    { id: '3', name: 'Fakhruddin', nisn: '0012345673', class: '4A', status: 'Menunggu Verifikasi' },
];

async function triggerConfirm() {
    const ok = await confirm({
        title: 'Hapus Data Uji?',
        message: 'Aksi ini akan menguji modal konfirmasi useConfirm() tanpa alert bawaan.',
        variant: 'danger',
    });
    if (ok) toast.success('Aksi konfirmasi disetujui pengguna.');
    else toast.info('Aksi dibatalkan.');
}
</script>

<template>
    <PortalShell title="Design System & Style Guide" variant="sidebar">
        <Head title="Living Style Guide — SIMSE" />

        <PageHeader
            title="SIMSE Living Style Guide"
            description="Fondasi antarmuka tunggal SD Muhammadiyah Al Mujahidin. Menggunakan token semantik, aksesibilitas kontras tinggi, dan responsivitas 360px."
            :breadcrumbs="[{ label: 'Pengembang' }, { label: 'Design System' }]"
        >
            <template #actions>
                <div class="flex items-center gap-2 bg-surface p-1.5 rounded-control border border-line">
                    <span class="text-xs font-semibold text-ink-muted px-2">Tema:</span>
                    <AppButton
                        v-for="t in ['madinah', 'madinah-gelap', 'uji-gelap']"
                        :key="t"
                        :variant="currentTheme === t ? 'primary' : 'ghost'"
                        size="sm"
                        @click="applyTheme(t)"
                    >
                        {{ t }}
                    </AppButton>
                </div>
            </template>
        </PageHeader>

        <!-- Navigation Tabs -->
        <Tabs
            v-model="activeTab"
            variant="pills"
            :tabs="[
                { id: 'primitif', label: '1. Komponen Primitif (UI)' },
                { id: 'domain', label: '2. Komponen Domain Bisnis' },
                { id: 'feedback', label: '3. Feedback, Dialog & State' },
                { id: 'cetak', label: '4. Kop Surat & Cetak A4' },
            ]"
        />

        <!-- TAB 1: PRIMITIF -->
        <section v-if="activeTab === 'primitif'" class="space-y-6 mt-6">
            <!-- Buttons Card -->
            <AppCard title="Tombol (AppButton)" subtitle="Varian warna semantik, ukuran, loading, dan fokus terlihat (>= 44px touch target)">
                <div class="flex flex-wrap items-center gap-3">
                    <AppButton variant="primary">Primary</AppButton>
                    <AppButton variant="secondary">Secondary</AppButton>
                    <AppButton variant="ghost">Ghost</AppButton>
                    <AppButton variant="danger">Danger</AppButton>
                    <AppButton variant="accent">Accent Emas</AppButton>
                    <AppButton variant="primary" :loading="true">Memuat</AppButton>
                    <AppButton variant="primary" size="sm">Kecil</AppButton>
                    <AppButton variant="primary" size="lg">Besar</AppButton>
                </div>
            </AppCard>

            <!-- Inputs Card -->
            <AppCard title="Form Controls & Inputs" subtitle="Input field standar, select, textarea, currency (Rp), search, dan error states">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <FormField label="Nama Lengkap" hint="Sesuai akta kelahiran" :required="true">
                        <AppInput v-model="textValue" placeholder="Masukkan nama..." />
                    </FormField>

                    <FormField label="Nominal SPP" hint="Otomatis berformat Rupiah">
                        <CurrencyInput v-model="currencyVal" />
                    </FormField>

                    <FormField label="Pencarian Cepat">
                        <SearchInput placeholder="Cari siswa atau materi..." />
                    </FormField>

                    <FormField label="Input Galat / Validasi Gagal" error="Wajib diisi dengan benar.">
                        <AppInput placeholder="Kolom bermasalah..." :invalid="true" />
                    </FormField>

                    <FormField label="Pilihan Status">
                        <AppSelect
                            v-model="attendanceVal"
                            :options="['Hadir', 'Izin', 'Sakit', 'Alpa']"
                        />
                    </FormField>

                    <div class="flex flex-col justify-center gap-3 pt-2">
                        <AppCheckbox v-model="checkVal" label="Simpan pilihan untuk sesi berikutnya" />
                        <AppSwitch v-model="switchVal" label="Kirim notifikasi WhatsApp wali siswa" />
                    </div>
                </div>
            </AppCard>

            <!-- Cards & Data Display -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <StatCard title="Total Kas Siswa" :value="`Rp 14.850.000`" subtitle="Kas aktif bendahara" trend="12.5%" :trend-up="true" icon-bg="brand" />
                <StatCard title="Tunggakan SPP" :value="`Rp 2.400.000`" subtitle="16 siswa menunggak" trend="4.1%" :trend-up="false" icon-bg="warning" />
                <StatCard title="Presensi Hari Ini" value="98.2%" subtitle="512 dari 520 hadir" icon-bg="success" />
            </div>

            <!-- DataTable Responsive -->
            <AppCard title="Tabel Data (DataTable)" subtitle="Otomatis beralih ke tampilan kartu di bawah 640px untuk layar 360px">
                <DataTable :columns="tableColumns" :items="tableData">
                    <template #cell(status)="{ value }">
                        <StatusBadge :status="value" />
                    </template>
                    <template #actions="{ item }">
                        <AppButton size="sm" variant="ghost" @click="toast.info(`Detail ${item.name}`)">Lihat</AppButton>
                    </template>
                </DataTable>
            </AppCard>
        </section>

        <!-- TAB 2: DOMAIN -->
        <section v-else-if="activeTab === 'domain'" class="space-y-6 mt-6">
            <AppCard title="Komponen Khusus Domain SIMSE" subtitle="Komponen bisnis dengan logika dan representasi entitas sekolah">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <FormField label="Pencarian Siswa (StudentPicker)">
                        <StudentPicker v-model="selectedStudent" :students="sampleStudents" />
                    </FormField>

                    <FormField label="Pemilih Rombel (ClassSelect)">
                        <ClassSelect v-model="selectedClass" :classes="sampleClasses" />
                    </FormField>

                    <FormField label="Pemilih Mapel (SubjectSelect)">
                        <SubjectSelect v-model="selectedSubject" :subjects="sampleSubjects" />
                    </FormField>

                    <FormField label="Status Presensi Cepat">
                        <AttendanceStatusToggle v-model="attendanceVal" />
                    </FormField>

                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-semibold text-ink-muted uppercase">Tahun Ajaran & Format Uang</span>
                        <div class="flex items-center gap-3">
                            <AcademicYearBadge year="2026/2027" semester="Ganjil" />
                            <MoneyText :amount="750000" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-semibold text-ink-muted uppercase">Skor & Predikat Nilai</span>
                        <div class="flex items-center gap-2">
                            <ScoreChip :score="95" predicate="A" />
                            <ScoreChip :score="84" predicate="B" />
                            <ScoreChip :score="68" predicate="D" />
                        </div>
                    </div>
                </div>
            </AppCard>

            <AppCard title="QR Code KTS / Presensi Siswa (QrDisplay)" subtitle="Render SVG qrcode.vue tajam untuk cetak kartu dan scan kiosk">
                <div class="flex flex-wrap items-center gap-6">
                    <QrDisplay value="SIMSE-NISN-0012345671" caption="NISN: 0012345671" :size="120" />
                    <div class="text-xs text-ink-muted max-w-sm space-y-1">
                        <p class="font-bold text-ink">QR Code Standar KTS Digital</p>
                        <p>Format string terenkripsi yang terbaca oleh scanner kiosk presensi maupun reader stand kantin sekolah.</p>
                    </div>
                </div>
            </AppCard>
        </section>

        <!-- TAB 3: FEEDBACK & DIALOG -->
        <section v-else-if="activeTab === 'feedback'" class="space-y-6 mt-6">
            <AppCard title="Pemicu Feedback & Dialog Interaktif" subtitle="Sistem toast, confirm dialog promise-based, modal, dan drawer">
                <div class="flex flex-wrap items-center gap-3">
                    <AppButton variant="secondary" @click="toast.success('Penyimpanan data berhasil!')">Toast Sukses</AppButton>
                    <AppButton variant="secondary" @click="toast.error('Gagal memproses permintaan!')">Toast Galat</AppButton>
                    <AppButton variant="secondary" @click="toast.warning('Peringatan kuota harian kantin!')">Toast Peringatan</AppButton>
                    <AppButton variant="danger" @click="triggerConfirm">Uji useConfirm()</AppButton>
                    <AppButton variant="primary" @click="isModalOpen = true">Buka Modal</AppButton>
                    <AppButton variant="secondary" @click="isDrawerOpen = true">Buka Drawer</AppButton>
                </div>
            </AppCard>

            <!-- Status Badges Catalog -->
            <AppCard title="Status Badge Domain Catalog" subtitle="Peta otomatis enum status bisnis ke varian semantik">
                <div class="flex flex-wrap items-center gap-2.5">
                    <StatusBadge status="Lunas" />
                    <StatusBadge status="Angsuran" />
                    <StatusBadge status="Belum Lunas" />
                    <StatusBadge status="Hadir" />
                    <StatusBadge status="Izin" />
                    <StatusBadge status="Sakit" />
                    <StatusBadge status="Alpa" />
                    <StatusBadge status="Disetujui" />
                    <StatusBadge status="Menunggu Verifikasi" />
                    <StatusBadge status="Ditolak" />
                    <StatusBadge status="Draft" />
                </div>
            </AppCard>

            <!-- Loading & Empty States -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <AppCard title="Keadaan Memuat (Skeleton)">
                    <Skeleton type="row" :rows="3" />
                </AppCard>
                <AppCard title="Keadaan Kosong (EmptyState)">
                    <EmptyState title="Belum Ada Nilai" description="Guru pengajar belum mengunggah nilai rapor kelas ini.">
                        <template #action>
                            <AppButton size="sm">Unggah Sekarang</AppButton>
                        </template>
                    </EmptyState>
                </AppCard>
            </div>

            <!-- Modal Demo -->
            <Modal :show="isModalOpen" title="Contoh Modal Dialog" @close="isModalOpen = false">
                <p class="text-sm text-ink-muted">Ini adalah kotak dialog modal standar SIMSE dengan fokus terlihat dan navigasi keyboard Esc.</p>
                <template #footer>
                    <AppButton variant="secondary" size="sm" @click="isModalOpen = false">Tutup</AppButton>
                    <AppButton variant="primary" size="sm" @click="isModalOpen = false; toast.success('Aksi disimpan.')">Simpan</AppButton>
                </template>
            </Modal>

            <!-- Drawer Demo -->
            <Drawer :show="isDrawerOpen" title="Panel Drawer Samping" @close="isDrawerOpen = false">
                <p class="text-sm text-ink-muted">Drawer samping cocok untuk filter mendalam, riwayat audit, atau formulir pengeditan cepat.</p>
                <template #footer>
                    <AppButton size="sm" variant="secondary" @click="isDrawerOpen = false">Tutup</AppButton>
                </template>
            </Drawer>
        </section>

        <!-- TAB 4: CETAK -->
        <section v-else class="space-y-6 mt-6">
            <AppCard title="Kop Surat Resmi & Tata Letak Cetak A4" subtitle="Standar kop Pimpinan Cabang Muhammadiyah Wonosari untuk e-Raport dan Berita Acara">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-xs text-ink-muted">Tampilan cetak proporsional A4 standar dokumen resmi.</p>
                    <AppButton size="sm" variant="primary" @click="showPrintPreview = !showPrintPreview">
                        {{ showPrintPreview ? 'Sembunyikan Lembar A4' : 'Tampilkan Lembar A4' }}
                    </AppButton>
                </div>

                <div v-if="!showPrintPreview">
                    <KopSurat />
                </div>

                <PrintSheet v-else>
                    <KopSurat />
                    <div class="text-center my-6">
                        <h3 class="text-base font-bold uppercase underline font-serif">SURAT KETERANGAN RESMI</h3>
                        <p class="text-xs font-mono text-ink-muted mt-1">Nomor: 421.2/085/SDM.WNS/XI/2026</p>
                    </div>
                    <p class="text-xs leading-relaxed text-ink indent-8 mb-4">
                        Yang bertanda tangan di bawah ini Kepala SD Muhammadiyah Al Mujahidin Wonosari, menerangkan dengan sesungguhnya bahwa siswa yang tercantum dalam lembar ini adalah benar terdaftar aktif pada tahun pelajaran 2026/2027.
                    </p>
                </PrintSheet>
            </AppCard>
        </section>
    </PortalShell>
</template>
