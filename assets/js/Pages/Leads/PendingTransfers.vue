<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
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
    CheckCircle,
    XCircle,
    Search,
    Loader2,
    ArrowRightLeft,
    Eye,
    Clock,
    User,
    Mail,
    Phone,
    Building,
    MoreHorizontal,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    transfers: { type: Object, required: true },
    myTransferredLeads: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.filters.search || '');
const rejectModal = ref({ open: false, transfer: null, loading: false });
const rejectForm = useForm({
    rejection_reason: '',
});

const columns = [
    { key: 'lead_name', label: 'Lead', sortable: true },
    { key: 'contact', label: 'Contact', sortable: false },
    { key: 'service_type', label: 'Service', sortable: false },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'transferred_by', label: 'Transferred By', sortable: false },
    { key: 'branch', label: 'Branch', sortable: false },
    { key: 'created_at', label: 'Requested', sortable: true },
    { key: 'actions', label: '', width: '180px' },
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handleSearch = (query) => {
    router.get(route('transfers.pending'), {
        search: query || undefined,
        page: 1,
    }, { preserveState: true, preserveScroll: true });
};

const handleSort = ({ field, direction }) => {
    router.get(route('transfers.pending'), {
        ...props.filters,
        sort: field,
        direction,
        page: 1,
    }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('transfers.pending'), {
        ...props.filters,
        page,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(route('transfers.pending'), {
        ...props.filters,
        per_page: perPage,
        page: 1,
    }, { preserveState: true, preserveScroll: true });
};

const handleAccept = async (transfer) => {
    if (!confirm('Are you sure you want to accept this lead transfer?')) {
        return;
    }

    try {
        const response = await fetch(route('transfers.accept', transfer.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Inertia': 'true',
            },
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.success(data.message || 'Lead transfer accepted successfully');
            // Reload the page to update the list
            router.reload({ only: ['transfers'], preserveScroll: false });
        } else {
            toast.error(data.error || data.message || 'Failed to accept transfer');
        }
    } catch (error) {
        console.error('Failed to accept transfer:', error);
        toast.error('An error occurred while accepting the transfer');
    }
};

const openRejectModal = (transfer) => {
    rejectModal.value = { open: true, transfer, loading: false };
    rejectForm.reset();
};

const closeRejectModal = () => {
    rejectModal.value = { open: false, transfer: null, loading: false };
    rejectForm.reset();
};

const handleReject = async () => {
    if (!rejectModal.value.transfer) return;

    rejectForm.processing = true;

    try {
        const response = await fetch(route('transfers.reject', rejectModal.value.transfer.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Inertia': 'true',
            },
            body: JSON.stringify({
                rejection_reason: rejectForm.rejection_reason || null,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.success(data.message || 'Lead transfer rejected successfully');
            closeRejectModal();
            // Reload the page to update the list
            router.reload({ only: ['transfers'], preserveScroll: false });
        } else {
            const errorMessage = data.error || data.message || (response.status === 422 ? 'Validation failed' : 'Failed to reject transfer');
            toast.error(errorMessage);
        }
    } catch (error) {
        console.error('Failed to reject transfer:', error);
        toast.error('An error occurred while rejecting the transfer');
    } finally {
        rejectForm.processing = false;
    }
};

const handleAcceptAll = async () => {
    if (!confirm('Are you sure you want to accept all pending transfers?')) {
        return;
    }

    try {
        const response = await fetch(route('transfers.accept-all'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Inertia': 'true',
            },
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.success(data.message || 'All transfers accepted successfully');
            // Reload the page to update the list
            router.reload({ only: ['transfers'], preserveScroll: false });
        } else {
            toast.error(data.error || data.message || 'Failed to accept transfers');
        }
    } catch (error) {
        console.error('Failed to accept all transfers:', error);
        toast.error('An error occurred while accepting transfers');
    }
};

const handleCancelTransfer = async (transfer) => {
    if (!confirm('Are you sure you want to cancel this transfer request?')) {
        return;
    }

    try {
        const response = await fetch(route('transfers.cancel', transfer.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Inertia': 'true',
            },
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.success(data.message || 'Transfer cancelled successfully');
            router.reload({ only: ['transfers', 'myTransferredLeads'], preserveScroll: false });
        } else {
            toast.error(data.error || data.message || 'Failed to cancel transfer');
        }
    } catch (error) {
        console.error('Failed to cancel transfer:', error);
        toast.error('An error occurred while cancelling the transfer');
    }
};

const handleRejectAll = async () => {
    if (!confirm('Are you sure you want to reject all pending transfers? This action cannot be undone.')) {
        return;
    }

    const reason = prompt('Please provide a reason for rejecting all transfers (optional):', '');

    try {
        const response = await fetch(route('transfers.reject-all'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Inertia': 'true',
            },
            body: JSON.stringify({
                rejection_reason: reason || null,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.success(data.message || 'All transfers rejected successfully');
            // Reload the page to update the list
            router.reload({ only: ['transfers'], preserveScroll: false });
        } else {
            toast.error(data.error || data.message || 'Failed to reject transfers');
        }
    } catch (error) {
        console.error('Failed to reject all transfers:', error);
        toast.error('An error occurred while rejecting transfers');
    }
};

const STATUS_COLOR = Object.freeze({
        new: '#3b82f6',
        contacted: '#8b5cf6',
        busy: '#f97316',
        unavailable: '#6b7280',
        no_answer: '#0ea5e9',
        qualified: '#f59e0b',
        not_qualified: '#f97316',
        follow_up: '#10b981',
        office_visited: '#06b6d4',
        documentation_pending: '#f59e0b',
        application_submitted: '#3b82f6',
        under_review: '#8b5cf6',
        interview_scheduled: '#10b981',
        approved: '#10b981',

        in_process: '#3b82f6',
        on_hold: '#6b7280',
        converted: '#10b981',
        flight_done: '#10b981',
        rejected: '#ef4444',
        cancelled: '#6b7280',
        lost: '#ef4444',
    });
const getStatusColor = (status) => STATUS_COLOR[status] || '#6b7280';

// Transform transfers data for table
const tableData = computed(() => {
    return props.transfers.data.map(transfer => ({
        id: transfer.id,
        lead_id: transfer.lead?.id,
        lead_name: transfer.lead?.full_name,
        lead: transfer.lead,
        contact: {
            email: transfer.lead?.email,
            phone: transfer.lead?.phone,
        },
        service_type: transfer.lead?.service_type,
        selected_service: transfer.lead?.selected_service,
        selected_package: transfer.lead?.selected_package,
        services: transfer.lead?.services ?? [],
        packages: transfer.lead?.packages ?? [],
        status: transfer.lead?.status,
        transferred_by: transfer.transferred_by,
        branch: transfer.lead?.branch,
        created_at: transfer.created_at,
        note: transfer.note,
        transfer: transfer,
    }));
});
</script>

<template>

    <Head title="Pending Lead Transfers" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pending Lead Transfers</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Review and accept or reject lead transfer requests
                </p>
            </div>
            <div class="flex items-center gap-2" v-if="transfers.data.length > 0">
                <Button variant="outline" size="sm" @click="handleAcceptAll" class="gap-2">
                    <CheckCircle class="h-4 w-4" />
                    Accept All
                </Button>
                <Button variant="outline" size="sm" @click="handleRejectAll" class="gap-2">
                    <XCircle class="h-4 w-4" />
                    Reject All
                </Button>
            </div>
        </div>

        <!-- Leads I've Transferred -->
        <Card v-if="myTransferredLeads?.length > 0">
            <CardHeader>
                <CardTitle class="text-lg flex items-center gap-2">
                    <ArrowRightLeft class="h-5 w-5" />
                    Leads I've Transferred
                </CardTitle>
                <p class="text-sm text-muted-foreground mt-1">
                    Pending transfer requests you initiated — awaiting recipient response
                </p>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="text-left font-medium px-4 py-3">Lead</th>
                                <th class="text-left font-medium px-4 py-3">Transferred To</th>
                                <th class="text-left font-medium px-4 py-3">Branch</th>
                                <th class="text-left font-medium px-4 py-3">Requested</th>
                                <th class="text-right font-medium px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in myTransferredLeads" :key="t.id"
                                class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    <Link :href="route('leads.show', t.lead?.id)"
                                        class="font-medium hover:underline text-primary">
                                        {{ t.lead?.full_name || 'Unknown' }}
                                    </Link>
                                    <p v-if="t.note" class="text-xs text-muted-foreground truncate max-w-[200px]">
                                        {{ t.note }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-muted-foreground">{{ t.transferred_to?.name || '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-muted-foreground">{{ t.lead?.branch?.name || '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-muted-foreground">{{ formatDateTime(t.created_at) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('leads.show', t.lead?.id)"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md border hover:bg-muted">
                                            <Eye class="h-3 w-3" />
                                            View
                                        </Link>
                                        <Button variant="outline" size="sm" class="gap-1 h-7 text-xs"
                                            @click="handleCancelTransfer(t)">
                                            <XCircle class="h-3 w-3" />
                                            Cancel
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Table -->
        <Card>
            <CardContent class="p-0">
                <DataTable :columns="columns" :data="tableData" :pagination="transfers"
                    :sort-field="typeof filters?.sort === 'string' ? filters.sort : 'created_at'"
                    :sort-direction="typeof filters?.direction === 'string' ? filters.direction : 'desc'"
                    :search-query="filters?.search || ''" search-placeholder="Search anything (name, email, phone, passport, notes...)"
                    @sort="handleSort" @search="handleSearch" @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange">
                    <!-- Lead Name Cell -->
                    <template #cell-lead_name="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                                <span class="text-xs font-medium text-primary">
                                    {{ row.lead_name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <Link :href="route('leads.show', row.lead_id)"
                                    class="text-xs font-medium hover:underline truncate block">
                                    {{ row.lead_name || 'Unnamed Lead' }}
                                </Link>
                                <p v-if="row.note" class="text-[10px] text-muted-foreground truncate max-w-[200px]"
                                    :title="row.note">
                                    <span class="font-semibold">Note:</span> {{ row.note }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Contact Cell -->
                    <template #cell-contact="{ row }">
                        <div class="space-y-0.5">
                            <div v-if="row.contact?.phone" class="flex items-center gap-1 text-xs">
                                <Phone class="h-3 w-3 text-muted-foreground shrink-0" />
                                <span class="truncate">{{ row.contact.phone }}</span>
                            </div>
                            <div v-if="row.contact?.email"
                                class="flex items-center gap-1 text-[10px] text-muted-foreground">
                                <Mail class="h-3 w-3 shrink-0" />
                                <span class="truncate max-w-[120px]">{{ row.contact.email }}</span>
                            </div>
                        </div>
                    </template>

                    <!-- Service Type Cell (service type + selected service + packages from lead) -->
                    <template #cell-service_type="{ row }">
                        <div class="space-y-0.5 min-w-0">
                            <span v-if="row.service_type" class="text-xs capitalize block">{{ row.service_type?.replace(/_/g, ' ') }}</span>
                            <template v-if="row.selected_service || row.selected_package || (row.services?.length) || (row.packages?.length)">
                                <span v-if="row.selected_service?.name" class="text-[10px] text-muted-foreground block truncate" :title="row.selected_service.name">Service: {{ row.selected_service.name }}</span>
                                <span v-if="row.selected_package?.name" class="text-[10px] text-muted-foreground block truncate" :title="row.selected_package.name">Package: {{ row.selected_package.name }}</span>
                                <template v-if="(row.services?.length) && !row.selected_service?.name">
                                    <span class="text-[10px] text-muted-foreground block truncate">Services: {{ row.services.map(s => s.name).join(', ') }}</span>
                                </template>
                                <template v-if="(row.packages?.length) && !row.selected_package?.name">
                                    <span class="text-[10px] text-muted-foreground block truncate">Packages: {{ row.packages.map(p => p.name).join(', ') }}</span>
                                </template>
                            </template>
                            <span v-if="!row.service_type && !row.selected_service?.name && !row.selected_package?.name && !(row.services?.length) && !(row.packages?.length)" class="text-xs text-muted-foreground">-</span>
                        </div>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-status="{ row }">
                        <Badge v-if="row.status"
                            :style="{ backgroundColor: getStatusColor(row.status), color: 'white' }"
                            class="text-[10px] font-medium capitalize">
                            {{ row.status?.replace(/_/g, ' ') }}
                        </Badge>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Transferred By Cell -->
                    <template #cell-transferred_by="{ row }">
                        <div class="flex items-center gap-2">
                            <User class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-xs">{{ row.transfer?.transferred_by?.name || 'N/A' }}</span>
                        </div>
                    </template>

                    <!-- Branch Cell -->
                    <template #cell-branch="{ row }">
                        <div v-if="row.branch" class="flex items-center gap-2">
                            <Building class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-xs">{{ row.branch.name }}</span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>

                    <!-- Created Date Cell -->
                    <template #cell-created_at="{ row }">
                        <div class="flex items-center gap-1">
                            <Clock class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="text-[10px] text-muted-foreground">{{ formatDateTime(row.created_at) }}</span>
                        </div>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ row }">
                        <div class="flex items-center gap-1">
                            <Link :href="route('leads.show', row.lead_id)"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                <Eye class="h-3 w-3" />
                                View
                            </Link>
                            <Button @click="handleAccept(row.transfer)" variant="default" size="sm"
                                class="gap-1 h-7 text-xs bg-green-600 hover:bg-green-700">
                                <CheckCircle class="h-3 w-3" />
                                Accept
                            </Button>
                            <Button @click="openRejectModal(row.transfer)" variant="destructive" size="sm"
                                class="gap-1 h-7 text-xs">
                                <XCircle class="h-3 w-3" />
                                Reject
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </CardContent>
        </Card>

        <!-- Empty State -->
        <Card v-if="transfers.data.length === 0">
            <CardContent class="py-12 text-center">
                <ArrowRightLeft class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    No Pending Transfers
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    You don't have any pending lead transfer requests at the moment.
                </p>
            </CardContent>
        </Card>

        <!-- Reject Modal -->
        <Dialog v-model:open="rejectModal.open">
            <DialogContent v-if="rejectModal.open">
                <DialogHeader>
                    <DialogTitle>Reject Lead Transfer</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to reject this lead transfer? You can optionally provide a reason.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                            Reason (Optional)
                        </label>
                        <Textarea v-model="rejectForm.rejection_reason" placeholder="Enter rejection reason..."
                            rows="3" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="closeRejectModal" :disabled="rejectForm.processing">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="handleReject" :disabled="rejectForm.processing">
                        <Loader2 v-if="rejectForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                        Reject Transfer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
