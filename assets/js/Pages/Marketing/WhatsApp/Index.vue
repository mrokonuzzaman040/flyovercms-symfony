<script setup>
import { ref, shallowRef, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Badge } from '@/Components/ui/badge';
import { MessageSquare, Phone, Send, Loader2, Users, Calendar, AlertCircle, Copy, Check, History, RefreshCw } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const POLL_INTERVAL_MS = 15000;

const props = defineProps({
    chats: { type: Array, default: () => [] },
    messages: { type: Array, default: () => [] },
    recentEvents: { type: Array, default: () => [] },
    sessionStatus: { type: Object, default: null },
    qrcodeEvent: { type: Object, default: null },
    isConfigured: { type: Boolean, default: false },
    webhookUrl: { type: String, default: '' },
});

const chats = shallowRef([...props.chats]);
const messages = shallowRef([...props.messages]);
const recentEvents = shallowRef([...props.recentEvents]);
const sessionStatus = ref(props.sessionStatus ? { ...props.sessionStatus } : null);
const qrcodeEvent = ref(props.qrcodeEvent ? { ...props.qrcodeEvent } : null);

watch(() => [props.chats, props.messages, props.recentEvents, props.sessionStatus, props.qrcodeEvent], () => {
    chats.value = [...props.chats];
    messages.value = [...props.messages];
    recentEvents.value = [...props.recentEvents];
    sessionStatus.value = props.sessionStatus ? { ...props.sessionStatus } : null;
    qrcodeEvent.value = props.qrcodeEvent ? { ...props.qrcodeEvent } : null;
});

const refreshing = ref(false);
const pollInFlight = ref(false);

async function fetchPollData() {
    if (pollInFlight.value) return;
    pollInFlight.value = true;
    try {
        const res = await fetch(route('marketing.whatsapp.poll'), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();
        chats.value = data.chats ?? chats.value;
        messages.value = data.messages ?? messages.value;
        recentEvents.value = data.recentEvents ?? recentEvents.value;
        sessionStatus.value = data.sessionStatus ?? sessionStatus.value;
        qrcodeEvent.value = data.qrcodeEvent ?? qrcodeEvent.value;
    } catch {
        // ignore network errors; next poll will retry
    } finally {
        pollInFlight.value = false;
        refreshing.value = false;
    }
}

async function refresh() {
    refreshing.value = true;
    await fetchPollData();
}

const copied = ref(false);
const copyWebhookUrl = async () => {
    if (!props.webhookUrl) return;
    try {
        await navigator.clipboard.writeText(props.webhookUrl);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        copied.value = false;
    }
};

const form = useForm({
    to: '',
    text: '',
});

const sendMessage = () => {
    form.post(route('marketing.whatsapp.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset('text'),
    });
};

const openChat = (jid) => {
    router.visit(route('marketing.whatsapp.show', { jid }));
};

const sessionLabel = computed(() => {
    if (!sessionStatus.value?.payload) return null;
    const d = sessionStatus.value.payload?.data ?? sessionStatus.value.payload;
    return d?.status ?? d?.state ?? JSON.stringify(d);
});

const qrcodeImageSrc = computed(() => {
    const payload = qrcodeEvent.value?.payload;
    const data = payload?.data ?? payload;
    const qr = data?.qr ?? data?.qrcode;
    if (!qr || typeof qr !== 'string') return null;
    if (qr.startsWith('data:image') || qr.startsWith('http')) return qr;
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qr)}`;
});

let pollTimer = null;
onMounted(() => {
    pollTimer = setInterval(() => fetchPollData(), POLL_INTERVAL_MS);
});
onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
    <Head title="WhatsApp Messaging" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20">
                    <MessageSquare class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">WhatsApp Messaging</h1>
                    <p class="text-xs text-muted-foreground">Chats and messages via WasenderAPI. Send to any number or JID.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <Button
                    variant="outline"
                    size="sm"
                    class="gap-1.5"
                    :disabled="refreshing"
                    @click="refresh"
                >
                    <RefreshCw v-if="refreshing" class="h-4 w-4 animate-spin" />
                    <RefreshCw v-else class="h-4 w-4" />
                    Refresh
                </Button>
                <Button variant="outline" size="sm" class="gap-1.5" as-child>
                    <Link :href="route('marketing.whatsapp.history')">
                        <History class="h-4 w-4" />
                        Message history
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200 flex items-center gap-2">
            <Check class="h-4 w-4 shrink-0" />
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive flex items-center gap-2">
            <AlertCircle class="h-4 w-4 shrink-0" />
            {{ $page.props.flash.error }}
        </div>

        <!-- Config alert -->
        <div v-if="!isConfigured" class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30 p-4 flex flex-wrap items-center gap-3">
            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0" />
            <p class="text-sm text-amber-800 dark:text-amber-200 flex-1 min-w-0">Configure WHATSAPP_API_KEY in .env and set the webhook URL in your Wasender dashboard to receive messages and events.</p>
            <Button variant="outline" size="sm" as-child>
                <Link :href="route('admin.settings.whatsapp')">Settings</Link>
            </Button>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Send message -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                        Send message
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 pt-4">
                    <div class="space-y-2">
                        <Label for="to" class="text-xs">To (phone or JID)</Label>
                        <Input id="to" v-model="form.to" placeholder="8801700000000 or 246278273413215@lid" class="font-mono text-sm" />
                        <p v-if="form.errors.to" class="text-xs text-destructive">{{ form.errors.to }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="text" class="text-xs">Message</Label>
                        <Textarea id="text" v-model="form.text" rows="3" placeholder="Type your message..." class="resize-none" />
                        <p v-if="form.errors.text" class="text-xs text-destructive">{{ form.errors.text }}</p>
                    </div>
                    <Button :disabled="form.processing || !form.to || !form.text" class="gap-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700" @click="sendMessage">
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Send v-else class="h-4 w-4" />
                        Send
                    </Button>
                </CardContent>
            </Card>

            <!-- Session status & QR -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                        Session status
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 pt-4">
                    <p v-if="sessionLabel" class="text-sm font-mono text-muted-foreground rounded bg-muted/50 px-2 py-1">{{ sessionLabel }}</p>
                    <p v-else class="text-sm text-muted-foreground">No session.status webhook yet. Add the webhook URL below in Wasender and link your device.</p>
                    <template v-if="qrcodeEvent">
                        <p class="text-xs text-muted-foreground">Scan with WhatsApp to link (QR at {{ qrcodeEvent.created_at }})</p>
                        <img
                            v-if="qrcodeImageSrc"
                            :src="qrcodeImageSrc"
                            alt="WhatsApp pairing QR code"
                            class="rounded-lg border border-border w-48 h-48 object-contain bg-white dark:bg-muted"
                        />
                    </template>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Chats -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                        Chats
                        <Badge variant="secondary" class="text-[10px] font-normal">{{ chats.length }}</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <ul class="divide-y divide-border max-h-64 overflow-y-auto">
                        <li v-for="chat in chats" :key="chat.jid">
                            <button
                                type="button"
                                class="w-full text-left px-3 py-2.5 text-sm hover:bg-muted/50 dark:hover:bg-muted/30 flex items-center justify-between gap-2 transition-colors"
                                @click="openChat(chat.jid)"
                            >
                                <span class="truncate font-medium">{{ chat.name || chat.jid }}</span>
                                <Badge variant="outline" class="text-[10px] shrink-0 capitalize">{{ chat.chat_type }}</Badge>
                            </button>
                        </li>
                        <li v-if="chats.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">No chats yet. Messages appear after webhook events.</li>
                    </ul>
                </CardContent>
            </Card>

            <!-- Recent messages -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                        Recent messages
                        <Badge variant="secondary" class="text-[10px] font-normal">{{ messages.length }}</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <ul class="divide-y divide-border max-h-64 overflow-y-auto">
                        <li v-for="msg in messages.slice(0, 30)" :key="msg.id" class="px-3 py-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-mono text-xs text-muted-foreground truncate">{{ msg.remote_jid }}</span>
                                <Badge v-if="msg.from_me" variant="secondary" class="text-[10px] shrink-0">Out</Badge>
                                <Badge v-else variant="outline" class="text-[10px] shrink-0">In</Badge>
                            </div>
                            <p class="mt-1 text-sm truncate">{{ msg.body || '(media)' }}</p>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-[10px] text-muted-foreground">{{ msg.created_at }}</span>
                                <Button v-if="msg.remote_jid" variant="ghost" size="sm" class="h-6 text-xs -mr-1" @click="openChat(msg.remote_jid)">
                                    Open chat
                                </Button>
                            </div>
                        </li>
                        <li v-if="messages.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">No messages yet.</li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- Webhook events -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                    Recent webhook events
                    <Badge variant="secondary" class="text-[10px] font-normal">{{ recentEvents.length }}</Badge>
                </CardTitle>
            </CardHeader>
            <CardContent class="pt-4">
                <p class="text-xs text-muted-foreground mb-3">messages.received, message.sent, session.status, qrcode.updated, chats.upsert, etc.</p>
                <ul class="space-y-1.5 max-h-48 overflow-y-auto font-mono text-xs">
                    <li v-for="e in recentEvents" :key="e.id" class="flex gap-2 items-center py-0.5">
                        <span class="text-muted-foreground shrink-0">{{ e.created_at }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ e.event }}</span>
                    </li>
                    <li v-if="recentEvents.length === 0" class="text-muted-foreground py-2">No events yet.</li>
                </ul>
                <div v-if="webhookUrl" class="mt-4 rounded-lg border border-border bg-muted/30 dark:bg-muted/20 p-3 space-y-2">
                    <p class="text-xs font-medium text-foreground">To receive events</p>
                    <p class="text-xs text-muted-foreground">In Wasender dashboard, add this webhook URL and subscribe to the events above.</p>
                    <div class="flex gap-2 items-center">
                        <code class="flex-1 min-w-0 truncate rounded bg-background dark:bg-muted px-2 py-1.5 text-xs border border-border">{{ webhookUrl }}</code>
                        <Button type="button" variant="outline" size="sm" class="shrink-0 gap-1" @click="copyWebhookUrl">
                            <Check v-if="copied" class="h-4 w-4 text-emerald-600" />
                            <Copy v-else class="h-4 w-4" />
                            {{ copied ? 'Copied' : 'Copy' }}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
