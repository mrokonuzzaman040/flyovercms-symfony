<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import {
    Plus,
    Eye,
    Pencil,
    UserCog,
    AlertCircle,
    Users,
    Shield,
    UserCheck,
    UserX,
    Search,
    Filter,
    BarChart3,
} from 'lucide-vue-next';
import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { BarController } from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    BarController
);

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, required: true },
    statistics: { type: Object, default: () => ({}) },
    roleDistribution: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role_id || '');
const roleChartRef = ref(null);
const page = usePage();

// Watch for updates and reload data if needed
watch(() => page.props.flash?.updated_user_id, (userId) => {
    if (userId) {
        // Reload the page data to show updated roles
        router.reload({ only: ['users', 'statistics', 'roleDistribution'] });
    }
});

const getUserInitials = (name) => {
    return name
        ?.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2) || 'U';
};

const handleSearch = () => {
    router.get(route('user-roles.index'), {
        ...props.filters,
        search: searchQuery.value,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleRoleFilter = (roleId) => {
    selectedRole.value = roleId;
    router.get(route('user-roles.index'), {
        ...props.filters,
        role_id: roleId || null,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    router.get(route('user-roles.index'), {
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const statCards = computed(() => [
    {
        title: 'Total Users',
        value: props.statistics.total_users || 0,
        icon: Users,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    },
    {
        title: 'With Roles',
        value: props.statistics.users_with_roles || 0,
        icon: UserCheck,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-900/30',
    },
    {
        title: 'Without Roles',
        value: props.statistics.users_without_roles || 0,
        icon: UserX,
        color: 'text-amber-600',
        bgColor: 'bg-amber-100 dark:bg-amber-900/30',
    },
    {
        title: 'Total Roles',
        value: props.statistics.total_roles || 0,
        icon: Shield,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    },
    {
        title: 'Total Assignments',
        value: props.statistics.total_assignments || 0,
        icon: UserCog,
        color: 'text-indigo-600',
        bgColor: 'bg-indigo-100 dark:bg-indigo-900/30',
    },
]);

const initRoleChart = () => {
    const ctx = roleChartRef.value?.getContext('2d');
    if (!ctx || !props.roleDistribution?.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.roleDistribution.map(r => r.name),
            datasets: [{
                label: 'Users',
                data: props.roleDistribution.map(r => r.count),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                borderRadius: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
};

// Initialize chart on mount
onMounted(() => {
    if (roleChartRef.value && props.roleDistribution?.length) {
        initRoleChart();
    }
});
</script>

<template>
    <Head title="User Roles" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">User Roles Management</h1>
                <p class="text-muted-foreground">Assign and manage user roles across the system</p>
            </div>
            <Button as-child>
                <Link :href="route('user-roles.create')">
                    <Plus class="mr-2 h-4 w-4" />
                    Assign Role
                </Link>
            </Button>
        </div>

        <!-- Flash Message -->
        <div 
            v-if="$page.props.flash?.success" 
            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center gap-2"
        >
            <AlertCircle class="h-4 w-4" />
            {{ $page.props.flash.success }}
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <Card 
                v-for="(stat, index) in statCards" 
                :key="index"
                class="hover:shadow-md transition-shadow"
            >
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div :class="['p-2 rounded-lg', stat.bgColor]">
                            <component :is="stat.icon" :class="['h-5 w-5', stat.color]" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-muted-foreground font-medium truncate">{{ stat.title }}</p>
                            <p class="text-xl font-bold">{{ stat.value }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Role Distribution Chart -->
            <Card class="lg:col-span-1">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Role Distribution</CardTitle>
                    </div>
                    <CardDescription>Users per role</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="roleDistribution.length" class="h-64">
                        <canvas ref="roleChartRef"></canvas>
                    </div>
                    <div v-else class="text-center py-8">
                        <Shield class="h-12 w-12 text-muted-foreground/30 mx-auto mb-2" />
                        <p class="text-muted-foreground text-sm">No role assignments</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card class="lg:col-span-2">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <CardTitle>All Users</CardTitle>
                            <CardDescription>Manage user role assignments</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 max-w-xs">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search users..."
                                    class="pl-9 h-9"
                                    @keyup.enter="handleSearch"
                                />
                            </div>
                            <Select :model-value="selectedRole" @update:model-value="handleRoleFilter">
                                <SelectTrigger class="w-[180px] h-9">
                                    <Filter class="mr-2 h-4 w-4" />
                                    <SelectValue placeholder="Filter by role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Roles</SelectItem>
                                    <SelectItem 
                                        v-for="role in roles" 
                                        :key="role.id" 
                                        :value="String(role.id)"
                                    >
                                        {{ role.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button 
                                v-if="filters.search || filters.role_id" 
                                variant="outline" 
                                size="sm"
                                @click="clearFilters"
                            >
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        User
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        Roles
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr 
                                    v-for="user in users.data" 
                                    :key="user.id" 
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar class="h-8 w-8">
                                                <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                                <AvatarFallback>{{ getUserInitials(user.name) }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <div class="font-medium">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge 
                                                v-for="role in user.roles" 
                                                :key="role.id" 
                                                variant="secondary" 
                                                class="text-xs"
                                            >
                                                {{ role.name }}
                                            </Badge>
                                            <span v-if="!user.roles?.length" class="text-xs text-muted-foreground">
                                                No roles
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                                <Link :href="route('user-roles.show', user.id)">
                                                    <Eye class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                                <Link :href="route('user-roles.edit', user.id)">
                                                    <Pencil class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!users.data?.length">
                                    <td colspan="4" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                                <UserCog class="h-6 w-6 text-muted-foreground" />
                                            </div>
                                            <p class="text-sm font-medium">No users found</p>
                                            <p class="text-xs text-muted-foreground">
                                                Try adjusting your search or filters
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links?.length > 3" class="flex items-center justify-center gap-1 p-4 border-t">
                        <template v-for="(link, index) in users.links" :key="index">
                            <Button
                                v-if="link.url"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                class="h-8 text-xs"
                                as-child
                            >
                                <Link :href="link.url" v-html="link.label" />
                            </Button>
                            <Button
                                v-else
                                variant="outline"
                                size="sm"
                                class="h-8 text-xs"
                                disabled
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
