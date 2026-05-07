<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Check, Cog } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    package: { type: Object, required: true },
});

const page = usePage();
const { hasPermission } = usePermissions();

const formatPrice = (price, currency) => {
    if (!price) return 'Not set';
    return `${currency} ${Number(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head :title="package.name" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link 
                    :href="route('packages.index')" 
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ package.name }}</h1>
                    <p class="text-xs text-muted-foreground">Package Details</p>
                </div>
            </div>
            <Button v-if="hasPermission('edit-packages')" size="sm" variant="outline" class="gap-1.5" as-child>
                <Link :href="route('packages.edit', package.id)">
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
                        <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                        Package Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Description -->
                    <div>
                        <label class="text-xs font-medium text-muted-foreground">Description</label>
                        <p class="text-xs mt-1">{{ package.description || 'No description provided' }}</p>
                    </div>

                    <!-- Features -->
                    <div v-if="package.features?.length">
                        <label class="text-xs font-medium text-muted-foreground">Features</label>
                        <ul class="mt-1 space-y-1">
                            <li v-for="(feature, index) in package.features" :key="index" class="flex items-center gap-2 py-1 border-b border-border/50 last:border-0">
                                <Check class="h-3 w-3 text-green-500 shrink-0" />
                                <span class="text-xs">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Included Services -->
                    <div v-if="package.services?.length">
                        <label class="text-xs font-medium text-muted-foreground">Included Services</label>
                        <div class="mt-2 space-y-2">
                            <div v-for="service in package.services" :key="service.id" class="flex items-center gap-2 p-2 border rounded-lg">
                                <div class="w-7 h-7 bg-gradient-to-br from-red-500 to-red-600 rounded flex items-center justify-center text-white shrink-0">
                                    <Cog class="h-3.5 w-3.5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium">{{ service.name }}</p>
                                    <p class="text-[10px] text-muted-foreground">
                                        {{ service.pivot?.quantity || 1 }}x @ 
                                        {{ formatPrice(service.pivot?.price || service.price, service.currency) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Details Card -->
                <Card>
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                            Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 space-y-3">
                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Total Price</label>
                            <p class="text-sm font-semibold text-purple-600 dark:text-purple-400 mt-1">{{ formatPrice(package.total_price, package.currency) }}</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Duration</label>
                            <p class="text-xs mt-1">
                                {{ package.duration_days ? `${package.duration_days} night${package.duration_days > 1 ? 's' : ''}` : 'Not set' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Services Included</label>
                            <p class="text-xs mt-1">{{ package.services?.length || 0 }} services</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Status</label>
                            <div class="mt-1">
                                <Badge :variant="package.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                    {{ package.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
