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
    CreditCard,
    Plane,
    CheckCircle2,
    XCircle,
    Clock,
    DollarSign,
    Eye,
    Tag,
    Briefcase,
    Receipt
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    bookings: {
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
    { label: 'Tour Bookings', active: true }
];

const searchQuery = ref('');
const selectedBooking = ref(null);
const isSheetOpen = ref(false);

const openDetail = (booking) => {
    selectedBooking.value = booking;
    isSheetOpen.value = true;
};

const filteredBookings = computed(() => {
    if (!searchQuery.value) return props.bookings;
    
    const query = searchQuery.value.toLowerCase();
    return props.bookings.filter(booking => 
        booking.guest_name?.toLowerCase().includes(query) ||
        booking.guest_email?.toLowerCase().includes(query) ||
        booking.guest_phone?.toLowerCase().includes(query)
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

const getStatusBadgeVariant = (status) => {
    const s = status?.toLowerCase();
    if (s === 'confirmed' || s === 'completed') return 'success';
    if (s === 'pending') return 'warning';
    if (s === 'cancelled' || s === 'rejected') return 'destructive';
    return 'outline';
};

const getPaymentBadgeVariant = (status) => {
    const s = status?.toLowerCase();
    if (s === 'paid') return 'success';
    if (s === 'unpaid') return 'destructive';
    if (s === 'partial') return 'warning';
    return 'secondary';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0
    }).format(amount);
};
</script>

<template>
    <Head title="FlyoverBD - Tour Bookings" />

    <DashboardLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tour Bookings</h1>
                    <p class="text-muted-foreground mt-1">
                        Real-time bookings from FlyoverBD tour platform.
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
                            <CardTitle>Bookings</CardTitle>
                            <CardDescription>
                                Total {{ filteredBookings.length }} bookings found.
                            </CardDescription>
                        </div>
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <input
                                v-model="searchQuery"
                                type="search"
                                placeholder="Search bookings..."
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
                                    <TableHead>Guest</TableHead>
                                    <TableHead>Tour / Service</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Payment</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="booking in filteredBookings" :key="booking.id">
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-sm">{{ booking.guest_name }}</span>
                                            <span class="text-[11px] text-muted-foreground">{{ booking.guest_email }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="booking.payable" class="flex flex-col">
                                            <span class="text-xs font-medium flex items-center gap-1 text-blue-600">
                                                <Plane class="h-3 w-3" /> {{ booking.payable.title || booking.payable.name || 'Tour Plan' }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadgeVariant(booking.status)" class="capitalize font-normal text-[10px] h-5">
                                            {{ booking.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getPaymentBadgeVariant(booking.payment_status)" class="capitalize font-normal text-[10px] h-5">
                                            {{ booking.payment_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="font-medium whitespace-nowrap text-xs">
                                        {{ formatCurrency(booking.total_amount) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-[10px] text-muted-foreground">
                                                {{ formatDate(booking.booking_date) }}
                                            </span>
                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDetail(booking)">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredBookings.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center text-sm text-muted-foreground">
                                        No bookings found.
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
                    <SheetTitle class="text-2xl font-bold">Booking Details</SheetTitle>
                    <SheetDescription>
                        Booking information for {{ selectedBooking?.guest_name }}.
                    </SheetDescription>
                </SheetHeader>

                <div v-if="selectedBooking" class="mt-8 space-y-6">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between rounded-lg border p-4 bg-muted/20">
                        <div class="space-y-1">
                            <p class="text-xs font-medium text-muted-foreground uppercase">Booking Status</p>
                            <Badge :variant="getStatusBadgeVariant(selectedBooking.status)" class="capitalize px-3">
                                {{ selectedBooking.status }}
                            </Badge>
                        </div>
                        <div class="space-y-1 text-right">
                            <p class="text-xs font-medium text-muted-foreground uppercase">Ref Number</p>
                            <p class="text-sm font-mono font-bold">#{{ selectedBooking.booking_number || selectedBooking.id }}</p>
                        </div>
                    </div>

                    <!-- Guest Details -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <User class="h-4 w-4" /> Guest Information
                        </h3>
                        <div class="grid grid-cols-2 gap-4 rounded-lg border p-4">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground uppercase">Name</p>
                                <p class="text-sm font-semibold mt-0.5">{{ selectedBooking.guest_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-muted-foreground uppercase">Travel Date</p>
                                <p class="text-sm font-semibold mt-0.5 flex items-center gap-1">
                                    <Calendar class="h-3 w-3" /> {{ formatDate(selectedBooking.booking_date) }}
                                </p>
                            </div>
                            <div class="col-span-2 border-t pt-2 mt-1">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Contact Info</p>
                                <div class="flex flex-col gap-1 mt-1">
                                    <p class="text-sm flex items-center gap-2">
                                        <Mail class="h-3 w-3 text-muted-foreground" /> {{ selectedBooking.guest_email }}
                                    </p>
                                    <p v-if="selectedBooking.guest_phone" class="text-sm flex items-center gap-2">
                                        <Phone class="h-3 w-3 text-muted-foreground" /> {{ selectedBooking.guest_phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tour Details -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <Briefcase class="h-4 w-4" /> Tour / Service
                        </h3>
                        <div class="rounded-lg border p-4 bg-blue-50/30 border-blue-100">
                            <div v-if="selectedBooking.payable" class="space-y-2">
                                <p class="text-xs font-medium text-blue-600 uppercase flex items-center gap-1">
                                    <Plane class="h-3 w-3" /> Service Details
                                </p>
                                <p class="text-base font-bold text-blue-900 leading-tight">
                                    {{ selectedBooking.payable.title || selectedBooking.payable.name || 'Tour Plan' }}
                                </p>
                                <div class="grid grid-cols-2 gap-2 mt-4 pt-2 border-t border-blue-100/50">
                                    <div v-if="selectedBooking.payable.duration">
                                        <p class="text-[10px] font-medium text-blue-600 uppercase">Duration</p>
                                        <p class="text-xs font-semibold">{{ selectedBooking.payable.duration }}</p>
                                    </div>
                                    <div v-if="selectedBooking.payable.category">
                                        <p class="text-[10px] font-medium text-blue-600 uppercase">Category</p>
                                        <p class="text-xs font-semibold">{{ selectedBooking.payable.category }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">No detailed service information linked.</p>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 text-primary">
                            <Receipt class="h-4 w-4" /> Payment Summary
                        </h3>
                        <div class="rounded-lg border p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-muted-foreground uppercase">Payment Status</p>
                                <Badge :variant="getPaymentBadgeVariant(selectedBooking.payment_status)" class="capitalize px-3 underline-offset-4">
                                    {{ selectedBooking.payment_status }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-dashed">
                                <p class="text-sm font-bold">Total Amount</p>
                                <p class="text-xl font-black text-primary">{{ formatCurrency(selectedBooking.total_amount) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t">
                        <Button variant="outline" size="sm" @click="isSheetOpen = false">Close Details</Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </DashboardLayout>
</template>
