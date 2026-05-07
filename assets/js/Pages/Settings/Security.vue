<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Switch } from '@/Components/ui/switch';
import { ArrowLeft, Loader2, Shield } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    session_lifetime: props.settings.session_lifetime || 120,
    two_factor_auth: props.settings.two_factor_auth || false,
    password_min_length: props.settings.password_min_length || 8,
    password_require_special: props.settings.password_require_special || false,
    user_registration: props.settings.user_registration || false,
    email_verification: props.settings.email_verification || false,
});

const submit = () => {
    form.put(route('admin.settings.security.update'));
};
</script>

<template>
    <Head title="Security Settings" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Security Settings</h1>
                <p class="text-sm text-muted-foreground mt-1">Password policies, authentication, and security features</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm flex items-center gap-2">
                    <Shield class="h-4 w-4" />
                    Security Configuration
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="session_lifetime">
                            Session Lifetime (minutes) <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="session_lifetime"
                            v-model.number="form.session_lifetime"
                            type="number"
                            min="1"
                            max="1440"
                            required
                            class="h-10"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="password_min_length">
                            Minimum Password Length <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="password_min_length"
                            v-model.number="form.password_min_length"
                            type="number"
                            min="6"
                            max="32"
                            required
                            class="h-10"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="two_factor_auth">Two-Factor Authentication</Label>
                        <Switch
                            id="two_factor_auth"
                            v-model:checked="form.two_factor_auth"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="password_require_special">Require Special Characters</Label>
                        <Switch
                            id="password_require_special"
                            v-model:checked="form.password_require_special"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="user_registration">User Registration</Label>
                        <Switch
                            id="user_registration"
                            v-model:checked="form.user_registration"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="email_verification">Email Verification</Label>
                        <Switch
                            id="email_verification"
                            v-model:checked="form.email_verification"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" as-child>
                            <Link :href="route('admin.settings.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update Security Settings
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
