<script setup>
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Loader2, History, Clock } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    history: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="text-sm">Call History</DialogTitle>
                <DialogDescription class="text-xs">
                    Call history for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3">
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
                </div>
                <div v-else-if="history.length === 0" class="text-center py-8">
                    <History class="h-10 w-10 mx-auto text-muted-foreground mb-2" />
                    <p class="text-xs text-muted-foreground">No call history found</p>
                </div>
                <div v-else class="space-y-2 max-h-[400px] overflow-y-auto">
                    <div v-for="call in history" :key="call.id"
                        class="p-3 rounded-lg border bg-muted/30">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <Badge :variant="call.call_type === 'incoming' ? 'secondary' : 'default'"
                                    class="text-[10px] capitalize">
                                    {{ call.call_type }}
                                </Badge>
                                <Badge :class="{
                                    'bg-green-100 text-green-700': call.status === 'completed',
                                    'bg-yellow-100 text-yellow-700': call.status === 'no_answer',
                                    'bg-red-100 text-red-700': call.status === 'busy',
                                    'bg-gray-100 text-gray-700': !['completed', 'no_answer', 'busy'].includes(call.status),
                                }" class="text-[10px] capitalize">
                                    {{ call.status?.replace(/_/g, ' ') }}
                                </Badge>
                            </div>
                            <span v-if="call.duration" class="text-[10px] text-muted-foreground">
                                {{ call.duration }} min
                            </span>
                        </div>
                        <p v-if="call.notes" class="text-xs text-muted-foreground mb-1.5">{{ call.notes }}</p>
                        <div class="flex items-center gap-2 text-[10px] text-muted-foreground">
                            <Clock class="h-3 w-3" />
                            {{ call.call_date }}
                            <span v-if="call.called_by">• {{ call.called_by.name }}</span>
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
