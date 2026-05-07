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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Loader2, RefreshCw, ChevronDown } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
    getStatusColor: { type: Function, required: true },
});
const emit = defineEmits(['update:open', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <RefreshCw class="h-4 w-4 text-purple-500" />
                    Change Status
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Update status for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3">
                <div class="space-y-1.5">
                    <Label class="text-xs">Select Status</Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline"
                                class="w-full h-9 text-xs justify-between hover:bg-accent/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div v-if="form.status" class="w-2.5 h-2.5 rounded-full shadow-sm"
                                        :style="{ backgroundColor: getStatusColor(form.status) }"></div>
                                    <span class="font-medium">
                                        {{ statusOptions.find(s => s.value === form.status)?.label || 'Select Status' }}
                                    </span>
                                </div>
                                <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="max-h-[300px] overflow-y-auto p-1.5"
                            style="width: var(--radix-dropdown-menu-trigger-width);">
                            <DropdownMenuRadioGroup v-model="form.status">
                                <DropdownMenuRadioItem v-for="status in statusOptions" :key="status.value"
                                    :value="status.value"
                                    class="text-xs px-3 py-2.5 rounded-md transition-all cursor-pointer">
                                    <div class="flex items-center justify-between w-full gap-3">
                                        <div class="flex items-center gap-2.5 flex-1">
                                            <div class="w-2.5 h-2.5 rounded-full shadow-sm shrink-0"
                                                :style="{ backgroundColor: getStatusColor(status.value) }"></div>
                                            <span>{{ status.label }}</span>
                                        </div>
                                    </div>
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')" :disabled="loading">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Update Status
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
