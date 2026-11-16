<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/ui/Modal.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import AppSwitch from '@/Components/ui/AppSwitch.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    show: { type: Boolean, default: false },
    preferences: {
        type: Object,
        default: () => ({
            channel_wa: true,
            channel_in_app: true,
            notify_attendance: true,
            notify_finance: true,
            notify_grades: true,
            notify_discipline: true,
        }),
    },
});

const emit = defineEmits(['close']);
const toast = useToast();

const form = useForm({
    channel_wa: props.preferences?.channel_wa ?? true,
    channel_in_app: props.preferences?.channel_in_app ?? true,
    notify_attendance: props.preferences?.notify_attendance ?? true,
    notify_finance: props.preferences?.notify_finance ?? true,
    notify_grades: props.preferences?.notify_grades ?? true,
    notify_discipline: props.preferences?.notify_discipline ?? true,
});

const submit = () => {
    form.post(route('ortu.notification-preferences.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Preferensi notifikasi berhasil disimpan.');
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" title="Preferensi Notifikasi Orang Tua" max-width="md" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="p-3 rounded-control bg-brand-50 border border-brand-200 text-xs text-brand-900 space-y-1">
                <p class="font-bold">Saluran & Kategori Pengumuman</p>
                <p class="text-[11px] text-ink-muted leading-relaxed">
                    Tentukan saluran komunikasi dan jenis informasi ananda yang ingin Ayah/Bunda terima secara berkala.
                </p>
            </div>

            <!-- 1. Saluran Komunikasi -->
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-ink uppercase tracking-wider">
                    Saluran Penerimaan
                </h4>
                <div class="p-3 rounded-control bg-surface-raised border border-line space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-ink block">Notifikasi WhatsApp Resmi</span>
                            <span class="text-[11px] text-ink-muted">Pesan instan ke nomor ponsel orang tua</span>
                        </div>
                        <AppSwitch v-model="form.channel_wa" />
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-line">
                        <div>
                            <span class="text-xs font-bold text-ink block">Notifikasi In-App Portal SIMSE</span>
                            <span class="text-[11px] text-ink-muted">Lonceng notifikasi di dalam aplikasi</span>
                        </div>
                        <AppSwitch v-model="form.channel_in_app" />
                    </div>
                </div>
            </div>

            <!-- 2. Kategori Notifikasi -->
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-ink uppercase tracking-wider">
                    Jenis Informasi yang Diaktifkan
                </h4>
                <div class="p-3 rounded-control bg-surface-raised border border-line space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-ink block">Presensi & Kehadiran Ananda</span>
                            <span class="text-[11px] text-ink-muted">Konfirmasi check-in masuk dan pulang sekolah</span>
                        </div>
                        <AppSwitch v-model="form.notify_attendance" />
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-line">
                        <div>
                            <span class="text-xs font-bold text-ink block">Keuangan, SPP & Saldo Kantin</span>
                            <span class="text-[11px] text-ink-muted">Tagihan bulanan dan peringatan saldo menipis</span>
                        </div>
                        <AppSwitch v-model="form.notify_finance" />
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-line">
                        <div>
                            <span class="text-xs font-bold text-ink block">Akademik & Nilai Ujian</span>
                            <span class="text-[11px] text-ink-muted">Hasil tugas dan pengumuman e-Raport</span>
                        </div>
                        <AppSwitch v-model="form.notify_grades" />
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-line">
                        <div>
                            <span class="text-xs font-bold text-ink block">Kedisiplinan & Bimbingan BK</span>
                            <span class="text-[11px] text-ink-muted">Catatan pembinaan dan poin kedisiplinan</span>
                        </div>
                        <AppSwitch v-model="form.notify_discipline" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <AppButton type="button" variant="ghost" size="sm" @click="emit('close')">
                    Batal
                </AppButton>
                <AppButton type="submit" variant="primary" size="sm" :loading="form.processing">
                    Simpan Preferensi
                </AppButton>
            </div>
        </form>
    </Modal>
</template>

