<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { History, ArrowLeft, MessageSquare, ArrowUpRight, ArrowDownLeft } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    messages: { type: Object, required: true },
    chats: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const selectedJid = ref(props.filters.jid ?? '');
const selectedDirection = ref(props.filters.direction ?? 'all');

watch(() => props.filters, (f) => {
    selectedJid.value = f.jid ?? '';
    selectedDirection.value = f.direction ?? 'all';
}, { deep: true });

const applyFilters = () => {
    const query = {};
    if (selectedJid.value) query.jid = selectedJid.value;
    if (selectedDirection.value && selectedDirection.value !== 'all') query.direction = selectedDirection.value;
    router.get(route('marketing.whatsapp.history'), query, { preserveState: true });
};

const openChat = (jid) => {
    router.visit(route('marketing.whatsapp.show', { jid }));
};

const chatName = (jid) => {
    const chat = props.chats.find((c) => c.jid === jid);
    return chat?.name || jid;
};

const messagePreview = (body, max = 80) => {
    if (!body) return '(media or other)';
    return body.length > max ? body.slice(0, max) + '…' : body;
};
</script>

<template>
    <Head title="WhatsApp Message History" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" class="shrink-0" as-child>
                    <Link :href="route('marketing.whatsapp.index')">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20">
                    <History class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">Message History</h1>
                    <p class="text-xs text-muted-foreground">Sent and received WhatsApp messages via WasenderAPI</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                    Filters
                </CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap items-end gap-4 pt-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-muted-foreground">Chat</label>
                    <Select v-model="selectedJid">
                        <SelectTrigger class="w-[220px] h-9 text-sm">
                            <SelectValue placeholder="All chats" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All chats</SelectItem>
                            <SelectItem v-for="c in chats" :key="c.jid" :value="c.jid">
                                {{ c.name || c.jid }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-muted-foreground">Direction</label>
                    <Select v-model="selectedDirection">
                        <SelectTrigger class="w-[130px] h-9 text-sm">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="sent">Sent</SelectItem>
                            <SelectItem value="received">Received</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <Button size="sm" class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700" @click="applyFilters">Apply</Button>
            </CardContent>
        </Card>

        <!-- Messages table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-emerald-500 rounded" />
                    Messages
                    <span v-if="messages.total !== undefined" class="font-normal text-muted-foreground">({{ messages.total }})</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 dark:bg-muted/30">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground w-40">Date</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground w-52">Chat</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground w-28">Direction</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Message</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground w-24"> </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="msg in messages.data" :key="msg.id" class="border-b border-border/50 hover:bg-muted/30 dark:hover:bg-muted/20 transition-colors">
                                <td class="px-3 py-2.5 text-muted-foreground whitespace-nowrap">{{ msg.created_at }}</td>
                                <td class="px-3 py-2.5 font-mono text-xs truncate max-w-48" :title="msg.remote_jid">
                                    {{ chatName(msg.remote_jid) }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <Badge v-if="msg.from_me" variant="secondary" class="text-[10px] gap-0.5">
                                        <ArrowUpRight class="h-3 w-3" />
                                        Sent
                                    </Badge>
                                    <Badge v-else variant="outline" class="text-[10px] gap-0.5">
                                        <ArrowDownLeft class="h-3 w-3" />
                                        Received
                                    </Badge>
                                </td>
                                <td class="px-3 py-2.5 text-muted-foreground max-w-md truncate" :title="msg.body">
                                    {{ messagePreview(msg.body) }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <Button v-if="msg.remote_jid" variant="ghost" size="sm" class="h-7 text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300" @click="openChat(msg.remote_jid)">
                                        Open chat
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!messages.data?.length">
                                <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">
                                    No messages found. Send or receive messages via WhatsApp to see them here.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="messages.links?.length > 3" class="flex items-center justify-center gap-1 border-t border-border p-3">
                    <template v-for="(link, index) in messages.links" :key="index">
                        <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm" class="h-8 min-w-8 text-xs" as-child>
                            <Link :href="link.url" preserve-scroll v-html="link.label" />
                        </Button>
                        <span v-else class="px-2 text-muted-foreground text-xs" v-html="link.label" />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
