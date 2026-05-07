import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function getErrorMessage(data, fallback = 'Something went wrong. Please try again.') {
    if (!data) return fallback;
    if (typeof data === 'string') return data;
    return data?.errors && Object.values(data.errors)[0]?.[0]
        ? Object.values(data.errors)[0][0]
        : data?.message || fallback;
}

export function useLeadActions() {
    const logCallModal = ref({ open: false, lead: null, loading: false });
    const callHistoryModal = ref({ open: false, lead: null, loading: false, history: [] });
    const noteModal = ref({ open: false, lead: null, loading: false });
    const viewNoteModal = ref({ open: false, lead: null, notes: [] });
    const scheduleModal = ref({ open: false, lead: null, loading: false });
    const statusModal = ref({ open: false, lead: null, loading: false });
    const priorityModal = ref({ open: false, lead: null, loading: false });
    const transferModal = ref({ open: false, lead: null, loading: false });
    const transferUsers = ref([]);
    const deleteModal = ref({ open: false, lead: null, loading: false });
    const copySuccess = ref(false);

    const logCallForm = ref({ call_type: 'outgoing', call_status: 'completed', duration_minutes: '', notes: '' });
    const noteForm = ref({ note: '' });
    const scheduleForm = ref({ follow_up_date: '', follow_up_time: '09:00', notes: '', follow_up_type: 'call', category: 'general', priority: 'medium', assigned_to: '' });
    const quickStatusForm = ref({ status: '' });
    const quickPriorityForm = ref({ priority: '' });
    const transferForm = ref({ transferred_to: '', note: '' });

    const openDeleteModal = (lead) => { deleteModal.value = { open: true, lead, loading: false }; };
    const openLogCallModal = (lead) => {
        logCallModal.value = { open: true, lead, loading: false };
        logCallForm.value = { call_type: 'outgoing', call_status: 'completed', duration_minutes: '', notes: '' };
    };
    const openNoteModal = (lead) => {
        noteModal.value = { open: true, lead, loading: false };
        noteForm.value.note = lead.latest_note?.note || '';
    };
    const openViewNoteModal = (lead) => { viewNoteModal.value = { open: true, lead, notes: lead.notes || [] }; };
    const openStatusModal = (lead) => {
        statusModal.value = { open: true, lead, loading: false };
        quickStatusForm.value.status = lead.status || 'new';
    };
    const openPriorityModal = (lead) => {
        priorityModal.value = { open: true, lead, loading: false };
        quickPriorityForm.value.priority = lead.priority || 'normal';
    };
    const openScheduleModal = (lead) => {
        scheduleModal.value = { open: true, lead, loading: false };
        scheduleForm.value = { follow_up_date: '', follow_up_time: '09:00', notes: '', follow_up_type: 'call', category: 'general', priority: 'medium', assigned_to: '' };
    };

    const openTransferModal = async (lead, propUsers = []) => {
        transferModal.value = { open: true, lead, loading: true };
        transferForm.value = { transferred_to: '', note: '' };

        try {
            const response = await fetch(route('api.leads.transfer-users', lead.id), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
            });
            if (response.ok) {
                const data = await response.json();
                transferUsers.value = data.users || [];
            } else {
                transferUsers.value = propUsers;
            }
        } catch {
            transferUsers.value = propUsers;
        } finally {
            transferModal.value.loading = false;
        }
    };

    const handleDelete = async () => {
        if (!deleteModal.value.lead) return;
        deleteModal.value.loading = true;

        try {
            router.delete(route('leads.destroy', deleteModal.value.lead.id), {
                onSuccess: () => { deleteModal.value.open = false; },
                onFinish: () => { deleteModal.value.loading = false; },
            });
        } catch {
            deleteModal.value.loading = false;
        }
    };

    const submitLogCall = async () => {
        if (!logCallModal.value.lead) return;
        logCallModal.value.loading = true;

        try {
            const response = await fetch(route('leads.call-history.store', logCallModal.value.lead.id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({
                    phone_number: logCallModal.value.lead.phone,
                    call_type: logCallForm.value.call_type,
                    status: logCallForm.value.call_status,
                    duration: logCallForm.value.duration_minutes ? parseInt(logCallForm.value.duration_minutes) : null,
                    notes: logCallForm.value.notes,
                }),
            });
            if (response.ok) { logCallModal.value.open = false; router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Failed to log call:', e); }
        finally { logCallModal.value.loading = false; }
    };

    const openCallHistoryModal = async (lead) => {
        callHistoryModal.value = { open: true, lead, loading: true, history: [] };
        try {
            const response = await fetch(route('leads.call-history', lead.id), { headers: { 'Accept': 'application/json' } });
            if (response.ok) { const data = await response.json(); callHistoryModal.value.history = data.call_history || []; }
        } catch (e) { console.error('Failed to fetch call history:', e); }
        finally { callHistoryModal.value.loading = false; }
    };

    const updateStatus = async (leadId, newStatus) => {
        try {
            const response = await fetch(route('leads.quick-update', leadId), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ status: newStatus }),
            });
            if (response.ok) { router.reload({ only: ['leads'] }); }
            else { const d = await response.json().catch(() => ({})); toast.error(d?.error ?? d?.message ?? 'Failed to update status.'); }
        } catch { toast.error('Failed to update status.'); }
    };

    const updateServicesAndPackages = async (leadId, selectedServices, selectedPackages) => {
        try {
            const response = await fetch(route('leads.update-services-packages', leadId), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ selected_services: selectedServices || [], selected_packages: selectedPackages || [] }),
            });
            if (response.ok) { router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Error updating services/packages:', e); }
    };

    const updateAssignedTo = async (leadId, assignedTo) => {
        if (!assignedTo) {
            try {
                const response = await fetch(route('leads.quick-update', leadId), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ assigned_to: null }),
                });
                if (response.ok) { router.reload({ only: ['leads'] }); toast.success('Lead unassigned successfully.'); }
                else { toast.error('Failed to unassign lead.'); }
            } catch { toast.error('An error occurred while unassigning the lead.'); }
            return;
        }

        try {
            const token = getCsrfToken();
            const response = await fetch(route('leads.quick-transfer', leadId), {
                method: 'PATCH', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ _token: token, transferred_to: assignedTo, note: 'Quick transfer from leads list' }),
            });
            const data = await response.json().catch(() => ({}));
            if (response.ok) { router.reload({ only: ['leads'] }); toast.success(data.message || 'Transfer request sent successfully.'); }
            else { toast.error(getErrorMessage(data, 'Failed to initiate transfer request.')); }
        } catch { toast.error('An error occurred while initiating the transfer.'); }
    };

    const submitNote = async () => {
        if (!noteModal.value.lead) return;
        noteModal.value.loading = true;
        try {
            const token = getCsrfToken();
            const response = await fetch(route('leads.notes.store', noteModal.value.lead.id), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ note: noteForm.value.note, _token: token }),
            });
            if (response.ok) { noteModal.value.open = false; router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Failed to save note:', e); }
        finally { noteModal.value.loading = false; }
    };

    const submitSchedule = async () => {
        if (!scheduleModal.value.lead) return;
        scheduleModal.value.loading = true;
        try {
            const response = await fetch(route('leads.follow-up.store', scheduleModal.value.lead.id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ ...scheduleForm.value, notes: scheduleForm.value.notes || null, assigned_to: scheduleForm.value.assigned_to || null }),
            });
            if (response.ok) { scheduleModal.value.open = false; router.reload({ only: ['leads'] }); }
            else { const d = await response.json().catch(() => ({})); alert(d.message || `Failed to schedule follow-up. Status: ${response.status}`); }
        } catch (e) { console.error('Failed to schedule follow-up:', e); alert('An error occurred while scheduling the follow-up.'); }
        finally { scheduleModal.value.loading = false; }
    };

    const submitStatusChange = async () => {
        if (!statusModal.value.lead) return;
        statusModal.value.loading = true;
        try {
            const response = await fetch(route('leads.quick-update', statusModal.value.lead.id), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ status: quickStatusForm.value.status }),
            });
            if (response.ok) { statusModal.value.open = false; router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Failed to update status:', e); }
        finally { statusModal.value.loading = false; }
    };

    const submitPriorityChange = async () => {
        if (!priorityModal.value.lead) return;
        priorityModal.value.loading = true;
        try {
            const response = await fetch(route('leads.quick-update', priorityModal.value.lead.id), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ priority: quickPriorityForm.value.priority }),
            });
            if (response.ok) { priorityModal.value.open = false; router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Failed to update priority:', e); }
        finally { priorityModal.value.loading = false; }
    };

    const updatePriority = async (leadId, newPriority) => {
        try {
            const response = await fetch(route('leads.quick-update', leadId), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify({ priority: newPriority }),
            });
            if (response.ok) { router.reload({ only: ['leads'] }); }
        } catch (e) { console.error('Failed to update priority:', e); }
    };

    const submitTransfer = async () => {
        if (!transferModal.value.lead || !transferForm.value.transferred_to) return;
        transferModal.value.loading = true;
        try {
            const token = getCsrfToken();
            const response = await fetch(route('leads.quick-transfer', transferModal.value.lead.id), {
                method: 'PATCH', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ _token: token, transferred_to: transferForm.value.transferred_to, note: transferForm.value.note || null }),
            });
            const data = await response.json().catch(() => ({}));
            if (response.ok) {
                transferModal.value.open = false;
                transferUsers.value = [];
                router.reload({ only: ['leads'] });
                toast.success(data.message || 'Lead transfer request sent successfully.');
            } else {
                toast.error(getErrorMessage(data, 'An error occurred while transferring the lead.'));
            }
        } catch { toast.error('An error occurred while transferring the lead.'); }
        finally { transferModal.value.loading = false; }
    };

    const copyLeadInfo = (lead) => {
        const info = `Name: ${lead.full_name}\nPhone: ${lead.phone || 'N/A'}\nEmail: ${lead.email || 'N/A'}\nStatus: ${lead.status?.replace(/_/g, ' ')}\nService: ${lead.service_type?.replace(/_/g, ' ') || 'N/A'}`;
        navigator.clipboard.writeText(info).then(() => {
            copySuccess.value = true;
            setTimeout(() => (copySuccess.value = false), 2000);
        });
    };

    const makeCall = (phone) => { if (phone) { window.location.href = `tel:${phone}`; } };
    const openWhatsApp = (phone) => { if (phone) { window.open(`https://wa.me/${phone.replace(/\D/g, '')}`, '_blank'); } };
    const sendEmail = (email) => { if (email) { window.location.href = `mailto:${email}`; } };
    const openInNewTab = (leadId) => { window.open(route('leads.show', leadId), '_blank'); };

    return {
        // state
        logCallModal, callHistoryModal, noteModal, viewNoteModal, scheduleModal,
        statusModal, priorityModal, transferModal, transferUsers, deleteModal, copySuccess,
        logCallForm, noteForm, scheduleForm, quickStatusForm, quickPriorityForm, transferForm,
        // openers
        openDeleteModal, openLogCallModal, openNoteModal, openViewNoteModal,
        openStatusModal, openPriorityModal, openScheduleModal, openTransferModal,
        openCallHistoryModal,
        // actions
        handleDelete, submitLogCall, updateStatus, updateServicesAndPackages, updateAssignedTo,
        submitNote, submitSchedule, submitStatusChange, submitPriorityChange, updatePriority, submitTransfer,
        copyLeadInfo, makeCall, openWhatsApp, sendEmail, openInNewTab,
    };
}
