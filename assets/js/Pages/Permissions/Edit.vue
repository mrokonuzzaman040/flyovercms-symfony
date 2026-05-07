<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    permission: { type: Object, required: true },
    groups: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.permission.name || '',
    slug: props.permission.slug || '',
    description: props.permission.description || '',
    group: props.permission.group || '',
});

const submit = () => {
    form.put(route('permissions.update', props.permission.id));
};
</script>

<template>
    <Head :title="`Edit ${permission.name}`" />
    
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('permissions.index')"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Permission</h1>
                <p class="text-muted-foreground">Update {{ permission.name }} permission.</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Permission Details</CardTitle>
                    <CardDescription>Update the permission information.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="e.g., View Users" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="e.g., view-users" :class="{ 'border-destructive': form.errors.slug }" />
                            <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="group">Group</Label>
                        <Input id="group" v-model="form.group" placeholder="e.g., Users" list="groups" />
                        <datalist id="groups">
                            <option v-for="group in groups" :key="group" :value="group" />
                        </datalist>
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Describe what this permission allows..." rows="2" />
                    </div>
                    <div class="flex items-center gap-4 pt-4">
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Update Permission
                        </Button>
                        <Button variant="outline" type="button" as-child>
                            <Link :href="route('permissions.index')">Cancel</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
