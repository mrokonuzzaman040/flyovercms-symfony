<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/Components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Separator } from '@/Components/ui/separator';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { User, Lock, Mail, Phone, Loader2, CheckCircle } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user: { type: Object, required: true },
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: null },
});

const page = usePage();

// Profile Update Form
const profileForm = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
});

// Password Update Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Delete Account Form
const deleteForm = useForm({
    password: '',
});

const profileSuccess = ref(false);
const passwordSuccess = ref(false);

const submitProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            profileSuccess.value = true;
            setTimeout(() => profileSuccess.value = false, 3000);
        },
    });
};

const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordSuccess.value = true;
            setTimeout(() => passwordSuccess.value = false, 3000);
        },
    });
};

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
};
</script>

<template>

    <Head title="Profile Settings" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Profile Settings</h1>
            <p class="text-muted-foreground">Manage your account settings and change your password.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Profile Card -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col items-center text-center">
                        <Avatar class="h-24 w-24">
                            <AvatarImage v-if="user.avatar_url" :src="user.avatar_url" />
                            <AvatarFallback class="text-2xl bg-primary/10 text-primary">{{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <h2 class="mt-4 text-lg font-semibold">{{ user.name }}</h2>
                        <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 justify-center">
                            <span v-for="role in user.roles" :key="role.id"
                                class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                {{ role.name }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Forms Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" /> Profile Information
                        </CardTitle>
                        <CardDescription>Update your account's profile information and email address.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitProfile" class="space-y-4">
                            <Alert v-if="profileSuccess"
                                class="border-green-500 text-green-700 bg-green-50 dark:bg-green-950/20">
                                <CheckCircle class="h-4 w-4" />
                                <AlertDescription>Profile updated successfully.</AlertDescription>
                            </Alert>
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="profileForm.name"
                                    :class="{ 'border-destructive': profileForm.errors.name }" />
                                <p v-if="profileForm.errors.name" class="text-sm text-destructive">{{
                                    profileForm.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="profileForm.email"
                                    :class="{ 'border-destructive': profileForm.errors.email }" />
                                <p v-if="profileForm.errors.email" class="text-sm text-destructive">{{
                                    profileForm.errors.email }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" v-model="profileForm.phone" placeholder="+1234567890" />
                            </div>
                            <Button type="submit" :disabled="profileForm.processing">
                                <Loader2 v-if="profileForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Save Changes
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Update Password -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Lock class="h-5 w-5" /> Update Password
                        </CardTitle>
                        <CardDescription>Ensure your account is using a long, random password to stay secure.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitPassword" class="space-y-4">
                            <Alert v-if="passwordSuccess"
                                class="border-green-500 text-green-700 bg-green-50 dark:bg-green-950/20">
                                <CheckCircle class="h-4 w-4" />
                                <AlertDescription>Password updated successfully.</AlertDescription>
                            </Alert>
                            <div class="space-y-2">
                                <Label for="current_password">Current Password</Label>
                                <Input id="current_password" type="password" v-model="passwordForm.current_password"
                                    :class="{ 'border-destructive': passwordForm.errors.current_password }" />
                                <p v-if="passwordForm.errors.current_password" class="text-sm text-destructive">{{
                                    passwordForm.errors.current_password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="password">New Password</Label>
                                <Input id="password" type="password" v-model="passwordForm.password"
                                    :class="{ 'border-destructive': passwordForm.errors.password }" />
                                <p v-if="passwordForm.errors.password" class="text-sm text-destructive">{{
                                    passwordForm.errors.password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input id="password_confirmation" type="password"
                                    v-model="passwordForm.password_confirmation" />
                            </div>
                            <Button type="submit" :disabled="passwordForm.processing">
                                <Loader2 v-if="passwordForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Update Password
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
