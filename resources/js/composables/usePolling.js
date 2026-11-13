import { onMounted, onUnmounted, ref } from 'vue';

export function usePolling(callback, intervalMs = 15000, { immediate = false, autoStart = true } = {}) {
    let timer = null;
    const isPolling = ref(false);

    function start() {
        if (timer) return;
        isPolling.value = true;
        if (immediate) {
            callback();
        }
        timer = setInterval(() => {
            if (typeof document !== 'undefined' && document.hidden) {
                // Skip tick when tab is hidden to save network and battery
                return;
            }
            callback();
        }, intervalMs);
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        isPolling.value = false;
    }

    function handleVisibilityChange() {
        if (typeof document === 'undefined') return;
        if (!document.hidden && isPolling.value) {
            // Run immediate update upon returning to tab
            callback();
        }
    }

    onMounted(() => {
        if (typeof document !== 'undefined') {
            document.addEventListener('visibilitychange', handleVisibilityChange);
        }
        if (autoStart) {
            start();
        }
    });

    onUnmounted(() => {
        stop();
        if (typeof document !== 'undefined') {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        }
    });

    return {
        isPolling,
        start,
        stop,
    };
}

