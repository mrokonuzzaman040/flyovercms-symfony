<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Activity, User, Lock, Settings, Clock } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
});

const ACTIVITY_I_C_O_N_S = Object.freeze({
        profile_updated: User,
        password_changed: Lock,
        settings_changed: Settings,
    });
const getActivityIcon = (type) => ACTIVITY_I_C_O_N_S[type] || Activity;

const ACTIVITY_C_O_L_O_R_S = Object.freeze({
        profile_updated: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600',
        password_changed: 'bg-green-100 dark:bg-green-900/30 text-green-600',
        settings_changed: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600',
    });
const getActivityColor = (type) => ACTIVITY_C_O_L_O_R_S[type] || 'bg-gray-100 dark:bg-gray-900/30 text-gray-600';

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Activity Log" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                <Link :href="route('profile.show')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Activity Log</h1>
                <p class="text-xs text-muted-foreground">View your recent account activity</p>
            </div>
        </div>

        <!-- User Info -->
        <Card>
            <CardContent class="p-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center text-white text-lg font-semibold">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold">{{ user.name }}</h3>
                        <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Activities -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-primary to-primary/80 rounded" />
                    Recent Activity
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="activities.length" class="divide-y divide-border">
                    <div v-for="(activity, index) in activities" :key="index" class="p-4 hover:bg-muted/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0', getActivityColor(activity.type)]">
                                <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium">{{ activity.description }}</p>
                                <div class="flex items-center gap-1.5 mt-1 text-[10px] text-muted-foreground">
                                    <Clock class="h-3 w-3" />
                                    {{ formatDate(activity.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div class="w-14 h-14 bg-muted rounded-full flex items-center justify-center mx-auto mb-3">
                        <Activity class="h-7 w-7 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium">No Activity</p>
                    <p class="text-xs text-muted-foreground">Your activity will appear here.</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
