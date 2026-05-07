<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { FileText } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });
</script>

<template>
    <Head title="Visa Department" />

    <div class="container mx-auto py-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Visa Department</h1>
                <p class="text-muted-foreground mt-1">
                    Manage visa-related operations and processes
                </p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5" />
                    <CardTitle>Visa Department</CardTitle>
                </div>
                <CardDescription>
                    This section is under development. Content will be added here.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <p class="text-sm text-muted-foreground">
                    The visa department page is coming soon.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
