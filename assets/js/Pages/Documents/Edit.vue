<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Save, Loader2, FileText, HardDrive } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    document: { type: Object, required: true },
    documentTypes: { type: Object, required: true },
});

const form = useForm({
    document_type: props.document.document_type || '',
    description: props.document.description || '',
});

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    form.put(route('leads.documents.update', [props.lead.id, props.document.id]));
};
</script>

<template>
    <Head :title="`Edit Document - ${document.original_name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('leads.documents.index', lead.id)">
                <Button variant="ghost" size="icon" class="h-8 w-8">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Document</h1>
                <p class="text-xs text-muted-foreground">{{ lead.full_name }}</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Document Info (Read-only) -->
                <Card>
                    <CardHeader class="p-4 pb-2">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <FileText class="h-4 w-4" />
                            Document Info
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 pt-2 space-y-3">
                        <div>
                            <p class="text-[10px] text-muted-foreground">File Name</p>
                            <p class="text-xs font-medium">{{ document.original_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-muted-foreground">File Type</p>
                            <p class="text-xs">{{ document.file_type }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <HardDrive class="h-3 w-3 text-muted-foreground" />
                            <span class="text-xs">{{ formatFileSize(document.file_size) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Edit Form -->
                <Card>
                    <CardHeader class="p-4 pb-2">
                        <CardTitle class="text-sm">Edit Details</CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 pt-2 space-y-4">
                        <!-- Document Type -->
                        <div class="space-y-2">
                            <Label class="text-xs">Document Type *</Label>
                            <Select v-model="form.document_type">
                                <SelectTrigger class="h-8 text-xs">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in documentTypes" :key="value" :value="value" class="text-xs">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.document_type" class="text-xs text-destructive">{{ form.errors.document_type }}</p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label class="text-xs">Description</Label>
                            <Textarea
                                v-model="form.description"
                                placeholder="Optional description..."
                                class="text-xs min-h-[80px]"
                            />
                            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-2">
                            <Button type="submit" size="sm" class="h-8 text-xs" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="h-3.5 w-3.5 mr-1 animate-spin" />
                                <Save v-else class="h-3.5 w-3.5 mr-1" />
                                Save Changes
                            </Button>
                            <Link :href="route('leads.documents.index', lead.id)">
                                <Button type="button" variant="outline" size="sm" class="h-8 text-xs">Cancel</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>
