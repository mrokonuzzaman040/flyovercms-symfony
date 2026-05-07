<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, ChevronDown, Loader2, Plus } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    users: { type: Array, required: true },
    roles: { type: Array, required: true },
});

const form = useForm({
    user_id: '',
    role_id: '',
});

const selectedUserLabel = computed(() => {
    if (!form.user_id) return 'Select a user';
    const u = props.users.find((user) => String(user.id) === form.user_id);
    return u ? `${u.name} (${u.email})` : 'Select a user';
});

const selectedRoleLabel = computed(() => {
    if (!form.role_id) return 'Select a role';
    const r = props.roles.find((role) => String(role.id) === form.role_id);
    return r ? r.name : 'Select a role';
});

const submit = () => {
    form.post(route('user-roles.store'));
};
</script>

<template>
    <Head title="Assign Role" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                <Link :href="route('user-roles.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Assign Role to User</h1>
                <p class="text-xs text-muted-foreground">Select a user and assign them a role</p>
            </div>
        </div>

        <!-- Form -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded" />
                    Role Assignment
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- User Selection -->
                        <div class="space-y-1.5">
                            <Label for="user-dropdown" class="text-xs">Select User <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        id="user-dropdown"
                                        variant="outline"
                                        class="h-8 w-full justify-between text-xs font-normal"
                                    >
                                        <span class="truncate">{{ selectedUserLabel }}</span>
                                        <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="z-200 w-(--reka-dropdown-menu-trigger-width) max-h-60 overflow-y-auto">
                                    <DropdownMenuRadioGroup v-model="form.user_id">
                                        <DropdownMenuRadioItem
                                            v-for="user in users"
                                            :key="user.id"
                                            :value="String(user.id)"
                                            class="text-xs"
                                        >
                                            {{ user.name }} ({{ user.email }})
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.user_id" class="text-[10px] text-red-500">{{ form.errors.user_id }}</p>
                        </div>

                        <!-- Role Selection -->
                        <div class="space-y-1.5">
                            <Label for="role-dropdown" class="text-xs">Select Role <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        id="role-dropdown"
                                        variant="outline"
                                        class="h-8 w-full justify-between text-xs font-normal"
                                    >
                                        <span class="truncate">{{ selectedRoleLabel }}</span>
                                        <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="z-200 w-(--reka-dropdown-menu-trigger-width) max-h-60 overflow-y-auto">
                                    <DropdownMenuRadioGroup v-model="form.role_id">
                                        <DropdownMenuRadioItem
                                            v-for="role in roles"
                                            :key="role.id"
                                            :value="String(role.id)"
                                            class="text-xs"
                                        >
                                            {{ role.name }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p v-if="form.errors.role_id" class="text-[10px] text-red-500">{{ form.errors.role_id }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="route('user-roles.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                            <Plus v-else class="h-3.5 w-3.5" />
                            Assign Role
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
