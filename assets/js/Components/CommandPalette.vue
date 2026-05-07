<script setup>
import { ref, shallowRef, watch, provide, onMounted, onUnmounted, computed, markRaw } from 'vue';
import CommandPaletteSearchBridge from '@/Components/CommandPaletteSearchBridge.vue';
import { router } from '@inertiajs/vue3';
import {
    CommandDialog,
    CommandInput,
    CommandList,
    CommandGroup,
    CommandItem
} from '@/Components/ui/command';
import {
    Search,
    User,
    Settings,
    LayoutGrid,
    Users,
    Loader2,
    Megaphone,
    Building2,
    Bell,
    MessageSquare,
    BarChart3,
    Clock,
    FileText,
    Upload,
    FileQuestion,
    ListTodo,
    Mail,
    Shield,
    ArrowRightLeft,
    History,
} from 'lucide-vue-next';
import axios from 'axios';
import { useDebounceFn } from '@vueuse/core';

const ICONS = markRaw({
    User,
    Users,
    LayoutGrid,
    Megaphone,
    Building2,
    Bell,
    MessageSquare,
    BarChart3,
    Clock,
    FileText,
    Upload,
    FileQuestion,
    ListTodo,
    Mail,
    Shield,
    ArrowRightLeft,
    History,
    Settings,
});

function resolveIcon(name) {
    return ICONS[name] || LayoutGrid;
}

const open = ref(false);
const results = shallowRef([]);
const loading = ref(false);

const isApplePlatform = computed(() => {
    if (typeof navigator === 'undefined') {
        return false;
    }
    return /Mac|iPhone|iPad|iPod/i.test(navigator.platform || '') || navigator.userAgent?.includes('Mac');
});

const staticLinks = [
    { title: 'Dashboard', url: route('dashboard'), icon: LayoutGrid, group: 'Navigation' },
    { title: 'All Leads', url: route('leads.index'), icon: User, group: 'Navigation' },
    { title: 'Settings', url: route('settings.index'), icon: Settings, group: 'Navigation' },
    { title: 'Notifications', url: route('notifications.index'), icon: Bell, group: 'Navigation' },
    { title: 'Messaging', url: route('messaging.conversations.index'), icon: MessageSquare, group: 'Navigation' },
    { title: 'Marketing', url: route('marketing.analytics.dashboard'), icon: BarChart3, group: 'Navigation' },
    { title: 'Marketing campaigns', url: route('marketing.campaigns.index'), icon: Megaphone, group: 'Navigation' },
    { title: 'Marketing logs', url: route('marketing.logs.index'), icon: Mail, group: 'Navigation' },
];

results.value = staticLinks;

function togglePalette(e) {
    if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        open.value = !open.value;
    }
}

onMounted(() => {
    document.addEventListener('keydown', togglePalette);
});

onUnmounted(() => {
    document.removeEventListener('keydown', togglePalette);
});

watch(open, (isOpen) => {
    if (isOpen) {
        results.value = staticLinks;
    }
});

const performSearch = useDebounceFn(async (q) => {
    const trimmed = typeof q === 'string' ? q.trim() : '';
    if (!trimmed || trimmed.length < 2) {
        results.value = trimmed
            ? staticLinks.filter((item) => item.title.toLowerCase().includes(trimmed.toLowerCase()))
            : staticLinks;
        loading.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(route('global-search'), { params: { query: trimmed } });
        const data = response?.data;
        const apiResults = Array.isArray(data) ? data : [];
        results.value = apiResults.map((item) => ({
            ...item,
            icon: resolveIcon(item.icon),
            title: item.title ?? '',
        }));
    } catch (error) {
        console.error('Search failed', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
}, 220);

provide('commandPalettePerformSearch', performSearch);

const handleSelect = (item) => {
    open.value = false;
    router.visit(item.url);
};
</script>

<template>
    <CommandDialog :open="open" @update:open="open = $event" content-class="sm:max-w-xl">
        <CommandPaletteSearchBridge />
        <div class="border-b px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-foreground">Global Search</h2>
                <kbd
                    class="pointer-events-none hidden h-6 select-none items-center gap-1 rounded border bg-muted px-2 font-mono text-[10px] font-medium text-muted-foreground sm:inline-flex"
                >
                    <template v-if="isApplePlatform">
                        <span class="text-xs">⌘</span>K
                    </template>
                    <template v-else>
                        <span class="text-xs">Ctrl</span>+K
                    </template>
                </kbd>
            </div>
        </div>
        <div class="px-3 py-2">
            <CommandInput
                placeholder="Search leads, users, campaigns, branches, or jump to a page…"
                class="border-0"
            />
        </div>
        <CommandList class="max-h-[min(420px,50vh)] p-2">
            <div
                v-if="loading"
                class="flex flex-col items-center justify-center gap-3 py-12 text-center text-muted-foreground"
            >
                <Loader2 class="size-8 animate-spin opacity-60" />
                <p class="text-sm font-medium">Searching…</p>
                <p class="text-xs">Multi-word queries narrow results (e.g. name + city)</p>
            </div>
            <template v-else>
                <div
                    v-if="results.length === 0"
                    class="flex flex-col items-center justify-center gap-2 py-12 text-center text-muted-foreground"
                >
                    <Search class="size-10 opacity-40" />
                    <p class="text-sm font-medium">No results found</p>
                    <p class="text-xs">Try another spelling, fewer words, or a lead email / phone</p>
                </div>
                <template v-else>
                    <CommandGroup
                        v-for="(group, groupName) in groupBy(results, 'group')"
                        :key="groupName"
                        :heading="groupName"
                        class="pb-2 last:pb-0"
                    >
                        <CommandItem
                            v-for="item in group"
                            :key="item.id || item.title"
                            :value="`${item.title || ''} ${item.subtitle || ''} ${item.id || ''}`"
                            class="flex items-center gap-3 rounded-md px-3 py-2.5"
                            @select="handleSelect(item)"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-muted/80 text-muted-foreground [&_svg]:size-4"
                            >
                                <component :is="item.icon" />
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <span class="truncate text-sm font-medium">{{ item.title }}</span>
                                <span v-if="item.subtitle" class="truncate text-xs text-muted-foreground">{{
                                    item.subtitle
                                }}</span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </template>
            </template>
        </CommandList>
    </CommandDialog>
</template>

<script>
function groupBy(array, key) {
    return array.reduce((result, currentValue) => {
        const groupKey = currentValue[key];
        (result[groupKey] = result[groupKey] || []).push(currentValue);
        return result;
    }, {});
}
</script>
