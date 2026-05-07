<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, Pencil, UserCog, Shield } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user_role: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`User Roles - ${user_role.name}`" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                    <Link :href="route('user-roles.index')">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">{{ user_role.name }}</h1>
                    <p class="text-xs text-muted-foreground">User Role Details</p>
                </div>
            </div>
            <Button size="sm" variant="outline" class="gap-1.5" as-child>
                <Link :href="route('user-roles.edit', user_role.id)">
                    <Pencil class="h-3.5 w-3.5" />
                    Edit Roles
                </Link>
            </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- User Info -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded" />
                        User Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center text-white">
                            <UserCog class="h-8 w-8" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold">{{ user_role.name }}</h3>
                            <p class="text-xs text-muted-foreground">{{ user_role.email }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Assigned Roles -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-green-500 to-green-600 rounded" />
                        Assigned Roles
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div v-if="user_role.roles?.length" class="space-y-2">
                        <div v-for="role in user_role.roles" :key="role.id" class="flex items-center gap-3 p-2 border rounded-lg">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <Shield class="h-4 w-4 text-green-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium">{{ role.name }}</p>
                                <p v-if="role.description" class="text-[10px] text-muted-foreground">{{ role.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-muted-foreground">
                        <Shield class="h-8 w-8 mx-auto mb-2 opacity-30" />
                        <p class="text-xs">No roles assigned</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
