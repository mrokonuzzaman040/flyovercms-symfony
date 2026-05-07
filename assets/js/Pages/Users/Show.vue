<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DeleteConfirmation } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Mail,
    Building2,
    Calendar,
    Shield,
    Key,
    TrendingUp,
    Users,
    UserPlus,
    CheckCircle,
    Clock,
    FileText,
    AlertTriangle,
    Activity,
    BarChart3,
    Target,
    Zap,
    Award,
    Globe,
    Phone,
    MapPin,
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
    user: {
        type: Object,
        required: true,
    },
    statistics: {
        type: Object,
        default: () => ({}),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    performanceMetrics: {
        type: Object,
        default: () => ({}),
    },
    loginActivity: {
        type: Object,
        default: () => ({
            sessions: [],
            unique_ips: [],
            last_login: null,
            total_sessions: 0,
        }),
    },
});

const deleteModal = ref({
    open: false,
    loading: false,
});

const statusChartRef = ref(null);
const priorityChartRef = ref(null);
const monthlyChartRef = ref(null);

const getUserInitials = (name) => {
    return name
        ?.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2) || 'U';
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

const ACTIVITY_I_C_O_N_S = Object.freeze({
        'user-plus': UserPlus,
        'file-text': FileText,
        'check-circle': CheckCircle,
        'clock': Clock,
    });
const getActivityIcon = (iconName) => ACTIVITY_I_C_O_N_S[iconName] || Activity;

const handleDelete = () => {
    deleteModal.value.loading = true;
    router.delete(route('users.destroy', props.user.id), {
        onSuccess: () => {
            deleteModal.value.open = false;
        },
        onFinish: () => {
            deleteModal.value.loading = false;
        },
    });
};

const statCards = computed(() => [
    {
        title: 'Assigned Leads',
        value: props.statistics.total_assigned_leads || 0,
        icon: Users,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    },
    {
        title: 'Created Leads',
        value: props.statistics.total_created_leads || 0,
        icon: UserPlus,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    },
    {
        title: 'Converted',
        value: props.statistics.converted_leads || 0,
        icon: CheckCircle,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-900/30',
    },
    {
        title: 'Active Leads',
        value: props.statistics.active_leads || 0,
        icon: Activity,
        color: 'text-amber-600',
        bgColor: 'bg-amber-100 dark:bg-amber-900/30',
    },
    {
        title: 'Conversion Rate',
        value: `${props.statistics.conversion_rate || 0}%`,
        icon: TrendingUp,
        color: 'text-indigo-600',
        bgColor: 'bg-indigo-100 dark:bg-indigo-900/30',
    },
    {
        title: 'Follow-ups',
        value: props.statistics.follow_up_leads || 0,
        icon: Clock,
        color: 'text-rose-600',
        bgColor: 'bg-rose-100 dark:bg-rose-900/30',
    },
]);

onMounted(() => {
    // Initialize charts if data is available
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
    <Head :title="user.name" />
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('users.index')" 
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">User Details</h1>
                    <p class="text-muted-foreground">View user account information and performance metrics.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="route('users.edit', user.id)">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
                <Button variant="destructive" @click="deleteModal.open = true">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Profile Card -->
            <Card class="lg:col-span-1">
                <CardContent class="pt-6">
                    <div class="flex flex-col items-center text-center">
                        <Avatar class="h-24 w-24 mb-4">
                            <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                            <AvatarFallback class="text-2xl">{{ getUserInitials(user.name) }}</AvatarFallback>
                        </Avatar>
                        <h2 class="mt-4 text-xl font-semibold">{{ user.name }}</h2>
                        <p class="text-muted-foreground">{{ user.email }}</p>
                        
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <Badge 
                                v-for="role in user.roles" 
                                :key="role.id"
                                variant="secondary"
                            >
                                {{ role.name }}
                            </Badge>
                        </div>
                    </div>

                    <Separator class="my-6" />

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Email</p>
                                <p class="font-medium">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <Building2 class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Department</p>
                                <p class="font-medium">{{ user.department?.name || 'Not assigned' }}</p>
                            </div>
                        </div>

                        <div v-if="user.branch" class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Branch</p>
                                <p class="font-medium">{{ user.branch.name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Member since</p>
                                <p class="font-medium">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>

                        <div v-if="loginActivity.last_login" class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Last Login</p>
                                <p class="font-medium">{{ formatDateTime(loginActivity.last_login.last_activity) }}</p>
                            </div>
                        </div>

                        <div v-if="loginActivity.unique_ips && loginActivity.unique_ips.length > 0" class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                <Globe class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Login IPs</p>
                                <p class="font-medium">{{ loginActivity.unique_ips.length }} unique IP(s)</p>
                            </div>
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

        <!-- Login Activity -->
        <Card>
            <CardHeader>
                <div class="flex items-center gap-2">
                    <Globe class="h-5 w-5 text-muted-foreground" />
                    <CardTitle>Login Activity & IP Addresses</CardTitle>
                </div>
                <CardDescription>Recent login sessions and IP addresses</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="loginActivity.sessions && loginActivity.sessions.length > 0" class="space-y-3">
                    <div 
                        v-for="session in loginActivity.sessions" 
                        :key="session.id"
                        class="flex items-center justify-between p-3 rounded-lg border bg-muted/30"
                        :class="session.is_current ? 'border-primary bg-primary/5' : ''"
                    >
                        <div class="flex items-center gap-3 flex-1">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <Globe class="h-5 w-5 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-medium text-sm">{{ session.ip_address || 'Unknown IP' }}</p>
                                    <Badge v-if="session.is_current" variant="default" class="text-[10px]">Current Session</Badge>
                                </div>
                                <p class="text-xs text-muted-foreground truncate" :title="session.user_agent">
                                    {{ session.user_agent || 'Unknown browser' }}
                                </p>
                                <p class="text-xs text-muted-foreground/70 mt-1">
                                    {{ formatDateTime(session.last_activity) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <Globe class="h-12 w-12 text-muted-foreground/30 mx-auto mb-2" />
                    <p class="text-muted-foreground text-sm">No login activity recorded</p>
                </div>

                <div v-if="loginActivity.unique_ips && loginActivity.unique_ips.length > 0" class="mt-6 pt-6 border-t">
                    <p class="text-sm font-medium mb-3">Unique IP Addresses ({{ loginActivity.unique_ips.length }})</p>
                    <div class="flex flex-wrap gap-2">
                        <Badge 
                            v-for="(ip, index) in loginActivity.unique_ips" 
                            :key="index"
                            variant="outline"
                            class="font-mono text-xs"
                        >
                            {{ ip }}
                        </Badge>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Activity Timeline & Roles/Permissions -->
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

            <!-- Roles & Permissions -->
            <div class="space-y-6">
                <!-- Roles -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Roles</CardTitle>
                        </div>
                        <CardDescription>Assigned roles and permissions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="user.roles?.length" class="space-y-3">
                            <div 
                                v-for="role in user.roles" 
                                :key="role.id"
                                class="flex items-center gap-3 p-3 rounded-lg border bg-muted/30"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                    <Shield class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ role.name }}</p>
                                    <p v-if="role.description" class="text-xs text-muted-foreground">
                                        {{ role.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground text-center py-8">No roles assigned.</p>
                    </CardContent>
                </Card>

                <!-- Permissions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Key class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Permissions</CardTitle>
                        </div>
                        <CardDescription>Granted permissions through roles</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="user.permissions?.length" class="flex flex-wrap gap-2">
                            <Badge 
                                v-for="permission in user.permissions" 
                                :key="permission.id"
                                variant="outline"
                            >
                                {{ permission.name }}
                            </Badge>
                        </div>
                        <p v-else class="text-muted-foreground text-center py-8">No specific permissions.</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete User"
            :description="`Are you sure you want to delete ${user.name}? This action cannot be undone and all associated data will be lost.`"
            @confirm="handleDelete"
        />
    </div>
</template>
