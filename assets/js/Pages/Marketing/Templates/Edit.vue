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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, Loader2, Save, ChevronDown, Sparkles, Copy, Check, Code, Eye } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    template: { type: Object, required: true },
});

const form = useForm({
    name: props.template.name,
    description: props.template.description || '',
    category: props.template.category || '',
    subject: props.template.subject || '',
    message: props.template.message,
    html_content: props.template.html_content || '',
    is_active: props.template.is_active,
    is_public: props.template.is_public,
});

const categories = [
    { value: 'welcome', label: 'Welcome' },
    { value: 'follow-up', label: 'Follow-up' },
    { value: 'promotional', label: 'Promotional' },
    { value: 'reminder', label: 'Reminder' },
    { value: 'notification', label: 'Notification' },
    { value: 'other', label: 'Other' },
];

const availableVariables = [
    { variable: '{{name}}', description: 'Full name of the lead' },
    { variable: '{{first_name}}', description: 'First name only' },
    { variable: '{{phone}}', description: 'Contact phone number' },
    { variable: '{{email}}', description: 'Email address' },
];

const copiedVariable = ref(null);
const showVariablesGuide = ref(false);

const copyVariable = (variable) => {
    navigator.clipboard.writeText(variable);
    copiedVariable.value = variable;
    setTimeout(() => {
        copiedVariable.value = null;
    }, 2000);
};

const submit = () => {
    form.put(route('marketing.templates.update', props.template.id));
};
</script>

<template>
    <Head title="Edit Template" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.templates.show', template.id)"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Edit Template</h1>
                <p class="text-xs text-muted-foreground">Update template details</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                        Template Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Name -->
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs">Template Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-8 text-xs" />
                        <p v-if="form.errors.name" class="text-[10px] text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-xs resize-none" />
                        <p v-if="form.errors.description" class="text-[10px] text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Category -->
                    <div class="space-y-1.5">
                        <Label for="category" class="text-xs">Category</Label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full justify-between h-8 text-xs">
                                    <span>{{ categories.find(c => c.value === form.category)?.label || 'Select category' }}</span>
                                    <ChevronDown class="h-3.5 w-3.5 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="form.category">
                                    <DropdownMenuRadioItem :value="''" class="text-xs">None</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem v-for="cat in categories" :key="cat.value" :value="cat.value" class="text-xs">
                                        {{ cat.label }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Subject (Email only) -->
                    <div v-if="template.type === 'email'" class="space-y-1.5">
                        <Label for="subject" class="text-xs">Subject</Label>
                        <Input id="subject" v-model="form.subject" class="h-8 text-xs" />
                        <p v-if="form.errors.subject" class="text-[10px] text-red-500">{{ form.errors.subject }}</p>
                    </div>

                    <!-- Variables Guide -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs">Message Variables</Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-6 text-[10px] px-2 gap-1"
                                @click="showVariablesGuide = !showVariablesGuide"
                            >
                                <Sparkles class="h-3 w-3" />
                                {{ showVariablesGuide ? 'Hide' : 'Show' }} Guide
                            </Button>
                        </div>
                        <div v-if="showVariablesGuide" class="border rounded-md p-3 bg-muted/30 space-y-2">
                            <div
                                v-for="(varItem, index) in availableVariables"
                                :key="index"
                                class="flex items-center justify-between p-2 border rounded bg-background"
                            >
                                <div class="flex-1">
                                    <code class="text-[10px] font-mono bg-primary/10 text-primary px-1.5 py-0.5 rounded">{{ varItem.variable }}</code>
                                    <p class="text-[10px] text-muted-foreground mt-1">{{ varItem.description }}</p>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0"
                                    @click="copyVariable(varItem.variable)"
                                >
                                    <Copy v-if="copiedVariable !== varItem.variable" class="h-3 w-3 text-muted-foreground" />
                                    <Check v-else class="h-3 w-3 text-green-600" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <Label for="message" class="text-xs">Message <span class="text-red-500">*</span></Label>
                            <div class="flex items-center gap-1">
                                <Button
                                    v-for="varItem in availableVariables"
                                    :key="varItem.variable"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-6 text-[10px] px-2 font-mono"
                                    @click="form.message += varItem.variable"
                                >
                                    {{ varItem.variable }}
                                </Button>
                            </div>
                        </div>
                        <Textarea id="message" v-model="form.message" rows="6" class="text-xs resize-none font-mono" />
                        <p v-if="form.errors.message" class="text-[10px] text-red-500">{{ form.errors.message }}</p>
                    </div>

                    <!-- HTML Content (Email only) with preview -->
                    <div v-if="template.type === 'email'" class="space-y-1.5">
                        <Label for="html_content" class="text-xs">HTML Content (Optional)</Label>
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
                                <Textarea id="html_content" v-model="form.html_content" rows="10" class="text-xs font-mono resize-y min-h-[200px] rounded-lg border-border/50" placeholder="<html>...</html>" />
                            </TabsContent>
                            <TabsContent value="preview" class="mt-0">
                                <div class="rounded-lg border border-border/50 bg-muted/20 min-h-[200px] overflow-auto">
                                    <iframe
                                        v-if="form.html_content"
                                        :srcdoc="form.html_content"
                                        title="Preview"
                                        class="w-full min-h-[200px] border-0 bg-white dark:bg-gray-900"
                                        sandbox="allow-same-origin"
                                    />
                                    <div v-else class="flex items-center justify-center min-h-[200px] text-xs text-muted-foreground p-4">
                                        Enter HTML to see preview.
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                        <p v-if="form.errors.html_content" class="text-[10px] text-red-500">{{ form.errors.html_content }}</p>
                    </div>

                    <!-- Options -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-input"
                            />
                            <Label for="is_active" class="text-xs cursor-pointer">Active</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                id="is_public"
                                v-model="form.is_public"
                                type="checkbox"
                                class="h-4 w-4 rounded border-input"
                            />
                            <Label for="is_public" class="text-xs cursor-pointer">Public (shareable)</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.templates.show', template.id)">Cancel</Link>
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
