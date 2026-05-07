<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, DeleteConfirmation, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    UserPlus,
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const deleteModal = ref({
    open: false,
    user: null,
    loading: false,
});

const columns = [
    { key: 'name', label: 'User', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'department.name', label: 'Department', sortable: false },
    { key: 'roles', label: 'Roles', sortable: false },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: '', width: '60px', sortable: false },
];

const getUserInitials = (name) => {
    return name
        ?.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2) || 'U';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const handleSort = ({ field, direction }) => {
    router.get(route('users.index'), {
        ...props.filters,
        sort: field,
        direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSearch = (query) => {
    router.get(route('users.index'), {
        ...props.filters,
        search: query,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageChange = (page) => {
    router.get(route('users.index'), {
        ...props.filters,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePerPageChange = (perPage) => {
    router.get(route('users.index'), {
        ...props.filters,
        per_page: perPage,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openDeleteModal = (user) => {
    deleteModal.value = {
        open: true,
        user,
        loading: false,
    };
};

const handleDelete = () => {
    if (!deleteModal.value.user) return;
    
    deleteModal.value.loading = true;
    router.delete(route('users.destroy', deleteModal.value.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteModal.value.open = false;
        },
        onFinish: () => {
            deleteModal.value.loading = false;
        },
    });
};
</script>

<template>
    <Head title="Users" />
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                <p class="text-muted-foreground">Manage user accounts and their roles.</p>
            </div>
            <Button as-child>
                <Link :href="route('users.create')">
                    <UserPlus class="mr-2 h-4 w-4" />
                    Add User
                </Link>
            </Button>
        </div>

        <!-- Data Table -->
        <DataTable
            :columns="columns"
            :data="users.data"
            :pagination="users"
            :sort-field="filters.sort"
            :sort-direction="filters.direction"
            :search-query="filters.search"
            search-placeholder="Search users..."
            @sort="handleSort"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <!-- User Cell -->
            <template #cell-name="{ row }">
                <div class="flex items-center gap-3">
                    <Avatar class="h-8 w-8">
                        <AvatarImage v-if="row.avatar" :src="row.avatar" :alt="row.name" />
                        <AvatarFallback>{{ getUserInitials(row.name) }}</AvatarFallback>
                    </Avatar>
                    <div>
                        <Link 
                            :href="route('users.show', row.id)"
                            class="font-medium hover:underline"
                        >
                            {{ row.name }}
                        </Link>
                    </div>
                </div>
            </template>

            <!-- Email Cell -->
            <template #cell-email="{ row }">
                <span class="text-muted-foreground">{{ row.email }}</span>
            </template>

            <!-- Department Cell -->
            <template #cell-department.name="{ row }">
                <span v-if="row.department">{{ row.department.name }}</span>
                <span v-else class="text-muted-foreground">-</span>
            </template>

            <!-- Roles Cell -->
            <template #cell-roles="{ row }">
                <div class="flex flex-wrap gap-1">
                    <Badge 
                        v-for="role in row.roles" 
                        :key="role.id"
                        variant="secondary"
                        class="text-xs"
                    >
                        {{ role.name }}
                    </Badge>
                    <span v-if="!row.roles?.length" class="text-muted-foreground">-</span>
                </div>
            </template>

            <!-- Created Date Cell -->
            <template #cell-created_at="{ row }">
                <span class="text-sm text-muted-foreground">{{ formatDate(row.created_at) }}</span>
            </template>

            <!-- Actions Cell -->
            <template #cell-actions="{ row }">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8">
                            <MoreHorizontal class="h-4 w-4" />
                            <span class="sr-only">Open menu</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('users.show', row.id)" class="flex items-center">
                                <Eye class="mr-2 h-4 w-4" />
                                View
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="route('users.edit', row.id)" class="flex items-center">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem 
                            class="text-destructive focus:text-destructive"
                            @click="openDeleteModal(row)"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </template>
        </DataTable>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete User"
            :description="`Are you sure you want to delete ${deleteModal.user?.name}? This action cannot be undone.`"
            @confirm="handleDelete"
        />
    </div>
</template>
