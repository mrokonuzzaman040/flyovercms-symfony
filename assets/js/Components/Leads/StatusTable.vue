<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { DataTable, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
    Phone,
    PhoneOutgoing,
    MessageCircle,
    History,
    CalendarCheck,
    RefreshCw,
    CheckCircle,
    XCircle,
    UserCheck,
    Building,
    StickyNote,
    CalendarPlus,
    Copy,
    Send,
    FileText,
    FileDown,
    Flag,
    ChevronDown,
    Briefcase,
    Package,
    Mail,
} from 'lucide-vue-next';

const props = defineProps({
    status: { type: String, required: true },
    leads: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    columns: { type: Array, required: true },
    statusOptions: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    formatDate: { type: Function, required: true },
    formatDateTime: { type: Function, required: true },
    isOverdue: { type: Function, required: true },
    getStatusColor: { type: Function, required: true },
    /** When provided, only actions with truthy value are shown (same keys as All leads). */
    actionVisibility: { type: Object, default: null },
});

const emit = defineEmits([
    'sort',
    'page-change',
    'per-page-change',
    'make-call',
    'open-whatsapp',
    'log-call',
    'call-history',
    'open-complete',
    'open-reschedule',
    'cancel-follow-up',
    'open-status',
    'open-priority',
    'open-note',
    'open-schedule',
    'copy-lead',
    'send-email',
    'delete-lead',
    'update-status',
    'update-assigned',
    'update-services-packages',
    'order-new-service',
]);

const { hasPermission } = usePermissions();

const isConvertedLead = (row) => ['converted', 'flight_done'].includes(row?.status);

const sortField = computed(() => typeof props.filters?.sort === 'string' ? props.filters.sort : '');
const sortDirection = computed(() => typeof props.filters?.direction === 'string' ? props.filters.direction : 'desc');
</script>

<template>
    <DataTable :columns="columns" :data="leads.data" :pagination="leads" :sort-field="sortField"
        :sort-direction="sortDirection" :searchable="false" @sort="emit('sort', $event)"
        @page-change="emit('page-change', $event)" @per-page-change="emit('per-page-change', $event)">
        <!-- Lead Name Cell -->
        <template #cell-full_name="{ row }">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0 relative">
                    <span class="text-xs font-medium text-primary">
                        {{ row.full_name?.charAt(0)?.toUpperCase() || '?' }}
                    </span>
                    <!-- Notes indicator -->
                    <div v-if="row.latest_note || row.notes_count > 0"
                        class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-yellow-500 border-2 border-background flex items-center justify-center"
                        title="Has notes">
                        <StickyNote class="h-1.5 w-1.5 text-white" />
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <Link :href="route('leads.show', row.id)" class="text-xs font-medium hover:underline truncate">
                            {{ row.full_name || 'Unnamed Lead' }}
                        </Link>
                        <!-- Priority indicator dot -->
                        <div v-if="row.priority === 'urgent'" class="h-1.5 w-1.5 rounded-full bg-red-500 shrink-0"
                            title="Urgent"></div>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span v-if="row.number_of_tour_persons" class="text-[10px] text-muted-foreground">
                            (P-{{ row.number_of_tour_persons }})
                        </span>
                        <span v-if="row.destination_country" class="text-[10px] text-muted-foreground truncate">
                            {{ row.destination_country }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Contact Cell -->
        <template #cell-contact="{ row }">
            <div class="space-y-0.5">
                <div v-if="row.phone" class="flex items-center gap-1 text-xs">
                    <Phone class="h-3 w-3 text-muted-foreground shrink-0" />
                    <span class="truncate">{{ row.phone }}</span>
                </div>
                <div v-if="row.email" class="flex items-center gap-1 text-[10px] text-muted-foreground">
                    <Mail class="h-3 w-3 shrink-0" />
                    <span class="truncate max-w-[120px]">{{ row.email }}</span>
                </div>
            </div>
        </template>

        <!-- Service Type Cell -->
        <template #cell-service_type="{ row }">
            <div class="space-y-0.5">
                <span v-if="row.service_type" class="text-xs capitalize">{{ row.service_type?.replace(/_/g, ' ')
                }}</span>
                <span v-else class="text-xs text-muted-foreground">-</span>
                <div v-if="row.vendor" class="text-[10px] text-muted-foreground">
                    <Building class="h-2.5 w-2.5 inline mr-0.5" />
                    {{ row.vendor?.name }}
                </div>
            </div>
        </template>

        <!-- Follow-up Date Cell (for follow_up status) -->
        <template #cell-follow_up_date="{ row }">
            <div v-if="row.follow_ups && row.follow_ups.length > 0" class="space-y-1.5">
                <div v-for="followUp in row.follow_ups.slice(0, 2)" :key="followUp.id"
                    class="flex items-start gap-1.5 group p-1.5 rounded-md hover:bg-muted/50 transition-colors">
                    <CalendarCheck :class="[
                        'h-3 w-3 shrink-0 mt-0.5',
                        isOverdue(followUp.follow_up_date) ? 'text-red-500' : 'text-green-500'
                    ]" />
                    <div class="flex flex-col flex-1 min-w-0">
                        <span :class="[
                            'text-xs',
                            isOverdue(followUp.follow_up_date) ? 'text-red-600 font-medium' : 'text-muted-foreground'
                        ]">
                            {{ formatDateTime(followUp.follow_up_date) }}
                        </span>
                        <div class="flex items-center gap-1 flex-wrap mt-1">
                            <Badge v-if="isOverdue(followUp.follow_up_date)" variant="destructive"
                                class="text-[9px] px-1 py-0 h-4">
                                Overdue
                            </Badge>
                            <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button v-if="hasPermission('edit-leads')" variant="ghost" size="sm"
                                    class="h-5 px-1.5 text-[9px]" @click="emit('open-complete', row, followUp)"
                                    title="Complete">
                                    <CheckCircle class="h-2.5 w-2.5 mr-0.5 text-green-600" />
                                    Complete
                                </Button>
                                <Button v-if="hasPermission('edit-leads')" variant="ghost" size="sm"
                                    class="h-5 px-1.5 text-[9px]" @click="emit('open-reschedule', row, followUp)"
                                    title="Reschedule">
                                    <RefreshCw class="h-2.5 w-2.5 mr-0.5 text-blue-600" />
                                    Reschedule
                                </Button>
                                <Button v-if="hasPermission('edit-leads')" variant="ghost" size="sm"
                                    class="h-5 px-1.5 text-[9px]" @click="emit('cancel-follow-up', row, followUp)"
                                    title="Cancel">
                                    <XCircle class="h-2.5 w-2.5 mr-0.5 text-red-600" />
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
                <span v-if="row.follow_ups.length > 2" class="text-[10px] text-muted-foreground pl-5">
                    +{{ row.follow_ups.length - 2 }} more
                </span>
            </div>
            <span v-else class="text-xs text-muted-foreground">-</span>
        </template>

        <!-- Status Cell -->
        <template #cell-status="{ row }">
            <DropdownMenu v-if="hasPermission('edit-leads') && !isConvertedLead(row)">
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm"
                        class="h-auto p-1.5 text-[10px] font-medium capitalize hover:bg-accent">
                        <Badge :style="{ backgroundColor: getStatusColor(row.status), color: 'white' }"
                            class="text-[10px] font-medium capitalize">
                            {{ row.status?.replace(/_/g, ' ') }}
                        </Badge>
                        <ChevronDown class="ml-1 h-3 w-3 opacity-50" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-56">
                    <DropdownMenuRadioGroup :model-value="row.status"
                        @update:model-value="(value) => emit('update-status', row.id, value)">
                        <DropdownMenuRadioItem v-for="statusOption in statusOptions" :key="statusOption.value"
                            :value="statusOption.value" class="text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full"
                                    :style="{ backgroundColor: getStatusColor(statusOption.value) }"></div>
                                {{ statusOption.label }}
                            </div>
                        </DropdownMenuRadioItem>
                    </DropdownMenuRadioGroup>
                </DropdownMenuContent>
            </DropdownMenu>
            <Badge v-else :style="{ backgroundColor: getStatusColor(row.status), color: 'white' }"
                class="text-[10px] font-medium capitalize">
                {{ row.status?.replace(/_/g, ' ') }}
            </Badge>
        </template>

        <!-- Priority Cell -->
        <template #cell-priority="{ row }">
            <StatusBadge v-if="row.priority" :status="row.priority" />
            <span v-else class="text-xs text-muted-foreground">-</span>
        </template>

        <!-- Assigned To Cell -->
        <template #cell-assigned_to="{ row }">
            <DropdownMenu v-if="hasPermission('edit-leads')">
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm" class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                        <UserCheck class="mr-1.5 h-3.5 w-3.5" />
                        <span class="truncate max-w-[120px]">
                            {{ row.assigned_user ? row.assigned_user.name : 'Unassigned' }}
                        </span>
                        <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                    <DropdownMenuRadioGroup :model-value="row.assigned_to ? String(row.assigned_to) : ''"
                        @update:model-value="(value) => emit('update-assigned', row.id, value ? Number(value) : null)">
                        <DropdownMenuRadioItem value="" class="text-xs">
                            <div class="flex items-center gap-2">
                                <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                <span>Unassigned</span>
                            </div>
                        </DropdownMenuRadioItem>
                        <DropdownMenuRadioItem v-for="user in users" :key="user.id" :value="String(user.id)"
                            class="text-xs">
                            <div class="flex items-center gap-2">
                                <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                <span>{{ user.name }}</span>
                                <span v-if="user.branch" class="text-muted-foreground text-[10px]">({{ user.branch.name
                                }})</span>
                            </div>
                        </DropdownMenuRadioItem>
                    </DropdownMenuRadioGroup>
                </DropdownMenuContent>
            </DropdownMenu>
            <div v-else class="text-xs text-muted-foreground">
                {{ row.assigned_user ? row.assigned_user.name : 'Unassigned' }}
            </div>
        </template>

        <!-- Notes Cell -->
        <template #cell-notes="{ row }">
            <div class="max-w-[200px] space-y-1.5">
                <div v-if="row.notes && row.notes.length > 0" class="space-y-1">
                    <div v-for="note in row.notes.slice(0, 1)" :key="note.id"
                        class="text-xs text-muted-foreground line-clamp-2" :title="note.note">
                        {{ note.note }}
                    </div>
                    <div v-if="row.notes.length > 1" class="text-[10px] text-muted-foreground">
                        +{{ row.notes.length - 1 }} more
                    </div>
                </div>
                <div v-else class="text-xs text-muted-foreground">No notes</div>
                <div class="flex items-center gap-1">
                    <Button v-if="hasPermission('edit-leads')" variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                        @click="emit('open-note', row)">
                        <StickyNote class="mr-1 h-3 w-3" />
                        {{ row.notes && row.notes.length > 0 ? 'Edit' : 'Add' }}
                    </Button>
                </div>
            </div>
        </template>

        <!-- Packages Cell -->
        <template #cell-packages="{ row }">
            <DropdownMenu v-if="hasPermission('edit-leads') && packages.length > 0">
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm" class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                        <Package class="mr-1.5 h-3.5 w-3.5" />
                        <span class="truncate max-w-[120px]">
                            {{row.packages && row.packages.length > 0
                                ? row.packages.map(p => p.name).join(', ')
                                : 'None'}}
                        </span>
                        <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                    <div class="px-2 py-1.5">
                        <div class="text-xs font-semibold mb-2">Select Packages</div>
                        <div class="space-y-1">
                            <label v-for="pkg in packages" :key="pkg.id"
                                class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                <Checkbox :checked="row.packages?.some(p => p.id === pkg.id) || false" @update:checked="(checked) => {
                                    const currentPackages = row.packages?.map(p => p.id) || [];
                                    const newPackages = checked
                                        ? [...currentPackages, pkg.id]
                                        : currentPackages.filter(id => id !== pkg.id);
                                    emit('update-services-packages', row.id, row.services?.map(s => s.id) || [], newPackages);
                                }" />
                                <span class="text-xs">{{ pkg.name }}</span>
                            </label>
                        </div>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
            <div v-else class="text-xs text-muted-foreground">
                {{row.packages && row.packages.length > 0
                    ? row.packages.map(p => p.name).join(', ')
                    : '-'}}
            </div>
        </template>

        <!-- Services Cell -->
        <template #cell-services="{ row }">
            <DropdownMenu v-if="hasPermission('edit-leads') && services.length > 0">
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm" class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                        <Briefcase class="mr-1.5 h-3.5 w-3.5" />
                        <span class="truncate max-w-[120px]">
                            {{row.services && row.services.length > 0
                                ? row.services.map(s => s.name).join(', ')
                                : 'None'}}
                        </span>
                        <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                    <div class="px-2 py-1.5">
                        <div class="text-xs font-semibold mb-2">Select Services</div>
                        <div class="space-y-1">
                            <label v-for="service in services" :key="service.id"
                                class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                <Checkbox :checked="row.services?.some(s => s.id === service.id) || false"
                                    @update:checked="(checked) => {
                                        const currentServices = row.services?.map(s => s.id) || [];
                                        const newServices = checked
                                            ? [...currentServices, service.id]
                                            : currentServices.filter(id => id !== service.id);
                                        emit('update-services-packages', row.id, newServices, row.packages?.map(p => p.id) || []);
                                    }" />
                                <span class="text-xs">{{ service.name }}</span>
                            </label>
                        </div>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
            <div v-else class="text-xs text-muted-foreground">
                {{row.services && row.services.length > 0
                    ? row.services.map(s => s.name).join(', ')
                    : '-'}}
            </div>
        </template>

        <!-- Created Date Cell -->
        <template #cell-created_at="{ row }">
            <span class="text-[10px] text-muted-foreground">{{ formatDate(row.created_at) }}</span>
        </template>

        <!-- Actions Cell -->
        <template #cell-actions="{ row }">
            <div class="flex items-center gap-1">
                <!-- Call Button -->
                <Button v-if="(!actionVisibility || actionVisibility.call) && row.phone" variant="ghost" size="icon"
                    class="h-7 w-7 text-green-600 hover:text-green-700 hover:bg-green-50"
                    @click="emit('make-call', row.phone)" title="Call">
                    <Phone class="h-3.5 w-3.5" />
                </Button>

                <!-- WhatsApp Button -->
                <Button v-if="(!actionVisibility || actionVisibility.whatsapp) && row.phone" variant="ghost" size="icon"
                    class="h-7 w-7 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                    @click="emit('open-whatsapp', row.phone)" title="WhatsApp">
                    <MessageCircle class="h-3.5 w-3.5" />
                </Button>

                <!-- Log Call Button -->
                <Button v-if="(!actionVisibility || actionVisibility.log_call) && row.phone && hasPermission('edit-leads')" variant="ghost" size="icon"
                    class="h-7 w-7 text-purple-600 hover:text-purple-700 hover:bg-purple-50"
                    @click="emit('log-call', row)" title="Log Call">
                    <PhoneOutgoing class="h-3.5 w-3.5" />
                </Button>

                <!-- Call History Button -->
                <Button v-if="(!actionVisibility || actionVisibility.call_history) && row.phone" variant="ghost" size="icon"
                    class="h-7 w-7 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                    @click="emit('call-history', row)" title="Call History">
                    <History class="h-3.5 w-3.5" />
                </Button>

                <!-- Schedule Follow-up Button -->
                <Button v-if="(!actionVisibility || actionVisibility.follow_up) && hasPermission('edit-leads')" variant="ghost" size="icon"
                    class="h-7 w-7 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                    @click="emit('open-schedule', row)" title="Schedule Follow-up">
                    <CalendarPlus class="h-3.5 w-3.5" />
                </Button>

                <!-- Reschedule Follow-up Button -->
                <Button
                    v-if="(!actionVisibility || actionVisibility.follow_up) && (status === 'follow_up' || status === 'overdue_follow_ups') && hasPermission('edit-leads') && row.follow_ups && row.follow_ups.length > 0 && row.follow_ups[0]"
                    variant="ghost" size="icon" class="h-7 w-7 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                    @click="emit('open-reschedule', row, row.follow_ups[0])" title="Reschedule Follow-up">
                    <RefreshCw class="h-3.5 w-3.5" />
                </Button>

                <!-- More Actions Dropdown -->
                <DropdownMenu v-if="!actionVisibility || actionVisibility.more_actions">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-7 w-7">
                            <MoreHorizontal class="h-3.5 w-3.5" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-52">
                        <!-- View & Edit Section -->
                        <DropdownMenuItem v-if="(!actionVisibility || actionVisibility.view_details) && row?.id" as-child>
                            <Link :href="route('leads.show', row.id)" class="flex items-center text-xs cursor-pointer">
                                <Eye class="mr-2 h-3.5 w-3.5" /> View Details
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="(!actionVisibility || actionVisibility.edit_lead) && hasPermission('edit-leads') && row?.id" as-child>
                            <Link :href="route('leads.edit', row.id)" class="flex items-center text-xs cursor-pointer">
                                <Pencil class="mr-2 h-3.5 w-3.5" /> Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-if="status === 'converted' && hasPermission('create-leads') && row?.id"
                            class="flex items-center text-xs cursor-pointer"
                            @click.prevent="emit('order-new-service', row)"
                        >
                            <Package class="mr-2 h-3.5 w-3.5 text-emerald-500" /> Order new service
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <!-- Quick Actions Section -->
                        <DropdownMenuItem v-if="hasPermission('edit-leads') && !isConvertedLead(row)" @click.prevent="emit('open-status', row)"
                            class="flex items-center text-xs">
                            <RefreshCw class="mr-2 h-3.5 w-3.5 text-purple-500" /> Change Status
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="hasPermission('edit-leads')" @click.prevent="emit('open-priority', row)"
                            class="flex items-center text-xs">
                            <Flag class="mr-2 h-3.5 w-3.5 text-orange-500" /> Change Priority
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="(!actionVisibility || actionVisibility.add_note) && hasPermission('edit-leads')" @click.prevent="emit('open-note', row)"
                            class="flex items-center text-xs">
                            <StickyNote class="mr-2 h-3.5 w-3.5 text-yellow-500" /> Add/Edit Note
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="(!actionVisibility || actionVisibility.follow_up) && hasPermission('edit-leads')" @click.prevent="emit('open-schedule', row)"
                            class="flex items-center text-xs">
                            <CalendarPlus class="mr-2 h-3.5 w-3.5 text-green-500" /> Schedule Follow-up
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-if="(!actionVisibility || actionVisibility.follow_up) && hasPermission('edit-leads') && row.follow_ups && row.follow_ups.length > 0 && row.follow_ups[0]"
                            @click.prevent="emit('open-reschedule', row, row.follow_ups[0])"
                            class="flex items-center text-xs">
                            <RefreshCw class="mr-2 h-3.5 w-3.5 text-blue-500" /> Reschedule Follow-up
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <!-- Communication Section -->
                        <DropdownMenuItem v-if="(!actionVisibility || actionVisibility.send_email) && row?.email" @click.prevent="emit('send-email', row.email)"
                            class="flex items-center text-xs">
                            <Send class="mr-2 h-3.5 w-3.5 text-blue-500" /> Send Email
                        </DropdownMenuItem>
                        <DropdownMenuItem @click.prevent="emit('copy-lead', row)" class="flex items-center text-xs">
                            <Copy class="mr-2 h-3.5 w-3.5 text-slate-500" /> Copy Lead Info
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <!-- Navigation Section -->
                        <DropdownMenuItem v-if="row?.id" as-child>
                            <Link :href="route('leads.documents.index', row.id)" class="flex items-center text-xs">
                                <FileText class="mr-2 h-3.5 w-3.5 text-indigo-500" /> View Documents
                            </Link>
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <!-- Danger Zone -->
                        <DropdownMenuItem v-if="hasPermission('delete-leads')" class="text-destructive text-xs"
                            @click.prevent="emit('delete-lead', row)">
                            <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete Lead
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>
    </DataTable>
</template>
