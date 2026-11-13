<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import QrDisplay from '@/Components/domain/QrDisplay.vue';
import Avatar from '@/Components/ui/Avatar.vue';

defineProps({
    student: { type: Object, required: true },
});

const printKts = () => {
    window.print();
};
</script>

<template>
    <Head title="Kartu Tanda Siswa (KTS) Digital" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Kartu Tanda Siswa (KTS)"
                description="Identitas Digital Resmi Siswa SD Muhammadiyah Al Mujahidin"
            />
        </template>

        <div class="py-4 sm:py-6 space-y-5">
            <div class="flex justify-center">
                <!-- Physical-styled ID Card Container -->
                <div class="w-full max-w-[360px] bg-surface rounded-card border-2 border-brand-600/40 shadow-card overflow-hidden text-ink relative">
                    <!-- Top Card Header -->
                    <div class="p-4 bg-brand-600 text-brand-fg flex items-center justify-between border-b border-brand-700">
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-accent-500 font-extrabold block">
                                KARTU TANDA SISWA
                            </span>
                            <h2 class="text-xs font-black tracking-wide mt-0.5">
                                SD Muhammadiyah Al Mujahidin
                            </h2>
                            <p class="text-[10px] text-white/80">Wonosari &bull; TA {{ student.academic_year }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-control bg-white/20 border border-white/30 flex items-center justify-center font-black text-xs text-brand-fg">
                            SD
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex flex-col items-center">
                        <div class="relative p-1 rounded-full bg-brand-500/20 mb-3">
                            <img
                                v-if="student.photo_url"
                                :src="student.photo_url"
                                alt="Foto Siswa"
                                class="w-20 h-20 rounded-full border-2 border-brand-600 object-cover shadow-sm"
                            />
                            <Avatar
                                v-else
                                :name="student.name"
                                size="xl"
                                class="ring-2 ring-brand-600"
                            />
                        </div>

                        <div class="text-center w-full">
                            <h3 class="text-base font-black text-ink tracking-tight">{{ student.name }}</h3>
                            <p class="text-xs text-brand-700 font-bold mt-0.5">
                                Kelas {{ student.class_name }} ({{ student.tingkat }})
                            </p>

                            <!-- Data Grid -->
                            <div class="mt-3 bg-surface-raised rounded-control p-3 text-left border border-line space-y-1.5 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-ink-muted uppercase tracking-wider font-semibold">NISN</span>
                                    <span class="font-mono font-bold text-ink">{{ student.nisn }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-ink-muted uppercase tracking-wider font-semibold">NIK</span>
                                    <span class="font-mono font-bold text-ink">{{ student.nik }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-ink-muted uppercase tracking-wider font-semibold">Jenis Kelamin</span>
                                    <span class="font-semibold text-ink">{{ student.gender }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: QR Identity Code -->
                    <div class="p-4 bg-surface-raised border-t border-line flex items-center justify-between gap-3">
                        <div class="text-xs">
                            <p class="font-bold text-brand-800">QR Identitas Siswa</p>
                            <p class="text-[10px] text-ink-muted leading-tight mt-0.5">
                                Dipindai untuk presensi tap, perpustakaan, dan transaksi e-Kantin.
                            </p>
                        </div>
                        <div class="shrink-0 p-1 bg-surface border border-line rounded-control shadow-sm">
                            <QrDisplay :value="student.qr_value || student.nisn || student.id" :size="64" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center print:hidden">
                <AppButton variant="primary" size="md" @click="printKts">
                    Cetak Kartu Tanda Siswa
                </AppButton>
            </div>
        </div>
    </PortalShell>
</template>
