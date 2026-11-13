import { ref } from 'vue';

const toasts = ref([]);
let nextId = 1;

export function useToast() {
    function addToast({ type = 'info', title = '', message = '', duration = 4000 }) {
        const id = nextId++;
        const toast = { id, type, title, message, duration };
        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
        return id;
    }

    function removeToast(id) {
        const idx = toasts.value.findIndex(t => t.id === id);
        if (idx !== -1) {
            toasts.value.splice(idx, 1);
        }
    }

    function success(message, title = 'Berhasil') {
        return addToast({ type: 'success', title, message });
    }

    function error(message, title = 'Terjadi Kesalahan') {
        return addToast({ type: 'danger', title, message, duration: 6000 });
    }

    function warning(message, title = 'Peringatan') {
        return addToast({ type: 'warning', title, message, duration: 5000 });
    }

    function info(message, title = 'Informasi') {
        return addToast({ type: 'info', title, message });
    }

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    };
}

