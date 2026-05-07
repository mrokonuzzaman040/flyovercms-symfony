<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Filter, History, User, Building2, Hash } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    activityLogs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    usersForFilter: { type: Array, default: () => [] },
    branchesForFilter: { type: Array, default: () => [] },
});

const actionQuery = ref(props.filters.action ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const applyFilter = (key, value) => {
    const next = { ...props.filters, [key]: value || undefined };
    if (!value) delete next[key];
    router.get(route('activity-log.index'), next, { preserveState: true });
};

const applyActionFilter = () => {
    applyFilter('action', actionQuery.value?.trim() || null);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

const subjectDisplay = (log) => {
    if (!log.subject_type) return '—';
    const short = log.subject_type.replace('App\\Models\\', '');
    const id = log.subject_id ? ` #${log.subject_id}` : '';
    return `${short}${id}`;
};
</script>

<template>
    <Head title="Activity Log" />

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Activity Log</h1>
                <p class="text-xs text-muted-foreground">Track in-app actions performed by users</p>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="p-3">
                <div class="flex flex-wrap items-center gap-3">
                    <Filter class="h-4 w-4 text-muted-foreground shrink-0" />
                    <Select
                        :model-value="filters.user_id ? String(filters.user_id) : 'all'"
                        @update:model-value="(v) => applyFilter('user_id', v === 'all' ? null : v)"
                    >
                        <SelectTrigger class="h-8 w-[180px] text-xs">
                            <User class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <SelectValue placeholder="All users" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all" class="text-xs">All users</SelectItem>
                            <SelectItem
                                v-for="u in usersForFilter"
                                :key="u.id"
                                :value="String(u.id)"
                                class="text-xs"
                            >
                                {{ u.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        :model-value="filters.branch_id ? String(filters.branch_id) : 'all'"
                        @update:model-value="(v) => applyFilter('branch_id', v === 'all' ? null : v)"
                    >
                        <SelectTrigger class="h-8 w-[160px] text-xs">
                            <Building2 class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <SelectValue placeholder="All branches" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all" class="text-xs">All branches</SelectItem>
                            <SelectItem
                                v-for="b in branchesForFilter"
                                :key="b.id"
                                :value="String(b.id)"
                                class="text-xs"
                            >
                                {{ b.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="actionQuery"
                            placeholder="Action (e.g. lead.created)"
                            class="h-8 text-xs w-[160px]"
                            @keydown.enter="applyActionFilter"
                        />
                        <Button size="sm" variant="secondary" class="h-8 text-xs" @click="applyActionFilter">
                            Filter action
                        </Button>
                    </div>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="h-8 text-xs w-[130px]"
                            @input="(e) => { dateFrom = e.target.value; applyFilter('date_from', e.target.value || null); }"
                        />
                        <span class="text-muted-foreground text-xs">to</span>
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="h-8 text-xs w-[130px]"
                            @input="(e) => { dateTo = e.target.value; applyFilter('date_to', e.target.value || null); }"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Table -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-primary rounded" />
                    Activity entries
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Date</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">User</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Action</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Description</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Subject</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="log in activityLogs.data" :key="log.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-3 py-2 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-3 py-2">
                                    <template v-if="log.user">
                                        <Link
                                            v-if="log.user.id"
                                            :href="route('users.show', log.user.id)"
                                            class="text-xs hover:underline"
                                        >
                                            {{ log.user.name }}
                                        </Link>
                                        <span v-else class="text-xs">{{ log.user.name }}</span>
                                        <div v-if="log.user.branch" class="text-[10px] text-muted-foreground">
                                            {{ log.user.branch.name }}
                                        </div>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center gap-1 rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium">
                                        <Hash class="h-3 w-3" />
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs max-w-[280px]">
                                    <span class="line-clamp-2" :title="log.description">{{ log.description || '—' }}</span>
                                </td>
                                <td class="px-3 py-2 text-xs text-muted-foreground">
                                    {{ subjectDisplay(log) }}
                                </td>
                            </tr>
                            <tr v-if="!activityLogs.data?.length">
                                <td colspan="5" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 bg-muted rounded-full flex items-center justify-center">
                                            <History class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <p class="text-sm font-medium">No activity yet</p>
                                        <p class="text-xs text-muted-foreground">Actions will appear here as users use the app.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="activityLogs.links?.length > 3" class="flex items-center justify-center gap-1 p-3 border-t">
                    <template v-for="(link, index) in activityLogs.links" :key="index">
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
