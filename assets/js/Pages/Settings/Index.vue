<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Mail,
    Shield,
    MessageSquare,
    Smartphone,
    Pencil,
    Puzzle,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const settingsCards = [
    {
        title: 'Email Settings',
        description: 'Configure email server and notification settings',
        icon: Mail,
        route: 'admin.settings.email',
        color: 'text-green-600',
    },
    {
        title: 'Security Settings',
        description: 'Password policies, authentication, and security features',
        icon: Shield,
        route: 'admin.settings.security',
        color: 'text-red-600',
    },
    {
        title: 'WhatsApp (WasenderAPI)',
        description: 'Configure WhatsApp messaging via WasenderAPI',
        icon: MessageSquare,
        route: 'admin.settings.whatsapp',
        color: 'text-green-600',
    },
    {
        title: 'SMS Settings',
        description: 'Configure SMS provider and credentials',
        icon: Smartphone,
        route: 'admin.settings.sms',
        color: 'text-blue-600',
    },
    {
        title: 'Modules',
        description: 'Install, activate, deactivate, and uninstall feature modules',
        icon: Puzzle,
        route: 'admin.settings.modules.index',
        color: 'text-amber-600',
    },
];
</script>

<template>

    <Head title="Application Settings" />

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Application Settings</h1>
            <p class="text-sm text-muted-foreground mt-1">Manage your application configuration and appearance</p>
        </div>

        <!-- Settings Cards Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="card in settingsCards" :key="card.route" class="hover:border-primary transition-colors">
                <CardHeader>
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-muted">
                            <component :is="card.icon" :class="['h-5 w-5', card.color]" />
                        </div>
                        <div class="flex-1">
                            <CardTitle class="text-sm">{{ card.title }}</CardTitle>
                            <p class="text-xs text-muted-foreground mt-1">{{ card.description }}</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Button variant="outline" size="sm" class="w-full h-8 text-xs" as-child>
                        <Link :href="route(card.route)">
                            <Pencil class="mr-2 h-3.5 w-3.5" />
                            Configure
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
