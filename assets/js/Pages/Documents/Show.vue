<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, FileText, Download, Eye, Pencil, Calendar, User, HardDrive, Type, FileType } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    document: { type: Object, required: true },
});

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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

const canPreview = isImage(props.document.file_type) || isPdf(props.document.file_type);
</script>

<template>
    <Head :title="`Document - ${document.original_name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="route('leads.documents.index', lead.id)">
                    <Button variant="ghost" size="icon" class="h-8 w-8">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight truncate max-w-md">{{ document.original_name }}</h1>
                    <p class="text-xs text-muted-foreground">{{ lead.full_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link v-if="canPreview" :href="route('leads.documents.preview', [lead.id, document.id])" target="_blank">
                    <Button variant="outline" size="sm" class="h-8 text-xs">
                        <Eye class="h-3.5 w-3.5 mr-1" />
                        Preview
                    </Button>
                </Link>
                <a :href="route('leads.documents.download', [lead.id, document.id])">
                    <Button variant="outline" size="sm" class="h-8 text-xs">
                        <Download class="h-3.5 w-3.5 mr-1" />
                        Download
                    </Button>
                </a>
                <Link :href="route('leads.documents.edit', [lead.id, document.id])">
                    <Button size="sm" class="h-8 text-xs">
                        <Pencil class="h-3.5 w-3.5 mr-1" />
                        Edit
                    </Button>
                </Link>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <!-- Document Info -->
            <Card>
                <CardHeader class="p-4 pb-2">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <FileText class="h-4 w-4" />
                        Document Details
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 pt-2 space-y-3">
                    <div class="flex items-center gap-2">
                        <Type class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-[10px] text-muted-foreground">Document Type</p>
                            <Badge variant="secondary" class="text-[9px]">{{ getDocumentTypeLabel(document.document_type) }}</Badge>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <FileType class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-[10px] text-muted-foreground">File Type</p>
                            <p class="text-xs">{{ document.file_type }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <HardDrive class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-[10px] text-muted-foreground">File Size</p>
                            <p class="text-xs">{{ formatFileSize(document.file_size) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-[10px] text-muted-foreground">Uploaded</p>
                            <p class="text-xs">{{ formatDate(document.created_at) }}</p>
                        </div>
                    </div>
                    <div v-if="document.uploader" class="flex items-center gap-2">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-[10px] text-muted-foreground">Uploaded By</p>
                            <p class="text-xs">{{ document.uploader.name }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Description -->
            <Card>
                <CardHeader class="p-4 pb-2">
                    <CardTitle class="text-sm">Description</CardTitle>
                </CardHeader>
                <CardContent class="p-4 pt-2">
                    <p v-if="document.description" class="text-xs text-muted-foreground whitespace-pre-wrap">{{ document.description }}</p>
                    <p v-else class="text-xs text-muted-foreground italic">No description provided.</p>
                </CardContent>
            </Card>
        </div>

        <!-- Preview (for images) -->
        <Card v-if="isImage(document.file_type)">
            <CardHeader class="p-4 pb-2">
                <CardTitle class="text-sm">Preview</CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-2">
                <img
                    :src="route('leads.documents.preview', [lead.id, document.id])"
                    :alt="document.original_name"
                    class="max-w-full max-h-[400px] rounded-lg mx-auto"
                />
            </CardContent>
        </Card>
    </div>
</template>
