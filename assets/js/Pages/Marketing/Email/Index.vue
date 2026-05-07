<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Mail, Send, FileText, List, Plus, TrendingUp, AlertCircle, CheckCircle, Clock, Zap } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recentMessages: { type: Array, default: () => [] },
    isConfigured: { type: Boolean, default: false },
});

const form = useForm({
    email: '',
    subject: '',
    message: '',
    lead_id: null,
});

const campaignsUrl = route('marketing.email.campaigns.index');
const campaignsCreateUrl = route('marketing.email.campaigns.create');
const templatesUrl = route('marketing.templates.index', { type: 'email' });
const logsUrl = route('marketing.logs.index', { channel: 'email' });

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
    <Head title="Email Marketing" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/25 dark:shadow-violet-600/20">
                    <Mail class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Email Marketing</h1>
                    <p class="text-xs text-muted-foreground">Send emails, run campaigns, and track delivery in real time.</p>
                </div>
            </div>
            <Button size="sm" class="gap-1.5 shrink-0" as-child>
                <Link :href="campaignsCreateUrl">
                    <Plus class="h-3.5 w-3.5" />
                    New campaign
                </Link>
            </Button>
        </div>

        <!-- Connection status -->
        <div v-if="!isConfigured" class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30 p-4 flex flex-wrap items-center gap-3">
            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0" />
            <p class="text-sm text-amber-800 dark:text-amber-200 flex-1 min-w-0">Mail is set to log driver. Configure SMTP in .env to send real emails.</p>
        </div>
        <div v-else class="rounded-xl border border-emerald-200 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-950/20 p-3 flex items-center gap-2">
            <CheckCircle class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
            <span class="text-sm text-emerald-800 dark:text-emerald-200">Mail configured · Quick send and campaigns are available.</span>
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

        <!-- Stats row -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-violet-500/10 to-purple-500/5 dark:from-violet-500/20 dark:to-purple-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Zap class="h-4 w-4" />
                        <span class="text-xs font-medium">Today</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-violet-700 dark:text-violet-300">{{ stats.sent_today ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">sent</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-violet-500/10 to-purple-500/5 dark:from-violet-500/20 dark:to-purple-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <TrendingUp class="h-4 w-4" />
                        <span class="text-xs font-medium">This week</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-violet-700 dark:text-violet-300">{{ stats.sent_week ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">sent</p>
                </CardContent>
            </Card>
            <Card class="overflow-hidden border-0 bg-gradient-to-br from-violet-500/10 to-purple-500/5 dark:from-violet-500/20 dark:to-purple-500/10">
                <CardContent class="p-4">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Send class="h-4 w-4" />
                        <span class="text-xs font-medium">This month</span>
                    </div>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-violet-700 dark:text-violet-300">{{ stats.sent_month ?? 0 }}</p>
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
            <Card class="border-violet-200/50 dark:border-violet-800/50">
                <CardHeader class="border-b bg-violet-500/5 dark:bg-violet-500/10">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="h-1 w-8 rounded-full bg-violet-500" />
                        Quick send email
                    </CardTitle>
                </CardHeader>
                <CardContent class="pt-4">
                    <form @submit.prevent="form.post(route('marketing.email.quick-send'), { preserveScroll: true, onSuccess: () => form.reset('message') })" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email" class="text-xs">Recipient email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="recipient@example.com"
                                :disabled="!isConfigured"
                            />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="subject" class="text-xs">Subject</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                placeholder="Email subject"
                                :disabled="!isConfigured"
                            />
                            <p v-if="form.errors.subject" class="text-xs text-destructive">{{ form.errors.subject }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="message" class="text-xs">Message</Label>
                            <Textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                placeholder="Type your message..."
                                class="resize-none"
                                :disabled="!isConfigured"
                            />
                        </div>
                        <Button type="submit" class="w-full gap-2 bg-violet-600 hover:bg-violet-700 dark:bg-violet-600 dark:hover:bg-violet-700" :disabled="form.processing || !form.email || !form.subject || !form.message || !isConfigured">
                            <Send class="h-4 w-4" />
                            {{ form.processing ? 'Sending…' : 'Send email' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Recent activity -->
            <Card>
                <CardHeader class="border-b flex flex-row items-center justify-between py-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="h-1 w-6 rounded-full bg-violet-500" />
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
                                    <p class="text-xs text-muted-foreground truncate">{{ msg.recipient_email }}</p>
                                    <p class="text-xs truncate mt-0.5">{{ msg.message_preview || '—' }}</p>
                                    <p class="text-[10px] text-muted-foreground mt-1">{{ msg.sent_at ? new Date(msg.sent_at).toLocaleString() : (msg.failed_at ? new Date(msg.failed_at).toLocaleString() : '—') }}</p>
                                </div>
                                <Badge :variant="statusVariant(msg.status)" class="shrink-0 text-[10px]">
                                    {{ statusLabel(msg.status) }}
                                </Badge>
                            </div>
                            <p v-if="msg.status === 'failed' && msg.error_message" class="mt-1 text-[10px] text-destructive truncate">{{ msg.error_message }}</p>
                        </li>
                        <li v-if="recentMessages.length === 0" class="px-4 py-8 text-center text-sm text-muted-foreground">No recent email activity.</li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- Quick links -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Link :href="campaignsUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-violet-500 rounded" />
                            Campaigns
                            <Badge variant="secondary" class="text-[10px] font-normal">{{ stats.campaigns_count ?? 0 }}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Create and run email campaigns to leads.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-violet-600 dark:text-violet-400 group-hover:underline">View email campaigns →</span>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="templatesUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-violet-500 rounded" />
                            Templates
                            <Badge variant="secondary" class="text-[10px] font-normal">{{ stats.templates_count ?? 0 }}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Manage reusable email templates.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-violet-600 dark:text-violet-400 group-hover:underline">View templates →</span>
                    </CardContent>
                </Card>
            </Link>
            <Link :href="logsUrl" class="group block">
                <Card class="h-full transition-all hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800">
                    <CardHeader class="border-b p-3">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-violet-500 rounded" />
                            Logs
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-3">
                        <p class="text-xs text-muted-foreground">Full delivery and error logs.</p>
                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-violet-600 dark:text-violet-400 group-hover:underline">View logs →</span>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
