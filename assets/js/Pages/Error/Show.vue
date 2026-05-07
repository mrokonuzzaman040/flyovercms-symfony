<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    ShieldAlert,
    FileQuestion,
    Clock,
    Ban,
    ServerCrash,
    Wrench,
    Home,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: [Number, String],
        required: true,
    },
    message: {
        type: String,
        default: null,
    },
});

const page = usePage();
const appName = computed(() => page.props.appName || 'FlyoverCMS');

const statusNumber = computed(() => Number(props.status));

const errorConfig = computed(() => {
    const configs = {
        403: {
            title: 'Access Denied',
            description: props.message || 'You do not have permission to access this page.',
            icon: ShieldAlert,
            suggestion: 'Contact your administrator if you believe this is an error.',
        },
        404: {
            title: 'Page Not Found',
            description: props.message || 'The page you are looking for does not exist or has been moved.',
            icon: FileQuestion,
            suggestion: 'Check the URL or go back to the dashboard.',
        },
        419: {
            title: 'Page Expired',
            description: props.message || 'Your session has expired. Please refresh and try again.',
            icon: Clock,
            suggestion: 'Refresh the page and submit the form again.',
        },
        429: {
            title: 'Too Many Requests',
            description: props.message || 'You have made too many requests. Please slow down and try again later.',
            icon: Ban,
            suggestion: 'Wait a few minutes before trying again.',
        },
        500: {
            title: 'Server Error',
            description: props.message || 'Something went wrong on our end. We have been notified.',
            icon: ServerCrash,
            suggestion: 'Please try again later or contact support if the problem persists.',
        },
        503: {
            title: 'Service Unavailable',
            description: props.message || 'We are temporarily unavailable. Please try again in a few moments.',
            icon: Wrench,
            suggestion: 'We are likely performing maintenance. Please try again shortly.',
        },
    };
    return configs[statusNumber.value] ?? {
        title: 'Something Went Wrong',
        description: props.message || `An error occurred (${statusNumber.value}).`,
        icon: ServerCrash,
        suggestion: 'Please try again or contact support.',
    };
});

const IconComponent = computed(() => errorConfig.value.icon);
</script>

<template>
    <AuthLayout>
        <Head :title="`${errorConfig.title} - ${statusNumber}`" />

        <Card class="w-full max-w-md border-0 shadow-none bg-transparent">
            <CardHeader class="space-y-1 text-center pb-6">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-destructive/10 text-destructive dark:bg-destructive/20"
                >
                    <component :is="IconComponent" class="h-8 w-8" />
                </div>
                <p class="text-sm font-medium text-muted-foreground">
                    Error {{ statusNumber }}
                </p>
                <CardTitle class="text-2xl font-bold tracking-tight">
                    {{ errorConfig.title }}
                </CardTitle>
                <CardDescription class="text-base">
                    {{ errorConfig.description }}
                </CardDescription>
                <p class="text-sm text-muted-foreground">
                    {{ errorConfig.suggestion }}
                </p>
            </CardHeader>
            <CardContent class="flex flex-col gap-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                    <Button variant="default" as-child>
                        <Link :href="route('dashboard')">
                            <Home class="mr-2 h-4 w-4" />
                            Go to Dashboard
                        </Link>
                    </Button>
                    <Button variant="outline" @click="() => router.visit(route('dashboard'))">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Go Back
                    </Button>
                </div>
                <p class="text-center text-xs text-muted-foreground">
                    If you need help, contact your administrator or support.
                </p>
            </CardContent>
        </Card>
    </AuthLayout>
</template>
