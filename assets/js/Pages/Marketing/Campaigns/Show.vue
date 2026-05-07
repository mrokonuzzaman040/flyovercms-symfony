<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, Send, XCircle, Loader2, Users, CheckCircle, XCircle as XCircleIcon, Clock, DollarSign, TrendingUp, RotateCcw } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
    pending_recipients_count: { type: Number, default: 0 },
});

const pendingRecipientsCount = computed(() => Number(props.pending_recipients_count ?? 0));
const canResumeCancelledCampaign = computed(() => props.campaign.status === 'cancelled' && pendingRecipientsCount.value > 0);

const starting = ref(false);
const resuming = ref(false);
const cancelling = ref(false);
const preparing = ref(false);

const prepareRecipients = () => {
    preparing.value = true;
    router.post(route('marketing.campaigns.prepare-recipients', props.campaign.id), {}, {
        onFinish: () => {
            preparing.value = false;
        },
    });
};

const startCampaign = () => {
    starting.value = true;
    router.post(route('marketing.campaigns.start', props.campaign.id), {}, {
        onFinish: () => {
            starting.value = false;
        },
    });
};

const resumeCampaign = () => {
    if (!confirm('Resume sending to pending recipients only? Already-delivered contacts will not be sent again.')) {
        return;
    }
    resuming.value = true;
    router.post(route('marketing.campaigns.resume', props.campaign.id), {}, {
        onFinish: () => {
            resuming.value = false;
        },
    });
};

const cancelCampaign = () => {
    if (!confirm('Are you sure you want to cancel this campaign?')) {
        return;
    }
    cancelling.value = true;
    router.post(route('marketing.campaigns.cancel', props.campaign.id), {}, {
        onFinish: () => {
            cancelling.value = false;
        },
    });
};

const STATUS_BADGE = Object.freeze({
        draft: 'secondary',
        scheduled: 'default',
        running: 'default',
        completed: 'default',
        cancelled: 'destructive',
    });
const getStatusBadge = (status) => STATUS_BADGE[status] || 'secondary';

const STATUS_L_A_B_E_L_S = Object.freeze({
        draft: 'Draft',
        scheduled: 'Scheduled',
        running: 'Running',
        completed: 'Completed',
        cancelled: 'Cancelled',
    });
const getStatusLabel = (status) => STATUS_L_A_B_E_L_S[status] || status;
</script>

<template>
    <Head :title="campaign.name" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('marketing.campaigns.index')"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ campaign.name }}</h1>
                    <p class="text-xs text-muted-foreground">{{ campaign.description || 'No description' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button
                    v-if="(campaign.status === 'draft' || campaign.status === 'scheduled') && campaign.total_recipients === 0"
                    variant="outline"
                    size="sm"
                    @click="prepareRecipients"
                    :disabled="preparing"
                    class="gap-1.5"
                >
                    <Loader2 v-if="preparing" class="h-3.5 w-3.5 animate-spin" />
                    <Users v-else class="h-3.5 w-3.5" />
                    Prepare Recipients
                </Button>
                <Button v-if="campaign.status === 'draft' || campaign.status === 'scheduled'" variant="default" size="sm" @click="startCampaign" :disabled="starting" class="gap-1.5">
                    <Loader2 v-if="starting" class="h-3.5 w-3.5 animate-spin" />
                    <Send v-else class="h-3.5 w-3.5" />
                    Start Campaign
                </Button>
                <Button v-if="campaign.status === 'running' || campaign.status === 'scheduled'" variant="destructive" size="sm" @click="cancelCampaign" :disabled="cancelling" class="gap-1.5">
                    <Loader2 v-if="cancelling" class="h-3.5 w-3.5 animate-spin" />
                    <XCircle v-else class="h-3.5 w-3.5" />
                    Cancel
                </Button>
                <Button
                    v-if="canResumeCancelledCampaign"
                    variant="default"
                    size="sm"
                    class="gap-1.5"
                    @click="resumeCampaign"
                    :disabled="resuming"
                >
                    <Loader2 v-if="resuming" class="h-3.5 w-3.5 animate-spin" />
                    <RotateCcw v-else class="h-3.5 w-3.5" />
                    Retry pending ({{ pendingRecipientsCount.toLocaleString() }})
                </Button>
                <Button v-if="campaign.status === 'draft'" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.campaigns.edit', campaign.id)">
                        <Pencil class="h-3.5 w-3.5" />
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Total Recipients</p>
                            <p class="text-lg font-semibold">{{ campaign.total_recipients || 0 }}</p>
                        </div>
                        <Users class="h-8 w-8 text-blue-500" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Sent</p>
                            <p class="text-lg font-semibold">{{ campaign.sent_count || 0 }}</p>
                        </div>
                        <CheckCircle class="h-8 w-8 text-green-500" />
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">Delivered</p>
                            <p class="text-lg font-semibold">{{ campaign.delivered_count || 0 }}</p>
                        </div>
                        <TrendingUp class="h-8 w-8 text-purple-500" />
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

        <!-- Campaign Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Campaign Info -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Campaign Details</CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-3">
                    <div>
                        <p class="text-xs text-muted-foreground">Status</p>
                        <Badge :variant="getStatusBadge(campaign.status)" class="mt-1">
                            {{ getStatusLabel(campaign.status) }}
                        </Badge>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Channel</p>
                        <p class="text-sm font-medium mt-1 uppercase">{{ campaign.type }}</p>
                    </div>
                    <div v-if="campaign.package">
                        <p class="text-xs text-muted-foreground">Target Package</p>
                        <p class="text-sm font-medium mt-1">{{ campaign.package.name }}</p>
                    </div>
                    <div v-if="campaign.service">
                        <p class="text-xs text-muted-foreground">Target Service</p>
                        <p class="text-sm font-medium mt-1">{{ campaign.service.name }}</p>
                    </div>
                    <div v-if="campaign.scheduled_at">
                        <p class="text-xs text-muted-foreground">Scheduled At</p>
                        <p class="text-sm font-medium mt-1">{{ new Date(campaign.scheduled_at).toLocaleString() }}</p>
                    </div>
                    <div v-if="campaign.started_at">
                        <p class="text-xs text-muted-foreground">Started At</p>
                        <p class="text-sm font-medium mt-1">{{ new Date(campaign.started_at).toLocaleString() }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Message Preview -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Message</CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="bg-muted p-3 rounded-lg">
                        <p class="text-xs whitespace-pre-wrap">{{ campaign.message }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recipients -->
        <Card v-if="campaign.recipients?.length">
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm">Recipients ({{ campaign.recipients.length }})</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Lead</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Sent At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="recipient in campaign.recipients.slice(0, 10)" :key="recipient.id">
                                <td class="px-3 py-2">
                                    <div class="text-xs font-medium">{{ recipient.lead?.full_name }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ recipient.lead?.phone }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="secondary" class="text-[10px]">
                                        {{ recipient.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ recipient.sent_at ? new Date(recipient.sent_at).toLocaleString() : '-' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
