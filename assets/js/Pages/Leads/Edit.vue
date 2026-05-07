<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useModules } from '@/composables/useModules';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Switch } from '@/Components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, Loader2, User, MapPin, Briefcase, ChevronDown, Settings2, X, Eye, EyeOff, Globe } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const props = defineProps({
    lead: { type: Object, required: true },
    services: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    canViewServices: { type: Boolean, default: false },
    canViewPackages: { type: Boolean, default: false },
});

const page = usePage();
const { isModuleActive } = useModules();
const hasFollowUpModule = computed(() => isModuleActive('lead-followup-scheduling'));

const serviceTypes = [
    { value: 'study_visa', label: 'Study Visa' },
    { value: 'tour_visa', label: 'Tour Visa' },
    { value: 'immigration', label: 'Immigration' },
    { value: 'other', label: 'Other' },
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
    let options = hasFollowUpModule.value
        ? baseStatusOptions
        : baseStatusOptions.filter((status) => !['follow_up', 'office_visited'].includes(status.value));

    if (props.lead?.status && !options.some((status) => status.value === props.lead.status)) {
        options = [
            ...options,
            {
                value: props.lead.status,
                label: props.lead.status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase()),
            },
        ];
    }

    return options;
});

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'normal', label: 'Normal' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
];

const sourceOptions = [
    { value: 'walk_in', label: 'Walk-in' },
    { value: 'referral', label: 'Referral' },
    { value: 'website', label: 'Website' },
    { value: 'social_media', label: 'Social Media' },
    { value: 'advertisement', label: 'Advertisement' },
    { value: 'agent', label: 'Agent' },
    { value: 'other', label: 'Other' },
];

// Computed properties for dynamic form sections
const isStudyVisa = computed(() => form.service_type === 'study_visa');
const isTourVisa = computed(() => form.service_type === 'tour_visa');
const isWorkVisa = computed(() => false); // Work visa is handled under immigration
const isImmigration = computed(() => form.service_type === 'immigration');
const isStatusLocked = computed(() => ['converted', 'flight_done'].includes(props.lead?.status));

// Course level options
const courseLevelOptions = [
    { value: 'undergraduate', label: 'Undergraduate' },
    { value: 'postgraduate', label: 'Postgraduate' },
    { value: 'phd', label: 'PhD' },
    { value: 'diploma', label: 'Diploma' },
    { value: 'certificate', label: 'Certificate' },
];

// Semester options
const semesterOptions = [
    { value: 'fall', label: 'Fall' },
    { value: 'spring', label: 'Spring' },
    { value: 'summer', label: 'Summer' },
    { value: 'winter', label: 'Winter' },
];

// Language test type options
const languageTestOptions = [
    { value: 'ielts', label: 'IELTS' },
    { value: 'toefl', label: 'TOEFL' },
    { value: 'pte', label: 'PTE' },
    { value: 'duolingo', label: 'Duolingo' },
    { value: 'other', label: 'Other' },
];

// Funding source options
const fundingSourceOptions = [
    { value: 'self', label: 'Self-funded' },
    { value: 'family', label: 'Family' },
    { value: 'scholarship', label: 'Scholarship' },
    { value: 'loan', label: 'Loan' },
    { value: 'sponsor', label: 'Sponsor' },
    { value: 'other', label: 'Other' },
];

// Application type options
const applicationTypeOptions = [
    { value: 'online', label: 'Online' },
    { value: 'offline', label: 'Offline' },
];

// Visa status options
const visaStatusOptions = [
    { value: 'new', label: 'New' },
    { value: 'applied', label: 'Applied' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'cancelled', label: 'Cancelled' },
];

const customServiceTypeOptions = [
    { value: 'visa', label: 'Visa' },
    { value: 'education', label: 'Education' },
    { value: 'tour', label: 'Tour' },
    { value: 'other', label: 'Other' },
];

const normalizeSelectValue = (value, allowedValues, legacyMap = {}) => {
    if (!value) return '';
    if (allowedValues.includes(value)) return value;
    const mappedValue = legacyMap[value];
    if (mappedValue && allowedValues.includes(mappedValue)) return mappedValue;
    return '';
};

const normalizedServiceType = normalizeSelectValue(
    props.lead.service_type,
    serviceTypes.map(type => type.value),
    {
        student_visa: 'study_visa',
        tourist_visa: 'tour_visa',
        work_visa: 'immigration',
        consultation: 'other',
    },
);

const normalizedPriority = normalizeSelectValue(
    props.lead.priority,
    priorityOptions.map(option => option.value),
    { medium: 'normal' },
) || 'normal';

// Helper function to convert ISO datetime to yyyy-MM-dd format for date inputs
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    } catch {
        return '';
    }
};

// Initialize hasPassport based on existing passport data
const hasPassport = ref(!!(
    props.lead.passport?.passport_number ||
    props.lead.passport?.issue_date ||
    props.lead.passport?.expiry_date ||
    props.lead.passport?.place_of_issue ||
    props.lead.passport_number ||
    props.lead.passport_issue_date ||
    props.lead.passport_expiry_date ||
    props.lead.passport_place_of_issue
));
const showCustomService = ref(false);
const showCustomPackage = ref(false);

const passportExpiryError = ref('');

// ─── Form Customizer ───────────────────────────────────────────────────────────
const STORAGE_KEY = 'lead_edit_form_sections';

const allSections = [
    { key: 'personal',      label: 'Personal Information',    icon: 'User',      required: true },
    { key: 'address',       label: 'Address',                  icon: 'MapPin',   required: false },
    { key: 'service',       label: 'Service Details',          icon: 'Briefcase', required: true },
    { key: 'passport',      label: 'Passport Information',     icon: 'Globe',    required: false },
    { key: 'study_visa',    label: 'Study Visa Fields',        icon: 'Globe',    required: false },
    { key: 'tour_visa',     label: 'Tour Visa Fields',         icon: 'Globe',    required: false },
    { key: 'immigration',   label: 'Immigration Fields',       icon: 'Globe',    required: false },
    { key: 'prev_visits',   label: 'Previous Visit History',   icon: 'Globe',    required: false },
    { key: 'additional',    label: 'Additional Info (Budget/Visa)', icon: 'Globe', required: false },
    { key: 'dependents',    label: 'Dependents',               icon: 'User',     required: false },
    { key: 'services_pkg',  label: 'Services & Packages',      icon: 'Briefcase', required: false },
    { key: 'management',    label: 'Lead Management',          icon: 'Briefcase', required: true },
];

const loadSectionPrefs = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            const result = {};
            allSections.forEach(s => {
                result[s.key] = parsed[s.key] !== undefined ? parsed[s.key] : true;
            });
            return result;
        }
    } catch (e) { /* ignore */ }
    const d = {};
    allSections.forEach(s => { d[s.key] = true; });
    return d;
};

const sectionVisibility = ref(loadSectionPrefs());
const showCustomizer = ref(false);

const saveSectionPrefs = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(sectionVisibility.value));
};

const toggleSection = (key) => {
    const section = allSections.find(s => s.key === key);
    if (section?.required) return;
    sectionVisibility.value[key] = !sectionVisibility.value[key];
    saveSectionPrefs();
};

const resetSections = () => {
    allSections.forEach(s => { sectionVisibility.value[s.key] = true; });
    saveSectionPrefs();
};

const visibleCount = computed(() => Object.values(sectionVisibility.value).filter(Boolean).length);
// ────────────────────────────────────────────────────────────────────────────────

const form = useForm({
    full_name: props.lead.full_name || '',
    email: props.lead.email || '',
    phone: props.lead.phone || '',
    alternate_phone: props.lead.alternate_phone || '',
    address: props.lead.current_address || props.lead.address || '',
    city: props.lead.city || '',
    state: props.lead.state || '',
    country: props.lead.country || '',
    postal_code: props.lead.postal_code || '',
    service_type: normalizedServiceType,
    priority: normalizedPriority,
    status: props.lead.status || 'new',
    source: props.lead.lead_source || props.lead.source || '',
    destination_country: props.lead.destination_country || '',
    passport_number: props.lead.passport_number || '',
    date_of_birth: formatDateForInput(props.lead.date_of_birth),
    nationality: props.lead.nationality || '',
    education_level: props.lead.education_level || '',
    english_proficiency: props.lead.english_proficiency || '',
    budget: props.lead.budget || '',
    preferred_start_date: props.lead.preferred_start_date || '',
    notes: props.lead.notes || '',
    assigned_to: props.lead.assigned_to ? String(props.lead.assigned_to) : 'unassigned',
    selected_services: props.lead.services && props.lead.services.length > 0
        ? (Array.isArray(props.lead.services) ? props.lead.services : [...props.lead.services]).map(s => Number(s.id))
        : [],
    selected_packages: props.lead.packages && props.lead.packages.length > 0
        ? (Array.isArray(props.lead.packages) ? props.lead.packages : [...props.lead.packages]).map(p => Number(p.id))
        : [],
    service_selection_type: props.lead.service_selection_type || '',
    custom_service_name: '',
    custom_service_description: '',
    custom_service_type: 'visa',
    custom_service_price: '',
    custom_service_currency: 'BDT',
    custom_service_duration_days: '',
    custom_package_name: '',
    custom_package_description: '',
    custom_package_total_price: '',
    custom_package_currency: 'BDT',
    custom_package_duration_days: '',
    // Study Visa specific fields
    preferred_university: props.lead.study_preference?.preferred_university || props.lead.preferred_university || '',
    preferred_course: props.lead.study_preference?.preferred_course || props.lead.preferred_course || '',
    course_level: props.lead.study_preference?.course_level || props.lead.course_level || '',
    intended_start_date: formatDateForInput(props.lead.study_preference?.intended_start_date || props.lead.intended_start_date),
    intended_semester: props.lead.study_preference?.intended_semester || props.lead.intended_semester || '',
    program_duration: props.lead.study_preference?.program_duration || props.lead.program_duration || '',
    tuition_fee_range: props.lead.study_preference?.tuition_fee_range || props.lead.tuition_fee_range || '',
    budget_range: props.lead.budget_range || '',
    funding_source: props.lead.funding_source || '',
    has_language_proficiency: props.lead.language_test?.has_proficiency === 1 || props.lead.language_test?.has_proficiency === true || props.lead.has_language_proficiency || false,
    language_test_type: props.lead.language_test?.test_type || props.lead.language_test_type || '',
    language_test_score: props.lead.language_test?.score || props.lead.language_test_score || '',
    language_test_date: formatDateForInput(props.lead.language_test?.test_date || props.lead.language_test_date),
    language_test_expiry_date: formatDateForInput(props.lead.language_test?.expiry_date || props.lead.language_test_expiry_date),
    // Tour Visa specific fields
    number_of_tour_persons: props.lead.number_of_tour_persons || '',
    // Immigration/Work Visa specific fields
    current_occupation: props.lead.occupation?.current_occupation || props.lead.current_occupation || '',
    job_title: props.lead.occupation?.job_title || props.lead.job_title || '',
    company_name: props.lead.occupation?.company_name || props.lead.company_name || '',
    work_experience_years: props.lead.occupation?.experience_years || props.lead.work_experience_years || '',
    job_description: props.lead.occupation?.description || props.lead.job_description || '',
    current_salary: props.lead.occupation?.current_salary || props.lead.current_salary || '',
    // Visa information
    visa_status: props.lead.visa_status || 'new',
    application_type: props.lead.application_type || '',
    application_date: formatDateForInput(props.lead.application_date),
    previous_visa_history: props.lead.previous_visa_history || '',
    // Previous visit information (optional)
    previous_visits: (props.lead.travel_visa_histories && props.lead.travel_visa_histories.length > 0)
        ? props.lead.travel_visa_histories.map(h => ({
            country: h.country || '',
            year: h.year ?? '',
            visa_type: h.visa_type || '',
            person: h.person || '',
        }))
        : [{ country: '', year: '', visa_type: '', person: '' }],
    // Dependents
    has_dependents: props.lead.has_dependents || false,
    number_of_dependents: props.lead.number_of_dependents || 0,
    dependents_information: props.lead.dependents_information || '',
    // Passport fields
    passport_number: props.lead.passport?.passport_number || props.lead.passport_number || '',
    passport_issue_date: formatDateForInput(props.lead.passport?.issue_date || props.lead.passport_issue_date),
    passport_expiry_date: formatDateForInput(props.lead.passport?.expiry_date || props.lead.passport_expiry_date),
    passport_place_of_issue: props.lead.passport?.place_of_issue || props.lead.passport_place_of_issue || '',
});

const addPreviousVisit = () => {
    form.previous_visits.push({ country: '', year: '', visa_type: '', person: '' });
};

const removePreviousVisit = (index) => {
    if (form.previous_visits.length > 1) {
        form.previous_visits.splice(index, 1);
    }
};

const toggleService = (serviceId) => {
    // Ensure selected_services is an array
    if (!Array.isArray(form.selected_services)) {
        form.selected_services = [];
    }
    // Convert serviceId to number for consistent comparison
    const id = Number(serviceId);
    // Find index using strict comparison with converted IDs
    const index = form.selected_services.findIndex(sid => Number(sid) === id);
    if (index === -1) {
        form.selected_services.push(id);
    } else {
        form.selected_services.splice(index, 1);
    }
    // Update service_selection_type based on selections
    if (form.selected_services.length > 0 || form.selected_packages.length > 0) {
        form.service_selection_type = 'existing_service';
    } else {
        form.service_selection_type = '';
    }
};

const togglePackage = (packageId) => {
    // Ensure selected_packages is an array
    if (!Array.isArray(form.selected_packages)) {
        form.selected_packages = [];
    }
    // Convert packageId to number for consistent comparison
    const id = Number(packageId);
    // Find index using strict comparison with converted IDs
    const index = form.selected_packages.findIndex(pid => Number(pid) === id);
    if (index === -1) {
        form.selected_packages.push(id);
    } else {
        form.selected_packages.splice(index, 1);
    }
    // Update service_selection_type based on selections
    if (form.selected_services.length > 0 || form.selected_packages.length > 0) {
        form.service_selection_type = 'existing_package';
    } else {
        form.service_selection_type = '';
    }
};

const getSelectedLabel = (value, options) => {
    if (!value) return '';
    const option = options.find(opt => opt.value === value);
    return option ? option.label : '';
};

const getSelectedUserName = (userId) => {
    if (!userId) return 'Unassigned';
    const user = props.users.find(u => String(u.id) === String(userId));
    return user ? user.name : 'Unassigned';
};

const submit = () => {
    passportExpiryError.value = '';
    if (hasPassport.value && form.passport_expiry_date) {
        const expiry = new Date(form.passport_expiry_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        expiry.setHours(0, 0, 0, 0);
        if (expiry <= today) {
            passportExpiryError.value = 'The passport expiry date must be in the future.';
            return;
        }
    }
    // Set service_selection_type if services or packages are selected
    if (form.selected_services.length > 0 || form.selected_packages.length > 0) {
        if (form.selected_services.length > 0) {
            form.service_selection_type = 'existing_service';
        } else if (form.selected_packages.length > 0) {
            form.service_selection_type = 'existing_package';
        }
    } else {
        form.service_selection_type = '';
    }

    // Ensure arrays are always present before transform
    if (!Array.isArray(form.selected_services)) {
        form.selected_services = [];
    }
    if (!Array.isArray(form.selected_packages)) {
        form.selected_packages = [];
    }

    // Convert empty string to null for assigned_to and ensure arrays are integers
    form.transform((data) => {
        // Map source to lead_source for backend compatibility
        if (data.source) {
            data.lead_source = data.source;
        }
        if (data.assigned_to === '' || data.assigned_to === 'unassigned') {
            data.assigned_to = null;
        }
        if ((data.custom_service_name || '').trim() || (data.custom_package_name || '').trim()) {
            data.service_selection_type = 'custom';
        }
        // Ensure selected_services and selected_packages are arrays
        if (!Array.isArray(data.selected_services)) {
            data.selected_services = [];
        }
        if (!Array.isArray(data.selected_packages)) {
            data.selected_packages = [];
        }
        // Convert string IDs to integers for proper database sync
        data.selected_services = data.selected_services.map(id => Number(id)).filter(id => !isNaN(id) && id > 0);
        data.selected_packages = data.selected_packages.map(id => Number(id)).filter(id => !isNaN(id) && id > 0);
        // Always include these fields (even if empty) so controller can sync/detach
        data.selected_services = data.selected_services || [];
        data.selected_packages = data.selected_packages || [];
        return data;
    }).put(route('leads.update', props.lead.id), {
        onSuccess: () => {
            toast.success('Lead updated successfully!', {
                description: `${form.full_name}'s information has been updated.`,
            });
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            const errorMessage = Object.values(errors)[0] || 'Failed to update lead. Please check the form and try again.';
            toast.error('Failed to update lead', {
                description: errorMessage,
            });
        },
    });
};

// Watch for flash messages from backend
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
    if (flash?.message) {
        toast.info(flash.message);
    }
}, { deep: true, immediate: true });

// Watch passport fields to set hasPassport checkbox
watch([() => form.passport_number, () => form.passport_issue_date, () => form.passport_expiry_date, () => form.passport_place_of_issue], () => {
    if (form.passport_number || form.passport_issue_date || form.passport_expiry_date || form.passport_place_of_issue) {
        hasPassport.value = true;
    }
}, { immediate: true });

// Watch hasPassport to clear fields when unchecked
watch(hasPassport, (value) => {
    if (!value) {
        form.passport_number = '';
        form.passport_issue_date = '';
        form.passport_expiry_date = '';
        form.passport_place_of_issue = '';
    }
});

// Clear client-side passport expiry error when date changes
watch(() => form.passport_expiry_date, () => {
    passportExpiryError.value = '';
});

// Clear dependents fields when Has Dependents is unchecked
watch(() => form.has_dependents, (value) => {
    if (!value) {
        form.number_of_dependents = 0;
        form.dependents_information = '';
    }
});

// Show flash messages on mount
onMounted(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
    if (flash?.message) {
        toast.info(flash.message);
    }
});
</script>

<template>
    <Head :title="`Edit ${lead.full_name}`" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link
                :href="route('leads.show', lead.id)"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="flex-1">
                <h1 class="text-2xl font-bold tracking-tight">Edit Lead</h1>
                <p class="text-muted-foreground">Update {{ lead.full_name }}'s information.</p>
            </div>
            <!-- Customize Form Button -->
            <Button
                type="button"
                variant="outline"
                class="gap-2 shrink-0"
                @click="showCustomizer = true"
            >
                <Settings2 class="h-4 w-4" />
                Customize Form
                <span class="ml-1 text-xs text-muted-foreground">({{ visibleCount }}/{{ allSections.length }})</span>
            </Button>
        </div>

        <!-- Form Customizer Panel (slide-over) -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showCustomizer"
                    class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                    @click="showCustomizer = false"
                />
            </Transition>
            <Transition name="slide-right">
                <div
                    v-if="showCustomizer"
                    class="fixed right-0 top-0 z-50 h-full w-80 bg-background border-l shadow-2xl flex flex-col"
                >
                    <div class="flex items-center justify-between px-5 py-4 border-b">
                        <div class="flex items-center gap-2">
                            <Settings2 class="h-5 w-5 text-primary" />
                            <h2 class="font-semibold text-base">Customize Form</h2>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent transition-colors"
                            @click="showCustomizer = false"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-2">
                        <p class="text-xs text-muted-foreground pb-2">Toggle which sections are visible on the form. Required sections cannot be hidden.</p>
                        <div
                            v-for="section in allSections"
                            :key="section.key"
                            class="flex items-center justify-between p-3 rounded-lg border transition-colors"
                            :class="[
                                sectionVisibility[section.key] ? 'bg-card' : 'bg-muted/40',
                                section.required ? 'opacity-70' : 'cursor-pointer hover:bg-accent/40',
                            ]"
                            @click="toggleSection(section.key)"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-md flex items-center justify-center"
                                    :class="sectionVisibility[section.key] ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'"
                                >
                                    <Eye v-if="sectionVisibility[section.key]" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium leading-none">{{ section.label }}</p>
                                    <p v-if="section.required" class="text-xs text-muted-foreground mt-1">Required</p>
                                </div>
                            </div>
                            <Switch
                                :model-value="sectionVisibility[section.key]"
                                :disabled="section.required"
                                @click.stop
                                @update:model-value="toggleSection(section.key)"
                            />
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t flex gap-2">
                        <Button type="button" variant="outline" class="flex-1" @click="resetSections">
                            Reset to Default
                        </Button>
                        <Button type="button" class="flex-1" @click="showCustomizer = false">
                            Done
                        </Button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Dismissed sections banner -->
            <div v-if="visibleCount < allSections.length" class="flex items-center gap-2 text-sm text-muted-foreground bg-muted/40 border rounded-lg px-4 py-2">
                <EyeOff class="h-4 w-4 shrink-0" />
                <span>{{ allSections.length - visibleCount }} section(s) are hidden. <button type="button" class="underline hover:text-foreground" @click="showCustomizer = true">Manage visibility</button></span>
            </div>

            <!-- Personal Information -->
            <Card v-show="sectionVisibility.personal">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" /> Personal Information
                    </CardTitle>
                    <CardDescription>Basic contact details of the lead.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="full_name">Full Name</Label>
                            <Input id="full_name" v-model="form.full_name" placeholder="John Doe" :class="{ 'border-destructive': form.errors.full_name }" />
                            <p v-if="form.errors.full_name" class="text-sm text-destructive">{{ form.errors.full_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone">Phone/WhatsApp</Label>
                            <Input id="phone" v-model="form.phone" placeholder="+8801XXXXXXXXX" :class="{ 'border-destructive': form.errors.phone }" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="alternate_phone">Alternate Phone</Label>
                            <Input id="alternate_phone" v-model="form.alternate_phone" placeholder="+8801XXXXXXXXX" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="date_of_birth">Date of Birth</Label>
                            <Input id="date_of_birth" type="date" v-model="form.date_of_birth" />
                        </div>
                        <div class="space-y-2">
                            <Label for="nationality">Nationality</Label>
                            <Input id="nationality" v-model="form.nationality" placeholder="e.g., Bangladeshi" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Address -->
            <Card v-show="sectionVisibility.address">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MapPin class="h-5 w-5" /> Address
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="address">Street Address</Label>
                        <Input id="address" v-model="form.address" placeholder="123 Main St" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="city">City</Label>
                            <Input id="city" v-model="form.city" placeholder="City" />
                        </div>
                        <div class="space-y-2">
                            <Label for="state">State/Province</Label>
                            <Input id="state" v-model="form.state" placeholder="State" />
                        </div>
                        <div class="space-y-2">
                            <Label for="country">Country</Label>
                            <Input id="country" v-model="form.country" placeholder="Country" />
                        </div>
                        <div class="space-y-2">
                            <Label for="postal_code">Postal Code</Label>
                            <Input id="postal_code" v-model="form.postal_code" placeholder="12345" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Service Details -->
            <Card v-show="sectionVisibility.service">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Briefcase class="h-5 w-5" /> Service Details
                    </CardTitle>
                    <CardDescription>What service is the lead interested in?</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Service Type Selection -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="service_type">Service Type</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between" :class="{ 'border-destructive': form.errors.service_type }">
                                        <span>{{ serviceTypes.find(t => t.value === form.service_type)?.label || 'Select type' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.service_type">
                                        <DropdownMenuRadioItem v-for="type in serviceTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.service_type" class="text-sm text-destructive">{{ form.errors.service_type }}</p>
                        </div>
                        <div v-if="!isTourVisa" class="space-y-2">
                            <Label for="destination_country">Destination Country</Label>
                            <Input id="destination_country" v-model="form.destination_country" placeholder="e.g., Canada" />
                        </div>
                    </div>
                    <!-- Passport Information (Conditional) -->
                    <div v-show="sectionVisibility.passport" class="space-y-4 pt-4 border-t">
                        <div class="flex items-center gap-2">
                            <Switch id="has_passport" v-model="hasPassport" />
                            <Label for="has_passport" class="cursor-pointer font-medium">Has Passport</Label>
                        </div>
                        <div v-if="hasPassport || form.errors.passport_number || form.errors.passport_issue_date || form.errors.passport_expiry_date || form.errors.passport_place_of_issue" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="passport_number">Passport Number</Label>
                                <Input id="passport_number" v-model="form.passport_number" placeholder="AB1234567" :class="{ 'border-destructive': form.errors.passport_number }" />
                                <p v-if="form.errors.passport_number" class="text-sm text-destructive">{{ form.errors.passport_number }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="passport_issue_date">Issue Date</Label>
                                <Input id="passport_issue_date" type="date" v-model="form.passport_issue_date" :class="{ 'border-destructive': form.errors.passport_issue_date }" />
                                <p v-if="form.errors.passport_issue_date" class="text-sm text-destructive">{{ form.errors.passport_issue_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="passport_expiry_date">Expiry Date</Label>
                                <Input
                                    id="passport_expiry_date"
                                    type="date"
                                    v-model="form.passport_expiry_date"
                                    :class="{ 'border-destructive': form.errors.passport_expiry_date || passportExpiryError }"
                                />
                                <p v-if="form.errors.passport_expiry_date || passportExpiryError" class="text-sm text-destructive">
                                    {{ form.errors.passport_expiry_date || passportExpiryError }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="passport_place_of_issue">Place of Issue</Label>
                                <Input id="passport_place_of_issue" v-model="form.passport_place_of_issue" placeholder="e.g., Dhaka" :class="{ 'border-destructive': form.errors.passport_place_of_issue }" />
                                <p v-if="form.errors.passport_place_of_issue" class="text-sm text-destructive">{{ form.errors.passport_place_of_issue }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Study Visa Specific Fields -->
                    <div v-if="isStudyVisa && sectionVisibility.study_visa" class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold">Study Visa Information</h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="preferred_university">Preferred University</Label>
                                <Input id="preferred_university" v-model="form.preferred_university"
                                    placeholder="e.g., University of Toronto" />
                            </div>
                            <div class="space-y-2">
                                <Label for="preferred_course">Preferred Course</Label>
                                <Input id="preferred_course" v-model="form.preferred_course"
                                    placeholder="e.g., Computer Science" />
                            </div>
                            <div class="space-y-2">
                                <Label for="course_level">Course Level</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>{{courseLevelOptions.find(c => c.value === form.course_level)?.label
                                                || 'Select level'}}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start"
                                        class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.course_level">
                                            <DropdownMenuRadioItem v-for="level in courseLevelOptions"
                                                :key="level.value" :value="level.value">
                                                {{ level.label }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="space-y-2">
                                <Label for="intended_start_date">Intended Start Date</Label>
                                <Input id="intended_start_date" type="date" v-model="form.intended_start_date" />
                            </div>
                            <div class="space-y-2">
                                <Label for="intended_semester">Intended Semester</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>{{semesterOptions.find(s => s.value ===
                                                form.intended_semester)?.label || 'Select semester'}}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start"
                                        class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.intended_semester">
                                            <DropdownMenuRadioItem v-for="semester in semesterOptions"
                                                :key="semester.value" :value="semester.value">
                                                {{ semester.label }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="space-y-2">
                                <Label for="program_duration">Program Duration</Label>
                                <Input id="program_duration" v-model="form.program_duration"
                                    placeholder="e.g., 2 years" />
                            </div>
                            <div class="space-y-2">
                                <Label for="tuition_fee_range">Tuition Fee Range</Label>
                                <Input id="tuition_fee_range" v-model="form.tuition_fee_range"
                                    placeholder="e.g., ৳20,000 - ৳40,000" />
                            </div>
                            <div class="space-y-2">
                                <Label for="budget_range">Budget Range</Label>
                                <Input id="budget_range" v-model="form.budget_range"
                                    placeholder="e.g., ৳50,000 - ৳80,000" />
                            </div>
                            <div class="space-y-2">
                                <Label for="funding_source">Funding Source</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>{{fundingSourceOptions.find(f => f.value ===
                                                form.funding_source)?.label || 'Select source'}}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start"
                                        class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.funding_source">
                                            <DropdownMenuRadioItem v-for="source in fundingSourceOptions"
                                                :key="source.value" :value="source.value">
                                                {{ source.label }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                        <div class="space-y-4 pt-4 border-t">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="has_language_proficiency" :model-value="form.has_language_proficiency" @update:model-value="form.has_language_proficiency = $event" />
                                <Label for="has_language_proficiency" class="cursor-pointer">Has Language Proficiency Test</Label>
                            </div>
                            <div v-if="form.has_language_proficiency" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="space-y-2">
                                    <Label for="language_test_type">Test Type</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="w-full justify-between">
                                                <span>{{languageTestOptions.find(t => t.value ===
                                                    form.language_test_type)?.label || 'Select test'}}</span>
                                                <ChevronDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start"
                                            class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                            <DropdownMenuRadioGroup v-model="form.language_test_type">
                                                <DropdownMenuRadioItem v-for="test in languageTestOptions"
                                                    :key="test.value" :value="test.value">
                                                    {{ test.label }}
                                                </DropdownMenuRadioItem>
                                            </DropdownMenuRadioGroup>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                <div class="space-y-2">
                                    <Label for="language_test_score">Test Score</Label>
                                    <Input id="language_test_score" v-model="form.language_test_score"
                                        placeholder="e.g., 7.5" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="language_test_date">Test Date</Label>
                                    <Input id="language_test_date" type="date" v-model="form.language_test_date" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="language_test_expiry_date">Expiry Date</Label>
                                    <Input id="language_test_expiry_date" type="date"
                                        v-model="form.language_test_expiry_date" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tour Visa Specific Fields -->
                    <div v-if="isTourVisa && sectionVisibility.tour_visa" class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold">Tour Visa Information</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="number_of_tour_persons">Number of Tour Persons</Label>
                                <Input id="number_of_tour_persons" type="number" v-model="form.number_of_tour_persons"
                                    placeholder="e.g., 2" min="1" max="100" />
                            </div>
                            <div class="space-y-2">
                                <Label for="budget_range">Budget Range</Label>
                                <Input id="budget_range" v-model="form.budget_range"
                                    placeholder="e.g., ৳5,000 - ৳10,000" />
                            </div>
                        </div>
                    </div>

                    <!-- Work Visa / Immigration Specific Fields -->
                    <div v-if="(isWorkVisa || isImmigration) && sectionVisibility.immigration" class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold">{{ isWorkVisa ? 'Work Visa' : 'Immigration' }} Information
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="current_occupation">Current Occupation</Label>
                                <Input id="current_occupation" v-model="form.current_occupation"
                                    placeholder="e.g., Software Engineer" />
                            </div>
                            <div class="space-y-2">
                                <Label for="job_title">Job Title</Label>
                                <Input id="job_title" v-model="form.job_title" placeholder="e.g., Senior Developer" />
                            </div>
                            <div class="space-y-2">
                                <Label for="company_name">Company Name</Label>
                                <Input id="company_name" v-model="form.company_name" placeholder="e.g., Tech Corp" />
                            </div>
                            <div class="space-y-2">
                                <Label for="work_experience_years">Work Experience (Years)</Label>
                                <Input id="work_experience_years" v-model="form.work_experience_years"
                                    placeholder="e.g., 5" />
                            </div>
                            <div class="space-y-2">
                                <Label for="current_salary">Current Salary</Label>
                                <Input id="current_salary" type="number" v-model="form.current_salary"
                                    placeholder="e.g., 75000" />
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <Label for="job_description">Job Description</Label>
                                <Textarea id="job_description" v-model="form.job_description"
                                    placeholder="Brief description of current role..." rows="3" />
                            </div>
                        </div>
                    </div>

                    <!-- Common Fields (shown for all service types) -->
                    <div class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold">Additional Information</h3>

                        <!-- Previous visit information (optional) -->
                        <div v-show="sectionVisibility.prev_visits" class="space-y-4 pt-4 border-t">
                            <h4 class="text-sm font-medium">Previous visit information <span class="text-muted-foreground font-normal">(optional)</span></h4>
                            <div v-for="(visit, index) in form.previous_visits" :key="index"
                                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
                                <div class="space-y-2">
                                    <Label :for="`prev_country_${index}`">Country</Label>
                                    <Input :id="`prev_country_${index}`" v-model="visit.country"
                                        placeholder="e.g., Canada"
                                        :class="{ 'border-destructive': form.errors[`previous_visits.${index}.country`] }" />
                                    <p v-if="form.errors[`previous_visits.${index}.country`]"
                                        class="text-sm text-destructive">{{ form.errors[`previous_visits.${index}.country`] }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label :for="`prev_year_${index}`">Year</Label>
                                    <Input :id="`prev_year_${index}`" v-model.number="visit.year" type="number"
                                        placeholder="e.g., 2023" min="1900" max="2100"
                                        :class="{ 'border-destructive': form.errors[`previous_visits.${index}.year`] }" />
                                    <p v-if="form.errors[`previous_visits.${index}.year`]"
                                        class="text-sm text-destructive">{{ form.errors[`previous_visits.${index}.year`] }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label :for="`prev_visa_type_${index}`">Visa type</Label>
                                    <Input :id="`prev_visa_type_${index}`" v-model="visit.visa_type"
                                        placeholder="e.g., Tourist"
                                        :class="{ 'border-destructive': form.errors[`previous_visits.${index}.visa_type`] }" />
                                    <p v-if="form.errors[`previous_visits.${index}.visa_type`]"
                                        class="text-sm text-destructive">{{ form.errors[`previous_visits.${index}.visa_type`] }}</p>
                                </div>
                                <div class="space-y-2 flex gap-2">
                                    <div class="grow space-y-2">
                                        <Label :for="`prev_person_${index}`">Person</Label>
                                        <Input :id="`prev_person_${index}`" v-model="visit.person"
                                            placeholder="e.g., John Doe"
                                            :class="{ 'border-destructive': form.errors[`previous_visits.${index}.person`] }" />
                                        <p v-if="form.errors[`previous_visits.${index}.person`]"
                                            class="text-sm text-destructive">{{ form.errors[`previous_visits.${index}.person`] }}</p>
                                    </div>
                                    <Button v-if="form.previous_visits.length > 1" type="button" variant="outline"
                                        size="icon" class="shrink-0" @click="removePreviousVisit(index)" title="Remove">×</Button>
                                </div>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addPreviousVisit">Add another visit</Button>
                        </div>

                        <div v-show="sectionVisibility.additional" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2" v-if="!isStudyVisa">
                                <Label for="education_level">Education Level</Label>
                                <Input id="education_level" v-model="form.education_level"
                                    placeholder="e.g., Bachelor's" />
                            </div>
                            <div class="space-y-2" v-if="!isStudyVisa || !form.has_language_proficiency">
                                <Label for="english_proficiency">English Proficiency</Label>
                                <Input id="english_proficiency" v-model="form.english_proficiency"
                                    placeholder="e.g., IELTS 7.0" />
                            </div>
                            <div class="space-y-2">
                                <Label for="budget">Budget</Label>
                                <Input id="budget" v-model="form.budget" placeholder="e.g., ৳50,000" />
                            </div>
                        </div>
                        <div class="space-y-4 pt-4 border-t">
                            <div class="space-y-2">
                                <Label for="visa_status">Visa Status</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>{{visaStatusOptions.find(v => v.value === form.visa_status)?.label ||
                                                'Select status'}}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start"
                                        class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.visa_status">
                                            <DropdownMenuRadioItem v-for="status in visaStatusOptions"
                                                :key="status.value" :value="status.value">
                                                {{ status.label }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <!-- Visa Application Fields (only show if visa status is not 'new') -->
                            <div v-if="form.visa_status && form.visa_status !== 'new'"
                                class="grid gap-4 sm:grid-cols-2 pt-4 border-t">
                                <div class="space-y-2">
                                    <Label for="application_type">Application Type</Label>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline" class="w-full justify-between">
                                                <span>{{applicationTypeOptions.find(a => a.value ===
                                                    form.application_type)?.label || 'Select type'}}</span>
                                                <ChevronDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="start"
                                            class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                            <DropdownMenuRadioGroup v-model="form.application_type">
                                                <DropdownMenuRadioItem v-for="type in applicationTypeOptions"
                                                    :key="type.value" :value="type.value">
                                                    {{ type.label }}
                                                </DropdownMenuRadioItem>
                                            </DropdownMenuRadioGroup>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                <div class="space-y-2">
                                    <Label for="application_date">Application Date</Label>
                                    <Input id="application_date" type="date" v-model="form.application_date" />
                                </div>
                                <div class="space-y-2 sm:col-span-2">
                                    <Label for="previous_visa_history">Previous Visa History</Label>
                                    <Textarea id="previous_visa_history" v-model="form.previous_visa_history"
                                        placeholder="Any previous visa applications or history..." rows="3" />
                                </div>
                            </div>
                        </div>
                        <div v-show="sectionVisibility.dependents" class="space-y-4 pt-4 border-t">
                            <div class="flex items-center gap-2">
                                <Switch id="has_dependents" v-model="form.has_dependents" />
                                <Label for="has_dependents" class="cursor-pointer font-medium">Has Dependents</Label>
                            </div>
                            <div v-if="form.has_dependents || form.errors.number_of_dependents || form.errors.dependents_information" class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="number_of_dependents">Number of Dependents</Label>
                                    <Input
                                        id="number_of_dependents"
                                        type="number"
                                        v-model="form.number_of_dependents"
                                        placeholder="e.g., 2"
                                        min="0"
                                        :class="{ 'border-destructive': form.errors.number_of_dependents }"
                                    />
                                    <p v-if="form.errors.number_of_dependents" class="text-sm text-destructive">{{ form.errors.number_of_dependents }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="dependents_information">Dependents Information</Label>
                                    <Textarea
                                        id="dependents_information"
                                        v-model="form.dependents_information"
                                        placeholder="Details about dependents..."
                                        rows="3"
                                        :class="{ 'border-destructive': form.errors.dependents_information }"
                                    />
                                    <p v-if="form.errors.dependents_information" class="text-sm text-destructive">{{ form.errors.dependents_information }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Services and Packages -->
            <div class="space-y-6" v-if="(canViewServices || canViewPackages) && sectionVisibility.services_pkg">
                <div class="grid gap-6 lg:grid-cols-2">
                    <Card v-if="canViewServices">
                        <CardHeader>
                            <CardTitle>Services</CardTitle>
                            <CardDescription>Select applicable services or add a custom one.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="services.length" class="grid gap-2 sm:grid-cols-2">
                                <div v-for="service in services" :key="service.id" class="flex items-center space-x-2">
                                    <Checkbox :id="`service-${service.id}`" :checked="form.selected_services.some(sid => Number(sid) === Number(service.id))" @update:model-value="toggleService(service.id)" />
                                    <Label :for="`service-${service.id}`" class="cursor-pointer text-sm">{{ service.name }}</Label>
                                </div>
                            </div>
                            <Button type="button" variant="outline" size="sm" class="mt-3" @click="showCustomService = !showCustomService">
                                {{ showCustomService ? 'Hide custom service' : 'Add custom service' }}
                            </Button>
                        </CardContent>
                    </Card>
                    <Card v-if="canViewPackages">
                        <CardHeader>
                            <CardTitle>Packages</CardTitle>
                            <CardDescription>Select applicable packages or add a custom one.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="packages.length" class="grid gap-2 sm:grid-cols-2">
                                <div v-for="pkg in packages" :key="pkg.id" class="flex items-center space-x-2">
                                    <Checkbox :id="`package-${pkg.id}`" :checked="form.selected_packages.some(pid => Number(pid) === Number(pkg.id))" @update:model-value="togglePackage(pkg.id)" />
                                    <Label :for="`package-${pkg.id}`" class="cursor-pointer text-sm">{{ pkg.name }}</Label>
                                </div>
                            </div>
                            <Button type="button" variant="outline" size="sm" class="mt-3" @click="showCustomPackage = !showCustomPackage">
                                {{ showCustomPackage ? 'Hide custom package' : 'Add custom package' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <Card v-if="showCustomService && canViewServices">
                    <CardHeader>
                        <CardTitle>Custom Service</CardTitle>
                        <CardDescription>Add a new service for this lead. It will be created and linked to the lead.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="custom_service_name">Service name</Label>
                                <Input id="custom_service_name" v-model="form.custom_service_name" placeholder="e.g., Canada Tourist Visa" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_service_type">Type</Label>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>{{ customServiceTypeOptions.find(t => t.value === form.custom_service_type)?.label || 'Select type' }}</span>
                                            <ChevronDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup v-model="form.custom_service_type">
                                            <DropdownMenuRadioItem v-for="t in customServiceTypeOptions" :key="t.value" :value="t.value">{{ t.label }}</DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_service_price">Price</Label>
                                <Input id="custom_service_price" v-model="form.custom_service_price" type="number" min="0" step="0.01" placeholder="0" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_service_currency">Currency</Label>
                                <Input id="custom_service_currency" v-model="form.custom_service_currency" placeholder="BDT" maxlength="10" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_service_duration_days">Duration (days)</Label>
                                <Input id="custom_service_duration_days" v-model="form.custom_service_duration_days" type="number" min="1" placeholder="e.g., 30" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="custom_service_description">Description</Label>
                            <Textarea id="custom_service_description" v-model="form.custom_service_description" placeholder="Optional description" rows="2" />
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="showCustomPackage && canViewPackages">
                    <CardHeader>
                        <CardTitle>Custom Package</CardTitle>
                        <CardDescription>Add a new package for this lead. It will be created and linked to the lead.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="custom_package_name">Package name</Label>
                                <Input id="custom_package_name" v-model="form.custom_package_name" placeholder="e.g., Premium Visa Package" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_package_total_price">Total price</Label>
                                <Input id="custom_package_total_price" v-model="form.custom_package_total_price" type="number" min="0" step="0.01" placeholder="0" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_package_currency">Currency</Label>
                                <Input id="custom_package_currency" v-model="form.custom_package_currency" placeholder="BDT" maxlength="10" />
                            </div>
                            <div class="space-y-2">
                                <Label for="custom_package_duration_days">Duration (days)</Label>
                                <Input id="custom_package_duration_days" v-model="form.custom_package_duration_days" type="number" min="1" placeholder="e.g., 14" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="custom_package_description">Description</Label>
                            <Textarea id="custom_package_description" v-model="form.custom_package_description" placeholder="Optional description" rows="2" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Lead Management -->
            <Card v-show="sectionVisibility.management">
                <CardHeader>
                    <CardTitle>Lead Management</CardTitle>
                    <CardDescription>Priority, source, status, and assignment.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <DropdownMenu v-if="!isStatusLocked">
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.status }">
                                        {{ getSelectedLabel(form.status, statusOptions) || 'Select status' }}
                                        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.status">
                                        <DropdownMenuRadioItem v-for="s in statusOptions" :key="s.value"
                                            :value="s.value">
                                            {{ s.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <div v-else class="flex h-10 w-full items-center rounded-lg border border-input bg-muted/50 px-3 py-2 text-sm">
                                {{ getSelectedLabel(form.status, statusOptions) || 'Converted' }}
                            </div>
                            <p v-if="isStatusLocked" class="text-xs text-muted-foreground">Converted lead status cannot be changed.</p>
                            <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.priority }">
                                        {{ getSelectedLabel(form.priority, priorityOptions) || 'Select priority' }}
                                        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.priority">
                                        <DropdownMenuRadioItem v-for="p in priorityOptions" :key="p.value"
                                            :value="p.value">
                                            {{ p.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.priority" class="text-sm text-destructive">{{ form.errors.priority }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="source">Lead Source</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.source || form.errors.lead_source }">
                                        {{ getSelectedLabel(form.source, sourceOptions) || 'Select source' }}
                                        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.source">
                                        <DropdownMenuRadioItem v-for="s in sourceOptions" :key="s.value"
                                            :value="s.value">
                                            {{ s.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.source || form.errors.lead_source" class="text-sm text-destructive">{{
                                form.errors.source || form.errors.lead_source }}</p>
                        </div>
                        <div class="space-y-2" v-if="users.length">
                            <Label>Assigned To</Label>
                            <div
                                class="flex h-10 w-full items-center rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background text-muted-foreground cursor-not-allowed opacity-50">
                                {{ getSelectedUserName(form.assigned_to) }}
                            </div>
                            <p class="text-[10px] text-muted-foreground mt-1">To change assignment, please use the
                                Transfer Lead action.</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" v-model="form.notes" placeholder="Any additional notes about this lead..." rows="3" />
                        <p v-if="form.errors.notes" class="text-sm text-destructive">{{ form.errors.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Update Lead
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('leads.show', lead.id)">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-right-enter-from,
.slide-right-leave-to {
    transform: translateX(100%);
}
</style>
