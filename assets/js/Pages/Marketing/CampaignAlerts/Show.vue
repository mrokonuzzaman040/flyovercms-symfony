<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Bell, CheckCircle2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    alert: { type: Object, required: true },
});

const acknowledge = () => {
    router.post(route('marketing.campaign-alerts.acknowledge', props.alert.id));
};

const reset = () => {
    router.post(route('marketing.campaign-alerts.reset', props.alert.id));
};
</script>

<template>
    <Head :title="`Alert: ${alert.name}`" />

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.campaign-alerts.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ alert.name }}</h1>
                    <p v-if="alert.description" class="text-xs text-muted-foreground">{{ alert.description }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button v-if="alert.is_triggered && !alert.acknowledged_at" variant="outline" size="sm" @click="acknowledge">
                    <CheckCircle2 class="h-3.5 w-3.5 mr-1.5" />
                    Acknowledge
                </Button>
                <Button v-if="alert.is_triggered" variant="outline" size="sm" @click="reset">Reset</Button>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.campaign-alerts.edit', alert.id)">
                        <Pencil class="h-3.5 w-3.5 mr-1.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Type</div>
                    <Badge variant="outline" class="mt-1 capitalize">{{ alert.alert_type }}</Badge>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Metric</div>
                    <div class="text-sm font-medium mt-1">{{ alert.metric }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Threshold</div>
                    <div class="text-sm font-medium mt-1">{{ alert.operator }} {{ alert.threshold_value }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Status</div>
                    <div class="flex items-center gap-1 mt-1">
                        <Badge :variant="alert.is_triggered ? 'destructive' : 'secondary'">
                            {{ alert.is_triggered ? 'Triggered' : 'Active' }}
                        </Badge>
                        <CheckCircle2 v-if="alert.acknowledged_at" class="h-4 w-4 text-green-600" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <Bell class="h-4 w-4" />
                    Alert Details
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4 space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-muted-foreground">Campaign:</span>
                    <span>{{ alert.campaign?.name || 'All Campaigns' }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-muted-foreground">Severity:</span>
                    <Badge :variant="alert.severity === 'critical' || alert.severity === 'high' ? 'destructive' : 'default'">
                        {{ alert.severity }}
                    </Badge>
                </div>
                <div v-if="alert.triggered_at" class="flex justify-between text-xs">
                    <span class="text-muted-foreground">Triggered At:</span>
                    <span>{{ new Date(alert.triggered_at).toLocaleString() }}</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
