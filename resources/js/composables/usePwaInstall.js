import { ref, onMounted, onUnmounted } from 'vue';

const deferredPrompt = ref(null);
const canInstall = ref(false);
const isInstalled = ref(false);

export function usePwaInstall() {
    const handleBeforeInstallPrompt = (e) => {
        // Prevent default browser banner
        e.preventDefault();
        deferredPrompt.value = e;
        canInstall.value = true;
    };

    const handleAppInstalled = () => {
        canInstall.value = false;
        isInstalled.value = true;
        deferredPrompt.value = null;
    };

    onMounted(() => {
        if (typeof window !== 'undefined') {
            window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
            window.addEventListener('appinstalled', handleAppInstalled);

            // Check if already in standalone display mode
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                isInstalled.value = true;
                canInstall.value = false;
            }
        }
    });

    onUnmounted(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
            window.removeEventListener('appinstalled', handleAppInstalled);
        }
    });

    const installPwa = async () => {
        if (!deferredPrompt.value) return false;

        deferredPrompt.value.prompt();
        const choiceResult = await deferredPrompt.value.userChoice;
        if (choiceResult.outcome === 'accepted') {
            canInstall.value = false;
            deferredPrompt.value = null;
            return true;
        }
        return false;
    };

    return {
        canInstall,
        isInstalled,
        installPwa,
    };
}

