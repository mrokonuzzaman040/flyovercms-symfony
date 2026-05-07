<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    ArrowLeft,
    FileText,
    Upload,
    Eye,
    Download,
    Pencil,
    Trash2,
    Check,
    X,
    User,
    Calendar,
    Info,
    Loader2,
    ChevronDown,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    documentRequest: { type: Object, required: true },
    documents: { type: Array, default: () => [] },
    documentTypes: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission } = usePermissions();

const uploadModal = ref({ open: false });
const fulfillModal = ref({ open: false });
const uploadDocument = ref(false);

const uploadForm = useForm({
    document_type: props.documentRequest.document_type,
    documents: [],
    description: '',
    document_request_id: props.documentRequest.id,
});

const fulfillForm = useForm({
    upload_document: false,
    document_type: props.documentRequest.document_type,
    documents: [],
    description: '',
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return 'N/A';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const submitUpload = () => {
    if (!props.documentRequest?.lead?.id) return;
    uploadForm.post(route('leads.documents.store', props.documentRequest.lead.id), {
        onSuccess: () => {
            uploadModal.value.open = false;
            uploadForm.reset();
        },
    });
};

const submitFulfill = () => {
    fulfillForm.post(route('document-requests.fulfill', props.documentRequest.id), {
        onSuccess: () => {
            fulfillModal.value.open = false;
            fulfillForm.reset();
        },
    });
};

const cancelRequest = () => {
    if (confirm('Are you sure you want to cancel this document request?')) {
        router.post(route('document-requests.cancel', props.documentRequest.id));
    }
};

const deleteDocument = (documentId) => {
    if (!props.documentRequest?.lead?.id) return;
    if (confirm('Are you sure you want to delete this document?')) {
        router.delete(route('leads.documents.destroy', [props.documentRequest.lead.id, documentId]));
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        fulfilled: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>

    <Head title="Document Request Details" />

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <Link :href="route('document-requests.index')"
                class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-3 mb-2">
                <ArrowLeft class="h-4 w-4" />
                Back to Document Requests
            </Link>
            <h1 class="text-2xl font-bold tracking-tight">Document Request Details</h1>
            <p class="text-sm text-muted-foreground mt-1">View details and manage document request</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Request Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm flex items-center gap-2">
                            <Info class="h-4 w-4 text-primary" />
                            Request Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-xs text-muted-foreground uppercase">Document Type</Label>
                            <p class="font-semibold text-sm mt-1">{{ documentRequest.document_type_name }}</p>
                        </div>
                        <div v-if="documentRequest.message">
                            <Label class="text-xs text-muted-foreground uppercase">Message</Label>
                            <div class="mt-1 p-3 rounded-lg bg-muted text-sm">
                                {{ documentRequest.message }}
                            </div>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground uppercase">Status</Label>
                            <div class="mt-1">
                                <Badge :class="getStatusBadgeClass(documentRequest.status)" class="text-xs capitalize">
                                    {{ documentRequest.status }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Lead Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm flex items-center gap-2">
                            <User class="h-4 w-4 text-primary" />
                            Lead Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="documentRequest?.lead">
                            <Label class="text-xs text-muted-foreground uppercase">Lead Name</Label>
                            <Link v-if="documentRequest.lead.id" :href="route('leads.show', documentRequest.lead.id)"
                                class="font-semibold text-sm text-primary hover:underline mt-1 block">
                                {{ documentRequest.lead.full_name || 'N/A' }}
                            </Link>
                            <p v-else class="font-semibold text-sm mt-1">{{ documentRequest.lead.full_name || 'N/A' }}
                            </p>
                        </div>
                        <div v-if="documentRequest?.lead">
                            <Label class="text-xs text-muted-foreground uppercase">Email</Label>
                            <p class="text-sm mt-1">{{ documentRequest.lead.email || 'N/A' }}</p>
                        </div>
                        <div v-if="documentRequest?.lead">
                            <Label class="text-xs text-muted-foreground uppercase">Phone</Label>
                            <p class="text-sm mt-1">{{ documentRequest.lead.phone || 'N/A' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Documents Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm flex items-center gap-2">
                                <FileText class="h-4 w-4 text-primary" />
                                Documents ({{ documentRequest.document_type_name }})
                            </CardTitle>
                            <Button v-if="hasPermission('upload-documents')" size="sm" class="h-8 text-xs"
                                @click="uploadModal.open = true">
                                <Upload class="mr-1.5 h-3.5 w-3.5" />
                                Upload
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="documents.length > 0" class="space-y-2">
                            <div v-for="document in documents" :key="document.id"
                                class="p-3 rounded-lg border hover:border-primary transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground flex-shrink-0">
                                            <FileText class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-sm truncate">{{ document.original_name }}</p>
                                            <div class="flex items-center gap-3 mt-1 text-xs text-muted-foreground">
                                                <span class="flex items-center gap-1">
                                                    <User class="h-3 w-3" />
                                                    {{ document.uploader?.name }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <Calendar class="h-3 w-3" />
                                                    {{ formatDate(document.uploaded_at) }}
                                                </span>
                                            </div>
                                            <p v-if="document.description"
                                                class="text-xs text-muted-foreground mt-1 italic line-clamp-2">
                                                {{ document.description }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <Button v-if="hasPermission('view-documents') && documentRequest?.lead?.id"
                                            variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link
                                                :href="route('leads.documents.preview', [documentRequest.lead.id, document.id])"
                                                target="_blank">
                                                <Eye class="h-3.5 w-3.5" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('view-documents') && documentRequest?.lead?.id"
                                            variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link
                                                :href="route('leads.documents.download', [documentRequest.lead.id, document.id])">
                                                <Download class="h-3.5 w-3.5" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('edit-documents') && documentRequest?.lead?.id"
                                            variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link
                                                :href="route('leads.documents.edit', [documentRequest.lead.id, document.id])">
                                                <Pencil class="h-3.5 w-3.5" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('delete-documents')" variant="ghost" size="icon"
                                            class="h-7 w-7 text-destructive" @click="deleteDocument(document.id)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <FileText class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p class="text-sm font-semibold mb-1">No documents found</p>
                            <p class="text-xs text-muted-foreground">
                                No {{ documentRequest.document_type_name.toLowerCase() }} documents have been uploaded
                                for this lead yet.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Request Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm">Request Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-xs text-muted-foreground uppercase">Requested By</Label>
                            <p class="font-semibold text-sm mt-1">{{ documentRequest.requester?.name }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ formatDate(documentRequest.requested_at) }}
                            </p>
                        </div>
                        <div v-if="documentRequest.fulfiller">
                            <Label class="text-xs text-muted-foreground uppercase">Fulfilled By</Label>
                            <p class="font-semibold text-sm mt-1">{{ documentRequest.fulfiller.name }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ formatDate(documentRequest.fulfilled_at) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <Card v-if="documentRequest.status === 'pending' && hasPermission('view-documents')">
                    <CardHeader>
                        <CardTitle class="text-sm">Actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Button class="w-full" @click="fulfillModal.open = true">
                            <Check class="mr-2 h-4 w-4" />
                            Mark as Fulfilled
                        </Button>
                        <Button variant="destructive" class="w-full" @click="cancelRequest">
                            <X class="mr-2 h-4 w-4" />
                            Cancel Request
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Upload Document Modal -->
        <Dialog v-model:open="uploadModal.open">
            <DialogContent v-if="uploadModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm">Upload Document</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Document Type</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-9 text-xs px-3 font-normal">
                                    {{
                                        documentTypes[uploadForm.document_type] ||
                                        'Select a document type'
                                    }}
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                class="w-[--radix-dropdown-menu-trigger-width] min-w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuItem v-for="(label, key) in documentTypes" :key="key"
                                    @click="uploadForm.document_type = key">
                                    {{ label }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">
                            Upload Documents <span class="text-destructive">*</span>
                        </Label>
                        <Input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                            @change="(e) => uploadForm.documents = Array.from(e.target.files)" class="h-9 text-xs"
                            required />
                        <p class="text-xs text-muted-foreground">
                            Max 10MB per file. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Description (Optional)</Label>
                        <Textarea v-model="uploadForm.description" rows="3"
                            placeholder="Add a description for the document..." class="text-xs" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" size="sm" class="h-8 text-xs"
                            @click="uploadModal.open = false">
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" class="h-8 text-xs" :disabled="uploadForm.processing">
                            <Loader2 v-if="uploadForm.processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                            <Upload v-else class="mr-1.5 h-3.5 w-3.5" />
                            Upload
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Fulfill Modal -->
        <Dialog v-model:open="fulfillModal.open">
            <DialogContent v-if="fulfillModal.open" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-sm">Fulfill Document Request</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitFulfill" class="space-y-4">
                    <div class="flex items-center gap-2">
                        <input id="upload_document" v-model="fulfillForm.upload_document" type="checkbox"
                            class="h-4 w-4" />
                        <Label for="upload_document" class="text-xs cursor-pointer">
                            Upload document while fulfilling
                        </Label>
                    </div>
                    <div v-if="fulfillForm.upload_document" class="space-y-3 border-t pt-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Document Type</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline"
                                        class="w-full justify-between h-9 text-xs px-3 font-normal">
                                        {{
                                            documentTypes[fulfillForm.document_type] ||
                                            'Select a document type'
                                        }}
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    class="w-[--radix-dropdown-menu-trigger-width] min-w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuItem v-for="(label, key) in documentTypes" :key="key"
                                        @click="fulfillForm.document_type = key">
                                        {{ label }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">
                                Upload Documents <span class="text-destructive">*</span>
                            </Label>
                            <Input type="file" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                @change="(e) => fulfillForm.documents = Array.from(e.target.files)" class="h-9 text-xs"
                                :required="fulfillForm.upload_document" />
                            <p class="text-xs text-muted-foreground">
                                Max 10MB per file. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Description (Optional)</Label>
                            <Textarea v-model="fulfillForm.description" rows="3"
                                placeholder="Add a description for the document..." class="text-xs" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" size="sm" class="h-8 text-xs"
                            @click="fulfillModal.open = false">
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" class="h-8 text-xs" :disabled="fulfillForm.processing">
                            <Loader2 v-if="fulfillForm.processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                            <Check v-else class="mr-1.5 h-3.5 w-3.5" />
                            Mark as Fulfilled
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
