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
});

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    trigger_type: props.campaign.trigger_type,
    channel: props.campaign.channel,
    message_template: props.campaign.message_template,
    trigger_conditions: props.campaign.trigger_conditions || {},
    is_active: props.campaign.is_active,
});

const submit = () => {
    form.put(route('marketing.automated-campaigns.update', props.campaign.id));
};
</script>

<template>
    <Head title="Edit Automated Campaign" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.automated-campaigns.show', campaign.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Automated Campaign</h1>
                <p class="text-xs text-muted-foreground">Update campaign configuration</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                        Campaign Configuration
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
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="channel" class="text-xs">Channel <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-8 text-xs">
                                        <span>{{ form.channel === 'sms' ? 'SMS' : form.channel === 'email' ? 'Email' : form.channel === 'whatsapp' ? 'WhatsApp' : 'Select channel' }}</span>
                                        <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.channel">
                                        <DropdownMenuRadioItem value="sms" class="text-xs">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-xs">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-xs">WhatsApp</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">WhatsApp is sent via WasenderAPI.</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="message_template" class="text-xs">Message Template <span class="text-red-500">*</span></Label>
                        <Textarea id="message_template" v-model="form.message_template" rows="4" class="text-xs resize-none" />
                        <p v-if="form.errors.message_template" class="text-[10px] text-red-500">{{ form.errors.message_template }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.automated-campaigns.show', campaign.id)">Cancel</Link>
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
