<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
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
    FileText,
    Download,
    FileDown,
    Send,
    CheckCircle,
    XCircle,
    Loader2,
    Globe,
    Calendar,
    Plane,
    ClipboardList,
    ChevronDown,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
});

const statusModal = ref({ open: false, loading: false });
const documentRequestModal = ref({ open: false, loading: false });

const statusForm = useForm({
    department_status: props.lead.accepted_department_transfer?.department_status || 'pending',
});

const documentRequestForm = useForm({
    document_type: '',
    message: '',
});

const pipelineSteps = [
    { key: 'pending', label: 'Pending' },
    { key: 'in_progress', label: 'In Progress' },
    { key: 'application_submitted', label: 'Submitted' },
    { key: 'under_review', label: 'Under Review' },
    { key: 'approved', label: 'Approved' },
    { key: 'rejected', label: 'Rejected' },
    { key: 'completed', label: 'Completed' },
];

/** Strict forward-only flow: current status → allowed next statuses (no reverse) */
const visaStatusFlow = {
    pending: ['in_progress'],
    in_progress: ['application_submitted'],
    application_submitted: ['under_review'],
    under_review: ['approved', 'rejected'],
    approved: ['completed'],
    rejected: [],
    completed: [],
};

const allDepartmentStatusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'application_submitted', label: 'Submitted' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'completed', label: 'Completed' },
];

const currentStatus = computed(() => props.lead.accepted_department_transfer?.department_status || 'pending');

const currentStepIndex = computed(() => {
    const idx = pipelineSteps.findIndex((s) => s.key === currentStatus.value);
    return idx >= 0 ? idx : 0;
});

/** Only statuses allowed as next step (forward-only, no reverse) */
const allowedNextStatusOptions = computed(() => {
    const current = currentStatus.value;
    const nextKeys = visaStatusFlow[current] || [];
    return allDepartmentStatusOptions.filter((o) => nextKeys.includes(o.value));
});

const canChangeStatus = computed(() => allowedNextStatusOptions.value.length > 0);

const getStatusBadge = (status) => {
    const statusConfig = {
        pending: { variant: 'secondary', label: 'Pending' },
        in_progress: { variant: 'default', label: 'In Progress' },
        application_submitted: { variant: 'default', label: 'Application Submitted' },
        under_review: { variant: 'default', label: 'Under Review' },
        approved: { variant: 'success', label: 'Approved' },
        rejected: { variant: 'destructive', label: 'Rejected' },
        completed: { variant: 'success', label: 'Completed' },
    };
    return statusConfig[status] || { variant: 'secondary', label: status };
};

const visaAppStatusBadge = (status) => {
    const map = {
        new: { variant: 'secondary', label: 'New' },
        applied: { variant: 'default', label: 'Applied' },
        approved: { variant: 'success', label: 'Approved' },
        rejected: { variant: 'destructive', label: 'Rejected' },
        cancelled: { variant: 'secondary', label: 'Cancelled' },
    };
    return map[status] || { variant: 'secondary', label: status };
};

const formatDate = (val) => {
    if (!val) return '—';
    const d = new Date(val);
    return Number.isNaN(d.getTime()) ? val : d.toLocaleDateString();
};

const openStatusModal = () => {
    const nextOptions = allowedNextStatusOptions.value;
    statusForm.department_status = nextOptions.length > 0 ? nextOptions[0].value : (props.lead.accepted_department_transfer?.department_status || 'pending');
    statusModal.value.open = true;
};

const updateStatus = () => {
    statusModal.value.loading = true;
    statusForm.patch(route('departments.visa.update-status', props.lead.id), {
        preserveScroll: true,
        onSuccess: () => {
            statusModal.value.open = false;
            statusModal.value.loading = false;
        },
        onError: () => {
            statusModal.value.loading = false;
        },
    });
};

const openDocumentRequestModal = () => {
    documentRequestForm.reset();
    documentRequestModal.value.open = true;
};

const requestDocument = () => {
    documentRequestModal.value.loading = true;
    documentRequestForm.post(route('departments.visa.request-document', props.lead.id), {
        preserveScroll: true,
        onSuccess: () => {
            documentRequestModal.value.open = false;
            documentRequestModal.value.loading = false;
            documentRequestForm.reset();
        },
        onError: () => {
            documentRequestModal.value.loading = false;
        },
    });
};

const exportPdf = () => {
    window.open(route('departments.visa.export-pdf', props.lead.id), '_blank');
};

const downloadDocuments = () => {
    window.location.href = route('departments.visa.download-documents', props.lead.id);
};

const visaApplications = computed(() => props.lead.visa_applications || []);
const travelVisaHistories = computed(() => props.lead.travel_visa_histories || []);

const page = usePage();
watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
}, { deep: true, immediate: true });
</script>

<template>

    <Head :title="`Visa Department - ${lead.full_name}`" />

    <div class="container mx-auto py-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <Button as-child variant="ghost" size="sm">
                    <Link :href="route('departments.visa.leads')">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to List
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ lead.full_name }}</h1>
                    <p class="text-xs text-muted-foreground mt-1">
                        Visa Department · {{ lead.email }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Button variant="outline" size="sm" @click="exportPdf">
                    <FileDown class="h-4 w-4 mr-2" />
                    Export PDF
                </Button>
                <Button variant="outline" size="sm" @click="downloadDocuments">
                    <Download class="h-4 w-4 mr-2" />
                    Download Documents
                </Button>
                <Button v-if="canChangeStatus" variant="outline" size="sm" @click="openStatusModal">
                    <CheckCircle class="h-4 w-4 mr-2" />
                    Update Status
                </Button>
                <Button size="sm" @click="openDocumentRequestModal">
                    <Send class="h-4 w-4 mr-2" />
                    Request Document
                </Button>
            </div>
        </div>

        <!-- Pipeline timeline -->
        <Card class="border-t-4 border-t-blue-500 dark:border-t-blue-600">
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <ClipboardList class="h-5 w-5" />
                    Application pipeline
                </CardTitle>
                <CardDescription class="text-xs">Current stage. Flow is forward-only—you cannot go back to a previous step.</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap items-center gap-0 overflow-x-auto pb-2">
                    <template v-for="(step, index) in pipelineSteps" :key="step.key">
                        <div class="flex items-center">
                            <div
                                class="flex min-w-[100px] flex-col items-center rounded-lg px-2 py-2 transition-colors md:min-w-[110px]"
                                :class="index <= currentStepIndex
                                    ? 'bg-primary/10 text-primary dark:bg-primary/20'
                                    : 'text-muted-foreground'"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
                                    :class="index < currentStepIndex
                                        ? 'bg-primary text-primary-foreground'
                                        : index === currentStepIndex
                                            ? 'bg-primary text-primary-foreground ring-2 ring-primary ring-offset-2'
                                            : 'bg-muted'"
                                >
                                    {{ index < currentStepIndex ? '✓' : index + 1 }}
                                </div>
                                <span class="mt-1 text-center text-xs font-medium">{{ step.label }}</span>
                            </div>
                            <div
                                v-if="index < pipelineSteps.length - 1"
                                class="h-0.5 w-4 shrink-0 bg-border md:w-6"
                                :class="index < currentStepIndex ? 'bg-primary/50' : ''"
                            />
                        </div>
                    </template>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                    <Badge
                        :variant="getStatusBadge(currentStatus).variant"
                        class="text-sm px-3 py-1"
                    >
                        {{ getStatusBadge(currentStatus).label }}
                    </Badge>
                    <span class="text-muted-foreground">
                        Managed by: {{ lead.department_manager?.name || 'Unassigned' }}
                    </span>
                    <span class="text-muted-foreground">
                        Transferred: {{ formatDate(lead.transferred_to_department_at) }}
                    </span>
                </div>
            </CardContent>
        </Card>

        <!-- Main grid -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Personal Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">Personal information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Full name</Label>
                            <p class="font-medium">{{ lead.full_name }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Email</Label>
                            <p class="font-medium">{{ lead.email }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Phone</Label>
                            <p class="font-medium">{{ lead.phone || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Date of birth</Label>
                            <p class="font-medium">{{ lead.date_of_birth || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Nationality</Label>
                            <p class="font-medium">{{ lead.nationality || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Current country</Label>
                            <p class="font-medium">{{ lead.current_country || '—' }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Visa / service info -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm flex items-center gap-2">
                        <Globe class="h-5 w-5" />
                        Visa & destination
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-muted-foreground">Service type</Label>
                            <p class="font-medium">{{ lead.service_type || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Destination country</Label>
                            <p class="font-medium">{{ lead.destination_country || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Passport number</Label>
                            <p class="font-medium">{{ lead.passport_number || '—' }}</p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Passport expiry</Label>
                            <p class="font-medium">{{ lead.passport_expiry_date || '—' }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Visa applications -->
            <Card v-if="visaApplications.length" class="lg:col-span-2">
                <CardHeader>
                    <CardTitle class="text-sm">Visa application(s)</CardTitle>
                    <CardDescription class="text-xs">Recorded visa applications for this lead</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="app in visaApplications"
                            :key="app.id"
                            class="rounded-lg border p-4 dark:border-border"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <Badge :variant="visaAppStatusBadge(app.status).variant">
                                    {{ visaAppStatusBadge(app.status).label }}
                                </Badge>
                                <span class="text-sm text-muted-foreground">
                                    {{ app.service?.name || '—' }}
                                </span>
                            </div>
                            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2 md:grid-cols-4">
                                <div>
                                    <Label class="text-xs text-muted-foreground">Application date</Label>
                                    <p class="font-medium">{{ formatDate(app.application_date) }}</p>
                                </div>
                                <div>
                                    <Label class="text-xs text-muted-foreground">Visa expiry</Label>
                                    <p class="font-medium">{{ formatDate(app.visa_expiry_date) }}</p>
                                </div>
                                <div>
                                    <Label class="text-xs text-muted-foreground">Type</Label>
                                    <p class="font-medium">{{ app.application_type || '—' }}</p>
                                </div>
                                <div v-if="app.has_previous_rejections">
                                    <Label class="text-xs text-muted-foreground">Previous rejections</Label>
                                    <p class="font-medium">{{ app.number_of_previous_rejections ?? 'Yes' }}</p>
                                </div>
                            </div>
                            <div v-if="app.rejection_reason" class="mt-3 rounded bg-destructive/10 p-2 text-sm text-destructive dark:bg-destructive/20">
                                {{ app.rejection_reason }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Travel visa history -->
            <Card v-if="travelVisaHistories.length" class="lg:col-span-2">
                <CardHeader>
                    <CardTitle class="text-sm flex items-center gap-2">
                        <Plane class="h-5 w-5" />
                        Travel & visa history
                    </CardTitle>
                    <CardDescription class="text-xs">Previous travel and visa records</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="pb-2 pr-4 font-medium">Country</th>
                                    <th class="pb-2 pr-4 font-medium">Year</th>
                                    <th class="pb-2 pr-4 font-medium">Visa type</th>
                                    <th class="pb-2 pr-4 font-medium">Person</th>
                                    <th class="pb-2 pr-4 font-medium">Issue / Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="h in travelVisaHistories"
                                    :key="h.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4 font-medium">{{ h.country || '—' }}</td>
                                    <td class="py-2 pr-4">{{ h.year || '—' }}</td>
                                    <td class="py-2 pr-4">{{ h.visa_type || '—' }}</td>
                                    <td class="py-2 pr-4">{{ h.person || '—' }}</td>
                                    <td class="py-2 pr-4 text-muted-foreground">
                                        {{ formatDate(h.visa_issue_date) }} / {{ formatDate(h.visa_expiry_date) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Documents -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">Documents ({{ lead.documents?.length || 0 }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="lead.documents?.length" class="space-y-2">
                        <div
                            v-for="document in lead.documents"
                            :key="document.id"
                            class="flex items-center justify-between rounded-lg border p-2 dark:border-border"
                        >
                            <div class="flex items-center gap-2">
                                <FileText class="h-4 w-4 shrink-0" />
                                <span class="text-sm truncate">{{ document.original_name || document.name }}</span>
                            </div>
                            <Button as-child variant="ghost" size="sm">
                                <a :href="`/storage/${document.file_path}`" target="_blank" rel="noopener">
                                    <Download class="h-4 w-4" />
                                </a>
                            </Button>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No documents uploaded yet.</p>
                </CardContent>
            </Card>

            <!-- Document requests -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">Pending document requests</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="lead.document_requests?.length" class="space-y-2">
                        <div
                            v-for="request in lead.document_requests"
                            :key="request.id"
                            class="rounded-lg border p-3 dark:border-border"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="font-medium text-sm">{{ request.document_type }}</p>
                                    <p v-if="request.message" class="text-xs text-muted-foreground">
                                        {{ request.message }}
                                    </p>
                                </div>
                                <Badge variant="secondary">Pending</Badge>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No pending document requests.</p>
                </CardContent>
            </Card>
        </div>

        <!-- Notes -->
        <Card v-if="lead.notes || lead.internal_notes">
            <CardHeader>
                <CardTitle class="text-sm">Notes</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="lead.notes">
                    <Label class="text-xs text-muted-foreground">Public notes</Label>
                    <p class="text-sm">{{ lead.notes }}</p>
                </div>
                <div v-if="lead.internal_notes">
                    <Label class="text-xs text-muted-foreground">Internal notes</Label>
                    <p class="text-sm">{{ lead.internal_notes }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Status modal: only next step(s) in flow (no reverse) -->
        <Dialog v-model:open="statusModal.open">
            <DialogContent v-if="statusModal.open">
                <DialogHeader>
                    <DialogTitle>Update department status</DialogTitle>
                    <DialogDescription>
                        Move to the next stage only. You cannot go back to a previous step.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Next status</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button type="button" variant="outline" class="w-full justify-between">
                                    <span>{{ allowedNextStatusOptions.find(o => o.value === statusForm.department_status)?.label || 'Select next status' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                <DropdownMenuRadioGroup v-model="statusForm.department_status">
                                    <DropdownMenuRadioItem
                                        v-for="option in allowedNextStatusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                        class="text-sm"
                                    >
                                        {{ option.label }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="statusModal.open = false">Cancel</Button>
                    <Button @click="updateStatus" :disabled="statusModal.loading">
                        <Loader2 v-if="statusModal.loading" class="mr-2 h-4 w-4 animate-spin" />
                        Update status
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Document request modal -->
        <Dialog v-model:open="documentRequestModal.open">
            <DialogContent v-if="documentRequestModal.open">
                <DialogHeader>
                    <DialogTitle>Request document</DialogTitle>
                    <DialogDescription>
                        Send a document request to the lead creator.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="requestDocument" class="space-y-4">
                    <div class="space-y-2">
                        <Label>Document type</Label>
                        <Input
                            v-model="documentRequestForm.document_type"
                            placeholder="e.g., Passport Copy, Bank Statement"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>Message (optional)</Label>
                        <Textarea
                            v-model="documentRequestForm.message"
                            placeholder="Additional instructions or notes..."
                            rows="3"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="documentRequestModal.open = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="documentRequestModal.loading">
                            <Loader2 v-if="documentRequestModal.loading" class="mr-2 h-4 w-4 animate-spin" />
                            Send request
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
