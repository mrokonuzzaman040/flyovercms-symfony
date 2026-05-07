<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Plus, Trash2, ChevronDown } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
    orchestrationTypes: { type: Object, required: true },
    channels: { type: Array, required: true },
});

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    orchestration_type: props.campaign.orchestration_type,
    channel_sequence: [...(props.campaign.channel_sequence || [])],
    routing_rules: props.campaign.routing_rules || {},
    fallback_rules: props.campaign.fallback_rules || {},
    is_active: props.campaign.is_active,
});

const addChannel = () => {
    form.channel_sequence.push('sms');
};

const removeChannel = (index) => {
    form.channel_sequence.splice(index, 1);
};

const submit = () => {
    form.put(route('marketing.multi-channel-campaigns.update', props.campaign.id));
};
</script>

<template>
    <Head :title="`Edit: ${campaign.name}`" />

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.multi-channel-campaigns.show', campaign.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Multi-Channel Campaign</h1>
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
                        <Label for="orchestration_type" class="text-xs">Type <span class="text-red-500">*</span></Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between text-xs h-8">
                                    <span>{{ orchestrationTypes[form.orchestration_type] || 'Select type' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup :model-value="form.orchestration_type" @update:model-value="(v) => form.orchestration_type = v">
                                    <DropdownMenuRadioItem v-for="(label, value) in orchestrationTypes" :key="value" :value="value" class="text-xs">
                                        {{ label }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs">Channel Sequence <span class="text-red-500">*</span></Label>
                        <div class="space-y-2">
                            <div v-for="(channel, index) in form.channel_sequence" :key="index" class="flex items-center gap-2">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="flex-1 justify-between text-xs h-8">
                                            <span class="uppercase">{{ channel }}</span>
                                            <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                        <DropdownMenuRadioGroup :model-value="channel" @update:model-value="(v) => form.channel_sequence[index] = v">
                                            <DropdownMenuRadioItem v-for="ch in channels" :key="ch" :value="ch" class="text-xs uppercase">
                                                {{ ch }}
                                            </DropdownMenuRadioItem>
                                        </DropdownMenuRadioGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                                <Button v-if="form.channel_sequence.length > 1" type="button" variant="ghost" size="icon" class="h-7 w-7" @click="removeChannel(index)">
                                    <Trash2 class="h-3.5 w-3.5 text-red-600" />
                                </Button>
                            </div>
                            <Button type="button" variant="outline" size="sm" class="gap-1.5" @click="addChannel">
                                <Plus class="h-3.5 w-3.5" />
                                Add Channel
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.multi-channel-campaigns.show', campaign.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing">Update</Button>
            </div>
        </form>
    </div>
</template>
