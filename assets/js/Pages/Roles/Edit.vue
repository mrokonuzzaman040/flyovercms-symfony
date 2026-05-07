<script setup>
import { ref, watch } from 'vue';
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
    role: { type: Object, required: true },
    permissions: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: props.role.name || '',
    slug: props.role.slug || '',
    description: props.role.description || '',
    permissions: props.role.permissions?.map(p => Number(p.id)) || [],
});

// Watch for prop changes to update form
watch(() => props.role, (newRole) => {
    if (newRole) {
        form.name = newRole.name || '';
        form.slug = newRole.slug || '';
        form.description = newRole.description || '';
        form.permissions = newRole.permissions?.map(p => Number(p.id)) || [];
    }
}, { deep: true, immediate: true });

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
    form.put(route('roles.update', props.role.id));
};
</script>

<template>
    <Head :title="`Edit ${role.name}`" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('roles.index')"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Role</h1>
                <p class="text-muted-foreground">Update {{ role.name }} role and permissions.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Role Details</CardTitle>
                    <CardDescription>Update the role information.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="e.g., Content Editor" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="e.g., content-editor" :class="{ 'border-destructive': form.errors.slug }" />
                            <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                        </div>
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
                    <CardDescription>Update the permissions for this role.</CardDescription>
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
                    Update Role
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link :href="route('roles.index')">Cancel</Link>
                </Button>
            </div>
        </form>

    </div>
</template>
