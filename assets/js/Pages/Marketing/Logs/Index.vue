<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Filter, Search, Phone, Mail, MessageSquare, FileText } from 'lucide-vue-next';
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
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.filters.search ?? '');
watch(() => props.filters.search, (v) => {
    searchQuery.value = v ?? '';
});

const applyFilter = (key, value) => {
    const next = { ...props.filters, [key]: value || undefined };
    if (!value) delete next[key];
    router.get(route('marketing.logs.index'), next, { preserveState: true });
};

const applySearch = () => {
    applyFilter('search', searchQuery.value?.trim() || null);
};

const CHANNEL_I_C_O_N_S = Object.freeze({ sms: Phone, email: Mail, whatsapp: MessageSquare });
const getChannelIcon = (channel) => CHANNEL_I_C_O_N_S[channel] || FileText;

const CHANNEL_L_A_B_E_L_S = Object.freeze({ sms: 'SMS', email: 'Email', whatsapp: 'WhatsApp' });
const getChannelLabel = (channel) => CHANNEL_L_A_B_E_L_S[channel] || channel;

const STATUS_BADGE = Object.freeze({
        pending: 'secondary',
        sent: 'default',
        delivered: 'default',
        failed: 'destructive',
        bounced: 'destructive',
    });
const getStatusBadge = (status) => STATUS_BADGE[status] || 'secondary';

const STATUS_L_A_B_E_L_S = Object.freeze({
        pending: 'Pending',
        sent: 'Sent',
        delivered: 'Delivered',
        failed: 'Failed',
        bounced: 'Bounced',
    });
const getStatusLabel = (status) => STATUS_L_A_B_E_L_S[status] || status;

const messagePreview = (msg, max = 50) => {
    if (!msg) return '—';
    return msg.length > max ? msg.slice(0, max) + '…' : msg;
};

const recipientDisplay = (log) => {
    if (log.channel === 'email' && log.recipient_email) return log.recipient_email;
    if ((log.channel === 'sms' || log.channel === 'whatsapp') && log.recipient_phone) return log.recipient_phone;
    return log.recipient_name || '—';
};

const sourceDisplay = (log) => {
    if (log.campaign?.name) return log.campaign.name;
    if (log.automated_campaign?.name) return `Auto: ${log.automated_campaign.name}`;
    return 'Direct (Lead)';
};
</script>

<template>
    <Head title="Marketing Logs" />

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Marketing Logs</h1>
                <p class="text-xs text-muted-foreground">SMS, WhatsApp (WasenderAPI), and email delivery logs including errors</p>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex flex-wrap items-center gap-3">
                    <Filter class="h-4 w-4 text-muted-foreground shrink-0" />
                    <div class="relative flex-1 min-w-[160px] max-w-xs">
                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search recipient or message..."
                            class="pl-8 h-8 text-xs pr-16"
                            @keydown.enter="applySearch"
                        />
                        <Button size="sm" variant="ghost" class="absolute right-0 top-0 h-8 px-2" @click="applySearch">
                            Search
                        </Button>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[120px] text-xs justify-between">
                                <span>{{ filters.channel ? getChannelLabel(filters.channel) : 'All Channels' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[120px]">
                            <DropdownMenuRadioGroup :model-value="filters.channel || 'all'" @update:model-value="(v) => applyFilter('channel', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Channels</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="email" class="text-xs">Email</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-8 w-[120px] text-xs justify-between">
                                <span>{{ filters.status ? getStatusLabel(filters.status) : 'All Status' }}</span>
                                <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[120px]">
                            <DropdownMenuRadioGroup :model-value="filters.status || 'all'" @update:model-value="(v) => applyFilter('status', v === 'all' ? null : v)">
                                <DropdownMenuRadioItem value="all" class="text-xs">All Status</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="pending" class="text-xs">Pending</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="sent" class="text-xs">Sent</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="delivered" class="text-xs">Delivered</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="failed" class="text-xs">Failed</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="bounced" class="text-xs">Bounced</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div class="flex items-center gap-2">
                        <Input
                            type="date"
                            :value="filters.date_from"
                            class="h-8 text-xs w-[130px]"
                            @input="(e) => applyFilter('date_from', e.target.value || null)"
                        />
                        <span class="text-muted-foreground text-xs">to</span>
                        <Input
                            type="date"
                            :value="filters.date_to"
                            class="h-8 text-xs w-[130px]"
                            @input="(e) => applyFilter('date_to', e.target.value || null)"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Logs Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-emerald-500 to-emerald-600 rounded" />
                    Message Logs
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Date</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Channel</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Recipient</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Message</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Error</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Cost</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Source</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ log.created_at ? new Date(log.created_at).toLocaleString() : '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-[10px] gap-1">
                                        <component :is="getChannelIcon(log.channel)" class="h-3 w-3" />
                                        {{ getChannelLabel(log.channel) }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs max-w-[180px] truncate" :title="recipientDisplay(log)">
                                        {{ recipientDisplay(log) }}
                                    </div>
                                    <div v-if="log.lead" class="text-[10px] text-muted-foreground">
                                        <Link :href="route('leads.show', log.lead.id)" class="hover:underline">{{ log.lead.full_name }}</Link>
                                    </div>
                                </td>
                                <td class="px-3 py-2 max-w-[200px]">
                                    <span class="text-xs text-muted-foreground" :title="log.message">{{ messagePreview(log.message) }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="getStatusBadge(log.status)" class="text-[10px]">
                                        {{ getStatusLabel(log.status) }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 max-w-[220px]">
                                    <template v-if="log.status === 'failed' && log.error_message">
                                        <span class="text-xs text-destructive" :title="log.error_message">{{ messagePreview(log.error_message, 40) }}</span>
                                        <div v-if="log.error_details && Object.keys(log.error_details).length" class="text-[10px] text-muted-foreground mt-0.5">
                                            <details class="cursor-pointer">
                                                <summary>Details</summary>
                                                <pre class="mt-1 p-1 bg-muted rounded text-[10px] overflow-auto max-h-24">{{ JSON.stringify(log.error_details, null, 2) }}</pre>
                                            </details>
                                        </div>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    {{ log.cost != null && Number(log.cost) > 0 ? `৳${Number(log.cost).toFixed(4)}` : '—' }}
                                </td>
                                <td class="px-3 py-2 text-xs text-muted-foreground max-w-[140px] truncate" :title="sourceDisplay(log)">
                                    {{ sourceDisplay(log) }}
                                </td>
                            </tr>
                            <tr v-if="!logs.data?.length">
                                <td colspan="8" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <FileText class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <p class="text-sm font-medium">No logs found</p>
                                        <p class="text-xs text-muted-foreground">Messages will appear here after campaigns are sent.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="logs.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in logs.links" :key="index">
                        <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm" class="h-7 text-xs" as-child>
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <Button v-else variant="outline" size="sm" class="h-7 text-xs" disabled v-html="link.label" />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
