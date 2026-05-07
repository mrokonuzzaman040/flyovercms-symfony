<script setup>
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Loader2, Flag } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
    priorityOptions: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <Flag class="h-4 w-4 text-orange-500" />
                    Change Priority
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Update priority for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3">
                <div class="space-y-2">
                    <Label class="text-xs">Select Priority</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <Button v-for="p in priorityOptions" :key="p.value"
                            :variant="form.priority === p.value ? 'default' : 'outline'" size="sm"
                            class="h-9 text-xs justify-start" @click="form.priority = p.value">
                            <div class="w-2 h-2 rounded-full mr-2" :class="{
                                'bg-red-500': p.value === 'urgent',
                                'bg-orange-500': p.value === 'high',
                                'bg-gray-500': p.value === 'normal',
                                'bg-blue-500': p.value === 'low',
                            }"></div>
                            {{ p.label }}
                        </Button>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')" :disabled="loading">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Update Priority
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
