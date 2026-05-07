<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Plus, Eye, Pencil, Trash2, Store, AlertCircle, Search, Users } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    vendors: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();

const showDeleteDialog = ref(false);
const vendorToDelete = ref(null);
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.is_active || '');

const confirmDelete = (vendor) => {
    vendorToDelete.value = vendor;
    showDeleteDialog.value = true;
};

const deleteVendor = () => {
    if (vendorToDelete.value) {
        router.delete(route('vendors.destroy', vendorToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                vendorToDelete.value = null;
            },
        });
    }
};

const applyFilters = useDebounceFn(() => {
    router.get(route('vendors.index'), {
        search: search.value || undefined,
        is_active: statusFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch([search, statusFilter], () => {
    applyFilters();
});
</script>

<template>
    <Head title="Vendors" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Vendors Management</h1>
                <p class="text-xs text-muted-foreground">Manage vendors and their leads</p>
            </div>
            <Button as-child size="sm" class="gap-1.5">
                <Link :href="route('vendors.create')">
                    <Plus class="h-3.5 w-3.5" />
                    Add Vendor
                </Link>
            </Button>
        </div>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <AlertCircle class="h-3.5 w-3.5" />
            {{ $page.props.flash.success }}
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search vendors..." class="pl-8 h-8 text-xs" />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-full sm:w-32 h-8 text-xs">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="" class="text-xs">All Status</SelectItem>
                            <SelectItem value="1" class="text-xs">Active</SelectItem>
                            <SelectItem value="0" class="text-xs">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>
        </Card>

        <!-- Vendors Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded" />
                    All Vendors
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Vendor</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Contact</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Location</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Leads</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="vendor in vendors.data" :key="vendor.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white shrink-0">
                                            <Store class="h-3.5 w-3.5" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium truncate">{{ vendor.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs">{{ vendor.email || '-' }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ vendor.phone || '-' }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ [vendor.city, vendor.country].filter(Boolean).join(', ') || '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1">
                                        <Users class="h-3 w-3 text-muted-foreground" />
                                        <span class="text-xs">{{ vendor.leads_count || 0 }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="vendor.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                        {{ vendor.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('vendors.show', vendor.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('vendors.edit', vendor.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(vendor)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!vendors.data?.length">
                                <td colspan="6" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <Store class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">No vendors found</p>
                                            <p class="text-xs text-muted-foreground">Get started by creating your first vendor.</p>
                                        </div>
                                        <Button as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="route('vendors.create')">
                                                <Plus class="h-3.5 w-3.5" />
                                                Add Vendor
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="vendors.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in vendors.links" :key="index">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-7 text-xs"
                            as-child
                        >
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="h-7 text-xs"
                            disabled
                            v-html="link.label"
                        />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Delete Confirmation -->
    <DeleteConfirmation
        v-model:open="showDeleteDialog"
        title="Delete Vendor"
        :description="`Are you sure you want to delete '${vendorToDelete?.name}'? This action cannot be undone.`"
        @confirm="deleteVendor"
    />
</template>
