<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    FileText,
    X,
    Calendar,
    Filter,
    Loader2,
    ChevronDown,
    Sparkles,
    BarChart3,
    Clock,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const page = usePage();

const props = defineProps({
    branches: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    destinationCountries: { type: Array, default: () => [] },
});

const appName = computed(() => page.props.appName ?? 'Reports');

const form = useForm({
    report_type: '',
    date_from: '',
    date_to: '',
    branch_id: '',
    service_id: '',
    package_id: '',
    country: '',
    destination_country: '',
    status: '',
    priority: '',
    assigned_to: '',
});

const showLeadFields = ref(false);

watch(() => form.report_type, (newValue) => {
    showLeadFields.value = newValue === 'leads';
    if (!showLeadFields.value) {
        form.status = '';
        form.priority = '';
    }
});

function formatDateLocal(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function setDatePreset(preset) {
    const end = new Date();
    const start = new Date();

    if (preset === 'today') {
        form.date_from = formatDateLocal(start);
        form.date_to = formatDateLocal(end);
        return;
    }
    if (preset === 'last7') {
        start.setDate(end.getDate() - 6);
        form.date_from = formatDateLocal(start);
        form.date_to = formatDateLocal(end);
        return;
    }
    if (preset === 'last30') {
        start.setDate(end.getDate() - 29);
        form.date_from = formatDateLocal(start);
        form.date_to = formatDateLocal(end);
        return;
    }
    if (preset === 'thisMonth') {
        start.setDate(1);
        form.date_from = formatDateLocal(start);
        form.date_to = formatDateLocal(end);
        return;
    }
    if (preset === 'clear') {
        form.date_from = '';
        form.date_to = '';
    }
}

const reportTypeDisplay = computed(() => {
    const types = {
        '': 'Select report type',
        leads: 'Leads report',
    };
    return types[form.report_type] || 'Select report type';
});

const branchDisplay = computed(() => {
    if (!form.branch_id) return 'All branches';
    const branch = props.branches.find(b => String(b.id) === form.branch_id);
    return branch ? branch.name : 'All branches';
});

const serviceDisplay = computed(() => {
    if (!form.service_id) return 'All services';
    const service = props.services.find(s => String(s.id) === form.service_id);
    return service ? service.name : 'All services';
});

const packageDisplay = computed(() => {
    if (!form.package_id) return 'All packages';
    const pkg = props.packages.find(p => String(p.id) === form.package_id);
    return pkg ? pkg.name : 'All packages';
});

const countryDisplay = computed(() => {
    return form.country || 'All countries';
});

const destinationCountryDisplay = computed(() => {
    return form.destination_country || 'All destination countries';
});

const statusDisplay = computed(() => {
    const statuses = {
        '': 'All statuses',
        new: 'New',
        contacted: 'Contacted',
        qualified: 'Qualified',
        converted: 'Converted',
        cancelled: 'Cancelled',
    };
    return statuses[form.status] || 'All statuses';
});

const priorityDisplay = computed(() => {
    const priorities = {
        '': 'All priorities',
        low: 'Low',
        normal: 'Normal',
        high: 'High',
        urgent: 'Urgent',
    };
    return priorities[form.priority] || 'All priorities';
});

const assignedToDisplay = computed(() => {
    if (!form.assigned_to) return 'All users';
    const user = props.users.find(u => String(u.id) === form.assigned_to);
    return user ? user.name : 'All users';
});

const submit = () => {
    form.post(route('reports.generate'));
};
</script>

<template>

    <Head title="Reports" />

    <div class="space-y-8">
        <div class="relative overflow-hidden rounded-xl border border-border bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 px-6 py-8 text-slate-50 shadow-sm dark:border-slate-800">
            <div class="relative z-10 max-w-3xl space-y-3">
                <div class="flex items-center gap-2 text-sm font-medium text-blue-200/90">
                    <Sparkles class="h-4 w-4 shrink-0" />
                    {{ appName }} · Analytics & exports
                </div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    Reports workspace
                </h1>
                <p class="text-sm leading-relaxed text-slate-300">
                    Build filtered lead datasets with live summaries, then export to PDF, CSV, or HTML.
                    Every export includes who generated it, when, reference ID, and the exact filters used.
                </p>
            </div>
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Filter class="h-4 w-4 text-muted-foreground" />
                        <CardTitle class="text-base">Configure report</CardTitle>
                    </div>
                    <CardDescription>
                        Choose a report type, optional date range, and filters. PDF and CSV include full column detail
                        and metadata headers.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <div class="space-y-2">
                            <Label for="report_type">
                                Report type <span class="text-primary">*</span>
                            </Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="h-10 w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.report_type }"
                                    >
                                        <span>{{ reportTypeDisplay }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.report_type">
                                        <DropdownMenuRadioItem value="">
                                            Select report type
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="leads">
                                            Leads report
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.report_type" class="text-sm text-destructive">
                                {{ form.errors.report_type }}
                            </p>
                        </div>

                        <div class="space-y-3 rounded-lg border bg-muted/30 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <Label class="flex items-center gap-1.5 text-sm font-semibold">
                                    <Calendar class="h-3.5 w-3.5" />
                                    Created date range
                                </Label>
                                <div class="flex flex-wrap gap-1.5">
                                    <Button type="button" variant="secondary" size="sm" class="h-8 text-xs" @click="setDatePreset('today')">
                                        Today
                                    </Button>
                                    <Button type="button" variant="secondary" size="sm" class="h-8 text-xs" @click="setDatePreset('last7')">
                                        Last 7 days
                                    </Button>
                                    <Button type="button" variant="secondary" size="sm" class="h-8 text-xs" @click="setDatePreset('last30')">
                                        Last 30 days
                                    </Button>
                                    <Button type="button" variant="secondary" size="sm" class="h-8 text-xs" @click="setDatePreset('thisMonth')">
                                        This month
                                    </Button>
                                    <Button type="button" variant="ghost" size="sm" class="h-8 text-xs" @click="setDatePreset('clear')">
                                        Clear
                                    </Button>
                                </div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="date_from">From</Label>
                                    <Input id="date_from" v-model="form.date_from" type="date" class="h-10" />
                                    <p v-if="form.errors.date_from" class="text-sm text-destructive">
                                        {{ form.errors.date_from }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="date_to">To</Label>
                                    <Input id="date_to" v-model="form.date_to" type="date" class="h-10" />
                                    <p v-if="form.errors.date_to" class="text-sm text-destructive">
                                        {{ form.errors.date_to }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="branch_id">Branch</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ branchDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.branch_id">
                                            <DropdownMenuRadioItem value="">All branches</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="branch in branches"
                                                :key="branch.id"
                                                :value="String(branch.id)"
                                            >
                                                {{ branch.name }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <Label for="service_id">Service</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ serviceDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.service_id">
                                            <DropdownMenuRadioItem value="">All services</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="service in services"
                                                :key="service.id"
                                                :value="String(service.id)"
                                            >
                                                {{ service.name }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <Label for="package_id">Package</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ packageDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.package_id">
                                            <DropdownMenuRadioItem value="">All packages</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="pkg in packages"
                                                :key="pkg.id"
                                                :value="String(pkg.id)"
                                            >
                                                {{ pkg.name }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <Label for="country">Country</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ countryDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.country">
                                            <DropdownMenuRadioItem value="">All countries</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="country in countries"
                                                :key="country"
                                                :value="country"
                                            >
                                                {{ country }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <Label for="destination_country">Destination country</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ destinationCountryDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.destination_country">
                                            <DropdownMenuRadioItem value="">
                                                All destination countries
                                            </DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="destCountry in destinationCountries"
                                                :key="destCountry"
                                                :value="destCountry"
                                            >
                                                {{ destCountry }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div v-if="showLeadFields" class="space-y-2">
                                <Label for="status">Status</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span>{{ statusDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.status">
                                            <DropdownMenuRadioItem value="">All statuses</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="new">New</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="contacted">Contacted</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="qualified">Qualified</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="converted">Converted</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="cancelled">Cancelled</DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div v-if="showLeadFields" class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span>{{ priorityDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.priority">
                                            <DropdownMenuRadioItem value="">All priorities</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="low">Low</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="normal">Normal</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="high">High</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="urgent">Urgent</DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="space-y-2">
                                <Label for="assigned_to">Assigned to</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="h-10 w-full justify-between">
                                            <span class="truncate">{{ assignedToDisplay }}</span>
                                            <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="max-h-64 w-[var(--radix-dropdown-menu-trigger-width)] overflow-y-auto">
                                        <DropdownMenuRadioGroup v-model="form.assigned_to">
                                            <DropdownMenuRadioItem value="">All users</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem
                                                v-for="user in users"
                                                :key="user.id"
                                                :value="String(user.id)"
                                            >
                                                {{ user.name }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 border-t pt-4">
                            <Button variant="outline" as-child>
                                <Link :href="route('dashboard')">
                                    <X class="mr-2 h-4 w-4" />
                                    Cancel
                                </Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <FileText v-else class="mr-2 h-4 w-4" />
                                Generate report
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-4 w-4 text-muted-foreground" />
                            <CardTitle class="text-sm">What you get</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <p class="flex gap-2">
                            <Badge variant="secondary" class="shrink-0">Summary</Badge>
                            Totals, breakdown by status, priority, and branch for the same filter set as the table.
                        </p>
                        <p class="flex gap-2">
                            <Badge variant="secondary" class="shrink-0">Exports</Badge>
                            PDF and HTML mirror the layout; CSV includes a preamble with reference, author, and filters.
                        </p>
                        <p class="flex gap-2">
                            <Clock class="h-4 w-4 shrink-0 text-muted-foreground" />
                            Timestamps use the application timezone from server configuration.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
