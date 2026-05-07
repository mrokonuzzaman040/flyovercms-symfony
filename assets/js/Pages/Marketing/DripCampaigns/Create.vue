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
import { ArrowLeft, Plus, Trash2, ChevronDown, FileText, Users, Search, ChevronLeft, ChevronRight, MessageCircle, Check, Layers, Loader2, Zap } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    lead_ids: [],
    steps: [
        {
            name: '',
            description: '',
            channel: 'sms',
            subject: '',
            message: '',
            html_content: '',
            delay_days: 0,
            delay_hours: 0,
            send_time: null,
            timezone: 'UTC',
            is_active: true,
        },
    ],
});

const addStep = () => {
    form.steps.push({
        name: '',
        description: '',
        channel: 'sms',
        subject: '',
        message: '',
        html_content: '',
        delay_days: 0,
        delay_hours: 0,
        send_time: null,
        timezone: 'UTC',
        is_active: true,
    });
};

const removeStep = (index) => {
    form.steps.splice(index, 1);
};

// Lead selection (reuse marketing.campaigns.leads)
const leads = shallowRef([]);
const leadsPagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const loadingLeads = ref(false);
const loadingSelectAll = ref(false);
const leadFilters = ref({ search: '', status: '', package_id: '', service_id: '' });

const selectedLeadIdsSet = computed(() => new Set(form.lead_ids));

function isLeadSelected(id) {
    return selectedLeadIdsSet.value.has(id);
}

function toggleLead(id) {
    const set = new Set(form.lead_ids);
    if (set.has(id)) set.delete(id);
    else set.add(id);
    form.lead_ids = Array.from(set);
}

const pageLeadIds = computed(() => leads.value.map((l) => l.id));
const allOnPageSelected = computed(() => pageLeadIds.value.length > 0 && pageLeadIds.value.every((id) => selectedLeadIdsSet.value.has(id)));

function toggleSelectAllOnPage() {
    const set = new Set(form.lead_ids);
    if (allOnPageSelected.value) pageLeadIds.value.forEach((id) => set.delete(id));
    else pageLeadIds.value.forEach((id) => set.add(id));
    form.lead_ids = Array.from(set);
}

function clearLeadSelection() {
    form.lead_ids = [];
}

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
            form.lead_ids = res.data.ids ?? [];
        })
        .finally(() => { loadingSelectAll.value = false; });
}

onMounted(() => {
    fetchLeads(1);
});

const submit = () => {
    form.post(route('marketing.drip-campaigns.store'));
};
</script>

<template>
    <Head title="Create Drip Campaign" />

    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.drip-campaigns.index')"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 shrink-0"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create Drip Campaign</h1>
                <p class="text-xs text-muted-foreground">Multi-step sequence: enroll leads and send messages over time. WhatsApp steps are sent via WasenderAPI.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Campaign Information -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <FileText class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Campaign information</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Name and description</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-xs font-medium">Campaign name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. Welcome drip" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-sm resize-none min-h-20 rounded-lg border-border/50" placeholder="Internal note" />
                    </div>
                </CardContent>
            </Card>

            <!-- Select leads (optional – enroll on create) -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <CardTitle class="text-sm font-semibold tracking-tight">Enroll leads now</CardTitle>
                                <p class="text-xs text-muted-foreground mt-0.5">Optionally select leads to enroll as soon as the campaign is created</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div v-if="form.lead_ids.length > 0" class="flex items-center gap-2 rounded-full bg-primary/10 dark:bg-primary/20 px-3 py-1.5 ring-1 ring-primary/20">
                                <MessageCircle class="h-3.5 w-3.5 text-primary shrink-0" />
                                <span class="text-xs font-medium text-primary">{{ form.lead_ids.length }} selected</span>
                            </div>
                            <Button v-if="form.lead_ids.length > 0" type="button" variant="ghost" size="sm" class="h-8 text-xs text-muted-foreground hover:text-destructive" @click="clearLeadSelection">Clear all</Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap rounded-xl bg-muted/30 dark:bg-muted/10 border border-border/50 p-3">
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
                    <div class="rounded-xl border overflow-hidden transition-all duration-200" :class="form.lead_ids.length > 0 ? 'border-primary/40 ring-1 ring-primary/20' : 'border-border/50'">
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
                                <span v-if="form.lead_ids.length > 0" class="font-medium text-primary">{{ form.lead_ids.length }} selected</span>
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

            <!-- Drip steps -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <Layers class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Drip steps</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Messages sent in order with optional delay between steps</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div v-for="(step, index) in form.steps" :key="index" class="rounded-xl border border-border/50 bg-muted/10 dark:bg-muted/5 p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Step {{ index + 1 }}</span>
                            <Button v-if="form.steps.length > 1" type="button" variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-destructive" @click="removeStep(index)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label :for="`step-${index}-name`" class="text-xs font-medium">Step name <span class="text-red-500">*</span></Label>
                                <Input :id="`step-${index}-name`" v-model="step.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. Welcome message" required />
                            </div>
                            <div class="space-y-2">
                                <Label :for="`step-${index}-channel`" class="text-xs font-medium">Channel <span class="text-red-500">*</span></Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50">
                                            <span class="uppercase">{{ step.channel }}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup :model-value="step.channel" @update:model-value="(v) => step.channel = v">
                                            <DropdownMenuRadioItem value="sms" class="text-sm">SMS</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="email" class="text-sm">Email</DropdownMenuRadioItem>
                                            <DropdownMenuRadioItem value="whatsapp" class="text-sm">WhatsApp</DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label :for="`step-${index}-message`" class="text-xs font-medium">Message <span class="text-red-500">*</span></Label>
                            <Textarea :id="`step-${index}-message`" v-model="step.message" class="text-sm rounded-lg border-border/50 min-h-24 resize-y" rows="4" placeholder="Use {{name}}, {{first_name}}, {{phone}}, {{email}}" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4 max-w-xs">
                            <div class="space-y-2">
                                <Label :for="`step-${index}-delay-days`" class="text-xs font-medium">Delay (days)</Label>
                                <Input :id="`step-${index}-delay-days`" v-model.number="step.delay_days" type="number" min="0" class="h-10 text-sm rounded-lg border-border/50" />
                            </div>
                            <div class="space-y-2">
                                <Label :for="`step-${index}-delay-hours`" class="text-xs font-medium">Delay (hours)</Label>
                                <Input :id="`step-${index}-delay-hours`" v-model.number="step.delay_hours" type="number" min="0" max="23" class="h-10 text-sm rounded-lg border-border/50" />
                            </div>
                        </div>
                    </div>
                    <Button type="button" variant="outline" size="sm" class="gap-1.5 rounded-lg" @click="addStep">
                        <Plus class="h-3.5 w-3.5" />
                        Add step
                    </Button>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.drip-campaigns.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" class="shadow-sm" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create drip campaign' }}
                </Button>
            </div>
        </form>
    </div>
</template>
