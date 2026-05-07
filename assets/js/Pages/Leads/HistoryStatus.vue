<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    ArrowLeft,
    History,
    Filter,
    MoreHorizontal,
    Eye,
    Pencil,
    List,
} from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    leads: { type: Object, required: true },
    status: { type: String, required: true },
    currentStatusConfig: { type: Object, required: true },
    today: { type: String, required: true },
    isToday: { type: Boolean, default: false },
    serviceTypes: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    destinationCountries: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission, hasRole } = usePermissions();

// Filter state
const search = ref(props.filters.search || '');
const serviceType = ref(props.filters.service_type || '');
const branchId = ref(props.filters.branch_id || '');
const assignedTo = ref(props.filters.assigned_to || '');
const priority = ref(props.filters.priority || '');
const destinationCountry = ref(props.filters.destination_country || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const perPage = ref(props.filters.per_page || '15');

// Apply filters
const applyFilters = () => {
    router.get(route('leads.status.history', props.status), {
        search: search.value || undefined,
        service_type: serviceType.value || undefined,
        branch_id: branchId.value || undefined,
        assigned_to: assignedTo.value || undefined,
        priority: priority.value || undefined,
        destination_country: destinationCountry.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        per_page: perPage.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    serviceType.value = '';
    branchId.value = '';
    assignedTo.value = '';
    priority.value = '';
    destinationCountry.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    perPage.value = '15';
    router.get(route('leads.status.history', props.status), {}, {
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

// Status color
const statusColor = computed(() => props.currentStatusConfig?.color || '#6b7280');

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// History description
const historyDescription = computed(() => {
    if (props.isToday) {
        return `Completed today (${formatDate(props.today)})`;
    }
    if (dateFrom.value && dateTo.value && dateFrom.value !== dateTo.value) {
        return `Completed ${formatDate(dateFrom.value)} - ${formatDate(dateTo.value)}`;
    }
    if (dateFrom.value) {
        return `Completed ${formatDate(dateFrom.value)}`;
    }
    return 'Completed';
});
</script>

<template>
    <Head :title="`${currentStatusConfig?.title} - History`" />
    
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl text-white"
                    :style="{ background: `linear-gradient(135deg, ${statusColor} 0%, ${statusColor}dd 100%)` }"
                >
                    <History class="h-6 w-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ currentStatusConfig?.title }} - History</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ historyDescription }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link 
                    :href="route('leads.status', status)" 
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to {{ currentStatusConfig?.title }}
                </Link>
                <Link 
                    :href="route('leads.index')" 
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4"
                >
                    <List class="h-4 w-4" />
                    All Leads
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <div
                        class="h-4 w-0.5 rounded"
                        :style="{ backgroundColor: statusColor }"
                    ></div>
                    Search & Filters
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Search</Label>
                        <Input
                            v-model="search"
                            placeholder="Name, email, phone, passport..."
                            class="h-9 text-xs"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Service Type</Label>
                        <Select v-model="serviceType" @update:model-value="applyFilters">
                            <SelectTrigger class="h-9 text-xs">
                                <SelectValue placeholder="All Services" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Services</SelectItem>
                                <SelectItem
                                    v-for="type in serviceTypes"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="hasRole('admin') && branches.length > 0" class="space-y-1.5">
                        <Label class="text-xs">Branch</Label>
                        <Select v-model="branchId" @update:model-value="applyFilters">
                            <SelectTrigger class="h-9 text-xs">
                                <SelectValue placeholder="All Branches" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Branches</SelectItem>
                                <SelectItem
                                    v-for="branch in branches"
                                    :key="branch.id"
                                    :value="String(branch.id)"
                                >
                                    {{ branch.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="hasRole('admin') && users.length > 0" class="space-y-1.5">
                        <Label class="text-xs">Assigned To</Label>
                        <Select v-model="assignedTo" @update:model-value="applyFilters">
                            <SelectTrigger class="h-9 text-xs">
                                <SelectValue placeholder="All Users" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Users</SelectItem>
                                <SelectItem value="unassigned">Unassigned</SelectItem>
                                <SelectItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Priority</Label>
                        <Select v-model="priority" @update:model-value="applyFilters">
                            <SelectTrigger class="h-9 text-xs">
                                <SelectValue placeholder="All Priorities" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Priorities</SelectItem>
                                <SelectItem
                                    v-for="p in priorities"
                                    :key="p"
                                    :value="p"
                                >
                                    {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="destinationCountries.length > 0" class="space-y-1.5">
                        <Label class="text-xs">Destination</Label>
                        <Select v-model="destinationCountry" @update:model-value="applyFilters">
                            <SelectTrigger class="h-9 text-xs">
                                <SelectValue placeholder="All Countries" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Countries</SelectItem>
                                <SelectItem
                                    v-for="country in destinationCountries"
                                    :key="country"
                                    :value="country"
                                >
                                    {{ country }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Date From</Label>
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="h-9 text-xs"
                            @change="applyFilters"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Date To</Label>
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="h-9 text-xs"
                            @change="applyFilters"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        size="sm"
                        class="h-8 text-xs"
                        :style="{ backgroundColor: statusColor }"
                        @click="applyFilters"
                    >
                        <Filter class="mr-1.5 h-3.5 w-3.5" />
                        Apply Filters
                    </Button>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="clearFilters">
                        Clear
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Leads Table -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div
                            class="h-4 w-0.5 rounded"
                            :style="{ backgroundColor: statusColor }"
                        ></div>
                        {{ currentStatusConfig?.title }} - History
                        <Badge variant="secondary" class="ml-2 text-xs">
                            {{ leads.total }} {{ leads.total === 1 ? 'result' : 'results' }}
                        </Badge>
                    </CardTitle>
                    <Select v-model="perPage" @update:model-value="applyFilters">
                        <SelectTrigger class="h-8 w-24 text-xs">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="15">15</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                            <SelectItem value="100">100</SelectItem>
                            <SelectItem value="all">All</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="leads.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2 text-left text-xs font-semibold uppercase">Lead</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Contact</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Service</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Priority</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Assigned</th>
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
                                    <Link
                                        :href="route('leads.show', lead.id)"
                                        class="font-semibold text-sm hover:text-primary"
                                    >
                                        {{ lead.full_name }}
                                    </Link>
                                </td>
                                <td class="p-2 text-xs text-muted-foreground">
                                    <div>{{ lead.email || '-' }}</div>
                                    <div>{{ lead.phone || '-' }}</div>
                                </td>
                                <td class="p-2 text-xs">
                                    {{ lead.service_type ? lead.service_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A' }}
                                </td>
                                <td class="p-2">
                                    <Badge
                                        v-if="lead.priority"
                                        :class="{
                                            'bg-red-100 text-red-800': lead.priority === 'urgent',
                                            'bg-orange-100 text-orange-800': lead.priority === 'high',
                                            'bg-blue-100 text-blue-800': lead.priority === 'normal',
                                            'bg-gray-100 text-gray-800': lead.priority === 'low',
                                        }"
                                        class="text-xs"
                                    >
                                        {{ lead.priority.charAt(0).toUpperCase() + lead.priority.slice(1) }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="p-2 text-xs">
                                    {{ lead.assigned_user?.name || 'Unassigned' }}
                                </td>
                                <td class="p-2">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-7 w-7">
                                                <MoreHorizontal class="h-3.5 w-3.5" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="route('leads.show', lead.id)" class="flex items-center text-xs">
                                                    <Eye class="mr-2 h-3.5 w-3.5" /> View Details
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="hasPermission('edit-leads')" as-child>
                                                <Link :href="route('leads.edit', lead.id)" class="flex items-center text-xs">
                                                    <Pencil class="mr-2 h-3.5 w-3.5" /> Edit
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-muted-foreground">No completed leads found.</p>
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
    </div>
</template>
