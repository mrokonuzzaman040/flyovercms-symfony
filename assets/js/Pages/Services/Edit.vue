<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    service: { type: Object, required: true },
    serviceTypes: { type: Object, required: true },
});

const form = useForm({
    name: props.service.name || '',
    type: props.service.type || 'visa',
    price: props.service.price || '',
    currency: props.service.currency || 'BDT',
    duration_days: props.service.duration_days || '',
    is_active: props.service.is_active ? '1' : '0',
    description: props.service.description || '',
    features: Array.isArray(props.service.features) ? props.service.features.join('\n') : (props.service.features || ''),
    requirements: Array.isArray(props.service.requirements) ? props.service.requirements.join('\n') : (props.service.requirements || ''),
});

const currencies = [
    { value: 'BDT', label: 'BDT' },
    { value: 'USD', label: 'USD' },
    { value: 'EUR', label: 'EUR' },
    { value: 'GBP', label: 'GBP' },
];

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>

<template>
    <Head title="Edit Service" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('services.index')" 
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Service</h1>
                <p class="text-xs text-muted-foreground">Update service information</p>
            </div>
        </div>

        <!-- Form -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                    Service Information
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Service Name -->
                        <div class="space-y-1.5">
                            <Label for="name" class="text-xs">Service Name <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" class="h-8 text-xs" placeholder="Enter service name" />
                            <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Service Type -->
                        <div class="space-y-1.5">
                            <Label for="type" class="text-xs">Service Type <span class="text-red-500">*</span></Label>
                            <Select v-model="form.type">
                                <SelectTrigger class="h-8 text-xs">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in serviceTypes" :key="value" :value="value" class="text-xs">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.type" class="text-[10px] text-red-500">{{ form.errors.type }}</p>
                        </div>

                        <!-- Price -->
                        <div class="space-y-1.5">
                            <Label for="price" class="text-xs">Price</Label>
                            <Input id="price" v-model="form.price" type="number" step="0.01" min="0" class="h-8 text-xs" placeholder="0.00" />
                            <p v-if="form.errors.price" class="text-[10px] text-red-500">{{ form.errors.price }}</p>
                        </div>

                        <!-- Currency -->
                        <div class="space-y-1.5">
                            <Label for="currency" class="text-xs">Currency <span class="text-red-500">*</span></Label>
                            <Select v-model="form.currency">
                                <SelectTrigger class="h-8 text-xs">
                                    <SelectValue placeholder="Select currency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="curr in currencies" :key="curr.value" :value="curr.value" class="text-xs">
                                        {{ curr.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.currency" class="text-[10px] text-red-500">{{ form.errors.currency }}</p>
                        </div>

                        <!-- Duration -->
                        <div class="space-y-1.5">
                            <Label for="duration_days" class="text-xs">Duration (Nights)</Label>
                            <Input id="duration_days" v-model="form.duration_days" type="number" min="1" class="h-8 text-xs" placeholder="Enter duration" />
                            <p v-if="form.errors.duration_days" class="text-[10px] text-red-500">{{ form.errors.duration_days }}</p>
                        </div>

                        <!-- Status -->
                        <div class="space-y-1.5">
                            <Label for="is_active" class="text-xs">Status</Label>
                            <Select v-model="form.is_active">
                                <SelectTrigger class="h-8 text-xs">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1" class="text-xs">Active</SelectItem>
                                    <SelectItem value="0" class="text-xs">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.is_active" class="text-[10px] text-red-500">{{ form.errors.is_active }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="3" class="text-xs resize-none" placeholder="Enter service description" />
                        <p v-if="form.errors.description" class="text-[10px] text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Features -->
                    <div class="space-y-1.5">
                        <Label for="features" class="text-xs">Features (one per line)</Label>
                        <Textarea id="features" v-model="form.features" rows="3" class="text-xs resize-none" placeholder="Enter features, one per line" />
                        <p v-if="form.errors.features" class="text-[10px] text-red-500">{{ form.errors.features }}</p>
                    </div>

                    <!-- Requirements -->
                    <div class="space-y-1.5">
                        <Label for="requirements" class="text-xs">Requirements (one per line)</Label>
                        <Textarea id="requirements" v-model="form.requirements" rows="3" class="text-xs resize-none" placeholder="Enter requirements, one per line" />
                        <p v-if="form.errors.requirements" class="text-[10px] text-red-500">{{ form.errors.requirements }}</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="route('services.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                            <Save v-else class="h-3.5 w-3.5" />
                            Update Service
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
