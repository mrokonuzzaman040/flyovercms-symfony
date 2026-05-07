<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DeleteConfirmation, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Phone,
    Mail,
    MapPin,
    Calendar,
    User,
    FileText,
    MessageSquare,
    Clock,
    Globe,
    Briefcase,
    Loader2,
    Send,
    ExternalLink,
    Building,
    ChevronDown,
    ArrowRightLeft,
    Package,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    notes: { type: Array, default: () => [] },
    transferableUsers: { type: Array, default: () => [] },
    statusConfig: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission } = usePermissions();

const deleteModal = ref({ open: false, loading: false });
const noteModal = ref({ open: false, loading: false });
const messageModal = ref({ open: false });
const orderNewServiceLoading = ref(false);
const activeTab = ref('overview');

const noteForm = useForm({
    note: '',
    is_important: false,
});

const messageForm = useForm({
    template: 'custom',
    custom_message: '',
    channel: 'sms',
    channels: ['sms'],
    selected_phone: '',
    subject: '',
    attachments: [],
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handleOrderNewService = () => {
    orderNewServiceLoading.value = true;
    router.post(route('leads.order-new-service', props.lead.id), {}, {
        onFinish: () => { orderNewServiceLoading.value = false; },
    });
};

const handleDelete = () => {
    deleteModal.value.loading = true;
    router.delete(route('leads.destroy', props.lead.id), {
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};

const submitNote = () => {
    noteForm.post(route('leads.notes.store', props.lead.id), {
        preserveScroll: true,
        onSuccess: () => {
            noteModal.value.open = false;
            noteForm.reset();
        },
    });
};

const getWhatsAppNumber = () => {
    return props.lead.whatsapp_number || props.lead.phone || null;
};

/** Unique lead phone options for "Send to number" (phone, alternate_phone, whatsapp_number). */
const leadPhoneOptions = computed(() => {
    const seen = new Set();
    const options = [];
    const add = (value, label) => {
        if (!value?.trim()) return;
        const normalized = formatPhone(value);
        if (!normalized || seen.has(normalized)) return;
        seen.add(normalized);
        options.push({ value: value.trim(), label: `${label} (${value.trim()})` });
    };
    add(props.lead.phone, 'Phone');
    add(props.lead.alternate_phone, 'Alternate');
    if (props.lead.whatsapp_number && props.lead.whatsapp_number !== props.lead.phone && props.lead.whatsapp_number !== props.lead.alternate_phone) {
        add(props.lead.whatsapp_number, 'WhatsApp');
    }
    return options;
});

const formatPhone = (phone) => {
    if (!phone) return '';
    // Remove all non-numeric characters (same as Index.vue)
    return phone.replace(/\D+/g, '');
};

const openWhatsApp = (phone) => {
    if (!phone) return;
    const phoneDigits = formatPhone(phone);
    if (!phoneDigits) return;
    window.open(`https://wa.me/${phoneDigits}`, '_blank');
};

const openWhatsAppCall = (phone) => {
    if (!phone) return;
    const phoneDigits = formatPhone(phone);
    if (!phoneDigits) return;
    window.open(`https://wa.me/${phoneDigits}`, '_blank');
};

const makeCall = (phone) => {
    if (!phone) return;
    window.location.href = `tel:${phone}`;
};

const attachmentInputRef = ref(null);
const attachmentFiles = ref([]);

const openMessageModal = () => {
    messageForm.reset();
    messageForm.template = 'custom';
    messageForm.channel = 'sms';
    messageForm.channels = ['sms'];
    messageForm.selected_phone = leadPhoneOptions.value[0]?.value ?? '';
    messageForm.subject = '';
    messageForm.attachments = [];
    attachmentFiles.value = [];
    messageModal.value.open = true;
};

const onAttachmentChange = (e) => {
    const files = e.target.files ? Array.from(e.target.files) : [];
    attachmentFiles.value = files;
    messageForm.attachments = files;
};

const clearAttachments = () => {
    attachmentFiles.value = [];
    messageForm.attachments = [];
    if (attachmentInputRef.value) attachmentInputRef.value.value = '';
};

const submitMessage = () => {
    const hasSms = messageForm.channel === 'sms' || messageForm.channel === 'both';
    const hasWhatsApp = messageForm.channel === 'whatsapp' || messageForm.channel === 'both';
    const hasEmail = messageForm.channel === 'email' || messageForm.channel === 'all';

    messageForm.channels = [];
    if (hasSms) messageForm.channels.push('sms');
    if (hasWhatsApp) messageForm.channels.push('whatsapp');
    if (hasEmail) messageForm.channels.push('email');

    messageForm.attachments = attachmentFiles.value;
    if (messageForm.channels.length) {
        messageForm.post(route('leads.send-communication', props.lead.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                messageModal.value.open = false;
                messageForm.reset();
                clearAttachments();
            },
        });
    } else {
        messageModal.value.open = false;
        messageForm.reset();
        clearAttachments();
    }
};

const showAttachmentSection = computed(() => {
    const ch = messageForm.channel;
    return ch === 'whatsapp' || ch === 'email' || ch === 'both' || ch === 'all';
});
</script>

<template>

    <Head :title="lead.full_name" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <Link :href="route('leads.index')"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9 mt-1">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                            <span class="text-lg font-bold text-primary">{{ lead.full_name?.charAt(0)?.toUpperCase() ||
                                '?' }}</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">{{ lead.full_name }}</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <StatusBadge v-if="lead.status" :status="lead.status" />
                                <StatusBadge v-if="lead.priority" :status="lead.priority" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" @click="noteModal.open = true">
                    <MessageSquare class="mr-2 h-4 w-4" /> Add Note
                </Button>
                <Button v-if="hasPermission('send-messages')" variant="outline" @click="openMessageModal">
                    <Send class="mr-2 h-4 w-4" /> Send message
                </Button>
                <Button v-if="!lead.is_locked_for_editing || hasPermission('edit-leads')" variant="outline" as-child>
                    <Link :href="route('leads.edit', lead.id)">
                        <Pencil class="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button v-if="hasPermission('transfer-leads')" variant="outline" as-child>
                    <Link :href="route('leads.transfer', lead.id)">
                        <ArrowRightLeft class="mr-2 h-4 w-4" /> Transfer
                    </Link>
                </Button>
                <Button
                    v-if="lead.status === 'converted' && hasPermission('create-leads')"
                    variant="default"
                    :disabled="orderNewServiceLoading"
                    @click="handleOrderNewService"
                >
                    <Loader2 v-if="orderNewServiceLoading" class="mr-2 h-4 w-4 animate-spin" />
                    <Package v-else class="mr-2 h-4 w-4" />
                    Order new service
                </Button>
                <DropdownMenu
                    v-if="!lead.transferred_to_department && !lead.pending_department_transfer && hasPermission('transfer-to-department')">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <Building class="mr-2 h-4 w-4" />
                            Transfer to Department
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem as-child>
                            <Link :href="route('departments.transfers.show-form', [lead.id, 'visa'])">
                                <FileText class="mr-2 h-4 w-4" />
                                Transfer to Visa Department
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="route('departments.transfers.show-form', [lead.id, 'accounts'])">
                                <Briefcase class="mr-2 h-4 w-4" />
                                Transfer to Accounts Department
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button variant="destructive" @click="deleteModal.open = true">
                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                </Button>
            </div>
        </div>

        <!-- Department Transfer Warning -->
        <div v-if="lead.transferred_to_department"
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <Building class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                        Lead Transferred to {{ lead.transferred_to_department.charAt(0).toUpperCase() +
                            lead.transferred_to_department.slice(1) }} Department
                    </h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        This lead has been transferred to the {{ lead.transferred_to_department }} department.
                        <span v-if="lead.is_locked_for_editing">
                            Editing is restricted. Only the department manager ({{ lead.department_manager?.name ||
                                'Unassigned' }}) can make changes.
                        </span>
                        <span v-else>
                            Managed by: {{ lead.department_manager?.name || 'Unassigned' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending Department Transfer Warning -->
        <div v-else-if="lead.pending_department_transfer"
            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <Building class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                        Pending Transfer to {{ lead.pending_department_transfer.department.charAt(0).toUpperCase() +
                            lead.pending_department_transfer.department.slice(1) }} Department
                    </h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        A transfer request has been sent to the {{ lead.pending_department_transfer.department }}
                        department.
                        Waiting for acceptance.
                    </p>
                </div>
            </div>
        </div>

        <!-- Source lead notice (this lead was created from a converted lead) -->
        <div v-if="lead.source_lead_id && (lead.source_lead || lead.sourceLead)"
            class="bg-muted/50 dark:bg-muted/20 border border-border rounded-lg p-4">
            <div class="flex items-start gap-3">
                <Package class="h-5 w-5 text-muted-foreground mt-0.5 shrink-0" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold">Order new service</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        This lead was created from a converted lead.
                        <Link :href="route('leads.show', (lead.source_lead || lead.sourceLead).id)" class="font-medium text-primary hover:underline">
                            View original lead ({{ (lead.source_lead || lead.sourceLead).full_name }})
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <!-- New service leads (when this is the original converted lead) -->
        <div v-if="(lead.new_service_leads || lead.newServiceLeads)?.length" class="bg-muted/50 dark:bg-muted/20 border border-border rounded-lg p-4">
            <h3 class="text-sm font-semibold mb-2">New service leads</h3>
            <p class="text-sm text-muted-foreground mb-2">Leads created from this converted lead (order new service):</p>
            <ul class="space-y-1">
                <li v-for="sub in (lead.new_service_leads || lead.newServiceLeads)" :key="sub.id" class="text-sm">
                    <Link :href="route('leads.show', sub.id)" class="font-medium text-primary hover:underline">
                        {{ sub.full_name }} ({{ sub.status }})
                    </Link>
                    <span class="text-muted-foreground"> – {{ formatDate(sub.created_at) }}</span>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <Tabs v-model="activeTab" class="space-y-6">
            <TabsList>
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="notes">Notes ({{ notes.length }})</TabsTrigger>
                <TabsTrigger value="activities">Activities</TabsTrigger>
            </TabsList>

            <!-- Overview Tab -->
            <TabsContent value="overview" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Contact Information -->
                    <Card class="lg:col-span-2">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" /> Contact Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex items-center gap-3">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <div class="flex-1">
                                        <p class="text-sm text-muted-foreground">Phone</p>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ lead.phone || '-' }}</p>
                                            <Button v-if="lead.phone" variant="ghost" size="icon"
                                                class="h-6 w-6 text-green-600 hover:text-green-700 hover:bg-green-50"
                                                @click="makeCall(lead.phone)" title="Call">
                                                <Phone class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <div class="flex-1">
                                        <p class="text-sm text-muted-foreground">Alternate Phone</p>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ lead.alternate_phone || '-' }}</p>
                                            <Button v-if="lead.alternate_phone" variant="ghost" size="icon"
                                                class="h-6 w-6 text-green-600 hover:text-green-700 hover:bg-green-50"
                                                @click="makeCall(lead.alternate_phone)" title="Call">
                                                <Phone class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <div class="flex-1">
                                        <p class="text-sm text-muted-foreground">Email</p>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ lead.email || '-' }}</p>
                                            <Button v-if="lead.email" variant="ghost" size="icon" class="h-6 w-6"
                                                @click="window.location.href = `mailto:${lead.email}`"
                                                title="Send Email">
                                                <Mail class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <MessageSquare class="h-4 w-4 text-[#25D366]" />
                                    <div class="flex-1">
                                        <p class="text-sm text-muted-foreground">WhatsApp</p>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ getWhatsAppNumber() || '-' }}</p>
                                            <Button v-if="getWhatsAppNumber()" variant="ghost" size="icon"
                                                class="h-6 w-6 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                                                @click="openWhatsApp(getWhatsAppNumber())" title="WhatsApp Chat">
                                                <MessageSquare class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button v-if="getWhatsAppNumber()" variant="ghost" size="icon"
                                                class="h-6 w-6 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                                                @click="openWhatsAppCall(getWhatsAppNumber())" title="WhatsApp Call">
                                                <Phone class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <Separator />
                            <div class="flex items-start gap-3">
                                <MapPin class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm text-muted-foreground">Address</p>
                                    <p class="font-medium">
                                        <template v-if="lead.current_address || lead.address">
                                            {{ lead.current_address || lead.address }}
                                            <span v-if="[lead.city, lead.state, lead.postal_code, lead.country].some(Boolean)">, {{ [lead.city, lead.state, lead.postal_code, lead.country].filter(Boolean).join(', ') }}</span>
                                        </template>
                                        <template v-else-if="[lead.city, lead.state, lead.postal_code, lead.country].some(Boolean)">
                                            {{ [lead.city, lead.state, lead.postal_code, lead.country].filter(Boolean).join(', ') }}
                                        </template>
                                        <template v-else>-</template>
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Info</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">Status</p>
                                <p class="font-medium capitalize">{{ lead.status?.replace(/_/g, ' ') || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Priority</p>
                                <p class="font-medium capitalize">{{ lead.priority?.replace(/_/g, ' ') || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Assigned To</p>
                                <p class="font-medium">{{ lead.assigned_user?.name || 'Unassigned' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Source</p>
                                <p class="font-medium capitalize">{{ (lead.lead_source || lead.source)?.replace(/_/g, ' ') || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Service Type</p>
                                <p class="font-medium capitalize">{{ lead.service_type?.replace('_', ' ') || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Created</p>
                                <p class="font-medium">{{ formatDate(lead.created_at) }}</p>
                            </div>
                            <div v-if="lead.creator">
                                <p class="text-sm text-muted-foreground">Created By</p>
                                <p class="font-medium">{{ lead.creator.name }}</p>
                            </div>
                            <div v-if="lead.branch">
                                <p class="text-sm text-muted-foreground">Branch</p>
                                <p class="font-medium">{{ lead.branch.name }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Personal & Service Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Briefcase class="h-5 w-5" /> Service & Personal Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <p class="text-sm text-muted-foreground">Destination Country</p>
                                <p class="font-medium">{{ lead.destination_country || '-' }}</p>
                            </div>
                            <div v-if="lead.secondary_destination_country">
                                <p class="text-sm text-muted-foreground">Secondary Destination Country</p>
                                <p class="font-medium">{{ lead.secondary_destination_country }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Nationality</p>
                                <p class="font-medium">{{ lead.nationality || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Date of Birth</p>
                                <p class="font-medium">{{ lead.date_of_birth ? new Date(lead.date_of_birth).toLocaleDateString() : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Place of Birth</p>
                                <p class="font-medium">{{ lead.place_of_birth || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Gender</p>
                                <p class="font-medium capitalize">{{ lead.gender || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Marital Status</p>
                                <p class="font-medium capitalize">{{ lead.marital_status?.replace(/_/g, ' ') || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Current Country</p>
                                <p class="font-medium">{{ lead.current_country || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Education Level</p>
                                <p class="font-medium">{{ lead.education_level || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">English Proficiency</p>
                                <p class="font-medium">{{ lead.english_proficiency || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Budget / Budget Range</p>
                                <p class="font-medium">{{ lead.budget_range || lead.budget || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Funding Source</p>
                                <p class="font-medium capitalize">{{ lead.funding_source?.replace(/_/g, ' ') || '-' }}</p>
                            </div>
                            <div v-if="lead.intended_department">
                                <p class="text-sm text-muted-foreground">Intended Department</p>
                                <p class="font-medium capitalize">{{ lead.intended_department }}</p>
                            </div>
                        </div>

                        <!-- Passport (from lead or lead.passport relation) -->
                        <div v-if="(lead.passport_number || lead.passport?.passport_number) || (lead.passport_issue_date || lead.passport?.issue_date) || (lead.passport_expiry_date || lead.passport?.expiry_date) || (lead.passport_place_of_issue || lead.passport?.place_of_issue)" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Passport</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Passport Number</p>
                                    <p class="font-medium">{{ lead.passport_number || lead.passport?.passport_number || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Issue Date</p>
                                    <p class="font-medium">{{ formatDate(lead.passport_issue_date || lead.passport?.issue_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Expiry Date</p>
                                    <p class="font-medium">{{ formatDate(lead.passport_expiry_date || lead.passport?.expiry_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Place of Issue</p>
                                    <p class="font-medium">{{ lead.passport_place_of_issue || lead.passport?.place_of_issue || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Study Visa (from lead or lead.study_preference relation) -->
                        <div v-if="lead.service_type === 'study_visa' && (lead.preferred_university || lead.study_preference?.preferred_university || lead.preferred_course || lead.study_preference?.preferred_course || lead.course_level || lead.study_preference?.course_level || lead.intended_start_date || lead.study_preference?.intended_start_date || lead.intended_semester || lead.study_preference?.intended_semester || lead.program_duration || lead.study_preference?.program_duration || lead.tuition_fee_range || lead.study_preference?.tuition_fee_range)" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Study Visa</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Preferred University</p>
                                    <p class="font-medium">{{ lead.preferred_university || lead.study_preference?.preferred_university || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Preferred Course</p>
                                    <p class="font-medium">{{ lead.preferred_course || lead.study_preference?.preferred_course || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Course Level</p>
                                    <p class="font-medium capitalize">{{ (lead.course_level || lead.study_preference?.course_level)?.replace(/_/g, ' ') || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Intended Start Date</p>
                                    <p class="font-medium">{{ formatDate(lead.intended_start_date || lead.study_preference?.intended_start_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Intended Semester</p>
                                    <p class="font-medium capitalize">{{ lead.intended_semester || lead.study_preference?.intended_semester || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Program Duration</p>
                                    <p class="font-medium">{{ lead.program_duration || lead.study_preference?.program_duration || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Tuition Fee Range</p>
                                    <p class="font-medium">{{ lead.tuition_fee_range || lead.study_preference?.tuition_fee_range || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Language Proficiency (from lead or lead.language_test relation) -->
                        <div v-if="lead.has_language_proficiency || lead.language_test?.has_proficiency || lead.language_test_type || lead.language_test?.test_type || lead.language_test_score || lead.language_test?.score" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Language Proficiency</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Test Type</p>
                                    <p class="font-medium">{{ lead.language_test_type || lead.language_test?.test_type || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Score</p>
                                    <p class="font-medium">{{ lead.language_test_score || lead.language_test?.score || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Test Date</p>
                                    <p class="font-medium">{{ formatDate(lead.language_test_date || lead.language_test?.test_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Expiry Date</p>
                                    <p class="font-medium">{{ formatDate(lead.language_test_expiry_date || lead.language_test?.expiry_date) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tour Visa -->
                        <div v-if="lead.service_type === 'tour_visa' && lead.number_of_tour_persons" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Tour Visa</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-sm text-muted-foreground">Number of Tour Persons</p>
                                    <p class="font-medium">{{ lead.number_of_tour_persons || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Immigration / Work (from lead or lead.occupation relation) -->
                        <div v-if="(lead.service_type === 'immigration' || lead.current_occupation || lead.occupation?.current_occupation || lead.job_title || lead.occupation?.job_title || lead.company_name || lead.occupation?.company_name) && (lead.current_occupation || lead.occupation?.current_occupation || lead.job_title || lead.occupation?.job_title || lead.company_name || lead.occupation?.company_name || lead.work_experience_years || lead.occupation?.experience_years || lead.current_salary || lead.occupation?.current_salary)" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Work / Immigration</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Current Occupation</p>
                                    <p class="font-medium">{{ lead.current_occupation || lead.occupation?.current_occupation || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Job Title</p>
                                    <p class="font-medium">{{ lead.job_title || lead.occupation?.job_title || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Company Name</p>
                                    <p class="font-medium">{{ lead.company_name || lead.occupation?.company_name || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Work Experience (Years)</p>
                                    <p class="font-medium">{{ lead.work_experience_years || lead.occupation?.experience_years || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Current Salary</p>
                                    <p class="font-medium">{{ lead.current_salary ?? lead.occupation?.current_salary ?? '-' }}</p>
                                </div>
                                <div v-if="(lead.job_description || lead.occupation?.description)" class="sm:col-span-2 lg:col-span-4">
                                    <p class="text-sm text-muted-foreground">Job Description</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.job_description || lead.occupation?.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Previous visits -->
                        <div v-if="(lead.travel_visa_histories && lead.travel_visa_histories.length)" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Previous visits</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border rounded-md">
                                    <thead>
                                        <tr class="border-b bg-muted/50">
                                            <th class="text-left p-2 font-medium">Country</th>
                                            <th class="text-left p-2 font-medium">Year</th>
                                            <th class="text-left p-2 font-medium">Visa type</th>
                                            <th class="text-left p-2 font-medium">Person</th>
                                            <th class="text-left p-2 font-medium">Issue / Expiry</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(h, idx) in lead.travel_visa_histories"
                                            :key="h.id || idx"
                                            class="border-b last:border-0"
                                        >
                                            <td class="p-2">{{ h.country || '—' }}</td>
                                            <td class="p-2">{{ h.year || '—' }}</td>
                                            <td class="p-2">{{ h.visa_type || '—' }}</td>
                                            <td class="p-2">{{ h.person || '—' }}</td>
                                            <td class="p-2 text-muted-foreground">{{ formatDate(h.visa_issue_date) }} / {{ formatDate(h.visa_expiry_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Visa Status & Application -->
                        <div v-if="lead.visa_status || lead.application_type || lead.application_date || lead.previous_visa_history" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Visa Status & Application</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Visa Status</p>
                                    <p class="font-medium capitalize">{{ lead.visa_status?.replace(/_/g, ' ') || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Application Type</p>
                                    <p class="font-medium capitalize">{{ lead.application_type || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Application Date</p>
                                    <p class="font-medium">{{ lead.application_date ? new Date(lead.application_date).toLocaleDateString() : '-' }}</p>
                                </div>
                                <div v-if="lead.previous_visa_history" class="sm:col-span-2 lg:col-span-4">
                                    <p class="text-sm text-muted-foreground">Previous Visa History</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.previous_visa_history }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Dependents -->
                        <div v-if="lead.has_dependents || lead.number_of_dependents || lead.dependents_information" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Dependents</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-sm text-muted-foreground">Number of Dependents</p>
                                    <p class="font-medium">{{ lead.number_of_dependents ?? '-' }}</p>
                                </div>
                                <div v-if="lead.dependents_information" class="sm:col-span-2">
                                    <p class="text-sm text-muted-foreground">Dependents Information</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.dependents_information }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes & Source Details -->
                        <div v-if="lead.notes || lead.special_requirements || lead.how_did_you_find_us || lead.referral_source_details || lead.lead_source_link" class="pt-4 border-t">
                            <h3 class="text-sm font-semibold mb-3">Notes & Source Details</h3>
                            <div class="space-y-4">
                                <div v-if="lead.notes">
                                    <p class="text-sm text-muted-foreground">Notes</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.notes }}</p>
                                </div>
                                <div v-if="lead.special_requirements">
                                    <p class="text-sm text-muted-foreground">Special Requirements</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.special_requirements }}</p>
                                </div>
                                <div v-if="lead.how_did_you_find_us">
                                    <p class="text-sm text-muted-foreground">How did you find us</p>
                                    <p class="font-medium">{{ lead.how_did_you_find_us }}</p>
                                </div>
                                <div v-if="lead.referral_source_details">
                                    <p class="text-sm text-muted-foreground">Referral / Source Details</p>
                                    <p class="font-medium whitespace-pre-wrap">{{ lead.referral_source_details }}</p>
                                </div>
                                <div v-if="lead.lead_source_link">
                                    <p class="text-sm text-muted-foreground">Source link</p>
                                    <a :href="lead.lead_source_link" target="_blank" rel="noopener noreferrer" class="font-medium text-primary hover:underline break-all">{{ lead.lead_source_link }}</a>
                                </div>
                            </div>
                        </div>

                        <div v-if="lead.services?.length || lead.packages?.length" class="pt-6 border-t">
                            <h3 class="text-lg font-semibold mb-4">Associated Services & Packages</h3>
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div v-if="lead.services?.length" class="space-y-2">
                                    <p class="text-sm font-medium text-muted-foreground">Services ({{
                                        lead.services.length }})</p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-for="service in lead.services" :key="service.id" variant="secondary"
                                            class="text-sm py-1 px-2">
                                            {{ service.name }}
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="lead.packages?.length" class="space-y-2">
                                    <p class="text-sm font-medium text-muted-foreground">Packages ({{
                                        lead.packages.length }})</p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-for="pkg in lead.packages" :key="pkg.id" variant="outline"
                                            class="text-sm py-1 px-2">
                                            {{ pkg.name }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Notes Tab -->
            <TabsContent value="notes">
                <Card>
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                        <CardDescription>All notes related to this lead.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="notes.length" class="space-y-4">
                            <div v-for="note in notes" :key="note.id" class="p-4 rounded-lg border"
                                :class="note.is_important ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-950/20' : ''">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm whitespace-pre-wrap">{{ note.note }}</p>
                                        <p class="text-xs text-muted-foreground mt-2">
                                            By {{ note.created_by?.name || 'Unknown' }} on {{
                                                formatDate(note.created_at) }}
                                        </p>
                                    </div>
                                    <Badge v-if="note.is_important" variant="secondary" class="ml-2">Important</Badge>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground text-center py-8">No notes yet. Add the first note!</p>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Activities Tab -->
            <TabsContent value="activities">
                <Card>
                    <CardHeader>
                        <CardTitle>Activity Timeline</CardTitle>
                        <CardDescription>Track all activities for this lead.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activities.length" class="space-y-4">
                            <div v-for="activity in activities" :key="activity.id" class="flex gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-sm">{{ activity.description }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground text-center py-8">No activities recorded yet.</p>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>

        <!-- Add Note Modal -->
        <Dialog v-model:open="noteModal.open">
            <DialogContent v-if="noteModal.open">
                <DialogHeader>
                    <DialogTitle>Add Note</DialogTitle>
                    <DialogDescription>Add a note to this lead.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitNote">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="note">Note</Label>
                            <Textarea id="note" v-model="noteForm.note" placeholder="Enter your note..." rows="4" />
                        </div>
                    </div>
                    <DialogFooter class="mt-4">
                        <Button type="button" variant="outline" @click="noteModal.open = false">Cancel</Button>
                        <Button type="submit" :disabled="noteForm.processing">
                            <Loader2 v-if="noteForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Save Note
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Send message Modal -->
        <Dialog v-model:open="messageModal.open">
            <DialogContent v-if="messageModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Send message</DialogTitle>
                    <DialogDescription>
                        Send via SMS, WhatsApp, or Email. Attach files for WhatsApp (attach manually in chat) or Email (sent as attachments).
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitMessage">
                    <div class="space-y-4">
                        <div v-if="leadPhoneOptions.length && (messageForm.channel !== 'email')" class="space-y-2">
                            <Label>Send to number</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button type="button" variant="outline" class="w-full justify-between">
                                        <span>{{ (leadPhoneOptions.find(o => o.value === messageForm.selected_phone)?.label) || 'Choose number' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                    <DropdownMenuRadioGroup v-model="messageForm.selected_phone">
                                        <DropdownMenuRadioItem
                                            v-for="opt in leadPhoneOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                            class="text-sm"
                                        >
                                            {{ opt.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <p v-if="(messageForm.channel !== 'email') && !leadPhoneOptions.length" class="text-sm text-muted-foreground">No phone number on this lead. Add one in Edit.</p>
                        <div v-if="messageForm.channel === 'email' && !lead?.email" class="text-sm text-muted-foreground">No email on this lead. Add one in Edit.</div>
                        <div class="space-y-2">
                            <Label>Channel</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between">
                                        <span>{{
                                            messageForm.channel === 'sms' ? 'SMS' :
                                            messageForm.channel === 'whatsapp' ? 'WhatsApp' :
                                            messageForm.channel === 'email' ? 'Email' :
                                            messageForm.channel === 'both' ? 'SMS and WhatsApp' :
                                            messageForm.channel === 'all' ? 'SMS, WhatsApp and Email' : 'Choose channel'
                                        }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                    <DropdownMenuRadioGroup v-model="messageForm.channel">
                                        <DropdownMenuRadioItem value="sms" class="text-sm">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-sm">WhatsApp</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-sm">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="both" class="text-sm">SMS and WhatsApp</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="all" class="text-sm">SMS, WhatsApp and Email</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">SMS uses your Marketing/SMS config. WhatsApp is sent via WasenderAPI. Email is sent from your app mail config.</p>
                        </div>
                        <div v-if="messageForm.channel === 'email' || messageForm.channel === 'all'" class="space-y-2">
                            <Label for="subject">Email subject</Label>
                            <input
                                id="subject"
                                v-model="messageForm.subject"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                placeholder="e.g. Update from Flyover Consultancy"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="custom_message">Message</Label>
                            <Textarea id="custom_message" v-model="messageForm.custom_message" placeholder="Type your message..." rows="4" />
                            <p v-if="messageForm.errors.custom_message" class="text-sm text-destructive">{{ messageForm.errors.custom_message }}</p>
                        </div>
                        <div v-if="showAttachmentSection" class="space-y-2">
                            <Label>Attachments</Label>
                            <input
                                ref="attachmentInputRef"
                                type="file"
                                multiple
                                class="flex h-9 w-full text-sm text-muted-foreground file:mr-2 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1 file:text-primary-foreground"
                                @change="onAttachmentChange"
                            />
                            <p class="text-xs text-muted-foreground">Max 5 files, 10 MB each. For Email they are attached to the message. For WhatsApp, attach them manually in the chat.</p>
                            <div v-if="attachmentFiles.length" class="flex flex-wrap items-center gap-2 mt-1">
                                <span v-for="(f, i) in attachmentFiles" :key="i" class="text-xs bg-muted px-2 py-1 rounded">{{ f.name }}</span>
                                <Button type="button" variant="ghost" size="sm" class="h-6 text-xs" @click="clearAttachments">Clear</Button>
                            </div>
                        </div>
                    </div>
                    <DialogFooter class="mt-4">
                        <Button type="button" variant="outline" @click="messageModal.open = false">Cancel</Button>
                        <Button
                            type="submit"
                            :disabled="messageForm.processing || !messageForm.custom_message?.trim() || (leadPhoneOptions.length > 0 && messageForm.channel !== 'email' && !messageForm.selected_phone?.trim()) || (messageForm.channel === 'email' && !lead?.email) || (messageForm.channel === 'all' && !lead?.email)"
                        >
                            <Loader2 v-if="messageForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Send
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <DeleteConfirmation v-model:open="deleteModal.open" :loading="deleteModal.loading" title="Delete Lead"
            :description="`Are you sure you want to delete '${lead.full_name}'? This action cannot be undone.`"
            @confirm="handleDelete" />
    </div>
</template>
