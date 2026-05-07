<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import { ArrowLeft, Loader2, FileText, Filter, Users, CheckCircle } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
    priorityOptions: { type: Array, default: () => [] },
    serviceTypes: { type: Object, default: () => ({}) },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    description: '',
    criteria: {
        status: [],
        package_id: [],
        service_id: [],
        branch_id: [],
        priority: [],
        service_type: [],
    },
    is_dynamic: false,
});

const previewSize = ref(null);
const isPreviewing = ref(false);

function toggleCriterion(key, value, checked) {
    const arr = Array.isArray(form.criteria[key]) ? [...form.criteria[key]] : [];
    if (checked) {
        if (!arr.includes(value)) arr.push(value);
    } else {
        const i = arr.indexOf(value);
        if (i !== -1) arr.splice(i, 1);
    }
    form.criteria = { ...form.criteria, [key]: arr };
}

function isCriterionSelected(key, value) {
    const arr = form.criteria[key];
    return Array.isArray(arr) && arr.includes(value);
}

const previewSegmentSize = async () => {
    isPreviewing.value = true;
    previewSize.value = null;
    try {
        const response = await fetch(route('marketing.segments.preview-size'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ criteria: form.criteria }),
        });
        const data = await response.json();
        previewSize.value = data.size ?? 0;
    } catch (error) {
        console.error('Error previewing segment size:', error);
    } finally {
        isPreviewing.value = false;
    }
};

const submit = () => {
    form.post(route('marketing.segments.store'));
};
</script>

<template>
    <Head title="Create Segment" />

    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.segments.index')"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 shrink-0"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create Segment</h1>
                <p class="text-xs text-muted-foreground">Define criteria to target specific leads</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Segment information -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <FileText class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Segment information</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Name, description and behaviour</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-xs font-medium">Segment name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. High-value leads" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-sm resize-none min-h-20 rounded-lg border-border/50" placeholder="Internal note" />
                        <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="is_dynamic" :checked="form.is_dynamic" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => form.is_dynamic = !!v" />
                        <Label for="is_dynamic" class="text-xs font-medium cursor-pointer">Dynamic segment (auto-updates when leads change)</Label>
                    </div>
                </CardContent>
            </Card>

            <!-- Criteria -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <Filter class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Segment criteria</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Leads matching any selected option in a group are included</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Status</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3">
                                <label v-for="opt in statusOptions" :key="opt" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('status', opt)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('status', opt, v)" />
                                    <span class="capitalize">{{ opt }}</span>
                                </label>
                                <p v-if="!statusOptions.length" class="text-xs text-muted-foreground">No options</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Package</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3 max-h-32 overflow-y-auto">
                                <label v-for="pkg in packages" :key="pkg.id" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('package_id', pkg.id)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('package_id', pkg.id, v)" />
                                    <span class="truncate">{{ pkg.name }}</span>
                                </label>
                                <p v-if="!packages.length" class="text-xs text-muted-foreground">No packages</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Service</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3 max-h-32 overflow-y-auto">
                                <label v-for="srv in services" :key="srv.id" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('service_id', srv.id)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('service_id', srv.id, v)" />
                                    <span class="truncate">{{ srv.name }}</span>
                                </label>
                                <p v-if="!services.length" class="text-xs text-muted-foreground">No services</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Branch</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3 max-h-32 overflow-y-auto">
                                <label v-for="branch in branches" :key="branch.id" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('branch_id', branch.id)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('branch_id', branch.id, v)" />
                                    <span class="truncate">{{ branch.name }}</span>
                                </label>
                                <p v-if="!branches.length" class="text-xs text-muted-foreground">No branches</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Priority</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3">
                                <label v-for="opt in priorityOptions" :key="opt" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('priority', opt)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('priority', opt, v)" />
                                    <span class="capitalize">{{ opt }}</span>
                                </label>
                                <p v-if="!priorityOptions.length" class="text-xs text-muted-foreground">No options</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Service type</Label>
                            <div class="flex flex-wrap gap-x-3 gap-y-2 rounded-lg border border-border/50 bg-muted/10 dark:bg-muted/5 p-3">
                                <label v-for="(label, value) in serviceTypes" :key="value" class="flex items-center gap-2 cursor-pointer text-xs">
                                    <Checkbox :checked="isCriterionSelected('service_type', value)" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => toggleCriterion('service_type', value, v)" />
                                    <span>{{ label }}</span>
                                </label>
                                <p v-if="!Object.keys(serviceTypes || {}).length" class="text-xs text-muted-foreground">No types</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-3">
                        <Button type="button" variant="outline" size="sm" class="gap-1.5 rounded-lg" :disabled="isPreviewing" @click="previewSegmentSize">
                            <Users class="h-3.5 w-3.5" />
                            Preview size
                        </Button>
                        <div v-if="isPreviewing" class="flex items-center gap-2 text-xs text-muted-foreground">
                            <Loader2 class="h-3.5 w-3.5 animate-spin" />
                            Counting…
                        </div>
                        <div v-else-if="previewSize !== null" class="flex items-center gap-2 text-xs">
                            <CheckCircle class="h-4 w-4 text-green-600 dark:text-green-500 shrink-0" />
                            <span class="text-muted-foreground">Approximately</span>
                            <span class="font-semibold">{{ previewSize }}</span>
                            <span class="text-muted-foreground">leads match this segment</span>
                        </div>
                    </div>
                    <p v-if="form.errors.criteria" class="text-xs text-red-500">{{ form.errors.criteria }}</p>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.segments.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" class="gap-1.5 shadow-sm" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <FileText v-else class="h-3.5 w-3.5" />
                    Create segment
                </Button>
            </div>
        </form>
    </div>
</template>
