<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Switch } from '@/Components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    users: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    address: '',
    phone: '',
    email: '',
    is_active: true,
    branch_manager_id: '',
});

const submit = () => {
    form.post(route('branches.store'));
};
</script>

<template>
    <Head title="Create Branch" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link 
                :href="route('branches.index')" 
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Create Branch</h1>
                <p class="text-muted-foreground">Add a new branch location.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Branch Details</CardTitle>
                    <CardDescription>Enter the branch information.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g., Main Branch" :class="{ 'border-destructive': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="branch@example.com" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" placeholder="+1234567890" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="address">Address</Label>
                        <Input id="address" v-model="form.address" placeholder="Full address" />
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Branch description..." rows="2" />
                    </div>
                    <div class="space-y-2">
                        <Label for="branch_manager">Branch Manager</Label>
                        <Select v-model="form.branch_manager_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a manager (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">No Manager</SelectItem>
                                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                                    {{ user.name }} ({{ user.email }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Switch id="is_active" v-model="form.is_active" />
                        <Label for="is_active">Active</Label>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Create Branch
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('branches.index')">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
