<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
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
import {
    BarChart3,
    TrendingUp,
    TrendingDown,
    DollarSign,
    Send,
    CheckCircle,
    XCircle,
    Mail,
    MessageSquare,
    Phone,
    Calendar,
    Filter,
    Download,
} from 'lucide-vue-next';

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
    overallStats: { type: Object, required: true },
    channelComparison: { type: Object, required: true },
    bestSendingTimes: { type: Object, default: () => ({}) },
    period: { type: String, default: '30' },
    startDate: { type: String, required: true },
    endDate: { type: String, required: true },
});

const periodOptions = [
    { value: '7', label: 'Last 7 days' },
    { value: '30', label: 'Last 30 days' },
    { value: '90', label: 'Last 90 days' },
    { value: '365', label: 'Last year' },
];

const selectedPeriod = ref(props.period);
let channelChart = null;
let timeChart = null;

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 2,
    }).format(value);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US').format(value);
};

const updatePeriod = (period) => {
    selectedPeriod.value = period;
    router.get(route('marketing.analytics.dashboard'), { period }, { preserveState: true });
};

onMounted(() => {
    // Channel Comparison Chart
    const channelCtx = document.getElementById('channelChart');
    if (channelCtx && props.channelComparison) {
        const channels = Object.keys(props.channelComparison);
        const deliveryRates = channels.map(ch => props.channelComparison[ch].delivery_rate || 0);
        const costs = channels.map(ch => props.channelComparison[ch].cost_per_message || 0);

        channelChart = new Chart(channelCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: channels.map(ch => ch.toUpperCase()),
                datasets: [
                    {
                        label: 'Delivery Rate (%)',
                        data: deliveryRates,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Cost per Message',
                        data: costs,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (context.datasetIndex === 0) {
                                    return `Delivery Rate: ${context.parsed.y.toFixed(2)}%`;
                                }
                                return `Cost: ${formatCurrency(context.parsed.y)}`;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Delivery Rate (%)',
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cost per Message',
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
            },
        });
    }

    // Best Sending Times Chart
    const timeCtx = document.getElementById('timeChart');
    if (timeCtx && props.bestSendingTimes) {
        const hours = Object.keys(props.bestSendingTimes).sort((a, b) => a - b);
        const openRates = hours.map(h => props.bestSendingTimes[h].open_rate || 0);

        timeChart = new Chart(timeCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: hours.map(h => `${h}:00`),
                datasets: [
                    {
                        label: 'Open Rate (%)',
                        data: openRates,
                        borderColor: 'rgba(139, 92, 246, 1)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Open Rate (%)',
                        },
                    },
                },
            },
        });
    }
});

onBeforeUnmount(() => {
    if (channelChart) {
        channelChart.destroy();
        channelChart = null;
    }
    if (timeChart) {
        timeChart.destroy();
        timeChart = null;
    }
});
</script>

<template>
    <Head title="Marketing Analytics" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Marketing Analytics</h1>
                <p class="text-xs text-muted-foreground">Campaign performance and insights</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" class="gap-1.5">
                    <Download class="h-3.5 w-3.5" />
                    Export
                </Button>
            </div>
        </div>

        <!-- Period Filter -->
        <Card>
            <CardContent class="p-3">
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <span class="text-xs text-muted-foreground">Period:</span>
                    <div class="flex items-center gap-1">
                        <Button
                            v-for="option in periodOptions"
                            :key="option.value"
                            :variant="selectedPeriod === option.value ? 'default' : 'outline'"
                            size="sm"
                            class="h-7 text-xs px-2"
                            @click="updatePeriod(option.value)"
                        >
                            {{ option.label }}
                        </Button>
                    </div>
                    <span class="text-xs text-muted-foreground ml-2">
                        {{ startDate }} to {{ endDate }}
                    </span>
                </div>
            </CardContent>
        </Card>

        <!-- Overall Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Total Campaigns</p>
                            <p class="text-lg font-semibold">{{ formatNumber(overallStats.total_campaigns) }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <BarChart3 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Total Sent</p>
                            <p class="text-lg font-semibold">{{ formatNumber(overallStats.total_sent) }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                            <Send class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Delivery Rate</p>
                            <p class="text-lg font-semibold">{{ overallStats.average_delivery_rate }}%</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <CheckCircle class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground mb-1">Total Cost</p>
                            <p class="text-lg font-semibold">{{ formatCurrency(overallStats.total_cost) }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center">
                            <DollarSign class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Channel Comparison -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                    Channel Comparison
                </CardTitle>
                <CardDescription class="text-xs">Compare performance across SMS, Email, and WhatsApp (WasenderAPI)</CardDescription>
            </CardHeader>
            <CardContent class="p-4">
                <div class="h-64">
                    <canvas id="channelChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div
                        v-for="(data, channel) in channelComparison"
                        :key="channel"
                        class="border rounded-md p-3 space-y-1"
                    >
                        <div class="flex items-center gap-2">
                            <component
                                :is="channel === 'sms' ? Phone : channel === 'email' ? Mail : MessageSquare"
                                class="h-4 w-4 text-muted-foreground"
                            />
                            <span class="text-xs font-semibold uppercase">{{ channel }}</span>
                        </div>
                        <p class="text-[10px] text-muted-foreground">Campaigns: {{ data.campaigns_count }}</p>
                        <p class="text-[10px] text-muted-foreground">Sent: {{ formatNumber(data.total_sent) }}</p>
                        <p class="text-[10px] text-muted-foreground">Delivery: {{ data.delivery_rate }}%</p>
                        <p class="text-[10px] text-muted-foreground">Cost: {{ formatCurrency(data.total_cost) }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Best Sending Times -->
        <Card>
            <CardHeader class="border-b p-3">
                <CardTitle class="text-sm flex items-center gap-2">
                    <div class="w-0.5 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded" />
                    Best Sending Times
                </CardTitle>
                <CardDescription class="text-xs">Optimal hours for sending campaigns based on engagement</CardDescription>
            </CardHeader>
            <CardContent class="p-4">
                <div class="h-64">
                    <canvas id="timeChart"></canvas>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
