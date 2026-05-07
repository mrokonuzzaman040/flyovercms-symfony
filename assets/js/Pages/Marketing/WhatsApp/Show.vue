<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, shallowRef, onMounted, onUnmounted, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { MessageSquare, Send, Loader2, ArrowLeft, RefreshCw, Paperclip, Image, Video, FileText, Mic, Phone, VideoIcon } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const POLL_INTERVAL_MS = 10000;

const page = usePage();
const props = defineProps({
    chat: { type: Object, default: null },
    jid: { type: String, default: '' },
    messages: { type: Array, default: () => [] },
    callHistory: { type: Array, default: () => [] },
    isConfigured: { type: Boolean, default: false },
});

const chat = ref(props.chat ? { ...props.chat } : null);
const messages = shallowRef([...props.messages]);
const callHistory = shallowRef([...props.callHistory]);

watch(() => [props.chat, props.messages, props.callHistory], () => {
    chat.value = props.chat ? { ...props.chat } : null;
    messages.value = [...props.messages];
    callHistory.value = [...props.callHistory];
});

const refreshing = ref(false);
const pollInFlight = ref(false);

async function fetchPollData() {
    if (!props.jid || pollInFlight.value) return;
    pollInFlight.value = true;
    try {
        const url = new URL(route('marketing.whatsapp.chat.poll'));
        url.searchParams.set('jid', props.jid);
        const res = await fetch(url.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();
        chat.value = data.chat ?? chat.value;
        messages.value = data.messages ?? messages.value;
    } catch {
        // ignore; next poll will retry
    } finally {
        pollInFlight.value = false;
        refreshing.value = false;
    }
}

async function refresh() {
    refreshing.value = true;
    await fetchPollData();
}

const form = useForm({
    to: props.jid,
    text: '',
});

const sendMessage = () => {
    form.to = props.jid;
    form.post(route('marketing.whatsapp.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset('text'),
    });
};

const mediaForm = useForm({
    to: props.jid,
    type: 'image',
    file: null,
    caption: '',
});

const mediaInputRef = ref({ image: null, video: null, document: null, audio: null });

function triggerMediaInput(type) {
    const el = mediaInputRef.value[type];
    if (el) el.click();
}

function onMediaSelected(event, type) {
    const file = event.target.files?.[0];
    if (!file) return;
    mediaForm.to = props.jid;
    mediaForm.type = type;
    mediaForm.file = file;
    mediaForm.caption = '';
    mediaForm.post(route('marketing.whatsapp.send-media'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            mediaForm.reset();
            event.target.value = '';
        },
        onError: () => {
            event.target.value = '';
        },
    });
}

function formatCallDate(d) {
    if (!d) return '—';
    const date = new Date(d);
    const now = new Date();
    const isToday = date.toDateString() === now.toDateString();
    return isToday ? date.toLocaleTimeString() : date.toLocaleString();
}

function callTypeLabel(notes) {
    if (!notes) return 'Call';
    if (notes.toLowerCase().includes('video')) return 'Video call';
    return 'Voice call';
}

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
}, { deep: true });

let pollTimer = null;
onMounted(() => {
    if (props.jid) {
        pollTimer = setInterval(() => fetchPollData(), POLL_INTERVAL_MS);
    }
});
onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
    <Head :title="chat?.name || jid || 'WhatsApp Chat'" />

    <div class="flex flex-col h-[calc(100vh-8rem)] min-h-[400px]">
        <!-- Chat header -->
        <div class="flex items-center gap-3 rounded-lg border border-border bg-card px-4 py-3 shrink-0">
            <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                <Link :href="route('marketing.whatsapp.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/20">
                <MessageSquare class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="truncate font-semibold text-sm">{{ chat?.name || jid || 'Chat' }}</h1>
                <p class="truncate font-mono text-xs text-muted-foreground">{{ jid }}</p>
            </div>
            <Button
                variant="outline"
                size="sm"
                class="gap-1.5 shrink-0"
                :disabled="refreshing"
                @click="refresh"
            >
                <RefreshCw v-if="refreshing" class="h-4 w-4 animate-spin" />
                <RefreshCw v-else class="h-4 w-4" />
                Refresh
            </Button>
        </div>

        <!-- Calls section (voice/video) -->
        <div v-if="callHistory.length > 0" class="mt-3 rounded-lg border border-border bg-muted/30 p-3 shrink-0">
            <p class="text-xs font-medium text-muted-foreground mb-2">Calls (Voice & Video)</p>
            <p class="text-[10px] text-muted-foreground mb-2">Incoming calls are logged here. Place or receive voice/video calls from your linked WhatsApp device.</p>
            <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto">
                <div
                    v-for="call in callHistory"
                    :key="call.id"
                    class="flex items-center gap-2 rounded-md bg-background px-2 py-1.5 border border-border"
                >
                    <VideoIcon v-if="call.notes?.toLowerCase().includes('video')" class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    <Phone v-else class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    <span class="text-[10px] text-muted-foreground">{{ formatCallDate(call.call_date) }}</span>
                    <span class="text-[10px]">{{ callTypeLabel(call.notes) }}</span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <Card class="mt-4 flex flex-1 flex-col overflow-hidden min-h-0">
            <CardContent class="flex flex-1 flex-col p-0 min-h-0">
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        :class="[
                            'flex flex-col gap-0.5 max-w-[85%] rounded-2xl px-4 py-2.5 shadow-sm',
                            msg.from_me
                                ? 'ml-auto rounded-br-md bg-emerald-600 text-white dark:bg-emerald-600'
                                : 'mr-auto rounded-bl-md bg-muted dark:bg-muted/80'
                        ]"
                    >
                        <span class="text-[10px] opacity-80">{{ msg.from_me ? 'You' : (msg.lead?.full_name || 'Contact') }}</span>
                        <!-- Media -->
                        <template v-if="msg.media_url || msg.message_type === 'image' || msg.message_type === 'video' || msg.message_type === 'document' || msg.message_type === 'audio'">
                            <div v-if="msg.media_url && (msg.message_type === 'image' || msg.message_type === 'sticker')" class="my-1">
                                <a :href="msg.media_url" target="_blank" rel="noopener noreferrer" class="block rounded-lg overflow-hidden max-w-[260px]">
                                    <img :src="msg.media_url" :alt="msg.media_caption || 'Image'" class="max-h-64 w-auto object-contain" @error="($e) => $e.target.style.display = 'none'" />
                                </a>
                            </div>
                            <div v-else-if="msg.media_url && msg.message_type === 'video'" class="my-1">
                                <a :href="msg.media_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-sm underline">
                                    <Video class="h-4 w-4 shrink-0" /> Video
                                </a>
                            </div>
                            <div v-else-if="msg.media_url && (msg.message_type === 'document' || msg.message_type === 'audio')" class="my-1">
                                <a :href="msg.media_url" target="_blank" rel="noopener noreferrer" :download="msg.media_filename" class="inline-flex items-center gap-1 text-sm underline">
                                    <FileText v-if="msg.message_type === 'document'" class="h-4 w-4 shrink-0" />
                                    <Mic v-else class="h-4 w-4 shrink-0" />
                                    {{ msg.media_filename || (msg.message_type === 'audio' ? 'Audio' : 'Document') }}
                                </a>
                            </div>
                            <div v-else class="text-sm opacity-90">(Media)</div>
                            <p v-if="msg.body || msg.media_caption" class="text-sm whitespace-pre-wrap break-words mt-0.5">{{ msg.body || msg.media_caption }}</p>
                        </template>
                        <template v-else>
                            <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body || '—' }}</p>
                        </template>
                        <span class="text-[10px] opacity-70 self-end">{{ msg.created_at }}</span>
                    </div>
                    <div v-if="messages.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <MessageSquare class="h-12 w-12 text-muted-foreground/50 mb-2" />
                        <p class="text-sm text-muted-foreground">No messages in this chat yet.</p>
                        <p class="text-xs text-muted-foreground mt-1">Send a message or media below to start.</p>
                    </div>
                </div>

                <!-- Reply box: text + attach media -->
                <div v-if="isConfigured && jid" class="border-t border-border bg-muted/30 dark:bg-muted/20 p-3 shrink-0">
                    <input
                        ref="mediaInputRef.image"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="(e) => onMediaSelected(e, 'image')"
                    />
                    <input
                        ref="mediaInputRef.video"
                        type="file"
                        accept="video/*"
                        class="hidden"
                        @change="(e) => onMediaSelected(e, 'video')"
                    />
                    <input
                        ref="mediaInputRef.document"
                        type="file"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.txt"
                        class="hidden"
                        @change="(e) => onMediaSelected(e, 'document')"
                    />
                    <input
                        ref="mediaInputRef.audio"
                        type="file"
                        accept="audio/*"
                        class="hidden"
                        @change="(e) => onMediaSelected(e, 'audio')"
                    />
                    <div class="flex gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="h-11 w-11 shrink-0 rounded-full"
                                    :disabled="mediaForm.processing"
                                >
                                    <Paperclip class="h-5 w-5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-48">
                                <DropdownMenuItem @click="triggerMediaInput('image')" class="gap-2 cursor-pointer">
                                    <Image class="h-4 w-4" /> Image
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="triggerMediaInput('video')" class="gap-2 cursor-pointer">
                                    <Video class="h-4 w-4" /> Video
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="triggerMediaInput('document')" class="gap-2 cursor-pointer">
                                    <FileText class="h-4 w-4" /> Document
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="triggerMediaInput('audio')" class="gap-2 cursor-pointer">
                                    <Mic class="h-4 w-4" /> Audio
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Textarea
                            v-model="form.text"
                            placeholder="Type a message..."
                            rows="2"
                            class="min-h-[44px] resize-none flex-1"
                            @keydown.enter.exact.prevent="sendMessage"
                        />
                        <Button
                            :disabled="form.processing || !form.text?.trim()"
                            size="icon"
                            class="h-11 w-11 shrink-0 rounded-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700"
                            @click="sendMessage"
                        >
                            <Loader2 v-if="form.processing" class="h-5 w-5 animate-spin" />
                            <Send v-else class="h-5 w-5" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
