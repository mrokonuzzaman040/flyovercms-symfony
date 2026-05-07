<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Switch } from '@/Components/ui/switch';
import { ArrowLeft, Loader2, MessageSquare, ExternalLink } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    apiUrl: { type: String, default: '' },
    apiKey: { type: String, default: '' },
    webhookSecret: { type: String, default: '' },
    enabled: { type: Boolean, default: false },
    webhookUrl: { type: String, default: '' },
    docsUrl: { type: String, default: 'https://wasenderapi.com/api-docs/getting-started/getting-started-with-wasenderapi' },
});

const form = useForm({
    api_url: props.apiUrl || '',
    api_key: props.apiKey || '',
    webhook_secret: props.webhookSecret || '',
    enabled: props.enabled ?? false,
});

const submit = () => {
    form.put(route('admin.settings.whatsapp.update'));
};
</script>

<template>
    <Head title="WhatsApp (WasenderAPI)" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">WhatsApp (WasenderAPI)</h1>
                <p class="text-sm text-muted-foreground mt-1">Configure WhatsApp messaging via WasenderAPI. Values are saved to .env.</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <MessageSquare class="h-4 w-4" />
                    WasenderAPI Configuration
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="api_url">API URL</Label>
                        <Input
                            id="api_url"
                            v-model="form.api_url"
                            type="url"
                            class="h-10"
                            placeholder="https://www.wasenderapi.com/api"
                        />
                        <p class="text-xs text-muted-foreground">Optional. Default: https://www.wasenderapi.com/api</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="api_key">API Key (Bearer token)</Label>
                        <Input
                            id="api_key"
                            v-model="form.api_key"
                            type="password"
                            class="h-10"
                            placeholder="Leave blank to keep current"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="webhook_secret">Webhook Secret</Label>
                        <Input
                            id="webhook_secret"
                            v-model="form.webhook_secret"
                            type="text"
                            class="h-10"
                            placeholder="Optional"
                        />
                        <p class="text-xs text-muted-foreground">Set and add to Wasender dashboard to verify webhooks.</p>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div>
                            <Label for="enabled" class="text-base">Enable WhatsApp</Label>
                            <p class="text-xs text-muted-foreground">Allow sending WhatsApp messages via WasenderAPI</p>
                        </div>
                        <Switch id="enabled" v-model="form.enabled" />
                    </div>
                    <div v-if="webhookUrl" class="rounded-lg border border-border/50 bg-muted/20 dark:bg-muted/10 p-3 space-y-1">
                        <p class="text-xs font-medium text-muted-foreground">Webhook URL (for Wasender dashboard)</p>
                        <p class="text-xs font-mono break-all">{{ webhookUrl }}</p>
                        <p class="text-xs text-muted-foreground">
                            <a :href="docsUrl" target="_blank" rel="noopener noreferrer" class="text-primary underline inline-flex items-center gap-1">
                                WasenderAPI docs <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" as-child>
                            <Link :href="route('admin.settings.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update WhatsApp Settings
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
