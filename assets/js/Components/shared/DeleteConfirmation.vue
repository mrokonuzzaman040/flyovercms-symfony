<script setup>
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Loader2, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Are you sure?',
    },
    description: {
        type: String,
        default: 'This action cannot be undone. This will permanently delete this item.',
    },
    confirmLabel: {
        type: String,
        default: 'Delete',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'destructive',
        validator: (value) => ['destructive', 'warning', 'default'].includes(value),
    },
});

const emit = defineEmits(['update:open', 'confirm', 'cancel']);

const handleOpenChange = (value) => {
    emit('update:open', value);
    if (!value) {
        emit('cancel');
    }
};

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('update:open', false);
    emit('cancel');
};
</script>

<template>
    <AlertDialog :open="open" @update:open="handleOpenChange">
        <AlertDialogContent>
            <AlertDialogHeader>
                <div class="flex items-center gap-3">
                    <div 
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                        :class="{
                            'bg-destructive/10': variant === 'destructive',
                            'bg-yellow-100 dark:bg-yellow-900/20': variant === 'warning',
                            'bg-muted': variant === 'default',
                        }"
                    >
                        <AlertTriangle 
                            class="h-5 w-5"
                            :class="{
                                'text-destructive': variant === 'destructive',
                                'text-yellow-600 dark:text-yellow-500': variant === 'warning',
                                'text-muted-foreground': variant === 'default',
                            }"
                        />
                    </div>
                    <div>
                        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
                    </div>
                </div>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel as-child>
                    <Button variant="outline" @click="handleCancel" :disabled="loading">
                        {{ cancelLabel }}
                    </Button>
                </AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button 
                        :variant="variant === 'destructive' ? 'destructive' : 'default'"
                        @click="handleConfirm"
                        :disabled="loading"
                    >
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        {{ confirmLabel }}
                    </Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
