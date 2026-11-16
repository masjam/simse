<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import CounselingRequestModal from './Partials/CounselingRequestModal.vue';

const props = defineProps({
    student: { type: Object, required: true },
    achievements: { type: Array, default: () => [] },
    guidanceNotes: { type: Array, default: () => [] },
    counselingRequests: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_achievements: 0, total_guidance: 0, active_requests: 0 }) },
});

const activeTab = ref('achievements'); // 'achievements' | 'guidance' | 'requests'
const showCounselingModal = ref(false);
</script>

<template>
    <Head title="Prestasi & Pembinaan Siswa" />

    <PortalShell variant="bottom-nav">
        <template #header>
            <PageHeader
                title="Prestasi & Pembinaan Positif"
                :description="`${student.name} &bull; ${student.class_name}`"
            >
                <template #actions>
                    <AppButton size="sm" variant="primary" @click="showCounselingModal = true">
                        💬 Hubungi Guru BK
                    </AppButton>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-4">
            <!-- 1. Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <StatCard
                    title="Prestasi Terverifikasi"
                    :value="stats.total_achievements"
                    description="Piagam lomba & penghargaan resmi"
                />
                <StatCard
                    title="Catatan Motivasi Guru"
                    :value="stats.total_guidance"
                    description="Apresiasi & pembinaan kebiasaan baik"
                />
                <StatCard
                    title="Konseling Aktif"
                    :value="stats.active_requests"
                    description="Permohonan ngobrol bersama Guru BK"
                />
            </div>

            <!-- Positive Growth Banner -->
            <div class="p-3.5 rounded-card bg-surface border border-line shadow-card flex items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-ink">Setiap Anak Berbakat & Istimewa</p>
                    <p class="text-[11px] text-ink-muted">
                        Terus kembangkan potensi terbaikmu dan jangan ragu berdiskusi santai dengan guru pembimbing sekolah.
                    </p>
                </div>
                <AppButton size="sm" variant="secondary" @click="showCounselingModal = true">
                    Curhat BK
                </AppButton>
            </div>

            <!-- 2. Tabs Selector -->
            <div class="flex items-center gap-2 border-b border-line pb-2 flex-wrap">
                <AppButton
                    size="sm"
                    :variant="activeTab === 'achievements' ? 'primary' : 'secondary'"
                    @click="activeTab = 'achievements'"
                >
                    Piagam & Prestasi ({{ achievements.length }})
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'guidance' ? 'primary' : 'secondary'"
                    @click="activeTab = 'guidance'"
                >
                    Catatan Motivasi ({{ guidanceNotes.length }})
                </AppButton>
                <AppButton
                    size="sm"
                    :variant="activeTab === 'requests' ? 'primary' : 'secondary'"
                    @click="activeTab = 'requests'"
                >
                    Janji Temu BK ({{ counselingRequests.length }})
                </AppButton>
            </div>

            <!-- 3. Tab Contents -->

            <!-- Tab 1: Achievements -->
            <div v-if="activeTab === 'achievements'" class="space-y-3">
                <div v-if="achievements.length === 0">
                    <EmptyState
                        title="Belum Ada Prestasi Terdata"
                        description="Ananda dapat mengajukan sertifikat prestasi melalui orang tua di Portal Orang Tua."
                    />
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        v-for="ach in achievements"
                        :key="ach.id"
                        class="p-4 rounded-card bg-surface border border-line shadow-card space-y-2.5 flex flex-col justify-between"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700">
                                    {{ ach.category }} &bull; {{ ach.level }}
                                </span>
                                <span class="text-xs text-ink-muted font-numeric">{{ ach.achievement_date }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-ink">{{ ach.name }}</h4>
                            <p class="text-xs text-ink-muted">Penyelenggara: {{ ach.organizer }}</p>
                            <p v-if="ach.description" class="text-xs text-ink leading-relaxed pt-1">
                                {{ ach.description }}
                            </p>
                        </div>

                        <div v-if="ach.certificate_url" class="pt-2 border-t border-line flex items-center justify-end">
                            <a :href="ach.certificate_url" target="_blank">
                                <AppButton size="sm" variant="secondary" class="!px-2 !py-0.5 text-xs">
                                    Unduh Sertifikat PDF
                                </AppButton>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Guidance Notes -->
            <div v-else-if="activeTab === 'guidance'" class="space-y-3">
                <div v-if="guidanceNotes.length === 0">
                    <EmptyState
                        title="Belum Ada Catatan Pembinaan"
                        description="Terus semangat beribadah, belajar, dan bersikap santun kepada sesama teman."
                    />
                </div>
                <div v-else class="space-y-2.5">
                    <div
                        v-for="gn in guidanceNotes"
                        :key="gn.id"
                        class="p-3.5 rounded-card bg-surface border border-line shadow-card space-y-1.5"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-brand-700">{{ gn.type }}</span>
                            <span class="text-ink-muted font-numeric">{{ gn.date }} &bull; Oleh: {{ gn.counselor_name }}</span>
                        </div>
                        <p class="text-xs text-ink leading-relaxed font-medium">
                            {{ gn.positive_summary }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Counseling Requests -->
            <div v-else-if="activeTab === 'requests'" class="space-y-3">
                <div v-if="counselingRequests.length === 0">
                    <EmptyState
                        title="Belum Pernah Mengajukan Sesi Curhat"
                        description="Ada hal yang ingin diceritakan? Klik tombol di bawah untuk membuat janji mengobrol bersama Guru BK."
                        action-label="💬 Hubungi Guru BK Sekarang"
                        @action="showCounselingModal = true"
                    />
                </div>
                <div v-else class="space-y-2.5">
                    <div
                        v-for="cr in counselingRequests"
                        :key="cr.id"
                        class="p-3.5 rounded-card bg-surface border border-line shadow-card space-y-2"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-ink">
                                Topik: {{ cr.category }}
                            </span>
                            <StatusBadge :status="cr.status === 'Selesai' ? 'Selesai' : (cr.status === 'Dijadwalkan' ? 'Aktif' : 'Menunggu')" />
                        </div>
                        <p class="text-xs text-ink-muted italic">
                            "{{ cr.notes }}"
                        </p>
                        <div class="text-[11px] text-ink-muted flex items-center justify-between pt-1 border-t border-line">
                            <span>Waktu pengajuan: {{ cr.created_at }}</span>
                            <span v-if="cr.scheduled_at" class="font-bold text-brand-700">
                                Dijadwalkan: {{ cr.scheduled_at }}
                            </span>
                        </div>
                        <div v-if="cr.counselor_feedback" class="p-2.5 rounded-control bg-brand-50 border border-brand-200 text-xs text-brand-900 mt-1">
                            <span class="font-bold block">Pesan Balasan Guru BK:</span>
                            <span class="text-[11px]">{{ cr.counselor_feedback }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Hubungi Guru BK -->
        <CounselingRequestModal
            :show="showCounselingModal"
            @close="showCounselingModal = false"
        />
    </PortalShell>
</template>

