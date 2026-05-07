<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { FileText, FileStack, BarChart3, FileDown, ChevronRight } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    stats: { type: Object, default: () => ({ total: 0, pending: 0 }) },
});

const sections = [
    { title: 'Leads', description: 'View and filter visa department leads', href: 'departments.visa.leads', icon: FileText },
    { title: 'Documents', description: 'Document requests and uploads', href: 'departments.visa.documents', icon: FileStack },
    { title: 'Analytics', description: 'Status and service type charts', href: 'departments.visa.analytics', icon: BarChart3 },
    { title: 'Exports', description: 'Export options and bulk download', href: 'departments.visa.exports', icon: FileDown },
];
</script>

<template>
    <Head title="Visa Department" />
    <div class="container mx-auto py-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-primary px-4 py-2.5 text-primary-foreground shadow-md">
            <div class="relative z-10">
                <h1 class="text-base font-semibold tracking-tight">Visa Department</h1>
                <p class="mt-0.5 text-[11px] text-primary-foreground/90">
                    Manage applications, documents, analytics, and exports.
                </p>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-linear-to-l from-primary-foreground/10 to-transparent" aria-hidden="true" />
        </div>

        <div class="grid gap-2 sm:grid-cols-2">
            <Card class="p-2.5 border-l-4 border-l-primary">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-medium text-muted-foreground">Total leads</p>
                        <p class="text-sm font-semibold leading-tight">{{ stats?.total ?? 0 }}</p>
                    </div>
                    <FileText class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                </div>
            </Card>
            <Card class="p-2.5 border-l-4 border-l-muted-foreground/50">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-medium text-muted-foreground">Pending</p>
                        <p class="text-sm font-semibold leading-tight">{{ stats?.pending ?? 0 }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <Link v-for="section in sections" :key="section.href" :href="route(section.href)">
                <Card class="p-2.5 transition-all hover:shadow-sm hover:border-primary/50 h-full">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <component :is="section.icon" class="h-4 w-4 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-xs">{{ section.title }}</p>
                                <p class="text-[11px] text-muted-foreground truncate">{{ section.description }}</p>
                            </div>
                        </div>
                        <ChevronRight class="h-4 w-4 text-muted-foreground shrink-0" />
                    </div>
                </Card>
            </Link>
        </div>
    </div>
</template>
