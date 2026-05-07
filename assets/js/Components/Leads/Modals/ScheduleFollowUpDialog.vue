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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { CalendarPlus, ChevronDown, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, required: true },
    lead: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    form: { type: Object, required: true },
    users: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open', 'submit']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="open" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-sm flex items-center gap-2">
                    <CalendarPlus class="h-4 w-4 text-green-500" />
                    Schedule Follow-up
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Schedule a follow-up for {{ lead?.full_name }}
                </DialogDescription>
            </DialogHeader>
            <div class="py-3 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Date *</Label>
                        <Input v-model="form.follow_up_date" type="date" class="h-8 text-xs" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Time</Label>
                        <Input v-model="form.follow_up_time" type="time" class="h-8 text-xs" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Type</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{ form.follow_up_type ?
                                        form.follow_up_type.charAt(0).toUpperCase() +
                                        form.follow_up_type.slice(1) : 'Call' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.follow_up_type">
                                    <DropdownMenuRadioItem value="call" class="text-xs">Call</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="email" class="text-xs">Email</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="meeting" class="text-xs">Meeting</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="other" class="text-xs">Other</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Category</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{form.category ? form.category.split('_').map(w =>
                                        w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'General'}}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.category">
                                    <DropdownMenuRadioItem value="initial_contact" class="text-xs">Initial Contact</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="qualification" class="text-xs">Qualification</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="documentation" class="text-xs">Documentation</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="application" class="text-xs">Application</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="general" class="text-xs">General</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Priority</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{ form.priority ? form.priority.charAt(0).toUpperCase() +
                                        form.priority.slice(1) : 'Medium' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.priority">
                                    <DropdownMenuRadioItem value="low" class="text-xs">Low</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="medium" class="text-xs">Medium</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="high" class="text-xs">High</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="urgent" class="text-xs">Urgent</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Assign To</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{form.assigned_to ? users.find(u => String(u.id) ===
                                        form.assigned_to)?.name || 'Current User' : 'Current User'}}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.assigned_to">
                                    <DropdownMenuRadioItem value="" class="text-xs">Current User</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem v-for="user in users" :key="user.id"
                                        :value="String(user.id)" class="text-xs">
                                        {{ user.name }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Notes</Label>
                    <Textarea v-model="form.notes" placeholder="Follow-up notes..."
                        class="text-xs min-h-[80px]" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" size="sm" class="h-8 text-xs" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button size="sm" class="h-8 text-xs" @click="emit('submit')"
                    :disabled="loading || !form.follow_up_date">
                    <Loader2 v-if="loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    Schedule
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
