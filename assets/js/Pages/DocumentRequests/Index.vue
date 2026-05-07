<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { Separator } from '@/Components/ui/separator';
import StatusBadge from '@/Components/shared/StatusBadge.vue';
import {
    Clock,
    CheckCircle2,
    XCircle,
    List,
    ChevronDown,
    ChevronRight,
    Mail,
    Phone,
    FileText,
    Eye,
    Filter,
    X,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    groupedRequests: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    statusFilter: { type: String, default: null },
});

const expandedLeads = ref(new Set());
const statusFilter = ref(props.statusFilter || 'all');

const toggleLead = (leadId) => {
    if (expandedLeads.value.has(leadId)) {
        expandedLeads.value.delete(leadId);
    } else {
        expandedLeads.value.add(leadId);
    }
};

const applyFilter = () => {
    router.get(route('document-requests.index'), {
        status: statusFilter.value && statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
    });
};

const clearFilter = () => {
    statusFilter.value = 'all';
    router.get(route('document-requests.index'), {}, {
        preserveState: false,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.charAt(0).toUpperCase();
};
</script>

<template>
    <Head title="Requested Documents" />
    
    <div class="space-y-3">
        <!-- Header -->
        <div class="flex flex-col gap-0.5">
            <h1 class="text-lg font-semibold tracking-tight">Document Requests</h1>
            <p class="text-[11px] text-muted-foreground">Manage and track document requests from leads</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <Link :href="route('document-requests.index', { status: 'pending' })">
                <Card class="transition-all hover:shadow-sm cursor-pointer border-yellow-200 dark:border-yellow-900/50 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-6 w-6 rounded-md bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center shrink-0">
                                <Clock class="h-3.5 w-3.5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-muted-foreground truncate">Pending</p>
                                <p class="text-sm font-semibold leading-tight">{{ stats.pending || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </Card>
            </Link>

            <Link :href="route('document-requests.index', { status: 'fulfilled' })">
                <Card class="transition-all hover:shadow-sm cursor-pointer border-green-200 dark:border-green-900/50 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-6 w-6 rounded-md bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                <CheckCircle2 class="h-3.5 w-3.5 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-muted-foreground truncate">Fulfilled</p>
                                <p class="text-sm font-semibold leading-tight">{{ stats.fulfilled || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </Card>
            </Link>

            <Link :href="route('document-requests.index', { status: 'cancelled' })">
                <Card class="transition-all hover:shadow-sm cursor-pointer border-red-200 dark:border-red-900/50 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-6 w-6 rounded-md bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                                <XCircle class="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-muted-foreground truncate">Cancelled</p>
                                <p class="text-sm font-semibold leading-tight">{{ stats.cancelled || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </Card>
            </Link>

            <Link :href="route('document-requests.index')">
                <Card class="transition-all hover:shadow-sm cursor-pointer border-primary/50 p-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-6 w-6 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                <List class="h-3.5 w-3.5 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-muted-foreground truncate">All</p>
                                <p class="text-sm font-semibold leading-tight">
                                    {{ (stats.pending || 0) + (stats.fulfilled || 0) + (stats.cancelled || 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </Link>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-2">
            <Filter class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="h-7 text-xs justify-between min-w-[120px] px-2.5">
                        <span>
                            {{ statusFilter === 'all' ? 'All Statuses' : statusFilter === 'pending' ? 'Pending' : statusFilter === 'fulfilled' ? 'Fulfilled' : 'Cancelled' }}
                        </span>
                        <ChevronDown class="h-3 w-3 opacity-50 ml-1.5 shrink-0" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                    <DropdownMenuLabel class="text-xs">Status</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuRadioGroup v-model="statusFilter" @update:model-value="applyFilter">
                        <DropdownMenuRadioItem value="all" class="text-xs">All Statuses</DropdownMenuRadioItem>
                        <DropdownMenuRadioItem value="pending" class="text-xs">Pending</DropdownMenuRadioItem>
                        <DropdownMenuRadioItem value="fulfilled" class="text-xs">Fulfilled</DropdownMenuRadioItem>
                        <DropdownMenuRadioItem value="cancelled" class="text-xs">Cancelled</DropdownMenuRadioItem>
                    </DropdownMenuRadioGroup>
                </DropdownMenuContent>
            </DropdownMenu>
            <Button
                variant="outline"
                size="sm"
                class="h-7 text-xs px-2.5"
                @click="clearFilter"
                :disabled="statusFilter === 'all'"
            >
                <X class="h-3 w-3 mr-1 shrink-0" />
                Clear
            </Button>
        </div>

        <!-- Grouped Document Requests by Lead -->
        <div v-if="groupedRequests.length > 0" class="space-y-2">
            <Collapsible
                v-for="group in groupedRequests"
                :key="group.lead_id"
                :open="expandedLeads.has(group.lead_id)"
                @update:open="(open) => open ? expandedLeads.add(group.lead_id) : expandedLeads.delete(group.lead_id)"
            >
                <Card class="overflow-hidden">
                    <CollapsibleTrigger as-child>
                        <CardHeader class="cursor-pointer hover:bg-muted/50 transition-colors py-2 px-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <ChevronRight
                                        class="h-3 w-3 text-muted-foreground transition-transform duration-200 shrink-0"
                                        :class="{ 'rotate-90': expandedLeads.has(group.lead_id) }"
                                    />
                                    <Avatar class="h-6 w-6 shrink-0">
                                        <AvatarFallback class="bg-primary text-primary-foreground font-medium text-[10px]">
                                            {{ getInitials(group.lead?.full_name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex-1 min-w-0">
                                        <Link
                                            :href="route('leads.show', group.lead_id)"
                                            class="font-medium text-xs hover:text-primary transition-colors block truncate"
                                            @click.stop
                                        >
                                            {{ group.lead?.full_name || 'Unknown Lead' }}
                                        </Link>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-muted-foreground flex-wrap">
                                            <span v-if="group.lead?.email" class="flex items-center gap-0.5 truncate max-w-[140px]">
                                                <Mail class="h-2.5 w-2.5 shrink-0" />
                                                <span class="truncate">{{ group.lead.email }}</span>
                                            </span>
                                            <span v-if="group.lead?.phone" class="flex items-center gap-0.5">
                                                <Phone class="h-2.5 w-2.5 shrink-0" />
                                                {{ group.lead.phone }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 ml-2 shrink-0">
                                    <Badge
                                        v-if="group.pending_count > 0"
                                        variant="secondary"
                                        class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800 text-[10px] px-1 py-0"
                                    >
                                        <Clock class="mr-0.5 h-2.5 w-2.5" />
                                        {{ group.pending_count }}
                                    </Badge>
                                    <Badge
                                        v-if="group.fulfilled_count > 0"
                                        variant="secondary"
                                        class="bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800 text-[10px] px-1 py-0"
                                    >
                                        <CheckCircle2 class="mr-0.5 h-2.5 w-2.5" />
                                        {{ group.fulfilled_count }}
                                    </Badge>
                                    <Badge
                                        v-if="group.cancelled_count > 0"
                                        variant="secondary"
                                        class="bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800 text-[10px] px-1 py-0"
                                    >
                                        <XCircle class="mr-0.5 h-2.5 w-2.5" />
                                        {{ group.cancelled_count }}
                                    </Badge>
                                </div>
                            </div>
                        </CardHeader>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <CardContent class="pt-0 pb-2 px-3">
                            <Separator class="mb-2" />
                            <div class="space-y-1.5">
                                <div
                                    v-for="request in group.requests"
                                    :key="request.id"
                                    class="p-2 rounded-md border bg-muted/30 hover:bg-muted/50 transition-colors"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex-1 min-w-0 flex items-center gap-2">
                                            <div class="h-5 w-5 rounded bg-primary/10 flex items-center justify-center shrink-0">
                                                <FileText class="h-3 w-3 text-primary" />
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-medium text-[11px] truncate">{{ request.document_type_name }}</h4>
                                                <div class="flex items-center gap-2 text-[10px] text-muted-foreground">
                                                    <span>{{ formatDate(request.requested_at) }}</span>
                                                    <span v-if="request.requester">· {{ request.requester.name }}</span>
                                                </div>
                                            </div>
                                            <StatusBadge :status="request.status" size="sm" class="shrink-0" />
                                        </div>
                                        <Button variant="ghost" size="icon" class="h-6 w-6 shrink-0" as-child>
                                            <Link :href="route('document-requests.show', request.id)">
                                                <Eye class="h-3 w-3" />
                                            </Link>
                                        </Button>
                                    </div>
                                    <p v-if="request.message" class="text-[10px] text-muted-foreground mt-1 ml-7 line-clamp-2">
                                        {{ request.message }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </CollapsibleContent>
                </Card>
            </Collapsible>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="flex flex-col items-center justify-center py-8">
                <div class="h-9 w-9 rounded-full bg-muted flex items-center justify-center mb-2">
                    <FileText class="h-4 w-4 text-muted-foreground" />
                </div>
                <h3 class="text-xs font-semibold mb-0.5">No document requests found</h3>
                <p class="text-[11px] text-muted-foreground text-center max-w-xs">
                    {{ statusFilter && statusFilter !== 'all'
                        ? `No ${statusFilter} document requests at this time.`
                        : 'There are no document requests to display.' }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
