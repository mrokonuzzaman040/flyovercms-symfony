<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Plus, Eye, Pencil, Trash2, Copy, FileText, Filter, Search, ChevronDown, Star } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    templates: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const showDeleteDialog = ref(false);
const templateToDelete = ref(null);

const confirmDelete = (template) => {
    templateToDelete.value = template;
    showDeleteDialog.value = true;
};

const deleteTemplate = () => {
    if (templateToDelete.value) {
        router.delete(route('marketing.templates.destroy', templateToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                templateToDelete.value = null;
            },
        });
    }
};

const applyFilter = (key, value) => {
    router.get(route('marketing.templates.index'), { ...props.filters, [key]: value }, { preserveState: true });
};

const search = ref(props.filters.search || '');

const performSearch = () => {
    router.get(route('marketing.templates.index'), { ...props.filters, search: search.value }, { preserveState: true });
};

const TYPE_I_C_O_N_S = Object.freeze({
        sms: '📱',
        email: '📧',
        whatsapp: '💬',
    });
const getTypeIcon = (type) => TYPE_I_C_O_N_S[type] || '📢';
</script>

<template>
    <Head title="Marketing Templates" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Marketing Templates</h1>
                <p class="text-xs text-muted-foreground">Manage message templates for campaigns</p>
            </div>
            <Button as-child size="sm" class="gap-1.5">
                <Link :href="route('marketing.templates.create')">
                    <Plus class="h-3.5 w-3.5" />
                    New Template
                </Link>
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex items-center gap-3">
                    <div class="flex-1 relative">
                        <Search class="absolute left-2 top-1/2 transform -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search templates..."
                            class="h-8 text-xs pl-8"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.type || 'All Types' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.type || 'all'" @update:model-value="(v) => applyFilter('type', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Types</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="email" class="text-xs">Email</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardContent>
        </Card>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <Card
                v-for="template in templates.data"
                :key="template.id"
                class="hover:shadow-md transition-shadow"
            >
                <CardHeader class="border-b p-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <CardTitle class="text-sm flex items-center gap-2">
                                <span class="text-base">{{ getTypeIcon(template.type) }}</span>
                                {{ template.name }}
                            </CardTitle>
                            <p v-if="template.category" class="text-[10px] text-muted-foreground mt-1">{{ template.category }}</p>
                        </div>
                        <Badge :variant="template.is_active ? 'default' : 'secondary'" class="text-[10px]">
                            {{ template.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="p-3 space-y-2">
                    <p v-if="template.description" class="text-xs text-muted-foreground line-clamp-2">{{ template.description }}</p>
                    <div class="flex items-center gap-4 text-[10px] text-muted-foreground">
                        <div class="flex items-center gap-1">
                            <FileText class="h-3 w-3" />
                            <span>{{ template.usage_count }} uses</span>
                        </div>
                        <div v-if="template.success_rate" class="flex items-center gap-1">
                            <Star class="h-3 w-3" />
                            <span>{{ template.success_rate }}%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 pt-2 border-t">
                        <Button variant="ghost" size="sm" class="h-7 text-xs flex-1" as-child>
                            <Link :href="route('marketing.templates.show', template.id)">
                                <Eye class="h-3 w-3 mr-1" />
                                View
                            </Link>
                        </Button>
                        <Button variant="ghost" size="sm" class="h-7 text-xs flex-1" as-child>
                            <Link :href="route('marketing.templates.edit', template.id)">
                                <Pencil class="h-3 w-3 mr-1" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-7 w-7 p-0"
                            @click="router.post(route('marketing.templates.duplicate', template.id))"
                        >
                            <Copy class="h-3 w-3" />
                        </Button>
                        <Button variant="ghost" size="sm" class="h-7 w-7 p-0" @click="confirmDelete(template)">
                            <Trash2 class="h-3 w-3 text-red-600" />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div v-if="templates.data.length === 0" class="col-span-full">
                <Card>
                    <CardContent class="p-8 text-center">
                        <FileText class="h-12 w-12 text-muted-foreground mx-auto mb-3" />
                        <p class="text-xs text-muted-foreground">No templates found. Create your first template to get started.</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="templates.links && templates.links.length > 3" class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">
                Showing {{ templates.from }} to {{ templates.to }} of {{ templates.total }} templates
            </p>
            <div class="flex items-center gap-1">
                <Button
                    v-for="link in templates.links"
                    :key="link.label"
                    variant="outline"
                    size="sm"
                    class="h-7 text-xs"
                    :disabled="!link.url"
                    v-html="link.label"
                    @click="link.url && router.visit(link.url)"
                />
            </div>
        </div>

        <DeleteConfirmation
            :show="showDeleteDialog"
            :title="'Delete Template'"
            :message="`Are you sure you want to delete '${templateToDelete?.name}'? This action cannot be undone.`"
            @confirm="deleteTemplate"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>
