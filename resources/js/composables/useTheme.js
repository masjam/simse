import { ref } from 'vue';

const currentTheme = ref('madinah');

export function useTheme() {
    function initTheme(initialTheme = 'madinah') {
        currentTheme.value = initialTheme;
        applyTheme(initialTheme);
    }

    function applyTheme(theme) {
        currentTheme.value = theme;
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('data-theme', theme);
            // Also store in cookie for persistence across full page loads
            document.cookie = `simse_theme=${theme};path=/;max-age=31536000;SameSite=Lax`;
        }
    }

    function toggleDark() {
        const next = currentTheme.value === 'madinah' ? 'madinah-gelap' : 'madinah';
        applyTheme(next);
    }

    return {
        currentTheme,
        initTheme,
        applyTheme,
        toggleDark,
    };
}

