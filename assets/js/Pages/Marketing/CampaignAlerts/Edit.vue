<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, ChevronDown } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    alert: { type: Object, required: true },
    campaigns: { type: Array, default: () => [] },
    alertTypes: { type: Object, required: true },
    metrics: { type: Object, required: true },
    operators: { type: Object, required: true },
    severities: { type: Object, required: true },
});

const form = useForm({
    campaign_id: props.alert.campaign_id,
    name: props.alert.name,
    description: props.alert.description || '',
    alert_type: props.alert.alert_type,
    metric: props.alert.metric,
    operator: props.alert.operator,
    threshold_value: props.alert.threshold_value,
    conditions: props.alert.conditions || {},
    severity: props.alert.severity,
    is_active: props.alert.is_active,
});

const submit = () => {
    form.put(route('marketing.campaign-alerts.update', props.alert.id));
};
</script>

<template>
    <Head :title="`Edit: ${alert.name}`" />

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.campaign-alerts.show', alert.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Campaign Alert</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Alert Information</CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs">Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="text-xs" required />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" class="text-xs" rows="3" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="campaign_id" class="text-xs">Campaign (Optional)</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between text-xs h-8">
                                    <span>{{ form.campaign_id ? campaigns.find(c => c.id === form.campaign_id)?.name || 'Select campaign' : 'All Campaigns' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup :model-value="form.campaign_id?.toString() || 'all'" @update:model-value="(v) => form.campaign_id = v === 'all' ? null : parseInt(v)">
                                    <DropdownMenuRadioItem value="all" class="text-xs">All Campaigns</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id.toString()" class="text-xs">
                                        {{ campaign.name }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="alert_type" class="text-xs">Type <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between text-xs h-8">
                                        <span>{{ alertTypes[form.alert_type] || 'Select type' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup :model-value="form.alert_type" @update:model-value="(v) => form.alert_type = v">
                                        <DropdownMenuRadioItem v-for="(label, value) in alertTypes" :key="value" :value="value" class="text-xs">
                                            {{ label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="severity" class="text-xs">Severity <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between text-xs h-8">
                                        <span>{{ severities[form.severity] || 'Select severity' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup :model-value="form.severity" @update:model-value="(v) => form.severity = v">
                                        <DropdownMenuRadioItem v-for="(label, value) in severities" :key="value" :value="value" class="text-xs">
                                            {{ label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <Label for="metric" class="text-xs">Metric <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between text-xs h-8">
                                        <span>{{ metrics[form.metric] || 'Select metric' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup :model-value="form.metric" @update:model-value="(v) => form.metric = v">
                                        <DropdownMenuRadioItem v-for="(label, value) in metrics" :key="value" :value="value" class="text-xs">
                                            {{ label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="operator" class="text-xs">Operator <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between text-xs h-8">
                                        <span>{{ operators[form.operator] || 'Select operator' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup :model-value="form.operator" @update:model-value="(v) => form.operator = v">
                                        <DropdownMenuRadioItem v-for="(label, value) in operators" :key="value" :value="value" class="text-xs">
                                            {{ label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="threshold_value" class="text-xs">Threshold <span class="text-red-500">*</span></Label>
                            <Input id="threshold_value" v-model.number="form.threshold_value" type="number" step="0.01" class="text-xs" required />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.campaign-alerts.show', alert.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing">Update</Button>
            </div>
        </form>
    </div>
</template>
