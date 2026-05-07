<script setup>
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { ScrollArea } from '@/Components/ui/scroll-area';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['sm', 'default', 'lg', 'xl', 'full'].includes(value),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    submitLabel: {
        type: String,
        default: 'Save',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    showFooter: {
        type: Boolean,
        default: true,
    },
    submitDisabled: {
        type: Boolean,
        default: false,
    },
    destructive: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:open', 'submit', 'cancel']);

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'max-w-md',
        default: 'max-w-lg',
        lg: 'max-w-2xl',
        xl: 'max-w-4xl',
        full: 'max-w-[90vw]',
    };
    return sizes[props.size] || sizes.default;
});

const handleOpenChange = (value) => {
    emit('update:open', value);
    if (!value) {
        emit('cancel');
    }
};

const handleSubmit = () => {
    emit('submit');
};

const handleCancel = () => {
    emit('update:open', false);
    emit('cancel');
};
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent :class="['gap-0 p-0', sizeClasses]">
            <DialogHeader class="px-6 py-4 border-b">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{ description }}</DialogDescription>
            </DialogHeader>
            
            <ScrollArea class="max-h-[calc(80vh-8rem)]">
                <div class="px-6 py-4">
                    <slot />
                </div>
            </ScrollArea>
            
            <DialogFooter v-if="showFooter" class="px-6 py-4 border-t bg-muted/30">
                <Button variant="outline" @click="handleCancel" :disabled="loading">
                    {{ cancelLabel }}
                </Button>
                <Button 
                    :variant="destructive ? 'destructive' : 'default'"
                    @click="handleSubmit" 
                    :disabled="loading || submitDisabled"
                >
                    <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                    {{ submitLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
