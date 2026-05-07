<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';

const ScheduleFollowUpDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/ScheduleFollowUpDialog.vue'));
const LogCallDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/LogCallDialog.vue'));
const CallHistoryDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/CallHistoryDialog.vue'));
const NoteDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/NoteDialog.vue'));
const ViewNotesDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/ViewNotesDialog.vue'));
const QuickStatusDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/QuickStatusDialog.vue'));
const QuickPriorityDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/QuickPriorityDialog.vue'));
const TransferLeadDialog = defineAsyncComponent(() => import('@/Components/Leads/Modals/TransferLeadDialog.vue'));
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useModules } from '@/composables/useModules';
import { usePermissions } from '@/composables/usePermissions';
import { useLeadActions } from '@/composables/useLeadActions';
import { useLeadFilters } from '@/composables/useLeadFilters';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DataTable, DeleteConfirmation, StatusBadge } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/Components/ui/sheet';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import {
    TooltipProvider,
} from '@/Components/ui/tooltip';
import { Textarea } from '@/Components/ui/textarea';
import { toast } from 'vue-sonner';
import {
    UserPlus,
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
    Phone,
    PhoneOutgoing,
    Mail,
    Filter,
    CalendarPlus,
    FileText,
    History,
    MessageCircle,
    Clock,
    CheckCircle,
    Loader2,
    StickyNote,
    Package,
    ArrowRightLeft,
    Copy,
    ExternalLink,
    Send,
    FileDown,
    Flag,
    UserCheck,
    RefreshCw,
    ChevronDown,
    Briefcase,
    Settings,
    Columns,
    Building,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    leads: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    branches: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
    statusConfig: { type: Object, default: () => ({}) },
    serviceTypes: { type: Array, default: () => [] },
    destinationCountries: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    // Inertia shared props (avoids "extraneous non-props" warning)
    auth: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
    notifications: { type: Array, default: () => [] },
    sidebarMenu: { type: Array, default: () => [] },
    appName: { type: String, default: '' },
    csrf_token: { type: String, default: '' },
});

const page = usePage();
const { hasPermission } = usePermissions();
const { isModuleActive } = useModules();
const hasFollowUpModule = computed(() => isModuleActive('lead-followup-scheduling'));

const getCsrfToken = () =>
    props.csrf_token ||
    page.props.csrf_token ||
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
    document.querySelector('meta[name="csrf-token"]')?.content ||
    '';

/** Extract a user-facing error message from API response data (error, message, or Laravel validation errors). */
const getErrorMessage = (data, fallback = 'Something went wrong. Please try again.') => {
    if (!data || typeof data !== 'object') return fallback;
    if (data.error && typeof data.error === 'string') return data.error;
    if (data.message && typeof data.message === 'string') return data.message;
    if (data.errors && typeof data.errors === 'object') {
        const first = Object.values(data.errors).flat().find(Boolean);
        return first || fallback;
    }
    return fallback;
};

const canTransferToDepartment = (row) => {
    const notTransferred = !row.transferred_to_department || row.transferred_to_department === null || row.transferred_to_department === '';
    return hasPermission('transfer-to-department') && notTransferred;
};

const filterOpen = ref(false);

const {
    logCallModal, callHistoryModal, noteModal, viewNoteModal, scheduleModal,
    statusModal, priorityModal, transferModal, transferUsers, deleteModal,
    logCallForm, noteForm, scheduleForm, quickStatusForm, quickPriorityForm, transferForm,
} = useLeadActions();

const { localSearchQuery, localFilters } = useLeadFilters(props);

const allColumns = [
    { key: 'full_name', label: 'Lead', sortable: true },
    { key: 'contact', label: 'Contact', sortable: false },
    { key: 'service_type', label: 'Service', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'priority', label: 'Priority', sortable: true },
    { key: 'assigned_to', label: 'Assigned', sortable: false },
    { key: 'notes', label: 'Notes', sortable: false },
    { key: 'packages', label: 'Packages', sortable: false },
    { key: 'services', label: 'Services', sortable: false },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: '', width: '180px' },
];

// Column visibility state - load from localStorage or default
const getDefaultColumnVisibility = () => {
    const stored = localStorage.getItem('leads-table-columns');
    if (stored) {
        try {
            const parsed = JSON.parse(stored);

            // Ensure all current columns are in the stored data
            const defaults = {};
            let allFalse = true;
            allColumns.forEach(col => {
                // If column exists in stored data, use that value (must be explicitly true)
                if (parsed.hasOwnProperty(col.key)) {
                    // Only set to true if explicitly true, otherwise false
                    defaults[col.key] = parsed[col.key] === true;
                    if (parsed[col.key] === true) {
                        allFalse = false;
                    }
                } else {
                    // New columns default to true (visible)
                    defaults[col.key] = true;
                    allFalse = false;
                }
            });

            // If all columns are false (user reset), default to all visible.
            // Also persist it so refresh doesn't keep hiding everything.
            const visibleCount = Object.values(defaults).filter(v => v === true).length;
            if (allFalse || visibleCount === 0) {
                allColumns.forEach(col => {
                    defaults[col.key] = true;
                });
                localStorage.setItem('leads-table-columns', JSON.stringify(defaults));
            }

            return defaults;
        } catch (e) {
            console.error('[Column Visibility] Error parsing stored visibility:', e);
            // If parsing fails, clear localStorage and return defaults
            localStorage.removeItem('leads-table-columns');
        }
    }
    // Default: all columns visible
    const defaults = {};
    allColumns.forEach(col => {
        defaults[col.key] = true;
    });
    return defaults;
};

const columnVisibility = ref(getDefaultColumnVisibility());
const columnVisibilityOpen = ref(false);

// Save column visibility to localStorage
const saveColumnVisibility = () => {
    localStorage.setItem('leads-table-columns', JSON.stringify(columnVisibility.value));
};

// Computed columns based on visibility - watch for changes
const columns = computed(() => {
    // Access columnVisibility to ensure reactivity - this makes the computed reactive
    const visibility = columnVisibility.value;

    // Filter columns where visibility is explicitly true
    const visibleColumns = allColumns.filter(col => {
        const visibilityValue = visibility[col.key];
        const isVisible = visibilityValue === true;
        return isVisible;
    });

    return visibleColumns;
});

const selectAllColumns = () => {
    const newVisibility = {};
    allColumns.forEach(col => {
        newVisibility[col.key] = true;
    });
    columnVisibility.value = newVisibility;
    saveColumnVisibility();
};

const deselectAllColumns = () => {
    const newVisibility = {};
    allColumns.forEach(col => {
        newVisibility[col.key] = false;
    });
    // Keep actions visible so the table isn't unusable
    newVisibility.actions = true;
    columnVisibility.value = newVisibility;
    saveColumnVisibility();
};

const resetColumns = () => {
    localStorage.removeItem('leads-table-columns');
    selectAllColumns();
};

// Toggle column visibility
const toggleColumnVisibility = (colKey, checked) => {
    // Create a completely new object to ensure Vue reactivity
    const newVisibility = {};
    allColumns.forEach(col => {
        if (col.key === colKey) {
            // Set the toggled column to the checked value (explicitly true or false)
            newVisibility[col.key] = checked === true;
        } else {
            // Keep other columns as they were (must be explicitly true to show)
            newVisibility[col.key] = columnVisibility.value[col.key] === true;
        }
    });

    // Update the ref with new object to trigger reactivity
    columnVisibility.value = newVisibility;
    saveColumnVisibility();
};

const baseStatusOptions = [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'busy', label: 'Busy' },
    { value: 'unavailable', label: 'Unavailable' },
    { value: 'no_answer', label: 'No Answer' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'not_qualified', label: 'Not Qualified' },
    { value: 'follow_up', label: 'Follow Up' },
    { value: 'office_visited', label: 'Office Visited' },
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
    { value: 'urgent_or_high', label: 'Urgent or High' },
    { value: 'normal', label: 'Normal' },
    { value: 'low', label: 'Low' },
];

/** Priority options for lead table dropdown (only valid lead priorities, no filter-only values) */
const leadPriorityOptions = [
    { value: 'urgent', label: 'Urgent' },
    { value: 'high', label: 'High' },
    { value: 'normal', label: 'Normal' },
    { value: 'low', label: 'Low' },
];

const callTypeOptions = [
    { value: 'outgoing', label: 'Outgoing Call' },
    { value: 'incoming', label: 'Incoming Call' },
    { value: 'missed', label: 'Missed Call' },
];

const callStatusOptions = [
    { value: 'completed', label: 'Completed' },
    { value: 'no_answer', label: 'No Answer' },
    { value: 'busy', label: 'Busy' },
    { value: 'voicemail', label: 'Voicemail' },
    { value: 'wrong_number', label: 'Wrong Number' },
];

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

// Action Visibility State
const getDefaultActionVisibility = () => {
    const stored = localStorage.getItem('leads-table-actions');
    if (stored) {
        try {
            const parsed = JSON.parse(stored);
            const defaults = {};
            let allFalse = true;
            actionButtons.value.forEach(btn => {
                if (parsed.hasOwnProperty(btn.key)) {
                    defaults[btn.key] = parsed[btn.key] === true;
                    if (parsed[btn.key] === true) allFalse = false;
                } else {
                    defaults[btn.key] = true;
                    allFalse = false;
                }
            });

            if (allFalse) {
                actionButtons.value.forEach(btn => defaults[btn.key] = true);
                localStorage.setItem('leads-table-actions', JSON.stringify(defaults));
            }

            if (!hasFollowUpModule.value) {
                defaults.follow_up = false;
            }

            return defaults;
        } catch (e) {
            console.error('[Action Visibility] Error parsing stored visibility:', e);
            localStorage.removeItem('leads-table-actions');
        }
    }
    const defaults = {};
    actionButtons.value.forEach(btn => defaults[btn.key] = true);
    defaults.follow_up = hasFollowUpModule.value;
    return defaults;
};

const actionVisibility = ref(getDefaultActionVisibility());
const actionVisibilityOpen = ref(false);

const saveActionVisibility = () => {
    localStorage.setItem('leads-table-actions', JSON.stringify(actionVisibility.value));
};

const toggleActionVisibility = (key, checked) => {
    const newVisibility = { ...actionVisibility.value };
    newVisibility[key] = checked === true;
    actionVisibility.value = newVisibility;
    saveActionVisibility();
};

const selectAllActions = () => {
    const newVisibility = {};
    actionButtons.value.forEach(btn => newVisibility[btn.key] = true);
    newVisibility.follow_up = hasFollowUpModule.value;
    actionVisibility.value = newVisibility;
    saveActionVisibility();
};

const deselectAllActions = () => {
    const newVisibility = {};
    actionButtons.value.forEach(btn => newVisibility[btn.key] = false);
    newVisibility.follow_up = false;
    actionVisibility.value = newVisibility;
    saveActionVisibility();
};

const resetActions = () => {
    localStorage.removeItem('leads-table-actions');
    selectAllActions();
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

const formatPhone = (phone) => {
    if (!phone) return '';
    return phone.replace(/\D+/g, '');
};

const STATUS_COLORS = Object.freeze({
    new: '#3b82f6',
    contacted: '#8b5cf6',
    busy: '#f97316',
    unavailable: '#6b7280',
    no_answer: '#0ea5e9',
    qualified: '#f59e0b',
    not_qualified: '#f97316',
    follow_up: '#10b981',
    office_visited: '#06b6d4',
    documentation_pending: '#f59e0b',
    application_submitted: '#3b82f6',
    under_review: '#8b5cf6',
    interview_scheduled: '#10b981',
    approved: '#10b981',
    in_process: '#3b82f6',
    on_hold: '#6b7280',
    converted: '#10b981',
    flight_done: '#10b981',
    rejected: '#ef4444',
    cancelled: '#6b7280',
    lost: '#ef4444',
});
const getStatusColor = (status) => STATUS_COLORS[status] || '#6b7280';

const handleSort = ({ field, direction }) => {
    router.get(route('leads.index'), {
        ...props.filters,
        sort: field,
        direction,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (query) => {
    router.get(route('leads.index'), {
        ...props.filters,
        search: query,
        page: 1,
    }, { preserveState: true, preserveScroll: true });
};

const handlePageChange = (page) => {
    router.get(route('leads.index'), { ...props.filters, page }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage) => {
    router.get(route('leads.index'), { ...props.filters, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
};

const applyFilters = () => {
    const filterData = { ...localFilters.value };
    if (filterData.status === 'all') filterData.status = undefined;
    if (filterData.branch_id === 'all') filterData.branch_id = undefined;
    if (filterData.assigned_to === 'all') filterData.assigned_to = undefined;
    if (filterData.priority === 'all') filterData.priority = undefined;
    if (filterData.service_type === 'all') filterData.service_type = undefined;
    if (filterData.service_id === 'all') filterData.service_id = undefined;
    if (filterData.package_id === 'all') filterData.package_id = undefined;
    if (filterData.destination_country === 'all') filterData.destination_country = undefined;
    if (!filterData.inactive_days || filterData.inactive_days === '') filterData.inactive_days = undefined;
    if (!filterData.date_from) filterData.date_from = undefined;
    if (!filterData.date_to) filterData.date_to = undefined;

    const params = Object.fromEntries(
        Object.entries(filterData).filter(([, v]) => v !== undefined && v !== '')
    );
    router.get(route('leads.index'), { ...params, page: 1 }, { preserveState: true, preserveScroll: true });
    filterOpen.value = false;
};

const clearFilters = () => {
    localSearchQuery.value = '';
    localFilters.value = {
        search: '',
        status: 'all',
        branch_id: 'all',
        assigned_to: 'all',
        priority: 'all',
        date_from: '',
        date_to: '',
        service_type: 'all',
        service_id: 'all',
        package_id: 'all',
        destination_country: 'all',
        inactive_days: '',
    };
    router.get(route('leads.index'), {}, { preserveState: true, preserveScroll: true });
    filterOpen.value = false;
};

const hasActiveFilters = computed(() => {
    const f = localFilters.value;
    return (
        (f.search && f.search !== '') ||
        (f.status && f.status !== 'all') ||
        (f.branch_id && f.branch_id !== 'all') ||
        (f.assigned_to && f.assigned_to !== 'all') ||
        (f.priority && f.priority !== 'all') ||
        (f.date_from && f.date_from !== '') ||
        (f.date_to && f.date_to !== '') ||
        (f.service_type && f.service_type !== 'all') ||
        (f.service_id && f.service_id !== 'all') ||
        (f.package_id && f.package_id !== 'all') ||
        (f.destination_country && f.destination_country !== 'all') ||
        (f.inactive_days && f.inactive_days !== '')
    );
});

const inactiveDaysOptions = [
    { value: '7', label: '7+ days' },
    { value: '14', label: '14+ days' },
    { value: '30', label: '30+ days' },
    { value: '60', label: '60+ days' },
    { value: '90', label: '90+ days' },
];

const serviceTypeLabels = {
    study_visa: 'Study Visa',
    tour_visa: 'Tour Visa',
    immigration: 'Immigration',
    other: 'Other',
};

const openDeleteModal = (lead) => {
    deleteModal.value = { open: true, lead, loading: false };
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
            await response.json();
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            const message = errorData?.error ?? errorData?.message ?? 'Failed to update status.';
            toast.error(message);
        }
    } catch (error) {
        console.error('[Lead Update] Error updating status:', error);
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
            await response.json();
            router.reload({ only: ['leads'] });
        } else {
            const errorData = await response.json().catch(() => ({}));
            console.error('[Lead Update] Failed to update services/packages:', errorData);
        }
    } catch (error) {
        console.error('[Lead Update] Error updating services/packages:', error);
    }
};

// Update assigned user
const updateAssignedTo = async (leadId, assignedTo) => {
    // If unassigning, use direct update (no approval needed to remove assignment)
    if (!assignedTo) {
        try {
            const response = await fetch(route('leads.quick-update', leadId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ assigned_to: null }),
            });

            if (response.ok) {
                await response.json();
                router.reload({ only: ['leads'] });
                toast.success('Lead unassigned successfully.');
            } else {
                const errorData = await response.json().catch(() => ({}));
                console.error('[Lead Update] Failed to unassign user:', errorData);
                toast.error('Failed to unassign lead.');
            }
        } catch (error) {
            console.error('[Lead Update] Error unassigning user:', error);
            toast.error('An error occurred while unassigning the lead.');
        }
        return;
    }

    // If assigning to a user, initiate a pending transfer
    try {
        const token = getCsrfToken();
        const response = await fetch(route('leads.quick-transfer', leadId), {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                _token: token,
                transferred_to: assignedTo,
                note: 'Quick transfer from leads list'
            }),
        });

        let data = {};
        try {
            data = await response.json();
        } catch {
            data = {};
        }

        if (response.ok) {
            router.reload({ only: ['leads'] });
            toast.success(data.message || 'Transfer request sent successfully.');
        } else {
            const message = getErrorMessage(data, 'Failed to initiate transfer request.');
            console.error('[Lead Transfer] Failed to transfer lead:', data);
            toast.error(message);
        }
    } catch (error) {
        console.error('[Lead Transfer] Error transferring lead:', error);
        toast.error('An error occurred while initiating the transfer.');
    }
};

// Note Modal
const openNoteModal = (lead) => {
    noteModal.value = { open: true, lead, loading: false };
    noteForm.value.note = lead.latest_note?.note || '';
};

const openViewNoteModal = (lead) => {
    viewNoteModal.value = {
        open: true,
        lead: lead,
        notes: lead.notes || []
    };
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
        }
    } catch (error) {
        console.error('Failed to update status:', error);
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

const updatePriority = async (leadId, newPriority) => {
    try {
        const response = await fetch(route('leads.quick-update', leadId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ priority: newPriority }),
        });

        if (response.ok) {
            await response.json();
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to update priority:', error);
    }
};

// Transfer Modal
const openTransferModal = async (lead) => {
    transferModal.value = { open: true, lead, loading: true };
    transferForm.value = {
        transferred_to: '',
        note: '',
    };

    // Fetch users for this lead's branch via API
    try {
        const response = await fetch(route('api.leads.transfer-users', lead.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        });

        if (response.ok) {
            const data = await response.json();
            transferUsers.value = data.users || [];
        } else {
            // Fallback: use users from props if available
            transferUsers.value = props.users || [];
        }
    } catch (error) {
        console.error('Failed to fetch users:', error);
        // Fallback: use users from props if available
        transferUsers.value = props.users || [];
    } finally {
        transferModal.value.loading = false;
    }
};

const submitTransfer = async () => {
    if (!transferModal.value.lead || !transferForm.value.transferred_to) return;
    transferModal.value.loading = true;

    try {
        const token = getCsrfToken();
        const response = await fetch(route('leads.quick-transfer', transferModal.value.lead.id), {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                _token: token,
                transferred_to: transferForm.value.transferred_to,
                note: transferForm.value.note || null,
            }),
        });

        let data = {};
        try {
            data = await response.json();
        } catch {
            data = {};
        }

        if (response.ok) {
            transferModal.value.open = false;
            transferUsers.value = [];
            router.reload({ only: ['leads'] });
            toast.success(data.message || 'Lead transfer request sent successfully.');
        } else {
            const message = getErrorMessage(data, 'An error occurred while transferring the lead. Please try again.');
            toast.error(message);
        }
    } catch (error) {
        console.error('Failed to transfer lead:', error);
        toast.error('An error occurred while transferring the lead. Please try again.');
    } finally {
        transferModal.value.loading = false;
    }
};

// Copy lead info to clipboard
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

// Open in new tab
const openInNewTab = (leadId) => {
    window.open(route('leads.show', leadId), '_blank');
};
</script>

<template>

    <Head title="Leads" />

    <div class="space-y-4">
        <!-- Page Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Leads</h1>
                <p class="text-xs text-muted-foreground">Manage and track all your leads.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Column Visibility Control -->
                <DropdownMenu v-model:open="columnVisibilityOpen">
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
                <!-- Actions Visibility Control -->
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
                <Sheet v-model:open="filterOpen">
                    <SheetTrigger as-child>
                        <Button variant="outline" size="sm" class="h-8 text-xs">
                            <Filter class="mr-1.5 h-3.5 w-3.5" />
                            Filter Leads
                            <Badge v-if="hasActiveFilters" variant="secondary" class="ml-1.5 text-[10px]">Active</Badge>
                        </Button>
                    </SheetTrigger>
                    <SheetContent v-if="filterOpen" side="right" class="flex w-full flex-col sm:max-w-sm overflow-hidden p-0">
                        <SheetHeader class="shrink-0 border-b border-border px-6 py-4">
                            <SheetTitle class="text-base font-semibold">Filter Leads</SheetTitle>
                            <SheetDescription class="text-xs text-muted-foreground mt-1">
                                Narrow down by status, priority, service type, service, package, assignment, date, or inactivity.
                            </SheetDescription>
                        </SheetHeader>
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            <div class="flex flex-col gap-5">
                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Status</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.status === 'all'
                                                        ? 'All statuses'
                                                        : (statusOptions.find(s => s.value === localFilters.status)?.label ?? localFilters.status)
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Status</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.status = 'all'">
                                                All statuses
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="status in statusOptions"
                                                :key="status.value"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.status = status.value"
                                            >
                                                {{ status.label }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Priority -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Priority</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.priority === 'all'
                                                        ? 'All priorities'
                                                        : (priorityOptions.find(p => p.value === localFilters.priority)?.label ?? localFilters.priority)
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)]">
                                            <DropdownMenuLabel class="text-xs">Priority</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.priority = 'all'">
                                                All priorities
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="p in priorityOptions"
                                                :key="p.value"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.priority = p.value"
                                            >
                                                {{ p.label }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Service type -->
                                <div v-if="serviceTypes.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Service type</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.service_type === 'all'
                                                        ? 'All types'
                                                        : (serviceTypeLabels[localFilters.service_type] || localFilters.service_type)
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)]">
                                            <DropdownMenuLabel class="text-xs">Service type</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.service_type = 'all'">
                                                All types
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="t in serviceTypes"
                                                :key="t"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.service_type = t"
                                            >
                                                {{ serviceTypeLabels[t] || t }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Service (catalog service) -->
                                <div v-if="services?.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Service</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.service_id === 'all'
                                                        ? 'All services'
                                                        : (services.find(s => String(s.id) === localFilters.service_id)?.name ?? '')
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Service</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.service_id = 'all'">
                                                All services
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="s in services"
                                                :key="s.id"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.service_id = String(s.id)"
                                            >
                                                {{ s.name }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Package (catalog package) -->
                                <div v-if="packages?.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Package</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.package_id === 'all'
                                                        ? 'All packages'
                                                        : (packages.find(p => String(p.id) === localFilters.package_id)?.name ?? '')
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Package</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.package_id = 'all'">
                                                All packages
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="p in packages"
                                                :key="p.id"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.package_id = String(p.id)"
                                            >
                                                {{ p.name }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <Separator class="my-1" />

                                <!-- Branch -->
                                <div v-if="branches.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Branch</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.branch_id === 'all'
                                                        ? 'All branches'
                                                        : (branches.find(b => String(b.id) === localFilters.branch_id)?.name ?? '')
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Branch</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.branch_id = 'all'">
                                                All branches
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="branch in branches"
                                                :key="branch.id"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.branch_id = String(branch.id)"
                                            >
                                                {{ branch.name }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Assigned to -->
                                <div v-if="users.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Assigned to</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.assigned_to === 'all'
                                                        ? 'All'
                                                        : localFilters.assigned_to === 'unassigned'
                                                            ? 'Unassigned'
                                                            : (users.find(u => String(u.id) === localFilters.assigned_to)?.name ?? '')
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Assigned to</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.assigned_to = 'all'">
                                                All
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.assigned_to = 'unassigned'">
                                                Unassigned
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="u in users"
                                                :key="u.id"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.assigned_to = String(u.id)"
                                            >
                                                {{ u.name }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Destination country -->
                                <div v-if="destinationCountries.length" class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Destination country</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    localFilters.destination_country === 'all' ? 'All countries' : localFilters.destination_country
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)] max-h-[280px] overflow-y-auto">
                                            <DropdownMenuLabel class="text-xs">Destination country</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.destination_country = 'all'">
                                                All countries
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="c in destinationCountries"
                                                :key="c"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.destination_country = c"
                                            >
                                                {{ c }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <Separator class="my-1" />

                                <!-- Created date range -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Created date range</Label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <Label class="text-[11px] text-muted-foreground">From</Label>
                                            <Input type="date" v-model="localFilters.date_from" class="h-9 text-xs w-full" />
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label class="text-[11px] text-muted-foreground">To</Label>
                                            <Input type="date" v-model="localFilters.date_to" class="h-9 text-xs w-full" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Inactive for -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium text-foreground">Inactive for</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="h-9 w-full justify-between text-xs font-normal">
                                                <span class="truncate">{{
                                                    !localFilters.inactive_days
                                                        ? 'Any'
                                                        : (inactiveDaysOptions.find(o => o.value === localFilters.inactive_days)?.label ?? localFilters.inactive_days + '+ days')
                                                }}</span>
                                                <ChevronDown class="h-3.5 w-3.5 shrink-0 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] min-w-[var(--radix-dropdown-menu-trigger-width)]">
                                            <DropdownMenuLabel class="text-xs">No status change for</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="text-xs cursor-pointer" @click="localFilters.inactive_days = ''">
                                                Any
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                v-for="opt in inactiveDaysOptions"
                                                :key="opt.value"
                                                class="text-xs cursor-pointer"
                                                @click="localFilters.inactive_days = opt.value"
                                            >
                                                {{ opt.label }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 border-t border-border px-6 py-4 flex gap-2">
                            <Button @click="applyFilters" size="sm" class="flex-1 h-9 text-xs">
                                Apply filters
                            </Button>
                            <Button variant="outline" @click="clearFilters" size="sm" class="h-9 text-xs">
                                Clear all
                            </Button>
                        </div>
                    </SheetContent>
                </Sheet>
                <Button as-child size="sm" class="h-8 text-xs" v-if="hasPermission('create-leads')" variant="outline">
                    <Link :href="route('leads.bulk-upload')">
                        <FileDown class="mr-1.5 h-3.5 w-3.5" />
                        Bulk Upload
                    </Link>
                </Button>
                <Button as-child size="sm" class="h-8 text-xs" v-if="hasPermission('create-leads')">
                    <Link :href="route('leads.create')">
                        <UserPlus class="mr-1.5 h-3.5 w-3.5" />
                        Add Lead
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Data Table -->
        <TooltipProvider>
            <DataTable
                :key="`table-${Object.keys(columnVisibility).filter(k => columnVisibility[k] === true).join('-')}`"
                :columns="columns" :data="leads.data" :pagination="leads"
                :sort-field="typeof filters?.sort === 'string' ? filters.sort : ''"
                :sort-direction="typeof filters?.direction === 'string' ? filters.direction : 'desc'"
                v-model:searchQuery="localSearchQuery" search-placeholder="Search anything (name, email, phone, passport, notes, address...)"
                @sort="handleSort" @search="handleSearch" @page-change="handlePageChange"
                @per-page-change="handlePerPageChange">
                <!-- Lead Name Cell -->
                <template #cell-full_name="{ row }">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 shrink-0">
                            <span class="text-xs font-medium text-primary">
                                {{ row.full_name?.charAt(0)?.toUpperCase() || '?' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <Link :href="route('leads.show', row.id)"
                                class="text-xs font-medium hover:underline truncate block">
                                {{ row.full_name || 'Unnamed Lead' }}
                                <span v-if="row.number_of_tour_persons" class="text-muted-foreground">(P-{{
                                    row.number_of_tour_persons }})</span>
                            </Link>
                            <p v-if="row.destination_country" class="text-[10px] text-muted-foreground truncate">{{
                                row.destination_country }}</p>
                        </div>
                    </div>
                </template>

                <!-- Contact Cell -->
                <template #cell-contact="{ row }">
                    <div class="space-y-0.5">
                        <div v-if="row.phone" class="flex items-center gap-1 text-xs">
                            <Phone class="h-3 w-3 text-muted-foreground shrink-0" />
                            <span class="truncate">{{ row.phone }}</span>
                        </div>
                        <div v-if="row.email" class="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Mail class="h-3 w-3 shrink-0" />
                            <span class="truncate max-w-[120px]">{{ row.email }}</span>
                        </div>
                    </div>
                </template>

                <!-- Service Type Cell -->
                <template #cell-service_type="{ row }">
                    <span v-if="row.service_type" class="text-xs capitalize">{{ row.service_type?.replace(/_/g, ' ')
                        }}</span>
                    <span v-else class="text-xs text-muted-foreground">-</span>
                </template>

                <!-- Status Cell -->
                <template #cell-status="{ row }">
                    <DropdownMenu v-if="hasPermission('edit-leads') && !['converted', 'flight_done'].includes(row?.status)">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                                class="h-auto p-1.5 text-[10px] font-medium capitalize hover:bg-accent">
                                <Badge :style="{ backgroundColor: getStatusColor(row.status), color: 'white' }"
                                    class="text-[10px] font-medium capitalize">
                                    {{ row.status?.replace(/_/g, ' ') }}
                                </Badge>
                                <ChevronDown class="ml-1 h-3 w-3 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-56">
                            <DropdownMenuRadioGroup :model-value="row.status"
                                @update:model-value="(value) => updateStatus(row.id, value)">
                                <DropdownMenuRadioItem v-for="status in statusOptions" :key="status.value"
                                    :value="status.value" class="text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full"
                                            :style="{ backgroundColor: getStatusColor(status.value) }"></div>
                                        {{ status.label }}
                                    </div>
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Badge v-else :style="{ backgroundColor: getStatusColor(row.status), color: 'white' }"
                        class="text-[10px] font-medium capitalize">
                        {{ row.status?.replace(/_/g, ' ') }}
                    </Badge>
                </template>

                <!-- Priority Cell -->
                <template #cell-priority="{ row }">
                    <DropdownMenu v-if="hasPermission('edit-leads')">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                                class="h-auto p-1.5 text-[10px] font-medium capitalize hover:bg-accent flex items-center gap-1">
                                <StatusBadge v-if="row.priority" :status="row.priority" />
                                <span v-else class="text-xs text-muted-foreground">-</span>
                                <ChevronDown class="h-3 w-3 opacity-50 shrink-0" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-44">
                            <DropdownMenuRadioGroup :model-value="row.priority || 'normal'"
                                @update:model-value="(value) => updatePriority(row.id, value)">
                                <DropdownMenuRadioItem v-for="p in leadPriorityOptions" :key="p.value"
                                    :value="p.value" class="text-xs">
                                    {{ p.label }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <template v-else>
                        <StatusBadge v-if="row.priority" :status="row.priority" />
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>
                </template>

                <!-- Assigned To Cell -->
                <template #cell-assigned_to="{ row }">
                    <DropdownMenu v-if="hasPermission('edit-leads')">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                                class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                                <UserCheck class="mr-1.5 h-3.5 w-3.5" />
                                <span class="truncate max-w-[120px]">
                                    {{ row.assigned_user ? row.assigned_user.name : 'Unassigned' }}
                                </span>
                                <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                            <DropdownMenuRadioGroup :model-value="row.assigned_to ? String(row.assigned_to) : ''"
                                @update:model-value="(value) => updateAssignedTo(row.id, value ? Number(value) : null)">
                                <DropdownMenuRadioItem value="" class="text-xs">
                                    <div class="flex items-center gap-2">
                                        <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span>Unassigned</span>
                                    </div>
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem v-for="user in props.users" :key="user.id"
                                    :value="String(user.id)" class="text-xs">
                                    <div class="flex items-center gap-2">
                                        <UserCheck class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span>{{ user.name }}</span>
                                        <span v-if="user.branch" class="text-muted-foreground text-[10px]">({{
                                            user.branch.name }})</span>
                                    </div>
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div v-else class="text-xs text-muted-foreground">
                        {{ row.assigned_user ? row.assigned_user.name : 'Unassigned' }}
                    </div>
                </template>

                <!-- Notes Cell -->
                <template #cell-notes="{ row }">
                    <div class="max-w-[200px] space-y-1.5">
                        <div v-if="row.notes && row.notes.length > 0" class="space-y-1">
                            <div v-for="note in row.notes.slice(0, 1)" :key="note.id"
                                class="text-xs text-muted-foreground line-clamp-2" :title="note.note">
                                {{ note.note }}
                            </div>
                            <div v-if="row.notes.length > 1" class="text-[10px] text-muted-foreground">
                                +{{ row.notes.length - 1 }} more
                            </div>
                        </div>
                        <div v-else class="text-xs text-muted-foreground">No notes</div>
                        <div class="flex items-center gap-1">
                            <Button v-if="hasPermission('edit-leads')" variant="ghost" size="sm"
                                class="h-6 text-[10px] px-2" @click="openNoteModal(row)">
                                <StickyNote class="mr-1 h-3 w-3" />
                                {{ row.notes && row.notes.length > 0 ? 'Edit' : 'Add' }}
                            </Button>
                            <Button v-if="row.notes && row.notes.length > 0" variant="ghost" size="sm"
                                class="h-6 text-[10px] px-2" @click="openViewNoteModal(row)" title="View all notes">
                                <Eye class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>
                </template>

                <!-- Packages Cell -->
                <template #cell-packages="{ row }">
                    <DropdownMenu v-if="hasPermission('edit-leads')">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                                class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                                <Package class="mr-1.5 h-3.5 w-3.5" />
                                <span class="truncate max-w-[120px]">
                                    {{row.packages && row.packages.length > 0
                                        ? row.packages.map(p => p.name).join(', ')
                                        : 'None'}}
                                </span>
                                <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                            <div class="px-2 py-1.5">
                                <div class="text-xs font-semibold mb-2">Select Packages</div>
                                <div class="space-y-1">
                                    <label v-for="pkg in props.packages" :key="pkg.id"
                                        class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                        <Checkbox :checked="row.packages?.some(p => p.id === pkg.id) || false"
                                            @update:checked="(checked) => {
                                                const currentPackages = row.packages?.map(p => p.id) || [];
                                                const newPackages = checked
                                                    ? [...currentPackages, pkg.id]
                                                    : currentPackages.filter(id => id !== pkg.id);
                                                updateServicesAndPackages(row.id, row.services?.map(s => s.id) || [], newPackages);
                                            }" />
                                        <span class="text-xs">{{ pkg.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div v-else class="text-xs text-muted-foreground">
                        {{row.packages && row.packages.length > 0
                            ? row.packages.map(p => p.name).join(', ')
                            : '-'}}
                    </div>
                </template>

                <!-- Services Cell -->
                <template #cell-services="{ row }">
                    <DropdownMenu v-if="hasPermission('edit-leads')">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm"
                                class="h-auto p-1.5 text-xs hover:bg-accent justify-start">
                                <Briefcase class="mr-1.5 h-3.5 w-3.5" />
                                <span class="truncate max-w-[120px]">
                                    {{row.services && row.services.length > 0
                                        ? row.services.map(s => s.name).join(', ')
                                        : 'None'}}
                                </span>
                                <ChevronDown class="ml-1 h-3 w-3 opacity-50 shrink-0" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-64 max-h-[300px] overflow-y-auto">
                            <div class="px-2 py-1.5">
                                <div class="text-xs font-semibold mb-2">Select Services</div>
                                <div class="space-y-1">
                                    <label v-for="service in props.services" :key="service.id"
                                        class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent cursor-pointer">
                                        <Checkbox :checked="row.services?.some(s => s.id === service.id) || false"
                                            @update:checked="(checked) => {
                                                const currentServices = row.services?.map(s => s.id) || [];
                                                const newServices = checked
                                                    ? [...currentServices, service.id]
                                                    : currentServices.filter(id => id !== service.id);
                                                updateServicesAndPackages(row.id, newServices, row.packages?.map(p => p.id) || []);
                                            }" />
                                        <span class="text-xs">{{ service.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div v-else class="text-xs text-muted-foreground">
                        {{row.services && row.services.length > 0
                            ? row.services.map(s => s.name).join(', ')
                            : '-'}}
                    </div>
                </template>

                <!-- Created Date Cell -->
                <template #cell-created_at="{ row }">
                    <span class="text-[10px] text-muted-foreground">{{ formatDate(row.created_at) }}</span>
                </template>

                <!-- Actions Cell -->
                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-1">
                        <!-- View Details Button -->
                        <Button v-if="actionVisibility.view_details" as-child variant="ghost" size="icon"
                            class="h-7 w-7 text-blue-500 hover:text-blue-600 hover:bg-blue-50" title="View Details">
                            <Link :href="route('leads.show', row.id)">
                                <Eye class="h-3.5 w-3.5" />
                            </Link>
                        </Button>

                        <!-- Edit Lead Button -->
                        <Button v-if="actionVisibility.edit_lead && hasPermission('edit-leads')" as-child
                            variant="ghost" size="icon"
                            class="h-7 w-7 text-amber-500 hover:text-amber-600 hover:bg-amber-50" title="Edit Lead">
                            <Link :href="route('leads.edit', row.id)">
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
                        </Button>

                        <!-- Call Button -->
                        <Button v-if="actionVisibility.call && row.phone" variant="ghost" size="icon"
                            class="h-7 w-7 text-green-600 hover:text-green-700 hover:bg-green-50"
                            @click="makeCall(row.phone)" title="Call">
                            <Phone class="h-3.5 w-3.5" />
                        </Button>

                        <!-- WhatsApp Button -->
                        <Button v-if="actionVisibility.whatsapp && row.phone" variant="ghost" size="icon"
                            class="h-7 w-7 text-[#25D366] hover:text-[#128C7E] hover:bg-green-50"
                            @click="openWhatsApp(row.phone)" title="WhatsApp">
                            <MessageCircle class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Log Call Button -->
                        <Button v-if="actionVisibility.log_call && row.phone && hasPermission('edit-leads')"
                            variant="ghost" size="icon"
                            class="h-7 w-7 text-purple-600 hover:text-purple-700 hover:bg-purple-50"
                            @click="openLogCallModal(row)" title="Log Call">
                            <PhoneOutgoing class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Call History Button -->
                        <Button v-if="actionVisibility.call_history && row.phone" variant="ghost" size="icon"
                            class="h-7 w-7 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                            @click="openCallHistoryModal(row)" title="Call History">
                            <History class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Add Note Button -->
                        <Button v-if="actionVisibility.add_note && hasPermission('edit-leads')" variant="ghost"
                            size="icon" class="h-7 w-7 text-yellow-500 hover:text-yellow-600 hover:bg-yellow-50"
                            @click="openNoteModal(row)" title="Add/Edit Note">
                            <StickyNote class="h-3.5 w-3.5" />
                        </Button>

                        <!-- FollowUp Button -->
                        <Button v-if="actionVisibility.follow_up && hasPermission('edit-leads')" variant="ghost"
                            size="icon" class="h-7 w-7 text-orange-600 hover:text-orange-700 hover:bg-orange-50"
                            @click="openScheduleModal(row)" title="Follow Up">
                            <CalendarPlus class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Transfer Lead Button -->
                        <Button v-if="actionVisibility.transfer_lead && hasPermission('transfer-leads')" variant="ghost"
                            size="icon" class="h-7 w-7 text-cyan-500 hover:text-cyan-600 hover:bg-cyan-50"
                            @click="openTransferModal(row)" title="Transfer Lead">
                            <ArrowRightLeft class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Transfer to Department Button -->
                        <DropdownMenu v-if="actionVisibility.transfer_to_department && canTransferToDepartment(row)">
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon"
                                    class="h-7 w-7 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50"
                                    title="Transfer to Department">
                                    <Building class="h-3.5 w-3.5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child>
                                    <Link :href="route('departments.transfers.show-form', [row.id, 'visa'])"
                                        class="flex items-center text-xs">
                                        <FileText class="mr-2 h-3.5 w-3.5 text-blue-500" /> Transfer to Visa Department
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link :href="route('departments.transfers.show-form', [row.id, 'accounts'])"
                                        class="flex items-center text-xs">
                                        <Briefcase class="mr-2 h-3.5 w-3.5 text-green-500" /> Transfer to Accounts
                                        Department
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Send Email Button -->
                        <Button v-if="actionVisibility.send_email && row.email" variant="ghost" size="icon"
                            class="h-7 w-7 text-blue-500 hover:text-blue-600 hover:bg-blue-50"
                            @click="sendEmail(row.email)" title="Send Email">
                            <Send class="h-3.5 w-3.5" />
                        </Button>

                        <!-- More Actions Dropdown -->
                        <DropdownMenu v-if="actionVisibility.more_actions">
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-7 w-7">
                                    <MoreHorizontal class="h-3.5 w-3.5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-52">
                                <!-- View & Edit Section -->
                                <DropdownMenuItem as-child>
                                    <Link :href="route('leads.show', row.id)" class="flex items-center text-xs">
                                        <Eye class="mr-2 h-3.5 w-3.5 text-blue-500" /> View Details
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click.prevent="openInNewTab(row.id)"
                                    class="flex items-center text-xs">
                                    <ExternalLink class="mr-2 h-3.5 w-3.5 text-slate-500" /> Open in New Tab
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="hasPermission('edit-leads')" as-child>
                                    <Link :href="route('leads.edit', row.id)" class="flex items-center text-xs">
                                        <Pencil class="mr-2 h-3.5 w-3.5 text-amber-500" /> Edit Lead
                                    </Link>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <!-- Quick Actions Section -->
                                <DropdownMenuItem v-if="hasPermission('edit-leads') && !['converted', 'flight_done'].includes(row?.status)"
                                    @click.prevent="openStatusModal(row)" class="flex items-center text-xs">
                                    <RefreshCw class="mr-2 h-3.5 w-3.5 text-purple-500" /> Change Status
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="hasPermission('edit-leads')"
                                    @click.prevent="openPriorityModal(row)" class="flex items-center text-xs">
                                    <Flag class="mr-2 h-3.5 w-3.5 text-orange-500" /> Change Priority
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="hasPermission('edit-leads')" @click.prevent="openNoteModal(row)"
                                    class="flex items-center text-xs">
                                    <StickyNote class="mr-2 h-3.5 w-3.5 text-yellow-500" /> Add/Edit Note
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="hasPermission('edit-leads')"
                                    @click.prevent="openScheduleModal(row)" class="flex items-center text-xs">
                                    <CalendarPlus class="mr-2 h-3.5 w-3.5 text-green-500" /> Schedule Follow-up
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="hasPermission('transfer-leads')"
                                    @click.prevent="openTransferModal(row)" class="flex items-center text-xs">
                                    <ArrowRightLeft class="mr-2 h-3.5 w-3.5 text-cyan-500" /> Transfer Lead
                                </DropdownMenuItem>
                                <!-- Transfer to Department -->
                                <template v-if="canTransferToDepartment(row)">
                                    <DropdownMenuSeparator />
                                    <!-- Visa Department -->
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('departments.transfers.show-form', [row.id, 'visa'])"
                                            class="flex items-center text-xs">
                                            <FileText class="mr-2 h-3.5 w-3.5 text-blue-500" /> Transfer to Visa
                                            Department
                                        </Link>
                                    </DropdownMenuItem>
                                    <!-- Accounts Department -->
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('departments.transfers.show-form', [row.id, 'accounts'])"
                                            class="flex items-center text-xs">
                                            <Briefcase class="mr-2 h-3.5 w-3.5 text-green-500" /> Transfer to Accounts
                                            Department
                                        </Link>
                                    </DropdownMenuItem>
                                </template>

                                <!-- Communication Section -->
                                <DropdownMenuItem v-if="row.email" @click.prevent="sendEmail(row.email)"
                                    class="flex items-center text-xs">
                                    <Send class="mr-2 h-3.5 w-3.5 text-blue-500" /> Send Email
                                </DropdownMenuItem>
                                <DropdownMenuItem @click.prevent="copyLeadInfo(row)" class="flex items-center text-xs">
                                    <Copy class="mr-2 h-3.5 w-3.5 text-slate-500" /> Copy Lead Info
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <!-- Navigation Section -->
                                <DropdownMenuItem as-child>
                                    <Link :href="route('leads.documents.index', row.id)"
                                        class="flex items-center text-xs">
                                        <FileText class="mr-2 h-3.5 w-3.5 text-indigo-500" /> View Documents
                                    </Link>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <!-- Danger Zone -->
                                <DropdownMenuItem v-if="hasPermission('delete-leads')" class="text-destructive text-xs"
                                    @click.prevent="openDeleteModal(row)">
                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete Lead
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </DataTable>
        </TooltipProvider>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmation v-model:open="deleteModal.open" :loading="deleteModal.loading" title="Delete Lead"
            :description="`Are you sure you want to delete '${deleteModal.lead?.full_name}'? This action cannot be undone.`"
            @confirm="handleDelete" />

        <!-- Log Call Modal (lazy chunk) -->
        <LogCallDialog v-if="logCallModal.open"
            :open="logCallModal.open" :lead="logCallModal.lead" :loading="logCallModal.loading"
            :form="logCallForm" :call-type-options="callTypeOptions" :call-status-options="callStatusOptions"
            @update:open="(v) => logCallModal.open = v" @submit="submitLogCall" />

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

        <!-- View Notes Modal (lazy chunk) -->
        <ViewNotesDialog v-if="viewNoteModal.open"
            :open="viewNoteModal.open" :lead="viewNoteModal.lead" :notes="viewNoteModal.notes"
            :format-date-time="formatDateTime"
            @update:open="(v) => viewNoteModal.open = v" />

        <!-- Schedule Follow-up Modal (lazy-loaded chunk) -->
        <ScheduleFollowUpDialog v-if="scheduleModal.open"
            :open="scheduleModal.open"
            :lead="scheduleModal.lead"
            :loading="scheduleModal.loading"
            :form="scheduleForm"
            :users="users"
            @update:open="(v) => scheduleModal.open = v"
            @submit="submitSchedule" />

        <!-- Quick Status Change Modal (lazy chunk) -->
        <QuickStatusDialog v-if="statusModal.open"
            :open="statusModal.open" :lead="statusModal.lead" :loading="statusModal.loading"
            :form="quickStatusForm" :status-options="statusOptions" :get-status-color="getStatusColor"
            @update:open="(v) => statusModal.open = v" @submit="submitStatusChange" />

        <!-- Quick Priority Change Modal (lazy chunk) -->
        <QuickPriorityDialog v-if="priorityModal.open"
            :open="priorityModal.open" :lead="priorityModal.lead" :loading="priorityModal.loading"
            :form="quickPriorityForm" :priority-options="priorityOptions"
            @update:open="(v) => priorityModal.open = v" @submit="submitPriorityChange" />

        <!-- Transfer Lead Modal (lazy chunk) -->
        <TransferLeadDialog v-if="transferModal.open"
            :open="transferModal.open" :lead="transferModal.lead" :loading="transferModal.loading"
            :form="transferForm" :users="transferUsers"
            @update:open="(v) => { transferModal.open = v; if (!v) transferUsers = []; }"
            @submit="submitTransfer" />

        <!-- Copy Success Toast -->
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100" leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
            <div v-if="copySuccess"
                class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 z-50">
                <CheckCircle class="h-4 w-4" />
                <span class="text-sm">Lead info copied!</span>
            </div>
        </Transition>
    </div>
</template>
