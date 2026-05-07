<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ChannelCampaignDetailsCard from '@/Components/Marketing/Campaigns/ChannelCampaignDetailsCard.vue';
import ChannelCampaignLeadsAndMessage from '@/Components/Marketing/Campaigns/ChannelCampaignLeadsAndMessage.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    channel: { type: String, required: true },
    channelLabel: { type: String, required: true },
});

const indexRoute = () => route(`marketing.${props.channel}.campaigns.index`);
const showRoute = (id) => route(`marketing.${props.channel}.campaigns.show`, id);
const updateRoute = (id) => route(`marketing.${props.channel}.campaigns.update`, id);

const targetCriteriaBase = { ...(props.campaign.target_criteria || {}) };
delete targetCriteriaBase.lead_ids;

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    type: props.campaign.type,
    message: props.campaign.message || '',
    scheduled_at: props.campaign.scheduled_at ? new Date(props.campaign.scheduled_at).toISOString().slice(0, 16) : '',
    package_id: props.campaign.package_id,
    service_id: props.campaign.service_id,
    target_criteria: targetCriteriaBase,
    lead_ids: props.campaign.target_criteria?.lead_ids ?? [],
});

const submit = () => {
    form.put(updateRoute(props.campaign.id));
};

const fieldErrors = computed(() => ({
    message: form.errors.message,
    lead_ids: form.errors.lead_ids,
}));
</script>

<template>
    <Head :title="`Edit ${channelLabel} Campaign`" />

    <div class="w-full space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <Link
                    :href="showRoute(campaign.id)"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div class="min-w-0">
                    <h1 class="text-lg font-semibold tracking-tight">Edit {{ channelLabel }} Campaign</h1>
                    <p class="text-xs text-muted-foreground">
                        Update details, recipients, and message ·
                        <Link :href="indexRoute()" class="text-sky-600 hover:underline dark:text-sky-400">Back to list</Link>
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <ChannelCampaignDetailsCard :form="form" accent="sky" :packages="packages" :services="services" />

            <ChannelCampaignLeadsAndMessage
                v-model:lead-ids="form.lead_ids"
                v-model:message="form.message"
                accent="sky"
                :channel="channel"
                :channel-label="channelLabel"
                :packages="packages"
                :services="services"
                :status-options="statusOptions"
                :field-errors="fieldErrors"
            />

            <div class="flex flex-col-reverse items-stretch justify-end gap-2 pt-2 sm:flex-row sm:items-center">
                <Button type="button" variant="outline" size="sm" class="sm:order-first" as-child>
                    <Link :href="showRoute(campaign.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5 bg-sky-500 text-white hover:bg-sky-600">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Save v-else class="h-3.5 w-3.5" />
                    Save changes
                </Button>
            </div>
        </form>
    </div>
</template>
