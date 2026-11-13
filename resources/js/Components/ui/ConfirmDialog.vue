<script setup>
import { useConfirm } from '@/composables/useConfirm';
import Modal from './Modal.vue';
import AppButton from './AppButton.vue';

const { confirmState, handleConfirm, handleCancel } = useConfirm();
</script>

<template>
    <Modal
        :show="confirmState.isOpen"
        :title="confirmState.title"
        max-width="sm"
        :closeable="true"
        @close="handleCancel"
    >
        <p class="text-sm text-ink-muted leading-relaxed">
            {{ confirmState.message }}
        </p>

        <template #footer>
            <AppButton variant="secondary" size="sm" @click="handleCancel">
                {{ confirmState.cancelText }}
            </AppButton>
            <AppButton
                :variant="confirmState.variant === 'danger' ? 'danger' : 'primary'"
                size="sm"
                @click="handleConfirm"
            >
                {{ confirmState.confirmText }}
            </AppButton>
        </template>
    </Modal>
</template>

