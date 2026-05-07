<script setup>
import { ref, shallowRef, watch, computed, onMounted } from 'vue';
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
import { ArrowLeft, Loader2, Send, ChevronDown, Sparkles, HelpCircle, Copy, Check, Users, Search, ChevronLeft, ChevronRight, MessageCircle, Zap, FileText, MessageSquare } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    type: 'sms',
    message: '',
    scheduled_at: '',
    package_id: null,
    service_id: null,
    target_criteria: {},
    lead_ids: [],
});

const leads = shallowRef([]);
const leadsPagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const loadingLeads = ref(false);
const loadingSelectAll = ref(false);
const leadFilters = ref({
    search: '',
    status: '',
    package_id: '',
    service_id: '',
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
        .finally(() => {
            loadingLeads.value = false;
        });
}

function applyLeadFilters() {
    fetchLeads(1);
}

const selectedLeadIdsSet = computed(() => new Set(form.lead_ids));

function isLeadSelected(id) {
    return selectedLeadIdsSet.value.has(id);
}

function toggleLead(id) {
    const set = new Set(form.lead_ids);
    if (set.has(id)) {
        set.delete(id);
    } else {
        set.add(id);
    }
    form.lead_ids = Array.from(set);
}

const pageLeadIds = computed(() => leads.value.map((l) => l.id));

const allOnPageSelected = computed(() => pageLeadIds.value.length > 0 && pageLeadIds.value.every((id) => selectedLeadIdsSet.value.has(id)));

function toggleSelectAllOnPage() {
    const set = new Set(form.lead_ids);
    if (allOnPageSelected.value) {
        pageLeadIds.value.forEach((id) => set.delete(id));
    } else {
        pageLeadIds.value.forEach((id) => set.add(id));
    }
    form.lead_ids = Array.from(set);
}

function clearLeadSelection() {
    form.lead_ids = [];
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
            const ids = res.data.ids ?? [];
            form.lead_ids = ids;
        })
        .finally(() => {
            loadingSelectAll.value = false;
        });
}

onMounted(() => {
    fetchLeads(1);
});

// Message templates organized by channel
const messageTemplates = {
    sms: [
        {
            id: 'welcome',
            name: 'Welcome Message',
            message: 'Hello {{name}}, welcome to Flyover Consultancy! Thank you for your interest in our services. We\'re here to help you with your visa and immigration needs.',
        },
        {
            id: 'followup',
            name: 'Follow-up Message',
            message: 'Hi {{first_name}}, this is a friendly follow-up from Flyover Consultancy. We wanted to check if you have any questions about our services. Feel free to reach us at {{phone}}.',
        },
        {
            id: 'service_promotion',
            name: 'Service Promotion',
            message: 'Dear {{name}}, discover our latest visa and immigration services! Get expert guidance for your journey. Contact us today at {{phone}} or visit our office.',
        },
        {
            id: 'document_reminder',
            name: 'Document Reminder',
            message: 'Hello {{first_name}}, this is a reminder to submit your required documents for your application. Please contact us if you need assistance.',
        },
        {
            id: 'appointment_reminder',
            name: 'Appointment Reminder',
            message: 'Hi {{name}}, reminder: You have an appointment with us. If you need to reschedule, please contact us at {{phone}}. We look forward to meeting you!',
        },
        {
            id: 'thank_you',
            name: 'Thank You Message',
            message: 'Thank you {{first_name}} for choosing Flyover Consultancy! We appreciate your trust in us. Our team is committed to providing you with the best service.',
        },
    ],
    whatsapp: [
        {
            id: 'welcome',
            name: 'Welcome Message',
            message: 'Hello {{name}}! 👋\n\nWelcome to Flyover Consultancy! Thank you for your interest in our visa and immigration services.\n\nWe\'re here to help you every step of the way. Feel free to ask us any questions!\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'followup',
            name: 'Follow-up Message',
            message: 'Hi {{first_name}}! 👋\n\nJust checking in to see if you have any questions about our services. We\'re here to help!\n\nContact us:\n📞 {{phone}}\n📧 {{email}}\n\nLooking forward to hearing from you!',
        },
        {
            id: 'service_promotion',
            name: 'Service Promotion',
            message: '🌟 Special Offer Alert! 🌟\n\nDear {{name}},\n\nDiscover our comprehensive visa and immigration services:\n✅ Expert consultation\n✅ Document assistance\n✅ Application support\n\nContact us today to get started!\n📞 {{phone}}',
        },
        {
            id: 'document_reminder',
            name: 'Document Reminder',
            message: '📋 Document Reminder\n\nHello {{first_name}},\n\nThis is a friendly reminder to submit your required documents for your application.\n\nIf you need any assistance, don\'t hesitate to contact us!\n\nBest regards,\nFlyover Consultancy',
        },
        {
            id: 'appointment_reminder',
            name: 'Appointment Reminder',
            message: '📅 Appointment Reminder\n\nHi {{name}},\n\nJust a reminder about your upcoming appointment with us.\n\nIf you need to reschedule, please let us know in advance.\n\nWe look forward to meeting you!\n\nFlyover Consultancy',
        },
        {
            id: 'thank_you',
            name: 'Thank You Message',
            message: '🙏 Thank You!\n\nDear {{first_name}},\n\nThank you for choosing Flyover Consultancy! We truly appreciate your trust in us.\n\nOur team is committed to providing you with exceptional service throughout your journey.\n\nBest regards,\nFlyover Consultancy Team',
        },
    ],
    email: [
        {
            id: 'welcome',
            name: 'Welcome Email',
            message: 'Subject: Welcome to Flyover Consultancy\n\nDear {{name}},\n\nWelcome to Flyover Consultancy! We are delighted that you have chosen us for your visa and immigration needs.\n\nAt Flyover Consultancy, we are committed to providing you with expert guidance and support throughout your journey. Our experienced team is here to help you every step of the way.\n\nIf you have any questions, please don\'t hesitate to contact us:\n\nPhone: {{phone}}\nEmail: {{email}}\n\nWe look forward to working with you!\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'followup',
            name: 'Follow-up Email',
            message: 'Subject: Following Up on Your Inquiry\n\nDear {{first_name}},\n\nWe wanted to follow up on your recent inquiry about our services. We hope you found the information helpful.\n\nIf you have any questions or would like to schedule a consultation, please feel free to reach out to us. We\'re here to help!\n\nContact Information:\nPhone: {{phone}}\nEmail: {{email}}\n\nWe look forward to hearing from you.\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'service_promotion',
            name: 'Service Promotion Email',
            message: 'Subject: Discover Our Comprehensive Visa Services\n\nDear {{name}},\n\nWe are excited to introduce you to our comprehensive visa and immigration services:\n\n• Expert consultation and guidance\n• Document preparation and verification\n• Application submission support\n• Post-application assistance\n\nOur experienced team is dedicated to making your visa journey smooth and successful.\n\nContact us today to learn more:\nPhone: {{phone}}\nEmail: {{email}}\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'document_reminder',
            name: 'Document Reminder Email',
            message: 'Subject: Document Submission Reminder\n\nDear {{first_name}},\n\nThis is a friendly reminder to submit your required documents for your application.\n\nPlease ensure all documents are:\n• Complete and accurate\n• Properly certified (if required)\n• Submitted before the deadline\n\nIf you need any assistance with document preparation, please contact us:\n\nPhone: {{phone}}\nEmail: {{email}}\n\nWe are here to help!\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'appointment_reminder',
            name: 'Appointment Reminder Email',
            message: 'Subject: Appointment Reminder\n\nDear {{name}},\n\nThis is a reminder about your upcoming appointment with us.\n\nPlease ensure you bring all necessary documents and arrive on time.\n\nIf you need to reschedule, please contact us at least 24 hours in advance:\n\nPhone: {{phone}}\nEmail: {{email}}\n\nWe look forward to meeting you!\n\nBest regards,\nFlyover Consultancy Team',
        },
        {
            id: 'thank_you',
            name: 'Thank You Email',
            message: 'Subject: Thank You for Choosing Flyover Consultancy\n\nDear {{first_name}},\n\nThank you for choosing Flyover Consultancy! We truly appreciate your trust in us.\n\nOur team is committed to providing you with exceptional service and support throughout your visa journey. We are here to help you achieve your goals.\n\nIf you have any questions or need assistance, please don\'t hesitate to contact us:\n\nPhone: {{phone}}\nEmail: {{email}}\n\nThank you again for your trust in us.\n\nBest regards,\nFlyover Consultancy Team',
        },
    ],
};

const selectedTemplate = ref(null);
const showVariablesGuide = ref(false);
const copiedVariable = ref(null);

// Get available templates for current channel
const availableTemplates = computed(() => {
    return messageTemplates[form.type] || [];
});

// Watch for channel changes and clear template selection
watch(() => form.type, () => {
    selectedTemplate.value = null;
    // Optionally clear message when channel changes
    // form.message = '';
});

const applyTemplate = (template) => {
    if (template) {
        form.message = template.message;
        selectedTemplate.value = template.id;
    }
};

const clearTemplate = () => {
    selectedTemplate.value = null;
    form.message = '';
};

// Available variables with examples
const availableVariables = [
    {
        variable: '{{name}}',
        description: 'Full name of the lead',
        example: 'John Doe',
        usage: 'Hello {{name}}, welcome to our service!',
        result: 'Hello John Doe, welcome to our service!',
    },
    {
        variable: '{{first_name}}',
        description: 'First name only (extracted from full name)',
        example: 'John',
        usage: 'Hi {{first_name}}, how can we help?',
        result: 'Hi John, how can we help?',
    },
    {
        variable: '{{phone}}',
        description: 'Contact phone number',
        example: '+1234567890',
        usage: 'Call us at {{phone}} for assistance.',
        result: 'Call us at +1234567890 for assistance.',
    },
    {
        variable: '{{email}}',
        description: 'Email address',
        example: 'john@example.com',
        usage: 'Email us at {{email}} for more info.',
        result: 'Email us at john@example.com for more info.',
    },
];

const copyVariable = (variable) => {
    navigator.clipboard.writeText(variable);
    copiedVariable.value = variable;
    setTimeout(() => {
        copiedVariable.value = null;
    }, 2000);
};

const submit = () => {
    form.post(route('marketing.campaigns.store'));
};
</script>

<template>
    <Head title="Create Campaign" />

    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.campaigns.index')"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 shrink-0"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create New Campaign</h1>
                <p class="text-xs text-muted-foreground">Send messages to leads via SMS, Email, or WhatsApp</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Card 1: Campaign Information -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <FileText class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Campaign Information</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                Name, channel and optional targeting
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-5">
                    <div class="space-y-4">
                        <Label for="name" class="text-xs font-medium">Campaign name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. Summer follow-up" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-sm resize-none min-h-20 rounded-lg border-border/50" placeholder="Internal note about this campaign" />
                        <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Channel <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50">
                                        <span>{{ form.type === 'sms' ? 'SMS' : form.type === 'email' ? 'Email' : form.type === 'whatsapp' ? 'WhatsApp' : 'Select channel' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.type">
                                        <DropdownMenuRadioItem value="sms" class="text-sm">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-sm">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-sm">WhatsApp</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">WhatsApp is sent via WasenderAPI.</p>
                            <p v-if="form.errors.type" class="text-xs text-red-500">{{ form.errors.type }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="scheduled_at" class="text-xs font-medium">Schedule (optional)</Label>
                            <Input id="scheduled_at" v-model="form.scheduled_at" type="datetime-local" class="h-10 text-sm rounded-lg border-border/50" />
                            <p v-if="form.errors.scheduled_at" class="text-xs text-red-500">{{ form.errors.scheduled_at }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 2: Select Leads (futuristic) -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <CardTitle class="text-sm font-semibold tracking-tight">Select Leads</CardTitle>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Choose who will receive this campaign message
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                v-if="form.lead_ids.length > 0"
                                class="flex items-center gap-2 rounded-full bg-primary/10 dark:bg-primary/20 px-3 py-1.5 ring-1 ring-primary/20"
                            >
                                <MessageCircle class="h-3.5 w-3.5 text-primary shrink-0" />
                                <span class="text-xs font-medium text-primary">{{ form.lead_ids.length }} selected</span>
                                <span class="text-[10px] text-muted-foreground">will get message</span>
                            </div>
                            <Button
                                v-if="form.lead_ids.length > 0"
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-8 text-xs text-muted-foreground hover:text-destructive"
                                @click="clearLeadSelection"
                            >
                                Clear all
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Filter bar: glass style -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap rounded-xl bg-muted/30 dark:bg-muted/10 border border-border/50 p-3">
                        <div class="relative flex-1 min-w-[200px]">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                            <Input
                                id="lead-search"
                                v-model="leadFilters.search"
                                class="h-9 text-xs pl-9 bg-background/80 dark:bg-background/50 border-border/50"
                                placeholder="Search by name, email or phone..."
                                @keydown.enter="applyLeadFilters"
                            />
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <select
                                v-model="leadFilters.status"
                                class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-shadow"
                            >
                                <option value="">All statuses</option>
                                <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <select
                                v-model="leadFilters.package_id"
                                class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-shadow"
                            >
                                <option value="">All packages</option>
                                <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                            </select>
                            <select
                                v-model="leadFilters.service_id"
                                class="flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 dark:bg-background/50 px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-shadow"
                            >
                                <option value="">All services</option>
                                <option v-for="srv in services" :key="srv.id" :value="srv.id">{{ srv.name }}</option>
                            </select>
                            <Button
                                type="button"
                                size="sm"
                                class="h-9 text-xs gap-1.5 shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm"
                                :disabled="loadingLeads"
                                @click="applyLeadFilters"
                            >
                                <Zap v-if="!loadingLeads" class="h-3.5 w-3.5" />
                                <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                                Apply
                            </Button>
                        </div>
                    </div>

                    <!-- Selection toolbar: Select all options + hint -->
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between rounded-lg bg-muted/20 dark:bg-muted/10 border border-border/50 px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-muted-foreground">Select:</span>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 text-xs gap-1.5"
                                :disabled="leads.length === 0"
                                @click="toggleSelectAllOnPage"
                            >
                                <span
                                    class="inline-flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border transition-colors"
                                    :class="allOnPageSelected ? 'border-primary bg-primary text-primary-foreground' : pageLeadIds.some(id => selectedLeadIdsSet.has(id)) ? 'border-primary bg-primary/20' : 'border-input'"
                                >
                                    <Check v-if="allOnPageSelected" class="h-2.5 w-2.5" />
                                    <span v-else-if="pageLeadIds.some(id => selectedLeadIdsSet.has(id))" class="h-2 w-0.5 bg-primary rounded-full" />
                                </span>
                                All on this page ({{ leads.length }})
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 text-xs gap-1.5"
                                :disabled="leadsPagination.total === 0 || loadingSelectAll"
                                @click="fetchAllLeadIds"
                            >
                                <Loader2 v-if="loadingSelectAll" class="h-3.5 w-3.5 animate-spin" />
                                <Users v-else class="h-3.5 w-3.5" />
                                All {{ leadsPagination.total }} leads
                            </Button>
                        </div>
                        <p class="text-[10px] text-muted-foreground sm:max-w-[200px]">
                            Selection is kept when you change pages. Use "Clear all" to reset.
                        </p>
                    </div>

                    <!-- Table container with subtle glow when selection exists -->
                    <div
                        class="rounded-xl border overflow-hidden transition-all duration-200"
                        :class="form.lead_ids.length > 0 ? 'border-primary/40 ring-1 ring-primary/20' : 'border-border/50'"
                    >
                        <div class="overflow-x-auto max-h-[340px] overflow-y-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead class="sticky top-0 z-10 bg-muted/95 dark:bg-muted/40 backdrop-blur-sm border-b border-border/50">
                                    <tr>
                                        <th class="text-left p-3 w-12 align-middle bg-muted/50 dark:bg-muted/20">
                                            <Checkbox
                                                :checked="allOnPageSelected"
                                                :indeterminate="pageLeadIds.length > 0 && pageLeadIds.some(id => selectedLeadIdsSet.has(id)) && !allOnPageSelected"
                                                class="data-[state=checked]:bg-primary data-[state=checked]:border-primary"
                                                @update:checked="toggleSelectAllOnPage"
                                            />
                                        </th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Name</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Email</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider">Phone</th>
                                        <th class="text-left p-3 font-semibold text-muted-foreground uppercase tracking-wider w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loadingLeads">
                                        <td colspan="5" class="p-12 text-center">
                                            <div class="flex flex-col items-center gap-3 text-muted-foreground">
                                                <div class="h-10 w-10 rounded-full border-2 border-primary/30 border-t-primary animate-spin" />
                                                <span class="text-xs">Loading leads...</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="leads.length === 0">
                                        <td colspan="5" class="p-12 text-center">
                                            <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                                <Users class="h-10 w-10 opacity-50" />
                                                <span class="text-xs">No leads found</span>
                                                <span class="text-[10px]">Try different filters or search</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(lead, index) in leads"
                                        :key="lead.id"
                                        class="border-b border-border/50 last:border-b-0 transition-colors duration-150"
                                        :class="[
                                            index % 2 === 1 ? 'bg-muted/20 dark:bg-muted/5' : '',
                                            isLeadSelected(lead.id)
                                                ? 'bg-primary/5 dark:bg-primary/10 border-l-2 border-l-primary'
                                                : 'hover:bg-muted/30 dark:hover:bg-muted/15'
                                        ]"
                                    >
                                        <td class="p-3 align-middle bg-muted/30 dark:bg-muted/10 w-12">
                                            <Checkbox
                                                :checked="isLeadSelected(lead.id)"
                                                class="data-[state=checked]:bg-primary data-[state=checked]:border-primary"
                                                @update:checked="toggleLead(lead.id)"
                                            />
                                        </td>
                                        <td class="p-3 font-medium">{{ lead.full_name || '—' }}</td>
                                        <td class="p-3 truncate max-w-[200px] text-muted-foreground" :title="lead.email">{{ lead.email || '—' }}</td>
                                        <td class="p-3 font-mono text-[11px] text-muted-foreground">{{ lead.phone || '—' }}</td>
                                        <td class="p-3">
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium capitalize"
                                                :class="isLeadSelected(lead.id) ? 'bg-primary/20 text-primary ring-1 ring-primary/30' : 'bg-muted/80 dark:bg-muted/50 text-muted-foreground'"
                                            >
                                                {{ lead.status || '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex flex-col gap-2 xs:flex-row xs:items-center xs:justify-between border-t border-border/50 px-4 py-2.5 bg-muted/20 dark:bg-muted/10"
                        >
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span class="truncate">
                                    Page {{ leadsPagination.current_page }} of {{ leadsPagination.last_page }} · {{ leadsPagination.total }} leads
                                </span>
                                <span v-if="form.lead_ids.length > 0" class="font-medium text-primary">
                                    {{ form.lead_ids.length }} selected
                                </span>
                            </div>
                            <div v-if="leadsPagination.last_page > 1" class="flex gap-1 shrink-0">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 w-8 p-0 rounded-lg border-border/50"
                                    :disabled="leadsPagination.current_page <= 1 || loadingLeads"
                                    @click="fetchLeads(leadsPagination.current_page - 1)"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 w-8 p-0 rounded-lg border-border/50"
                                    :disabled="leadsPagination.current_page >= leadsPagination.last_page || loadingLeads"
                                    @click="fetchLeads(leadsPagination.current_page + 1)"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 3: Message -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <MessageSquare class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Message</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                Content that selected leads will receive
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <Label class="text-xs font-medium">Templates</Label>
                            <Button
                                v-if="selectedTemplate"
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-8 text-xs text-muted-foreground"
                                @click="clearTemplate"
                            >
                                Clear template
                            </Button>
                        </div>
                        <div v-if="availableTemplates.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button
                                v-for="template in availableTemplates"
                                :key="template.id"
                                type="button"
                                :class="[
                                    'flex items-center gap-2.5 p-3 rounded-lg border text-left transition-colors text-sm',
                                    selectedTemplate === template.id
                                        ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary/20'
                                        : 'border-border/50 bg-muted/20 dark:bg-muted/10 hover:bg-muted/40 dark:hover:bg-muted/20'
                                ]"
                                @click="applyTemplate(template)"
                            >
                                <Sparkles class="h-4 w-4 shrink-0" />
                                <span class="truncate font-medium">{{ template.name }}</span>
                            </button>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">No templates for this channel.</p>
                    </div>

                    <div class="space-y-3">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-9 text-xs gap-2 rounded-lg border-border/50"
                            @click="showVariablesGuide = !showVariablesGuide"
                        >
                            <HelpCircle class="h-3.5 w-3.5" />
                            {{ showVariablesGuide ? 'Hide' : 'Show' }} variables guide
                        </Button>
                        <div v-if="showVariablesGuide" class="rounded-lg border border-border/50 p-4 bg-muted/20 dark:bg-muted/10 space-y-4">
                            <p class="text-xs text-muted-foreground">
                                These placeholders are replaced with each lead’s data when the campaign is sent.
                            </p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="(varItem, index) in availableVariables"
                                    :key="index"
                                    class="rounded-lg border border-border/50 p-3 bg-background flex items-start gap-2"
                                >
                                    <code class="text-xs font-mono bg-primary/10 text-primary px-2 py-1 rounded shrink-0">{{ varItem.variable }}</code>
                                    <div class="min-w-0">
                                        <p class="text-xs text-muted-foreground">{{ varItem.description }}</p>
                                        <Button type="button" variant="ghost" size="sm" class="h-6 mt-1 text-xs px-2 gap-1" @click="copyVariable(varItem.variable)">
                                            <Copy v-if="copiedVariable !== varItem.variable" class="h-3 w-3" />
                                            <Check v-else class="h-3 w-3 text-green-600" />
                                            Copy
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-border/50">
                                <p class="text-xs font-medium text-foreground mb-1.5">Tips</p>
                                <ul class="text-xs text-muted-foreground space-y-1 list-disc list-inside">
                                    <li>Use exactly as shown (e.g. {{name}} not {{Name}})</li>
                                    <li>Wrap in double curly braces</li>
                                    <li>Missing data becomes an empty string</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <Label for="message" class="text-xs font-medium">Message <span class="text-red-500">*</span></Label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs text-muted-foreground shrink-0">Insert:</span>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <Button
                                        v-for="varItem in availableVariables"
                                        :key="varItem.variable"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class="h-8 text-xs px-2.5 font-mono rounded-lg border-border/50"
                                        @click="form.message += varItem.variable"
                                    >
                                        {{ varItem.variable }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <Textarea
                            id="message"
                            v-model="form.message"
                            rows="6"
                            class="text-sm resize-y min-h-32 font-mono rounded-lg border-border/50"
                            placeholder="Write your message or choose a template above. Use {{name}}, {{first_name}}, {{phone}}, {{email}}"
                        />
                        <div class="flex items-center justify-between gap-2 flex-wrap text-xs text-muted-foreground">
                            <span>
                                <span class="font-medium text-foreground">Variables:</span>
                                <span v-for="(varItem, index) in availableVariables" :key="index" class="ml-1">
                                    <code class="text-[10px]">{{ varItem.variable }}</code>
                                    <span v-if="index < availableVariables.length - 1">,</span>
                                </span>
                            </span>
                            <span>{{ form.message.length }} characters</span>
                        </div>
                        <p v-if="form.errors.message" class="text-xs text-red-500">{{ form.errors.message }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-2">
                <Button type="button" variant="outline" size="sm" class="sm:order-first" as-child>
                    <Link :href="route('marketing.campaigns.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Send v-else class="h-3.5 w-3.5" />
                    Create Campaign
                </Button>
            </div>
        </form>
    </div>
</template>
