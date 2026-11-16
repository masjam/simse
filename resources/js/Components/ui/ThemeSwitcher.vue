<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import AppButton from '@/Components/ui/AppButton.vue';
import Modal from '@/Components/ui/Modal.vue';

const props = defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const { currentTheme, applyTheme, themes } = useTheme();
const isOpen = ref(false);

const allowSwitch = computed(() => {
    return page.props.allowThemeSwitch !== false;
});

const currentThemeObj = computed(() => {
    return themes.find((t) => t.id === currentTheme.value) || themes[0];
});

function selectTheme(themeId) {
    applyTheme(themeId);
    isOpen.value = false;
}
</script>

<template>
    <div v-if="allowSwitch" class="inline-flex items-center">
        <!-- Compact button (for navbar / header icon) -->
        <AppButton
            v-if="compact"
            variant="ghost"
            size="sm"
            class="!p-1.5 text-ink-muted hover:text-ink relative"
            title="Ganti Tema Tampilan"
            aria-label="Ganti Tema Tampilan"
            @click="isOpen = true"
        >
            <span class="text-base select-none">🎨</span>
        </AppButton>

        <!-- Full button (for sidebar footer / settings) -->
        <AppButton
            v-else
            variant="secondary"
            size="sm"
            class="flex items-center gap-2 text-xs font-semibold"
            @click="isOpen = true"
        >
            <span
                class="w-3 h-3 rounded-full border border-line"
                :class="{
                    'bg-[#0F6B5C]': currentTheme === 'madinah',
                    'bg-[#1D6FD8]': currentTheme === 'taman-belajar',
                    'bg-[#B3361B]': currentTheme === 'kertas-tinta',
                    'bg-[#1B3FD1]': currentTheme === 'ledger-tegas',
                    'bg-[#6D4AD6]': currentTheme === 'senja-kaca',
                    'bg-[#C2410C]': currentTheme === 'pasar-kantin',
                    'bg-[#27272A]': currentTheme === 'fokus-ujian',
                    'bg-[#14B8A6]': currentTheme === 'madinah-gelap',
                }"
            />
            <span class="truncate max-w-[100px]">{{ currentThemeObj.name }}</span>
        </AppButton>

        <!-- Modal Pemilih Tema -->
        <Modal
            :show="isOpen"
            title="Pilih Tema Tampilan SIMSE"
            max-width="md"
            @close="isOpen = false"
        >
            <div class="space-y-4 text-xs">
                <p class="text-ink-muted leading-relaxed">
                    Pilih suasana visual yang paling nyaman untuk aktivitas Anda di SIMSE SD Muhammadiyah Al Mujahidin:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                    <button
                        v-for="t in themes"
                        :key="t.id"
                        type="button"
                        class="p-3 rounded-control border text-left transition-all duration-150 flex flex-col justify-between gap-2"
                        :class="[
                            currentTheme === t.id
                                ? 'border-brand-600 bg-brand-50/50 dark:bg-brand-900/20 ring-1 ring-brand-600'
                                : 'border-line bg-surface hover:bg-surface-raised hover:border-ink-muted/30'
                        ]"
                        @click="selectTheme(t.id)"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-ink text-xs">{{ t.name }}</span>
                            <span
                                v-if="currentTheme === t.id"
                                class="w-2 h-2 rounded-full bg-brand-600"
                            />
                        </div>
                        <span class="text-[11px] text-ink-muted line-clamp-1">
                            {{ t.badge }}
                        </span>
                    </button>
                </div>

                <div class="flex justify-end pt-2 border-t border-line">
                    <AppButton
                        variant="secondary"
                        size="sm"
                        @click="isOpen = false"
                    >
                        Tutup
                    </AppButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

