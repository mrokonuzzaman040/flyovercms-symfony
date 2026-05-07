<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { ListTodo, Mail, MessageSquare, Phone, Layers, ChevronDown, RotateCw, Trash2, AlertCircle } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    pendingByQueue: { type: Object, required: true },
    totalPending: { type: Number, default: 0 },
    queueNames: { type: Array, default: () => [] },
    failedJobs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    connection: { type: String, default: 'sync' },
});

const queueLabel = (name) => {
    const map = {
        'marketing-whatsapp': 'WhatsApp',
        'marketing-sms': 'SMS',
        'marketing-email': 'Email',
        'marketing-multi-channel': 'Multi-Channel',
        default: 'Default',
    };
    return map[name] || name;
};

const queueIcon = (name) => {
    const map = {
        'marketing-whatsapp': MessageSquare,
        'marketing-sms': Phone,
        'marketing-email': Mail,
        'marketing-multi-channel': Layers,
        default: ListTodo,
    };
    return map[name] || ListTodo;
};

const applyFilter = (key, value) => {
    const next = { ...props.filters, [key]: value || undefined };
    if (!value) delete next[key];
    router.get(route('marketing.queue.index'), next, { preserveState: true });
};

const retry = (uuid) => {
    router.post(route('marketing.queue.retry', { uuid }), {}, { preserveScroll: true });
};

const forget = (uuid) => {
    router.delete(route('marketing.queue.forget', { uuid }), { preserveScroll: true });
};

const retryAll = () => {
    router.post(route('marketing.queue.retry-all'), props.filters.queue ? { queue: props.filters.queue } : {}, { preserveScroll: true });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString();
};

const exceptionPreview = (str, max = 120) => {
    if (!str) return '—';
    const firstLine = str.split("\n")[0]?.trim() || str;
    return firstLine.length > max ? firstLine.slice(0, max) + '…' : firstLine;
};
</script>

<template>
    <Head title="Queue Monitoring" />

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Queue Monitoring</h1>
                <p class="text-xs text-muted-foreground">
                    Pending and failed jobs for WhatsApp, SMS, Email and multi-channel. Connection: {{ connection }}
                </p>
            </div>
        </div>

        <!-- Pending by queue -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <Card
                v-for="name in queueNames"
                :key="name"
                class="overflow-hidden"
            >
                <CardContent class="p-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-muted">
                            <component :is="queueIcon(name)" class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-muted-foreground truncate">
                                {{ queueLabel(name) }}
                            </p>
                            <p class="text-lg font-semibold tabular-nums">
                                {{ pendingByQueue[name] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <ListTodo class="h-4 w-4 shrink-0" />
            <span>Total pending: <strong class="text-foreground">{{ totalPending }}</strong></span>
        </div>

        <!-- Failed jobs -->
        <Card>
            <CardHeader class="border-b p-3 flex flex-row items-center justify-between gap-2">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-destructive/80 rounded" />
                    Failed Jobs
                </CardTitle>
                <div class="flex items-center gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.queue ? queueLabel(filters.queue) : 'All queues' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-[160px]">
                            <DropdownMenuRadioGroup :model-value="filters.queue || 'all'" @update:model-value="(v) => applyFilter('queue', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All queues</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem v-for="q in queueNames" :key="q" :value="q" class="text-xs">
                                    {{ queueLabel(q) }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button
                        size="sm"
                        variant="secondary"
                        class="h-8 text-xs gap-1"
                        :disabled="!failedJobs.data?.length"
                        @click="retryAll"
                    >
                        <RotateCw class="h-3.5 w-3.5" />
                        Retry all
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Queue</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Failed at</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Exception</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="job in failedJobs.data" :key="job.uuid" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-[10px]">
                                        {{ job.queue }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ formatDate(job.failed_at) }}
                                </td>
                                <td class="px-3 py-2 max-w-[320px]">
                                    <span class="text-xs text-destructive" :title="job.exception">
                                        {{ exceptionPreview(job.exception) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button size="sm" variant="ghost" class="h-7 text-xs gap-1" @click="retry(job.uuid)">
                                            <RotateCw class="h-3 w-3" />
                                            Retry
                                        </Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button size="sm" variant="ghost" class="h-7 text-xs text-destructive hover:text-destructive gap-1">
                                                    <Trash2 class="h-3 w-3" />
                                                    Delete
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Remove failed job?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This will permanently remove the job from the failed list. It will not be retried.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction class="bg-destructive text-destructive-foreground hover:bg-destructive/90" @click="forget(job.uuid)">
                                                        Delete
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!failedJobs.data?.length">
                                <td colspan="4" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center">
                                            <AlertCircle class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <p class="text-sm font-medium">No failed jobs</p>
                                        <p class="text-xs text-muted-foreground">Failed jobs will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="failedJobs.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in failedJobs.links" :key="index">
                        <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm" class="h-7 text-xs" as-child>
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <Button v-else variant="outline" size="sm" class="h-7 text-xs" disabled v-html="link.label" />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
