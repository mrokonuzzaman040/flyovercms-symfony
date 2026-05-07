<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Droplets } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    dripCampaign: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Drip Campaign: ${dripCampaign.name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.drip-campaigns.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ dripCampaign.name }}</h1>
                    <p v-if="dripCampaign.description" class="text-xs text-muted-foreground">{{ dripCampaign.description }}</p>
                </div>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link :href="route('marketing.drip-campaigns.edit', dripCampaign.id)">
                    <Pencil class="h-3.5 w-3.5 mr-1.5" />
                    Edit
                </Link>
            </Button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Steps</div>
                    <div class="text-lg font-semibold">{{ dripCampaign.steps?.length || 0 }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Enrolled</div>
                    <div class="text-lg font-semibold">{{ dripCampaign.total_enrolled || 0 }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Completed</div>
                    <div class="text-lg font-semibold">{{ dripCampaign.total_completed || 0 }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Status</div>
                    <Badge :variant="dripCampaign.is_active ? 'default' : 'secondary'" class="mt-1">
                        {{ dripCampaign.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </CardContent>
            </Card>
        </div>

        <!-- Steps -->
        <Card v-if="dripCampaign.steps?.length">
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <Droplets class="h-4 w-4" />
                    Campaign Steps
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <div class="space-y-4">
                    <div v-for="(step, index) in dripCampaign.steps" :key="step.id" class="border rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <Badge variant="outline">Step {{ step.step_order }}</Badge>
                                <span class="text-sm font-medium">{{ step.name }}</span>
                            </div>
                            <Badge variant="outline" class="uppercase">{{ step.channel }}</Badge>
                        </div>
                        <p class="text-xs text-muted-foreground mb-2">{{ step.message }}</p>
                        <div class="text-xs text-muted-foreground">
                            Delay: {{ step.delay_days }} days, {{ step.delay_hours }} hours
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
