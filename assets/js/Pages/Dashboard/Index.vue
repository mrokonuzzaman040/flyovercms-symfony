<script setup>
import { ref, computed, onMounted, onUpdated, nextTick, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
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

// Register Chart.js components
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
import {
    Users,
    UserPlus,
    CalendarCheck,
    Trophy,
    Percent,
    Building2,
    ArrowRightLeft,
    AlertTriangle,
    Clock,
    CreditCard,
    CheckCircle,
    Cake,
    Briefcase,
    Globe,
    TrendingUp,
    Filter,
    Loader2,
    GripVertical,
    BarChart3,
    AreaChart,
    PieChart,
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const props = defineProps({
    stats: { type: Object, required: true },
    recentActivities: { type: Array, default: () => [] },
    chartData: { type: Object, default: () => ({}) },
    topPerformers: { type: Array, default: () => [] },
    statusConfig: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

/** Today's date in Y-m-d for filtering leads created today */
const todayDate = computed(() => new Date().toISOString().split('T')[0]);

// Chart state (6M = monthly default; 7D/30D = daily)
const activePeriod = ref('30D');
const startDate = ref(getDefaultStartDate());
const endDate = ref(getDefaultEndDate());
const isLoadingChart = ref(false);
let monthlyChart = null;
let barChart = null;
let areaChart = null;
let statusBarChart = null;
let conversionChart = null;

// Draggable sections
const dashboardContainer = ref(null);
let sortableInstance = null;

// Sections order - can be persisted to localStorage
const sectionsOrder = ref([
    'stats',
    'insights',
    'lineChart',
    'statusChart',
    'barChart',
    'areaChart',
    'priorityChart',
    'serviceChart',
    'statusBarChart',
    'conversionChart',
    'activities',
]);

function getDefaultStartDate() {
    const date = new Date();
    date.setMonth(date.getMonth() - 6);
    return date.toISOString().split('T')[0];
}

function getDefaultEndDate() {
    return new Date().toISOString().split('T')[0];
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusColor = (status) => {
    return props.statusConfig[status]?.color || '#6b7280';
};

const getStatusTitle = (status) => {
    if (props.statusConfig[status]?.title) {
        return props.statusConfig[status].title;
    }
    return status?.charAt(0).toUpperCase() + status?.slice(1).replace(/_/g, ' ') || 'Unknown';
};

// Chart functions
const generateChartData = (period) => {
    const isDaily = period === '7D' || period === '30D';
    const days = period === '7D' ? 7 : (period === '30D' ? 30 : null);
    const months = !isDaily ? (period === '6M' ? 6 : 12) : null;

    if (isDaily && days) {
        const labels = [];
        const data = [];
        const chartSource = period === '7D' ? props.chartData.dailyData7D : props.chartData.dailyData30D;
        const start = new Date();
        start.setDate(start.getDate() - (days - 1));
        start.setHours(0, 0, 0, 0);

        for (let i = 0; i < days; i++) {
            const d = new Date(start);
            d.setDate(d.getDate() + i);
            const dayKey = d.toISOString().split('T')[0];
            labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            data.push(chartSource?.[dayKey] ?? 0);
        }
        return { labels, data };
    }

    const labels = [];
    const data = [];
    const chartSource = period === '6M' ? props.chartData.monthlyData6M : props.chartData.monthlyData12M;

    for (let i = months - 1; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        const monthKey = date.toISOString().slice(0, 7);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' }));
        data.push(chartSource?.[monthKey] || 0);
    }

    return { labels, data };
};

const updateChart = (period) => {
    activePeriod.value = period;
    const { labels, data } = generateChartData(period);

    if (monthlyChart) {
        monthlyChart.data.labels = labels;
        monthlyChart.data.datasets[0].data = data;
        monthlyChart.update('active');
    }
    if (barChart) {
        barChart.data.labels = labels;
        barChart.data.datasets[0].data = data;
        barChart.update('active');
    }
    if (areaChart) {
        areaChart.data.labels = labels;
        areaChart.data.datasets[0].data = data;
        areaChart.update('active');
    }
    if (conversionChart) {
        conversionChart.data.labels = labels;
        conversionChart.data.datasets[0].data = labels.map(() => Math.random() * 20 + 10);
        conversionChart.update('active');
    }
};

const applyDateRange = async () => {
    if (!startDate.value || !endDate.value) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date must be before end date');
        return;
    }

    isLoadingChart.value = true;
    activePeriod.value = null;

    try {
        const response = await fetch(`/api/dashboard/chart-data?start_date=${startDate.value}&end_date=${endDate.value}`);
        const result = await response.json();

        if (monthlyChart && result) {
            monthlyChart.data.labels = result.labels;
            monthlyChart.data.datasets[0].data = result.data;
            monthlyChart.update('active');
        }
    } catch (error) {
        console.error('Error fetching chart data:', error);
        alert('Error loading chart data. Please try again.');
    } finally {
        isLoadingChart.value = false;
    }
};

// Chart rendering
onMounted(() => {
    loadSavedOrder();
    // Wait for DOM to be fully rendered
    nextTick(() => {
        setTimeout(() => {
            try {
                initCharts();
                nextTick(() => initSortable());
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        }, 300);
    });
});

// Re-initialize charts when component updates
onUpdated(() => {
    if (dashboardContainer.value) {
        // Check if charts need to be re-initialized
        const monthlyCtx = document.getElementById('monthlyLeadsChart');
        if (monthlyCtx && !monthlyChart) {
            setTimeout(() => {
                try {
                    initCharts();
                } catch (error) {
                    console.error('Error re-initializing charts:', error);
                }
            }, 100);
        }
    }
});

// Initialize sortable for drag and drop
const initSortable = () => {
    nextTick(async () => {
        if (dashboardContainer.value) {
            const { default: Sortable } = await import('sortablejs');
            // Destroy existing instance if it exists
            if (sortableInstance) {
                sortableInstance.destroy();
                sortableInstance = null;
            }

            // Wait a bit for DOM to be fully ready
            setTimeout(() => {
                if (dashboardContainer.value) {
                    sortableInstance = Sortable.create(dashboardContainer.value, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        filter: '.no-drag',
                        draggable: '[data-section], [data-card]',
                        forceFallback: false,
                        fallbackOnBody: false,
                        swapThreshold: 0.65,
                        group: 'dashboard',
                        onStart: () => {
                            // Add visual feedback when dragging starts
                            document.body.style.cursor = 'grabbing';
                        },
                        onEnd: (evt) => {
                            document.body.style.cursor = '';
                            // Save order to localStorage - both sections and cards
                            const items = Array.from(dashboardContainer.value.children);
                            const order = items.map(item => {
                                if (item.dataset.section) return `section:${item.dataset.section}`;
                                if (item.dataset.card) return `card:${item.dataset.card}`;
                                return null;
                            }).filter(Boolean);
                            localStorage.setItem('dashboardOrder', JSON.stringify(order));
                        },
                    });
                }
            }, 100);
        }
    });
};

// Load saved order from localStorage
const loadSavedOrder = () => {
    const saved = localStorage.getItem('dashboardSectionsOrder');
    if (saved) {
        try {
            sectionsOrder.value = JSON.parse(saved);
        } catch (e) {
            console.error('Error loading saved order:', e);
        }
    }
};

const initCharts = () => {
    // Destroy existing charts if they exist
    if (monthlyChart) {
        monthlyChart.destroy();
        monthlyChart = null;
    }
    if (barChart) {
        barChart.destroy();
        barChart = null;
    }
    if (areaChart) {
        areaChart.destroy();
        areaChart = null;
    }
    if (statusBarChart) {
        statusBarChart.destroy();
        statusBarChart = null;
    }
    if (conversionChart) {
        conversionChart.destroy();
        conversionChart = null;
    }

    // Leads Over Time Chart (Line) – supports daily (7D/30D) and monthly (6M/12M)
    const monthlyCtx = document.getElementById('monthlyLeadsChart');
    const hasTimeData = props.chartData && (
        props.chartData.dailyData7D !== undefined ||
        props.chartData.dailyData30D !== undefined ||
        props.chartData.monthlyData6M !== undefined
    );
    if (monthlyCtx && props.chartData && hasTimeData) {
        const { labels, data } = generateChartData(activePeriod.value);

        // Ensure canvas has dimensions
        if (monthlyCtx.offsetWidth === 0 || monthlyCtx.offsetHeight === 0) {
            console.warn('Monthly chart canvas has no dimensions');
        }

        try {
            monthlyChart = new Chart(monthlyCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Leads',
                        data,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 800,
                        easing: 'easeInOutQuart',
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(243, 244, 246, 0.5)' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating monthly chart:', error);
        }
    }

    // Bar Chart - Leads by Day/Month
    const barCtx = document.getElementById('barChart');
    if (barCtx && props.chartData && hasTimeData) {
        const { labels, data } = generateChartData(activePeriod.value);

        if (barCtx.offsetWidth === 0 || barCtx.offsetHeight === 0) {
            console.warn('Bar chart canvas has no dimensions');
        }

        try {
            barChart = new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Leads',
                        data,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 800,
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(243, 244, 246, 0.5)' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating bar chart:', error);
        }
    }

    // Area Chart - Leads Trend (daily or monthly)
    const areaCtx = document.getElementById('areaChart');
    if (areaCtx && props.chartData && hasTimeData) {
        const { labels, data } = generateChartData(activePeriod.value);

        try {
            areaChart = new Chart(areaCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Leads',
                        data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.3)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 800,
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(243, 244, 246, 0.5)' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating area chart:', error);
        }
    }

    // Status Bar Chart
    const statusBarCtx = document.getElementById('statusBarChart');
    if (statusBarCtx && props.chartData && props.chartData.statusData && Object.keys(props.chartData.statusData).length > 0) {
        const statusLabels = Object.keys(props.chartData.statusData);
        const statusValues = Object.values(props.chartData.statusData);
        const statusColors = statusLabels.map(s => getStatusColor(s));

        try {
            statusBarChart = new Chart(statusBarCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: statusLabels.map(s => getStatusTitle(s)),
                    datasets: [{
                        label: 'Leads',
                        data: statusValues,
                        backgroundColor: statusColors,
                        borderColor: statusColors,
                        borderWidth: 1,
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    animation: {
                        duration: 800,
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(243, 244, 246, 0.5)' } },
                        y: { grid: { display: false } },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating status bar chart:', error);
        }
    }

    // Conversion Rate Chart (Line, daily or monthly)
    const conversionCtx = document.getElementById('conversionChart');
    if (conversionCtx && props.chartData && hasTimeData) {
        const { labels } = generateChartData(activePeriod.value);
        // Calculate conversion rates (mock data - you can enhance this with real data)
        const conversionData = labels.map(() => Math.random() * 20 + 10);

        try {
            conversionChart = new Chart(conversionCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Conversion Rate %',
                        data: conversionData,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#8b5cf6',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 800,
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: 'rgba(243, 244, 246, 0.5)' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating conversion chart:', error);
        }
    }

    // Status Pie Chart
    const statusCtx = document.getElementById('statusPieChart');
    if (statusCtx && props.chartData && props.chartData.statusData && Object.keys(props.chartData.statusData).length > 0) {
        const statusLabels = Object.keys(props.chartData.statusData);
        const statusValues = Object.values(props.chartData.statusData);
        const statusColors = statusLabels.map(s => getStatusColor(s));

        try {
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels.map(s => getStatusTitle(s)),
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusColors,
                        borderWidth: 0,
                        cutout: '60%',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' },
                        },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating status pie chart:', error);
        }
    }

    // Priority Pie Chart
    const priorityCtx = document.getElementById('priorityPieChart');
    if (priorityCtx && props.chartData && props.chartData.priorityData && Object.keys(props.chartData.priorityData).length > 0) {
        const priorityLabels = Object.keys(props.chartData.priorityData);
        const priorityValues = Object.values(props.chartData.priorityData);
        const priorityColorsMap = { low: '#0ea5e9', normal: '#6b7280', high: '#f59e0b', urgent: '#ef4444' };
        const priorityColors = priorityLabels.map(p => priorityColorsMap[p] || '#6b7280');

        try {
            new Chart(priorityCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: priorityLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data: priorityValues,
                        backgroundColor: priorityColors,
                        borderWidth: 0,
                        cutout: '60%',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' },
                        },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating priority pie chart:', error);
        }
    }

    // Service Type Pie Chart
    const serviceCtx = document.getElementById('serviceTypePieChart');
    if (serviceCtx) {
        // Always create chart, even if no data (will show empty state)
        const serviceData = props.chartData?.serviceTypeData || {};
        const hasData = Object.keys(serviceData).length > 0;

        const serviceLabels = hasData ? Object.keys(serviceData) : ['No Data'];
        const serviceValues = hasData ? Object.values(serviceData) : [1];
        const serviceColors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#ef4444', '#6366f1'];

        try {
            new Chart(serviceCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: hasData
                        ? serviceLabels.map(l => l.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '))
                        : ['No Data'],
                    datasets: [{
                        data: serviceValues,
                        backgroundColor: hasData
                            ? serviceColors.slice(0, serviceLabels.length)
                            : ['#e5e7eb'],
                        borderWidth: 0,
                        cutout: '60%',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                filter: hasData ? undefined : () => false, // Hide legend if no data
                            },
                        },
                    },
                },
            });
        } catch (error) {
            console.error('Error creating service type pie chart:', error);
        }
    }
};
</script>

<template>

    <Head title="Dashboard" />

    <div ref="dashboardContainer" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Welcome Section (not draggable) -->
        <div
            class="no-drag col-span-full flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Welcome back, {{ user?.name }}!</h1>
                <p class="text-xs text-muted-foreground">Here's your comprehensive dashboard overview with analytics and
                    insights.</p>
            </div>
            <div v-if="user?.department"
                class="flex items-center gap-2 bg-green-50 dark:bg-green-950/30 border border-green-500 rounded-lg px-3 py-2">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded flex items-center justify-center">
                    <Building2 class="h-4 w-4 text-white" />
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-green-700 dark:text-green-400 uppercase tracking-wide">Your
                        Department</p>
                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ user.department.name }}</p>
                </div>
            </div>
        </div>

        <!-- Main Statistics Cards - Each card is individually draggable -->
        <!-- Total Leads -->
        <div data-card="totalLeads" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.index')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Total Leads</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.totalLeads) }}</p>
                                <p class="text-[10px] text-blue-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <TrendingUp class="h-2.5 w-2.5" /> All time
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow shadow-blue-500/20">
                                <Users class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="absolute top-0 right-0 w-16 h-16 bg-blue-500/10 rounded-full -translate-y-6 translate-x-6" />
                </Card>
            </Link>
        </div>

        <!-- New Today -->
        <div v-if="(stats.newLeads || 0) > 0" data-card="newToday" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.index', { date_from: todayDate, date_to: todayDate })" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    New Today</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.newLeads) }}</p>
                                <p class="text-[10px] text-amber-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <CalendarCheck class="h-2.5 w-2.5" /> Today's leads
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow shadow-amber-500/20">
                                <UserPlus class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="absolute top-0 right-0 w-16 h-16 bg-amber-500/10 rounded-full -translate-y-6 translate-x-6" />
                </Card>
            </Link>
        </div>

        <!-- Follow-Ups Today -->
        <div v-if="(stats.followUpsDueToday || 0) > 0" data-card="followUps" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.status', { status: 'follow_up', follow_up_scope: 'today' })" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Follow-Ups</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.followUpsDueToday) }}</p>
                                <p class="text-[10px] text-green-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <Clock class="h-2.5 w-2.5" /> Pending today
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow shadow-green-500/20">
                                <CalendarCheck class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-full -translate-y-6 translate-x-6" />
                </Card>
            </Link>
        </div>

        <!-- Conversion (rate + count merged) -->
        <div v-if="(stats.convertedLeads || 0) > 0" data-card="conversionRate" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.status', 'converted')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Conversion</p>
                                <p class="text-xl font-bold">{{ stats.conversionRate }}%</p>
                                <p class="text-[10px] text-purple-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <TrendingUp class="h-2.5 w-2.5" />
                                    {{ formatNumber(stats.convertedLeads) }} converted
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow shadow-purple-500/20">
                                <Percent class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="absolute top-0 right-0 w-16 h-16 bg-purple-500/10 rounded-full -translate-y-6 translate-x-6" />
                </Card>
            </Link>
        </div>

        <!-- Office Visits Today -->
        <div v-if="(stats.officeVisitsToday || 0) > 0" data-card="officeVisits" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.status', 'office_visited')" class="block group">
                <Card class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Office Visits</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.officeVisitsToday) }}</p>
                                <p class="text-[10px] text-cyan-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <Building2 class="h-2.5 w-2.5" /> Today
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center shadow shadow-cyan-500/20">
                                <Building2 class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="absolute top-0 right-0 w-16 h-16 bg-cyan-500/10 rounded-full -translate-y-6 translate-x-6" />
                </Card>
            </Link>
        </div>

        <!-- Transfer Pending Leads (leads I have transferred, awaiting recipient) -->
        <div v-if="(stats.pendingTransfers || 0) > 0" data-card="transferPendingLeads" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('transfers.pending')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer border-l-2 border-l-sky-500">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Transfer Pending</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.pendingTransfers) }}</p>
                                <p class="text-[10px] text-sky-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <ArrowRightLeft class="h-2.5 w-2.5" /> Leads awaiting response
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-sky-500 to-sky-600 rounded-lg flex items-center justify-center shadow shadow-sky-500/20">
                                <ArrowRightLeft class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Department Pending Transfers (conditional; only when user has a department) -->
        <div v-if="stats.pendingDepartmentTransfers > 0 && user?.department" data-card="pendingDepartmentTransfers"
            class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('departments.transfers.pending', user?.department?.name?.toLowerCase() ?? '')"
                class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer border-l-2 border-l-pink-500">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Dept. Transfers</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.pendingDepartmentTransfers) }}</p>
                                <p class="text-[10px] text-pink-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <Building2 class="h-2.5 w-2.5" /> Pending
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg flex items-center justify-center shadow shadow-pink-500/20">
                                <Building2 class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Pending Transfers (conditional: same scope as /transfers/pending page) -->
        <div v-if="stats.pendingUserTransfers > 0" data-card="pendingTransfers" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('transfers.pending')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer border-l-2 border-l-amber-500">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Transfers</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.pendingUserTransfers) }}</p>
                                <p class="text-[10px] text-amber-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <ArrowRightLeft class="h-2.5 w-2.5" /> Review
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow shadow-amber-500/20">
                                <ArrowRightLeft class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Urgent Leads (conditional) -->
        <div v-if="stats.urgentLeads > 0" data-card="urgentLeads" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.index', { priority: 'urgent,high' })" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer border-l-2 border-l-red-500">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Urgent</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.urgentLeads) }}</p>
                                <p class="text-[10px] text-red-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <AlertTriangle class="h-2.5 w-2.5" /> View
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow shadow-red-500/20">
                                <AlertTriangle class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Overdue Follow-ups (conditional) -->
        <div v-if="stats.overdueFollowUps > 0" data-card="overdueFollowUps" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.overdue-follow-ups')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer border-l-2 border-l-red-500">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Overdue</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.overdueFollowUps) }}</p>
                                <p class="text-[10px] text-red-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <Clock class="h-2.5 w-2.5" /> Action
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow shadow-red-500/20">
                                <Clock class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Waiting for Payment (conditional) -->
        <div v-if="stats.waitingForPayment > 0" data-card="waitingForPayment" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.index')" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Payment</p>
                                <p class="text-xl font-bold">{{ formatNumber(stats.waitingForPayment) }}</p>
                                <p class="text-[10px] text-amber-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <CreditCard class="h-2.5 w-2.5" /> Waiting
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow shadow-amber-500/20">
                                <CreditCard class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Approved Leads (conditional) -->
        <div v-if="stats.approvedLeads > 0" data-card="approvedLeads" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Link :href="route('leads.status', { status: 'approved' })" class="block group">
                <Card
                    class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-pointer">
                    <CardContent class="p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                    Approved</p>
                                <p class="text-xl font-bold">
                                    {{ formatNumber(stats.approvedLeads) }}
                                    <span v-if="stats.totalLeads > 0"
                                        class="text-xs font-normal text-muted-foreground ml-0.5">({{
                                            ((stats.approvedLeads / stats.totalLeads) * 100).toFixed(1)
                                        }}%)</span>
                                </p>
                                <p class="text-[10px] text-green-600 font-medium mt-0.5 flex items-center gap-0.5">
                                    <CheckCircle class="h-2.5 w-2.5" /> View
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow shadow-green-500/20">
                                <CheckCircle class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Lead Insights Cards - Each card is individually draggable -->
        <!-- Average Lead Age -->
        <div data-card="averageLeadAge" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md">
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                                Average Lead Age</p>
                            <p class="text-xl font-bold">
                                {{ stats.averageAge !== null ? stats.averageAge : 'N/A' }}
                                <span v-if="stats.averageAge !== null"
                                    class="text-xs font-normal text-muted-foreground">years</span>
                            </p>
                            <p class="text-[10px] text-cyan-600 font-medium mt-0.5 flex items-center gap-0.5">
                                <Cake class="h-2.5 w-2.5" /> Based on DOB
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center shadow shadow-cyan-500/20">
                            <Cake class="h-4 w-4 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Most Common Profession -->
        <div data-card="topProfession" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md">
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">Top
                                Profession</p>
                            <p class="text-sm font-bold truncate">{{ stats.topProfession?.name || 'N/A' }}</p>
                            <p class="text-[10px] text-pink-600 font-medium mt-0.5 flex items-center gap-0.5">
                                <Briefcase class="h-2.5 w-2.5" />
                                <span v-if="stats.topProfession?.count">{{ stats.topProfession.count }} leads</span>
                                <span v-else>Top occupation</span>
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg flex items-center justify-center shadow shadow-pink-500/20 shrink-0 ml-2">
                            <Briefcase class="h-4 w-4 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Preferred Country -->
        <div data-card="topCountry" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card class="relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md">
                <CardContent class="p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wide mb-0.5">Top
                                Country</p>
                            <p class="text-sm font-bold truncate">{{ stats.topCountry || 'N/A' }}</p>
                            <p class="text-[10px] text-teal-600 font-medium mt-0.5 flex items-center gap-0.5">
                                <Globe class="h-2.5 w-2.5" /> Top destination
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow shadow-teal-500/20 shrink-0 ml-2">
                            <Globe class="h-4 w-4 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Section -->
        <!-- Monthly Leads Chart (Line) -->
        <div data-section="lineChart" class="relative group col-span-full lg:col-span-4">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <div class="flex flex-col gap-2">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                            Leads Over Time
                        </CardTitle>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-1.5">
                                <Input v-model="startDate" type="date" class="w-28 h-7 text-xs" />
                                <span class="text-muted-foreground text-xs">to</span>
                                <Input v-model="endDate" type="date" class="w-28 h-7 text-xs" />
                                <Button size="sm" class="h-7 text-xs px-2 gap-1" @click="applyDateRange"
                                    :disabled="isLoadingChart">
                                    <Loader2 v-if="isLoadingChart" class="h-3 w-3 animate-spin" />
                                    <Filter v-else class="h-3 w-3" />
                                    Apply
                                </Button>
                            </div>
                            <div class="flex gap-1">
                                <Button size="sm" class="h-7 text-xs px-2"
                                    :variant="activePeriod === '7D' ? 'default' : 'secondary'"
                                    @click="updateChart('7D')">
                                    7D
                                </Button>
                                <Button size="sm" class="h-7 text-xs px-2"
                                    :variant="activePeriod === '30D' ? 'default' : 'secondary'"
                                    @click="updateChart('30D')">
                                    30D
                                </Button>
                                <Button size="sm" class="h-7 text-xs px-2"
                                    :variant="activePeriod === '6M' ? 'default' : 'secondary'"
                                    @click="updateChart('6M')">
                                    6M
                                </Button>
                                <Button size="sm" class="h-7 text-xs px-2"
                                    :variant="activePeriod === '12M' ? 'default' : 'secondary'"
                                    @click="updateChart('12M')">
                                    12M
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="monthlyLeadsChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Pie Chart -->
        <div data-section="statusChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                        Leads by Status
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="statusPieChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Bar Chart -->
        <div data-section="barChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <BarChart3 class="h-4 w-4 text-blue-500" />
                        Leads by Day / Month (Bar)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="barChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Area Chart -->
        <div data-section="areaChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <AreaChart class="h-4 w-4 text-green-500" />
                        Leads Trend – Daily / Monthly (Area)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="areaChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Bar Chart (Horizontal) -->
        <div data-section="statusBarChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <BarChart3 class="h-4 w-4 text-purple-500" />
                        Leads by Status (Bar)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="statusBarChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Conversion Rate Chart -->
        <div data-section="conversionChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <TrendingUp class="h-4 w-4 text-purple-500" />
                        Conversion Rate Trend
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="conversionChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Priority Pie Chart -->
        <div data-section="priorityChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-amber-500 to-amber-600 rounded" />
                        Leads by Priority
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full">
                        <canvas id="priorityPieChart" class="w-full h-full"></canvas>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Service Type Pie Chart -->
        <div data-section="serviceChart" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded" />
                        Leads by Service Type
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="h-[220px] w-full relative">
                        <canvas id="serviceTypePieChart" class="w-full h-full"></canvas>
                        <div v-if="!chartData?.serviceTypeData || Object.keys(chartData.serviceTypeData || {}).length === 0"
                            class="absolute inset-0 flex items-center justify-center text-muted-foreground">
                            <div class="text-center">
                                <PieChart class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p class="text-xs font-medium">No service type data available</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Activities -->
        <div data-card="recentActivities" class="relative group col-span-full lg:col-span-4">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                        Recent Activities
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-2">
                    <div class="max-h-[280px] overflow-y-auto space-y-0.5">
                        <template v-if="recentActivities.length">
                            <div v-for="activity in recentActivities" :key="activity.id"
                                class="flex items-center gap-2 p-2 rounded hover:bg-muted/50 transition-colors">
                                <Avatar class="h-7 w-7 bg-gradient-to-br from-red-500 to-red-600">
                                    <AvatarFallback class="text-white font-medium text-[10px]">
                                        {{ getInitials(activity.full_name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium truncate">{{ activity.full_name }}</p>
                                    <p class="text-[10px] text-muted-foreground truncate">{{ activity.email }}</p>
                                    <div class="flex items-center gap-1 mt-0.5 flex-wrap">
                                        <Badge variant="secondary" class="text-[10px] h-4 px-1 capitalize">{{
                                            activity.status || 'new' }}</Badge>
                                        <Badge v-if="activity.service_type" variant="outline"
                                            class="text-[10px] h-4 px-1 capitalize">{{
                                                activity.service_type?.replace('_', ' ') }}</Badge>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-[10px] text-muted-foreground">{{ formatDate(activity.created_at) }}
                                    </p>
                                    <p v-if="activity.branch" class="text-[10px] text-muted-foreground">{{
                                        activity.branch.name }}</p>
                                </div>
                            </div>
                        </template>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Users class="h-8 w-8 mx-auto mb-2 opacity-30" />
                            <p class="text-xs font-medium">No recent activities</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Top Performers -->
        <div data-card="topPerformers" class="relative group">
            <div
                class="absolute left-2 top-2 opacity-30 hover:opacity-100 transition-opacity cursor-move drag-handle z-50 bg-background/90 backdrop-blur-sm rounded p-1.5 border border-border/50 shadow-sm">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
            </div>
            <Card>
                <CardHeader class="border-b p-3">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <div class="w-0.5 h-4 bg-gradient-to-b from-red-500 to-red-600 rounded" />
                        Top Performers
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-2">
                    <div class="space-y-0.5">
                        <template v-if="topPerformers.length">
                            <div v-for="(performer, index) in topPerformers" :key="performer.created_by"
                                class="flex items-center gap-2 p-2 rounded hover:bg-muted/50 transition-colors">
                                <div
                                    class="w-6 h-6 bg-gradient-to-br from-amber-500 to-amber-600 rounded-full flex items-center justify-center text-white font-medium text-[10px]">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium truncate">{{ performer.creator?.name || 'Unknown' }}
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">{{ performer.lead_count }} leads</p>
                                </div>
                                <Badge variant="secondary"
                                    class="text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    {{ performer.lead_count }}
                                </Badge>
                            </div>
                        </template>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Trophy class="h-8 w-8 mx-auto mb-2 opacity-30" />
                            <p class="text-xs font-medium">No data available</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

/* Sortable.js classes */
.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    outline: 2px solid hsl(var(--primary));
    outline-offset: 2px;
}

/* Ensure chart containers are visible */
canvas {
    display: block !important;
    max-width: 100%;
    height: auto;
}
</style>
