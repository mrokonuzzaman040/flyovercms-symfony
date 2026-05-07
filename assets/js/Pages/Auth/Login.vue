<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Checkbox } from '@/Components/ui/checkbox'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Alert, AlertDescription } from '@/Components/ui/alert'
import { Loader2, LogIn } from 'lucide-vue-next'

defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
    lastEmail: {
        type: String,
        default: '',
    },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
    _csrf_token: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <AuthLayout>
        <template #head><title>Sign in — FlyoverCMS</title></template>

        <Card class="w-full max-w-md border border-border/50 bg-card/95 backdrop-blur-sm shadow-xl shadow-black/5 dark:shadow-none transition-shadow hover:shadow-2xl hover:shadow-black/5">
            <CardHeader class="space-y-1 text-center pb-6">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <LogIn class="h-6 w-6" />
                </div>
                <CardTitle class="text-2xl font-semibold tracking-tight">Welcome back</CardTitle>
                <CardDescription class="text-muted-foreground">
                    Sign in to your account to continue
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Alert v-if="errors.email" class="mb-6 border-destructive/50 bg-destructive/10">
                    <AlertDescription class="text-destructive">
                        {{ errors.email }}
                    </AlertDescription>
                </Alert>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            autocomplete="username"
                            required
                            autofocus
                            :class="{ 'border-destructive': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            :class="{ 'border-destructive': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                :checked="form.remember"
                                @update:checked="form.remember = $event"
                            />
                            <Label for="remember" class="text-sm font-normal cursor-pointer text-muted-foreground">
                                Remember me
                            </Label>
                        </div>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </AuthLayout>
</template>
