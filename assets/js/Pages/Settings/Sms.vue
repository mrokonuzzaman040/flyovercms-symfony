<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, ChevronDown, Loader2, Smartphone } from 'lucide-vue-next';

const providerOptions = [
    { value: 'rtcom', label: 'RTCom' },
    { value: 'bulksmsbd', label: 'BulkSMS BD' },
    { value: 'sslwireless', label: 'SSL Wireless' },
    { value: 'greenweb', label: 'Greenweb' },
    { value: 'infozill', label: 'Infozill' },
];

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    provider: { type: String, default: 'rtcom' },
    baseUrl: { type: String, default: '' },
    accountCode: { type: String, default: '' },
    apiKey: { type: String, default: '' },
    senderId: { type: String, default: '' },
    messageType: { type: String, default: 'text' },
    transactionType: { type: String, default: 'T' },
});

const form = useForm({
    provider: props.provider || 'rtcom',
    base_url: props.baseUrl || 'https://api.rtcom.xyz',
    account_code: props.accountCode || '',
    api_key: props.apiKey || '',
    sender_id: props.senderId || '',
    message_type: props.messageType || 'text',
    transaction_type: props.transactionType || 'T',
});

const submit = () => {
    form.put(route('admin.settings.sms.update'));
};
</script>

<template>
    <Head title="SMS Settings" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">SMS Settings</h1>
                <p class="text-sm text-muted-foreground mt-1">Configure SMS provider and credentials. Values are saved to .env.</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Smartphone class="h-4 w-4" />
                    SMS Configuration
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="provider">Provider <span class="text-destructive">*</span></Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button id="provider" variant="outline" class="h-10 w-full justify-between font-normal">
                                    <span>{{ providerOptions.find(o => o.value === form.provider)?.label || 'Select provider' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                <DropdownMenuRadioGroup v-model="form.provider">
                                    <DropdownMenuRadioItem
                                        v-for="opt in providerOptions"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-2">
                        <Label for="base_url">Base URL</Label>
                        <Input
                            id="base_url"
                            v-model="form.base_url"
                            type="url"
                            class="h-10"
                            placeholder="https://api.rtcom.xyz"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="account_code">Account Code</Label>
                            <Input
                                id="account_code"
                                v-model="form.account_code"
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="sender_id">Sender ID</Label>
                            <Input
                                id="sender_id"
                                v-model="form.sender_id"
                                class="h-10"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="api_key">API Key</Label>
                        <Input
                            id="api_key"
                            v-model="form.api_key"
                            type="password"
                            class="h-10"
                            placeholder="Leave blank to keep current"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="message_type">Message Type</Label>
                            <Input
                                id="message_type"
                                v-model="form.message_type"
                                class="h-10"
                                placeholder="text"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="transaction_type">Transaction Type</Label>
                            <Input
                                id="transaction_type"
                                v-model="form.transaction_type"
                                class="h-10"
                                placeholder="T"
                            />
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" as-child>
                            <Link :href="route('admin.settings.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update SMS Settings
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
