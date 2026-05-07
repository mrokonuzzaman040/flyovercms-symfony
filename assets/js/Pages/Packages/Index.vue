<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Plus, Eye, Pencil, Trash2, Box, AlertCircle } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    packages: { type: Object, required: true },
});

const page = usePage();
const { hasPermission } = usePermissions();

const showDeleteDialog = ref(false);
const packageToDelete = ref(null);

const confirmDelete = (pkg) => {
    packageToDelete.value = pkg;
    showDeleteDialog.value = true;
};

const deletePackage = () => {
    if (packageToDelete.value) {
        router.delete(route('packages.destroy', packageToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                packageToDelete.value = null;
            },
        });
    }
};

const formatPrice = (price, currency) => {
    if (!price) return 'Not set';
    return `${currency} ${Number(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head title="Packages" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Packages Management</h1>
                <p class="text-xs text-muted-foreground">Manage service packages and bundles</p>
            </div>
            <Button v-if="hasPermission('create-packages')" as-child size="sm" class="gap-1.5">
                <Link :href="route('packages.create')">
                    <Plus class="h-3.5 w-3.5" />
                    Add Package
                </Link>
            </Button>
        </div>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <AlertCircle class="h-3.5 w-3.5" />
            {{ $page.props.flash.success }}
        </div>

        <!-- Packages Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                    All Packages
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Package</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Services</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Price</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Duration</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="pkg in packages.data" :key="pkg.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center text-white shrink-0">
                                            <Box class="h-3.5 w-3.5" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium truncate">{{ pkg.name }}</div>
                                            <div class="text-[10px] text-muted-foreground truncate max-w-[200px]">{{ pkg.description?.substring(0, 50) }}{{ pkg.description?.length > 50 ? '...' : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="secondary" class="text-[10px]">
                                        {{ pkg.services?.length || 0 }} services
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400">{{ formatPrice(pkg.total_price, pkg.currency) }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ pkg.duration_days ? `${pkg.duration_days} night${pkg.duration_days > 1 ? 's' : ''}` : 'Not set' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="pkg.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                        {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('packages.show', pkg.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('edit-packages')" variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('packages.edit', pkg.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('delete-packages')" variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(pkg)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!packages.data?.length">
                                <td colspan="6" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <Box class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">No packages found</p>
                                            <p class="text-xs text-muted-foreground">Get started by creating your first package.</p>
                                        </div>
                                        <Button v-if="hasPermission('create-packages')" as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="route('packages.create')">
                                                <Plus class="h-3.5 w-3.5" />
                                                Add Package
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="packages.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in packages.links" :key="index">
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
        title="Delete Package"
        :description="`Are you sure you want to delete '${packageToDelete?.name}'? This action cannot be undone.`"
        @confirm="deletePackage"
    />
</template>
