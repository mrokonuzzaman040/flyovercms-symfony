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
import { ArrowLeft, ChevronDown, Loader2, Mail } from 'lucide-vue-next';

const mailDriverOptions = [
    { value: 'smtp', label: 'SMTP' },
    { value: 'sendmail', label: 'Sendmail' },
    { value: 'mailgun', label: 'Mailgun' },
    { value: 'ses', label: 'Amazon SES' },
];

const encryptionOptions = [
    { value: '', label: 'None' },
    { value: 'tls', label: 'TLS' },
    { value: 'ssl', label: 'SSL' },
];

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    mail_from_name: props.settings.mail_from_name || '',
    mail_from_address: props.settings.mail_from_address || '',
    mail_driver: props.settings.mail_driver || 'smtp',
    mail_host: props.settings.mail_host || '',
    mail_port: props.settings.mail_port || '',
    mail_username: props.settings.mail_username || '',
    mail_password: props.settings.mail_password || '',
    mail_encryption: props.settings.mail_encryption || '',
});

const submit = () => {
    form.put(route('admin.settings.email.update'));
};
</script>

<template>
    <Head title="Email Settings" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Email Settings</h1>
                <p class="text-sm text-muted-foreground mt-1">Configure email server and notification settings</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Mail class="h-4 w-4" />
                    Email Configuration
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="mail_from_name">
                                From Name <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="mail_from_name"
                                v-model="form.mail_from_name"
                                required
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="mail_from_address">
                                From Address <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="mail_from_address"
                                v-model="form.mail_from_address"
                                type="email"
                                required
                                class="h-10"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="mail_driver">
                            Mail Driver <span class="text-destructive">*</span>
                        </Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button id="mail_driver" variant="outline" class="h-10 w-full justify-between font-normal">
                                    <span>{{ mailDriverOptions.find(o => o.value === form.mail_driver)?.label || 'Select driver' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                <DropdownMenuRadioGroup v-model="form.mail_driver">
                                    <DropdownMenuRadioItem
                                        v-for="opt in mailDriverOptions"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div v-if="form.mail_driver === 'smtp'" class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="mail_host">SMTP Host</Label>
                            <Input
                                id="mail_host"
                                v-model="form.mail_host"
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="mail_port">SMTP Port</Label>
                            <Input
                                id="mail_port"
                                v-model="form.mail_port"
                                type="number"
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="mail_username">Username</Label>
                            <Input
                                id="mail_username"
                                v-model="form.mail_username"
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="mail_password">Password</Label>
                            <Input
                                id="mail_password"
                                v-model="form.mail_password"
                                type="password"
                                class="h-10"
                                placeholder="Leave blank to keep current"
                                autocomplete="new-password"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="mail_encryption">Encryption</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button id="mail_encryption" variant="outline" class="h-10 w-full justify-between font-normal">
                                        <span>{{ encryptionOptions.find(o => o.value === form.mail_encryption)?.label || 'None' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-(--radix-dropdown-menu-trigger-width)">
                                    <DropdownMenuRadioGroup v-model="form.mail_encryption">
                                        <DropdownMenuRadioItem
                                            v-for="opt in encryptionOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" as-child>
                            <Link :href="route('admin.settings.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update Email Settings
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
