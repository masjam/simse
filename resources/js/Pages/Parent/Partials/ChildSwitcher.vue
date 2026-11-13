<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Avatar from '@/Components/ui/Avatar.vue';
import AppSelect from '@/Components/ui/AppSelect.vue';

const props = defineProps({
    children: {
        type: Array,
        default: () => [],
    },
    activeChild: {
        type: Object,
        required: true,
    },
});

const childOptions = computed(() =>
    props.children.map((c) => ({
        value: c.id,
        label: `${c.name} (${c.class_name})`,
    }))
);

const switchChild = (studentId) => {
    if (!studentId || studentId === props.activeChild?.id) return;
    router.post(route('ortu.switch-child'), { student_id: studentId }, { preserveScroll: true });
};
</script>

<template>
    <div class="bg-surface rounded-card border border-line p-4 shadow-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <Avatar
                    :name="activeChild.name"
                    size="lg"
                    class="ring-2 ring-brand-500/30"
                />
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-2 py-0.5 rounded-control">
                            Siswa Aktif
                        </span>
                        <span class="text-xs text-ink-muted">
                            NISN: {{ activeChild.nisn || '-' }}
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-ink truncate mt-0.5">
                        {{ activeChild.name }}
                    </h2>
                    <p class="text-xs text-ink-muted truncate">
                        Kelas {{ activeChild.class_name }} &bull; Wali: {{ activeChild.wali_kelas || '-' }}
                    </p>
                </div>
            </div>

            <!-- Switcher if parent has multiple children -->
            <div v-if="children.length > 1" class="flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-line">
                <label for="child-select" class="text-xs font-semibold text-ink-muted shrink-0">
                    Ganti Anak:
                </label>
                <div class="w-48 sm:w-56">
                    <AppSelect
                        id="child-select"
                        :model-value="activeChild.id"
                        :options="childOptions"
                        @update:model-value="switchChild"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
