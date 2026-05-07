<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Send, Loader2, Users, User } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    lead: { type: Object, required: true },
    department: { type: String, required: true },
    departmentUsers: { type: Array, default: () => [] },
    auth: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
    notifications: { type: Array, default: () => [] },
    csrf_token: { type: String, default: '' },
    sidebarMenu: { type: Array, default: () => [] },
    appName: { type: String, default: '' },
});

const transferMode = ref('broadcast');

const form = useForm({
    transferred_to: null,
    note: '',
});

const isBroadcast = computed(() => transferMode.value === 'broadcast');

const canSubmit = computed(() => {
    if (form.processing) return false;
    if (isBroadcast.value) return true;
    return Boolean(form.transferred_to) && props.departmentUsers.length > 0;
});

const submit = () => {
    const payload = {
        note: form.note,
        transferred_to: isBroadcast.value ? null : form.transferred_to,
    };
    form.transform(() => payload).post(route('departments.transfers.transfer', [props.lead.id, props.department]), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head :title="`Transfer Lead to ${department.charAt(0).toUpperCase() + department.slice(1)} Department`" />

    <div class="container mx-auto py-6 space-y-6">
        <div class="flex items-center gap-4">
            <Button as-child variant="ghost" size="sm">
                <Link :href="route('leads.show', lead.id)">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Lead
                </Link>
            </Button>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">
                    Transfer to {{ department.charAt(0).toUpperCase() + department.slice(1) }} Department
                </h1>
                <p class="text-muted-foreground mt-1">
                    Transfer lead "{{ lead.full_name }}" to {{ department }} department
                </p>
            </div>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle>Transfer Details</CardTitle>
                <CardDescription>
                    Send this lead to the {{ department }} department. You can notify everyone in the branch (first to accept gets it)
                    or assign to one person.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <Label>Lead Information</Label>
                        <div class="mt-2 p-4 bg-muted rounded-md">
                            <p class="font-medium">{{ lead.full_name }}</p>
                            <p class="text-sm text-muted-foreground">{{ lead.email }}</p>
                            <p class="text-sm text-muted-foreground">{{ lead.phone }}</p>
                        </div>
                    </div>

                    <div>
                        <Label class="mb-3 block">Who can accept this lead?</Label>
                        <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-input bg-background p-4 transition-colors hover:bg-muted/50 has-checked:ring-2 has-checked:ring-ring has-checked:border-primary"
                            >
                                <input
                                    v-model="transferMode"
                                    type="radio"
                                    value="broadcast"
                                    class="h-4 w-4 border-input text-primary"
                                />
                                <Users class="h-5 w-5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p class="font-medium">Notify entire department</p>
                                    <p class="text-sm text-muted-foreground">
                                        All users in this branch with permission will get a notification. First to accept gets the lead.
                                    </p>
                                </div>
                            </label>
                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-input bg-background p-4 transition-colors hover:bg-muted/50 has-checked:ring-2 has-checked:ring-ring has-checked:border-primary"
                            >
                                <input
                                    v-model="transferMode"
                                    type="radio"
                                    value="specific"
                                    class="h-4 w-4 border-input text-primary"
                                />
                                <User class="h-5 w-5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p class="font-medium">Assign to specific person</p>
                                    <p class="text-sm text-muted-foreground">
                                        Only the selected user will be notified and can accept.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div v-show="!isBroadcast">
                        <Label for="transferred_to">Select person <span class="text-destructive">*</span></Label>
                        <select
                            id="transferred_to"
                            v-model="form.transferred_to"
                            class="mt-2 flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option :value="null" disabled>Select a person</option>
                            <option v-for="user in departmentUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.transferred_to" class="mt-1 text-sm text-destructive">
                            {{ form.errors.transferred_to }}
                        </p>
                        <p v-if="!isBroadcast && departmentUsers.length === 0" class="mt-1 text-sm text-muted-foreground">
                            No users found with permission to accept transfers for this branch.
                        </p>
                    </div>

                    <div>
                        <Label>Note (optional)</Label>
                        <Textarea
                            v-model="form.note"
                            placeholder="Add any notes or instructions for the department..."
                            rows="4"
                        />
                        <p v-if="form.errors.note" class="mt-1 text-sm text-destructive">
                            {{ form.errors.note }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Button type="button" variant="outline" as-child>
                            <Link :href="route('leads.show', lead.id)">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="!canSubmit">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Send v-else class="mr-2 h-4 w-4" />
                            Transfer to {{ department.charAt(0).toUpperCase() + department.slice(1) }} Department
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
