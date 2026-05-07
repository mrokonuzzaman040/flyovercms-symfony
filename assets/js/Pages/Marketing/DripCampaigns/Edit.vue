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
    dripCampaign: { type: Object, required: true },
    segments: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.dripCampaign.name,
    description: props.dripCampaign.description || '',
    segment_id: props.dripCampaign.segment_id,
    is_active: props.dripCampaign.is_active,
});

const submit = () => {
    form.put(route('marketing.drip-campaigns.update', props.dripCampaign.id));
};
</script>

<template>
    <Head :title="`Edit: ${dripCampaign.name}`" />

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.drip-campaigns.show', dripCampaign.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Drip Campaign</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm">Campaign Information</CardTitle>
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
                        <Label for="segment_id" class="text-xs">Segment</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between text-xs h-8">
                                    <span>{{ form.segment_id ? segments.find(s => s.id === form.segment_id)?.name || 'Select segment' : 'All Leads' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup :model-value="form.segment_id?.toString() || 'all'" @update:model-value="(v) => form.segment_id = v === 'all' ? null : parseInt(v)">
                                    <DropdownMenuRadioItem value="all" class="text-xs">All Leads</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem v-for="segment in segments" :key="segment.id" :value="segment.id.toString()" class="text-xs">
                                        {{ segment.name }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.drip-campaigns.show', dripCampaign.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing">Update</Button>
            </div>
        </form>
    </div>
</template>
