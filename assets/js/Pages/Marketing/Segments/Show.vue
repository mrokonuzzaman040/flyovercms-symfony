<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, RefreshCw, Users, Calendar, User } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    segment: { type: Object, required: true },
    leads: { type: Object, required: true },
});

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
    <Head :title="`Segment: ${segment.name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.segments.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ segment.name }}</h1>
                    <p v-if="segment.description" class="text-xs text-muted-foreground">{{ segment.description }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button
                    v-if="segment.is_dynamic"
                    variant="outline"
                    size="sm"
                    class="gap-1.5"
                    @click="router.post(route('marketing.segments.recalculate', segment.id))"
                >
                    <RefreshCw class="h-3.5 w-3.5" />
                    Recalculate
                </Button>
                <Button variant="outline" size="sm" as-child class="gap-1.5">
                    <Link :href="route('marketing.segments.edit', segment.id)">
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Segment Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Type</p>
                            <Badge :variant="segment.is_dynamic ? 'default' : 'secondary'" class="text-xs">
                                {{ segment.is_dynamic ? 'Dynamic' : 'Static' }}
                            </Badge>
                        </div>
                        <Users class="h-8 w-8 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Total Leads</p>
                            <p class="text-lg font-semibold">{{ segment.lead_count }}</p>
                        </div>
                        <Users class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Last Calculated</p>
                            <p class="text-xs font-medium">
                                {{ segment.last_calculated_at ? formatDate(segment.last_calculated_at) : 'Never' }}
                            </p>
                        </div>
                        <Calendar class="h-8 w-8 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Criteria Summary -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                    Segment Criteria
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <div class="space-y-2">
                    <pre class="text-xs bg-muted p-3 rounded-md overflow-auto">{{ JSON.stringify(segment.criteria, null, 2) }}</pre>
                </div>
            </CardContent>
        </Card>

        <!-- Leads List -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-green-500 to-green-600 rounded" />
                    Matching Leads ({{ leads.total }})
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Lead</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Contact</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Service</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center">
                                            <User class="h-3 w-3 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium">{{ lead.full_name }}</p>
                                            <p v-if="lead.email" class="text-[10px] text-muted-foreground">{{ lead.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="text-xs">{{ lead.phone || 'N/A' }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-[10px]">
                                        {{ lead.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="text-xs text-muted-foreground">{{ lead.service_type || 'N/A' }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="text-xs text-muted-foreground">{{ formatDate(lead.created_at) }}</p>
                                </td>
                            </tr>
                            <tr v-if="leads.data.length === 0">
                                <td colspan="5" class="px-3 py-8 text-center">
                                    <p class="text-xs text-muted-foreground">No leads match this segment.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="leads.links && leads.links.length > 3" class="border-t p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ leads.from }} to {{ leads.to }} of {{ leads.total }} leads
                        </p>
                        <div class="flex items-center gap-1">
                            <Button
                                v-for="link in leads.links"
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
    </div>
</template>
