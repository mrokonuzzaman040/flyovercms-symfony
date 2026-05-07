<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Zap, Send, CheckCircle, DollarSign } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
});

const getTriggerLabel = (type) => {
    const labels = {
        birthday: 'Birthday Wish',
        passport_expiry: 'Passport Expiry Alert',
        visa_status_change: 'Visa Status Change',
        custom: 'Custom',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head :title="campaign.name" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.automated-campaigns.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ campaign.name }}</h1>
                    <p class="text-xs text-muted-foreground">{{ campaign.description || 'No description' }}</p>
                </div>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link :href="route('marketing.automated-campaigns.edit', campaign.id)">
                    <Pencil class="h-3.5 w-3.5" />
                </Link>
            </Button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Total Sent</p>
                            <p class="text-lg font-semibold">{{ campaign.total_sent || 0 }}</p>
                        </div>
                        <Send class="h-8 w-8 text-blue-500" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Delivered</p>
                            <p class="text-lg font-semibold">{{ campaign.total_delivered || 0 }}</p>
                        </div>
                        <CheckCircle class="h-8 w-8 text-green-500" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Total Cost</p>
                            <p class="text-lg font-semibold">৳{{ Number(campaign.total_cost || 0).toFixed(2) }}</p>
                        </div>
                        <DollarSign class="h-8 w-8 text-amber-500" aria-hidden="true" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Campaign Details</CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-3">
                    <div>
                        <p class="text-xs text-muted-foreground">Status</p>
                        <Badge :variant="campaign.is_active ? 'default' : 'secondary'" class="mt-1">
                            {{ campaign.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Trigger Type</p>
                        <p class="text-sm font-medium mt-1">{{ getTriggerLabel(campaign.trigger_type) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Channel</p>
                        <p class="text-sm font-medium mt-1 uppercase">{{ campaign.channel }}</p>
                    </div>
                    <div v-if="campaign.last_triggered_at">
                        <p class="text-xs text-muted-foreground">Last Triggered</p>
                        <p class="text-sm font-medium mt-1">{{ new Date(campaign.last_triggered_at).toLocaleString() }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Message Template</CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="bg-muted p-3 rounded-lg">
                        <p class="text-xs whitespace-pre-wrap">{{ campaign.message_template }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
