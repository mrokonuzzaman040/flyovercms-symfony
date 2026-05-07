<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Phone, Send, Plus, TrendingUp, AlertCircle, CheckCircle, Clock, Zap, Wallet } from 'lucide-vue-next';
import { usePermissions } from '@/composables/usePermissions';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const { hasPermission } = usePermissions();

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recentMessages: { type: Array, default: () => [] },
    isConfigured: { type: Boolean, default: false },
    accountBalance: {
        type: Object,
        default: () => ({ skipped: true, success: false, balance: null, error: null }),
    },
});

const form = useForm({
    phone: '',
    message: '',
    lead_id: null,
});

const SMS_SEGMENT_LENGTH = 160;
const charCount = computed(() => form.message.length);
const segmentCount = computed(() => Math.ceil(charCount.value / SMS_SEGMENT_LENGTH) || 1);

const campaignsUrl = route('marketing.sms.campaigns.index');
const campaignsCreateUrl = route('marketing.sms.campaigns.create');
const templatesUrl = route('marketing.templates.index', { type: 'sms' });
const logsUrl = route('marketing.logs.index', { channel: 'sms' });

const statusVariant = (status) => {
    if (status === 'sent' || status === 'delivered') return 'default';
    if (status === 'failed') return 'destructive';
    return 'secondary';
};

const statusLabel = (status) => {
    const map = { sent: 'Sent', delivered: 'Delivered', failed: 'Failed', pending: 'Pending' };
    return map[status] ?? status;
};
</script>

<template>
    <Head title="SMS Marketing" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-lg shadow-sky-500/25 dark:shadow-sky-600/20">
                    <Phone class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">SMS Marketing</h1>
                    <p class="text-xs text-muted-foreground">Send messages, run campaigns, and track delivery in real time.</p>
                </div>
            </div>
            <Button v-if="hasPermission('manage-campaigns')" size="sm" class="gap-1.5 shrink-0" as-child>
                <Link :href="campaignsCreateUrl">
                    <Plus class="h-3.5 w-3.5" />
                    New campaign
                </Link>
            </Button>
        </div>

        <!-- Connection status -->
        <div v-if="!isConfigured" class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30 p-4 flex flex-wrap items-center gap-3">
            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0" />
            <p class="text-sm text-amber-800 dark:text-amber-200 flex-1 min-w-0">SMS provider is not configured. Set SMS_ACCOUNT_CODE and SMS_API_KEY in .env to send messages.</p>
        </div>
        <div v-else class="rounded-xl border border-emerald-200 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-950/20 p-3 flex items-center gap-2">
            <CheckCircle class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
            <span class="text-sm text-emerald-800 dark:text-emerald-200">SMS provider connected · Quick send and campaigns are available.</span>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200 flex items-center gap-2">
            <CheckCircle class="h-4 w-4 shrink-0" />
            {{ $page.props.flash.success }}
        </div>
        <div v-if="form.errors.message" class="rounded-xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive flex items-center gap-2">
            <AlertCircle class="h-4 w-4 shrink-0" />
            {{ form.errors.message }}
        </div>

        <!-- Provider account balance -->
        <Card
            class="overflow-hidden border-emerald-200/60 bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent dark:border-emerald-800/50 dark:from-emerald-500/15 dark:via-teal-500/10"
        >
            <CardContent class="p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-500/25 dark:bg-emerald-500/20 dark:text-emerald-300 dark:ring-emerald-500/30"
                        >
                            <Wallet class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-foreground">SMS account balance</p>
                            <p class="text-xs text-muted-foreground mt-0.5">Provider wallet (RTCom). Shown when SMS credentials are configured.</p>
                        </div>
                    </div>
                    <div class="sm:text-right shrink-0">
                        <template v-if="accountBalance.skipped">
                            <p class="text-sm text-muted-foreground">Configure SMS in <code class="text-xs rounded bg-muted px-1 py-0.5">.env</code> to load balance.</p>
                        </template>
                        <template v-else-if="accountBalance.success">
                            <p class="text-3xl font-bold tabular-nums tracking-tight text-emerald-800 dark:text-emerald-200">
                                ৳{{ Number(accountBalance.balance ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 }) }}
                            </p>
                            <p class="text-[10px] text-muted-foreground mt-1">Available credit</p>
                        </template>
                        <template v-else>
                            <p class="text-sm font-medium text-destructive">{{ accountBalance.error || 'Could not load balance' }}</p>
                            <p class="text-[10px] text-muted-foreground mt-1">Check API credentials or provider status.</p>
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Stats row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-sky-500/10 to-cyan-500/5 dark:from-sky-500/20 dark:to-cyan-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Zap class="h-4 w-4" />
                        <span class="text-xs font-medium">Today</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-sky-700 dark:text-sky-300">{{ stats.sent_today ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">sent</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-sky-500/10 to-cyan-500/5 dark:from-sky-500/20 dark:to-cyan-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <TrendingUp class="h-4 w-4" />
                        <span class="text-xs font-medium">This week</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-sky-700 dark:text-sky-300">{{ stats.sent_week ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">sent</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-sky-500/10 to-cyan-500/5 dark:from-sky-500/20 dark:to-cyan-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Send class="h-4 w-4" />
                        <span class="text-xs font-medium">This month</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-sky-700 dark:text-sky-300">{{ stats.sent_month ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">sent</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-muted/50 dark:bg-muted/20">
                <CardContent class="p-4">
                    <span class="text-xs font-medium text-muted-foreground">Delivery rate</span>
                    <p class="mt-1 text-2xl font-bold tabular-nums">{{ stats.delivery_rate ?? 100 }}%</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-muted/50 dark:bg-muted/20">
                <CardContent class="p-4">
                    <span class="text-xs font-medium text-muted-foreground">Failed (30d)</span>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-destructive">{{ stats.failed_count ?? 0 }}</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-muted/50 dark:bg-muted/20">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Clock class="h-4 w-4" />
                        <span class="text-xs font-medium">Scheduled</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums">{{ stats.scheduled_count ?? 0 }}</p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Quick send -->
            <Card class="border-sky-200/50 dark:border-sky-800/50">
                <CardHeader class="border-b bg-sky-500/5 dark:bg-sky-500/10">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="h-1 w-8 rounded-full bg-sky-500" />
                        Quick send SMS
                    </CardTitle>
                </CardHeader>
                <CardContent class="pt-4">
                    <form @submit.prevent="form.post(route('marketing.sms.quick-send'), { preserveScroll: true, onSuccess: () => form.reset('message') })" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="phone" class="text-xs">Phone number</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="e.g. 8801712345678"
                                class="font-mono"
                                :disabled="!isConfigured"
                            />
                            <p v-if="form.errors.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <Label for="message" class="text-xs">Message</Label>
                                <span class="text-[10px] text-muted-foreground">{{ charCount }} chars · {{ segmentCount }} segment(s)</span>
                            </div>
                            <Textarea
                                id="message"
                                v-model="form.message"
                                rows="3"
                                placeholder="Type your message..."
                                class="resize-none"
                                maxlength="1000"
                                :disabled="!isConfigured"
                            />
                        </div>
                        <Button type="submit" class="w-full gap-2 bg-sky-600 hover:bg-sky-700 dark:bg-sky-600 dark:hover:bg-sky-700" :disabled="form.processing || !form.phone || !form.message || !isConfigured">
                            <Send class="h-4 w-4" />
                            {{ form.processing ? 'Sending…' : 'Send SMS' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Recent activity -->
            <Card>
                <CardHeader class="border-b flex flex-row items-center justify-between py-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="h-1 w-6 rounded-full bg-sky-500" />
                        Recent activity
                    </CardTitle>
                    <Button variant="ghost" size="sm" class="text-xs" as-child>
                        <Link :href="logsUrl">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <ul class="divide-y divide-border max-h-80 overflow-y-auto">
                        <li v-for="msg in recentMessages" :key="msg.id" class="px-4 py-3 hover:bg-muted/30 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-mono text-xs text-muted-foreground truncate">{{ msg.recipient_phone }}</p>
                                    <p class="text-xs truncate mt-0.5">{{ msg.message_preview || '—' }}</p>
                                    <p class="text-[10px] text-muted-foreground mt-1">{{ msg.sent_at ? new Date(msg.sent_at).toLocaleString() : (msg.failed_at ? new Date(msg.failed_at).toLocaleString() : '—') }}</p>
                                </div>
                                <Badge :variant="statusVariant(msg.status)" class="shrink-0 text-[10px]">
                                    {{ statusLabel(msg.status) }}
                                </Badge>
                            </div>
                            <p v-if="msg.status === 'failed' && msg.error_message" class="mt-1 text-[10px] text-destructive truncate">{{ msg.error_message }}</p>
                        </li>
                        <li v-if="recentMessages.length === 0" class="px-4 py-8 text-center text-sm text-muted-foreground">No recent SMS activity.</li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- Quick links -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Link :href="campaignsUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-sky-500 rounded" />
                            Campaigns
                            <Badge variant="secondary" class="text-[10px] font-normal">{{ stats.campaigns_count ?? 0 }}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Create and run SMS campaigns to leads.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-sky-600 dark:text-sky-400 group-hover:underline">View SMS campaigns →</span>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="templatesUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-sky-500 rounded" />
                            Templates
                            <Badge variant="secondary" class="text-[10px] font-normal">{{ stats.templates_count ?? 0 }}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Manage reusable SMS message templates.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-sky-600 dark:text-sky-400 group-hover:underline">View templates →</span>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="logsUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-sky-500 rounded" />
                            Logs
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Full delivery and error logs.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-sky-600 dark:text-sky-400 group-hover:underline">View logs →</span>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
