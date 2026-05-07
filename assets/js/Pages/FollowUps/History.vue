<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
} from '@/Components/ui/dropdown-menu';
import {
    History,
    Calendar,
    Clock,
    CheckCircle2,
    XCircle,
    User,
    Filter,
    ArrowLeft,
    Eye,
    Phone,
    MessageCircle,
    CalendarCheck,
    ChevronDown,
    Loader2,
} from 'lucide-vue-next';
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
import { Textarea } from '@/Components/ui/textarea';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    followUps: { type: Object, required: true },
    officeVisits: { type: Object, required: true },
    dateFilter: { type: String, default: 'today' },
    typeFilter: { type: String, default: 'all' },
});

const dateFilter = ref(props.dateFilter);
const typeFilter = ref(props.typeFilter);
const customDate = ref('');

// Completion modal
const completionModal = ref({ open: false, followUpId: null, loading: false });
const completionForm = ref({
    outcome: '',
    outcome_notes: '',
    duration_minutes: '',
    status: 'completed',
});

const applyFilters = () => {
    const params = {};
    if (dateFilter.value === 'custom' && customDate.value) {
        params.date = customDate.value;
    } else if (dateFilter.value !== 'all') {
        params.date = dateFilter.value;
    }
    if (typeFilter.value !== 'all') {
        params.type = typeFilter.value;
    }
    router.get(route('follow-ups.history'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString;
};

// Follow-up columns
const followUpColumns = [
    { key: 'lead', label: 'Lead', sortable: false },
    { key: 'follow_up_date', label: 'Follow-up Date', sortable: true },
    { key: 'follow_up_type', label: 'Type', sortable: false },
    { key: 'category', label: 'Category', sortable: false },
    { key: 'priority', label: 'Priority', sortable: false },
    { key: 'assigned_to', label: 'Assigned To', sortable: false },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'outcome', label: 'Outcome', sortable: false },
    { key: 'notes', label: 'Notes', sortable: false },
    { key: 'creator', label: 'Created By', sortable: false },
    { key: 'completed_by', label: 'Completed By', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' },
];

// Office visit columns
const officeVisitColumns = [
    { key: 'lead', label: 'Lead', sortable: false },
    { key: 'scheduled_date', label: 'Scheduled Date', sortable: true },
    { key: 'purpose', label: 'Purpose', sortable: false },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'notes', label: 'Notes', sortable: false },
    { key: 'creator', label: 'Created By', sortable: false },
    { key: 'completed_by', label: 'Completed By', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' },
];

const openCompleteModal = (followUpId) => {
    completionModal.value = { open: true, followUpId, loading: false };
    completionForm.value = {
        outcome: '',
        outcome_notes: '',
        duration_minutes: '',
        status: 'completed',
    };
};

const completeFollowUp = async () => {
    if (!completionModal.value.followUpId) return;
    
    completionModal.value.loading = true;
    
    try {
        router.patch(
            route('follow-ups.complete', completionModal.value.followUpId),
            completionForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    completionModal.value.open = false;
                    router.reload({ only: ['followUps', 'officeVisits'] });
                },
                onError: (errors) => {
                    console.error('Failed to complete follow-up:', errors);
                },
            }
        );
    } catch (error) {
        console.error('Failed to complete follow-up:', error);
    } finally {
        completionModal.value.loading = false;
    }
};

const completeOfficeVisit = async (visitId) => {
    if (!confirm('Mark this office visit as completed?')) return;
    
    try {
        router.patch(route('office-visits.complete', visitId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['followUps', 'officeVisits'] });
            },
        });
    } catch (error) {
        console.error('Failed to complete office visit:', error);
    }
};

const makeCall = (phone) => {
    if (!phone) return;
    window.location.href = `tel:${phone.replace(/\D+/g, '')}`;
};

const openWhatsApp = (phone) => {
    if (!phone) return;
    window.open(`https://wa.me/${phone.replace(/\D+/g, '')}`, '_blank');
};
</script>

<template>
    <Head title="Follow-up & Office Visit History" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Follow-up & Office Visit History</h1>
                <p class="text-sm text-muted-foreground mt-1">View history of follow-ups and office visits</p>
            </div>
            <Link 
                :href="route('leads.status', 'follow_up')"
                class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Follow-ups
            </Link>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Filter class="h-4 w-4" />
                    Filters
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Date Filter Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-9 text-xs justify-between min-w-[140px]">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-3.5 w-3.5" />
                                    <span>
                                        {{ dateFilter === 'today' ? 'Today' : dateFilter === 'all' ? 'All Dates' : dateFilter === 'custom' ? 'Custom Date' : 'Select Date' }}
                                    </span>
                                </div>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-48">
                            <DropdownMenuLabel class="text-xs">Date Filter</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuRadioGroup :model-value="dateFilter" @update:model-value="(value) => { dateFilter = value; if (value !== 'custom') applyFilters(); }">
                                <DropdownMenuRadioItem value="today" class="text-xs cursor-pointer">
                                    <Calendar class="mr-2 h-3.5 w-3.5" />
                                    Today
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="all" class="text-xs cursor-pointer">
                                    <History class="mr-2 h-3.5 w-3.5" />
                                    All Dates
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="custom" class="text-xs cursor-pointer">
                                    <CalendarCheck class="mr-2 h-3.5 w-3.5" />
                                    Custom Date
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Custom Date Input -->
                    <div v-if="dateFilter === 'custom'" class="flex items-center gap-2">
                        <Input
                            v-model="customDate"
                            type="date"
                            class="h-9 text-xs w-[160px]"
                            @change="applyFilters"
                            placeholder="Select date"
                        />
                    </div>

                    <!-- Type Filter Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-9 text-xs justify-between min-w-[140px]">
                                <div class="flex items-center gap-2">
                                    <Filter class="h-3.5 w-3.5" />
                                    <span>
                                        {{ typeFilter === 'all' ? 'All Types' : typeFilter === 'followup' ? 'Follow-ups Only' : 'Office Visits Only' }}
                                    </span>
                                </div>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-48">
                            <DropdownMenuLabel class="text-xs">Type Filter</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuRadioGroup :model-value="typeFilter" @update:model-value="(value) => { typeFilter = value; applyFilters(); }">
                                <DropdownMenuRadioItem value="all" class="text-xs cursor-pointer">
                                    <Filter class="mr-2 h-3.5 w-3.5" />
                                    All Types
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="followup" class="text-xs cursor-pointer">
                                    <Clock class="mr-2 h-3.5 w-3.5" />
                                    Follow-ups Only
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="office_visit" class="text-xs cursor-pointer">
                                    <Calendar class="mr-2 h-3.5 w-3.5" />
                                    Office Visits Only
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Active Filters Badge -->
                    <div v-if="dateFilter !== 'today' || typeFilter !== 'all'" class="flex items-center gap-2 ml-auto">
                        <Badge variant="secondary" class="text-xs">
                            {{ (dateFilter !== 'today' ? 1 : 0) + (typeFilter !== 'all' ? 1 : 0) }} filter{{ (dateFilter !== 'today' ? 1 : 0) + (typeFilter !== 'all' ? 1 : 0) === 1 ? '' : 's' }} active
                        </Badge>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-7 text-xs"
                            @click="dateFilter = 'today'; typeFilter = 'all'; customDate = ''; applyFilters();"
                        >
                            Clear Filters
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Follow-ups Table -->
        <Card v-if="typeFilter === 'all' || typeFilter === 'followup'">
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Clock class="h-4 w-4 text-primary" />
                    Follow-ups
                    <Badge variant="secondary" class="ml-2 text-xs">
                        {{ followUps.total || 0 }}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <DataTable
                    v-if="followUps.data && followUps.data.length > 0"
                    :columns="followUpColumns"
                    :data="followUps.data"
                    :pagination="followUps"
                >
                    <!-- Lead Cell -->
                    <template #cell-lead="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                                <span class="text-xs font-medium text-primary">
                                    {{ row.lead?.full_name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <Link :href="route('leads.show', row.lead_id)" class="text-xs font-medium hover:underline truncate block">
                                    {{ row.lead?.full_name || 'Unknown Lead' }}
                                </Link>
                                <div v-if="row.lead?.phone" class="flex items-center gap-1 mt-0.5">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-5 w-5 p-0 text-green-600 hover:text-green-700 hover:bg-green-50"
                                        @click="makeCall(row.lead.phone)"
                                        title="Call"
                                    >
                                        <Phone class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-5 w-5 p-0 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                                        @click="openWhatsApp(row.lead.phone)"
                                        title="WhatsApp"
                                    >
                                        <MessageCircle class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Follow-up Date Cell -->
                    <template #cell-follow_up_date="{ row }">
                        <div class="flex items-center gap-2">
                            <CalendarCheck 
                                :class="[
                                    'h-3 w-3 shrink-0',
                                    row.is_completed ? 'text-green-500' : new Date(row.follow_up_date) < new Date() ? 'text-red-500' : 'text-blue-500'
                                ]"
                            />
                            <div>
                                <div class="text-xs font-medium">{{ formatDateTime(row.follow_up_date) }}</div>
                                <div class="text-[10px] text-muted-foreground">{{ formatDate(row.created_at) }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Type Cell -->
                    <template #cell-follow_up_type="{ row }">
                        <Badge variant="outline" class="text-xs capitalize">
                            {{ row.follow_up_type || 'call' }}
                        </Badge>
                    </template>

                    <!-- Category Cell -->
                    <template #cell-category="{ row }">
                        <Badge variant="secondary" class="text-xs capitalize">
                            {{ row.category || 'general' }}
                        </Badge>
                    </template>

                    <!-- Priority Cell -->
                    <template #cell-priority="{ row }">
                        <Badge
                            :class="{
                                'bg-gray-100 text-gray-800': row.priority === 'low',
                                'bg-blue-100 text-blue-800': row.priority === 'medium',
                                'bg-orange-100 text-orange-800': row.priority === 'high',
                                'bg-red-100 text-red-800': row.priority === 'urgent',
                            }"
                            class="text-xs capitalize"
                        >
                            {{ row.priority || 'medium' }}
                        </Badge>
                    </template>

                    <!-- Assigned To Cell -->
                    <template #cell-assigned_to="{ row }">
                        <div v-if="row.assigned_user" class="flex items-center gap-1.5">
                            <User class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-xs">{{ row.assigned_user?.name || 'Unknown' }}</span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-status="{ row }">
                        <Badge
                            :class="{
                                'bg-blue-100 text-blue-800': row.status === 'scheduled',
                                'bg-yellow-100 text-yellow-800': row.status === 'in_progress',
                                'bg-green-100 text-green-800': row.status === 'completed' || row.is_completed,
                                'bg-gray-100 text-gray-800': row.status === 'cancelled',
                                'bg-red-100 text-red-800': row.status === 'no_show',
                            }"
                            class="text-xs capitalize"
                        >
                            {{ row.status || (row.is_completed ? 'completed' : 'scheduled') }}
                        </Badge>
                    </template>

                    <!-- Outcome Cell -->
                    <template #cell-outcome="{ row }">
                        <Badge
                            v-if="row.outcome"
                            variant="outline"
                            class="text-xs capitalize"
                        >
                            {{ row.outcome }}
                        </Badge>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Notes Cell -->
                    <template #cell-notes="{ row }">
                        <span v-if="row.notes" class="text-xs text-muted-foreground line-clamp-2 max-w-[200px]">
                            {{ row.notes }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Creator Cell -->
                    <template #cell-creator="{ row }">
                        <div class="flex items-center gap-1.5">
                            <User class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-xs">{{ row.creator?.name || 'Unknown' }}</span>
                        </div>
                    </template>

                    <!-- Completed By Cell -->
                    <template #cell-completed_by="{ row }">
                        <div v-if="row.is_completed && row.completed_by" class="flex items-center gap-1.5">
                            <CheckCircle2 class="h-3 w-3 text-green-600 shrink-0" />
                            <span class="text-xs">{{ row.completed_by?.name || 'Unknown' }}</span>
                            <span v-if="row.completed_at" class="text-[10px] text-muted-foreground">
                                ({{ formatDate(row.completed_at) }})
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-1">
                            <Link :href="route('leads.show', row.lead_id)">
                                <Button variant="ghost" size="icon" class="h-7 w-7">
                                    <Eye class="h-3.5 w-3.5" />
                                </Button>
                            </Link>
                            <Button
                                v-if="!row.is_completed"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="openCompleteModal(row.id)"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                                Complete
                            </Button>
                        </div>
                    </template>
                </DataTable>
                <div v-else class="text-center py-12">
                    <Clock class="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p class="text-sm font-medium mb-1">No follow-ups found</p>
                    <p class="text-xs text-muted-foreground">Try adjusting your filters</p>
                </div>
            </CardContent>
        </Card>

        <!-- Office Visits Table -->
        <Card v-if="typeFilter === 'all' || typeFilter === 'office_visit'">
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Calendar class="h-4 w-4 text-primary" />
                    Office Visits
                    <Badge variant="secondary" class="ml-2 text-xs">
                        {{ officeVisits.total || 0 }}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <DataTable
                    v-if="officeVisits.data && officeVisits.data.length > 0"
                    :columns="officeVisitColumns"
                    :data="officeVisits.data"
                    :pagination="officeVisits"
                >
                    <!-- Lead Cell -->
                    <template #cell-lead="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                                <span class="text-xs font-medium text-primary">
                                    {{ row.lead?.full_name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <Link :href="route('leads.show', row.lead_id)" class="text-xs font-medium hover:underline truncate block">
                                    {{ row.lead?.full_name || 'Unknown Lead' }}
                                </Link>
                                <div v-if="row.lead?.phone" class="flex items-center gap-1 mt-0.5">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-5 w-5 p-0 text-green-600 hover:text-green-700 hover:bg-green-50"
                                        @click="makeCall(row.lead.phone)"
                                        title="Call"
                                    >
                                        <Phone class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-5 w-5 p-0 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                                        @click="openWhatsApp(row.lead.phone)"
                                        title="WhatsApp"
                                    >
                                        <MessageCircle class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Scheduled Date Cell -->
                    <template #cell-scheduled_date="{ row }">
                        <div class="flex items-center gap-2">
                            <Calendar 
                                :class="[
                                    'h-3 w-3 shrink-0',
                                    row.is_completed ? 'text-green-500' : new Date(row.scheduled_date) < new Date() ? 'text-red-500' : 'text-blue-500'
                                ]"
                            />
                            <div>
                                <div class="text-xs font-medium">
                                    {{ formatDate(row.scheduled_date) }}
                                    <span v-if="row.scheduled_time" class="text-muted-foreground ml-1">
                                        {{ formatTime(row.scheduled_time) }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-muted-foreground">{{ formatDate(row.created_at) }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Purpose Cell -->
                    <template #cell-purpose="{ row }">
                        <span v-if="row.purpose" class="text-xs">{{ row.purpose }}</span>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-status="{ row }">
                        <Badge
                            :class="row.is_completed ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
                            class="text-xs"
                        >
                            {{ row.is_completed ? 'Completed' : 'Scheduled' }}
                        </Badge>
                    </template>

                    <!-- Notes Cell -->
                    <template #cell-notes="{ row }">
                        <span v-if="row.notes" class="text-xs text-muted-foreground line-clamp-2 max-w-[200px]">
                            {{ row.notes }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Creator Cell -->
                    <template #cell-creator="{ row }">
                        <div class="flex items-center gap-1.5">
                            <User class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-xs">{{ row.creator?.name || 'Unknown' }}</span>
                        </div>
                    </template>

                    <!-- Completed By Cell -->
                    <template #cell-completed_by="{ row }">
                        <div v-if="row.is_completed && row.completed_by" class="flex items-center gap-1.5">
                            <CheckCircle2 class="h-3 w-3 text-green-600 shrink-0" />
                            <span class="text-xs">{{ row.completed_by?.name || 'Unknown' }}</span>
                            <span v-if="row.completed_at" class="text-[10px] text-muted-foreground">
                                ({{ formatDate(row.completed_at) }})
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-1">
                            <Link :href="route('leads.show', row.lead_id)">
                                <Button variant="ghost" size="icon" class="h-7 w-7">
                                    <Eye class="h-3.5 w-3.5" />
                                </Button>
                            </Link>
                            <Button
                                v-if="!row.is_completed"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="completeOfficeVisit(row.id)"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                                Complete
                            </Button>
                        </div>
                    </template>
                </DataTable>
                <div v-else class="text-center py-12">
                    <Calendar class="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p class="text-sm font-medium mb-1">No office visits found</p>
                    <p class="text-xs text-muted-foreground">Try adjusting your filters</p>
                </div>
            </CardContent>
        </Card>

        <!-- Completion Modal -->
        <Dialog v-model:open="completionModal.open">
            <DialogContent v-if="completionModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm flex items-center gap-2">
                        <CheckCircle2 class="h-4 w-4 text-green-500" />
                        Complete Follow-up
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Mark this follow-up as completed and record the outcome
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3 space-y-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Outcome *</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{ completionForm.outcome ? completionForm.outcome.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Select outcome...' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="completionForm.outcome">
                                    <DropdownMenuRadioItem value="contacted" class="text-xs">Contacted</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="no_answer" class="text-xs">No Answer</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="busy" class="text-xs">Busy</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="not_interested" class="text-xs">Not Interested</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="rescheduled" class="text-xs">Rescheduled</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="converted" class="text-xs">Converted</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="other" class="text-xs">Other</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Duration (minutes)</Label>
                        <Input 
                            v-model="completionForm.duration_minutes"
                            type="number"
                            min="0"
                            max="1440"
                            placeholder="e.g., 15"
                            class="h-8 text-xs"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Outcome Notes</Label>
                        <Textarea 
                            v-model="completionForm.outcome_notes"
                            placeholder="Add notes about the outcome..."
                            class="text-xs min-h-[80px]"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Status</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{ completionForm.status ? completionForm.status.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Completed' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="completionForm.status">
                                    <DropdownMenuRadioItem value="completed" class="text-xs">Completed</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="no_show" class="text-xs">No Show</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <DialogFooter>
                    <Button 
                        variant="outline" 
                        size="sm" 
                        class="h-8 text-xs" 
                        @click="completionModal.open = false"
                        :disabled="completionModal.loading"
                    >
                        Cancel
                    </Button>
                    <Button 
                        size="sm" 
                        class="h-8 text-xs" 
                        @click="completeFollowUp"
                        :disabled="completionModal.loading || !completionForm.outcome"
                    >
                        <Loader2 v-if="completionModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Complete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
