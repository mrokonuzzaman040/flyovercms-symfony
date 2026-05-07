<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
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
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    userRoles: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
    branch_id: props.user.branch_id ? String(props.user.branch_id) : '',
    roles: props.userRoles && props.userRoles.length > 0
        ? props.userRoles.map(r => Number(r))
        : (props.user.roles?.map(r => Number(r.id)) || []),
});

const toggleRole = (roleId, checked = null) => {
    const roleIdNum = Number(roleId);
    const index = form.roles.indexOf(roleIdNum);
    const isCurrentlySelected = index !== -1;

    // If checked is explicitly provided (from checkbox), use it
    // Otherwise, toggle the current state (from div click)
    const shouldBeSelected = checked !== null ? checked : !isCurrentlySelected;

    if (shouldBeSelected && !isCurrentlySelected) {
        form.roles.push(roleIdNum);
    } else if (!shouldBeSelected && isCurrentlySelected) {
        form.roles.splice(index, 1);
    }
};

const isRoleSelected = (roleId) => {
    return form.roles.includes(Number(roleId));
};

const selectedBranch = computed(() => {
    if (!form.branch_id) {
        return null;
    }
    return props.branches.find(b => String(b.id) === form.branch_id);
});

const branchDisplayText = computed(() => {
    if (!selectedBranch.value) {
        return 'Select a branch (optional)';
    }
    return `${selectedBranch.value.name}${selectedBranch.value.code ? ` (${selectedBranch.value.code})` : ''}`;
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>

    <Head :title="`Edit ${user.name}`" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-4">
            <Link :href="route('users.index')"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 w-9">
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit User</h1>
                <p class="text-muted-foreground">Update {{ user.name }}'s account details.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>Update the user's basic details.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" type="text" placeholder="Enter full name"
                                :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="Enter email address"
                                :class="{ 'border-destructive': form.errors.email }" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="branch">Branch</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between"
                                        :class="{ 'border-destructive': form.errors.branch_id }">
                                        <span>{{ branchDisplayText }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.branch_id">
                                        <DropdownMenuRadioItem value="">
                                            No Branch
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="branch in branches" :key="branch.id"
                                            :value="String(branch.id)">
                                            {{ branch.name }} {{ branch.code ? `(${branch.code})` : '' }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.branch_id" class="text-sm text-destructive">
                                {{ form.errors.branch_id }}
                            </p>
                        </div>

                    </CardContent>
                </Card>

                <!-- Security -->
                <Card>
                    <CardHeader>
                        <CardTitle>Security</CardTitle>
                        <CardDescription>Leave password blank to keep current password.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="password">New Password</Label>
                            <Input id="password" v-model="form.password" type="password"
                                placeholder="Enter new password (optional)"
                                :class="{ 'border-destructive': form.errors.password }" />
                            <p v-if="form.errors.password" class="text-sm text-destructive">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation">Confirm New Password</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                placeholder="Confirm new password" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Roles -->
            <Card>
                <CardHeader>
                    <CardTitle>Roles</CardTitle>
                    <CardDescription>Update roles to modify user permissions.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="role in roles" :key="role.id"
                            class="flex items-center space-x-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors cursor-pointer"
                            @click.stop="toggleRole(role.id)">
                            <Checkbox :id="`role-${role.id}`" :checked="isRoleSelected(role.id)"
                                @update:checked="(checked) => toggleRole(role.id, checked)" @click.stop />
                            <div class="flex-1">
                                <Label :for="`role-${role.id}`" class="cursor-pointer font-medium">
                                    {{ role.name }}
                                </Label>
                                <p v-if="role.description" class="text-xs text-muted-foreground">
                                    {{ role.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.roles" class="mt-2 text-sm text-destructive">
                        {{ form.errors.roles }}
                    </p>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Update User
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('users.index')">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
