<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import {
    ArrowLeft,
    Send,
    Info,
    AlertTriangle,
    User,
    Mail,
    Phone,
    Building,
    Loader2,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    users: { type: Array, default: () => [] },
});

const form = useForm({
    transferred_to: '',
    note: '',
});

const submit = () => {
    form.post(route('leads.transfer.store', props.lead.id));
};

const leadName = () => props.lead?.full_name ?? props.lead?.name ?? '—';
</script>

<template>
    <Head title="Transfer Lead" />

    <div class="fade-in space-y-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center gap-4">
            <Button
                variant="ghost"
                size="icon"
                :as="Link"
                :href="route('leads.show', lead.id)"
                class="shrink-0"
                aria-label="Back to lead"
            >
                <ArrowLeft class="h-4 w-4" />
            </Button>
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    Transfer Lead
                </h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Transfer lead ownership to another user in your branch
                </p>
            </div>
        </div>

        <!-- Lead summary -->
        <Card class="border-border bg-card">
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Lead information</CardTitle>
                <CardDescription>Lead being transferred</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                            <User class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Name</p>
                            <p class="truncate font-medium text-foreground">{{ leadName() }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                            <Mail class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Email</p>
                            <p class="truncate text-sm text-foreground">{{ lead.email ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                            <Phone class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Phone</p>
                            <p class="truncate text-sm text-foreground">{{ lead.phone ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                            <Building class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Branch</p>
                            <p class="truncate text-sm text-foreground">
                                {{ lead.branch?.name ?? 'No branch' }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Transfer form -->
        <Card class="border-border bg-card">
            <form @submit.prevent="submit">
                <CardHeader class="pb-4">
                    <CardTitle class="text-base">Transfer details</CardTitle>
                    <CardDescription>Choose the recipient and add an optional note</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <Alert
                        v-if="lead.pending_transfer || lead.pendingTransfer"
                        class="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200 [&>svg]:text-amber-600 dark:[&>svg]:text-amber-400"
                    >
                        <AlertTriangle class="h-4 w-4" />
                        <AlertTitle>Pending transfer</AlertTitle>
                        <AlertDescription>
                            There is already a pending transfer request for this lead.
                        </AlertDescription>
                    </Alert>

                    <div class="space-y-2">
                        <Label for="transferred_to">Transfer to *</Label>
                        <select
                            id="transferred_to"
                            v-model="form.transferred_to"
                            required
                            :disabled="form.processing"
                            :class="[
                                'flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30',
                                form.errors.transferred_to && 'border-destructive ring-destructive/20',
                            ]"
                        >
                            <option value="" disabled>Select user</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="String(user.id)"
                            >
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.transferred_to" class="text-sm text-destructive">
                            {{ form.errors.transferred_to }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Only users from the same branch can receive this lead.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="note">Note (optional)</Label>
                        <Textarea
                            id="note"
                            v-model="form.note"
                            rows="3"
                            placeholder="Add any additional information about this transfer..."
                            class="resize-y"
                            :disabled="form.processing"
                        />
                        <p v-if="form.errors.note" class="text-sm text-destructive">
                            {{ form.errors.note }}
                        </p>
                    </div>

                    <Alert class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/30">
                        <Info class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        <AlertTitle class="text-blue-900 dark:text-blue-100">What happens next?</AlertTitle>
                        <AlertDescription class="text-blue-800 dark:text-blue-200">
                            The selected user will receive a notification and can accept or reject the transfer.
                            If accepted, they will become the new owner of this lead.
                        </AlertDescription>
                    </Alert>
                </CardContent>
                <CardFooter class="flex flex-wrap gap-3 border-t border-border pt-6">
                    <Button
                        type="button"
                        variant="outline"
                        :as="Link"
                        :href="route('leads.show', lead.id)"
                        :disabled="form.processing"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        variant="default"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Send v-else class="h-4 w-4" />
                        {{ form.processing ? 'Sending…' : 'Send transfer request' }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
