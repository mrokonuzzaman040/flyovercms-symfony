<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Copy, FileText, Star, Users, History } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    template: { type: Object, required: true },
});

const TYPE_I_C_O_N_S = Object.freeze({
        sms: '📱',
        email: '📧',
        whatsapp: '💬',
    });
const getTypeIcon = (type) => TYPE_I_C_O_N_S[type] || '📢';

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Template: ${template.name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.templates.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ template.name }}</h1>
                    <p v-if="template.description" class="text-xs text-muted-foreground">{{ template.description }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" class="gap-1.5" @click="router.post(route('marketing.templates.duplicate', template.id))">
                    <Copy class="h-3.5 w-3.5" />
                    Duplicate
                </Button>
                <Button variant="outline" size="sm" as-child class="gap-1.5">
                    <Link :href="route('marketing.templates.edit', template.id)">
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Template Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Type</p>
                            <div class="flex items-center gap-1.5">
                                <span class="text-base">{{ getTypeIcon(template.type) }}</span>
                                <span class="text-xs font-medium uppercase">{{ template.type }}</span>
                            </div>
                        </div>
                        <FileText class="h-8 w-8 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Usage</p>
                            <p class="text-lg font-semibold">{{ template.usage_count }}</p>
                        </div>
                        <Users class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Success Rate</p>
                            <p class="text-lg font-semibold">{{ template.success_rate ? `${template.success_rate}%` : 'N/A' }}</p>
                        </div>
                        <Star class="h-8 w-8 text-amber-600 dark:text-amber-400" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Status</p>
                            <Badge :variant="template.is_active ? 'default' : 'secondary'" class="text-xs">
                                {{ template.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                            <FileText class="h-4 w-4 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Template Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Message Preview -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                        Message Preview
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-3">
                    <div v-if="template.subject" class="space-y-1">
                        <Label class="text-xs text-muted-foreground">Subject:</Label>
                        <p class="text-xs font-medium">{{ template.subject }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label class="text-xs text-muted-foreground">Message:</Label>
                        <div class="border rounded-md p-3 bg-muted/30">
                            <pre class="text-xs whitespace-pre-wrap font-mono">{{ template.message }}</pre>
                        </div>
                    </div>
                    <div v-if="template.html_content" class="space-y-1">
                        <Label class="text-xs text-muted-foreground">HTML Content:</Label>
                        <div class="border rounded-md p-3 bg-muted/30 max-h-64 overflow-auto">
                            <pre class="text-xs whitespace-pre-wrap font-mono">{{ template.html_content }}</pre>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Template Metadata -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-green-500 to-green-600 rounded" />
                        Template Details
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-3">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Category:</span>
                            <Badge variant="outline" class="text-xs">{{ template.category || 'None' }}</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Public:</span>
                            <Badge :variant="template.is_public ? 'default' : 'secondary'" class="text-xs">
                                {{ template.is_public ? 'Yes' : 'No' }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Version:</span>
                            <span class="text-xs font-medium">{{ template.version }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Created:</span>
                            <span class="text-xs">{{ formatDate(template.created_at) }}</span>
                        </div>
                        <div v-if="template.creator" class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Created by:</span>
                            <span class="text-xs">{{ template.creator.name }}</span>
                        </div>
                    </div>

                    <!-- Variables -->
                    <div v-if="template.variables && template.variables.length > 0" class="pt-3 border-t space-y-2">
                        <Label class="text-xs text-muted-foreground">Available Variables:</Label>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="variable in template.variables" :key="variable" variant="outline" class="text-[10px] font-mono">
                                {{ variable }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Versions -->
                    <div v-if="template.versions && template.versions.length > 0" class="pt-3 border-t space-y-2">
                        <div class="flex items-center gap-2">
                            <History class="h-3.5 w-3.5 text-muted-foreground" />
                            <Label class="text-xs text-muted-foreground">Versions ({{ template.versions.length }})</Label>
                        </div>
                        <div class="space-y-1">
                            <div
                                v-for="version in template.versions"
                                :key="version.id"
                                class="flex items-center justify-between p-2 border rounded text-xs"
                            >
                                <span>Version {{ version.version }}</span>
                                <Link :href="route('marketing.templates.show', version.id)" class="text-primary hover:underline">
                                    View
                                </Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
