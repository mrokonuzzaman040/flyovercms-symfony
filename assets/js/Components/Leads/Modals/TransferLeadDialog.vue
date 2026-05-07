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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Loader2, ArrowRightLeft, UserCheck, ChevronDown } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
    users: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open', 'submit']);

function handleClose() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <ArrowRightLeft class="h-4 w-4 text-cyan-500" />
                    Transfer Lead
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Transfer {{ lead?.full_name }} to another user
                </DialogDescription>
            </DialogHeader>
            <div class="py-3 space-y-4">
                <div class="space-y-1.5">
                    <Label class="text-xs">Transfer To *</Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline"
                                class="w-full h-9 text-xs justify-between hover:bg-accent/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span class="font-medium">
                                        {{ form.transferred_to ? users.find(u => String(u.id) === form.transferred_to)?.name || 'Select a user' : 'Select a user' }}
                                    </span>
                                </div>
                                <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="max-h-[300px] overflow-y-auto p-1.5"
                            style="width: var(--radix-dropdown-menu-trigger-width);">
                            <DropdownMenuRadioGroup v-model="form.transferred_to">
                                <div v-if="loading" class="p-4 text-center text-xs text-muted-foreground">
                                    <Loader2 class="h-4 w-4 animate-spin mx-auto mb-2" />
                                    Loading users...
                                </div>
                                <template v-else-if="users.length > 0">
                                    <DropdownMenuRadioItem v-for="user in users" :key="user.id"
                                        :value="String(user.id)"
                                        class="text-xs px-3 py-2.5 rounded-md transition-all cursor-pointer">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-2">
                                                <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                                <span>{{ user.name }}</span>
                                                <span v-if="user.branch" class="text-muted-foreground text-[10px]">({{ user.branch.name }})</span>
                                            </div>
                                        </div>
                                    </DropdownMenuRadioItem>
                                </template>
                                <div v-else class="p-4 text-center text-xs text-muted-foreground">
                                    No users available for transfer
                                </div>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Note (Optional)</Label>
                    <Textarea v-model="form.note" placeholder="Add a note about this transfer..."
                        class="text-xs min-h-[100px]" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="handleClose">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')"
                    :disabled="loading || !form.transferred_to">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Transfer Lead
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
