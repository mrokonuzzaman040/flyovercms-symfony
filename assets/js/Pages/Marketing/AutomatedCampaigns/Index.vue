<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Plus, Eye, Pencil, Trash2, Zap, AlertCircle, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import DeleteConfirmation from '@/Components/shared/DeleteConfirmation.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaigns: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission } = usePermissions();

const showDeleteDialog = ref(false);
const campaignToDelete = ref(null);

const confirmDelete = (campaign) => {
    campaignToDelete.value = campaign;
    showDeleteDialog.value = true;
};

const deleteCampaign = () => {
    if (campaignToDelete.value) {
        router.delete(route('marketing.automated-campaigns.destroy', campaignToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                campaignToDelete.value = null;
            },
        });
    }
};

const toggleActive = (campaign) => {
    router.post(route('marketing.automated-campaigns.toggle-active', campaign.id), {}, {
        preserveScroll: true,
    });
};

const getTriggerLabel = (type) => {
    const labels = {
        birthday: 'Birthday Wish',
        passport_expiry: 'Passport Expiry Alert',
        visa_status_change: 'Visa Status Change',
        custom: 'Custom',
    };
    return labels[type] || type;
};

const applyFilter = (key, value) => {
    router.get(route('marketing.automated-campaigns.index'), { ...props.filters, [key]: value }, { preserveState: true });
};
</script>

<template>
    <Head title="Automated Campaigns" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Automated Campaigns</h1>
                <p class="text-xs text-muted-foreground">Set up automated messages for birthdays, passport expiry, and visa status changes</p>
            </div>
            <Button v-if="hasPermission('create-leads') || hasPermission('view-leads')" as-child size="sm" class="gap-1.5">
                <Link :href="route('marketing.automated-campaigns.create')">
                    <Plus class="h-3.5 w-3.5" />
                    New Automated Campaign
                </Link>
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex items-center gap-3">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[180px] text-xs justify-between">
                                <span>{{ filters.trigger_type ? getTriggerLabel(filters.trigger_type) : 'All Triggers' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[180px]">
                            <DropdownMenuRadioGroup :model-value="filters.trigger_type || 'all'" @update:model-value="(v) => applyFilter('trigger_type', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Triggers</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="birthday" class="text-xs">Birthday</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="passport_expiry" class="text-xs">Passport Expiry</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="visa_status_change" class="text-xs">Visa Status</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.is_active === null ? 'All Status' : filters.is_active ? 'Active' : 'Inactive' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.is_active === null ? 'all' : filters.is_active ? 'active' : 'inactive'" @update:model-value="(v) => applyFilter('is_active', v === 'all' ? null : v === 'active')">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Status</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="active" class="text-xs">Active</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="inactive" class="text-xs">Inactive</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardContent>
        </Card>

        <!-- Campaigns Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                    Automated Campaigns
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Campaign</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Trigger</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Channel</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Sent</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase">Status</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="text-xs font-medium">{{ campaign.name }}</div>
                                    <div class="text-[10px] text-muted-foreground truncate max-w-[200px]">
                                        {{ campaign.description?.substring(0, 50) }}{{ campaign.description?.length > 50 ? '...' : '' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-[10px]">
                                        {{ getTriggerLabel(campaign.trigger_type) }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="secondary" class="text-[10px] uppercase">
                                        {{ campaign.channel }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs">
                                        <div class="font-medium">{{ campaign.total_sent || 0 }}</div>
                                        <div class="text-[10px] text-muted-foreground">
                                            {{ campaign.total_delivered || 0 }} delivered
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 gap-1.5"
                                        @click="toggleActive(campaign)"
                                    >
                                        <ToggleRight v-if="campaign.is_active" class="h-3.5 w-3.5 text-green-500" />
                                        <ToggleLeft v-else class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span class="text-[10px]">{{ campaign.is_active ? 'Active' : 'Inactive' }}</span>
                                    </Button>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.automated-campaigns.show', campaign.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('edit-leads') || hasPermission('view-leads')" variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.automated-campaigns.edit', campaign.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('delete-leads') || hasPermission('view-leads')" variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(campaign)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!campaigns.data?.length">
                                <td colspan="6" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <Zap class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">No automated campaigns found</p>
                                            <p class="text-xs text-muted-foreground">Create your first automated campaign.</p>
                                        </div>
                                        <Button v-if="hasPermission('create-leads') || hasPermission('view-leads')" as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="route('marketing.automated-campaigns.create')">
                                                <Plus class="h-3.5 w-3.5" />
                                                New Automated Campaign
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="campaigns.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in campaigns.links" :key="index">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-7 text-xs"
                            as-child
                        >
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="h-7 text-xs"
                            disabled
                            v-html="link.label"
                        />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Delete Confirmation -->
    <DeleteConfirmation
        v-model:open="showDeleteDialog"
        title="Delete Automated Campaign"
        :description="`Are you sure you want to delete '${campaignToDelete?.name}'? This action cannot be undone.`"
        @confirm="deleteCampaign"
    />
</template>
