<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Plus, Eye, Pencil, Trash2, Megaphone, AlertCircle, Filter } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuRadioGroup, DropdownMenuRadioItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaigns: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    channel: { type: String, default: 'sms' },
    channelLabel: { type: String, default: 'SMS' },
});

const { hasPermission } = usePermissions();
const showDeleteDialog = ref(false);
const campaignToDelete = ref(null);

const campaignsIndex = () => route(`marketing.${props.channel}.campaigns.index`);
const campaignsCreate = () => route(`marketing.${props.channel}.campaigns.create`);
const campaignShow = (id) => route(`marketing.${props.channel}.campaigns.show`, id);
const campaignEdit = (id) => route(`marketing.${props.channel}.campaigns.edit`, id);
const campaignDestroy = (id) => route(`marketing.${props.channel}.campaigns.destroy`, id);

const confirmDelete = (campaign) => {
    campaignToDelete.value = campaign;
    showDeleteDialog.value = true;
};

const deleteCampaign = () => {
    if (campaignToDelete.value) {
        router.delete(campaignDestroy(campaignToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                campaignToDelete.value = null;
            },
        });
    }
};

const STATUS_BADGE = Object.freeze({
        draft: 'secondary',
        scheduled: 'default',
        running: 'default',
        completed: 'default',
        cancelled: 'destructive',
        paused_low_balance: 'destructive',
    });
const getStatusBadge = (status) => STATUS_BADGE[status] || 'secondary';

const STATUS_L_A_B_E_L_S = Object.freeze({
        draft: 'Draft',
        scheduled: 'Scheduled',
        running: 'Running',
        completed: 'Completed',
        cancelled: 'Cancelled',
        paused_low_balance: 'Paused (low SMS balance)',
    });
const getStatusLabel = (status) => STATUS_L_A_B_E_L_S[status] || status;

const applyFilter = (key, value) => {
    router.get(campaignsIndex(), { ...props.filters, [key]: value }, { preserveState: true });
};

function isCampaignEditable(status) {
    return status !== 'running' && status !== 'completed';
}
</script>

<template>
    <Head :title="`${channelLabel} Campaigns`" />

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">{{ channelLabel }} Campaigns</h1>
                <p class="text-xs text-muted-foreground">Create and manage {{ channelLabel }} campaigns</p>
            </div>
            <Button v-if="hasPermission('manage-campaigns')" as-child size="sm" class="gap-1.5">
                <Link :href="campaignsCreate()">
                    <Plus class="h-3.5 w-3.5" />
                    New {{ channelLabel }} Campaign
                </Link>
            </Button>
        </div>

        <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <AlertCircle class="h-3.5 w-3.5" />
            {{ $page.props.flash.success }}
        </div>

        <Card>
            <CardContent class="p-3">
                <div class="flex items-center gap-3">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.status ? getStatusLabel(filters.status) : 'All Status' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.status || 'all'" @update:model-value="(v) => applyFilter('status', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Status</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="draft" class="text-xs">Draft</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="scheduled" class="text-xs">Scheduled</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="running" class="text-xs">Running</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="completed" class="text-xs">Completed</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="cancelled" class="text-xs">Cancelled</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="paused_low_balance" class="text-xs">Paused (low SMS balance)</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-sky-500 rounded" />
                    {{ channelLabel }} Campaigns
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Campaign</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Target</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Recipients</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Cost</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-sky-500 rounded-lg flex items-center justify-center text-white shrink-0 text-lg">📱</div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium truncate">{{ campaign.name }}</div>
                                            <div class="text-[10px] text-muted-foreground truncate max-w-[200px]">{{ campaign.description?.substring(0, 50) }}{{ campaign.description?.length > 50 ? '...' : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-xs text-muted-foreground">
                                    <div v-if="campaign.package">Package: {{ campaign.package.name }}</div>
                                    <div v-else-if="campaign.service">Service: {{ campaign.service.name }}</div>
                                    <div v-else>All Leads</div>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <div class="font-medium">{{ campaign.total_recipients || 0 }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ campaign.sent_count || 0 }} sent</div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="getStatusBadge(campaign.status)" class="text-[10px]">{{ getStatusLabel(campaign.status) }}</Badge>
                                </td>
                                <td class="px-3 py-2 text-xs font-medium text-sky-600 dark:text-sky-400">{{ campaign.total_cost ? `৳${Number(campaign.total_cost).toFixed(2)}` : '৳0.00' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="campaignShow(campaign.id)"><Eye class="h-3.5 w-3.5 text-sky-600" /></Link>
                                        </Button>
                                        <Button v-if="hasPermission('manage-campaigns') && isCampaignEditable(campaign.status)" variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="campaignEdit(campaign.id)"><Pencil class="h-3.5 w-3.5 text-amber-600" /></Link>
                                        </Button>
                                        <Button v-if="hasPermission('manage-campaigns') && campaign.status !== 'running'" variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(campaign)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!campaigns.data?.length">
                                <td colspan="6" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center"><Megaphone class="h-6 w-6 text-muted-foreground" /></div>
                                        <p class="text-sm font-medium">No {{ channelLabel }} campaigns yet</p>
                                        <p class="text-xs text-muted-foreground">Create your first {{ channelLabel }} campaign.</p>
                                        <Button v-if="hasPermission('manage-campaigns')" as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="campaignsCreate()"><Plus class="h-3.5 w-3.5" />New Campaign</Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="campaigns.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in campaigns.links" :key="index">
                        <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm" class="h-7 text-xs" as-child><Link :href="link.url" v-html="link.label" /></Button>
                        <Button v-else variant="outline" size="sm" class="h-7 text-xs" disabled v-html="link.label" />
                    </template>
                </div>
            </CardContent>
        </Card>

        <DeleteConfirmation v-model:open="showDeleteDialog" title="Delete Campaign" :description="`Are you sure you want to delete '${campaignToDelete?.name}'? This action cannot be undone.`" @confirm="deleteCampaign" />
    </div>
</template>
