<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Layers } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Multi-Channel Campaign: ${campaign.name}`" />

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.multi-channel-campaigns.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ campaign.name }}</h1>
                    <p v-if="campaign.description" class="text-xs text-muted-foreground">{{ campaign.description }}</p>
                </div>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link :href="route('marketing.multi-channel-campaigns.edit', campaign.id)">
                    <Pencil class="h-3.5 w-3.5 mr-1.5" />
                    Edit
                </Link>
            </Button>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Type</div>
                    <Badge variant="outline" class="mt-1">{{ campaign.orchestration_type }}</Badge>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Sent</div>
                    <div class="text-lg font-semibold">{{ campaign.total_sent || 0 }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Delivered</div>
                    <div class="text-lg font-semibold">{{ campaign.total_delivered || 0 }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="text-xs text-muted-foreground">Status</div>
                    <Badge :variant="campaign.is_active ? 'default' : 'secondary'" class="mt-1">
                        {{ campaign.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <Layers class="h-4 w-4" />
                    Channel Sequence
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <div class="flex gap-2">
                    <Badge v-for="(channel, index) in campaign.channel_sequence" :key="index" variant="outline" class="uppercase">
                        {{ channel }}
                    </Badge>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
