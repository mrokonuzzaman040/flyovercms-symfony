<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    FileText,
    Eye,
    Download,
    FileDown,
    Search,
    RefreshCw,
    CheckCircle,
    Clock,
    XCircle,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    leads: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusFilter = ref(props.filters?.status || '');
const searchQuery = ref(props.filters?.search || '');

const columns = [
    { key: 'full_name', label: 'Lead', sortable: true },
    { key: 'contact', label: 'Contact', sortable: false },
    { key: 'department_status', label: 'Status', sortable: false },
    { key: 'department_manager', label: 'Manager', sortable: false },
    { key: 'transferred_at', label: 'Transferred', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' },
];

const departmentStatusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'application_submitted', label: 'Application Submitted' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'completed', label: 'Completed' },
];

const getStatusBadge = (status) => {
    const statusConfig = {
        pending: { variant: 'secondary', label: 'Pending' },
        in_progress: { variant: 'default', label: 'In Progress' },
        application_submitted: { variant: 'default', label: 'Application Submitted' },
        under_review: { variant: 'default', label: 'Under Review' },
        approved: { variant: 'success', label: 'Approved' },
        rejected: { variant: 'destructive', label: 'Rejected' },
        completed: { variant: 'success', label: 'Completed' },
    };
    return statusConfig[status] || { variant: 'secondary', label: status };
};

const visaLeadsRoute = () => route('departments.visa.leads');

const handleSearch = (query) => {
    router.get(visaLeadsRoute(), { search: query, status: statusFilter.value, page: 1 }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (value) => {
    statusFilter.value = value;
    router.get(visaLeadsRoute(), { search: searchQuery.value, status: value, page: 1 }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(visaLeadsRoute(), { search: searchQuery.value, status: statusFilter.value, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(visaLeadsRoute(), { search: searchQuery.value, status: statusFilter.value, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const exportPdf = (lead) => {
    window.open(route('departments.visa.export-pdf', lead.id), '_blank');
};

const downloadDocuments = (lead) => {
    window.location.href = route('departments.visa.download-documents', lead.id);
};
</script>

<template>
    <Head title="Visa Department - Leads" />
    <div class="container mx-auto py-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-primary px-4 py-2.5 text-primary-foreground shadow-md">
            <div class="relative z-10 flex items-center justify-between gap-2 flex-wrap">
                <div>
                    <h1 class="text-base font-semibold tracking-tight">Visa Department · Leads</h1>
                    <p class="mt-0.5 text-[11px] text-primary-foreground/90">View and filter visa department leads</p>
                </div>
                <nav class="flex flex-wrap gap-1">
                    <Link :href="route('departments.visa.index')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Dashboard</Link>
                    <span class="text-primary-foreground/50">·</span>
                    <span class="text-[11px] text-primary-foreground px-2 py-1">Leads</span>
                    <Link :href="route('departments.visa.documents')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Documents</Link>
                    <Link :href="route('departments.visa.analytics')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Analytics</Link>
                    <Link :href="route('departments.visa.exports')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Exports</Link>
                </nav>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-linear-to-l from-primary-foreground/10 to-transparent" aria-hidden="true" />
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
            <Link :href="route('departments.visa.leads')">
                <Card class="border-l-4 border-l-primary p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">Total</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.total }}</p>
                        </div>
                        <FileText class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
            <Link :href="route('departments.visa.leads', { status: 'pending' })">
                <Card class="border-l-4 border-l-muted-foreground/50 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">Pending</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.pending }}</p>
                        </div>
                        <Clock class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
            <Link :href="route('departments.visa.leads', { status: 'in_progress' })">
                <Card class="border-l-4 border-l-primary/70 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">In Progress</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.in_progress }}</p>
                        </div>
                        <RefreshCw class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
            <Link :href="route('departments.visa.leads', { status: 'approved' })">
                <Card class="border-l-4 border-l-primary/70 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">Approved</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.approved ?? 0 }}</p>
                        </div>
                        <CheckCircle class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
            <Link :href="route('departments.visa.leads', { status: 'rejected' })">
                <Card class="border-l-4 border-l-destructive p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">Rejected</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.rejected ?? 0 }}</p>
                        </div>
                        <XCircle class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
            <Link :href="route('departments.visa.leads', { status: 'completed' })">
                <Card class="border-l-4 border-l-primary p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-medium text-muted-foreground">Completed</p>
                            <p class="text-sm font-semibold leading-tight">{{ stats.completed }}</p>
                        </div>
                        <CheckCircle class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-[180px] flex-1 max-w-xs">
                <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name, email, phone..."
                    class="pl-8 h-8 text-xs"
                    @input="(e) => handleSearch(e.target.value)"
                />
            </div>
            <Select :model-value="statusFilter" @update:model-value="handleStatusFilter">
                <SelectTrigger class="w-[180px] h-8 text-xs">
                    <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="option in departmentStatusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <DataTable
            :columns="columns"
            :data="leads.data"
            :pagination="leads"
            :search-query="searchQuery"
            search-placeholder="Search leads..."
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #cell-full_name="{ row }">
                <div class="flex flex-col gap-0.5">
                    <Link :href="route('departments.visa.show', row.id)" class="font-medium text-xs hover:underline">
                        {{ row.full_name }}
                    </Link>
                    <span class="text-[11px] text-muted-foreground">{{ row.email }}</span>
                </div>
            </template>
            <template #cell-contact="{ row }">
                <span class="text-xs">{{ row.phone || '—' }}</span>
            </template>
            <template #cell-department_status="{ row }">
                <Badge
                    v-if="row.accepted_department_transfer?.department_status"
                    :variant="getStatusBadge(row.accepted_department_transfer.department_status).variant"
                    class="text-[10px] px-1.5 py-0"
                >
                    {{ getStatusBadge(row.accepted_department_transfer.department_status).label }}
                </Badge>
                <Badge v-else variant="secondary" class="text-[10px] px-1.5 py-0">Pending</Badge>
            </template>
            <template #cell-department_manager="{ row }">
                <span class="text-xs">{{ row.department_manager?.name ?? 'Unassigned' }}</span>
            </template>
            <template #cell-transferred_at="{ row }">
                <span class="text-xs text-muted-foreground">
                    {{ row.transferred_to_department_at ? new Date(row.transferred_to_department_at).toLocaleDateString() : '—' }}
                </span>
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center gap-1">
                    <Button as-child variant="ghost" size="icon" class="h-7 w-7">
                        <Link :href="route('departments.visa.show', row.id)">
                            <Eye class="h-3.5 w-3.5" />
                        </Link>
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7" title="Export PDF" @click="exportPdf(row)">
                        <FileDown class="h-3.5 w-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7" title="Download Documents" @click="downloadDocuments(row)">
                        <Download class="h-3.5 w-3.5" />
                    </Button>
                </div>
            </template>
        </DataTable>
    </div>
</template>
