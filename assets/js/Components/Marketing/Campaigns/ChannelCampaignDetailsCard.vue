<script setup>
import { computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { ChevronDown, FileText } from 'lucide-vue-next';
import { resolveChannelCampaignAccent } from '@/Components/Marketing/Campaigns/channelCampaignAccents';

const props = defineProps({
    form: { type: Object, required: true },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    accent: { type: String, default: 'sky' },
    title: { type: String, default: 'Campaign Information' },
    description: { type: String, default: 'Name, schedule, and package or service targeting' },
    targetingHint: {
        type: String,
        default: 'When specific leads are selected below, they override package and service targeting for delivery.',
    },
    namePlaceholder: { type: String, default: '' },
    descriptionPlaceholder: { type: String, default: '' },
});

const accentClasses = computed(() => resolveChannelCampaignAccent(props.accent));
</script>

<template>
    <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
        <CardHeader class="border-b border-border/50 bg-muted/20 px-4 py-4 dark:bg-muted/10">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="accentClasses.iconContainer">
                    <FileText class="h-5 w-5" :class="accentClasses.iconText" />
                </div>
                <div>
                    <CardTitle class="text-sm font-semibold tracking-tight">{{ title }}</CardTitle>
                    <p class="mt-0.5 text-xs text-muted-foreground">{{ description }}</p>
                </div>
            </div>
        </CardHeader>
        <CardContent class="space-y-5 p-4">
            <div class="space-y-4">
                <Label for="name" class="text-xs font-medium">Campaign name <span class="text-red-500">*</span></Label>
                <Input id="name" v-model="form.name" class="h-10 rounded-lg border-border/50 text-sm" :placeholder="namePlaceholder" />
                <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
                <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    rows="2"
                    class="min-h-20 resize-none rounded-lg border-border/50 text-sm"
                    :placeholder="descriptionPlaceholder"
                />
                <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
            </div>

            <div class="space-y-2">
                <Label for="scheduled_at" class="text-xs font-medium">Schedule (optional)</Label>
                <Input id="scheduled_at" v-model="form.scheduled_at" type="datetime-local" class="h-10 rounded-lg border-border/50 text-sm" />
                <p v-if="form.errors.scheduled_at" class="text-xs text-red-500">{{ form.errors.scheduled_at }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium">Target package</Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-10 w-full justify-between rounded-lg text-sm">
                                <span>{{ form.package_id ? packages.find((pkg) => pkg.id === form.package_id)?.name || 'Select package' : 'All packages' }}</span>
                                <ChevronDown class="h-4 w-4 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                            <DropdownMenuRadioGroup v-model="form.package_id">
                                <DropdownMenuRadioItem :value="null" class="text-xs">All packages</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem v-for="pkg in packages" :key="pkg.id" :value="pkg.id" class="text-xs">
                                    {{ pkg.name }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium">Target service</Label>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-10 w-full justify-between rounded-lg text-sm">
                                <span>{{ form.service_id ? services.find((service) => service.id === form.service_id)?.name || 'Select service' : 'All services' }}</span>
                                <ChevronDown class="h-4 w-4 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                            <DropdownMenuRadioGroup v-model="form.service_id">
                                <DropdownMenuRadioItem :value="null" class="text-xs">All services</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem v-for="service in services" :key="service.id" :value="service.id" class="text-xs">
                                    {{ service.name }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <p class="text-[10px] text-muted-foreground">{{ targetingHint }}</p>
        </CardContent>
    </Card>
</template>
