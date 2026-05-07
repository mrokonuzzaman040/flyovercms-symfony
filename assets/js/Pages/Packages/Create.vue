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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Checkbox } from '@/Components/ui/checkbox';
import { ArrowLeft, Plus, Loader2, Cog } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    services: { type: Array, required: true },
});

const form = useForm({
    name: '',
    total_price: '',
    currency: 'BDT',
    duration_days: '',
    is_active: '1',
    description: '',
    features: '',
    included_services: '',
    services: {},
});

const currencies = [
    { value: 'BDT', label: 'BDT' },
    { value: 'USD', label: 'USD' },
    { value: 'EUR', label: 'EUR' },
    { value: 'GBP', label: 'GBP' },
];

const selectedServices = ref({});

const toggleService = (serviceId, checked) => {
    if (checked) {
        selectedServices.value[serviceId] = {
            selected: '1',
            price: '',
            quantity: 1,
        };
    } else {
        delete selectedServices.value[serviceId];
    }
};

const submit = () => {
    form.services = selectedServices.value;
    form.post(route('packages.store'));
};
</script>

<template>
    <Head title="Create Package" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('packages.index')" 
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Create New Package</h1>
                <p class="text-xs text-muted-foreground">Add a new service package</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                        Package Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Package Name -->
                        <div class="space-y-1.5">
                            <Label for="name" class="text-xs">Package Name <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" class="h-8 text-xs" placeholder="Enter package name" />
                            <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Price -->
                        <div class="space-y-1.5">
                            <Label for="total_price" class="text-xs">Total Price <span class="text-red-500">*</span></Label>
                            <Input id="total_price" v-model="form.total_price" type="number" step="0.01" min="0" class="h-8 text-xs" placeholder="0.00" />
                            <p v-if="form.errors.total_price" class="text-[10px] text-red-500">{{ form.errors.total_price }}</p>
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
                    <div class="space-y-1.5 mt-4">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="3" class="text-xs resize-none" placeholder="Enter package description" />
                        <p v-if="form.errors.description" class="text-[10px] text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Features -->
                    <div class="space-y-1.5 mt-4">
                        <Label for="features" class="text-xs">Features (one per line)</Label>
                        <Textarea id="features" v-model="form.features" rows="3" class="text-xs resize-none" placeholder="Enter features, one per line" />
                        <p v-if="form.errors.features" class="text-[10px] text-red-500">{{ form.errors.features }}</p>
                    </div>

                    <!-- Included Services Text -->
                    <div class="space-y-1.5 mt-4">
                        <Label for="included_services" class="text-xs">Included Services Description (one per line)</Label>
                        <Textarea id="included_services" v-model="form.included_services" rows="3" class="text-xs resize-none" placeholder="Enter included services description" />
                        <p v-if="form.errors.included_services" class="text-[10px] text-red-500">{{ form.errors.included_services }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Services Selection -->
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                        Select Services
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <div v-if="services.length" class="space-y-3">
                        <div v-for="service in services" :key="service.id" class="flex items-center gap-3 p-2 border rounded-lg hover:bg-muted/30">
                            <Checkbox
                                :id="`service-${service.id}`"
                                :checked="!!selectedServices[service.id]"
                                @update:checked="(checked) => toggleService(service.id, checked)"
                            />
                            <div class="w-7 h-7 bg-gradient-to-br from-red-500 to-red-600 rounded flex items-center justify-center text-white shrink-0">
                                <Cog class="h-3.5 w-3.5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <label :for="`service-${service.id}`" class="text-xs font-medium cursor-pointer">{{ service.name }}</label>
                                <p class="text-[10px] text-muted-foreground">{{ service.currency }} {{ Number(service.price).toLocaleString() }}</p>
                            </div>
                            <div v-if="selectedServices[service.id]" class="flex items-center gap-2">
                                <div class="space-y-0.5">
                                    <Label class="text-[10px]">Price Override</Label>
                                    <Input v-model="selectedServices[service.id].price" type="number" step="0.01" class="h-7 w-24 text-xs" placeholder="Optional" />
                                </div>
                                <div class="space-y-0.5">
                                    <Label class="text-[10px]">Qty</Label>
                                    <Input v-model="selectedServices[service.id].quantity" type="number" min="1" class="h-7 w-16 text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-muted-foreground">
                        <Cog class="h-8 w-8 mx-auto mb-2 opacity-30" />
                        <p class="text-xs">No active services available</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button variant="outline" size="sm" as-child>
                    <Link :href="route('packages.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Plus v-else class="h-3.5 w-3.5" />
                    Create Package
                </Button>
            </div>
        </form>
    </div>
</template>
