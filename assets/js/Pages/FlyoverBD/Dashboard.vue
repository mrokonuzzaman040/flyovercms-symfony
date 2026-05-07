<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { 
    MessageSquare, 
    FileQuestion, 
    CalendarCheck, 
    ArrowRight,
    AlertCircle,
    Download,
    RefreshCw,
    Activity,
    Globe,
    Zap
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            contacts_count: 0,
            inquiries_count: 0,
            bookings_count: 0
        })
    },
    error: {
        type: String,
        default: null
    }
});

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'FlyoverBD Connection', active: true }
];

const lastSync = ref('Just now');
const isRefreshing = ref(false);

const refreshData = () => {
    isRefreshing.value = true;
    // Inertia reload to get fresh data from server
    window.location.reload();
};

const stats = [
    {
        title: 'Contact Messages',
        description: 'Direct messages from website visitors',
        value: props.summary?.contacts_count || 0,
        icon: MessageSquare,
        color: 'text-blue-500',
        bgColor: 'bg-blue-500/10',
        href: route('flyover-bd.contacts')
    },
    {
        title: 'Custom Inquiries',
        description: 'Specific requests for tour plans',
        value: props.summary?.inquiries_count || 0,
        icon: FileQuestion,
        color: 'text-purple-500',
        bgColor: 'bg-purple-500/10',
        href: route('flyover-bd.inquiries')
    },
    {
        title: 'Tour Bookings',
        description: 'Confirmed and pending tour orders',
        value: props.summary?.bookings_count || 0,
        icon: CalendarCheck,
        color: 'text-green-500',
        bgColor: 'bg-green-500/10',
        href: route('flyover-bd.bookings')
    }
];
</script>

<template>
    <Head title="FlyoverBD Connection Summary" />

    <DashboardLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">FlyoverBD Connection</h1>
                    <p class="text-muted-foreground mt-1 flex items-center gap-2 text-sm">
                        <Globe class="h-3 w-3" /> Live feed from flyoverbd.net API
                        <span class="mx-1">•</span>
                        <span class="flex items-center gap-1 text-xs">
                            <Activity class="h-3 w-3 text-green-500" /> System Active
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <p class="hidden md:block text-[11px] text-muted-foreground">
                        Last sync: {{ lastSync }}
                    </p>
                    <Button variant="outline" size="sm" @click="refreshData" :disabled="isRefreshing">
                        <RefreshCw :class="['mr-2 h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
                        Refresh Data
                    </Button>
                </div>
            </div>

            <Alert v-if="error" variant="destructive" class="border-red-200 bg-red-50 text-red-900">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>API Connectivity Issue</AlertTitle>
                <AlertDescription class="mt-2 text-red-700">
                    {{ error }}
                    <div class="mt-3">
                        <p class="font-semibold text-xs text-red-800">Troubleshooting Steps:</p>
                        <ul class="list-disc pl-5 mt-1 text-xs space-y-1">
                            <li>Verify your API Token in the <code class="bg-red-100 px-1 rounded">.env</code> file.</li>
                            <li>Ensure <code class="bg-red-100 px-1 rounded">FLYOVER_API_URL</code> is set to <code class="bg-red-100 px-1 rounded">https://flyoverbd.net/api/export/v1</code>.</li>
                            <li>Check if the remote server is reachable.</li>
                        </ul>
                    </div>
                </AlertDescription>
            </Alert>

            <div class="grid gap-6 md:grid-cols-3">
                <Card v-for="stat in stats" :key="stat.title" class="overflow-hidden border-2 transition-all hover:border-primary/20 hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-semibold text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="['rounded-full p-2.5', stat.bgColor]">
                            <component :is="stat.icon" :class="['h-5 w-5', stat.color]" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-black">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground mt-2 leading-relaxed">
                            {{ stat.description }}
                        </p>
                        <div class="mt-6">
                            <Link :href="stat.href">
                                <Button variant="secondary" size="sm" class="w-full justify-between px-3 h-9 bg-muted/50 hover:bg-muted font-bold text-xs">
                                    Browse Records
                                    <ArrowRight class="h-4 w-4 ml-2" />
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">
                <Card class="col-span-4 border shadow-sm">
                    <CardHeader class="pb-3 border-b bg-muted/5">
                        <div class="flex items-center gap-2">
                            <Zap class="h-4 w-4 text-amber-500 fill-amber-500" />
                            <CardTitle class="text-lg">Security & Connection Status</CardTitle>
                        </div>
                        <CardDescription>
                            Current API configuration and health monitoring.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between group">
                                <div class="space-y-0.5">
                                    <p class="text-sm font-bold">API Endpoint</p>
                                    <p class="text-xs text-muted-foreground">The primary hub for data synchronization.</p>
                                </div>
                                <code class="text-xs bg-muted px-2 py-1 rounded border font-mono">flyoverbd.net</code>
                            </div>
                            <div class="flex items-center justify-between group">
                                <div class="space-y-0.5">
                                    <p class="text-sm font-bold">Authentication Mode</p>
                                    <p class="text-xs text-muted-foreground">Secured via encrypted X-API-TOKEN.</p>
                                </div>
                                <span class="text-xs text-green-600 font-bold flex items-center gap-1.5 bg-green-50 px-2 py-1 rounded-full border border-green-100">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    Verified
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <div class="space-y-0.5">
                                    <p class="text-sm font-bold">Sync Topology</p>
                                    <p class="text-xs text-muted-foreground">Real-time on-demand pull requests.</p>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground bg-muted/50 px-2 py-1 rounded">Active Pull</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="col-span-3 border shadow-sm">
                    <CardHeader class="pb-3 border-b bg-muted/5">
                        <CardTitle class="text-lg">Resources</CardTitle>
                        <CardDescription>
                            Quick links to related modules.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-3 pt-6">
                        <Link :href="route('flyover-bd.contacts')" class="flex items-center gap-4 rounded-xl border p-4 transition-all hover:bg-blue-50/50 hover:border-blue-200 group">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                <MessageSquare class="h-5 w-5 text-blue-600" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-none">Contact Manager</p>
                                <p class="text-[11px] text-muted-foreground mt-1">Review website queries</p>
                            </div>
                        </Link>
                        <Link :href="route('flyover-bd.inquiries')" class="flex items-center gap-4 rounded-xl border p-4 transition-all hover:bg-purple-50/50 hover:border-purple-200 group">
                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                                <FileQuestion class="h-5 w-5 text-purple-600" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-none">Tour Inquiry Hub</p>
                                <p class="text-[11px] text-muted-foreground mt-1">Manage custom tour plans</p>
                            </div>
                        </Link>
                        <Link :href="route('flyover-bd.bookings')" class="flex items-center gap-4 rounded-xl border p-4 transition-all hover:bg-green-50/50 hover:border-green-200 group">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                <CalendarCheck class="h-5 w-5 text-green-600" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-none">Booking Pipeline</p>
                                <p class="text-[11px] text-muted-foreground mt-1">Track confirmed orders</p>
                            </div>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </DashboardLayout>
</template>
