<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';
import { FileText, Plus, Eye, Download, Pencil, Trash2, ArrowLeft, Calendar, User, HardDrive } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    documents: { type: Object, required: true },
});

const page = usePage();
const { hasPermission } = usePermissions();

const showDeleteDialog = ref(false);
const documentToDelete = ref(null);

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getDocumentTypeLabel = (type) => {
    const types = {
        passport: 'Passport',
        cv: 'CV/Resume',
        academic_transcript: 'Academic Transcript',
        degree_certificate: 'Degree Certificate',
        bank_statement: 'Bank Statement',
        work_experience_letter: 'Work Experience Letter',
        medical_report: 'Medical Report',
        police_clearance: 'Police Clearance',
        language_test_certificate: 'Language Test Certificate',
        photo: 'Photo',
        other: 'Other',
    };
    return types[type] || type;
};

const isImage = (fileType) => {
    return fileType?.startsWith('image/');
};

const isPdf = (fileType) => {
    return fileType === 'application/pdf';
};

const confirmDelete = (document) => {
    documentToDelete.value = document;
    showDeleteDialog.value = true;
};

const deleteDocument = () => {
    if (documentToDelete.value) {
        router.delete(route('leads.documents.destroy', [props.lead.id, documentToDelete.value.id]), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                documentToDelete.value = null;
            },
        });
    }
};
</script>

<template>

    <Head :title="`Documents - ${lead.full_name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button as-child variant="ghost" size="icon" class="h-8 w-8">
                    <Link :href="route('leads.show', lead.id)">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">Documents</h1>
                    <p class="text-xs text-muted-foreground">{{ lead.full_name }}</p>
                </div>
            </div>
            <Button v-if="hasPermission('upload-documents')" as-child size="sm" class="h-8 text-xs">
                <Link :href="route('leads.documents.create', lead.id)">
                    <Plus class="h-3.5 w-3.5 mr-1" />
                    Upload Document
                </Link>
            </Button>
        </div>

        <!-- Documents List -->
        <div v-if="documents.data?.length" class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="document in documents.data" :key="document.id">
                <CardContent class="p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                            <FileText class="h-5 w-5 text-primary" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs font-medium truncate">{{ document.original_name }}</h3>
                            <Badge variant="secondary" class="text-[9px] mt-1">
                                {{ getDocumentTypeLabel(document.document_type) }}
                            </Badge>
                            <div class="flex items-center gap-2 mt-2 text-[10px] text-muted-foreground">
                                <span class="flex items-center gap-1">
                                    <HardDrive class="h-3 w-3" />
                                    {{ formatFileSize(document.file_size) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Calendar class="h-3 w-3" />
                                    {{ formatDate(document.created_at) }}
                                </span>
                            </div>
                            <div v-if="document.uploader"
                                class="flex items-center gap-1 mt-1 text-[10px] text-muted-foreground">
                                <User class="h-3 w-3" />
                                {{ document.uploader.name }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 mt-3 pt-3 border-t">
                        <Button v-if="isImage(document.file_type) || isPdf(document.file_type)" as-child variant="ghost"
                            size="icon" class="h-7 w-7">
                            <Link :href="route('leads.documents.preview', [lead.id, document.id])" target="_blank">
                                <Eye class="h-3.5 w-3.5" />
                            </Link>
                        </Button>
                        <Button as-child variant="ghost" size="icon" class="h-7 w-7">
                            <a :href="route('leads.documents.download', [lead.id, document.id])">
                                <Download class="h-3.5 w-3.5" />
                            </a>
                        </Button>
                        <Button v-if="hasPermission('edit-documents')" as-child variant="ghost" size="icon"
                            class="h-7 w-7">
                            <Link :href="route('leads.documents.edit', [lead.id, document.id])">
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
                        </Button>
                        <Button v-if="hasPermission('delete-documents')" variant="ghost" size="icon"
                            class="h-7 w-7 text-destructive hover:text-destructive" @click="confirmDelete(document)">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="p-12 text-center">
                <div class="w-14 h-14 bg-muted rounded-full flex items-center justify-center mx-auto mb-3">
                    <FileText class="h-7 w-7 text-muted-foreground" />
                </div>
                <p class="text-sm font-medium">No Documents</p>
                <p class="text-xs text-muted-foreground mb-4">Upload documents for this lead.</p>
                <Button v-if="hasPermission('upload-documents')" as-child size="sm" class="h-8 text-xs">
                    <Link :href="route('leads.documents.create', lead.id)">
                        <Plus class="h-3.5 w-3.5 mr-1" />
                        Upload Document
                    </Link>
                </Button>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div v-if="documents.links?.length > 3" class="flex justify-center gap-1">
            <template v-for="link in documents.links" :key="link.label">
                <Button v-if="link.url" as-child variant="outline" size="sm"
                    :class="{ 'bg-primary text-primary-foreground': link.active }" class="h-7 text-xs">
                    <Link :href="link.url" preserve-scroll v-html="link.label" />
                </Button>
                <Button v-else variant="outline" size="sm" disabled class="h-7 text-xs" v-html="link.label" />
            </template>
        </div>

        <DeleteConfirmation :open="showDeleteDialog" title="Delete Document"
            description="Are you sure you want to delete this document? This action cannot be undone."
            @update:open="showDeleteDialog = $event" @confirm="deleteDocument" />
    </div>
</template>
