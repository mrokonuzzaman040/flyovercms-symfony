<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Plus, Eye, Pencil, Trash2, Filter, Bell, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
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
    alerts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const { hasPermission } = usePermissions();

const showDeleteDialog = ref(false);
const alertToDelete = ref(null);

const confirmDelete = (alert) => {
    alertToDelete.value = alert;
    showDeleteDialog.value = true;
};

const deleteAlert = () => {
    if (alertToDelete.value) {
        router.delete(route('marketing.campaign-alerts.destroy', alertToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                alertToDelete.value = null;
            },
        });
    }
};

const SEVERITY_BADGE = Object.freeze({
        low: 'secondary',
        medium: 'default',
        high: 'destructive',
        critical: 'destructive',
    });
const getSeverityBadge = (severity) => SEVERITY_BADGE[severity] || 'secondary';

const SEVERITY_L_A_B_E_L_S = Object.freeze({
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        critical: 'Critical',
    });
const getSeverityLabel = (severity) => SEVERITY_L_A_B_E_L_S[severity] || severity;

const applyFilter = (key, value) => {
    router.get(route('marketing.campaign-alerts.index'), { ...props.filters, [key]: value }, { preserveState: true });
};
</script>

<template>
    <Head title="Campaign Alerts" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Campaign Alerts</h1>
                <p class="text-xs text-muted-foreground">Monitor and manage campaign performance alerts</p>
            </div>
            <Button v-if="hasPermission('create-leads') || hasPermission('view-leads')" as-child size="sm" class="gap-1.5">
                <Link :href="route('marketing.campaign-alerts.create')">
                    <Plus class="h-3.5 w-3.5" />
                    New Alert
                </Link>
            </Button>
        </div>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <AlertCircle class="h-3.5 w-3.5" />
            {{ $page.props.flash.success }}
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex items-center gap-3">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.is_active === '1' ? 'Active' : filters.is_active === '0' ? 'Inactive' : 'All Status' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.is_active?.toString() || 'all'" @update:model-value="(v) => applyFilter('is_active', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Status</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="1" class="text-xs">Active</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="0" class="text-xs">Inactive</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[140px] text-xs justify-between">
                                <span>{{ filters.is_triggered === '1' ? 'Triggered' : filters.is_triggered === '0' ? 'Not Triggered' : 'All Alerts' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[140px]">
                            <DropdownMenuRadioGroup :model-value="filters.is_triggered?.toString() || 'all'" @update:model-value="(v) => applyFilter('is_triggered', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Alerts</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="1" class="text-xs">Triggered</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="0" class="text-xs">Not Triggered</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </CardContent>
        </Card>

        <!-- Alerts Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded" />
                    All Campaign Alerts
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Alert</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Campaign</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Type</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Metric</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Severity</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="alert in alerts.data" :key="alert.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white shrink-0">
                                            <Bell class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium truncate">{{ alert.name }}</div>
                                            <div class="text-[10px] text-muted-foreground truncate max-w-[200px]">
                                                {{ alert.description?.substring(0, 50) }}{{ alert.description?.length > 50 ? '...' : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs">
                                        <span v-if="alert.campaign" class="text-muted-foreground">{{ alert.campaign.name }}</span>
                                        <span v-else class="text-muted-foreground">All Campaigns</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-[10px] capitalize">
                                        {{ alert.alert_type }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs">
                                        <div class="font-medium">{{ alert.metric }}</div>
                                        <div class="text-[10px] text-muted-foreground">
                                            {{ alert.operator }} {{ alert.threshold_value }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="getSeverityBadge(alert.severity)" class="text-[10px]">
                                        {{ getSeverityLabel(alert.severity) }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1">
                                        <Badge :variant="alert.is_triggered ? 'destructive' : 'secondary'" class="text-[10px]">
                                            {{ alert.is_triggered ? 'Triggered' : 'Active' }}
                                        </Badge>
                                        <CheckCircle2 v-if="alert.acknowledged_at" class="h-3.5 w-3.5 text-green-600" />
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.campaign-alerts.show', alert.id)">
                                                <Eye class="h-3.5 w-3.5 text-blue-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('edit-leads') || hasPermission('view-leads')" variant="ghost" size="icon" class="h-7 w-7" as-child>
                                            <Link :href="route('marketing.campaign-alerts.edit', alert.id)">
                                                <Pencil class="h-3.5 w-3.5 text-amber-600" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('delete-leads') || hasPermission('view-leads')" variant="ghost" size="icon" class="h-7 w-7" @click="confirmDelete(alert)">
                                            <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!alerts.data?.length">
                                <td colspan="7" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <Bell class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">No alerts found</p>
                                            <p class="text-xs text-muted-foreground">Get started by creating your first campaign alert.</p>
                                        </div>
                                        <Button v-if="hasPermission('create-leads') || hasPermission('view-leads')" as-child size="sm" class="mt-2 gap-1.5">
                                            <Link :href="route('marketing.campaign-alerts.create')">
                                                <Plus class="h-3.5 w-3.5" />
                                                New Alert
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="alerts.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in alerts.links" :key="index">
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
        title="Delete Campaign Alert"
        :description="`Are you sure you want to delete '${alertToDelete?.name}'? This action cannot be undone.`"
        @confirm="deleteAlert"
    />
</template>
