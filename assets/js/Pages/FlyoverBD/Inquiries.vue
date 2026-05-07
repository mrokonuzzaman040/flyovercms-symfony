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
    User,
    Mail, 
    Phone, 
    ArrowLeft,
    Search,
    AlertCircle,
    Calendar,
    Users,
    Hotel,
    Info,
    Package,
    Eye,
    MessageSquare,
    Clipboard
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    inquiries: {
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
    { label: 'Custom Inquiries', active: true }
];

const searchQuery = ref('');
const selectedInquiry = ref(null);
const isSheetOpen = ref(false);

const openDetail = (inquiry) => {
    selectedInquiry.value = inquiry;
    isSheetOpen.value = true;
};

const filteredInquiries = computed(() => {
    if (!searchQuery.value) return props.inquiries;
    
    const query = searchQuery.value.toLowerCase();
    return props.inquiries.filter(inquiry => 
        inquiry.name?.toLowerCase().includes(query) ||
        inquiry.email?.toLowerCase().includes(query) ||
        inquiry.phone?.toLowerCase().includes(query)
    );
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const parseDetails = (details) => {
    if (!details) return {};
    if (typeof details === 'string') {
        try {
            return JSON.parse(details);
        } catch (e) {
            return {};
        }
    }
    return details;
};
</script>

<template>
    <Head title="FlyoverBD - Custom Inquiries" />

    <DashboardLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Custom Plan Inquiries</h1>
                    <p class="text-muted-foreground mt-1">
                        Requests for customized tour packages from FlyoverBD users.
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
                            <CardTitle>Inquiries</CardTitle>
                            <CardDescription>
                                Total {{ filteredInquiries.length }} requests found.
                            </CardDescription>
                        </div>
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <input
                                v-model="searchQuery"
                                type="search"
                                placeholder="Search inquiries..."
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
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Package Info</TableHead>
                                    <TableHead>Travel Details</TableHead>
                                    <TableHead>Message</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="inquiry in filteredInquiries" :key="inquiry.id">
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ inquiry.name }}</span>
                                            <span class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                <Mail class="h-3 w-3" /> {{ inquiry.email }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="inquiry.package" class="flex flex-col">
                                            <span class="text-xs font-medium flex items-center gap-1">
                                                <Package class="h-3 w-3 text-blue-500" /> {{ inquiry.package.title || 'Linked Package' }}
                                            </span>
                                        </div>
                                        <Badge v-else variant="outline" class="font-normal text-xs">General</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col gap-1 text-xs">
                                            <span v-if="parseDetails(inquiry.details).pax" class="flex items-center gap-1 text-muted-foreground">
                                                <Users class="h-3 w-3" /> {{ parseDetails(inquiry.details).pax }} Pax
                                            </span>
                                            <span v-if="parseDetails(inquiry.details).hotel_type" class="flex items-center gap-1 text-muted-foreground">
                                                <Hotel class="h-3 w-3" /> {{ parseDetails(inquiry.details).hotel_type }} Star
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="max-w-[200px]">
                                        <p v-if="inquiry.message" class="text-xs text-muted-foreground line-clamp-1 italic">
                                            "{{ inquiry.message }}"
                                        </p>
                                    </TableCell>
                                    <TableCell class="text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-xs text-muted-foreground">
                                                {{ formatDate(inquiry.created_at) }}
                                            </span>
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDetail(inquiry)">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredInquiries.length === 0">
                                    <TableCell colspan="5" class="h-24 text-center">
                                        No inquiries found.
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
                    <SheetTitle class="text-2xl font-bold">Inquiry Details</SheetTitle>
                    <SheetDescription>
                        Custom plan request from {{ selectedInquiry?.name }}.
                    </SheetDescription>
                </SheetHeader>

                <div v-if="selectedInquiry" class="mt-8 space-y-6">
                    <!-- Customer Section -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <User class="h-4 w-4" /> Customer Information
                        </h3>
                        <div class="grid grid-cols-2 gap-4 rounded-lg border p-4 bg-muted/30">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground uppercase">Name</p>
                                <p class="text-sm font-semibold mt-0.5">{{ selectedInquiry.name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-muted-foreground uppercase">Joined</p>
                                <p class="text-sm font-semibold mt-0.5">{{ formatDate(selectedInquiry.created_at) }}</p>
                            </div>
                            <div class="col-span-2 border-t pt-2 mt-2">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Contact</p>
                                <div class="flex flex-col gap-1 mt-1">
                                    <p class="text-sm font-semibold flex items-center gap-2">
                                        <Mail class="h-3 w-3 text-muted-foreground" /> {{ selectedInquiry.email }}
                                    </p>
                                    <p v-if="selectedInquiry.phone" class="text-sm font-semibold flex items-center gap-2">
                                        <Phone class="h-3 w-3 text-muted-foreground" /> {{ selectedInquiry.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Requirements -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <Plane class="h-4 w-4" /> Travel Requirements
                        </h3>
                        <div class="grid grid-cols-2 gap-4 rounded-lg border p-4">
                            <div v-if="parseDetails(selectedInquiry.details).pax">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Number of Pax</p>
                                <p class="text-sm font-semibold mt-0.5 flex items-center gap-1">
                                    <Users class="h-3 w-3" /> {{ parseDetails(selectedInquiry.details).pax }} Persons
                                </p>
                            </div>
                            <div v-if="parseDetails(selectedInquiry.details).hotel_type">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Hotel Category</p>
                                <p class="text-sm font-semibold mt-0.5 flex items-center gap-1">
                                    <Hotel class="h-3 w-3" /> {{ parseDetails(selectedInquiry.details).hotel_type }} Star
                                </p>
                            </div>
                            <div v-if="parseDetails(selectedInquiry.details).estimated_date">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Est. Travel Date</p>
                                <p class="text-sm font-semibold mt-0.5 flex items-center gap-1">
                                    <Calendar class="h-3 w-3" /> {{ formatDate(parseDetails(selectedInquiry.details).estimated_date) }}
                                </p>
                            </div>
                            <div v-if="selectedInquiry.package">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Base Package</p>
                                <p class="text-sm font-semibold mt-0.5 flex items-center gap-1">
                                    <Package class="h-3 w-3" /> {{ selectedInquiry.package.title }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Section -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <MessageSquare class="h-4 w-4" /> Additional Message
                        </h3>
                        <div class="rounded-lg bg-muted/30 p-4 border block min-h-[100px]">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap italic">
                                "{{ selectedInquiry.message || 'No additional requirements provided.' }}"
                            </p>
                        </div>
                    </div>

                    <!-- Full JSON Details (for debugging or extra fields) -->
                    <div class="space-y-2 prose prose-sm max-w-none">
                        <p class="text-xs font-medium text-muted-foreground uppercase">Raw Data Record</p>
                        <pre class="rounded-lg bg-slate-950 p-4 text-slate-50 text-[11px] overflow-x-auto"><code>{{ JSON.stringify(parseDetails(selectedInquiry.details), null, 2) }}</code></pre>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t">
                        <Button variant="outline" size="sm" @click="isSheetOpen = false">Close Details</Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </DashboardLayout>
</template>
