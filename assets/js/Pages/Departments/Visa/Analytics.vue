<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { LineController, BarController, DoughnutController } from 'chart.js';
import { PieChart, BarChart3, TrendingUp } from 'lucide-vue-next';

Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    LineController,
    BarController,
    DoughnutController
);

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    analytics: {
        type: Object,
        default: () => ({
            status_distribution: {},
            service_type_breakdown: {},
            monthly_transfers: {},
        }),
    },
});

const statusLabels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    application_submitted: 'Submitted',
    under_review: 'Under Review',
    approved: 'Approved',
    rejected: 'Rejected',
    completed: 'Completed',
};

const serviceTypeLabels = {
    study_visa: 'Study Visa',
    tour_visa: 'Tour Visa',
    immigration: 'Immigration',
    other: 'Other',
};

const chartColors = [
    'rgba(59, 130, 246, 0.8)',
    'rgba(139, 92, 246, 0.8)',
    'rgba(16, 185, 129, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(6, 182, 212, 0.8)',
    'rgba(168, 85, 247, 0.8)',
];

let statusChart = null;
let serviceTypeChart = null;
let monthlyChart = null;
const statusChartRef = ref(null);
const serviceTypeChartRef = ref(null);
const monthlyChartRef = ref(null);

function initCharts() {
    const statusDist = props.analytics?.status_distribution || {};
    const labels = Object.keys(statusDist).map((k) => statusLabels[k] || k);
    const data = Object.values(statusDist);

    if (statusChartRef.value && labels.length > 0) {
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(statusChartRef.value.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: chartColors.slice(0, labels.length), borderWidth: 2, borderColor: 'rgb(255,255,255)' }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }

    const serviceBreakdown = props.analytics?.service_type_breakdown || {};
    const serviceLabels = Object.keys(serviceBreakdown).map((k) => serviceTypeLabels[k] || k);
    const serviceData = Object.values(serviceBreakdown);

    if (serviceTypeChartRef.value && serviceLabels.length > 0) {
        if (serviceTypeChart) serviceTypeChart.destroy();
        serviceTypeChart = new Chart(serviceTypeChartRef.value.getContext('2d'), {
            type: 'bar',
            data: {
                labels: serviceLabels,
                datasets: [{ label: 'Leads', data: serviceData, backgroundColor: 'rgba(59, 130, 246, 0.7)', borderColor: 'rgb(59, 130, 246)', borderWidth: 1 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }

    const monthly = props.analytics?.monthly_transfers || {};
    const monthLabels = Object.keys(monthly).sort().map((m) => {
        const [y, mo] = m.split('-');
        return new Date(parseInt(y, 10), parseInt(mo, 10) - 1, 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    });
    const monthData = Object.keys(monthly).sort().map((m) => monthly[m] ?? 0);

    if (monthlyChartRef.value && monthLabels.length > 0) {
        if (monthlyChart) monthlyChart.destroy();
        monthlyChart = new Chart(monthlyChartRef.value.getContext('2d'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Transfers',
                        data: monthData,
                        fill: true,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                    },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    }
}

onMounted(() => setTimeout(initCharts, 100));
watch(() => props.analytics, () => setTimeout(initCharts, 100), { deep: true });
</script>

<template>
    <Head title="Visa Department - Analytics" />
    <div class="container mx-auto py-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-primary px-4 py-2.5 text-primary-foreground shadow-md">
            <div class="relative z-10 flex items-center justify-between gap-2 flex-wrap">
                <div>
                    <h1 class="text-base font-semibold tracking-tight">Visa Department · Analytics</h1>
                    <p class="mt-0.5 text-[11px] text-primary-foreground/90">Status and service type distribution</p>
                </div>
                <nav class="flex flex-wrap gap-1">
                    <Link :href="route('departments.visa.index')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Dashboard</Link>
                    <Link :href="route('departments.visa.leads')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Leads</Link>
                    <Link :href="route('departments.visa.documents')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Documents</Link>
                    <span class="text-primary-foreground/50">·</span>
                    <span class="text-[11px] text-primary-foreground px-2 py-1">Analytics</span>
                    <Link :href="route('departments.visa.exports')" class="text-[11px] text-primary-foreground/80 hover:text-primary-foreground px-2 py-1 rounded">Exports</Link>
                </nav>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-linear-to-l from-primary-foreground/10 to-transparent" aria-hidden="true" />
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
            <Card>
                <CardHeader class="py-2 px-3">
                    <div class="flex items-center gap-2">
                        <PieChart class="h-4 w-4 text-primary" />
                        <CardTitle class="text-xs">Status distribution</CardTitle>
                    </div>
                    <CardDescription class="text-[11px]">Leads by department status</CardDescription>
                </CardHeader>
                <CardContent class="px-3 pb-3">
                    <div class="h-48">
                        <canvas ref="statusChartRef" />
                    </div>
                    <p v-if="!Object.keys(analytics?.status_distribution || {}).length" class="flex h-48 items-center justify-center text-xs text-muted-foreground">
                        No data yet. Transfer leads to the visa department to see distribution.
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="py-2 px-3">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-4 w-4 text-primary" />
                        <CardTitle class="text-xs">Service type</CardTitle>
                    </div>
                    <CardDescription class="text-[11px]">Leads by visa/service type</CardDescription>
                </CardHeader>
                <CardContent class="px-3 pb-3">
                    <div class="h-48">
                        <canvas ref="serviceTypeChartRef" />
                    </div>
                    <p v-if="!Object.keys(analytics?.service_type_breakdown || {}).length" class="flex h-48 items-center justify-center text-xs text-muted-foreground">
                        No data yet.
                    </p>
                </CardContent>
            </Card>
        </div>
        <Card>
            <CardHeader class="py-2 px-3">
                <div class="flex items-center gap-2">
                    <TrendingUp class="h-4 w-4 text-primary" />
                    <CardTitle class="text-xs">Monthly transfers</CardTitle>
                </div>
                <CardDescription class="text-[11px]">Leads transferred to Visa department (last 6 months)</CardDescription>
            </CardHeader>
            <CardContent class="px-3 pb-3">
                <div class="h-48">
                    <canvas ref="monthlyChartRef" />
                </div>
                <p v-if="!Object.keys(analytics?.monthly_transfers || {}).length" class="flex h-48 items-center justify-center text-xs text-muted-foreground">
                    No transfer data for this period.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
