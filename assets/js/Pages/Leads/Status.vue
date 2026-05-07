<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';

const NoteDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/NoteDialog.vue'));
const CallHistoryDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/CallHistoryDialog.vue'));
const QuickPriorityDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/QuickPriorityDialog.vue'));
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useModules } from '@/composables/useModules';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatusTable from '@/Components/Leads/StatusTable.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuLabel,
} from '@/Components/ui/dropdown-menu';
import { TooltipProvider } from '@/Components/ui/tooltip';
import {
    ArrowLeft,
    ArrowRightLeft,
    Plus,
    History,
    Filter,
    Clock,
    Loader2,
    CalendarPlus,
    CalendarCheck,
    StickyNote,
    RefreshCw,
    CheckCircle,
    ChevronDown,
    Building,
    Package,
    Globe,
    UserCheck,
    Flag,
    X,
    Columns,
    Settings,
    Eye,
    Pencil,
    Phone,
    PhoneOutgoing,
    MessageCircle,
    Send,
    MoreHorizontal,
} from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import { toast } from 'vue-sonner';
import { Textarea } from '@/Components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
} from '@/Components/ui/dropdown-menu';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';

// Persistent layout removed to allow manual wrapping with key
// defineOptions({ 
//     layout: DashboardLayout
// });

const props = defineProps({
    leads: { type: Object, required: true },
    status: { type: String, required: true },
    statuses: { type: Array, default: () => [] },
    serviceTypes: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    destinationCountries: { type: Array, default: () => [] },
    currentStatusConfig: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    leadSortOptions: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission, hasRole } = usePermissions();
const { isModuleActive } = useModules();
const hasFollowUpModule = computed(() => isModuleActive('lead-followup-scheduling'));

const getCsrfToken = () =>
    page.props.csrf_token ||
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
    document.querySelector('meta[name="csrf-token"]')?.content ||
    '';

// Modal states
const deleteModal = ref({ open: false, lead: null, loading: false });
const logCallModal = ref({ open: false, lead: null, loading: false });
const callHistoryModal = ref({ open: false, lead: null, loading: false, history: [] });
const noteModal = ref({ open: false, lead: null, loading: false });
const scheduleModal = ref({ open: false, lead: null, loading: false });
const rescheduleModal = ref({ open: false, lead: null, followUp: null, loading: false });
const statusModal = ref({ open: false, lead: null, loading: false });
const priorityModal = ref({ open: false, lead: null, loading: false });

// Forms
const logCallForm = ref({
    call_type: 'outgoing',
    call_status: 'completed',
    duration_minutes: '',
    notes: '',
});

const noteForm = ref({
    note: '',
});

const scheduleForm = ref({
    follow_up_date: '',
    follow_up_time: '09:00',
    notes: '',
    follow_up_type: 'call',
    category: 'general',
    priority: 'medium',
    assigned_to: '',
});

const rescheduleForm = ref({
    follow_up_date: '',
    follow_up_time: '09:00',
    notes: '',
    follow_up_type: 'call',
    category: 'general',
    priority: 'medium',
    assigned_to: '',
});

// Completion modal
const completionModal = ref({ open: false, lead: null, followUp: null, loading: false });
const completionForm = ref({
    outcome: '',
    outcome_notes: '',
    duration_minutes: '',
    status: 'completed',
});

const quickStatusForm = ref({
    status: '',
});

const quickPriorityForm = ref({
    priority: '',
});

// Options
const callTypeOptions = [
    { value: 'incoming', label: 'Incoming' },
    { value: 'outgoing', label: 'Outgoing' },
];

const callStatusOptions = [
    { value: 'completed', label: 'Completed' },
    { value: 'no_answer', label: 'No Answer' },
    { value: 'busy', label: 'Busy' },
    { value: 'voicemail', label: 'Voicemail' },
    { value: 'wrong_number', label: 'Wrong Number' },
];

const baseStatusOptions = [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'busy', label: 'Busy' },
    { value: 'unavailable', label: 'Unavailable' },
    { value: 'no_answer', label: 'No Answer' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'not_qualified', label: 'Not Qualified' },
    { value: 'office_visited', label: 'Office Visited' },
    { value: 'follow_up', label: 'Follow Up' },
    { value: 'converted', label: 'Converted' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'lost', label: 'Lost' },
];

const statusOptions = computed(() => {
    if (hasFollowUpModule.value) {
        return baseStatusOptions;
    }

    return baseStatusOptions.filter((status) => !['follow_up', 'office_visited'].includes(status.value));
});

const priorityOptions = [
    { value: 'urgent', label: 'Urgent' },
    { value: 'high', label: 'High' },
    { value: 'normal', label: 'Normal' },
    { value: 'low', label: 'Low' },
];

// Utility functions
const formatPhone = (phone) => {
    if (!phone) return '';
    return phone.replace(/\D+/g, '');
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
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

const formatTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDateForInput = (date = new Date()) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const isOverdue = (dateString) => {
    if (!dateString) return false;
    return new Date(dateString) < new Date();
};

const STATUS_COLOR = Object.freeze({
        new: '#3b82f6',
        contacted: '#8b5cf6',
        busy: '#f59e0b',
        unavailable: '#ef4444',
        no_answer: '#6b7280',
        qualified: '#10b981',
        not_qualified: '#ef4444',
        office_visited: '#06b6d4',
        follow_up: '#10b981',
        documentation_pending: '#f59e0b',
        application_submitted: '#3b82f6',
        under_review: '#8b5cf6',
        interview_scheduled: '#ec4899',
        approved: '#10b981',

        in_process: '#3b82f6',
        on_hold: '#6b7280',
        converted: '#10b981',
        flight_done: '#10b981',
        rejected: '#ef4444',
        cancelled: '#6b7280',
        lost: '#ef4444',
    });
const getStatusColor = (status) => STATUS_COLOR[status] || '#6b7280';

// Call actions
const makeCall = (phone) => {
    window.location.href = `tel:${formatPhone(phone)}`;
};

const openWhatsApp = (phone) => {
    window.open(`https://wa.me/${formatPhone(phone)}`, '_blank');
};

// Log Call Modal
const openLogCallModal = (lead) => {
    logCallModal.value = { open: true, lead, loading: false };
    logCallForm.value = {
        call_type: 'outgoing',
        call_status: 'completed',
        duration_minutes: '',
        notes: '',
    };
};

const submitLogCall = async () => {
    if (!logCallModal.value.lead) return;
    logCallModal.value.loading = true;

    try {
        const response = await fetch(route('leads.call-history.store', logCallModal.value.lead.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                phone_number: logCallModal.value.lead.phone,
                call_type: logCallForm.value.call_type,
                status: logCallForm.value.call_status,
                duration: logCallForm.value.duration_minutes ? parseInt(logCallForm.value.duration_minutes) : null,
                notes: logCallForm.value.notes,
            }),
        });

        if (response.ok) {
            logCallModal.value.open = false;
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to log call:', error);
    } finally {
        logCallModal.value.loading = false;
    }
};

// Call History Modal
const openCallHistoryModal = async (lead) => {
    callHistoryModal.value = { open: true, lead, loading: true, history: [] };

    try {
        const response = await fetch(route('leads.call-history', lead.id), {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            const data = await response.json();
            callHistoryModal.value.history = data.call_history || [];
        }
    } catch (error) {
        console.error('Failed to fetch call history:', error);
    } finally {
        callHistoryModal.value.loading = false;
    }
};

// Quick status update
const updateStatus = async (leadId, newStatus) => {
    try {
        const response = await fetch(route('leads.quick-update', leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        });

        if (response.ok) {
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            const message = errorData?.error ?? errorData?.message ?? 'Failed to update status.';
            toast.error(message);
        }
    } catch (error) {
        console.error('Failed to update status:', error);
        toast.error('Failed to update status.');
    }
};

// Update services and packages
const updateServicesAndPackages = async (leadId, selectedServices, selectedPackages) => {
    try {
        const response = await fetch(route('leads.update-services-packages', leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                selected_services: selectedServices || [],
                selected_packages: selectedPackages || [],
            }),
        });

        if (response.ok) {
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('Failed to update services/packages:', errorData);
        }
    } catch (error) {
        console.error('Failed to update services/packages:', error);
    }
};

// Update assigned user
const updateAssignedTo = async (leadId, assignedTo) => {
    try {
        const response = await fetch(route('leads.quick-update', leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ assigned_to: assignedTo || null }),
        });

        if (response.ok) {
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('Failed to update assigned user:', errorData);
        }
    } catch (error) {
        console.error('Failed to update assigned user:', error);
    }
};

// Note Modal
const openNoteModal = (lead) => {
    noteModal.value = { open: true, lead, loading: false };
    noteForm.value.note = lead.latest_note?.note || '';
};

const submitNote = async () => {
    if (!noteModal.value.lead) return;
    noteModal.value.loading = true;

    try {
        const token = getCsrfToken();
        const response = await fetch(route('leads.notes.store', noteModal.value.lead.id), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ note: noteForm.value.note, _token: token }),
        });

        if (response.ok) {
            noteModal.value.open = false;
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to save note:', error);
    } finally {
        noteModal.value.loading = false;
    }
};

// Schedule Follow-up Modal
const openScheduleModal = (lead) => {
    scheduleModal.value = { open: true, lead, loading: false };
    scheduleForm.value = {
        follow_up_date: '',
        follow_up_time: '09:00',
        notes: '',
        follow_up_type: 'call',
        category: 'general',
        priority: 'medium',
        assigned_to: '',
    };
};

const submitSchedule = async () => {
    if (!scheduleModal.value.lead) return;
    scheduleModal.value.loading = true;

    try {
        const response = await fetch(route('leads.follow-up.store', scheduleModal.value.lead.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                follow_up_date: scheduleForm.value.follow_up_date,
                follow_up_time: scheduleForm.value.follow_up_time,
                notes: scheduleForm.value.notes || null,
                follow_up_type: scheduleForm.value.follow_up_type,
                category: scheduleForm.value.category,
                priority: scheduleForm.value.priority,
                assigned_to: scheduleForm.value.assigned_to || null,
            }),
        });

        if (response.ok) {
            scheduleModal.value.open = false;
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('Failed to schedule follow-up:', response.status, errorData);
            alert(errorData.message || `Failed to schedule follow-up. Status: ${response.status}`);
        }
    } catch (error) {
        console.error('Failed to schedule follow-up:', error);
        alert('An error occurred while scheduling the follow-up. Please try again.');
    } finally {
        scheduleModal.value.loading = false;
    }
};

// Reschedule Follow-up Modal
const openRescheduleModal = (lead, followUp) => {
    rescheduleModal.value = { open: true, lead, followUp, loading: false };
    if (followUp && followUp.follow_up_date) {
        const followUpDate = new Date(followUp.follow_up_date);
        rescheduleForm.value = {
            follow_up_date: followUpDate.toISOString().split('T')[0],
            follow_up_time: followUpDate.toTimeString().slice(0, 5),
            notes: followUp.notes || '',
            follow_up_type: followUp.follow_up_type || 'call',
            category: followUp.category || 'general',
            priority: followUp.priority || 'medium',
            assigned_to: followUp.assigned_to ? String(followUp.assigned_to) : '',
        };
    } else {
        rescheduleForm.value = {
            follow_up_date: '',
            follow_up_time: '09:00',
            notes: '',
            follow_up_type: 'call',
            category: 'general',
            priority: 'medium',
            assigned_to: '',
        };
    }
};

const submitReschedule = async () => {
    if (!rescheduleModal.value.lead || !rescheduleModal.value.followUp) return;
    rescheduleModal.value.loading = true;

    try {
        const response = await fetch(route('follow-ups.reschedule', rescheduleModal.value.followUp.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                follow_up_date: rescheduleForm.value.follow_up_date,
                follow_up_time: rescheduleForm.value.follow_up_time,
                notes: rescheduleForm.value.notes || null,
                follow_up_type: rescheduleForm.value.follow_up_type,
                category: rescheduleForm.value.category,
                priority: rescheduleForm.value.priority,
                assigned_to: rescheduleForm.value.assigned_to || null,
            }),
        });

        if (response.ok) {
            rescheduleModal.value.open = false;
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('Failed to reschedule follow-up:', response.status, errorData);
            alert(errorData.message || `Failed to reschedule follow-up. Status: ${response.status}`);
        }
    } catch (error) {
        console.error('Failed to reschedule follow-up:', error);
        alert('An error occurred while rescheduling the follow-up. Please try again.');
    } finally {
        rescheduleModal.value.loading = false;
    }
};

// Complete Follow-up
const openCompleteModal = (lead, followUp) => {
    if (!followUp || !hasPermission('edit-leads')) return;

    completionModal.value = { open: true, lead, followUp, loading: false };
    completionForm.value = {
        outcome: '',
        outcome_notes: '',
        duration_minutes: '',
        status: 'completed',
    };
};

const completeFollowUp = async () => {
    if (!completionModal.value.followUp || !hasPermission('edit-leads')) return;

    completionModal.value.loading = true;

    router.patch(
        route('follow-ups.complete', completionModal.value.followUp.id),
        completionForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                completionModal.value.open = false;
                router.reload({ only: ['leads'] });
            },
            onError: (errors) => {
                console.error('Failed to complete follow-up:', errors);
            },
            onFinish: () => {
                completionModal.value.loading = false;
            },
        }
    );
};

// Cancel Follow-up (mark as completed with cancelled note)
const cancelFollowUp = async (lead, followUp) => {
    if (!followUp || !hasPermission('edit-leads')) return;

    if (!confirm(`Are you sure you want to cancel this follow-up? It will be marked as completed.`)) {
        return;
    }

    try {
        // First update notes to indicate cancellation
        if (followUp.notes && !followUp.notes.includes('[Cancelled]')) {
            await fetch(route('follow-ups.update', followUp.id), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    notes: (followUp.notes || '') + '\n[Cancelled]',
                }),
            });
        }

        // Mark as completed to remove from active list
        const response = await fetch(route('follow-ups.complete', followUp.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('Failed to cancel follow-up:', response.status, errorData);
            alert(errorData.message || `Failed to cancel follow-up. Status: ${response.status}`);
        }
    } catch (error) {
        console.error('Failed to cancel follow-up:', error);
        alert('An error occurred while cancelling the follow-up. Please try again.');
    }
};

// Status Modal
const openStatusModal = (lead) => {
    statusModal.value = { open: true, lead, loading: false };
    quickStatusForm.value.status = lead.status || 'new';
};

const submitStatusChange = async () => {
    if (!statusModal.value.lead) return;
    statusModal.value.loading = true;

    try {
        const response = await fetch(route('leads.quick-update', statusModal.value.lead.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: quickStatusForm.value.status }),
        });

        if (response.ok) {
            statusModal.value.open = false;
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            const message = errorData?.error ?? errorData?.message ?? 'Failed to update status.';
            toast.error(message);
        }
    } catch (error) {
        console.error('Failed to update status:', error);
        toast.error('Failed to update status.');
    } finally {
        statusModal.value.loading = false;
    }
};

// Priority Modal
const openPriorityModal = (lead) => {
    priorityModal.value = { open: true, lead, loading: false };
    quickPriorityForm.value.priority = lead.priority || 'normal';
};

const submitPriorityChange = async () => {
    if (!priorityModal.value.lead) return;
    priorityModal.value.loading = true;

    try {
        const response = await fetch(route('leads.quick-update', priorityModal.value.lead.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ priority: quickPriorityForm.value.priority }),
        });

        if (response.ok) {
            priorityModal.value.open = false;
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to update priority:', error);
    } finally {
        priorityModal.value.loading = false;
    }
};

// Delete Modal
const openDeleteModal = (lead) => {
    deleteModal.value = { open: true, lead, loading: false };
};

const handleOrderNewService = (lead) => {
    router.post(route('leads.order-new-service', lead.id), {}, { preserveScroll: false });
};

const handleDelete = () => {
    if (!deleteModal.value.lead) return;
    deleteModal.value.loading = true;
    router.delete(route('leads.destroy', deleteModal.value.lead.id), {
        preserveScroll: true,
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};

// Copy lead info
const copySuccess = ref(false);
const copyLeadInfo = (lead) => {
    const info = `Name: ${lead.full_name}\nPhone: ${lead.phone || 'N/A'}\nEmail: ${lead.email || 'N/A'}\nStatus: ${lead.status?.replace(/_/g, ' ')}\nService: ${lead.service_type?.replace(/_/g, ' ') || 'N/A'}`;
    navigator.clipboard.writeText(info).then(() => {
        copySuccess.value = true;
        setTimeout(() => copySuccess.value = false, 2000);
    });
};

// Send email
const sendEmail = (email) => {
    if (email) {
        window.location.href = `mailto:${email}`;
    }
};

// Table columns - dynamic based on status
const allColumns = computed(() => {
    const baseColumns = [
        { key: 'full_name', label: 'Lead', sortable: true },
        { key: 'contact', label: 'Contact', sortable: false },
        { key: 'service_type', label: 'Service', sortable: true },
    ];

    // Add follow-up date column for follow_up and overdue_follow_ups status
    if (props.status === 'follow_up' || props.status === 'overdue_follow_ups') {
        baseColumns.push({ key: 'follow_up_date', label: 'Follow-up Date', sortable: true });
    }

    baseColumns.push(
        { key: 'status', label: 'Status', sortable: true },
        { key: 'priority', label: 'Priority', sortable: true },
        { key: 'assigned_to', label: 'Assigned', sortable: false },
        { key: 'notes', label: 'Notes', sortable: false },
        { key: 'packages', label: 'Packages', sortable: false },
        { key: 'services', label: 'Services', sortable: false }
    );

    baseColumns.push(
        { key: 'created_at', label: 'Created', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false, width: '200px' }
    );

    return baseColumns;
});

const columnVisibilityKey = computed(() => `leads-status-columns-${props.status}`);

const buildDefaultVisibility = (columns) => {
    const defaults = {};
    columns.forEach((column) => {
        defaults[column.key] = true;
    });
    return defaults;
};

const columnVisibility = ref({});

const loadColumnVisibility = () => {
    const columns = allColumns.value;
    const defaults = buildDefaultVisibility(columns);
    const stored = localStorage.getItem(columnVisibilityKey.value);

    if (stored) {
        try {
            const parsed = JSON.parse(stored);
            const merged = {};
            columns.forEach((column) => {
                if (Object.prototype.hasOwnProperty.call(parsed, column.key)) {
                    merged[column.key] = parsed[column.key] === true;
                } else {
                    merged[column.key] = true;
                }
            });
            merged.actions = true;
            columnVisibility.value = merged;
            return;
        } catch (error) {
            localStorage.removeItem(columnVisibilityKey.value);
        }
    }

    const defaultsWithActions = { ...defaults, actions: true };
    columnVisibility.value = defaultsWithActions;
};

const saveColumnVisibility = () => {
    localStorage.setItem(columnVisibilityKey.value, JSON.stringify(columnVisibility.value));
};

const toggleColumnVisibility = (key, checked) => {
    const next = { ...columnVisibility.value, [key]: checked === true };
    next.actions = true;
    columnVisibility.value = next;
    saveColumnVisibility();
};

const selectAllColumns = () => {
    const next = buildDefaultVisibility(allColumns.value);
    next.actions = true;
    columnVisibility.value = next;
    saveColumnVisibility();
};

const deselectAllColumns = () => {
    const next = buildDefaultVisibility(allColumns.value);
    Object.keys(next).forEach((key) => {
        next[key] = key === 'actions';
    });
    columnVisibility.value = next;
    saveColumnVisibility();
};

const resetColumns = () => {
    localStorage.removeItem(columnVisibilityKey.value);
    loadColumnVisibility();
};

const columns = computed(() => {
    return allColumns.value.filter((column) => columnVisibility.value[column.key] === true);
});

watch([() => props.status, allColumns], () => {
    loadColumnVisibility();
}, { immediate: true });

// Action visibility (same as All leads - shared localStorage key)
const baseActionButtons = [
    { key: 'view_details', label: 'View Details', icon: Eye },
    { key: 'edit_lead', label: 'Edit Lead', icon: Pencil },
    { key: 'call', label: 'Call', icon: Phone },
    { key: 'whatsapp', label: 'WhatsApp', icon: MessageCircle },
    { key: 'log_call', label: 'Log Call', icon: PhoneOutgoing },
    { key: 'call_history', label: 'Call History', icon: History },
    { key: 'add_note', label: 'Add/Edit Note', icon: StickyNote },
    { key: 'follow_up', label: 'Follow Up', icon: CalendarPlus },
    { key: 'transfer_lead', label: 'Transfer Lead', icon: ArrowRightLeft },
    { key: 'transfer_to_department', label: 'Transfer to Department', icon: Building },
    { key: 'send_email', label: 'Send Email', icon: Send },
    { key: 'more_actions', label: 'More Actions', icon: MoreHorizontal },
];

const actionButtons = computed(() => {
    if (hasFollowUpModule.value) {
        return baseActionButtons;
    }

    return baseActionButtons.filter((button) => button.key !== 'follow_up');
});

const getDefaultActionVisibility = () => {
    const stored = localStorage.getItem('leads-table-actions');
    if (stored) {
        try {
            const parsed = JSON.parse(stored);
            const defaults = {};
            let allFalse = true;
            actionButtons.value.forEach((btn) => {
                if (Object.prototype.hasOwnProperty.call(parsed, btn.key)) {
                    defaults[btn.key] = parsed[btn.key] === true;
                    if (parsed[btn.key] === true) allFalse = false;
                } else {
                    defaults[btn.key] = true;
                    allFalse = false;
                }
            });
            if (allFalse) {
                actionButtons.value.forEach((btn) => { defaults[btn.key] = true; });
                localStorage.setItem('leads-table-actions', JSON.stringify(defaults));
            }
            if (!hasFollowUpModule.value) {
                defaults.follow_up = false;
            }
            return defaults;
        } catch (e) {
            localStorage.removeItem('leads-table-actions');
        }
    }
    const defaults = {};
    actionButtons.value.forEach((btn) => { defaults[btn.key] = true; });
    defaults.follow_up = hasFollowUpModule.value;
    return defaults;
};

const actionVisibility = ref(getDefaultActionVisibility());
const actionVisibilityOpen = ref(false);

const saveActionVisibility = () => {
    localStorage.setItem('leads-table-actions', JSON.stringify(actionVisibility.value));
};

const toggleActionVisibility = (key, checked) => {
    const next = { ...actionVisibility.value, [key]: checked === true };
    actionVisibility.value = next;
    saveActionVisibility();
};

const selectAllActions = () => {
    const next = {};
    actionButtons.value.forEach((btn) => { next[btn.key] = true; });
    next.follow_up = hasFollowUpModule.value;
    actionVisibility.value = next;
    saveActionVisibility();
};

const deselectAllActions = () => {
    const next = {};
    actionButtons.value.forEach((btn) => { next[btn.key] = false; });
    next.follow_up = false;
    actionVisibility.value = next;
    saveActionVisibility();
};

const resetActions = () => {
    localStorage.removeItem('leads-table-actions');
    selectAllActions();
};

// Route for status list (overdue uses a separate URL)
const statusListUrl = computed(() =>
    props.status === 'overdue_follow_ups' ? route('leads.overdue-follow-ups') : route('leads.status', props.status)
);

// Sort handlers
const handleSort = ({ field, direction }) => {
    router.get(statusListUrl.value, {
        ...props.filters,
        sort: field,
        direction,
    }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(statusListUrl.value, { ...props.filters, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(statusListUrl.value, { ...props.filters, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const isFollowUpStatus = computed(() => props.status === 'follow_up');

const resolveFollowUpScope = (filters) => {
    if (!isFollowUpStatus.value) return '';
    if (filters?.follow_up_scope) return filters.follow_up_scope;
    if (filters?.follow_up_date_from || filters?.follow_up_date_to) return 'custom';
    return 'upcoming';
};

// Filter state - sync with props
const search = ref(props.filters?.search || '');
const serviceType = ref(props.filters?.service_type || '');
const branchId = ref(props.filters?.branch_id || '');
const assignedTo = ref(props.filters?.assigned_to || '');
const priority = ref(props.filters?.priority || '');
const destinationCountry = ref(props.filters?.destination_country || '');
const dateFrom = ref(isFollowUpStatus.value ? (props.filters?.follow_up_date_from || '') : (props.filters?.date_from || ''));
const dateTo = ref(isFollowUpStatus.value ? (props.filters?.follow_up_date_to || '') : (props.filters?.date_to || ''));
const followUpScope = ref(resolveFollowUpScope(props.filters));
const perPage = ref(props.filters?.per_page || '15');

// Computed properties for filtered select options
const validServiceTypes = computed(() => {
    return (props.serviceTypes || []).filter(t => t != null && t !== '' && t !== undefined);
});

const validPriorities = computed(() => {
    return (props.priorities || []).filter(p => p != null && p !== '' && p !== undefined);
});

const validDestinationCountries = computed(() => {
    return (props.destinationCountries || []).filter(c => c != null && c !== '' && c !== undefined);
});

const validBranches = computed(() => {
    return (props.branches || []).filter(b => b?.id);
});

const validUsers = computed(() => {
    return (props.users || []).filter(u => u?.id);
});

const syncFiltersFromProps = (filters) => {
    const resolvedFilters = filters || {};

    search.value = resolvedFilters.search || '';
    serviceType.value = resolvedFilters.service_type || '';
    branchId.value = resolvedFilters.branch_id || '';
    assignedTo.value = resolvedFilters.assigned_to || '';
    priority.value = resolvedFilters.priority || '';
    destinationCountry.value = resolvedFilters.destination_country || '';
    perPage.value = resolvedFilters.per_page || '15';

    if (isFollowUpStatus.value) {
        dateFrom.value = resolvedFilters.follow_up_date_from || '';
        dateTo.value = resolvedFilters.follow_up_date_to || '';
        followUpScope.value = resolveFollowUpScope(resolvedFilters);
    } else {
        dateFrom.value = resolvedFilters.date_from || '';
        dateTo.value = resolvedFilters.date_to || '';
        followUpScope.value = '';
    }
};

// Watch for prop changes and update local state (but only if filters actually changed)
watch(() => props.filters, (newFilters, oldFilters) => {
    if (newFilters && (!oldFilters || JSON.stringify(newFilters) !== JSON.stringify(oldFilters))) {
        syncFiltersFromProps(newFilters);
    }
}, { deep: true, immediate: true });

const buildFilterPayload = () => {
    const payload = {
        search: search.value || undefined,
        service_type: serviceType.value || undefined,
        branch_id: branchId.value || undefined,
        assigned_to: assignedTo.value || undefined,
        priority: priority.value || undefined,
        destination_country: destinationCountry.value || undefined,
        per_page: perPage.value || undefined,
    };

    if (isFollowUpStatus.value) {
        payload.follow_up_scope = followUpScope.value || undefined;
        payload.follow_up_date_from = dateFrom.value || undefined;
        payload.follow_up_date_to = dateTo.value || undefined;
    } else {
        payload.date_from = dateFrom.value || undefined;
        payload.date_to = dateTo.value || undefined;
    }

    return payload;
};

// Apply filters
const applyFilters = (options = {}) => {
    router.get(statusListUrl.value, buildFilterPayload(), {
        preserveState: options.preserveState ?? true,
        preserveScroll: options.preserveScroll ?? true,
    });
};

const inferFollowUpScopeFromDates = () => {
    if (!isFollowUpStatus.value) return;

    const today = formatDateForInput();

    if (!dateFrom.value && !dateTo.value) {
        followUpScope.value = 'all';
        return;
    }

    if (dateFrom.value === today && dateTo.value === today) {
        followUpScope.value = 'today';
        return;
    }

    if (dateFrom.value === today && !dateTo.value) {
        followUpScope.value = 'upcoming';
        return;
    }

    followUpScope.value = 'custom';
};

const setFollowUpScope = (scope) => {
    followUpScope.value = scope;

    const today = formatDateForInput();

    if (scope === 'today') {
        dateFrom.value = today;
        dateTo.value = today;
    } else if (scope === 'upcoming') {
        dateFrom.value = today;
        dateTo.value = '';
    } else if (scope === 'all') {
        dateFrom.value = '';
        dateTo.value = '';
    }

    applyFilters();
};

const handleDateFilterChange = () => {
    if (isFollowUpStatus.value) {
        inferFollowUpScopeFromDates();
    }

    applyFilters();
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    serviceType.value = '';
    branchId.value = '';
    assignedTo.value = '';
    priority.value = '';
    destinationCountry.value = '';
    perPage.value = '15';

    if (isFollowUpStatus.value) {
        followUpScope.value = 'upcoming';
        dateFrom.value = formatDateForInput();
        dateTo.value = '';
        applyFilters({ preserveState: false });
        return;
    }

    dateFrom.value = '';
    dateTo.value = '';
    applyFilters({ preserveState: false, preserveScroll: false });
};

// Debounced search
const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 500);

watch(search, () => {
    debouncedSearch();
});

// Watch for status changes and update filters from props
watch(() => props.status, (newStatus, oldStatus) => {
    if (newStatus !== oldStatus && oldStatus !== undefined) {
        // When status changes, sync filters from props
        if (props.filters) {
            syncFiltersFromProps(props.filters);
        } else {
            search.value = '';
            serviceType.value = '';
            branchId.value = '';
            assignedTo.value = '';
            priority.value = '';
            destinationCountry.value = '';
            dateFrom.value = '';
            dateTo.value = '';
            followUpScope.value = '';
            perPage.value = '15';
        }
    }
}, { immediate: false });

// Status color
const statusColor = computed(() => props.currentStatusConfig?.color || '#6b7280');

// Show history link for certain statuses
const showHistoryLink = computed(() => {
    return ['follow_up', 'overdue_follow_ups', 'office_visited'].includes(props.status);
});
</script>

<template>

    <Head :title="currentStatusConfig?.title || 'Leads'" />

    <DashboardLayout :key="props.status">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white"
                        :style="{ background: `linear-gradient(135deg, ${statusColor} 0%, ${statusColor}dd 100%)` }">
                        <Filter class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ currentStatusConfig?.title }}</h1>
                        <p class="text-sm text-muted-foreground mt-1">{{ currentStatusConfig?.description }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <Link :href="route('leads.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4">
                        <ArrowLeft class="h-4 w-4" />
                        All Leads
                    </Link>
                    <template v-if="status === 'follow_up'">
                        <Link :href="route('follow-ups.history', { type: 'followup', date: 'all' })"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4">
                            <History class="h-4 w-4" />
                            All Follow-up History
                        </Link>
                        <Link :href="route('follow-ups.history', { type: 'followup', date: 'today' })"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4">
                            <Clock class="h-4 w-4" />
                            Today's History
                        </Link>
                        <Link :href="route('leads.status', { status: 'follow_up', follow_up_scope: 'all' })"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4">
                            <CalendarCheck class="h-4 w-4" />
                            All Follow-ups
                        </Link>
                    </template>
                    <Link v-else-if="showHistoryLink"
                        :href="route('leads.status.history', status === 'overdue_follow_ups' ? 'follow_up' : status)"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4">
                        <History class="h-4 w-4" />
                        History (Today)
                    </Link>
                    <Link v-if="hasPermission('create-leads')" :href="route('leads.create')"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-primary/90 h-9 px-4">
                        <Plus class="h-4 w-4" />
                        Add Lead
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="h-4 w-0.5 rounded" :style="{ backgroundColor: statusColor }"></div>
                        Search & Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-3">
                        <div v-if="status === 'follow_up'" class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-muted-foreground">Follow-up range:</span>
                            <div class="flex flex-wrap items-center gap-2">
                                <Button size="sm" :variant="followUpScope === 'today' ? 'default' : 'outline'"
                                    class="h-7 px-2 text-[11px]" @click="setFollowUpScope('today')">
                                    Today
                                </Button>
                                <Button size="sm" :variant="followUpScope === 'upcoming' ? 'default' : 'outline'"
                                    class="h-7 px-2 text-[11px]" @click="setFollowUpScope('upcoming')">
                                    Upcoming
                                </Button>
                                <Button size="sm" :variant="followUpScope === 'all' ? 'default' : 'outline'"
                                    class="h-7 px-2 text-[11px]" @click="setFollowUpScope('all')">
                                    All
                                </Button>
                                <Link :href="route('leads.overdue-follow-ups')"
                                    class="inline-flex h-7 items-center justify-center rounded-md border border-input bg-background px-2 text-[11px] font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                                    Overdue
                                </Link>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Search Input -->
                            <div class="flex-1 min-w-[200px]">
                                <Input v-model="search" placeholder="Search anything (name, email, phone, passport, notes, address...)"
                                    class="h-9 text-xs" />
                            </div>

                            <!-- Service Type Dropdown -->
                            <DropdownMenu v-if="serviceTypes && serviceTypes.length > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-9 text-xs justify-between min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            <Package class="h-3.5 w-3.5" />
                                            <span>{{serviceType ? serviceType.replace(/_/g, ' ').replace(/\b\w/g, l =>
                                                l.toUpperCase()) : 'All Services'}}</span>
                                        </div>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-56 max-h-[300px] overflow-y-auto">
                                    <DropdownMenuLabel class="text-xs">Service Type</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="serviceType = ''; applyFilters();">
                                        All Services
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem v-for="type in validServiceTypes" :key="`service-${type}`"
                                        class="text-xs cursor-pointer"
                                        @click="serviceType = String(type); applyFilters();">
                                        {{type ? type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''}}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Branch Dropdown -->
                            <DropdownMenu v-if="hasRole('admin') && branches.length > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-9 text-xs justify-between min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            <Building class="h-3.5 w-3.5" />
                                            <span>{{branchId && validBranches.find(b => String(b.id) === branchId) ?
                                                validBranches.find(b => String(b.id) === branchId).name : 'All Branches'
                                                }}</span>
                                        </div>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-56 max-h-[300px] overflow-y-auto">
                                    <DropdownMenuLabel class="text-xs">Branch</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="branchId = ''; applyFilters();">
                                        All Branches
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem v-for="branch in validBranches" :key="`branch-${branch.id}`"
                                        class="text-xs cursor-pointer"
                                        @click="branchId = String(branch.id); applyFilters();">
                                        {{ branch?.name || '' }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Assigned To Dropdown -->
                            <DropdownMenu v-if="hasRole('admin') && users.length > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-9 text-xs justify-between min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            <UserCheck class="h-3.5 w-3.5" />
                                            <span>{{assignedTo === 'unassigned' ? 'Unassigned' : assignedTo &&
                                                validUsers.find(u =>
                                                    String(u.id) === assignedTo) ? validUsers.find(u => String(u.id) ===
                                                        assignedTo).name : 'All Users'}}</span>
                                        </div>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-56 max-h-[300px] overflow-y-auto">
                                    <DropdownMenuLabel class="text-xs">Assigned To</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="assignedTo = ''; applyFilters();">
                                        All Users
                                    </DropdownMenuItem>
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="assignedTo = 'unassigned'; applyFilters();">
                                        Unassigned
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem v-for="user in validUsers" :key="`user-${user.id}`"
                                        class="text-xs cursor-pointer"
                                        @click="assignedTo = String(user.id); applyFilters();">
                                        {{ user?.name || '' }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Priority Dropdown -->
                            <DropdownMenu v-if="priorities && priorities.length > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-9 text-xs justify-between min-w-[140px]">
                                        <div class="flex items-center gap-2">
                                            <Flag class="h-3.5 w-3.5" />
                                            <span>{{ priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : 'All Priorities' }}</span>
                                        </div>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-48">
                                    <DropdownMenuLabel class="text-xs">Priority</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="priority = ''; applyFilters();">
                                        All Priorities
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem v-for="p in validPriorities" :key="`priority-${p}`"
                                        class="text-xs cursor-pointer" @click="priority = String(p); applyFilters();">
                                        {{ p ? p.charAt(0).toUpperCase() + p.slice(1) : '' }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Destination Country Dropdown -->
                            <DropdownMenu v-if="destinationCountries.length > 0">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-9 text-xs justify-between min-w-[160px]">
                                        <div class="flex items-center gap-2">
                                            <Globe class="h-3.5 w-3.5" />
                                            <span>{{ destinationCountry || 'All Countries' }}</span>
                                        </div>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-56 max-h-[300px] overflow-y-auto">
                                    <DropdownMenuLabel class="text-xs">Destination Country</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-xs cursor-pointer"
                                        @click="destinationCountry = ''; applyFilters();">
                                        All Countries
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem v-for="country in validDestinationCountries"
                                        :key="`country-${country}`" class="text-xs cursor-pointer"
                                        @click="destinationCountry = String(country); applyFilters();">
                                        {{ country || '' }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Date From -->
                            <div class="flex items-center gap-2">
                                <Input v-model="dateFrom" type="date" class="h-9 text-xs w-[140px]"
                                    @change="handleDateFilterChange" placeholder="Date From" />
                            </div>

                            <!-- Date To -->
                            <div class="flex items-center gap-2">
                                <Input v-model="dateTo" type="date" class="h-9 text-xs w-[140px]"
                                    @change="handleDateFilterChange" placeholder="Date To" />
                            </div>

                            <!-- Active Filters Badge & Clear -->
                            <div v-if="serviceType || branchId || assignedTo || priority || destinationCountry || dateFrom || dateTo"
                                class="flex items-center gap-2 ml-auto">
                                <Badge variant="secondary" class="text-xs">
                                    {{ [serviceType, branchId, assignedTo, priority, destinationCountry, dateFrom,
                                        dateTo].filter(Boolean).length }} filter{{ [serviceType, branchId, assignedTo,
                                        priority,
                                        destinationCountry, dateFrom, dateTo].filter(Boolean).length === 1 ? '' : 's' }}
                                    active
                                </Badge>
                                <Button variant="outline" size="sm" class="h-8 text-xs" @click="clearFilters">
                                    <X class="mr-1.5 h-3.5 w-3.5" />
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>



            <!-- Table header: same layout as All leads (Leads/Index.vue) -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ currentStatusConfig?.title }}</h1>
                    <p class="text-xs text-muted-foreground">
                        {{ leads?.total ?? 0 }} {{ (leads?.total ?? 0) === 1 ? 'result' : 'results' }}. Manage and
                        track leads in this status.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="status === 'follow_up' || status === 'overdue_follow_ups'" variant="outline" size="sm" class="h-8 text-xs"
                        @click="router.get(statusListUrl, { ...filters, sort: 'follow_up_date', direction: 'asc' }, { preserveState: true })">
                        <Clock class="mr-1.5 h-3.5 w-3.5" />
                        Sort by Date
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm" class="h-8 text-xs">
                                <Columns class="mr-1.5 h-3.5 w-3.5" />
                                Columns
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <div class="px-2 py-1.5">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs font-semibold">Toggle Columns</div>
                                    <div class="flex items-center gap-1">
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="selectAllColumns">
                                            Select All
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="deselectAllColumns">
                                            Deselect All
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="resetColumns">
                                            Reset
                                        </Button>
                                    </div>
                                </div>
                                <div class="space-y-1.5 max-h-[400px] overflow-y-auto">
                                    <label v-for="col in allColumns.filter(c => c.key !== 'actions')" :key="col.key"
                                        class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                        <Checkbox :checked="columnVisibility[col.key] === true"
                                            @update:checked="(checked) => toggleColumnVisibility(col.key, checked)" />
                                        <span class="text-xs">{{ col.label }}</span>
                                    </label>
                                </div>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <!-- Actions visibility (same as All leads) -->
                    <DropdownMenu v-model:open="actionVisibilityOpen">
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm" class="h-8 text-xs">
                                <Settings class="mr-1.5 h-3.5 w-3.5" />
                                Actions
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <div class="px-2 py-1.5">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs font-semibold">Toggle Actions</div>
                                    <div class="flex items-center gap-1">
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="selectAllActions">
                                            All
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="deselectAllActions">
                                            None
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-6 text-[10px] px-2"
                                            @click="resetActions">
                                            Reset
                                        </Button>
                                    </div>
                                </div>
                                <div class="space-y-1.5 max-h-[400px] overflow-y-auto">
                                    <label v-for="btn in actionButtons" :key="btn.key"
                                        class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                        <Checkbox :checked="actionVisibility[btn.key] === true"
                                            @update:checked="(checked) => toggleActionVisibility(btn.key, checked)" />
                                        <component :is="btn.icon" class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span class="text-xs">{{ btn.label }}</span>
                                    </label>
                                </div>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button v-if="hasPermission('create-leads')" as-child size="sm" class="h-8 text-xs">
                        <Link :href="route('leads.create')">
                            <Plus class="mr-1.5 h-3.5 w-3.5" />
                            Add Lead
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Data Table (same design as All leads: no Card, TooltipProvider + table) -->
            <TooltipProvider>
                <StatusTable v-if="leads?.data && leads.data.length > 0" :status="status" :leads="leads"
                    :filters="filters" :columns="columns" :status-options="statusOptions" :users="users"
                    :services="services" :packages="packages" :format-date="formatDate"
                    :format-date-time="formatDateTime" :is-overdue="isOverdue" :get-status-color="getStatusColor"
                    :action-visibility="actionVisibility"
                    @sort="handleSort" @page-change="handlePageChange" @per-page-change="handlePerPageChange"
                    @make-call="makeCall" @open-whatsapp="openWhatsApp" @log-call="openLogCallModal"
                    @call-history="openCallHistoryModal" @open-complete="openCompleteModal"
                    @open-reschedule="openRescheduleModal" @cancel-follow-up="cancelFollowUp"
                    @open-status="openStatusModal" @open-priority="openPriorityModal" @open-note="openNoteModal"
                    @open-schedule="openScheduleModal" @copy-lead="copyLeadInfo" @send-email="sendEmail"
                    @delete-lead="openDeleteModal" @update-status="updateStatus" @update-assigned="updateAssignedTo"
                    @update-services-packages="updateServicesAndPackages" @order-new-service="handleOrderNewService" />
                <div v-else class="flex flex-col items-center justify-center py-16 rounded-lg border bg-card">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-muted mb-4">
                        <Filter class="h-8 w-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-sm font-semibold mb-1">No leads found</h3>
                    <p class="text-xs text-muted-foreground text-center max-w-sm mb-4">
                        {{ search || serviceType || branchId || assignedTo || priority || destinationCountry ||
                            dateFrom ||
                            dateTo
                            ? 'Try adjusting your filters to see more results.'
                            : `No leads with status "${currentStatusConfig?.title || status}" found.` }}
                    </p>
                    <div class="flex gap-2">
                        <Button
                            v-if="search || serviceType || branchId || assignedTo || priority || destinationCountry || dateFrom || dateTo"
                            variant="outline" size="sm" class="h-8 text-xs" @click="clearFilters">
                            Clear Filters
                        </Button>
                        <Button v-if="hasPermission('create-leads')" as-child size="sm" class="h-8 text-xs">
                            <Link :href="route('leads.create')">
                                <Plus class="mr-1.5 h-3.5 w-3.5" />
                                Add Lead
                            </Link>
                        </Button>
                    </div>
                </div>
            </TooltipProvider>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation v-model:open="deleteModal.open" :loading="deleteModal.loading" title="Delete Lead"
            :description="`Are you sure you want to delete '${deleteModal.lead?.full_name}'? This action cannot be undone.`"
            @confirm="handleDelete" />

        <!-- Log Call Modal -->
        <Dialog v-model:open="logCallModal.open">
            <DialogContent v-if="logCallModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm">Log Call</DialogTitle>
                    <DialogDescription class="text-xs">
                        Record a call with {{ logCallModal.lead?.full_name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-3 py-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Call Type</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{callTypeOptions.find(opt => opt.value === logCallForm.call_type)?.label
                                            ||
                                            'Select call type...'}}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="logCallForm.call_type">
                                        <DropdownMenuRadioItem v-for="opt in callTypeOptions" :key="opt.value"
                                            :value="opt.value" class="text-xs">
                                            {{ opt.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Call Status</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{callStatusOptions.find(opt => opt.value ===
                                            logCallForm.call_status)?.label ||
                                            'Select status...'}}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="logCallForm.call_status">
                                        <DropdownMenuRadioItem v-for="opt in callStatusOptions" :key="opt.value"
                                            :value="opt.value" class="text-xs">
                                            {{ opt.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Duration (minutes)</Label>
                        <Input v-model="logCallForm.duration_minutes" type="number" min="0" placeholder="Enter duration"
                            class="h-8 text-xs" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Notes</Label>
                        <Textarea v-model="logCallForm.notes" placeholder="Call notes..."
                            class="text-xs min-h-[80px]" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="logCallModal.open = false">
                        Cancel
                    </Button>
                    <Button size="sm" class="h-8 text-xs" @click="submitLogCall" :disabled="logCallModal.loading">
                        <Loader2 v-if="logCallModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Save Call Log
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Call History Modal (lazy chunk) -->
        <CallHistoryDialog v-if="callHistoryModal.open"
            :open="callHistoryModal.open" :lead="callHistoryModal.lead" :loading="callHistoryModal.loading"
            :history="callHistoryModal.history"
            @update:open="(v) => callHistoryModal.open = v" />

        <!-- Add/Edit Note Modal (lazy chunk) -->
        <NoteDialog v-if="noteModal.open"
            :open="noteModal.open" :lead="noteModal.lead" :loading="noteModal.loading"
            :form="noteForm"
            @update:open="(v) => noteModal.open = v" @submit="submitNote" />

        <!-- Schedule Follow-up Modal -->
        <Dialog v-model:open="scheduleModal.open">
            <DialogContent v-if="scheduleModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm flex items-center gap-2">
                        <CalendarPlus class="h-4 w-4 text-green-500" />
                        Schedule Follow-up
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Schedule a follow-up for {{ scheduleModal.lead?.full_name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Date *</Label>
                            <Input v-model="scheduleForm.follow_up_date" type="date" class="h-8 text-xs" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Time</Label>
                            <Input v-model="scheduleForm.follow_up_time" type="time" class="h-8 text-xs" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Type</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{ scheduleForm.follow_up_type ?
                                            scheduleForm.follow_up_type.charAt(0).toUpperCase() +
                                            scheduleForm.follow_up_type.slice(1) : 'Call' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="scheduleForm.follow_up_type">
                                        <DropdownMenuRadioItem value="call" class="text-xs">Call</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-xs">Email
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="meeting" class="text-xs">Meeting
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="other" class="text-xs">Other
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Category</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{scheduleForm.category ? scheduleForm.category.split('_').map(w =>
                                            w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'General'}}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="scheduleForm.category">
                                        <DropdownMenuRadioItem value="initial_contact" class="text-xs">Initial Contact
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="qualification" class="text-xs">Qualification
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="documentation" class="text-xs">Documentation
                                        </DropdownMenuRadioItem>

                                        <DropdownMenuRadioItem value="application" class="text-xs">Application
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="general" class="text-xs">General
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Priority</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{ scheduleForm.priority ? scheduleForm.priority.charAt(0).toUpperCase() +
                                            scheduleForm.priority.slice(1) : 'Medium' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="scheduleForm.priority">
                                        <DropdownMenuRadioItem value="low" class="text-xs">Low</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="medium" class="text-xs">Medium
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="high" class="text-xs">High</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="urgent" class="text-xs">Urgent
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Assign To</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{scheduleForm.assigned_to ? users.find(u => String(u.id) ===
                                            scheduleForm.assigned_to)?.name || 'Current User' : 'Current User'}}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="scheduleForm.assigned_to">
                                        <DropdownMenuRadioItem value="" class="text-xs">Current User
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="user in users" :key="user.id"
                                            :value="String(user.id)" class="text-xs">
                                            {{ user.name }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Notes</Label>
                        <Textarea v-model="scheduleForm.notes" placeholder="Follow-up notes..."
                            class="text-xs min-h-[80px]" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="scheduleModal.open = false">
                        Cancel
                    </Button>
                    <Button size="sm" class="h-8 text-xs" @click="submitSchedule"
                        :disabled="scheduleModal.loading || !scheduleForm.follow_up_date">
                        <Loader2 v-if="scheduleModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Schedule
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reschedule Follow-up Modal -->
        <Dialog v-model:open="rescheduleModal.open">
            <DialogContent v-if="rescheduleModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm flex items-center gap-2">
                        <RefreshCw class="h-4 w-4 text-blue-500" />
                        Reschedule Follow-up
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Reschedule follow-up for {{ rescheduleModal.lead?.full_name }}
                        <span v-if="rescheduleModal.followUp">
                            (Current: {{ formatDateTime(rescheduleModal.followUp.follow_up_date) }})
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">New Date *</Label>
                            <Input v-model="rescheduleForm.follow_up_date" type="date"
                                :min="new Date().toISOString().split('T')[0]" class="h-8 text-xs" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">New Time *</Label>
                            <Input v-model="rescheduleForm.follow_up_time" type="time" class="h-8 text-xs" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Type</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{ rescheduleForm.follow_up_type ?
                                            rescheduleForm.follow_up_type.charAt(0).toUpperCase() +
                                            rescheduleForm.follow_up_type.slice(1) : 'Call' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="rescheduleForm.follow_up_type">
                                        <DropdownMenuRadioItem value="call" class="text-xs">Call</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-xs">Email
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="meeting" class="text-xs">Meeting
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="other" class="text-xs">Other
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Category</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{rescheduleForm.category ? rescheduleForm.category.split('_').map(w =>
                                            w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'General'}}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="rescheduleForm.category">
                                        <DropdownMenuRadioItem value="initial_contact" class="text-xs">Initial Contact
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="qualification" class="text-xs">Qualification
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="documentation" class="text-xs">Documentation
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="payment" class="text-xs">Payment
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="application" class="text-xs">Application
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="general" class="text-xs">General
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Priority</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{ rescheduleForm.priority ?
                                            rescheduleForm.priority.charAt(0).toUpperCase() +
                                            rescheduleForm.priority.slice(1) : 'Medium' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="rescheduleForm.priority">
                                        <DropdownMenuRadioItem value="low" class="text-xs">Low</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="medium" class="text-xs">Medium
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="high" class="text-xs">High</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="urgent" class="text-xs">Urgent
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Assign To</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                        <span>{{rescheduleForm.assigned_to ? users.find(u => String(u.id) ===
                                            rescheduleForm.assigned_to)?.name || 'Current User' : 'Current User'
                                            }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="rescheduleForm.assigned_to">
                                        <DropdownMenuRadioItem value="" class="text-xs">Current User
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="user in users" :key="user.id"
                                            :value="String(user.id)" class="text-xs">
                                            {{ user.name }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Notes</Label>
                        <Textarea v-model="rescheduleForm.notes" placeholder="Reschedule notes..."
                            class="text-xs min-h-[80px]" />
                    </div>
                    <div class="rounded-md bg-blue-50 p-2 border border-blue-200">
                        <p class="text-[10px] text-blue-700">
                            <strong>Note:</strong> This will mark the current follow-up as completed and create a new
                            one with
                            the new date/time.
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="rescheduleModal.open = false">
                        Cancel
                    </Button>
                    <Button size="sm" class="h-8 text-xs" @click="submitReschedule"
                        :disabled="rescheduleModal.loading || !rescheduleForm.follow_up_date || !rescheduleForm.follow_up_time">
                        <Loader2 v-if="rescheduleModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Reschedule
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Quick Status Change Modal -->
        <Dialog v-model:open="statusModal.open">
            <DialogContent v-if="statusModal.open" class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle class="text-sm flex items-center gap-2">
                        <RefreshCw class="h-4 w-4 text-purple-500" />
                        Change Status
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Update status for {{ statusModal.lead?.full_name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Select Status</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline"
                                    class="w-full h-9 text-xs justify-between hover:bg-accent/50 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <div v-if="quickStatusForm.status" class="w-2.5 h-2.5 rounded-full shadow-sm"
                                            :style="{ backgroundColor: getStatusColor(quickStatusForm.status) }"></div>
                                        <span class="font-medium">
                                            {{statusOptions.find(s => s.value === quickStatusForm.status)?.label || 'Select status' }}
                                        </span>
                                    </div>
                                    <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="max-h-[300px] overflow-y-auto p-1.5"
                                style="width: var(--radix-dropdown-menu-trigger-width);">
                                <DropdownMenuRadioGroup v-model="quickStatusForm.status">
                                    <DropdownMenuRadioItem v-for="status in statusOptions" :key="status.value"
                                        :value="status.value"
                                        class="text-xs px-3 py-2.5 rounded-md transition-all cursor-pointer">
                                        <div class="flex items-center justify-between w-full gap-3">
                                            <div class="flex items-center gap-2.5 flex-1">
                                                <div class="w-2.5 h-2.5 rounded-full shadow-sm shrink-0"
                                                    :style="{ backgroundColor: getStatusColor(status.value) }"></div>
                                                <span>{{ status.label }}</span>
                                            </div>
                                        </div>
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="statusModal.open = false">
                        Cancel
                    </Button>
                    <Button size="sm" class="h-8 text-xs" @click="submitStatusChange" :disabled="statusModal.loading">
                        <Loader2 v-if="statusModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Update Status
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Quick Priority Change Modal (lazy chunk) -->
        <QuickPriorityDialog v-if="priorityModal.open"
            :open="priorityModal.open" :lead="priorityModal.lead" :loading="priorityModal.loading"
            :form="quickPriorityForm" :priority-options="priorityOptions"
            @update:open="(v) => priorityModal.open = v" @submit="submitPriorityChange" />

        <!-- Completion Modal -->
        <Dialog v-model:open="completionModal.open">
            <DialogContent v-if="completionModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm flex items-center gap-2">
                        <CheckCircle class="h-4 w-4 text-green-500" />
                        Complete Follow-up
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Mark this follow-up as completed and record the outcome
                    </DialogDescription>
                </DialogHeader>
                <div class="py-3 space-y-3">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Outcome *</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{completionForm.outcome ? completionForm.outcome.split('_').map(w =>
                                        w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Select outcome...'
                                        }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="completionForm.outcome">
                                    <DropdownMenuRadioItem value="contacted" class="text-xs">Contacted
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="no_answer" class="text-xs">No Answer
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="busy" class="text-xs">Busy</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="not_interested" class="text-xs">Not Interested
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="rescheduled" class="text-xs">Rescheduled
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="converted" class="text-xs">Converted
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="other" class="text-xs">Other</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Duration (minutes)</Label>
                        <Input v-model="completionForm.duration_minutes" type="number" min="0" max="1440"
                            placeholder="e.g., 15" class="h-8 text-xs" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Outcome Notes</Label>
                        <Textarea v-model="completionForm.outcome_notes" placeholder="Add notes about the outcome..."
                            class="text-xs min-h-[80px]" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Status</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="h-8 w-full justify-between text-xs">
                                    <span>{{completionForm.status ? completionForm.status.split('_').map(w =>
                                        w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Completed'}}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="completionForm.status">
                                    <DropdownMenuRadioItem value="completed" class="text-xs">Completed
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="no_show" class="text-xs">No Show
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" class="h-8 text-xs" @click="completionModal.open = false"
                        :disabled="completionModal.loading">
                        Cancel
                    </Button>
                    <Button size="sm" class="h-8 text-xs" @click="completeFollowUp"
                        :disabled="completionModal.loading || !completionForm.outcome">
                        <Loader2 v-if="completionModal.loading" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        Complete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </DashboardLayout>
</template>
