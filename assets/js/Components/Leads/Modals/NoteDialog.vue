<script setup>
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Loader2, StickyNote } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
});
const emit = defineEmits(['update:open', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <StickyNote class="h-4 w-4 text-yellow-500" />
                    Add Note
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Add a note for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3">
                <div class="space-y-1.5">
                    <Label class="text-xs">Note</Label>
                    <Textarea v-model="form.note" placeholder="Enter your note here..."
                        class="text-xs min-h-[120px]" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')"
                    :disabled="loading || !form.note?.trim()">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Save Note
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
