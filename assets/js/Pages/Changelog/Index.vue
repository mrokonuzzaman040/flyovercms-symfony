<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { GitBranch, Calendar, User, Plus, AlertTriangle, Wrench, CheckCircle2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    changelogs: { type: Object, required: true },
});

const formatMonthYear = (key) => {
    const [year, month] = key.split('-');
    const date = new Date(year, month - 1, 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const TYPE_I_C_O_N_S = Object.freeze({
        feature: Plus,
        improvement: CheckCircle2,
        bug_fix: Wrench,
        breaking: AlertTriangle,
    });
const getTypeIcon = (type) => TYPE_I_C_O_N_S[type] || GitBranch;

const TYPE_C_O_L_O_R_S = Object.freeze({
        feature: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        improvement: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        bug_fix: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        breaking: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    });
const getTypeColor = (type) => TYPE_C_O_L_O_R_S[type] || 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400';

const TYPE_BADGE_C_O_L_O_R_S = Object.freeze({
        feature: 'bg-green-500',
        improvement: 'bg-blue-500',
        bug_fix: 'bg-amber-500',
        breaking: 'bg-red-500',
    });
const getTypeBadgeColor = (type) => TYPE_BADGE_C_O_L_O_R_S[type] || 'bg-gray-500';

const sortedMonths = computed(() => {
    return Object.keys(props.changelogs).sort((a, b) => b.localeCompare(a));
});
</script>

<template>
    <Head title="Changelog" />

    <div class="space-y-4">
        <!-- Header -->
        <div>
            <h1 class="text-lg font-semibold tracking-tight">Changelog</h1>
            <p class="text-xs text-muted-foreground">View all updates and changes to the system</p>
        </div>

        <!-- Changelog List -->
        <div v-if="sortedMonths.length" class="space-y-6">
            <div v-for="monthKey in sortedMonths" :key="monthKey">
                <!-- Month Header -->
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <Calendar class="h-4 w-4 text-primary" />
                    </div>
                    <h2 class="text-sm font-semibold">{{ formatMonthYear(monthKey) }}</h2>
                </div>

                <!-- Changelogs for this month -->
                <div class="space-y-3 ml-4 border-l-2 border-border pl-4">
                    <Card v-for="changelog in changelogs[monthKey]" :key="changelog.id">
                        <CardHeader class="p-3 pb-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-white', getTypeBadgeColor(changelog.type)]">
                                        <component :is="getTypeIcon(changelog.type)" class="h-3 w-3" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xs font-semibold">{{ changelog.title }}</CardTitle>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <Badge :class="getTypeColor(changelog.type)" class="text-[9px] uppercase px-1.5 py-0">
                                                {{ changelog.type?.replace('_', ' ') }}
                                            </Badge>
                                            <span v-if="changelog.version" class="text-[10px] text-muted-foreground">v{{ changelog.version }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-muted-foreground shrink-0">
                                    <Calendar class="h-3 w-3" />
                                    {{ formatDate(changelog.release_date) }}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="p-3 pt-0">
                            <p class="text-xs text-muted-foreground whitespace-pre-wrap">{{ changelog.description }}</p>
                            <div v-if="changelog.creator" class="flex items-center gap-1 mt-2 text-[10px] text-muted-foreground">
                                <User class="h-3 w-3" />
                                {{ changelog.creator.name }}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="p-12 text-center">
                <div class="w-14 h-14 bg-muted rounded-full flex items-center justify-center mx-auto mb-3">
                    <GitBranch class="h-7 w-7 text-muted-foreground" />
                </div>
                <p class="text-sm font-medium">No Changelog Entries</p>
                <p class="text-xs text-muted-foreground">Changelog updates will appear here.</p>
            </CardContent>
        </Card>
    </div>
</template>
