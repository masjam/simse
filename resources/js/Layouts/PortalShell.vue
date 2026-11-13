<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { getNavigationForRole } from '@/config/navigation';
import { useToast } from '@/composables/useToast';
import { useTheme } from '@/composables/useTheme';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NotificationBell from '@/Components/domain/NotificationBell.vue';
import ToastHost from '@/Components/ui/ToastHost.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Dropdown from '@/Components/ui/Dropdown.vue';
import Avatar from '@/Components/ui/Avatar.vue';

const props = defineProps({
    variant: {
        type: String,
        default: null, // 'sidebar' | 'bottom-nav' | 'bare'
        validator: (v) => v === null || ['sidebar', 'bottom-nav', 'bare'].includes(v),
    },
    title: {
        type: String,
        default: null,
    },
    autosaveStatus: {
        type: String,
        default: null, // 'idle' | 'saving' | 'saved' | 'error'
    },
});

const page = usePage();
const toast = useToast();
const { currentTheme, initTheme, toggleDark } = useTheme();

const isSidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user || null);
const roles = computed(() => user.value?.roles || []);
const primaryRole = computed(() => roles.value[0] || 'Tamu');

// Auto-detect layout variant based on role if not explicitly set
const resolvedVariant = computed(() => {
    if (props.variant) return props.variant;
    if (['Siswa', 'Orang Tua'].includes(primaryRole.value)) {
        return 'bottom-nav';
    }
    return 'sidebar';
});

// Navigation groups
const navGroups = computed(() => {
    const activeModules = page.props.activeModules || {};
    return getNavigationForRole(primaryRole.value, activeModules);
});

// Flash messages automatic toast handler
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.warning) toast.warning(flash.warning);
    },
    { deep: true, immediate: true }
);

onMounted(() => {
    const initialTheme = page.props.theme || 'madinah';
    initTheme(initialTheme);
});

function isRouteActive(routeName) {
    if (!routeName || typeof route !== 'function') return false;
    try {
        return route().current(routeName) || route().current(`${routeName}.*`);
    } catch {
        return false;
    }
}

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-surface-sunken text-ink flex flex-col font-body selection:bg-brand-500 selection:text-white">
        <!-- Skip to Content Accessibility Link -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-brand-600 focus:text-white focus:rounded-control shadow-pop"
        >
            Lompat ke konten utama
        </a>

        <!-- Toast and Confirm Hosts -->
        <ToastHost />
        <ConfirmDialog />

        <!-- ──────────────── VARIANT 1: BARE (CBT / Kiosk / Cetak) ──────────────── -->
        <div v-if="resolvedVariant === 'bare'" class="min-h-screen flex flex-col">
            <main id="main-content" class="flex-1">
                <slot />
            </main>
        </div>

        <!-- ──────────────── VARIANT 2: SIDEBAR (Admin, Guru, Bendahara, Staf, Kantin) ──────────────── -->
        <div v-else-if="resolvedVariant === 'sidebar'" class="min-h-screen flex flex-col lg:flex-row">
            <!-- Mobile Topbar -->
            <header class="lg:hidden sticky top-0 z-30 h-16 bg-surface border-b border-line px-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="p-2 -ml-2 rounded-control text-ink-muted hover:text-ink focus:ring-2 ring-focus"
                        aria-label="Buka menu navigasi"
                        @click="isSidebarOpen = true"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <ApplicationLogo class="w-8 h-8 text-brand-700" />
                        <span class="font-display font-bold text-sm tracking-tight text-ink">SIMSE</span>
                    </Link>
                </div>

                <div class="flex items-center gap-2">
                    <NotificationBell />
                    <button
                        type="button"
                        class="p-2 rounded-control text-ink-muted hover:text-ink border border-line"
                        aria-label="Ubah tema tampilan"
                        @click="toggleDark"
                    >
                        <span v-if="currentTheme === 'madinah'">🌙</span>
                        <span v-else>☀️</span>
                    </button>
                </div>
            </header>

            <!-- Backdrop for Mobile Sidebar -->
            <div
                v-if="isSidebarOpen"
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden transition-opacity"
                @click="isSidebarOpen = false"
            />

            <!-- Sidebar Drawer -->
            <aside
                class="fixed inset-y-0 left-0 z-40 w-64 bg-surface border-r border-line shadow-card flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
                :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <!-- Brand Header -->
                <div class="h-16 px-5 border-b border-line flex items-center justify-between">
                    <Link :href="route('dashboard')" class="flex items-center gap-2.5">
                        <ApplicationLogo class="w-8 h-8 text-brand-700 flex-shrink-0" />
                        <div>
                            <span class="font-display font-bold text-base tracking-tight text-ink block leading-none">
                                SIMSE
                            </span>
                            <span class="text-[10px] text-ink-muted uppercase tracking-wider block mt-0.5">
                                SD Muh Al Mujahidin
                            </span>
                        </div>
                    </Link>
                    <button
                        type="button"
                        class="lg:hidden p-1.5 rounded-control text-ink-muted hover:text-ink"
                        aria-label="Tutup menu"
                        @click="isSidebarOpen = false"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links List -->
                <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto" aria-label="Menu navigasi samping">
                    <div v-for="(group, gIdx) in navGroups" :key="gIdx">
                        <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-ink-muted/80 mb-2">
                            {{ group.group }}
                        </p>
                        <ul class="space-y-1">
                            <li v-for="item in group.items" :key="item.route">
                                <Link
                                    :href="route(item.route)"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-control transition min-h-[40px]',
                                        isRouteActive(item.route)
                                            ? 'bg-brand-600 text-brand-fg shadow-sm font-bold'
                                            : 'text-ink-muted hover:text-ink hover:bg-surface-raised',
                                    ]"
                                    @click="isSidebarOpen = false"
                                >
                                    <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                                        ●
                                    </span>
                                    <span class="truncate">{{ item.label }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Sidebar Footer Profile & Theme Toggle -->
                <div class="p-3 border-t border-line bg-surface-raised/40 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <Avatar :name="user?.name || primaryRole" size="sm" />
                        <div class="truncate">
                            <p class="text-xs font-bold text-ink truncate leading-tight">{{ user?.name || 'Pengguna' }}</p>
                            <p class="text-[10px] text-ink-muted truncate">{{ primaryRole }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="p-2 rounded-control text-ink-muted hover:text-ink hover:bg-surface transition"
                        title="Ganti Tema"
                        aria-label="Ganti tema"
                        @click="toggleDark"
                    >
                        <span v-if="currentTheme === 'madinah'">🌙</span>
                        <span v-else>☀️</span>
                    </button>
                </div>
            </aside>

            <!-- Main Workspace -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Topbar -->
                <header class="hidden lg:flex h-16 bg-surface border-b border-line px-8 items-center justify-between sticky top-0 z-20">
                    <div class="flex items-center gap-4">
                        <h2 class="font-display font-bold text-lg text-ink">
                            {{ title || primaryRole }}
                        </h2>

                        <!-- Autosave Indicator -->
                        <div
                            v-if="autosaveStatus && autosaveStatus !== 'idle'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                            :class="{
                                'bg-brand-50 text-brand-700': autosaveStatus === 'saving',
                                'bg-status-success-bg text-status-success-fg': autosaveStatus === 'saved',
                                'bg-status-danger-bg text-status-danger-fg': autosaveStatus === 'error',
                            }"
                        >
                            <span v-if="autosaveStatus === 'saving'" class="animate-spin">⏳</span>
                            <span v-else-if="autosaveStatus === 'saved'">✓</span>
                            <span v-else>⚠</span>
                            <span>{{ autosaveStatus === 'saving' ? 'Menyimpan...' : autosaveStatus === 'saved' ? 'Tersimpan' : 'Gagal menyimpan' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <NotificationBell />

                        <!-- Profile Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2.5 p-1.5 pl-2.5 rounded-control hover:bg-surface-raised border border-line transition min-h-[44px]"
                                >
                                    <Avatar :name="user?.name || primaryRole" size="sm" />
                                    <span class="text-xs font-semibold text-ink max-w-[120px] truncate hidden sm:inline">
                                        {{ user?.name || 'Akun' }}
                                    </span>
                                    <svg class="w-4 h-4 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-2 border-b border-line">
                                    <p class="text-xs font-bold text-ink truncate">{{ user?.name }}</p>
                                    <p class="text-[11px] text-ink-muted truncate">{{ user?.email }}</p>
                                </div>
                                <button
                                    type="button"
                                    class="w-full text-left px-4 py-2 text-xs text-status-danger-fg hover:bg-status-danger-bg font-semibold transition"
                                    @click="logout"
                                >
                                    Keluar (Logout)
                                </button>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                    <slot />
                </main>
            </div>
        </div>

        <!-- ──────────────── VARIANT 3: BOTTOM-NAV (Siswa & Ortu; Mobile-First) ──────────────── -->
        <div v-else class="min-h-screen flex flex-col pb-20 sm:pb-0">
            <!-- Mobile App Topbar -->
            <header class="sticky top-0 z-30 h-14 bg-surface border-b border-line px-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <ApplicationLogo class="w-7 h-7 text-brand-700" />
                    <div>
                        <h1 class="font-display font-bold text-sm text-ink leading-tight">
                            {{ title || 'SD Muh Al Mujahidin' }}
                        </h1>
                        <p class="text-[10px] text-ink-muted leading-none">
                            {{ user?.name || primaryRole }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <NotificationBell />
                    <button
                        type="button"
                        class="p-2 rounded-control text-ink-muted hover:text-ink"
                        aria-label="Ganti tema"
                        @click="toggleDark"
                    >
                        <span v-if="currentTheme === 'madinah'">🌙</span>
                        <span v-else>☀️</span>
                    </button>
                    <button
                        type="button"
                        class="p-2 rounded-control text-status-danger-fg hover:bg-status-danger-bg"
                        aria-label="Keluar"
                        title="Keluar"
                        @click="logout"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <main id="main-content" class="flex-1 p-3.5 sm:p-6 max-w-2xl sm:max-w-4xl w-full mx-auto">
                <slot />
            </main>

            <!-- Fixed Bottom Navigation Bar -->
            <nav
                class="fixed bottom-0 inset-x-0 z-40 bg-surface border-t border-line shadow-pop flex items-center justify-around h-16 px-2 sm:max-w-md sm:mx-auto sm:rounded-t-card"
                aria-label="Navigasi bawah mobile"
            >
                <template v-for="group in navGroups" :key="group.group">
                    <Link
                        v-for="item in group.items.slice(0, 5)"
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex flex-col items-center justify-center flex-1 h-full py-1 text-center transition select-none outline-none ring-focus focus:ring-1 rounded-control"
                        :class="[
                            isRouteActive(item.route)
                                ? 'text-brand-600 font-bold'
                                : 'text-ink-muted hover:text-ink',
                        ]"
                    >
                        <span class="text-base mb-0.5">●</span>
                        <span class="text-[10px] truncate max-w-[64px]">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </div>
    </div>
</template>

