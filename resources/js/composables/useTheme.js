import { ref, computed } from 'vue';

const currentTheme = ref('madinah');

export const THEMES = [
    { id: 'madinah', name: 'Madinah', badge: 'Standar Islami', color: '#0F6B5C' },
    { id: 'taman-belajar', name: 'Taman Belajar', badge: 'Kelas 1-3', color: '#1D6FD8' },
    { id: 'kertas-tinta', name: 'Kertas & Tinta', badge: 'Editorial/Rapor', color: '#B3361B' },
    { id: 'ledger-tegas', name: 'Ledger Tegas', badge: 'Keuangan', color: '#1B3FD1' },
    { id: 'senja-kaca', name: 'Senja Kaca', badge: 'Wali Murid', color: '#6D4AD6' },
    { id: 'pasar-kantin', name: 'Pasar Kantin', badge: 'Kantin POS', color: '#C2410C' },
    { id: 'fokus-ujian', name: 'Fokus Ujian', badge: 'CBT Anti-Lelah', color: '#27272A' },
    { id: 'madinah-gelap', name: 'Madinah Gelap', badge: 'Mode Malam', color: '#14B8A6' },
];

export function useTheme() {
    function initTheme(initialTheme = 'madinah') {
        currentTheme.value = initialTheme;
        applyTheme(initialTheme);
    }

    function applyTheme(theme) {
        currentTheme.value = theme;
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('data-theme', theme);
            document.cookie = `simse_theme=${theme};path=/;max-age=31536000;SameSite=Lax`;
        }
    }

    function toggleDark() {
        const next = currentTheme.value === 'madinah' ? 'madinah-gelap' : 'madinah';
        applyTheme(next);
    }

    const isDark = computed(() => {
        return currentTheme.value === 'madinah-gelap' || currentTheme.value === 'uji-gelap';
    });

    return {
        currentTheme,
        themes: THEMES,
        isDark,
        initTheme,
        applyTheme,
        toggleDark,
    };
}
