<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Switch } from '@/Components/ui/switch';
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    vendor: { type: Object, required: true },
});

const form = useForm({
    name: props.vendor.name || '',
    email: props.vendor.email || '',
    phone: props.vendor.phone || '',
    alternate_phone: props.vendor.alternate_phone || '',
    address: props.vendor.address || '',
    city: props.vendor.city || '',
    state: props.vendor.state || '',
    country: props.vendor.country || '',
    postal_code: props.vendor.postal_code || '',
    notes: props.vendor.notes || '',
    is_active: props.vendor.is_active ?? true,
});

const submit = () => {
    form.put(route('vendors.update', props.vendor.id));
};
</script>

<template>
    <Head title="Edit Vendor" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="outline" size="icon" class="h-8 w-8" as-child>
                <Link :href="route('vendors.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Vendor</h1>
                <p class="text-xs text-muted-foreground">Update vendor information</p>
            </div>
        </div>

        <!-- Form -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded" />
                    Vendor Information
                </CardTitle>
            </CardHeader>
            <CardContent class="p-4">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <Label for="name" class="text-xs">Vendor Name <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" class="h-8 text-xs" placeholder="Enter vendor name" />
                            <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <Label for="email" class="text-xs">Email</Label>
                            <Input id="email" v-model="form.email" type="email" class="h-8 text-xs" placeholder="vendor@example.com" />
                            <p v-if="form.errors.email" class="text-[10px] text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1.5">
                            <Label for="phone" class="text-xs">Phone</Label>
                            <Input id="phone" v-model="form.phone" class="h-8 text-xs" placeholder="+880 1234 567890" />
                            <p v-if="form.errors.phone" class="text-[10px] text-red-500">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Alternate Phone -->
                        <div class="space-y-1.5">
                            <Label for="alternate_phone" class="text-xs">Alternate Phone</Label>
                            <Input id="alternate_phone" v-model="form.alternate_phone" class="h-8 text-xs" placeholder="+880 1234 567890" />
                            <p v-if="form.errors.alternate_phone" class="text-[10px] text-red-500">{{ form.errors.alternate_phone }}</p>
                        </div>

                        <!-- City -->
                        <div class="space-y-1.5">
                            <Label for="city" class="text-xs">City</Label>
                            <Input id="city" v-model="form.city" class="h-8 text-xs" placeholder="Enter city" />
                            <p v-if="form.errors.city" class="text-[10px] text-red-500">{{ form.errors.city }}</p>
                        </div>

                        <!-- State -->
                        <div class="space-y-1.5">
                            <Label for="state" class="text-xs">State/Province</Label>
                            <Input id="state" v-model="form.state" class="h-8 text-xs" placeholder="Enter state" />
                            <p v-if="form.errors.state" class="text-[10px] text-red-500">{{ form.errors.state }}</p>
                        </div>

                        <!-- Country -->
                        <div class="space-y-1.5">
                            <Label for="country" class="text-xs">Country</Label>
                            <Input id="country" v-model="form.country" class="h-8 text-xs" placeholder="Enter country" />
                            <p v-if="form.errors.country" class="text-[10px] text-red-500">{{ form.errors.country }}</p>
                        </div>

                        <!-- Postal Code -->
                        <div class="space-y-1.5">
                            <Label for="postal_code" class="text-xs">Postal Code</Label>
                            <Input id="postal_code" v-model="form.postal_code" class="h-8 text-xs" placeholder="Enter postal code" />
                            <p v-if="form.errors.postal_code" class="text-[10px] text-red-500">{{ form.errors.postal_code }}</p>
                        </div>

                        <!-- Status -->
                        <div class="space-y-1.5">
                            <Label class="text-xs">Status</Label>
                            <div class="flex items-center gap-2 h-8">
                                <Switch v-model:checked="form.is_active" />
                                <span class="text-xs">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="space-y-1.5">
                        <Label for="address" class="text-xs">Address</Label>
                        <Textarea id="address" v-model="form.address" rows="2" class="text-xs resize-none" placeholder="Enter full address" />
                        <p v-if="form.errors.address" class="text-[10px] text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <Label for="notes" class="text-xs">Notes</Label>
                        <Textarea id="notes" v-model="form.notes" rows="3" class="text-xs resize-none" placeholder="Any additional notes..." />
                        <p v-if="form.errors.notes" class="text-[10px] text-red-500">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="route('vendors.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5">
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                            <Save v-else class="h-3.5 w-3.5" />
                            Update Vendor
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
