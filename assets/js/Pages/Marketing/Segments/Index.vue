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
import { Plus, Eye, Pencil, Trash2, Users, Filter, Search, ChevronDown, RefreshCw } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    segments: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const showDeleteDialog = ref(false);
const segmentToDelete = ref(null);

const confirmDelete = (segment) => {
    segmentToDelete.value = segment;
    showDeleteDialog.value = true;
};

const deleteSegment = () => {
    if (segmentToDelete.value) {
        router.delete(route('marketing.segments.destroy', segmentToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                segmentToDelete.value = null;
            },
        });
    }
};

const applyFilter = (key, value) => {
    router.get(route('marketing.segments.index'), { ...props.filters, [key]: value }, { preserveState: true });
};

const search = ref(props.filters.search || '');

const performSearch = () => {
    router.get(route('marketing.segments.index'), { ...props.filters, search: search.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Marketing Segments" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Marketing Segments</h1>
                <p class="text-xs text-muted-foreground">Create and manage lead segments for targeted campaigns</p>
            </div>
            <Button as-child size="sm" class="gap-1.5">
                <Link :href="route('marketing.segments.create')">
                    <Plus class="h-3.5 w-3.5" />
                    New Segment
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
                            placeholder="Search segments..."
                            class="h-8 text-xs pl-8"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.is_dynamic === null ? 'All Types' : filters.is_dynamic ? 'Dynamic' : 'Static' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.is_dynamic === null ? 'all' : filters.is_dynamic ? 'dynamic' : 'static'" @update:model-value="(v) => applyFilter('is_dynamic', v === 'all' ? null : v === 'dynamic')">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Types</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="dynamic" class="text-xs">Dynamic</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="static" class="text-xs">Static</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardContent>
        </Card>

        <!-- Segments Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                    All Segments
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Name</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Type</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Leads</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Last Calculated</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="segment in segments.data" :key="segment.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div>
                                        <p class="text-xs font-medium">{{ segment.name }}</p>
                                        <p v-if="segment.description" class="text-[10px] text-muted-foreground line-clamp-1">{{ segment.description }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="segment.is_dynamic ? 'default' : 'secondary'" class="text-[10px]">
                                        {{ segment.is_dynamic ? 'Dynamic' : 'Static' }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1.5">
                                        <Users class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span class="text-xs font-medium">{{ segment.lead_count }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ segment.last_calculated_at ? new Date(segment.last_calculated_at).toLocaleDateString() : 'Never' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.segments.show', segment.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.segments.edit', segment.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="segment.is_dynamic"
                                            variant="ghost"
                                            size="icon"
                                            class="h-7 w-7"
                                            @click="router.post(route('marketing.segments.recalculate', segment.id))"
                                        >
                                            <RefreshCw class="h-3.5 w-3.5 text-green-600" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(segment)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="segments.data.length === 0">
                                <td colspan="5" class="px-3 py-8 text-center">
                                    <p class="text-xs text-muted-foreground">No segments found. Create your first segment to get started.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="segments.links && segments.links.length > 3" class="border-t p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ segments.from }} to {{ segments.to }} of {{ segments.total }} segments
                        </p>
                        <div class="flex items-center gap-1">
                            <Button
                                v-for="link in segments.links"
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
                </div>
            </CardContent>
        </Card>

        <DeleteConfirmation
            :show="showDeleteDialog"
            :title="'Delete Segment'"
            :message="`Are you sure you want to delete '${segmentToDelete?.name}'? This action cannot be undone.`"
            @confirm="deleteSegment"
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>
