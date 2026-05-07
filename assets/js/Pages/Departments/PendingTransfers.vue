<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Textarea } from '@/Components/ui/textarea';
import { Label } from '@/Components/ui/label';
import { CheckCircle, XCircle, Eye, Loader2, Search } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    transfers: { type: Object, required: true },
    department: { type: String, required: true },
    filters: { type: Object, default: () => ({}) },
});

const acceptModal = ref({ open: false, transfer: null, loading: false });
const rejectModal = ref({ open: false, transfer: null, loading: false, reason: '' });
const searchQuery = ref(props.filters?.search || '');

const columns = [
    { key: 'lead_name', label: 'Lead', sortable: true },
    { key: 'transferred_by', label: 'Transferred By', sortable: false },
    { key: 'assignee', label: 'Assignee', sortable: false },
    { key: 'note', label: 'Note', sortable: false },
    { key: 'created_at', label: 'Requested', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '200px' },
];

const handleSearch = (query) => {
    router.get(route('departments.transfers.pending', props.department), {
        ...props.filters,
        search: query,
        page: 1,
    }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('departments.transfers.pending', props.department), {
        ...props.filters,
        search: searchQuery.value,
        page,
    }, { preserveState: true, preserveScroll: true });
};

const openAcceptModal = (transfer) => {
    acceptModal.value = { open: true, transfer, loading: false };
};

const acceptTransfer = () => {
    acceptModal.value.loading = true;
    router.post(route('departments.transfers.accept', acceptModal.value.transfer.id), {
        preserveScroll: true,
        onSuccess: () => {
            acceptModal.value.open = false;
            acceptModal.value.loading = false;
        },
        onError: () => {
            acceptModal.value.loading = false;
        },
    });
};

const openRejectModal = (transfer) => {
    rejectModal.value = { open: true, transfer, loading: false, reason: '' };
};

const rejectTransfer = () => {
    rejectModal.value.loading = true;
    router.post(route('departments.transfers.reject', rejectModal.value.transfer.id), {
        rejection_reason: rejectModal.value.reason,
        preserveScroll: true,
        onSuccess: () => {
            rejectModal.value.open = false;
            rejectModal.value.loading = false;
            rejectModal.value.reason = '';
        },
        onError: () => {
            rejectModal.value.loading = false;
        },
    });
};
</script>

<template>
    <Head :title="`${department.charAt(0).toUpperCase() + department.slice(1)} Department - Pending Transfers`" />

    <div class="container mx-auto py-6 space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                {{ department.charAt(0).toUpperCase() + department.slice(1) }} Department
            </h1>
            <p class="text-muted-foreground mt-1">
                Pending transfer requests
            </p>
        </div>

        <!-- Search -->
        <Card>
            <CardContent class="pt-6">
                <div class="relative">
                    <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        @input="handleSearch($event.target.value)"
                        type="text"
                        placeholder="Search by lead name, email, phone..."
                        class="w-full pl-8 pr-4 py-2 border rounded-md"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Data Table -->
        <DataTable
            :columns="columns"
            :data="transfers.data"
            :pagination="transfers"
            :search-query="searchQuery"
            search-placeholder="Search transfers..."
            @search="handleSearch"
            @page-change="handlePageChange"
        >
            <!-- Lead Name Cell -->
            <template #cell-lead_name="{ row }">
                <div class="flex flex-col">
                    <Link
                        :href="route('leads.show', row.lead.id)"
                        class="font-medium hover:underline"
                    >
                        {{ row.lead.full_name }}
                    </Link>
                    <span class="text-xs text-muted-foreground">{{ row.lead.email }}</span>
                </div>
            </template>

            <!-- Transferred By Cell -->
            <template #cell-transferred_by="{ row }">
                <span class="text-sm">{{ row.transferred_by?.name || '-' }}</span>
            </template>

            <!-- Assignee Cell (broadcast vs specific) -->
            <template #cell-assignee="{ row }">
                <Badge v-if="row.transferred_to == null" variant="secondary" class="text-xs">
                    Broadcast — first to accept
                </Badge>
                <span v-else class="text-sm">{{ row.transferred_to?.name || '-' }}</span>
            </template>

            <!-- Note Cell -->
            <template #cell-note="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.note || '-' }}</span>
            </template>

            <!-- Created At Cell -->
            <template #cell-created_at="{ row }">
                <span class="text-sm text-muted-foreground">
                    {{ new Date(row.created_at).toLocaleDateString() }}
                </span>
            </template>

            <!-- Actions Cell -->
            <template #cell-actions="{ row }">
                <div class="flex items-center gap-2">
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                    >
                        <Link :href="route('leads.show', row.lead.id)">
                            <Eye class="h-4 w-4" />
                        </Link>
                    </Button>
                    <Button
                        variant="default"
                        size="sm"
                        @click="openAcceptModal(row)"
                    >
                        <CheckCircle class="h-4 w-4 mr-1" />
                        Accept
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="openRejectModal(row)"
                    >
                        <XCircle class="h-4 w-4 mr-1" />
                        Reject
                    </Button>
                </div>
            </template>
        </DataTable>

        <!-- Accept Modal -->
        <Dialog v-model:open="acceptModal.open">
            <DialogContent v-if="acceptModal.open">
                <DialogHeader>
                    <DialogTitle>Accept Transfer</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to accept this transfer? You will become the manager of this lead.
                        {{ acceptModal.transfer?.transferred_to == null ? ' Only one person can accept; first to accept wins.' : '' }}
                    </DialogDescription>
                </DialogHeader>
                <div v-if="acceptModal.transfer" class="space-y-2">
                    <div>
                        <Label>Lead</Label>
                        <p class="font-medium">{{ acceptModal.transfer.lead.full_name }}</p>
                    </div>
                    <div v-if="acceptModal.transfer.note">
                        <Label>Note</Label>
                        <p class="text-sm text-muted-foreground">{{ acceptModal.transfer.note }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="acceptModal.open = false">Cancel</Button>
                    <Button @click="acceptTransfer" :disabled="acceptModal.loading">
                        <Loader2 v-if="acceptModal.loading" class="mr-2 h-4 w-4 animate-spin" />
                        Accept Transfer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reject Modal -->
        <Dialog v-model:open="rejectModal.open">
            <DialogContent v-if="rejectModal.open">
                <DialogHeader>
                    <DialogTitle>Reject Transfer</DialogTitle>
                    <DialogDescription>
                        Please provide a reason for rejecting this transfer (optional).
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div v-if="rejectModal.transfer">
                        <Label>Lead</Label>
                        <p class="font-medium">{{ rejectModal.transfer.lead.full_name }}</p>
                    </div>
                    <div>
                        <Label>Rejection Reason (Optional)</Label>
                        <Textarea
                            v-model="rejectModal.reason"
                            placeholder="Enter reason for rejection..."
                            rows="3"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="rejectModal.open = false">Cancel</Button>
                    <Button variant="destructive" @click="rejectTransfer" :disabled="rejectModal.loading">
                        <Loader2 v-if="rejectModal.loading" class="mr-2 h-4 w-4 animate-spin" />
                        Reject Transfer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
