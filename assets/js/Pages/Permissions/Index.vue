<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, DeleteConfirmation } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Plus, MoreHorizontal, Eye, Pencil, Trash2, Key } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    permissions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteModal = ref({ open: false, permission: null, loading: false });

const columns = [
    { key: 'name', label: 'Permission', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'group', label: 'Group', sortable: true },
    { key: 'roles', label: 'Roles', sortable: false },
    { key: 'actions', label: '', width: '60px' },
];

const handleSort = ({ field, direction }) => {
    router.get(route('permissions.index'), { ...props.filters, sort: field, direction }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (query) => {
    router.get(route('permissions.index'), { ...props.filters, search: query, page: 1 }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('permissions.index'), { ...props.filters, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(route('permissions.index'), { ...props.filters, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const openDeleteModal = (permission) => {
    deleteModal.value = { open: true, permission, loading: false };
};

const handleDelete = () => {
    if (!deleteModal.value.permission) return;
    deleteModal.value.loading = true;
    router.delete(route('permissions.destroy', deleteModal.value.permission.id), {
        preserveScroll: true,
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};
</script>

<template>
    <Head title="Permissions" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Permissions</h1>
                <p class="text-muted-foreground">Manage system permissions.</p>
            </div>
            <Button as-child>
                <Link :href="route('permissions.create')">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Permission
                </Link>
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="permissions.data"
            :pagination="permissions"
            :sort-field="filters.sort"
            :sort-direction="filters.direction"
            :search-query="filters.search"
            search-placeholder="Search permissions..."
            @sort="handleSort"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2">
                    <Key class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium">{{ row.name }}</span>
                </div>
            </template>

            <template #cell-slug="{ row }">
                <code class="text-xs bg-muted px-1.5 py-0.5 rounded">{{ row.slug }}</code>
            </template>

            <template #cell-group="{ row }">
                <Badge v-if="row.group" variant="outline">{{ row.group }}</Badge>
                <span v-else class="text-muted-foreground">-</span>
            </template>

            <template #cell-roles="{ row }">
                <Badge variant="secondary">{{ row.roles?.length || 0 }} roles</Badge>
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
                            <Link :href="route('permissions.show', row.id)" class="flex items-center">
                                <Eye class="mr-2 h-4 w-4" /> View
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="route('permissions.edit', row.id)" class="flex items-center">
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
            title="Delete Permission"
            :description="`Are you sure you want to delete the '${deleteModal.permission?.name}' permission?`"
            @confirm="handleDelete"
        />
    </div>
</template>
