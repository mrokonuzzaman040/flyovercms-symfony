<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { DeleteConfirmation } from '@/Components/shared';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Pencil, Trash2, Key, Shield } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    permission: { type: Object, required: true },
});

const deleteModal = ref({ open: false, loading: false });

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const handleDelete = () => {
    deleteModal.value.loading = true;
    router.delete(route('permissions.destroy', props.permission.id), {
        onSuccess: () => { deleteModal.value.open = false; },
        onFinish: () => { deleteModal.value.loading = false; },
    });
};
</script>

<template>
    <Head :title="permission.name" />
    
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('permissions.index')"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ permission.name }}</h1>
                    <p class="text-muted-foreground">{{ permission.description || 'Permission details' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="route('permissions.edit', permission.id)">
                        <Pencil class="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" @click="deleteModal.open = true">
                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Key class="h-5 w-5 text-primary" />
                        <CardTitle>Permission Info</CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="font-medium">{{ permission.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Slug</p>
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ permission.slug }}</code>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Group</p>
                        <Badge v-if="permission.group" variant="outline">{{ permission.group }}</Badge>
                        <span v-else class="text-muted-foreground">No group</span>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">{{ formatDate(permission.created_at) }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Shield class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Assigned Roles</CardTitle>
                    </div>
                    <CardDescription>Roles that have this permission.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="permission.roles?.length" class="flex flex-wrap gap-2">
                        <Badge v-for="role in permission.roles" :key="role.id" variant="secondary">
                            {{ role.name }}
                        </Badge>
                    </div>
                    <p v-else class="text-muted-foreground">Not assigned to any roles.</p>
                </CardContent>
            </Card>
        </div>

        <DeleteConfirmation
            v-model:open="deleteModal.open"
            :loading="deleteModal.loading"
            title="Delete Permission"
            :description="`Are you sure you want to delete the '${permission.name}' permission? This will remove it from all roles.`"
            @confirm="handleDelete"
        />
    </div>
</template>
