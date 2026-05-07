<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    ArrowLeft,
    GitBranch,
    Loader2,
    CheckCircle2,
    ChevronDown,
} from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    leads: { type: Object, required: true },
    statuses: { type: Array, default: () => [] },
    serviceTypes: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    destinationCountries: { type: Array, default: () => [] },
    leadSortOptions: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission } = usePermissions();

// Filter state
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const serviceType = ref(props.filters.service_type || 'all');
const priority = ref(props.filters.priority || 'all');
const assignedTo = ref(props.filters.assigned_to || 'all');
const destinationCountry = ref(props.filters.destination_country || 'all');
const perPage = ref(props.filters.per_page || '15');

// Bulk assign modal
const bulkAssignModal = ref({ open: false, loading: false, branchId: null });
const selectedLeads = ref(new Set());

// Individual assign
const assignLoading = ref({});

// Toggle select all
const selectAll = ref(false);
const toggleSelectAll = () => {
    selectAll.value = !selectAll.value;
    if (selectAll.value) {
        props.leads.data.forEach(lead => selectedLeads.value.add(lead.id));
    } else {
        selectedLeads.value.clear();
    }
};

// Toggle individual selection
const toggleLeadSelection = (leadId) => {
    if (selectedLeads.value.has(leadId)) {
        selectedLeads.value.delete(leadId);
    } else {
        selectedLeads.value.add(leadId);
    }
    selectAll.value = selectedLeads.value.size === props.leads.data.length;
};

// Watch for select all checkbox
watch(() => props.leads.data, () => {
    if (selectAll.value && props.leads.data.length > 0) {
        props.leads.data.forEach(lead => selectedLeads.value.add(lead.id));
    }
}, { immediate: true });

// Apply filters
const applyFilters = () => {
    router.get(route('leads.no-branch'), {
        search: search.value || undefined,
        status: status.value && status.value !== 'all' ? status.value : undefined,
        service_type: serviceType.value && serviceType.value !== 'all' ? serviceType.value : undefined,
        priority: priority.value && priority.value !== 'all' ? priority.value : undefined,
        assigned_to: assignedTo.value && assignedTo.value !== 'all' ? assignedTo.value : undefined,
        destination_country: destinationCountry.value && destinationCountry.value !== 'all' ? destinationCountry.value : undefined,
        per_page: perPage.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    status.value = 'all';
    serviceType.value = 'all';
    priority.value = 'all';
    assignedTo.value = 'all';
    destinationCountry.value = 'all';
    perPage.value = '15';
    router.get(route('leads.no-branch'), {}, {
        preserveState: false,
    });
};

// Debounced search
const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 500);

watch(search, () => {
    debouncedSearch();
});

// Assign single lead to branch
const assignLeadToBranch = async (leadId, branchId) => {
    if (!branchId || branchId === 'none') return;
    
    if (!confirm('Are you sure you want to assign this lead to the selected branch?')) {
        return;
    }
    
    assignLoading.value[leadId] = true;
    
    try {
        const response = await fetch(route('leads.assign-branch', leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ branch_id: branchId }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            router.reload({ only: ['leads'] });
        } else {
            alert(data.error || 'Failed to assign lead to branch.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while assigning the lead to the branch.');
    } finally {
        assignLoading.value[leadId] = false;
    }
};

// Bulk assign
const openBulkAssignModal = () => {
    if (selectedLeads.value.size === 0) {
        alert('Please select at least one lead to assign.');
        return;
    }
    bulkAssignModal.value.open = true;
};

const submitBulkAssign = async () => {
    if (!bulkAssignModal.value.branchId) {
        alert('Please select a branch.');
        return;
    }
    
    if (!confirm(`Are you sure you want to assign ${selectedLeads.value.size} lead(s) to the selected branch?`)) {
        return;
    }
    
    bulkAssignModal.value.loading = true;
    
    try {
        const response = await fetch(route('leads.bulk-assign-branch'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                lead_ids: Array.from(selectedLeads.value),
                branch_id: bulkAssignModal.value.branchId,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            bulkAssignModal.value.open = false;
            bulkAssignModal.value.branchId = null;
            selectedLeads.value.clear();
            router.reload({ only: ['leads'] });
        } else {
            alert(data.error || 'Failed to assign leads to branch.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while assigning leads to the branch.');
    } finally {
        bulkAssignModal.value.loading = false;
    }
};

// Priority color
const PRIORITY_COLOR = Object.freeze({
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        normal: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    });
const getPriorityColor = (priority) => PRIORITY_COLOR[priority] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';

// Computed properties for display text
const statusDisplay = computed(() => {
    if (status.value === 'all') return 'All Statuses';
    return status.value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
});

const serviceTypeDisplay = computed(() => {
    if (serviceType.value === 'all') return 'All Service Types';
    return serviceType.value.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
});

const priorityDisplay = computed(() => {
    if (priority.value === 'all') return 'All Priorities';
    return priority.value.charAt(0).toUpperCase() + priority.value.slice(1);
});

const assignedToDisplay = computed(() => {
    if (assignedTo.value === 'all') return 'All Users';
    if (assignedTo.value === 'unassigned') return 'Unassigned';
    const user = props.users.find(u => String(u.id) === assignedTo.value);
    return user ? user.name : 'All Users';
});

const destinationCountryDisplay = computed(() => {
    return destinationCountry.value === 'all' ? 'All Countries' : destinationCountry.value;
});

const perPageDisplay = computed(() => {
    return perPage.value === 'all' ? 'All' : perPage.value;
});

const branchDisplay = computed(() => {
    if (!bulkAssignModal.value.branchId) return 'Select Branch...';
    const branch = props.branches.find(b => String(b.id) === bulkAssignModal.value.branchId);
    return branch ? branch.name : 'Select Branch...';
});
</script>

<template>
    <Head title="No Branch Leads" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">No Branch Leads</h1>
                <p class="text-sm text-muted-foreground mt-1">Manage leads that have not been assigned to a branch</p>
            </div>
            <div class="flex items-center gap-2">
                <Link 
                    :href="route('leads.index')" 
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Leads
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle class="text-sm">Filters</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 mb-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Search</Label>
                        <Input
                            v-model="search"
                            placeholder="Name, Email, Phone..."
                            class="h-9 text-xs"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Status</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs">
                                    <span>{{ statusDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="status">
                                    <DropdownMenuRadioItem value="all">All Statuses</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem
                                        v-for="s in statuses"
                                        :key="s"
                                        :value="s"
                                    >
                                        {{ s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Service Type</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs">
                                    <span>{{ serviceTypeDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="serviceType">
                                    <DropdownMenuRadioItem value="all">All Service Types</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem
                                        v-for="type in serviceTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Priority</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs">
                                    <span>{{ priorityDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="priority">
                                    <DropdownMenuRadioItem value="all">All Priorities</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem
                                        v-for="p in priorities"
                                        :key="p"
                                        :value="p"
                                    >
                                        {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Assigned To</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs">
                                    <span>{{ assignedToDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="assignedTo">
                                    <DropdownMenuRadioItem value="all">All Users</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="unassigned">Unassigned</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Destination Country</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs">
                                    <span>{{ destinationCountryDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="destinationCountry">
                                    <DropdownMenuRadioItem value="all">All Countries</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem
                                        v-for="country in destinationCountries"
                                        :key="country"
                                        :value="country"
                                    >
                                        {{ country }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button size="sm" class="h-8 text-xs" @click="applyFilters">
                        Apply Filters
                    </Button>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="clearFilters">
                        Clear Filters
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Leads Table -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle class="text-sm">
                        No Branch Leads
                        <Badge variant="secondary" class="ml-2 text-xs">
                            {{ leads.total }} {{ leads.total === 1 ? 'lead' : 'leads' }}
                        </Badge>
                    </CardTitle>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="leads.data.length > 0"
                            variant="default"
                            size="sm"
                            class="h-8 text-xs"
                            @click="openBulkAssignModal"
                        >
                            <GitBranch class="mr-1.5 h-3.5 w-3.5" />
                            Bulk Assign to Branch
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-24 justify-between text-xs">
                                    <span>{{ perPageDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-24">
                                <DropdownMenuRadioGroup v-model="perPage" @update:model-value="applyFilters">
                                    <DropdownMenuRadioItem value="10">10</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="15">15</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="20">20</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="50">50</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="100">100</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="all">All</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="leads.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="selectAll"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 cursor-pointer"
                                    />
                                </th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Lead</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Service Type</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Priority</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Assigned To</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="lead in leads.data"
                                :key="lead.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-2">
                                    <input
                                        type="checkbox"
                                        :checked="selectedLeads.has(lead.id)"
                                        @change="toggleLeadSelection(lead.id)"
                                        class="h-4 w-4 cursor-pointer"
                                    />
                                </td>
                                <td class="p-2">
                                    <div>
                                        <Link
                                            :href="route('leads.show', lead.id)"
                                            class="font-semibold text-sm hover:text-primary"
                                        >
                                            {{ lead.full_name }}
                                        </Link>
                                        <div class="text-xs text-muted-foreground mt-0.5">
                                            {{ lead.email }} · {{ lead.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="p-2">
                                    <StatusBadge v-if="lead.status" :status="lead.status" />
                                </td>
                                <td class="p-2 text-sm">
                                    {{ lead.service_type ? lead.service_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A' }}
                                </td>
                                <td class="p-2">
                                    <Badge
                                        v-if="lead.priority"
                                        :class="getPriorityColor(lead.priority)"
                                        class="text-xs"
                                    >
                                        {{ lead.priority.charAt(0).toUpperCase() + lead.priority.slice(1) }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="p-2 text-sm">
                                    {{ lead.assigned_user?.name || 'Unassigned' }}
                                </td>
                                <td class="p-2">
                                    <div class="flex items-center gap-2">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="outline"
                                                    class="h-8 w-40 justify-between text-xs"
                                                    :disabled="assignLoading[lead.id]"
                                                >
                                                    <span>Select Branch...</span>
                                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="start" class="w-40">
                                                <DropdownMenuRadioGroup
                                                    @update:model-value="(value) => assignLeadToBranch(lead.id, value)"
                                                >
                                                    <DropdownMenuRadioItem
                                                        v-for="branch in branches"
                                                        :key="branch.id"
                                                        :value="String(branch.id)"
                                                    >
                                                        {{ branch.name }}
                                                    </DropdownMenuRadioItem>
                                                </DropdownMenuRadioGroup>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                        <Loader2
                                            v-if="assignLoading[lead.id]"
                                            class="h-4 w-4 animate-spin"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <GitBranch class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p class="text-muted-foreground">No leads without branches found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="leads.links && leads.links.length > 3" class="mt-4 flex items-center justify-center">
                    <div class="flex gap-1">
                        <Link
                            v-for="link in leads.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 text-xs rounded-md',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted hover:bg-muted/80',
                                !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                            ]"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Bulk Assign Modal -->
        <Dialog v-model:open="bulkAssignModal.open">
            <DialogContent v-if="bulkAssignModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm">Bulk Assign to Branch</DialogTitle>
                    <DialogDescription class="text-xs">
                        Select a branch to assign all selected leads to:
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Branch</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    class="w-full justify-between h-9 text-xs"
                                    :class="{ 'border-destructive': !bulkAssignModal.branchId }"
                                >
                                    <span>{{ branchDisplay }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup 
                                    :model-value="bulkAssignModal.branchId ? String(bulkAssignModal.branchId) : null"
                                    @update:model-value="(value) => bulkAssignModal.branchId = value"
                                >
                                    <DropdownMenuRadioItem
                                        v-for="branch in branches"
                                        :key="branch.id"
                                        :value="String(branch.id)"
                                    >
                                        {{ branch.name }}
                                    </DropdownMenuRadioItem>
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
                        @click="bulkAssignModal.open = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        size="sm"
                        class="h-8 text-xs"
                        @click="submitBulkAssign"
                        :disabled="bulkAssignModal.loading || !bulkAssignModal.branchId"
                    >
                        <Loader2
                            v-if="bulkAssignModal.loading"
                            class="mr-1.5 h-3.5 w-3.5 animate-spin"
                        />
                        Assign to Branch
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
