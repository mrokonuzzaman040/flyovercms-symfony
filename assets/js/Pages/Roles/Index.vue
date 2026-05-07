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
import { Plus, MoreHorizontal, Eye, Pencil, Trash2, Shield } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    roles: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteModal = ref({ open: false, role: null, loading: false });

const columns = [
    { key: 'name', label: 'Role', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'permissions', label: 'Permissions', sortable: false },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: '', width: '60px' },
];

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const handleSort = ({ field, direction }) => {
    router.get(route('roles.index'), { ...props.filters, sort: field, direction }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (query) => {
    router.get(route('roles.index'), { ...props.filters, search: query, page: 1 }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('roles.index'), { ...props.filters, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(route('roles.index'), { ...props.filters, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const openDeleteModal = (role) => {
    deleteModal.value = { open: true, role, loading: false };
};

const handleDelete = () => {
    if (!deleteModal.value.role) return;
    deleteModal.value.loading = true;
    router.delete(route('roles.destroy', deleteModal.value.role.id), {
        preserveScroll: true,
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};
</script>

<template>
    <Head title="Roles" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Roles</h1>
                <p class="text-muted-foreground">Manage user roles and their permissions.</p>
            </div>
            <Button as-child>
                <Link :href="route('roles.create')">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Role
                </Link>
            </Button>
        </div>

        <DataTable
            :columns="columns"
            :data="roles.data"
            :pagination="roles"
            :sort-field="filters.sort"
            :sort-direction="filters.direction"
            :search-query="filters.search"
            search-placeholder="Search roles..."
            @sort="handleSort"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                        <Shield class="h-4 w-4 text-primary" />
                    </div>
                    <div>
                        <Link :href="route('roles.show', row.id)" class="font-medium hover:underline">{{ row.name }}</Link>
                        <p v-if="row.description" class="text-xs text-muted-foreground">{{ row.description }}</p>
                    </div>
                </div>
            </template>

            <template #cell-slug="{ row }">
                <code class="text-xs bg-muted px-1.5 py-0.5 rounded">{{ row.slug }}</code>
            </template>

            <template #cell-permissions="{ row }">
                <Badge variant="secondary">{{ row.permissions?.length || 0 }} permissions</Badge>
            </template>

            <template #cell-created_at="{ row }">
                <span class="text-sm text-muted-foreground">{{ formatDate(row.created_at) }}</span>
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
                            <Link :href="route('roles.show', row.id)" class="flex items-center">
                                <Eye class="mr-2 h-4 w-4" /> View
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="route('roles.edit', row.id)" class="flex items-center">
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
            title="Delete Role"
            :description="`Are you sure you want to delete the '${deleteModal.role?.name}' role?`"
            @confirm="handleDelete"
        />
    </div>
</template>
