<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Switch } from '@/Components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, ChevronDown, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    branch: { type: Object, required: true },
    users: { type: Array, default: () => [] },
    currentManagerId: { type: [Number, null], default: null },
});

const form = useForm({
    name: props.branch.name || '',
    code: props.branch.code || '',
    description: props.branch.description || '',
    address: props.branch.address || '',
    phone: props.branch.phone || '',
    email: props.branch.email || '',
    is_active: props.branch.is_active !== undefined ? Boolean(props.branch.is_active) : true,
    branch_manager_id: props.currentManagerId ? String(props.currentManagerId) : '',
});

const selectedManager = computed(() => {
    if (!form.branch_manager_id) {
        return null;
    }
    return props.users.find(u => String(u.id) === form.branch_manager_id);
});

const managerDisplayText = computed(() => {
    if (!selectedManager.value) {
        return 'Select a manager (optional)';
    }
    return `${selectedManager.value.name} (${selectedManager.value.email})`;
});

const submit = () => {
    form.put(route('branches.update', props.branch.id));
};
</script>

<template>
    <Head :title="`Edit ${branch.name}`" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link 
                :href="route('branches.index')" 
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Branch</h1>
                <p class="text-muted-foreground">Update {{ branch.name }} details.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Branch Details</CardTitle>
                    <CardDescription>Update the branch information.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="e.g., Main Branch" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="code">Code</Label>
                            <Input id="code" v-model="form.code" placeholder="e.g., MAIN" maxlength="10" :class="{ 'border-destructive': form.errors.code }" />
                            <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                        </div>
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
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button 
                                    variant="outline" 
                                    class="w-full justify-between" 
                                    :class="{ 'border-destructive': form.errors.branch_manager_id }"
                                >
                                    <span>{{ managerDisplayText }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.branch_manager_id">
                                    <DropdownMenuRadioItem value="">
                                        No Manager
                                    </DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem 
                                        v-for="user in users" 
                                        :key="user.id" 
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <p v-if="form.errors.branch_manager_id" class="text-sm text-destructive">{{ form.errors.branch_manager_id }}</p>
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
                    Update Branch
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('branches.index')">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
