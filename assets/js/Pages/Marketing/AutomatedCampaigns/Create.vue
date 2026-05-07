<script setup>
import { ref, shallowRef, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, Loader2, Zap, ChevronDown, MessageSquare, Users, Search, ChevronLeft, ChevronRight, MessageCircle, Clock, Gauge, Check } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    trigger_type: 'birthday',
    channel: 'sms',
    message_template: '',
    trigger_conditions: {},
    is_active: true,
    metadata: {
        target_lead_ids: [],
        send_time_window: { start: '', end: '' },
        throttle: { max_per_hour: '' },
    },
});

const triggerTypes = [
    { value: 'birthday', label: 'Birthday Wish' },
    { value: 'passport_expiry', label: 'Passport Expiry Alert' },
    { value: 'visa_status_change', label: 'Visa Status Change' },
    { value: 'custom', label: 'Custom' },
];

const showDaysInput = ref(false);

const onTriggerTypeChange = (value) => {
    form.trigger_type = value;
    if (value === 'passport_expiry') {
        showDaysInput.value = true;
        form.trigger_conditions = { days_before_expiry: 30 };
    } else if (value === 'visa_status_change') {
        showDaysInput.value = false;
        form.trigger_conditions = { visa_statuses: ['approved', 'rejected'] };
    } else {
        showDaysInput.value = false;
        form.trigger_conditions = {};
    }
};

// Lead selection (reuse campaigns/leads API)
const leads = shallowRef([]);
const leadsPagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const loadingLeads = ref(false);
const loadingSelectAll = ref(false);
const leadFilters = ref({ search: '', status: '', package_id: '', service_id: '' });

const targetLeadIds = computed({
    get: () => form.metadata?.target_lead_ids ?? [],
    set: (val) => {
        form.metadata = { ...form.metadata, target_lead_ids: val };
    },
});

function fetchLeads(page = 1) {
    loadingLeads.value = true;
    const params = new URLSearchParams({
        page,
        per_page: 15,
        ...(leadFilters.value.search && { search: leadFilters.value.search }),
        ...(leadFilters.value.status && { status: leadFilters.value.status }),
        ...(leadFilters.value.package_id && { package_id: leadFilters.value.package_id }),
        ...(leadFilters.value.service_id && { service_id: leadFilters.value.service_id }),
    });
    axios.get(route('marketing.campaigns.leads') + '?' + params.toString())
        .then((res) => {
            leads.value = res.data.data ?? [];
            leadsPagination.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                per_page: res.data.per_page,
                total: res.data.total,
            };
        })
        .finally(() => { loadingLeads.value = false; });
}

function applyLeadFilters() {
    fetchLeads(1);
}

const selectedLeadIdsSet = computed(() => new Set(targetLeadIds.value));

function isLeadSelected(id) {
    return selectedLeadIdsSet.value.has(id);
}

function toggleLead(id) {
    const set = new Set(targetLeadIds.value);
    if (set.has(id)) set.delete(id);
    else set.add(id);
    targetLeadIds.value = Array.from(set);
}

const pageLeadIds = computed(() => leads.value.map((l) => l.id));
const allOnPageSelected = computed(() => pageLeadIds.value.length > 0 && pageLeadIds.value.every((id) => selectedLeadIdsSet.value.has(id)));

function toggleSelectAllOnPage() {
    const set = new Set(targetLeadIds.value);
    if (allOnPageSelected.value) pageLeadIds.value.forEach((id) => set.delete(id));
    else pageLeadIds.value.forEach((id) => set.add(id));
    targetLeadIds.value = Array.from(set);
}

function clearLeadSelection() {
    targetLeadIds.value = [];
}

function fetchAllLeadIds() {
    loadingSelectAll.value = true;
    const params = new URLSearchParams({
        ids_only: 1,
        max_ids: 5000,
        ...(leadFilters.value.search && { search: leadFilters.value.search }),
        ...(leadFilters.value.status && { status: leadFilters.value.status }),
        ...(leadFilters.value.package_id && { package_id: leadFilters.value.package_id }),
        ...(leadFilters.value.service_id && { service_id: leadFilters.value.service_id }),
    });
    axios.get(route('marketing.campaigns.leads') + '?' + params.toString())
        .then((res) => {
            targetLeadIds.value = res.data.ids ?? [];
        })
        .finally(() => { loadingSelectAll.value = false; });
}

onMounted(() => {
    fetchLeads(1);
});

const submit = () => {
    form.transform((data) => {
        const meta = { ...data.metadata };
        if (meta.send_time_window && (!meta.send_time_window.start || !meta.send_time_window.end)) {
            meta.send_time_window = null;
        } else if (meta.send_time_window) {
            meta.send_time_window = { start: meta.send_time_window.start, end: meta.send_time_window.end };
        }
        const maxPerHour = meta.throttle?.max_per_hour;
        meta.throttle = maxPerHour ? { max_per_hour: Number(maxPerHour) } : null;
        return { ...data, metadata: meta };
    });
    form.post(route('marketing.automated-campaigns.store'));
};
</script>

<template>
    <Head title="Create Automated Campaign" />

    <div class="w-full space-y-6">
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.automated-campaigns.index')"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 shrink-0"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create Automated Campaign</h1>
                <p class="text-xs text-muted-foreground">Messages that send automatically based on triggers</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Campaign configuration -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <Zap class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Campaign configuration</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Trigger, channel and message template</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-5">
                    <div class="space-y-2">
                        <Label for="name" class="text-xs font-medium">Campaign name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. Birthday wishes" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-sm resize-none min-h-20 rounded-lg border-border/50" placeholder="Internal note about this automation" />
                        <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Trigger type <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50">
                                        <span>{{ triggerTypes.find(t => t.value === form.trigger_type)?.label || 'Select trigger' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup :model-value="form.trigger_type" @update:model-value="onTriggerTypeChange">
                                        <DropdownMenuRadioItem v-for="type in triggerTypes" :key="type.value" :value="type.value" class="text-sm">{{ type.label }}</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.trigger_type" class="text-xs text-red-500">{{ form.errors.trigger_type }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Channel <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50">
                                        <span>{{ form.channel === 'sms' ? 'SMS' : form.channel === 'email' ? 'Email' : form.channel === 'whatsapp' ? 'WhatsApp' : 'Select channel' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.channel">
                                        <DropdownMenuRadioItem value="sms" class="text-sm">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-sm">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-sm">WhatsApp</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">WhatsApp is sent via WasenderAPI.</p>
                            <p v-if="form.errors.channel" class="text-xs text-red-500">{{ form.errors.channel }}</p>
                        </div>
                    </div>
                    <div v-if="showDaysInput" class="rounded-lg border border-border/50 bg-muted/20 dark:bg-muted/10 p-4 space-y-2">
                        <Label for="days_before_expiry" class="text-xs font-medium">Days before expiry</Label>
                        <Input id="days_before_expiry" v-model.number="form.trigger_conditions.days_before_expiry" type="number" min="1" max="365" class="h-10 text-sm rounded-lg border-border/50 max-w-[140px]" />
                        <p class="text-xs text-muted-foreground">Send alert this many days before passport expires</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="message_template" class="text-xs font-medium flex items-center gap-1.5">
                            <MessageSquare class="h-3.5 w-3.5" />
                            Message template <span class="text-red-500">*</span>
                        </Label>
                        <Textarea id="message_template" v-model="form.message_template" rows="5" class="text-sm resize-y min-h-32 font-mono rounded-lg border-border/50" placeholder="e.g. Happy birthday {{first_name}}! Use {{name}}, {{first_name}}, {{phone}}, {{email}}" />
                        <p class="text-xs text-muted-foreground">
                            Variables: {{name}}, {{first_name}}, {{phone}}, {{email}}
                            <span v-if="form.trigger_type === 'passport_expiry'"> · {{passport_number}}, {{expiry_date}}, {{days_until_expiry}}</span>
                            <span v-if="form.trigger_type === 'visa_status_change'"> · {{visa_status}}, {{old_status}}, {{new_status}}</span>
                        </p>
                        <p v-if="form.errors.message_template" class="text-xs text-red-500">{{ form.errors.message_template }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Target leads -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <CardTitle class="text-sm font-semibold tracking-tight">Target leads</CardTitle>
                                <p class="text-xs text-muted-foreground mt-0.5">Who receives this automation. Leave empty for all leads.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div v-if="targetLeadIds.length > 0" class="flex items-center gap-2 rounded-full bg-primary/10 dark:bg-primary/20 px-3 py-1.5 ring-1 ring-primary/20">
                                <MessageCircle class="h-3.5 w-3.5 text-primary shrink-0" />
                                <span class="text-xs font-medium text-primary">{{ targetLeadIds.length }} selected</span>
                            </div>
                            <Button v-if="targetLeadIds.length > 0" type="button" variant="ghost" size="sm" class="h-8 text-xs text-muted-foreground hover:text-destructive" @click="clearLeadSelection">Clear all</Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap rounded-xl bg-muted/30 dark:bg-muted/10 border border-border/50 p-3 gap-3">
                        <div class="relative flex-1 min-w-[200px]">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                            <Input v-model="leadFilters.search" class="h-9 text-xs pl-9 bg-background/80 dark:bg-background/50 border-border/50 rounded-lg focus:ring-2 focus:ring-primary/20" placeholder="Search by name, email or phone..." @keydown.enter="applyLeadFilters" />
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <select v-model="leadFilters.status" class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                                <option value="">All statuses</option>
                                <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <select v-model="leadFilters.package_id" class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                                <option value="">All packages</option>
                                <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                            </select>
                            <select v-model="leadFilters.service_id" class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                                <option value="">All services</option>
                                <option v-for="srv in services" :key="srv.id" :value="srv.id">{{ srv.name }}</option>
                            </select>
                            <Button type="button" size="sm" class="h-9 text-xs gap-1.5 shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm" :disabled="loadingLeads" @click="applyLeadFilters">
                                <Zap v-if="!loadingLeads" class="h-3.5 w-3.5" />
                                <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                                Apply
                            </Button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between rounded-lg bg-muted/20 dark:bg-muted/10 border border-border/50 px-3 py-2.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-muted-foreground">Select:</span>
                            <Button type="button" variant="outline" size="sm" class="h-8 text-xs gap-1.5 rounded-lg" :disabled="leads.length === 0" @click="toggleSelectAllOnPage">
                                <span class="inline-flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border transition-colors" :class="allOnPageSelected ? 'border-primary bg-primary text-primary-foreground' : pageLeadIds.some(id => selectedLeadIdsSet.has(id)) ? 'border-primary bg-primary/20' : 'border-input'">
                                    <Check v-if="allOnPageSelected" class="h-2.5 w-2.5" />
                                </span>
                                All on this page ({{ leads.length }})
                            </Button>
                            <Button type="button" variant="outline" size="sm" class="h-8 text-xs gap-1.5 rounded-lg" :disabled="leadsPagination.total === 0 || loadingSelectAll" @click="fetchAllLeadIds">
                                <Loader2 v-if="loadingSelectAll" class="h-3.5 w-3.5 animate-spin" />
                                <Users v-else class="h-3.5 w-3.5" />
                                All {{ leadsPagination.total }} leads
                            </Button>
                        </div>
                        <p class="text-[10px] text-muted-foreground sm:max-w-[220px]">Selection is kept when you change pages.</p>
                    </div>
                    <div class="rounded-xl border overflow-hidden transition-all duration-200" :class="targetLeadIds.length > 0 ? 'border-primary/40 ring-1 ring-primary/20' : 'border-border/50'">
                        <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead class="sticky top-0 z-10 bg-muted/95 dark:bg-muted/40 backdrop-blur-sm border-b border-border/50">
                                    <tr>
                                        <th class="text-left p-3 w-12 align-middle bg-muted/50 dark:bg-muted/20">
                                            <Checkbox :checked="allOnPageSelected" :indeterminate="pageLeadIds.length > 0 && pageLeadIds.some(id => selectedLeadIdsSet.has(id)) && !allOnPageSelected" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="toggleSelectAllOnPage" />
                                        </th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Name</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Email</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Phone</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loadingLeads">
                                        <td colspan="5" class="p-10 text-center">
                                            <div class="flex flex-col items-center gap-3 text-muted-foreground">
                                                <div class="h-9 w-9 rounded-full border-2 border-primary/30 border-t-primary animate-spin" />
                                                <span class="text-xs">Loading leads...</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="leads.length === 0">
                                        <td colspan="5" class="p-10 text-center">
                                            <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                                <Users class="h-9 w-9 opacity-50" />
                                                <span class="text-xs">No leads found</span>
                                                <span class="text-[10px]">Try different filters or search</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="(lead, index) in leads" :key="lead.id" class="border-b border-border/50 last:border-b-0 transition-colors duration-150" :class="[index % 2 === 1 ? 'bg-muted/20 dark:bg-muted/5' : '', isLeadSelected(lead.id) ? 'bg-primary/5 dark:bg-primary/10 border-l-2 border-l-primary' : 'hover:bg-muted/30 dark:hover:bg-muted/15']">
                                        <td class="p-3 align-middle bg-muted/30 dark:bg-muted/10 w-12">
                                            <Checkbox :checked="isLeadSelected(lead.id)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="toggleLead(lead.id)" />
                                        </td>
                                        <td class="p-3 font-medium">{{ lead.full_name || '—' }}</td>
                                        <td class="p-3 truncate max-w-[200px] text-muted-foreground" :title="lead.email">{{ lead.email || '—' }}</td>
                                        <td class="p-3 font-mono text-[11px] text-muted-foreground">{{ lead.phone || '—' }}</td>
                                        <td class="p-3">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="isLeadSelected(lead.id) ? 'bg-primary/20 text-primary ring-1 ring-primary/30' : 'bg-muted/80 dark:bg-muted/50 text-muted-foreground'">{{ lead.status || '—' }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex flex-col gap-2 xs:flex-row xs:items-center xs:justify-between border-t border-border/50 px-4 py-2.5 bg-muted/20 dark:bg-muted/10">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span class="truncate">Page {{ leadsPagination.current_page }} of {{ leadsPagination.last_page }} · {{ leadsPagination.total }} leads</span>
                                <span v-if="targetLeadIds.length > 0" class="font-medium text-primary">{{ targetLeadIds.length }} selected</span>
                            </div>
                            <div v-if="leadsPagination.last_page > 1" class="flex gap-1 shrink-0">
                                <Button type="button" variant="outline" size="sm" class="h-8 w-8 p-0 rounded-lg border-border/50" :disabled="leadsPagination.current_page <= 1 || loadingLeads" @click="fetchLeads(leadsPagination.current_page - 1)">
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <Button type="button" variant="outline" size="sm" class="h-8 w-8 p-0 rounded-lg border-border/50" :disabled="leadsPagination.current_page >= leadsPagination.last_page || loadingLeads" @click="fetchLeads(leadsPagination.current_page + 1)">
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Send options -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <Clock class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Send options</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">When and how fast to send (optional)</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-border/50 bg-muted/20 dark:bg-muted/10 p-4 space-y-3">
                            <Label class="text-xs font-medium flex items-center gap-2 text-foreground">
                                <Clock class="h-4 w-4 text-primary/80" />
                                Send only during these hours
                            </Label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <Input v-model="form.metadata.send_time_window.start" type="time" class="h-10 text-sm rounded-lg border-border/50 w-[120px]" />
                                <span class="text-xs text-muted-foreground">to</span>
                                <Input v-model="form.metadata.send_time_window.end" type="time" class="h-10 text-sm rounded-lg border-border/50 w-[120px]" />
                            </div>
                            <p class="text-[10px] text-muted-foreground">Leave empty to send anytime. Avoids late-night messages.</p>
                        </div>
                        <div class="rounded-xl border border-border/50 bg-muted/20 dark:bg-muted/10 p-4 space-y-3">
                            <Label for="max_per_hour" class="text-xs font-medium flex items-center gap-2 text-foreground">
                                <Gauge class="h-4 w-4 text-primary/80" />
                                Max messages per hour
                            </Label>
                            <Input id="max_per_hour" v-model="form.metadata.throttle.max_per_hour" type="number" min="1" max="500" placeholder="e.g. 50" class="h-10 text-sm rounded-lg border-border/50 w-full max-w-[120px]" />
                            <p class="text-[10px] text-muted-foreground">Limits burst when many leads match the trigger.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-2">
                <Button type="button" variant="outline" size="sm" class="sm:order-first" as-child>
                    <Link :href="route('marketing.automated-campaigns.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Zap v-else class="h-3.5 w-3.5" />
                    Create automated campaign
                </Button>
            </div>
        </form>
    </div>
</template>
