<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Loader2, CheckCircle2, ArrowLeft, Mail } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});
</script>

<template>
    <AuthLayout>
        <Head title="Forgot Password" />

        <Card class="w-full max-w-md border border-border/50 bg-card/95 backdrop-blur-sm shadow-xl shadow-black/5 dark:shadow-none">
            <CardHeader class="space-y-1 text-center pb-6">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Mail class="h-6 w-6" />
                </div>
                <CardTitle class="text-2xl font-semibold tracking-tight">Forgot your password?</CardTitle>
                <CardDescription class="text-muted-foreground">
                    Enter your email and we'll send you a link to reset your password.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <Alert v-if="status" class="border-green-200 bg-green-50 dark:bg-green-950/50 dark:border-green-800">
                    <CheckCircle2 class="h-4 w-4 text-green-600 dark:text-green-400" />
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        {{ status }}
                    </AlertDescription>
                </Alert>

                <form @submit.prevent="form.post(route('password.email'))" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">Email Address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                            autofocus
                            :class="{ 'border-destructive': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Sending...' : 'Send reset link' }}
                    </Button>
                </form>

                <Link
                    :href="route('login')"
                    class="inline-flex items-center justify-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors w-full"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to sign in
                </Link>
            </CardContent>
        </Card>
    </AuthLayout>
</template>
