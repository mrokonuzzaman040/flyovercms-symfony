<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/Components/ui/table';
import { 
    Sheet, 
    SheetContent, 
    SheetHeader, 
    SheetTitle, 
    SheetDescription 
} from '@/Components/ui/sheet';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { 
    Mail, 
    Phone, 
    ArrowLeft,
    Search,
    AlertCircle,
    CheckCircle2,
    Clock,
    Eye,
    Calendar,
    User
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    contacts: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: null
    }
});

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'FlyoverBD Connection', href: route('flyover-bd.index') },
    { label: 'Contact Messages', active: true }
];

const searchQuery = ref('');
const selectedContact = ref(null);
const isSheetOpen = ref(false);

const openDetail = (contact) => {
    selectedContact.value = contact;
    isSheetOpen.value = true;
};

const filteredContacts = computed(() => {
    if (!searchQuery.value) return props.contacts;
    
    const query = searchQuery.value.toLowerCase();
    return props.contacts.filter(contact => 
        (contact.first_name + ' ' + contact.last_name).toLowerCase().includes(query) ||
        contact.email.toLowerCase().includes(query) ||
        contact.subject.toLowerCase().includes(query)
    );
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="FlyoverBD - Contact Messages" />

    <DashboardLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Contact Messages</h1>
                    <p class="text-muted-foreground mt-1">
                        Messages submitted via the contact form on FlyoverBD.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('flyover-bd.index')">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Summary
                        </Button>
                    </Link>
                </div>
            </div>

            <Alert v-if="error" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>API Error</AlertTitle>
                <AlertDescription>{{ error }}</AlertDescription>
            </Alert>

            <Card>
                <CardHeader>
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>Messages</CardTitle>
                            <CardDescription>
                                Total {{ filteredContacts.length }} messages found.
                            </CardDescription>
                        </div>
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <input
                                v-model="searchQuery"
                                type="search"
                                placeholder="Search messages..."
                                class="flex h-10 w-full rounded-md border border-input bg-background px-9 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Sender</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead>Message</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Date</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="contact in filteredContacts" :key="contact.id">
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ contact.first_name }} {{ contact.last_name }}</span>
                                            <span class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                <Mail class="h-3 w-3" /> {{ contact.email }}
                                            </span>
                                            <span v-if="contact.phone" class="text-xs text-muted-foreground flex items-center gap-1">
                                                <Phone class="h-3 w-3" /> {{ contact.phone }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="max-w-[200px] truncate">
                                        {{ contact.subject }}
                                    </TableCell>
                                    <TableCell class="max-w-[300px]">
                                        <p class="text-xs text-muted-foreground line-clamp-1">
                                            {{ contact.message }}
                                        </p>
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="contact.is_read" variant="secondary" class="bg-green-50 text-green-700 hover:bg-green-100 flex w-fit items-center gap-1">
                                            <CheckCircle2 class="h-3 w-3" /> Read
                                        </Badge>
                                        <Badge v-else variant="outline" class="text-amber-700 bg-amber-50 border-amber-200 flex w-fit items-center gap-1">
                                            <Clock class="h-3 w-3" /> Unread
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-xs text-muted-foreground whitespace-nowrap">
                                                {{ formatDate(contact.created_at) }}
                                            </span>
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDetail(contact)">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredContacts.length === 0">
                                    <TableCell colspan="5" class="h-24 text-center">
                                        No messages found.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Detail Sheet -->
        <Sheet v-model:open="isSheetOpen">
            <SheetContent v-if="isSheetOpen" class="sm:max-w-xl overflow-y-auto">
                <SheetHeader class="space-y-1">
                    <SheetTitle class="text-2xl font-bold">Message Details</SheetTitle>
                    <SheetDescription>
                        Full message details from {{ selectedContact?.first_name }} {{ selectedContact?.last_name }}.
                    </SheetDescription>
                </SheetHeader>

                <div v-if="selectedContact" class="mt-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1 rounded-lg border p-4">
                            <p class="text-xs font-medium text-muted-foreground uppercase flex items-center gap-1">
                                <User class="h-3 w-3" /> Sender Name
                            </p>
                            <p class="text-sm font-semibold">{{ selectedContact.first_name }} {{ selectedContact.last_name }}</p>
                        </div>
                        <div class="space-y-1 rounded-lg border p-4">
                            <p class="text-xs font-medium text-muted-foreground uppercase flex items-center gap-1">
                                <Calendar class="h-3 w-3" /> Received Date
                            </p>
                            <p class="text-sm font-semibold">{{ formatDate(selectedContact.created_at) }}</p>
                        </div>
                    </div>

                    <div class="space-y-1 rounded-lg border p-4">
                        <p class="text-xs font-medium text-muted-foreground uppercase flex items-center gap-1">
                            <Mail class="h-3 w-3" /> Contact Information
                        </p>
                        <div class="flex flex-col gap-1 mt-1">
                            <p class="text-sm font-semibold flex items-center gap-2">
                                {{ selectedContact.email }}
                                <Button variant="ghost" size="icon" class="h-6 w-6 text-muted-foreground hover:text-primary" @click="() => { navigator.clipboard.writeText(selectedContact.email) }">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                </Button>
                            </p>
                            <p v-if="selectedContact.phone" class="text-sm text-muted-foreground flex items-center gap-2">
                                <Phone class="h-3 w-3" /> {{ selectedContact.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs font-medium text-muted-foreground uppercase">Subject</p>
                        <div class="rounded-lg bg-muted/50 p-4 border block">
                            <p class="text-sm font-semibold leading-relaxed">
                                {{ selectedContact.subject }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs font-medium text-muted-foreground uppercase">Message Content</p>
                        <div class="rounded-lg bg-muted/30 p-4 border min-h-[150px]">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">
                                {{ selectedContact.message }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t">
                        <Badge :variant="selectedContact.is_read ? 'secondary' : 'outline'" :class="selectedContact.is_read ? 'bg-green-50 text-green-700' : 'text-amber-700 bg-amber-50'">
                            {{ selectedContact.is_read ? 'Marked as Read' : 'Unread Message' }}
                        </Badge>
                        <Button variant="outline" size="sm" @click="isSheetOpen = false">Close Details</Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </DashboardLayout>
</template>
