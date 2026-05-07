<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { StatusBadge } from '@/Components/shared';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    ArrowLeft,
    Plus,
    Printer,
    FileDown,
    FileSpreadsheet,
    FileText,
    ChevronDown,
    User,
    Hash,
    Clock,
    SlidersHorizontal,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const page = usePage();

const props = defineProps({
    results: { type: Object, required: true },
    summary: { type: Object, required: true },
    validated: { type: Object, required: true },
    reportType: { type: String, required: true },
    reportMeta: {
        type: Object,
        default: () => ({}),
    },
});

const reportTitle = computed(() => {
    return props.reportMeta.report_title ?? `${props.reportType.charAt(0).toUpperCase() + props.reportType.slice(1)} report`;
});

const generatedAtLabel = computed(() => props.reportMeta.generated_at_display ?? '—');

const generatedByLabel = computed(() => {
    const g = props.reportMeta.generated_by;
    if (!g?.name && !g?.email) {
        return page.props.auth?.user?.name ?? '—';
    }
    if (g?.email) {
        return `${g.name} <${g.email}>`;
    }
    return g?.name ?? '—';
});

const referenceLabel = computed(() => props.reportMeta.reference ?? '—');

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US').format(num || 0);
};

const printReport = () => {
    window.print();
};

function reportDownloadUrl(format) {
    const q = new URLSearchParams();
    const validated = { ...(props.validated || {}) };
    if (format) {
        validated.format = format;
    }
    Object.entries(validated).forEach(([k, v]) => {
        if (v != null && v !== '') {
            q.set(k, String(v));
        }
    });
    const base = route('reports.download');
    const query = q.toString();
    return query ? `${base}?${query}` : base;
}

function formatStatusLabel(status) {
    return String(status)
        .split('_')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
}
</script>

<template>

    <Head :title="reportTitle" />

    <div class="space-y-6 print-content">
        <div class="flex flex-col gap-4 print-header sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="route('reports.index')"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring no-print"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ reportTitle }}
                    </h1>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex items-start gap-2 rounded-lg border bg-card px-3 py-2.5 text-sm shadow-sm">
                        <Hash class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-muted-foreground">Reference</div>
                            <div class="truncate font-mono text-xs font-semibold">{{ referenceLabel }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg border bg-card px-3 py-2.5 text-sm shadow-sm">
                        <Clock class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-muted-foreground">Generated at</div>
                            <div class="text-xs leading-snug">{{ generatedAtLabel }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg border bg-card px-3 py-2.5 text-sm shadow-sm sm:col-span-2 lg:col-span-2">
                        <User class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-muted-foreground">Generated by</div>
                            <div class="truncate text-xs">{{ generatedByLabel }}</div>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground">
                    Dataset matches your filters below. Exports include the same metadata and up to 10,000 rows per file.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 no-print">
                <Button variant="outline" @click="printReport">
                    <Printer class="mr-2 h-4 w-4" />
                    Print
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <FileDown class="mr-2 h-4 w-4" />
                            Export
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <DropdownMenuItem as-child>
                            <a
                                :href="reportDownloadUrl('pdf')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex cursor-pointer items-center"
                            >
                                <FileText class="mr-2 h-4 w-4" />
                                PDF (landscape)
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="reportDownloadUrl('excel')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex cursor-pointer items-center"
                            >
                                <FileSpreadsheet class="mr-2 h-4 w-4" />
                                Excel-compatible CSV
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="reportDownloadUrl('csv')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex cursor-pointer items-center"
                            >
                                <FileDown class="mr-2 h-4 w-4" />
                                CSV
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <a
                                :href="reportDownloadUrl('html')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex cursor-pointer items-center"
                            >
                                <FileText class="mr-2 h-4 w-4" />
                                HTML
                            </a>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button as-child>
                    <Link :href="route('reports.index')">
                        <Plus class="mr-2 h-4 w-4" />
                        New report
                    </Link>
                </Button>
            </div>
        </div>

        <Card v-if="reportMeta.criteria?.length" class="print-avoid-break no-print">
            <CardHeader class="pb-2">
                <div class="flex items-center gap-2">
                    <SlidersHorizontal class="h-4 w-4 text-muted-foreground" />
                    <CardTitle class="text-sm">Filters applied</CardTitle>
                </div>
            </CardHeader>
            <CardContent>
                <dl class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(row, idx) in reportMeta.criteria"
                        :key="idx"
                        class="rounded-md border bg-muted/30 px-3 py-2 text-sm"
                    >
                        <dt class="text-xs font-medium text-muted-foreground">{{ row.label }}</dt>
                        <dd class="font-medium leading-snug">{{ row.value }}</dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <Card class="print-avoid-break">
            <CardHeader>
                <CardTitle class="text-sm">Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Total leads
                        </div>
                        <div class="text-3xl font-bold">{{ formatNumber(summary.total) }}</div>
                    </div>
                </div>

                <div v-if="summary.by_status && Object.keys(summary.by_status).length">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">By status</h4>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="[statusName, countOrObj] in Object.entries(summary.by_status)"
                            :key="statusName"
                            variant="secondary"
                            class="text-xs"
                        >
                            {{ formatStatusLabel(statusName) }}:
                            {{ formatNumber(typeof countOrObj === 'object' ? countOrObj.count : countOrObj) }}
                        </Badge>
                    </div>
                </div>

                <div v-if="summary.by_priority && Object.keys(summary.by_priority).length">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">By priority</h4>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="[p, count] in Object.entries(summary.by_priority)"
                            :key="p"
                            variant="outline"
                            class="text-xs capitalize"
                        >
                            {{ p }}: {{ formatNumber(count) }}
                        </Badge>
                    </div>
                </div>

                <div v-if="summary.by_branch && Object.keys(summary.by_branch).length">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">By branch</h4>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="(count, branch) in summary.by_branch"
                            :key="branch"
                            variant="secondary"
                            class="text-xs"
                        >
                            {{ branch }}: {{ formatNumber(count) }}
                        </Badge>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card class="print-avoid-break">
            <CardHeader>
                <CardTitle class="text-sm">
                    Results ({{ formatNumber(results.total) }} {{ results.total === 1 ? 'record' : 'records' }} total)
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="results.data.length > 0" class="overflow-x-auto">
                    <table v-if="reportType === 'leads'" class="w-full min-w-[1000px] text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2 text-left text-xs font-semibold uppercase">ID</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Name</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Email</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Phone</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Country</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Destination</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Branch</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Assigned</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Service</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Package</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Priority</th>
                                <th class="p-2 text-left text-xs font-semibold uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="lead in results.data"
                                :key="lead.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-2 font-mono text-xs text-muted-foreground">{{ lead.id }}</td>
                                <td class="p-2">
                                    <Link
                                        :href="route('leads.show', lead.id)"
                                        class="font-semibold text-primary no-print hover:underline"
                                    >
                                        {{ lead.full_name }}
                                    </Link>
                                    <span class="print-only font-semibold">{{ lead.full_name }}</span>
                                </td>
                                <td class="p-2 text-muted-foreground">{{ lead.email || 'N/A' }}</td>
                                <td class="p-2 text-muted-foreground">{{ lead.phone || 'N/A' }}</td>
                                <td class="p-2 text-muted-foreground">{{ lead.country || '—' }}</td>
                                <td class="p-2 text-muted-foreground">{{ lead.destination_country || '—' }}</td>
                                <td class="p-2">
                                    <Badge variant="secondary" class="text-xs">
                                        {{ lead.branch?.name || 'N/A' }}
                                    </Badge>
                                </td>
                                <td class="p-2 text-muted-foreground">{{ lead.assigned_user?.name ?? '—' }}</td>
                                <td class="p-2 text-muted-foreground">{{ lead.selected_service?.name ?? '—' }}</td>
                                <td class="p-2 text-muted-foreground">{{ lead.selected_package?.name ?? '—' }}</td>
                                <td class="p-2">
                                    <StatusBadge v-if="lead.status" :status="lead.status" />
                                </td>
                                <td class="p-2">
                                    <Badge
                                        v-if="lead.priority"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-200': lead.priority === 'urgent',
                                            'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-200': lead.priority === 'high',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200': lead.priority === 'normal',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': lead.priority === 'low',
                                        }"
                                        class="text-xs capitalize"
                                    >
                                        {{ lead.priority }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="p-2 text-muted-foreground">{{ formatDate(lead.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-12 text-center">
                    <p class="text-muted-foreground">No results found.</p>
                </div>

                <div
                    v-if="results.links && results.links.length > 3"
                    class="mt-4 flex items-center justify-center no-print"
                >
                    <div class="flex gap-1">
                        <Link
                            v-for="link in results.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'rounded-md px-3 py-1.5 text-xs',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted hover:bg-muted/80',
                                !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }

    .print-only {
        display: inline !important;
    }

    .print-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

.print-only {
    display: none;
}
</style>
