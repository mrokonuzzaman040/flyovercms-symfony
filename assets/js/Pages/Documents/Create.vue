<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Upload, X, FileText, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    documentTypes: { type: Object, required: true },
});

const form = useForm({
    documents: [],
    document_type: '',
    description: '',
});

const selectedFiles = ref([]);
const fileInput = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    selectedFiles.value = [...selectedFiles.value, ...files];
    form.documents = selectedFiles.value;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.documents = selectedFiles.value;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    form.post(route('leads.documents.store', props.lead.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Upload Documents - ${lead.full_name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('leads.documents.index', lead.id)">
                <Button variant="ghost" size="icon" class="h-8 w-8">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Upload Documents</h1>
                <p class="text-xs text-muted-foreground">{{ lead.full_name }}</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader class="p-4 pb-2">
                    <CardTitle class="text-sm">Document Upload</CardTitle>
                </CardHeader>
                <CardContent class="p-4 pt-2 space-y-4">
                    <!-- File Upload -->
                    <div class="space-y-2">
                        <Label class="text-xs">Files *</Label>
                        <div
                            class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary/50 transition-colors"
                            @click="fileInput?.click()"
                            @dragover.prevent
                            @drop.prevent="handleFileSelect({ target: { files: $event.dataTransfer.files } })"
                        >
                            <Upload class="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                            <p class="text-xs text-muted-foreground">
                                Click to upload or drag and drop<br />
                                PDF, DOC, DOCX, JPG, JPEG, PNG, GIF (max 10MB each)
                            </p>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                class="hidden"
                                @change="handleFileSelect"
                            />
                        </div>
                        <p v-if="form.errors.documents" class="text-xs text-destructive">{{ form.errors.documents }}</p>

                        <!-- Selected Files -->
                        <div v-if="selectedFiles.length" class="space-y-2 mt-3">
                            <div
                                v-for="(file, index) in selectedFiles"
                                :key="index"
                                class="flex items-center justify-between p-2 bg-muted rounded-lg"
                            >
                                <div class="flex items-center gap-2">
                                    <FileText class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs font-medium truncate max-w-[200px]">{{ file.name }}</p>
                                        <p class="text-[10px] text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                                    </div>
                                </div>
                                <Button variant="ghost" size="icon" class="h-6 w-6" @click="removeFile(index)">
                                    <X class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>

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
                        <Button type="submit" size="sm" class="h-8 text-xs" :disabled="form.processing || !selectedFiles.length">
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 mr-1 animate-spin" />
                            <Upload v-else class="h-3.5 w-3.5 mr-1" />
                            Upload {{ selectedFiles.length > 1 ? `${selectedFiles.length} Files` : 'File' }}
                        </Button>
                        <Link :href="route('leads.documents.index', lead.id)">
                            <Button type="button" variant="outline" size="sm" class="h-8 text-xs">Cancel</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
