<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';

defineOptions({ layout: DashboardLayout });
</script>

<template>
    <Head title="Visa Department - Exports" />
    <div class="container mx-auto py-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-primary px-4 py-2.5 text-primary-foreground shadow-md">
            <div class="relative z-10 flex items-center justify-between gap-2 flex-wrap">
                <div>
                    <h1 class="text-base font-semibold tracking-tight">Visa Department · Exports</h1>
                    <p class="mt-0.5 text-[11px] text-primary-foreground/90">Export visa department data</p>
                </div>
                <nav class="flex flex-wrap gap-1">
                    <Link :href="route('departments.visa.index')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Dashboard</Link>
                    <Link :href="route('departments.visa.leads')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Leads</Link>
                    <Link :href="route('departments.visa.documents')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Documents</Link>
                    <Link :href="route('departments.visa.analytics')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Analytics</Link>
                    <span class="text-primary-foreground/50">·</span>
                    <span class="text-[11px] text-primary-foreground px-2 py-1">Exports</span>
                </nav>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-linear-to-l from-primary-foreground/10 to-transparent" aria-hidden="true" />
        </div>

        <Card>
            <CardHeader class="py-2 px-3">
                <CardTitle class="text-xs">Exports</CardTitle>
                <CardDescription class="text-[11px]">Export visa department data for reporting or backup</CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col items-center justify-center gap-2 py-6 px-3">
                <p class="text-xs text-muted-foreground text-center">
                    Use "Export PDF" or "Download Documents" on individual leads from the
                    <Link :href="route('departments.visa.leads')" class="text-primary hover:underline">Leads</Link>
                    page. Bulk export options can be added here later.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
