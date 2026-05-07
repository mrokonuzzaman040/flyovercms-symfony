<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DeleteConfirmation } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Pencil, Trash2, Shield, Key, Users } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    role: { type: Object, required: true },
});

const deleteModal = ref({ open: false, loading: false });

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const handleDelete = () => {
    deleteModal.value.loading = true;
    router.delete(route('roles.destroy', props.role.id), {
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};

// Group permissions
const permissionsByGroup = () => {
    const groups = {};
    props.role.permissions?.forEach(p => {
        const group = p.group || 'General';
        if (!groups[group]) groups[group] = [];
        groups[group].push(p);
    });
    return groups;
};
</script>

<template>
    <Head :title="role.name" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('roles.index')"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ role.name }}</h1>
                    <p class="text-muted-foreground">{{ role.description || 'Role details and permissions' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="route('roles.edit', role.id)">
                        <Pencil class="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" @click="deleteModal.open = true">
                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Shield class="h-5 w-5 text-primary" />
                        <CardTitle>Role Info</CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="font-medium">{{ role.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Slug</p>
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ role.slug }}</code>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">{{ formatDate(role.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Users</p>
                        <Badge variant="secondary">{{ role.users?.length || 0 }} users</Badge>
                    </div>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Key class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Permissions</CardTitle>
                    </div>
                    <CardDescription>{{ role.permissions?.length || 0 }} permissions assigned</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="role.permissions?.length" class="space-y-4">
                        <div v-for="(permissions, group) in permissionsByGroup()" :key="group">
                            <h4 class="text-sm font-semibold mb-2">{{ group }}</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="permission in permissions" :key="permission.id" variant="outline">
                                    {{ permission.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground">No permissions assigned.</p>
                </CardContent>
            </Card>
        </div>

        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete Role"
            :description="`Are you sure you want to delete the '${role.name}' role? Users with this role will lose these permissions.`"
            @confirm="handleDelete"
        />
    </div>
</template>
