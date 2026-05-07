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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, Loader2, Save, ChevronDown } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    type: props.campaign.type,
    message: props.campaign.message,
    scheduled_at: props.campaign.scheduled_at ? new Date(props.campaign.scheduled_at).toISOString().slice(0, 16) : '',
    package_id: props.campaign.package_id,
    service_id: props.campaign.service_id,
    target_criteria: props.campaign.target_criteria || {},
});

const submit = () => {
    form.put(route('marketing.campaigns.update', props.campaign.id));
};
</script>

<template>
    <Head title="Edit Campaign" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.campaigns.show', campaign.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Campaign</h1>
                <p class="text-xs text-muted-foreground">Update campaign details</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                        Campaign Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs">Campaign Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-8 text-xs" />
                        <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-xs resize-none" />
                        <p v-if="form.errors.description" class="text-[10px] text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="type" class="text-xs">Channel <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-8 text-xs">
                                        <span>{{ form.type === 'sms' ? 'SMS' : form.type === 'email' ? 'Email' : form.type === 'whatsapp' ? 'WhatsApp' : 'Select channel' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.type">
                                        <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-xs">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">WhatsApp is sent via WasenderAPI.</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="scheduled_at" class="text-xs">Schedule</Label>
                            <Input id="scheduled_at" v-model="form.scheduled_at" type="datetime-local" class="h-8 text-xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="package_id" class="text-xs">Target Package</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-8 text-xs">
                                        <span>{{ form.package_id ? packages.find(p => p.id === form.package_id)?.name || 'Select package' : 'All Packages' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.package_id">
                                        <DropdownMenuRadioItem :value="null" class="text-xs">All Packages</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="pkg in packages" :key="pkg.id" :value="pkg.id" class="text-xs">
                                            {{ pkg.name }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="service_id" class="text-xs">Target Service</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-8 text-xs">
                                        <span>{{ form.service_id ? services.find(s => s.id === form.service_id)?.name || 'Select service' : 'All Services' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.service_id">
                                        <DropdownMenuRadioItem :value="null" class="text-xs">All Services</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="service in services" :key="service.id" :value="service.id" class="text-xs">
                                            {{ service.name }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="message" class="text-xs">Message <span class="text-red-500">*</span></Label>
                        <Textarea id="message" v-model="form.message" rows="4" class="text-xs resize-none" />
                        <p class="text-[10px] text-muted-foreground">Available variables: {{name}}, {{first_name}}, {{phone}}, {{email}}</p>
                        <p v-if="form.errors.message" class="text-[10px] text-red-500">{{ form.errors.message }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.campaigns.show', campaign.id)">Cancel</Link>
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
