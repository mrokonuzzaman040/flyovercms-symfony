<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Plus, Eye, Pencil, Trash2, Cog, AlertCircle } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    services: { type: Object, required: true },
});

const page = usePage();
const { hasPermission } = usePermissions();

const showDeleteDialog = ref(false);
const serviceToDelete = ref(null);

const confirmDelete = (service) => {
    serviceToDelete.value = service;
    showDeleteDialog.value = true;
};

const deleteService = () => {
    if (serviceToDelete.value) {
        router.delete(route('services.destroy', serviceToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                serviceToDelete.value = null;
            },
        });
    }
};

const formatPrice = (price, currency) => {
    if (!price) return 'Not set';
    return `${currency} ${Number(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const getTypeColor = (type) => {
    const colors = {
        visa: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        tour: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        education: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        document: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        consultation: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        group_package: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400',
        air_ticket: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400',
        land_package: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        documentation_others: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Services" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Services Management</h1>
                <p class="text-xs text-muted-foreground">Manage Visa and Tour services</p>
            </div>
            <Button v-if="hasPermission('create-services')" as-child size="sm" class="gap-1.5">
                <Link :href="route('services.create')">
                    <Plus class="h-3.5 w-3.5" />
                    Add Service
                </Link>
            </Button>
        </div>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <AlertCircle class="h-3.5 w-3.5" />
            {{ $page.props.flash.success }}
        </div>

        <!-- Services Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                    All Services
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Service</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Type</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Price</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Duration</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="service in services.data" :key="service.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center text-white shrink-0">
                                            <Cog class="h-3.5 w-3.5" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium truncate">{{ service.name }}</div>
                                            <div class="text-[10px] text-muted-foreground truncate max-w-[200px]">{{ service.description?.substring(0, 50) }}{{ service.description?.length > 50 ? '...' : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :class="getTypeColor(service.type)" class="text-[10px] uppercase">
                                        {{ service.type?.replace('_', ' ') }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ formatPrice(service.price, service.currency) }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ service.duration_days ? `${service.duration_days} night${service.duration_days > 1 ? 's' : ''}` : 'Not set' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="service.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                        {{ service.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('services.show', service.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('edit-services')" variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('services.edit', service.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('delete-services')" variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(service)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!services.data?.length">
                                <td colspan="6" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <Cog class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">No services found</p>
                                            <p class="text-xs text-muted-foreground">Get started by creating your first service.</p>
                                        </div>
                                        <Button v-if="hasPermission('create-services')" as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="route('services.create')">
                                                <Plus class="h-3.5 w-3.5" />
                                                Add Service
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="services.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in services.links" :key="index">
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
        title="Delete Service"
        :description="`Are you sure you want to delete '${serviceToDelete?.name}'? This action cannot be undone.`"
        @confirm="deleteService"
    />
</template>
