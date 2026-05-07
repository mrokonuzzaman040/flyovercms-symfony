<script setup>
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Loader2 } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
    callTypeOptions: { type: Array, default: () => [] },
    callStatusOptions: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-sm">Log Call</DialogTitle>
                <DialogDescription class="text-xs">
                    Record a call with {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-3 py-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Call Type</Label>
                        <Select v-model="form.call_type">
                            <SelectTrigger class="h-8 text-xs">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in callTypeOptions" :key="opt.value" :value="opt.value"
                                    class="text-xs">
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Call Status</Label>
                        <Select v-model="form.call_status">
                            <SelectTrigger class="h-8 text-xs">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in callStatusOptions" :key="opt.value" :value="opt.value"
                                    class="text-xs">
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Duration (minutes)</Label>
                    <Input v-model="form.duration_minutes" type="number" min="0" placeholder="Enter duration"
                        class="h-8 text-xs" />
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Notes</Label>
                    <Textarea v-model="form.notes" placeholder="Call notes..."
                        class="text-xs min-h-[80px]" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')" :disabled="loading">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Save Call Log
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
