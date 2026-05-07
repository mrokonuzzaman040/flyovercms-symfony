<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, DeleteConfirmation, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Plus, MoreHorizontal, Eye, Pencil, Trash2, Building2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    branches: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteModal = ref({ open: false, branch: null, loading: false });

const columns = [
    { key: 'name', label: 'Branch', sortable: true },
    { key: 'code', label: 'Code', sortable: true },
    { key: 'users_count', label: 'Users', sortable: true },
    { key: 'leads_count', label: 'Leads', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: '', width: '60px' },
];

const handleSort = ({ field, direction }) => {
    router.get(route('branches.index'), { ...props.filters, sort: field, direction }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (query) => {
    router.get(route('branches.index'), { ...props.filters, search: query, page: 1 }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('branches.index'), { ...props.filters, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(route('branches.index'), { ...props.filters, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const openDeleteModal = (branch) => {
    deleteModal.value = { open: true, branch, loading: false };
};

const handleDelete = () => {
    if (!deleteModal.value.branch) return;
    deleteModal.value.loading = true;
    router.delete(route('branches.destroy', deleteModal.value.branch.id), {
        preserveScroll: true,
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};
</script>

<template>
    <Head title="Branches" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Branches</h1>
                <p class="text-muted-foreground">Manage company branches and locations.</p>
            </div>
            <Button as-child>
                <Link :href="route('branches.create')">
                    <Plus class="mr-2 h-4 w-4" /> Add Branch
                </Link>
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="branches.data"
            :pagination="branches"
            :sort-field="filters.sort"
            :sort-direction="filters.direction"
            :search-query="filters.search"
            search-placeholder="Search branches..."
            @sort="handleSort"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                        <Building2 class="h-4 w-4 text-primary" />
                    </div>
                    <div>
                        <Link :href="route('branches.show', row.id)" class="font-medium hover:underline">{{ row.name }}</Link>
                        <p v-if="row.address" class="text-xs text-muted-foreground truncate max-w-[200px]">{{ row.address }}</p>
                    </div>
                </div>
            </template>

            <template #cell-code="{ row }">
                <code class="text-xs bg-muted px-1.5 py-0.5 rounded">{{ row.code }}</code>
            </template>

            <template #cell-users_count="{ row }">
                <Badge variant="secondary">{{ row.users_count || 0 }}</Badge>
            </template>

            <template #cell-leads_count="{ row }">
                <Badge variant="outline">{{ row.leads_count || 0 }}</Badge>
            </template>

            <template #cell-is_active="{ row }">
                <StatusBadge :status="row.is_active ? 'active' : 'inactive'" />
            </template>

            <template #cell-actions="{ row }">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8">
                            <MoreHorizontal class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('branches.show', row.id)" class="flex items-center">
                                <Eye class="mr-2 h-4 w-4" /> View
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="route('branches.edit', row.id)" class="flex items-center">
                                <Pencil class="mr-2 h-4 w-4" /> Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem class="text-destructive" @click="openDeleteModal(row)">
                            <Trash2 class="mr-2 h-4 w-4" /> Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </template>
        </DataTable>

        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete Branch"
            :description="`Are you sure you want to delete '${deleteModal.branch?.name}'?`"
            @confirm="handleDelete"
        />
    </div>
</template>
