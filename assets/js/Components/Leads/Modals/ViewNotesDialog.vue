<script setup>
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { StickyNote } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    notes: { type: Array, default: () => [] },
    formatDateTime: { type: Function, required: true },
});
const emit = defineEmits(['update:open']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <StickyNote class="h-4 w-4 text-primary" />
                    Lead Notes
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Notes for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3">
                <div v-if="notes.length === 0" class="text-center text-xs text-muted-foreground py-4">
                    No notes found.
                </div>
                <div v-else class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                    <div v-for="note in notes" :key="note.id"
                        class="p-3 bg-muted/50 rounded-lg space-y-1">
                        <p class="text-xs whitespace-pre-wrap">{{ note.note }}</p>
                        <div class="flex items-center justify-between text-[10px] text-muted-foreground pt-1">
                            <span>{{ note.created_at ? formatDateTime(note.created_at) : '-' }}</span>
                            <span v-if="note.user" class="font-medium">{{ note.user.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
