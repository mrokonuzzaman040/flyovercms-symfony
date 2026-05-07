<script setup>
import { ref, shallowRef, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { MessageSquare, Plus, Users } from 'lucide-vue-next';
import MessagingComposer from '@/Components/Messaging/Composer.vue';
import MessageBubble from '@/Components/Messaging/MessageBubble.vue';

defineOptions({ layout: DashboardLayout, inheritAttrs: false });

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    usersForNewChat: { type: Array, default: () => [] },
    activeConversationId: { type: Number, default: null },
    activeConversation: { type: Object, default: null },
    messages: { type: Object, default: () => ({ data: [] }) },
});

const page = usePage();
const localConversations = ref([...(props.conversations ?? [])]);
const localMessages = shallowRef([...(props.messages?.data ?? [])]);
const messagesCurrentPage = ref(props.messages?.current_page ?? 1);
const messagesLastPage = ref(props.messages?.last_page ?? 1);
const lastSeenMessageId = ref(localMessages.value.at(-1)?.id ?? null);
const loadingOlder = ref(false);
const showNewChatModal = ref(false);
const showNewGroupModal = ref(false);
const showForwardModal = ref(false);
const messageToForward = ref(null);
const newGroupName = ref('');
const newGroupUserIds = ref([]);

const activeId = computed(() => props.activeConversationId);
const hasMoreOlder = computed(() => messagesCurrentPage.value < messagesLastPage.value);

watch(
    () => props.conversations,
    (next) => {
        localConversations.value = [...(next ?? [])];
    },
    { immediate: true }
);

watch(
    () => [props.messages?.data, props.activeConversationId],
    ([data, convId]) => {
        if (data && convId) {
            localMessages.value = [...(data ?? [])];
            messagesCurrentPage.value = props.messages?.current_page ?? 1;
            messagesLastPage.value = props.messages?.last_page ?? 1;
            lastSeenMessageId.value = localMessages.value.at(-1)?.id ?? null;
        }
    },
    { immediate: true }
);

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function openConversation(conv) {
    router.visit(route('messaging.conversations.show', conv.id));
}

const forwardableConversations = computed(() =>
    localConversations.value.filter((c) => c.id !== props.activeConversationId)
);

function openForwardModal(message) {
    messageToForward.value = message;
    showForwardModal.value = true;
}

function forwardTo(convId) {
    if (!messageToForward.value) return;
    router.post(route('messaging.messages.forward', messageToForward.value.id), {
        target_conversation_id: convId,
    }, {
        onSuccess: () => {
            showForwardModal.value = false;
            messageToForward.value = null;
        },
    });
}

function markRead(conv) {
    if (conv.unread_count > 0) {
        conv.unread_count = 0;
        router.patch(route('messaging.conversations.read', conv.id), {}, { preserveScroll: true });
    }
}

function sortConversationsByLastMessage() {
    localConversations.value = [...localConversations.value].sort((a, b) => {
        const aIso = a?.last_message?.created_at;
        const bIso = b?.last_message?.created_at;
        const aTs = aIso ? new Date(aIso).getTime() : 0;
        const bTs = bIso ? new Date(bIso).getTime() : 0;
        return bTs - aTs;
    });
}

function updateConversationListFromMessage(data, shouldSort = false) {
    if (!data?.conversation_id) return;

    const conv = localConversations.value.find((c) => c.id === data.conversation_id);
    if (!conv) return;

    const previousLastMessageId = conv?.last_message?.id ?? null;
    const attachmentsCount = data.attachments_count ?? data.attachments?.length ?? 0;
    const hasAttachments = Number(attachmentsCount) > 0;

    conv.last_message = {
        id: data.id ?? null,
        body: data.body ? String(data.body).slice(0, 80) : null,
        created_at: data.created_at ?? null,
        user_id: data.user_id ?? null,
        has_attachments: hasAttachments,
    };

    // If the user is currently in this thread, unread should be 0.
    // Otherwise, increment unread for this conversation row.
    if (data.conversation_id === props.activeConversationId) {
        conv.unread_count = 0;
    } else {
        // Avoid double-incrementing unread count if we receive a duplicate event.
        if (previousLastMessageId !== data.id) {
            conv.unread_count = (conv.unread_count ?? 0) + 1;
        }
    }

    if (shouldSort) sortConversationsByLastMessage();
}

function appendMessage(payload) {
    const data = payload?.message ?? payload;
    if (!data) return;

    updateConversationListFromMessage(data, true);

    if (data.conversation_id !== props.activeConversationId) return;
    const exists = localMessages.value.some((m) => m.id === data.id);
    if (!exists) {
        localMessages.value = [...localMessages.value, {
            id: data.id,
            conversation_id: data.conversation_id,
            user_id: data.user_id,
            body: data.body,
            created_at: data.created_at,
            sender: data.sender,
            attachments: data.attachments ?? [],
            user: data.sender ? { id: data.sender.id, name: data.sender.name, avatar: data.sender.avatar } : null,
        }];
        lastSeenMessageId.value = data.id;
        scrollToLastMessage();
    }
}

let echoChannel = null;
let pollInterval = null;
let pollingInFlight = false;

function subscribeToConversation(conversationId) {
    if (!conversationId) return;
    if (window.Echo) {
        if (echoChannel) {
            echoChannel.stopListening('.message.sent');
            window.Echo.leave(`conversation.${conversationId}`);
        }
        echoChannel = window.Echo.private(`conversation.${conversationId}`);
        echoChannel.listen('.message.sent', (e) => appendMessage(e));
    } else {
        pollInterval = setInterval(() => {
            if (pollingInFlight) return;
            if (document.visibilityState !== 'visible' || !props.activeConversationId) return;

            pollingInFlight = true;
            window.axios
                .get(route('messaging.conversations.messages', { conversation: props.activeConversationId }), {
                    params: { page: 1 },
                })
                .then((response) => {
                    const items = response?.data?.data ?? [];
                    if (items.length === 0) return;

                    const newest = items.at(-1);
                    if (!newest?.id) return;

                    if (lastSeenMessageId.value && newest.id === lastSeenMessageId.value) return;

                    const thresholdId = lastSeenMessageId.value ?? 0;
                    const candidates = items.filter((m) => m?.id > thresholdId);
                    if (candidates.length === 0) return;

                    localMessages.value = [...localMessages.value, ...candidates];
                    candidates.forEach((m) => updateConversationListFromMessage(m, false));
                    sortConversationsByLastMessage();
                    lastSeenMessageId.value = newest.id;
                    scrollToLastMessage();
                })
                .catch(() => {})
                .finally(() => {
                    pollingInFlight = false;
                });
        }, 4000);
    }
}

function unsubscribe(conversationId) {
    const id = conversationId ?? props.activeConversationId;
    if (echoChannel && window.Echo && id) {
        echoChannel.stopListening('.message.sent');
        window.Echo.leave(`conversation.${id}`);
        echoChannel = null;
    }
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

onMounted(() => {
    if (props.activeConversationId) {
        subscribeToConversation(props.activeConversationId);
    }
});

onUnmounted(() => {
    unsubscribe(props.activeConversationId);
});

watch(activeId, (newId, oldId) => {
    unsubscribe(oldId);
    if (!newId) return;

    subscribeToConversation(newId);

    // If this conversation is open, mark it as read so the unread badge is correct.
    const conv = localConversations.value.find((c) => c.id === newId);
    if (conv?.unread_count > 0) {
        markRead(conv);
    }
}, { immediate: true });

const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const messageListRef = ref(null);

function scrollToLastMessage() {
    nextTick(() => {
        const el = messageListRef.value;
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    });
}

watch(
    () => [props.activeConversationId, localMessages.value.length],
    () => {
        if (props.activeConversationId && localMessages.value.length > 0) {
            scrollToLastMessage();
        }
    },
    { immediate: true }
);

function onMessageListScroll() {
    const el = messageListRef.value;
    if (!el || !props.activeConversationId || loadingOlder.value || !hasMoreOlder.value) return;
    if (el.scrollTop > 80) return;
    loadMoreOlder();
}

async function loadMoreOlder() {
    if (!props.activeConversationId || loadingOlder.value || !hasMoreOlder.value) return;
    const el = messageListRef.value;
    const nextPage = messagesCurrentPage.value + 1;
    loadingOlder.value = true;
    const oldScrollHeight = el?.scrollHeight ?? 0;
    try {
        const { data } = await window.axios.get(
            route('messaging.conversations.messages', { conversation: props.activeConversationId }),
            { params: { page: nextPage } }
        );
        if (data.data?.length && data.current_page === nextPage) {
            localMessages.value = [...(data.data ?? []), ...localMessages.value];
            messagesCurrentPage.value = data.current_page;
            messagesLastPage.value = data.last_page;
            nextTick(() => {
                if (el) {
                    el.scrollTop = el.scrollHeight - oldScrollHeight;
                }
            });
        }
    } finally {
        loadingOlder.value = false;
    }
}
</script>

<template>
    <Head title="Messages" />

    <div class="flex flex-col sm:flex-row gap-0 rounded-lg border bg-card overflow-hidden min-h-0 w-full"
        style="height: calc(100vh - 8rem); max-height: calc(100vh - 8rem);">
        <!-- Conversation list -->
        <aside class="w-full sm:w-72 md:w-80 shrink-0 border-r flex flex-col bg-muted/30 min-h-0 sm:max-h-[calc(100vh-8rem)]">
            <div class="p-3 border-b flex items-center justify-between shrink-0">
                <h2 class="font-semibold text-sm">Messages</h2>
                <div class="flex gap-1">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="showNewChatModal = true" title="New chat">
                        <MessageSquare class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="showNewGroupModal = true" title="New group">
                        <Users class="h-4 w-4" />
                    </Button>
                </div>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto">
                <div class="p-2 space-y-0.5">
                    <Link
                        v-for="conv in localConversations"
                        :key="conv.id"
                        :href="route('messaging.conversations.show', conv.id)"
                        class="flex items-center gap-3 rounded-md p-2 hover:bg-muted/80 transition-colors"
                        :class="{ 'bg-muted': activeConversationId === conv.id }"
                        @click="markRead(conv)"
                    >
                        <Avatar class="h-10 w-10 shrink-0">
                            <AvatarImage v-if="conv.participants?.[0]?.avatar" :src="conv.participants[0].avatar" />
                            <AvatarFallback class="text-xs">
                                {{ (conv.display_name || conv.name || '?').slice(0, 2).toUpperCase() }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate">{{ conv.display_name || conv.name || 'Chat' }}</p>
                            <p class="text-xs text-muted-foreground truncate">
                                {{ conv.last_message?.body || (conv.last_message?.has_attachments ? 'Attachment' : 'No messages yet') }}
                            </p>
                        </div>
                        <Badge v-if="conv.unread_count > 0" variant="default" class="shrink-0 h-5 min-w-5 text-xs">
                            {{ conv.unread_count }}
                        </Badge>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Thread + composer -->
        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
            <template v-if="activeConversation">
                <div class="p-3 border-b flex items-center gap-2 shrink-0">
                    <Avatar class="h-8 w-8">
                        <AvatarImage v-if="activeConversation.participants?.[0]?.avatar" :src="activeConversation.participants[0].avatar" />
                        <AvatarFallback class="text-xs">
                            {{ (activeConversation.display_name || activeConversation.name || '?').slice(0, 2).toUpperCase() }}
                        </AvatarFallback>
                    </Avatar>
                    <span class="font-medium text-sm truncate">{{ activeConversation.display_name || activeConversation.name || 'Chat' }}</span>
                </div>
                <!-- Scrollable message list: flex-1 + min-h-0 + overflow-y-auto so it scrolls inside flex -->
                <div
                    ref="messageListRef"
                    class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4"
                    @scroll="onMessageListScroll"
                >
                    <div v-if="loadingOlder" class="text-center py-2 text-muted-foreground text-sm">
                        Loading older messages…
                    </div>
                    <div class="space-y-3">
                        <MessageBubble
                            v-for="msg in localMessages"
                            :key="msg.id"
                            :message="msg"
                            :is-own="msg.user_id === currentUserId"
                            @forward="openForwardModal"
                        />
                    </div>
                </div>
                <div class="p-3 border-t shrink-0">
                    <MessagingComposer :conversation-id="activeConversation.id" />
                </div>
            </template>
            <div v-else class="flex-1 flex items-center justify-center text-muted-foreground min-h-48">
                <div class="text-center px-4">
                    <MessageSquare class="h-12 w-12 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">Select a conversation or start a new chat</p>
                    <Button variant="outline" class="mt-3" @click="showNewChatModal = true">
                        <Plus class="h-4 w-4 mr-1" />
                        New chat
                    </Button>
                </div>
            </div>
        </main>
    </div>

    <!-- New chat modal: simple list of users -->
    <Teleport to="body">
        <div
            v-if="showNewChatModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showNewChatModal = false"
        >
            <Card class="w-full max-w-md mx-4">
                <CardContent class="p-4">
                    <h3 class="font-semibold mb-3">New chat</h3>
                    <div class="max-h-80 overflow-auto space-y-1">
                        <button
                            v-for="u in usersForNewChat"
                            :key="u.id"
                            type="button"
                            class="flex w-full items-center gap-2 p-2 rounded hover:bg-muted text-left"
                            @click="router.post(route('messaging.conversations.store'), { type: 'direct', other_user_id: u.id }); showNewChatModal = false"
                        >
                            <Avatar class="h-8 w-8">
                                <AvatarFallback class="text-xs">{{ (u.name || '?').slice(0, 2).toUpperCase() }}</AvatarFallback>
                            </Avatar>
                            <span class="text-sm">{{ u.name }}</span>
                        </button>
                    </div>
                    <Button variant="outline" class="mt-3 w-full" @click="showNewChatModal = false">Cancel</Button>
                </CardContent>
            </Card>
        </div>
    </Teleport>

    <!-- Forward modal -->
    <Teleport to="body">
        <div
            v-if="showForwardModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showForwardModal = false; messageToForward = null"
        >
            <Card class="w-full max-w-md mx-4">
                <CardContent class="p-4">
                    <h3 class="font-semibold mb-3">Forward to</h3>
                    <div v-if="forwardableConversations.length === 0" class="text-sm text-muted-foreground py-4">
                        No other conversations to forward to.
                    </div>
                    <div v-else class="max-h-80 overflow-auto space-y-1">
                        <button
                            v-for="conv in forwardableConversations"
                            :key="conv.id"
                            type="button"
                            class="flex w-full items-center gap-2 p-2 rounded hover:bg-muted text-left"
                            @click="forwardTo(conv.id)"
                        >
                            <Avatar class="h-8 w-8 shrink-0">
                                <AvatarImage v-if="conv.participants?.[0]?.avatar" :src="conv.participants[0].avatar" />
                                <AvatarFallback class="text-xs">
                                    {{ (conv.display_name || conv.name || '?').slice(0, 2).toUpperCase() }}
                                </AvatarFallback>
                            </Avatar>
                            <span class="text-sm truncate">{{ conv.display_name || conv.name || 'Chat' }}</span>
                        </button>
                    </div>
                    <Button variant="outline" class="mt-3 w-full" @click="showForwardModal = false; messageToForward = null">
                        Cancel
                    </Button>
                </CardContent>
            </Card>
        </div>
    </Teleport>

    <!-- New group modal: name + user multi-select -->
    <Teleport to="body">
        <div
            v-if="showNewGroupModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showNewGroupModal = false"
        >
            <Card class="w-full max-w-md mx-4">
                <CardContent class="p-4">
                    <h3 class="font-semibold mb-3">New group</h3>
                    <input
                        v-model="newGroupName"
                        type="text"
                        placeholder="Group name"
                        class="w-full rounded border px-3 py-2 text-sm mb-3"
                    />
                    <p class="text-xs text-muted-foreground mb-2">Select members</p>
                    <div class="max-h-48 overflow-auto space-y-1 mb-3">
                        <label
                            v-for="u in usersForNewChat"
                            :key="u.id"
                            class="flex items-center gap-2 p-2 rounded hover:bg-muted cursor-pointer"
                        >
                            <input v-model="newGroupUserIds" type="checkbox" :value="u.id" class="rounded" />
                            <span class="text-sm">{{ u.name }}</span>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            class="flex-1"
                            @click="router.post(route('messaging.conversations.store'), { type: 'group', name: newGroupName, user_ids: newGroupUserIds }); showNewGroupModal = false; newGroupName = ''; newGroupUserIds = [];"
                        >
                            Create
                        </Button>
                        <Button variant="outline" @click="showNewGroupModal = false; newGroupName = ''; newGroupUserIds = []">Cancel</Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </Teleport>
</template>
