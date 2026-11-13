import { ref } from 'vue';

const state = ref({
    isOpen: false,
    title: 'Konfirmasi Aksi',
    message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    confirmText: 'Lanjutkan',
    cancelText: 'Batal',
    variant: 'danger', // 'danger' | 'primary' | 'warning'
    resolve: null,
});

export function useConfirm() {
    function confirm({
        title = 'Konfirmasi Aksi',
        message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText = 'Lanjutkan',
        cancelText = 'Batal',
        variant = 'danger',
    } = {}) {
        state.value = {
            isOpen: true,
            title,
            message,
            confirmText,
            cancelText,
            variant,
            resolve: null,
        };

        return new Promise((resolve) => {
            state.value.resolve = resolve;
        });
    }

    function handleConfirm() {
        if (state.value.resolve) {
            state.value.resolve(true);
        }
        state.value.isOpen = false;
    }

    function handleCancel() {
        if (state.value.resolve) {
            state.value.resolve(false);
        }
        state.value.isOpen = false;
    }

    return {
        confirmState: state,
        confirm,
        handleConfirm,
        handleCancel,
    };
}

