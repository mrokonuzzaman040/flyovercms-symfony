<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Phone, Mail, MapPin, Users } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    vendor: { type: Object, required: true },
    leads: { type: Object, default: () => ({ data: [] }) },
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head :title="vendor.name" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                    <Link :href="route('vendors.index')">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ vendor.name }}</h1>
                    <p class="text-xs text-muted-foreground">Vendor Details</p>
                </div>
            </div>
            <Button size="sm" variant="outline" class="gap-1.5" as-child>
                <Link :href="route('vendors.edit', vendor.id)">
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
                        <div class="w-0.5 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded" />
                        Contact Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <Mail class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Email</label>
                                <p class="text-xs">{{ vendor.email || 'Not provided' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <Phone class="h-4 w-4 text-green-600" />
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Phone</label>
                                <p class="text-xs">{{ vendor.phone || 'Not provided' }}</p>
                                <p v-if="vendor.alternate_phone" class="text-[10px] text-muted-foreground">Alt: {{ vendor.alternate_phone }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 sm:col-span-2">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <MapPin class="h-4 w-4 text-purple-600" />
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Address</label>
                                <p class="text-xs">{{ vendor.address || 'Not provided' }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ [vendor.city, vendor.state, vendor.postal_code, vendor.country].filter(Boolean).join(', ') || '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="vendor.notes">
                        <label class="text-xs font-medium text-muted-foreground">Notes</label>
                        <p class="text-xs mt-1 whitespace-pre-wrap">{{ vendor.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Stats Card -->
                <Card>
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded" />
                            Statistics
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Users class="h-4 w-4 text-muted-foreground" />
                                <span class="text-xs">Total Leads</span>
                            </div>
                            <Badge variant="secondary" class="text-xs">{{ vendor.leads_count || 0 }}</Badge>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Status</label>
                            <div class="mt-1">
                                <Badge :variant="vendor.is_active ? 'default' : 'secondary'" class="text-[10px]">
                                    {{ vendor.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide">Created</label>
                            <p class="text-xs mt-1">{{ formatDate(vendor.created_at) }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Recent Leads -->
        <Card v-if="leads.data?.length">
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                    Recent Leads from this Vendor
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Name</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Contact</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Branch</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Assigned To</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <Link :href="route('leads.show', lead.id)" class="text-xs font-medium text-primary hover:underline">
                                        {{ lead.full_name }}
                                    </Link>
                                </td>
                                <td class="px-3 py-2 text-xs text-muted-foreground">{{ lead.phone || lead.email || '-' }}</td>
                                <td class="px-3 py-2 text-xs">{{ lead.branch?.name || '-' }}</td>
                                <td class="px-3 py-2 text-xs">{{ lead.assigned_user?.name || '-' }}</td>
                                <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(lead.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
