<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import {
    ArrowLeft,
    Loader2,
    Power,
    PowerOff,
    Trash2,
    UploadCloud,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    modules: { type: Array, default: () => [] },
});

const fileInput = ref(null);
const uploadForm = useForm({
    module_archive: null,
});

const installedModules = computed(() => props.modules.filter((module) => module.installed));
const selectedArchiveLabel = computed(() => uploadForm.module_archive?.name ?? 'No file selected');

const formatDateTime = (value) => {
    if (!value) {
        return 'N/A';
    }

    return new Date(value).toLocaleString();
};

const onArchiveSelected = (event) => {
    const [file] = event.target.files ?? [];
    uploadForm.module_archive = file ?? null;
};

const resetArchiveInput = () => {
    uploadForm.reset('module_archive');

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitArchive = () => {
    uploadForm.post(route('admin.settings.modules.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            resetArchiveInput();
        },
    });
};

const activateModule = (module) => {
    if (!module?.installed_module?.id) {
        return;
    }

    router.post(route('admin.settings.modules.activate', module.installed_module.id), {}, {
        preserveScroll: true,
    });
};

const deactivateModule = (module) => {
    if (!module?.installed_module?.id) {
        return;
    }

    router.post(route('admin.settings.modules.deactivate', module.installed_module.id), {}, {
        preserveScroll: true,
    });
};

const uninstallModule = (module) => {
    if (!module?.installed_module?.id) {
        return;
    }

    if (!confirm(`Uninstall ${module.name}? This removes the uploaded package and disables the module.`)) {
        return;
    }

    router.delete(route('admin.settings.modules.destroy', module.installed_module.id), {
        preserveScroll: true,
    });
};

const statusLabel = (module) => (module.active ? 'Active' : 'Inactive');
const statusVariant = (module) => (module.active ? 'default' : 'secondary');
</script>

<template>
    <Head title="Modules" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('admin.settings.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Modules</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Upload a module ZIP file, then activate, deactivate, or uninstall it from this page.
                </p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <UploadCloud class="h-4 w-4" />
                    Upload Module
                </CardTitle>
                <CardDescription>Select the ZIP package you want to install.</CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="submitArchive">
                    <div class="space-y-2">
                        <Label for="module_archive">Module ZIP</Label>
                        <Input
                            id="module_archive"
                            ref="fileInput"
                            type="file"
                            accept=".zip,application/zip"
                            @change="onArchiveSelected"
                        />
                        <p class="text-sm text-muted-foreground">{{ selectedArchiveLabel }}</p>
                        <p v-if="uploadForm.errors.module_archive" class="text-sm text-destructive">
                            {{ uploadForm.errors.module_archive }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <Button type="button" variant="outline" @click="resetArchiveInput">
                            Clear
                        </Button>
                        <Button type="submit" :disabled="uploadForm.processing || !uploadForm.module_archive">
                            <Loader2 v-if="uploadForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Install Module
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Installed Modules</CardTitle>
                <CardDescription>All installed modules are listed below.</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="installedModules.length">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Version</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Installed</TableHead>
                                <TableHead>Activated</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="module in installedModules" :key="module.slug">
                                <TableCell class="font-medium">
                                    <div>{{ module.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ module.slug }}</div>
                                </TableCell>
                                <TableCell>{{ module.installed_module?.version ?? 'N/A' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariant(module)">
                                        {{ statusLabel(module) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDateTime(module.installed_module?.installed_at) }}</TableCell>
                                <TableCell>{{ formatDateTime(module.installed_module?.activated_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            v-if="!module.active"
                                            size="sm"
                                            @click="activateModule(module)"
                                        >
                                            <Power class="mr-2 h-3.5 w-3.5" />
                                            Activate
                                        </Button>
                                        <Button
                                            v-if="module.active"
                                            size="sm"
                                            variant="outline"
                                            @click="deactivateModule(module)"
                                        >
                                            <PowerOff class="mr-2 h-3.5 w-3.5" />
                                            Deactivate
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="destructive"
                                            @click="uninstallModule(module)"
                                        >
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Uninstall
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div v-else class="rounded-md border border-dashed p-6 text-sm text-muted-foreground">
                    No modules installed yet.
                </div>
            </CardContent>
        </Card>
    </div>
</template>
