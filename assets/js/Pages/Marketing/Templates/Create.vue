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
import { Checkbox } from '@/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, Loader2, FileText, MessageSquare, ChevronDown, Sparkles, Copy, Check, Code, Eye } from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const form = useForm({
    name: '',
    description: '',
    type: 'sms',
    category: '',
    subject: '',
    message: '',
    html_content: '',
    is_active: true,
    is_public: false,
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
    form.post(route('marketing.templates.store'));
};
</script>

<template>
    <Head title="Create Template" />

    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link
                :href="route('marketing.templates.index')"
                class="inline-flex items-center justify-center rounded-md border border-input bg-background text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 shrink-0"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <h1 class="text-lg font-semibold tracking-tight">Create Template</h1>
                <p class="text-xs text-muted-foreground">Reusable message template for campaigns</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Template information -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <FileText class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Template information</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Name, channel and category</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-xs font-medium">Template name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" class="h-10 text-sm rounded-lg border-border/50" placeholder="e.g. Welcome SMS" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description" class="text-xs font-medium">Description (optional)</Label>
                        <Textarea id="description" v-model="form.description" rows="2" class="text-sm resize-none min-h-20 rounded-lg border-border/50" placeholder="Internal note" />
                        <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Channel <span class="text-red-500">*</span></Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50 focus:ring-2 focus:ring-primary/20">
                                        <span>{{ form.type === 'sms' ? 'SMS' : form.type === 'email' ? 'Email' : form.type === 'whatsapp' ? 'WhatsApp' : 'Select channel' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.type">
                                        <DropdownMenuRadioItem value="sms" class="text-sm">SMS</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="email" class="text-sm">Email</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem value="whatsapp" class="text-sm">WhatsApp</DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <p class="text-xs text-muted-foreground">WhatsApp is sent via WasenderAPI.</p>
                            <p v-if="form.errors.type" class="text-xs text-red-500">{{ form.errors.type }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium">Category</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between h-10 text-sm rounded-lg border-border/50 focus:ring-2 focus:ring-primary/20">
                                        <span>{{ categories.find(c => c.value === form.category)?.label || 'Select category' }}</span>
                                        <ChevronDown class="h-4 w-4 opacity-50 shrink-0" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                    <DropdownMenuRadioGroup v-model="form.category">
                                        <DropdownMenuRadioItem :value="''" class="text-sm">None</DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem v-for="cat in categories" :key="cat.value" :value="cat.value" class="text-sm">
                                            {{ cat.label }}
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div v-if="form.type === 'email'" class="space-y-2">
                        <Label for="subject" class="text-xs font-medium">Subject</Label>
                        <Input id="subject" v-model="form.subject" class="h-10 text-sm rounded-lg border-border/50" placeholder="Email subject" />
                        <p v-if="form.errors.subject" class="text-xs text-red-500">{{ form.errors.subject }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex items-center gap-2">
                            <Checkbox id="is_active" :checked="form.is_active" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => form.is_active = !!v" />
                            <Label for="is_active" class="text-xs font-medium cursor-pointer">Active</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox id="is_public" :checked="form.is_public" class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" @update:checked="(v) => form.is_public = !!v" />
                            <Label for="is_public" class="text-xs font-medium cursor-pointer">Public (shareable)</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Message content -->
            <Card class="overflow-hidden border-border/80 bg-card shadow-sm">
                <CardHeader class="border-b border-border/50 bg-muted/20 dark:bg-muted/10 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 dark:bg-primary/20 ring-1 ring-primary/20">
                            <MessageSquare class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-sm font-semibold tracking-tight">Message content</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Body and optional variables</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <Label class="text-xs font-medium">Message variables</Label>
                        <Button type="button" variant="ghost" size="sm" class="h-8 text-xs gap-1.5" @click="showVariablesGuide = !showVariablesGuide">
                            <Sparkles class="h-3.5 w-3.5" />
                            {{ showVariablesGuide ? 'Hide' : 'Show' }} guide
                        </Button>
                    </div>
                    <div v-if="showVariablesGuide" class="rounded-xl border border-border/50 bg-muted/10 dark:bg-muted/5 p-4 space-y-2">
                        <div
                            v-for="(varItem, index) in availableVariables"
                            :key="index"
                            class="flex items-center justify-between rounded-lg border border-border/50 bg-background p-3"
                        >
                            <div class="min-w-0 flex-1">
                                <code class="text-xs font-mono bg-primary/10 dark:bg-primary/20 text-primary px-1.5 py-0.5 rounded">{{ varItem.variable }}</code>
                                <p class="text-xs text-muted-foreground mt-1">{{ varItem.description }}</p>
                            </div>
                            <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0 shrink-0" @click="copyVariable(varItem.variable)">
                                <Copy v-if="copiedVariable !== varItem.variable" class="h-3.5 w-3.5 text-muted-foreground" />
                                <Check v-else class="h-3.5 w-3.5 text-green-600 dark:text-green-500" />
                            </Button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <Label for="message" class="text-xs font-medium">Message <span class="text-red-500">*</span></Label>
                            <div class="flex flex-wrap items-center gap-1">
                                <Button
                                    v-for="varItem in availableVariables"
                                    :key="varItem.variable"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 text-xs font-mono rounded-lg"
                                    @click="form.message += varItem.variable"
                                >
                                    {{ varItem.variable }}
                                </Button>
                            </div>
                        </div>
                        <Textarea id="message" v-model="form.message" rows="6" class="text-sm resize-y rounded-lg border-border/50 font-mono min-h-32" placeholder="Use {{name}}, {{first_name}}, {{phone}}, {{email}}" required />
                        <p v-if="form.errors.message" class="text-xs text-red-500">{{ form.errors.message }}</p>
                    </div>
                    <div v-if="form.type === 'email'" class="space-y-2">
                        <Label for="html_content" class="text-xs font-medium">HTML content (optional)</Label>
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
                                <Textarea id="html_content" v-model="form.html_content" rows="10" class="text-sm resize-y rounded-lg border-border/50 font-mono min-h-40" placeholder="<html>... Rich HTML for email. Use {{name}}, {{email}}, etc.</html>" />
                            </TabsContent>
                            <TabsContent value="preview" class="mt-0">
                                <div class="rounded-lg border border-border/50 bg-muted/20 dark:bg-muted/10 min-h-[200px] overflow-auto">
                                    <iframe
                                        v-if="form.html_content"
                                        :srcdoc="form.html_content"
                                        title="HTML preview"
                                        class="w-full min-h-[200px] border-0 bg-white dark:bg-gray-900"
                                        sandbox="allow-same-origin"
                                    />
                                    <div v-else class="flex items-center justify-center min-h-[200px] text-xs text-muted-foreground p-4">
                                        Enter HTML above to see preview.
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                        <p v-if="form.errors.html_content" class="text-xs text-red-500">{{ form.errors.html_content }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" size="sm" as-child>
                    <Link :href="route('marketing.templates.index')">Cancel</Link>
                </Button>
                <Button type="submit" size="sm" class="gap-1.5 shadow-sm" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                    <FileText v-else class="h-3.5 w-3.5" />
                    Create template
                </Button>
            </div>
        </form>
    </div>
</template>
