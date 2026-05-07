<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { ArrowLeft, Save, Loader2, Shield } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    user_role: { type: Object, required: true },
    roles: { type: Array, required: true },
});

// Get current role IDs - ensure they're all numbers
const currentRoleIds = computed(() => {
    return props.user_role.roles?.map(r => Number(r.id)) || [];
});

// Initialize selectedRoles with numbers
const selectedRoles = ref(currentRoleIds.value.map(id => Number(id)));

const form = useForm({
    roles: currentRoleIds.value.map(id => Number(id)),
});

// Watch for prop changes and update selectedRoles
watch(() => props.user_role.roles, (newRoles) => {
    const newRoleIds = newRoles?.map(r => Number(r.id)) || [];
    selectedRoles.value = [...newRoleIds];
    form.roles = [...newRoleIds];
}, { deep: true, immediate: true });

const toggleRole = (roleId, checked) => {
    const roleIdNum = Number(roleId);
    
    if (checked) {
        if (!selectedRoles.value.includes(roleIdNum)) {
            selectedRoles.value.push(roleIdNum);
        }
    } else {
        selectedRoles.value = selectedRoles.value.filter(id => Number(id) !== roleIdNum);
    }
    
    // Sync form.roles with selectedRoles immediately
    form.roles = [...selectedRoles.value];
};

const submit = () => {
    form.roles = selectedRoles.value;
    form.put(route('user-roles.update', props.user_role.id), {
        preserveState: false,
        preserveScroll: false,
    });
};
</script>

<template>
    <Head title="Edit User Roles" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                <Link :href="route('user-roles.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit User Roles</h1>
                <p class="text-xs text-muted-foreground">Update roles for {{ user_role.name }}</p>
            </div>
        </div>

        <!-- Form -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded" />
                    Select Roles
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-3">
                        <div v-if="!roles || roles.length === 0" class="text-center py-8 text-sm text-muted-foreground">
                            No roles available
                        </div>
                        <div v-for="role in roles" :key="role.id" class="flex items-center gap-3 p-3 border rounded-lg hover:bg-muted/30">
                            <Checkbox
                                :id="`role-${role.id}`"
                                :model-value="selectedRoles.includes(Number(role.id))"
                                @update:model-value="(checked) => toggleRole(Number(role.id), checked)"
                            />
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center shrink-0">
                                <Shield class="h-4 w-4 text-green-600" />
                            </div>
                            <div class="flex-1">
                                <label :for="`role-${role.id}`" class="text-xs font-medium cursor-pointer">{{ role.name }}</label>
                                <p v-if="role.description" class="text-[10px] text-muted-foreground">{{ role.description }}</p>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.roles" class="text-[10px] text-red-500">{{ form.errors.roles }}</p>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="route('user-roles.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                            <Save v-else class="h-3.5 w-3.5" />
                            Update Roles
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
