<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert/index.js';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { ScrollArea } from '@/Components/ui/scroll-area/index.js';
import {
    ArrowLeft,
    Upload,
    FileDown,
    FileText,
    CheckCircle,
    AlertCircle,
    Info,
    Loader2,
    Download,
    RefreshCw,
    X,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const page = usePage();

const form = useForm({
    csv_file: null,
});

const updateForm = useForm({
    csv_file: null,
});

const fileName = ref('');
const showAllFields = ref(false);
const showErrorsModal = ref(false);

const hasErrors = computed(() => {
    return page.props.flash?.bulk_upload_errors && page.props.flash.bulk_upload_errors.length > 0;
});

const errorCount = computed(() => {
    return page.props.flash?.bulk_upload_total_errors || page.props.flash?.bulk_upload_errors?.length || 0;
});

const successMessage = computed(() => {
    return page.props.flash?.success || '';
});

const hasFailedRows = computed(() => {
    return successMessage.value.includes('failed') && hasErrors.value;
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10MB limit. Please select a smaller file.');
            event.target.value = '';
            form.csv_file = null;
            fileName.value = '';
            return;
        }
        form.csv_file = file;
        fileName.value = file.name;
    }
};

const submit = () => {
    form.post(route('leads.bulk-upload.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const submitUpdate = () => {
    if (!form.csv_file) {
        alert('Please select a CSV file first.');
        return;
    }

    updateForm.csv_file = form.csv_file;
    updateForm.post(route('leads.bulk-upload.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const downloadSampleCsv = () => {
    const csvContent = `full_name,phone,email,alternate_phone,date_of_birth,place_of_birth,gender,nationality,current_country,marital_status,passport_number,passport_issue_date,passport_expiry_date,passport_place_of_issue,current_address,city,state,postal_code,country,education_level,field_of_study,institution_name,graduation_year,gpa_score,current_occupation,job_title,company_name,work_experience_years,job_description,current_salary,service_type,destination_country,secondary_destination_country,preferred_university,preferred_course,course_level,intended_start_date,intended_semester,program_duration,tuition_fee_range,budget_range,funding_source,has_bank_statement,has_language_proficiency,language_test_type,language_test_score,language_test_date,language_test_expiry_date,has_dependents,number_of_dependents,dependents_information,visa_status,application_type,application_date,visa_expiry_date,rejection_reason,has_previous_visa_rejections,number_of_previous_rejections,previous_visa_history,is_sponsor_required,sponsor_name,sponsor_relationship,notes,internal_notes,special_requirements,how_did_you_find_us,lead_source,lead_source_link,status,priority
John Doe,+1234567890,john.doe@example.com,+1234567891,1990-01-15,New York,male,American,United States,single,AB123456,2015-01-10,2030-01-10,New York,"123 Main St",New York,NY,10001,United States,Bachelor,Computer Science,NYU,2012,3.8,Software Engineer,Senior Developer,Tech Corp,10,"Software development",75000,study_visa,Canada,,University of Toronto,Computer Science,Master,2025-09-01,Fall,24 months,30000-50000,50000-80000,Personal savings,1,1,IELTS,7.5,2024-01-15,2026-01-15,0,0,,new,online,,,,0,0,,0,,,"Interested in Master's program","High potential lead",Needs scholarship,Google,Website,https://example.com,new,high
Jane Smith,+1234567891,jane.smith@example.com,,1992-05-20,London,female,British,United Kingdom,married,UK789012,2018-05-15,2028-05-15,London,"456 Oak Ave",London,,SW1A 1AA,United Kingdom,Master,Education,Oxford,2015,3.9,Teacher,Primary Teacher,School District,5,"Teaching primary students",45000,study_visa,Australia,,University of Sydney,Education,PhD,2025-02-01,Spring,36 months,20000-40000,40000-60000,Scholarship,1,1,IELTS,8.0,2023-12-10,2025-12-10,1,2,"Two children aged 5 and 7",new,offline,,,,0,0,,0,,,"PhD in Education","Research interest",None,Referral,Friend,https://example.com,new,normal`;

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'sample_leads.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const allColumns = [
    // Personal Information
    { category: 'Personal Information', fields: ['full_name', 'phone', 'email', 'alternate_phone', 'date_of_birth', 'place_of_birth', 'gender', 'nationality', 'current_country', 'marital_status'] },
    // Passport Information
    { category: 'Passport Information', fields: ['passport_number', 'passport_issue_date', 'passport_expiry_date', 'passport_place_of_issue'] },
    // Address Information
    { category: 'Address Information', fields: ['current_address', 'city', 'state', 'postal_code', 'country'] },
    // Education & Employment
    { category: 'Education & Employment', fields: ['education_level', 'field_of_study', 'institution_name', 'graduation_year', 'gpa_score', 'current_occupation', 'job_title', 'company_name', 'work_experience_years', 'job_description', 'current_salary'] },
    // Service & Destination
    { category: 'Service & Destination', fields: ['service_type', 'destination_country', 'secondary_destination_country', 'preferred_university', 'preferred_course', 'course_level', 'intended_start_date', 'intended_semester', 'program_duration', 'tuition_fee_range', 'budget_range'] },
    // Financial & Language
    { category: 'Financial & Language', fields: ['funding_source', 'has_bank_statement', 'has_language_proficiency', 'language_test_type', 'language_test_score', 'language_test_date', 'language_test_expiry_date'] },
    // Dependents
    { category: 'Dependents', fields: ['has_dependents', 'number_of_dependents', 'dependents_information'] },
    // Visa Information
    { category: 'Visa Information', fields: ['visa_status', 'application_type', 'application_date', 'visa_expiry_date', 'rejection_reason', 'has_previous_visa_rejections', 'number_of_previous_rejections', 'previous_visa_history'] },
    // Sponsor Information
    { category: 'Sponsor Information', fields: ['is_sponsor_required', 'sponsor_name', 'sponsor_relationship'] },
    // Additional Information
    { category: 'Additional Information', fields: ['notes', 'internal_notes', 'special_requirements', 'how_did_you_find_us', 'lead_source', 'lead_source_link'] },
    // Status & Priority
    { category: 'Status & Priority', fields: ['status', 'priority'] },
];
</script>

<template>
    <Head title="Bulk Upload Leads" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('leads.index')" 
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Bulk Upload Leads</h1>
                    <p class="text-sm text-muted-foreground">Upload multiple leads at once using a CSV file</p>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <Alert v-if="page.props.errors && Object.keys(page.props.errors).length > 0 && !page.props.flash?.bulk_upload_errors" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Validation Errors</AlertTitle>
            <AlertDescription>
                <ul class="list-disc pl-5 mt-2">
                    <li v-for="(error, key) in page.props.errors" :key="key">{{ error }}</li>
                </ul>
            </AlertDescription>
        </Alert>

        <!-- Success Message -->
        <Alert v-if="page.props.flash?.success" variant="default" class="border-green-200 bg-green-50">
            <CheckCircle class="h-4 w-4 text-green-600" />
            <AlertTitle class="text-green-800">Success</AlertTitle>
            <AlertDescription class="text-green-700">
                <div class="flex items-center justify-between">
                    <span>{{ page.props.flash.success }}</span>
                    <Button 
                        v-if="hasFailedRows" 
                        variant="outline" 
                        size="sm" 
                        class="ml-4 h-7 text-xs"
                        @click="showErrorsModal = true"
                    >
                        <AlertCircle class="mr-1.5 h-3.5 w-3.5" />
                        View Failed Rows ({{ errorCount }})
                    </Button>
                </div>
            </AlertDescription>
        </Alert>

        <!-- Bulk Upload Errors (Inline) -->
        <Alert v-if="page.props.flash?.bulk_upload_errors && !page.props.flash?.success" variant="default" class="border-amber-200 bg-amber-50">
            <AlertCircle class="h-4 w-4 text-amber-600" />
            <AlertTitle class="text-amber-800">Upload Errors</AlertTitle>
            <AlertDescription class="text-amber-700">
                <div class="mb-2">
                    <span v-if="page.props.flash.bulk_upload_total_errors && page.props.flash.bulk_upload_total_errors > page.props.flash.bulk_upload_errors.length">
                        Showing first {{ page.props.flash.bulk_upload_errors.length }} of {{ page.props.flash.bulk_upload_total_errors }} errors:
                    </span>
                    <span v-else>
                        {{ page.props.flash.bulk_upload_errors.length }} error(s):
                    </span>
                </div>
                <ul class="list-decimal pl-5 max-h-[300px] overflow-y-auto">
                    <li v-for="(error, index) in page.props.flash.bulk_upload_errors" :key="index" class="mb-1">
                        {{ error }}
                    </li>
                </ul>
                <div class="mt-4">
                    <Button 
                        variant="outline" 
                        size="sm" 
                        class="h-7 text-xs"
                        @click="showErrorsModal = true"
                    >
                        View All Errors
                    </Button>
                </div>
                <div v-if="page.props.flash.bulk_upload_total_errors && page.props.flash.bulk_upload_total_errors > page.props.flash.bulk_upload_errors.length" class="mt-4 pt-4 border-t border-amber-300 italic text-sm">
                    Note: Only the first {{ page.props.flash.bulk_upload_errors.length }} errors are displayed. Please fix these errors and try again.
                </div>
            </AlertDescription>
        </Alert>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Upload Form -->
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Upload CSV File</CardTitle>
                    <CardDescription>Select a CSV file to upload and process leads</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="csv_file" class="block text-sm font-medium mb-2">
                                CSV File <span class="text-destructive">*</span>
                            </label>
                            <input
                                type="file"
                                id="csv_file"
                                accept=".csv,.txt"
                                required
                                @change="handleFileSelect"
                                class="w-full p-3 border-2 border-dashed border-muted rounded-lg bg-muted/50 hover:border-primary transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                            <p class="text-xs text-muted-foreground mt-2 flex items-center gap-1">
                                <Info class="h-3 w-3" />
                                Maximum file size: 10MB. File must be in CSV format.
                            </p>
                            <p v-if="fileName" class="text-sm text-muted-foreground mt-2">
                                Selected: <span class="font-medium">{{ fileName }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing || !form.csv_file">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Upload v-else class="mr-2 h-4 w-4" />
                                Upload & Process
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                @click="submitUpdate" 
                                :disabled="updateForm.processing || !form.csv_file"
                            >
                                <Loader2 v-if="updateForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <RefreshCw v-else class="mr-2 h-4 w-4" />
                                Update Existing Leads
                            </Button>
                            <Button variant="ghost" type="button" as-child>
                                <Link :href="route('leads.index')">Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <Button variant="outline" class="w-full justify-start" @click="downloadSampleCsv">
                        <Download class="mr-2 h-4 w-4" />
                        Download Sample CSV
                    </Button>
                    <Button variant="outline" class="w-full justify-start" as-child>
                        <Link :href="route('leads.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Leads
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>

        <!-- Instructions -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Info class="h-5 w-5" />
                    CSV File Format Instructions
                </CardTitle>
                <CardDescription>Follow these guidelines to ensure successful lead uploads</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Required Fields -->
                <div>
                    <h3 class="text-sm font-semibold mb-3">Required Fields</h3>
                    <div class="bg-muted/50 rounded-lg p-4 space-y-2">
                        <div class="flex items-start gap-2">
                            <CheckCircle class="h-4 w-4 text-green-600 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-sm font-medium">Name</p>
                                <p class="text-xs text-muted-foreground">
                                    Use <code>full_name</code>, <code>full name</code>, <code>name</code>, or combine <code>first_name</code> + <code>last_name</code>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <CheckCircle class="h-4 w-4 text-green-600 mt-0.5 shrink-0" />
                            <div>
                                <p class="text-sm font-medium">Phone Number</p>
                                <p class="text-xs text-muted-foreground">
                                    Use <code>phone</code>, <code>mobile</code>, or <code>phone_number</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format Guidelines -->
                <div>
                    <h3 class="text-sm font-semibold mb-3">Format Guidelines</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-muted/50 rounded-lg p-4">
                            <p class="text-sm font-medium mb-2">Date Formats</p>
                            <ul class="text-xs text-muted-foreground space-y-1">
                                <li>• YYYY-MM-DD (e.g., 2025-01-15)</li>
                                <li>• DD/MM/YYYY (e.g., 15/01/2025)</li>
                                <li>• DD-MM-YYYY (e.g., 15-01-2025)</li>
                            </ul>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-4">
                            <p class="text-sm font-medium mb-2">Boolean Fields</p>
                            <ul class="text-xs text-muted-foreground space-y-1">
                                <li>• Use: <code>1</code>, <code>yes</code>, <code>true</code>, or <code>y</code></li>
                                <li>• For false: <code>0</code>, <code>no</code>, <code>false</code>, or <code>n</code></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- All Available Columns -->
                <div>
                    <Button 
                        variant="ghost" 
                        class="w-full justify-between mb-3"
                        @click="showAllFields = !showAllFields"
                    >
                        <span class="flex items-center gap-2">
                            <FileText class="h-4 w-4" />
                            All Available Columns
                        </span>
                        <span class="text-muted-foreground text-xs">
                            {{ showAllFields ? 'Hide' : 'Show' }}
                        </span>
                    </Button>
                    
                    <div v-show="showAllFields" class="space-y-4 border rounded-lg p-4 bg-muted/30 max-h-[500px] overflow-y-auto">
                        <div 
                            v-for="category in allColumns" 
                            :key="category.category"
                            class="space-y-2"
                        >
                            <h4 class="text-sm font-semibold text-foreground">{{ category.category }}</h4>
                            <div class="flex flex-wrap gap-2">
                                <code 
                                    v-for="field in category.fields" 
                                    :key="field"
                                    class="text-xs bg-background border rounded px-2 py-1"
                                >
                                    {{ field }}
                                </code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <Alert>
                    <Info class="h-4 w-4" />
                    <AlertTitle>Important Notes</AlertTitle>
                    <AlertDescription class="space-y-2">
                        <p>• The first row of your CSV file must contain column headers</p>
                        <p>• Column names are case-insensitive and can include spaces or underscores</p>
                        <p>• For updating existing leads, include <code>phone</code> or <code>email</code> to match records</p>
                        <p>• Empty rows will be automatically skipped</p>
                        <p>• Maximum file size: 10MB</p>
                    </AlertDescription>
                </Alert>
            </CardContent>
        </Card>

        <!-- Errors Modal -->
        <Dialog :open="showErrorsModal" @update:open="showErrorsModal = $event">
            <DialogContent v-if="showErrorsModal" class="sm:max-w-4xl max-h-[90vh] flex flex-col">
                <DialogHeader class="shrink-0">
                    <DialogTitle class="flex items-center gap-2">
                        <AlertCircle class="h-5 w-5 text-destructive" />
                        Failed Rows Details
                    </DialogTitle>
                    <DialogDescription>
                        <div class="flex items-center gap-2 mt-2">
                            <span>Total failed rows: <strong>{{ errorCount }}</strong></span>
                            <span v-if="page.props.flash?.bulk_upload_total_errors && page.props.flash.bulk_upload_total_errors > page.props.flash.bulk_upload_errors.length" class="text-xs text-muted-foreground">
                                (Showing first {{ page.props.flash.bulk_upload_errors.length }})
                            </span>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <ScrollArea class="flex-1 min-h-0 pr-4">
                    <div class="space-y-3 py-2">
                        <div 
                            v-for="(error, index) in page.props.flash?.bulk_upload_errors || []" 
                            :key="index"
                            class="flex items-start gap-3 p-3 rounded-lg border bg-muted/30 hover:bg-muted/50 transition-colors"
                        >
                            <div class="shrink-0 mt-0.5">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-destructive/10 text-destructive text-xs font-semibold">
                                    {{ index + 1 }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-foreground wrap-break-word">{{ error }}</p>
                            </div>
                        </div>
                    </div>
                </ScrollArea>

                <DialogFooter class="shrink-0">
                    <Button variant="outline" @click="showErrorsModal = false">
                        Close
                    </Button>
                    <Button 
                        variant="outline" 
                        @click="downloadSampleCsv"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Download Sample CSV
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
