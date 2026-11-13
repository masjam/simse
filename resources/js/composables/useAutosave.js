import { ref } from 'vue';

export function useAutosave(saveCallback, delayMs = 1000) {
    const status = ref('idle'); // 'idle' | 'saving' | 'saved' | 'error'
    const lastSavedAt = ref(null);
    const errorMessage = ref(null);
    let timer = null;

    function trigger(...args) {
        status.value = 'idle';
        clearTimeout(timer);
        timer = setTimeout(async () => {
            status.value = 'saving';
            errorMessage.value = null;
            try {
                await saveCallback(...args);
                status.value = 'saved';
                lastSavedAt.value = new Date();
                setTimeout(() => {
                    if (status.value === 'saved') {
                        status.value = 'idle';
                    }
                }, 3000);
            } catch (err) {
                status.value = 'error';
                errorMessage.value = err?.message || 'Gagal menyimpan otomatis.';
            }
        }, delayMs);
    }

    function cancel() {
        clearTimeout(timer);
        status.value = 'idle';
    }

    return {
        status,
        lastSavedAt,
        errorMessage,
        trigger,
        cancel,
    };
}

