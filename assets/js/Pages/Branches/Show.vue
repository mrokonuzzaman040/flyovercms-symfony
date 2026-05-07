<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DeleteConfirmation, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Building2,
    Users,
    FileText,
    Mail,
    Phone,
    MapPin,
    TrendingUp,
    CheckCircle,
    Clock,
    Activity,
    BarChart3,
    Target,
    Zap,
    UserPlus,
    AlertTriangle,
} from 'lucide-vue-next';
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { LineController, BarController, DoughnutController } from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    LineController,
    BarController,
    DoughnutController
);

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    branch: { type: Object, required: true },
    statistics: { type: Object, default: () => ({}) },
    recentActivity: { type: Array, default: () => [] },
    performanceMetrics: { type: Object, default: () => ({}) },
    recentLeads: { type: Array, default: () => [] },
    branchUsers: { type: Array, default: () => [] },
});

const deleteModal = ref({ open: false, loading: false });

const statusChartRef = ref(null);
const priorityChartRef = ref(null);
const monthlyChartRef = ref(null);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatRelativeTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDate(dateString);
};

const getActivityIcon = (iconName) => {
    const icons = {
        'user-plus': UserPlus,
        'file-text': FileText,
        'check-circle': CheckCircle,
        'clock': Clock,
    };
    return icons[iconName] || Activity;
};

const handleDelete = () => {
    deleteModal.value.loading = true;
    router.delete(route('branches.destroy', props.branch.id), {
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
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
        title: 'Branch Managers',
        value: props.statistics.branch_managers || 0,
        icon: Users,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-900/30',
    },
    {
        title: 'Total Leads',
        value: props.statistics.total_leads || 0,
        icon: FileText,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    },
    {
        title: 'Active Leads',
        value: props.statistics.active_leads || 0,
        icon: Activity,
        color: 'text-amber-600',
        bgColor: 'bg-amber-100 dark:bg-amber-900/30',
    },
    {
        title: 'Converted',
        value: props.statistics.converted_leads || 0,
        icon: CheckCircle,
        color: 'text-emerald-600',
        bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
    },
    {
        title: 'Conversion Rate',
        value: `${props.statistics.conversion_rate || 0}%`,
        icon: TrendingUp,
        color: 'text-indigo-600',
        bgColor: 'bg-indigo-100 dark:bg-indigo-900/30',
    },
]);

onMounted(() => {
    if (statusChartRef.value && props.performanceMetrics.leads_by_status) {
        initStatusChart();
    }
    if (priorityChartRef.value && props.performanceMetrics.leads_by_priority) {
        initPriorityChart();
    }
    if (monthlyChartRef.value && props.performanceMetrics.monthly_leads) {
        initMonthlyChart();
    }
});

const initStatusChart = () => {
    const ctx = statusChartRef.value?.getContext('2d');
    if (!ctx) return;

    const data = props.performanceMetrics.leads_by_status || {};
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(data),
            datasets: [{
                data: Object.values(data),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                borderWidth: 2,
                borderColor: 'rgba(255, 255, 255, 0.1)',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
        },
    });
};

const initPriorityChart = () => {
    const ctx = priorityChartRef.value?.getContext('2d');
    if (!ctx) return;

    const data = props.performanceMetrics.leads_by_priority || {};
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(data).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
            datasets: [{
                label: 'Leads',
                data: Object.values(data),
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

const initMonthlyChart = () => {
    const ctx = monthlyChartRef.value?.getContext('2d');
    if (!ctx) return;

    const data = props.performanceMetrics.monthly_leads || {};
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(data),
            datasets: [{
                label: 'Leads',
                data: Object.values(data),
                borderColor: 'rgba(99, 102, 241, 1)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
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
</script>

<template>
    <Head :title="branch.name" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('branches.index')" 
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight">{{ branch.name }}</h1>
                        <StatusBadge :status="branch.is_active ? 'active' : 'inactive'" />
                    </div>
                    <p class="text-muted-foreground">{{ branch.description || 'Branch details and performance metrics' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="route('branches.edit', branch.id)">
                        <Pencil class="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" @click="deleteModal.open = true">
                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Branch Info Card -->
            <Card class="lg:col-span-1">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-primary" />
                        <CardTitle>Branch Information</CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground mb-1">Code</p>
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ branch.code }}</code>
                    </div>
                    <div v-if="branch.email" class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                            <Mail class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ branch.email }}</p>
                        </div>
                    </div>
                    <div v-if="branch.phone" class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                            <Phone class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Phone</p>
                            <p class="font-medium">{{ branch.phone }}</p>
                        </div>
                    </div>
                    <div v-if="branch.address" class="flex items-start gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Address</p>
                            <p class="font-medium">{{ branch.address }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Performance Charts -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Monthly Leads Chart -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Monthly Leads Trend</CardTitle>
                        </div>
                        <CardDescription>Last 6 months performance</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <canvas ref="monthlyChartRef"></canvas>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status & Priority Charts -->
                <div class="grid md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <Target class="h-5 w-5 text-muted-foreground" />
                                <CardTitle>Leads by Status</CardTitle>
                            </div>
                            <CardDescription>Status distribution</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-64">
                                <canvas ref="statusChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <Zap class="h-5 w-5 text-muted-foreground" />
                                <CardTitle>Leads by Priority</CardTitle>
                            </div>
                            <CardDescription>Priority breakdown</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-64">
                                <canvas ref="priorityChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Card 
                v-for="(stat, index) in statCards" 
                :key="index"
                class="hover:shadow-md transition-shadow"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div :class="['p-3 rounded-lg', stat.bgColor]">
                            <component :is="stat.icon" :class="['h-6 w-6', stat.color]" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground font-medium">{{ stat.title }}</p>
                        <p class="text-3xl font-bold">{{ stat.value }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Activity Timeline & Branch Users -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Activity Timeline -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Recent Activity</CardTitle>
                    </div>
                    <CardDescription>Latest actions and updates</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto">
                        <div 
                            v-for="(activity, index) in recentActivity" 
                            :key="index"
                            class="relative pl-8 pb-4 border-l-2 border-muted last:border-0 last:pb-0"
                        >
                            <div class="absolute left-0 top-0 -translate-x-1/2">
                                <div class="p-1.5 rounded-full bg-muted border-2 border-background">
                                    <component :is="getActivityIcon(activity.icon)" class="h-3 w-3 text-muted-foreground" />
                                </div>
                            </div>
                            <div class="bg-muted/50 rounded-lg p-3">
                                <p class="font-medium text-sm mb-1">{{ activity.title }}</p>
                                <p class="text-muted-foreground text-xs mb-2">{{ activity.description }}</p>
                                <p class="text-muted-foreground/70 text-xs">{{ formatRelativeTime(activity.timestamp) }}</p>
                            </div>
                        </div>
                        <div v-if="recentActivity.length === 0" class="text-center py-8">
                            <Activity class="h-12 w-12 text-muted-foreground/30 mx-auto mb-2" />
                            <p class="text-muted-foreground text-sm">No recent activity</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Branch Users -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Branch Users</CardTitle>
                    </div>
                    <CardDescription>{{ branchUsers.length }} users assigned</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="branchUsers.length" class="space-y-2 max-h-[500px] overflow-y-auto">
                        <div 
                            v-for="user in branchUsers" 
                            :key="user.id" 
                            class="flex items-center justify-between p-3 rounded-lg border bg-muted/30 hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex-1">
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                            </div>
                            <div class="flex gap-1 flex-wrap justify-end">
                                <Badge 
                                    v-for="role in user.roles" 
                                    :key="role.id" 
                                    variant="secondary" 
                                    class="text-xs"
                                >
                                    {{ role.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground text-center py-8">No users assigned to this branch.</p>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Leads -->
        <Card>
            <CardHeader>
                <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-muted-foreground" />
                    <CardTitle>Recent Leads</CardTitle>
                </div>
                <CardDescription>Latest leads in this branch</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="recentLeads.length" class="space-y-2">
                    <div 
                        v-for="lead in recentLeads" 
                        :key="lead.id"
                        class="flex items-center justify-between p-3 rounded-lg border bg-muted/30 hover:bg-muted/50 transition-colors"
                    >
                        <div class="flex-1">
                            <p class="font-medium">{{ lead.full_name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <StatusBadge v-if="lead.status" :status="lead.status" />
                                <Badge v-if="lead.priority" variant="outline" class="text-xs">
                                    {{ lead.priority }}
                                </Badge>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatRelativeTime(lead.created_at) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p v-if="lead.assignedUser" class="text-sm text-muted-foreground">
                                Assigned to: {{ lead.assignedUser.name }}
                            </p>
                            <p v-if="lead.department" class="text-sm text-muted-foreground">
                                {{ lead.department.name }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-muted-foreground text-center py-8">No recent leads.</p>
            </CardContent>
        </Card>

        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete Branch"
            :description="`Are you sure you want to delete '${branch.name}'? This will affect all users and leads associated with this branch.`"
            @confirm="handleDelete"
        />
    </div>
</template>
