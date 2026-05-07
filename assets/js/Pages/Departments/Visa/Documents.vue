<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { FileStack } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    documentsOverview: {
        type: Object,
        default: () => ({ pending_requests_count: 0, recent_requests: [] }),
    },
});

const leadFullName = (lead) => (lead?.full_name ?? '—');
</script>

<template>
    <Head title="Visa Department - Documents" />
    <div class="container mx-auto py-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-primary px-4 py-2.5 text-primary-foreground shadow-md">
            <div class="relative z-10 flex items-center justify-between gap-2 flex-wrap">
                <div>
                    <h1 class="text-base font-semibold tracking-tight">Visa Department · Documents</h1>
                    <p class="mt-0.5 text-[11px] text-primary-foreground/90">Document requests and recent activity</p>
                </div>
                <nav class="flex flex-wrap gap-1">
                    <Link :href="route('departments.visa.index')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Dashboard</Link>
                    <Link :href="route('departments.visa.leads')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Leads</Link>
                    <span class="text-primary-foreground/50">·</span>
                    <span class="text-[11px] text-primary-foreground px-2 py-1">Documents</span>
                    <Link :href="route('departments.visa.analytics')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Analytics</Link>
                    <Link :href="route('departments.visa.exports')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Exports</Link>
                </nav>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-linear-to-l from-primary-foreground/10 to-transparent" aria-hidden="true" />
        </div>

        <div class="grid gap-2 md:grid-cols-2">
            <Card class="p-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-medium text-muted-foreground">Pending Document Requests</p>
                        <p class="text-sm font-semibold leading-tight">{{ documentsOverview.pending_requests_count ?? 0 }}</p>
                    </div>
                    <FileStack class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                </div>
            </Card>
            <Card class="p-2.5">
                <p class="text-[11px] text-muted-foreground">
                    Open a lead from the Leads page to request documents or download uploaded files.
                </p>
            </Card>
        </div>

        <Card>
            <CardHeader class="py-2 px-3">
                <CardTitle class="text-xs">Recent Document Requests</CardTitle>
                <CardDescription class="text-[11px]">Latest requests across visa department leads</CardDescription>
            </CardHeader>
            <CardContent class="pt-0 px-3 pb-3">
                <div v-if="documentsOverview.recent_requests?.length" class="space-y-1.5">
                    <div
                        v-for="req in documentsOverview.recent_requests"
                        :key="req.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-md border p-2 dark:border-border"
                    >
                        <div class="flex flex-col gap-0.5 min-w-0">
                            <span class="font-medium text-xs">{{ req.document_type }}</span>
                            <span class="text-[11px] text-muted-foreground">
                                {{ leadFullName(req.lead) }} · {{ req.requester?.name ?? '—' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <Badge :variant="req.status === 'pending' ? 'secondary' : 'success'" class="text-[10px] px-1.5 py-0">
                                {{ req.status }}
                            </Badge>
                            <Button v-if="req.lead" as-child variant="ghost" size="sm" class="h-7 text-xs">
                                <Link :href="route('departments.visa.show', req.lead.id)">View lead</Link>
                            </Button>
                        </div>
                    </div>
                </div>
                <p v-else class="py-6 text-center text-xs text-muted-foreground">
                    No recent document requests.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
