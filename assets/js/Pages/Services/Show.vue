<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Check, List, Box } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    service: { type: Object, required: true },
});

const page = usePage();
const { hasPermission } = usePermissions();

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
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="service.name" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link 
                    :href="route('services.index')" 
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ service.name }}</h1>
                    <p class="text-xs text-muted-foreground">Service Details</p>
                </div>
            </div>
            <Button v-if="hasPermission('edit-services')" size="sm" variant="outline" class="gap-1.5" as-child>
                <Link :href="route('services.edit', service.id)">
                    <Pencil class="h-3.5 w-3.5" />
                    Edit
                </Link>
            </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Main Information -->
            <Card class="lg:col-span-2">
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                        Service Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Description -->
                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Description</label>
                        <p class="text-xs mt-1">{{ service.description || 'No description provided' }}</p>
                    </div>

                    <!-- Features -->
                    <div v-if="service.features?.length">
                        <label class="text-xs font-medium text-muted-foreground">Features</label>
                        <ul class="mt-1 space-y-1">
                            <li v-for="(feature, index) in service.features" :key="index" class="flex items-center gap-2 py-1 border-b border-border/50 last:border-0">
                                <Check class="h-3 w-3 text-green-500 shrink-0" />
                                <span class="text-xs">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Requirements -->
                    <div v-if="service.requirements?.length">
                        <label class="text-xs font-medium text-muted-foreground">Requirements</label>
                        <ul class="mt-1 space-y-1">
                            <li v-for="(requirement, index) in service.requirements" :key="index" class="flex items-center gap-2 py-1 border-b border-border/50 last:border-0">
                                <List class="h-3 w-3 text-blue-500 shrink-0" />
                                <span class="text-xs">{{ requirement }}</span>
                            </li>
                        </ul>
                    </div>
                </CardContent>
            </Card>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Details Card -->
                <Card>
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                            Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 space-y-3">
                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Type</label>
                            <div class="mt-1">
                                <Badge :class="getTypeColor(service.type)" class="text-[10px] uppercase">
                                    {{ service.type?.replace('_', ' ') }}
                                </Badge>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Price</label>
                            <p class="text-sm font-semibold mt-1">{{ formatPrice(service.price, service.currency) }}</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Duration</label>
                            <p class="text-xs mt-1">
                                {{ service.duration_days ? `${service.duration_days} night${service.duration_days > 1 ? 's' : ''}` : 'Not set' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Status</label>
                            <div class="mt-1">
                                <Badge :variant="service.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                    {{ service.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Packages Card -->
                <Card v-if="service.packages?.length">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                            Used in Packages
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <div class="space-y-2">
                            <Link
                                v-for="pkg in service.packages"
                                :key="pkg.id"
                                :href="route('packages.show', pkg.id)"
                                class="flex items-center gap-2 p-2 rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <div class="w-6 h-6 bg-gradient-to-br from-purple-500 to-purple-600 rounded flex items-center justify-center text-white">
                                    <Box class="h-3 w-3" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium truncate">{{ pkg.name }}</p>
                                    <p class="text-[10px] text-muted-foreground">{{ pkg.currency }} {{ Number(pkg.total_price).toLocaleString() }}</p>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
