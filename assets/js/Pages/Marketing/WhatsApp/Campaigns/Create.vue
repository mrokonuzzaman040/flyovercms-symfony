<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ChannelCampaignDetailsCard from '@/Components/Marketing/Campaigns/ChannelCampaignDetailsCard.vue';
import ChannelCampaignLeadsAndMessage from '@/Components/Marketing/Campaigns/ChannelCampaignLeadsAndMessage.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Loader2, Send } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    channel: { type: String, required: true },
    channelLabel: { type: String, required: true },
});

const indexRoute = () => route(`marketing.${props.channel}.campaigns.index`);
const storeRoute = () => route(`marketing.${props.channel}.campaigns.store`);

const form = useForm({
    name: '',
    description: '',
    type: props.channel,
    message: '',
    scheduled_at: '',
    package_id: null,
    service_id: null,
    target_criteria: {},
    lead_ids: [],
});

const fieldErrors = computed(() => ({
    message: form.errors.message,
    lead_ids: form.errors.lead_ids,
}));

const submit = () => {
    form.post(storeRoute());
};
</script>

<template>
    <Head :title="`Create ${channelLabel} Campaign`" />

    <div class="w-full space-y-6">
        <div class="flex items-center gap-3">
            <Link
                :href="indexRoute()"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create New {{ channelLabel }} Campaign</h1>
                <p class="text-xs text-muted-foreground">Send messages to leads via {{ channelLabel }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <ChannelCampaignDetailsCard
                :form="form"
                accent="emerald"
                :description="`Name and optional targeting · Channel: ${channelLabel}`"
                description-placeholder="Internal note about this campaign"
                name-placeholder="e.g. Summer follow-up"
                :packages="packages"
                :services="services"
            />

            <ChannelCampaignLeadsAndMessage
                v-model:lead-ids="form.lead_ids"
                v-model:message="form.message"
                accent="emerald"
                channel="whatsapp"
                :channel-label="channelLabel"
                :field-errors="fieldErrors"
                message-description="Content that selected leads will receive"
                :packages="packages"
                :services="services"
                :status-options="statusOptions"
            />

            <div class="flex flex-col-reverse items-stretch justify-end gap-2 pt-2 sm:flex-row sm:items-center">
                <Button type="button" variant="outline" size="sm" class="sm:order-first" as-child>
                    <Link :href="indexRoute()">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5 bg-emerald-500 text-white hover:bg-emerald-600">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Send v-else class="h-3.5 w-3.5" />
                    Create Campaign
                </Button>
            </div>
        </form>
    </div>
</template>
