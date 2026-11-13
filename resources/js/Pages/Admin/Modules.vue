<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalShell from '@/Layouts/PortalShell.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppSwitch from '@/Components/ui/AppSwitch.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();
const localModules = ref(props.modules.map((m) => ({ ...m })));
const savingId = ref(null);

const toggleModule = (mod) => {
    mod.status = !mod.status;
    savingId.value = mod.id;

    if (mod.id) {
        router.patch(route('sistem.modules.toggle', mod.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                savingId.value = null;
                toast.success(`Modul ${mod.name} berhasil diubah.`);
            },
            onError: () => {
                savingId.value = null;
                mod.status = !mod.status;
            },
        });
    } else {
        setTimeout(() => {
            savingId.value = null;
            toast.success(`Status ${mod.name} berhasil disimpan.`);
        }, 300);
    }
};
</script>

<template>
    <Head title="Manajemen Modul" />

    <PortalShell variant="sidebar">
        <div class="space-y-6 max-w-7xl">
            <PageHeader
                title="Manajemen Modul Aplikasi"
                subtitle="Aktifkan atau nonaktifkan modul sistem sesuai kebutuhan operasional sekolah"
            />

            <!-- Info Bar -->
            <AppCard class="shadow-card">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Pusat Pengendali Modul Terpadu</h3>
                        <p class="text-xs text-ink-muted mt-1">
                            SIMSE menggunakan arsitektur modular. Menonaktifkan modul yang tidak digunakan akan menyembunyikan modul dari navigasi dan menutup route terkait.
                        </p>
                    </div>
                </div>
            </AppCard>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <AppCard
                    v-for="mod in localModules"
                    :key="mod.id"
                    class="flex flex-col justify-between shadow-card hover:border-brand-500 transition-all"
                >
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="w-12 h-12 rounded-control bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-lg">
                                📦
                            </div>

                            <AppSwitch
                                :model-value="Boolean(mod.status)"
                                :disabled="savingId === mod.id"
                                @change="() => toggleModule(mod)"
                            />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-ink mb-1">
                                {{ mod.name }}
                            </h2>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                {{ mod.description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-line flex items-center justify-between">
                        <StatusBadge
                            :variant="mod.status ? 'success' : 'danger'"
                            :text="mod.status ? 'AKTIF' : 'NONAKTIF'"
                            size="sm"
                        />
                        <span v-if="savingId === mod.id" class="text-[11px] text-brand-700 animate-pulse font-medium">
                            Menyimpan...
                        </span>
                    </div>
                </AppCard>
            </div>
        </div>
    </PortalShell>
</template>
