<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Loader2, Save, Users, CheckCircle } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    segment: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
    priorityOptions: { type: Array, default: () => [] },
    serviceTypes: { type: Object, default: () => ({}) },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.segment.name,
    description: props.segment.description || '',
    criteria: props.segment.criteria || {},
    is_dynamic: props.segment.is_dynamic,
});

const previewSize = ref(null);
const isPreviewing = ref(false);

const previewSegmentSize = async () => {
    isPreviewing.value = true;
    try {
        const response = await fetch(route('marketing.segments.preview-size'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({ criteria: form.criteria }),
        });
        const data = await response.json();
        previewSize.value = data.size;
    } catch (error) {
        console.error('Error previewing segment size:', error);
    } finally {
        isPreviewing.value = false;
    }
};

const submit = () => {
    form.put(route('marketing.segments.update', props.segment.id));
};
</script>

<template>
    <Head title="Edit Segment" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.segments.show', segment.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Segment</h1>
                <p class="text-xs text-muted-foreground">Update segment criteria and settings</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                        Segment Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Name -->
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs">Segment Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-8 text-xs" />
                        <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-xs resize-none" />
                        <p v-if="form.errors.description" class="text-[10px] text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Dynamic Segment -->
                    <div class="flex items-center gap-2">
                        <input
                            id="is_dynamic"
                            v-model="form.is_dynamic"
                            type="checkbox"
                            class="h-4 w-4 rounded border-input"
                        />
                        <Label for="is_dynamic" class="text-xs cursor-pointer">Dynamic Segment (auto-updates)</Label>
                    </div>

                    <!-- Criteria Builder -->
                    <div class="space-y-1.5">
                        <Label class="text-xs">Segment Criteria</Label>
                        <p class="text-[10px] text-muted-foreground mb-2">
                            Segment criteria builder will be implemented here. For now, criteria is stored as JSON.
                        </p>
                        <div class="border rounded-md p-3 bg-muted/30">
                            <pre class="text-xs overflow-auto">{{ JSON.stringify(form.criteria, null, 2) }}</pre>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="outline" size="sm" class="gap-1.5" @click="previewSegmentSize" :disabled="isPreviewing">
                            <Users class="h-3.5 w-3.5" />
                            Preview Size
                        </Button>
                        <div v-if="previewSize !== null" class="flex items-center gap-1.5 text-xs">
                            <CheckCircle class="h-3.5 w-3.5 text-green-600" />
                            <span class="text-muted-foreground">Approximately</span>
                            <span class="font-semibold">{{ previewSize }}</span>
                            <span class="text-muted-foreground">leads match this segment</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.segments.show', segment.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Save v-else class="h-3.5 w-3.5" />
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>
