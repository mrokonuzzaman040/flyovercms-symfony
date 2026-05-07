<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, CheckCircle2, XCircle, AlertCircle, Settings } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    services: { type: Object, default: () => ({}) },
    analytics: { type: Object, default: () => ({}) },
});

const getStatusIcon = (status) => {
    if (status === 'operational') return CheckCircle2;
    if (status === 'degraded') return AlertCircle;
    return XCircle;
};

const getStatusColor = (status) => {
    if (status === 'operational') return 'text-green-600';
    if (status === 'degraded') return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Service Status" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Service Status</h1>
                <p class="text-sm text-muted-foreground mt-1">Monitor and manage service status and health</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card
                v-for="(service, key) in services"
                :key="key"
            >
                <CardHeader>
                    <CardTitle class="text-sm flex items-center justify-between">
                        <span>{{ service.name }}</span>
                        <component
                            :is="getStatusIcon(service.status)"
                            :class="['h-5 w-5', getStatusColor(service.status)]"
                        />
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Enabled:</span>
                        <Badge :variant="service.enabled ? 'default' : 'secondary'" class="text-xs">
                            {{ service.enabled ? 'Yes' : 'No' }}
                        </Badge>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Configured:</span>
                        <Badge :variant="service.configured ? 'default' : 'secondary'" class="text-xs">
                            {{ service.configured ? 'Yes' : 'No' }}
                        </Badge>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Status:</span>
                        <Badge
                            :class="{
                                'bg-green-100 text-green-800': service.status === 'operational',
                                'bg-yellow-100 text-yellow-800': service.status === 'degraded',
                                'bg-red-100 text-red-800': service.status === 'down',
                            }"
                            class="text-xs capitalize"
                        >
                            {{ service.status }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
