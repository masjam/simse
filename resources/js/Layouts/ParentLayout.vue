<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const props = defineProps({
    children: {
        type: Array,
        default: () => [],
    },
    activeChild: {
        type: Object,
        default: null,
    },
    activeTab: {
        type: String,
        default: 'akademik',
    },
});

const emit = defineEmits(['select-tab']);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Orang Tua', email: '' });
const flash = computed(() => page.props.flash || {});

const switchChild = (studentId) => {
    if (!studentId || studentId === props.activeChild?.id) {
        return;
    }

    router.post(
        route('ortu.switch-child'),
        { student_id: studentId },
        { preserveScroll: true }
    );
};

const activeModules = computed(() => page.props.activeModules || {});

const tabs = computed(() => {
    const items = [
        { id: 'akademik', label: 'Akademik', module: 'akademik' },
        { id: 'presensi', label: 'Presensi', module: 'absensi' },
        { id: 'keuangan', label: 'Keuangan SPP', module: 'keuangan' },
        { id: 'perilaku', label: 'Perilaku & BK', module: 'bk' },
    ];

    return items.filter((item) => activeModules.value[item.module] !== false);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col text-slate-800">
        <!-- Top Header -->
        <header class="sticky top-0 z-30 bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white shadow-md">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
                <Link :href="route('ortu.dashboard')" class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-white/95 text-amber-700 font-black flex items-center justify-center shadow shrink-0">
                        OT
                    </div>
                    <div class="min-w-0">
                        <p class="font-extrabold text-sm leading-tight truncate">Portal Orang Tua</p>
                        <p class="text-[11px] text-amber-100 truncate">SD Muhammadiyah Al Mujahidin</p>
                    </div>
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <div v-if="children.length > 1" class="hidden sm:flex items-center gap-2 bg-white/15 px-3 py-1.5 rounded-xl border border-white/20">
                        <label for="child-selector" class="text-[11px] font-semibold text-amber-100">Ananda:</label>
                        <select
                            id="child-selector"
                            :value="activeChild?.id"
                            @change="switchChild($event.target.value)"
                            class="bg-transparent text-xs font-bold text-white focus:outline-none cursor-pointer [&>option]:text-slate-900"
                        >
                            <option v-for="child in children" :key="child.id" :value="child.id">
                                {{ child.name }} ({{ child.class_name }})
                            </option>
                        </select>
                    </div>

                    <div class="hidden md:block text-right">
                        <p class="text-xs font-bold leading-tight">{{ user.name }}</p>
                        <p class="text-[11px] text-amber-100">Wali Murid</p>
                    </div>

                    <NotificationBell theme="dark" />

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="px-3 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-xs font-bold transition-colors border border-white/20"
                    >
                        Keluar
                    </Link>
                </div>
            </div>

            <!-- Mobile Multi-Child Switcher + Tab Bar -->
            <div class="bg-white text-slate-700 border-b border-slate-200">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div v-if="children.length > 1" class="sm:hidden py-2 border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
                        <span class="text-[11px] font-bold text-slate-500 shrink-0">Anak:</span>
                        <button
                            v-for="child in children"
                            :key="child.id"
                            type="button"
                            @click="switchChild(child.id)"
                            class="px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap transition-colors"
                            :class="activeChild?.id === child.id ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700'"
                        >
                            {{ child.name }} ({{ child.class_name }})
                        </button>
                    </div>

                    <nav class="flex items-center gap-1 overflow-x-auto py-2" aria-label="Tab Portal Orang Tua">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            type="button"
                            @click="emit('select-tab', tab.id)"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all"
                            :class="activeTab === tab.id
                                ? 'bg-amber-600 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Flash Banners -->
        <div v-if="flash.success || flash.error" class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div v-if="flash.success" class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mt-2 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
                {{ flash.error }}
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>
    </div>
</template>

