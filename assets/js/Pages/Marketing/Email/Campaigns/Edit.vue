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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ArrowLeft, Loader2, Save, ChevronDown, Sparkles, Code, Eye, MessageSquare } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    campaign: { type: Object, required: true },
    packages: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    channel: { type: String, required: true },
    channelLabel: { type: String, required: true },
    emailTemplates: { type: Array, default: () => [] },
});

const showRoute = (id) => route(`marketing.${props.channel}.campaigns.show`, id);
const updateRoute = (id) => route(`marketing.${props.channel}.campaigns.update`, id);

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    type: props.campaign.type,
    message: props.campaign.message ?? '',
    subject: props.campaign.subject ?? '',
    html_content: props.campaign.html_content ?? '',
    scheduled_at: props.campaign.scheduled_at ? new Date(props.campaign.scheduled_at).toISOString().slice(0, 16) : '',
    package_id: props.campaign.package_id,
    service_id: props.campaign.service_id,
    target_criteria: props.campaign.target_criteria || {},
});

const emailContentMode = ref(props.campaign.html_content ? 'html' : 'plain');
const selectedEmailTemplateId = ref(null);

const applyEmailTemplate = (template) => {
    if (!template) return;
    form.subject = template.subject ?? '';
    form.message = template.message ?? '';
    form.html_content = template.html_content ?? '';
    selectedEmailTemplateId.value = template.id;
};

const clearEmailTemplate = () => {
    selectedEmailTemplateId.value = null;
};

const submit = () => {
    form.put(updateRoute(props.campaign.id));
};
</script>

<template>
    <Head title="Edit Campaign" />

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link
                :href="showRoute(campaign.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit {{ channelLabel }} Campaign</h1>
                <p class="text-xs text-muted-foreground">Update campaign details</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-violet-500 to-violet-600 rounded" />
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

                    <div class="space-y-1.5">
                        <Label for="subject" class="text-xs">Subject</Label>
                        <Input id="subject" v-model="form.subject" class="h-8 text-xs" placeholder="Email subject" />
                        <p v-if="form.errors.subject" class="text-[10px] text-red-500">{{ form.errors.subject }}</p>
                    </div>

                    <div v-if="emailTemplates.length > 0" class="space-y-2">
                        <Label class="text-xs">Load from template</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="t in emailTemplates"
                                :key="t.id"
                                type="button"
                                :class="[
                                    'flex items-center gap-2 px-3 py-2 rounded-lg border text-left text-xs transition-colors',
                                    selectedEmailTemplateId === t.id
                                        ? 'border-violet-500 bg-violet-500/10 text-violet-600 dark:text-violet-400'
                                        : 'border-border/50 hover:bg-muted/30'
                                ]"
                                @click="applyEmailTemplate(t)"
                            >
                                <Sparkles class="h-3.5 w-3.5 shrink-0" />
                                {{ t.name }}
                            </button>
                            <Button v-if="selectedEmailTemplateId" type="button" variant="ghost" size="sm" class="h-8 text-xs" @click="clearEmailTemplate">
                                Clear
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Channel</Label>
                            <div class="h-8 flex items-center text-xs font-medium text-muted-foreground">{{ channelLabel }}</div>
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

                    <div class="flex gap-2 border-b border-border/50 pb-2">
                        <Button
                            type="button"
                            :variant="emailContentMode === 'html' ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 text-xs gap-1.5"
                            @click="emailContentMode = 'html'"
                        >
                            <Code class="h-3.5 w-3.5" />
                            HTML
                        </Button>
                        <Button
                            type="button"
                            :variant="emailContentMode === 'plain' ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 text-xs gap-1.5"
                            @click="emailContentMode = 'plain'"
                        >
                            <MessageSquare class="h-3.5 w-3.5" />
                            Plain text
                        </Button>
                    </div>
                    <div v-if="emailContentMode === 'html'" class="space-y-2">
                        <Label for="html_content" class="text-xs">HTML body</Label>
                        <Tabs default-value="editor" class="w-full">
                            <TabsList class="grid w-full grid-cols-2 max-w-[200px] mb-2">
                                <TabsTrigger value="editor" class="text-xs gap-1.5">
                                    <Code class="h-3.5 w-3.5" />
                                    Editor
                                </TabsTrigger>
                                <TabsTrigger value="preview" class="text-xs gap-1.5">
                                    <Eye class="h-3.5 w-3.5" />
                                    Preview
                                </TabsTrigger>
                            </TabsList>
                            <TabsContent value="editor" class="mt-0">
                                <Textarea
                                    id="html_content"
                                    v-model="form.html_content"
                                    rows="12"
                                    class="text-xs font-mono resize-y min-h-[240px] rounded-lg border-border/50"
                                    placeholder="<html>...</html>"
                                />
                            </TabsContent>
                            <TabsContent value="preview" class="mt-0">
                                <div class="rounded-lg border border-border/50 bg-muted/20 min-h-[240px] overflow-auto">
                                    <iframe
                                        v-if="form.html_content"
                                        :srcdoc="form.html_content"
                                        title="Preview"
                                        class="w-full min-h-[240px] border-0 bg-white dark:bg-gray-900"
                                        sandbox="allow-same-origin"
                                    />
                                    <div v-else class="flex items-center justify-center min-h-[240px] text-xs text-muted-foreground p-4">
                                        Enter HTML to see preview.
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                        <p v-if="form.errors.html_content" class="text-[10px] text-red-500">{{ form.errors.html_content }}</p>
                    </div>
                    <div v-else class="space-y-1.5">
                        <Label for="message" class="text-xs">Plain text body</Label>
                        <Textarea id="message" v-model="form.message" rows="4" class="text-xs resize-none" />
                        <p v-if="form.errors.message" class="text-[10px] text-red-500">{{ form.errors.message }}</p>
                    </div>
                    <p class="text-[10px] text-muted-foreground">Variables: {{name}}, {{first_name}}, {{phone}}, {{email}}</p>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="showRoute(campaign.id)">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" :disabled="form.processing" class="gap-1.5 bg-violet-500 hover:bg-violet-600 text-white">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <Save v-else class="h-3.5 w-3.5" />
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>
