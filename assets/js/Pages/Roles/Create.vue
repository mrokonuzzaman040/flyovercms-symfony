<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    permissions: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: '',
    description: '',
    permissions: [],
});

const togglePermission = (permissionId) => {
    const permissionIdNum = Number(permissionId);
    const index = form.permissions.indexOf(permissionIdNum);
    if (index === -1) {
        form.permissions.push(permissionIdNum);
    } else {
        form.permissions.splice(index, 1);
    }
};

const isPermissionSelected = (permissionId) => form.permissions.includes(Number(permissionId));

const toggleGroupPermissions = (permissions) => {
    const allSelected = permissions.every(p => form.permissions.includes(Number(p.id)));
    if (allSelected) {
        permissions.forEach(p => {
            const idx = form.permissions.indexOf(Number(p.id));
            if (idx !== -1) form.permissions.splice(idx, 1);
        });
    } else {
        permissions.forEach(p => {
            const permissionId = Number(p.id);
            if (!form.permissions.includes(permissionId)) form.permissions.push(permissionId);
        });
    }
};

const isGroupSelected = (permissions) => permissions.every(p => form.permissions.includes(Number(p.id)));

const submit = () => {
    // Ensure all permission IDs are numbers and remove duplicates
    form.permissions = [...new Set(form.permissions.map(id => Number(id)))];
    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('roles.index')"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Create Role</h1>
                <p class="text-muted-foreground">Add a new role with specific permissions.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Role Details</CardTitle>
                    <CardDescription>Enter the role information.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g., Content Editor" :class="{ 'border-destructive': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Describe this role..." rows="2" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Permissions</CardTitle>
                    <CardDescription>Select the permissions for this role.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-6">
                        <div v-for="(groupPermissions, group) in permissions" :key="group" class="space-y-3">
                            <div class="flex items-center gap-2">
                                <Checkbox :checked="isGroupSelected(groupPermissions)" @update:checked="toggleGroupPermissions(groupPermissions)" />
                                <Label class="text-sm font-semibold cursor-pointer" @click="toggleGroupPermissions(groupPermissions)">
                                    {{ group || 'General' }}
                                </Label>
                            </div>
                            <div class="ml-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="permission in groupPermissions" :key="permission.id" class="flex items-center space-x-2">
                                    <Checkbox :id="`perm-${permission.id}`" :checked="isPermissionSelected(permission.id)" @update:checked="togglePermission(Number(permission.id))" />
                                    <Label :for="`perm-${permission.id}`" class="text-sm cursor-pointer">{{ permission.name }}</Label>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Create Role
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('roles.index')">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
