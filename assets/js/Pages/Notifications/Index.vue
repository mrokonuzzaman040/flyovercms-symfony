<script setup>
import { ref, shallowRef, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Checkbox } from '@/Components/ui/checkbox';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { useDebounceFn } from '@vueuse/core';
import { 
    Bell, Mail, CheckCircle, Trash2, CheckCheck, AlertCircle, 
    Info, AlertTriangle, XCircle, ExternalLink, Filter, X,
    Clock, MoreHorizontal, Search, ArrowUpDown, ArrowUp, ArrowDown,
    Calendar, RotateCcw, ChevronDown
} from 'lucide-vue-next';

defineOptions({ layout: DashboardLayout });

const page = usePage();
const getCsrfToken = () => page.props.csrf_token ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const props = defineProps({
    notifications: { type: Object, required: true },
    unreadCount: { type: Number, default: 0 },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const activeTab = ref('all');
const showFilters = ref(false);
const searchQuery = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
const categoryFilter = ref(props.filters?.category || '');
const priorityFilter = ref(props.filters?.priority || '');
const statusFilter = ref(props.filters?.is_read || '');
const dateFilter = ref(props.filters?.date || '');
const sortField = ref(props.filters?.sort || 'created_at');
const sortDirection = ref(props.filters?.direction || 'desc');

// Infinite scroll: extended list built from initial props + API load more
// shallowRef — list items are replaced wholesale, never deep-mutated
const extendedNotifications = shallowRef([]);
const currentPageNum = ref(1);
const lastPageNum = ref(1);
const loadingMoreNotifications = ref(false);
const loadMoreSentinelRef = ref(null);

watch(() => props.notifications, (val) => {
    if (!val?.data) return;
    extendedNotifications.value = [...val.data];
    currentPageNum.value = val.current_page ?? 1;
    lastPageNum.value = val.last_page ?? 1;
}, { immediate: true });

const loadMoreNotifications = async () => {
    if (loadingMoreNotifications.value || currentPageNum.value >= lastPageNum.value) return;

    loadingMoreNotifications.value = true;
    try {
        const url = new URL(route('notifications.api'));
        url.searchParams.set('per_page', '50');
        url.searchParams.set('page', String(currentPageNum.value + 1));
        if (typeFilter.value) url.searchParams.set('type', typeFilter.value);
        if (categoryFilter.value) url.searchParams.set('category', categoryFilter.value);
        if (priorityFilter.value) url.searchParams.set('priority', priorityFilter.value);
        if (statusFilter.value) url.searchParams.set('is_read', statusFilter.value);

        const response = await fetch(url.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            loadingMoreNotifications.value = false;
            return;
        }

        const data = await response.json();
        const list = data.notifications || [];
        if (list.length > 0) {
            extendedNotifications.value = [...extendedNotifications.value, ...list];
        }
        const pagination = data.pagination || {};
        currentPageNum.value = pagination.current_page ?? currentPageNum.value;
        lastPageNum.value = pagination.last_page ?? lastPageNum.value;
    } catch {
        // ignore
    } finally {
        loadingMoreNotifications.value = false;
    }
};

let loadMoreObserver = null;
onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') return;
    loadMoreObserver = new IntersectionObserver(
        (entries) => {
            if (!entries[0]?.isIntersecting) return;
            loadMoreNotifications();
        },
        { rootMargin: '200px', threshold: 0 }
    );
    nextTick(() => {
        if (loadMoreSentinelRef.value) loadMoreObserver.observe(loadMoreSentinelRef.value);
    });
});

onUnmounted(() => {
    if (loadMoreObserver && loadMoreSentinelRef.value) {
        try {
            loadMoreObserver.unobserve(loadMoreSentinelRef.value);
        } catch {
            // ref may be null
        }
    }
});

watch(loadMoreSentinelRef, (el, oldEl) => {
    if (!loadMoreObserver) return;
    if (oldEl) {
        try {
            loadMoreObserver.unobserve(oldEl);
        } catch {
            // ignore
        }
    }
    if (el) loadMoreObserver.observe(el);
});

// Bulk selection
const selectedNotifications = ref([]);
const selectAll = ref(false);

// Apply filters with debounce for search
const applyFilters = useDebounceFn(() => {
    router.get(route('notifications.index'), {
        search: searchQuery.value || undefined,
        type: typeFilter.value || undefined,
        category: categoryFilter.value || undefined,
        priority: priorityFilter.value || undefined,
        is_read: statusFilter.value || undefined,
        date: dateFilter.value || undefined,
        sort: sortField.value || undefined,
        direction: sortDirection.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch([searchQuery, typeFilter, categoryFilter, priorityFilter, statusFilter, dateFilter, sortField, sortDirection], () => {
    applyFilters();
});

watch(activeTab, (newTab) => {
    selectedNotifications.value = [];
    selectAll.value = false;
    if (newTab === 'unread') {
        statusFilter.value = '0';
    } else if (newTab === 'read') {
        statusFilter.value = '1';
    } else {
        statusFilter.value = '';
    }
});

const clearFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
    categoryFilter.value = '';
    priorityFilter.value = '';
    statusFilter.value = '';
    dateFilter.value = '';
    sortField.value = 'created_at';
    sortDirection.value = 'desc';
    router.get(route('notifications.index'));
};

const hasFilters = computed(() => {
    return searchQuery.value || typeFilter.value || categoryFilter.value || priorityFilter.value || statusFilter.value || dateFilter.value;
});

// Bulk selection functions
const toggleSelectAll = (checked) => {
    if (checked) {
        selectedNotifications.value = getFilteredNotifications.value.map(n => n.id);
    } else {
        selectedNotifications.value = [];
    }
    selectAll.value = checked;
};

const toggleNotification = (notificationId, checked) => {
    if (checked) {
        if (!selectedNotifications.value.includes(notificationId)) {
            selectedNotifications.value.push(notificationId);
        }
    } else {
        const index = selectedNotifications.value.indexOf(notificationId);
        if (index > -1) {
            selectedNotifications.value.splice(index, 1);
        }
    }
    selectAll.value = selectedNotifications.value.length > 0 && selectedNotifications.value.length === getFilteredNotifications.value.length;
};

watch(() => selectedNotifications.value.length, (length) => {
    selectAll.value = length > 0 && length === getFilteredNotifications.value.length;
});

// Bulk actions
const bulkMarkAsRead = () => {
    if (selectedNotifications.value.length === 0) return;

    Promise.all(
        selectedNotifications.value.map(id =>
            fetch(route('notifications.mark-read', id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
        )
    ).then(() => {
        selectedNotifications.value = [];
        selectAll.value = false;
        router.reload();
    });
};

const bulkMarkAsUnread = () => {
    if (selectedNotifications.value.length === 0) return;

    Promise.all(
        selectedNotifications.value.map(id =>
            fetch(route('notifications.mark-unread', id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            }).catch(() =>
                fetch(route('notifications.mark-read', id), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                })
            )
        )
    ).then(() => {
        selectedNotifications.value = [];
        selectAll.value = false;
        router.reload();
    });
};

const bulkDelete = () => {
    if (selectedNotifications.value.length === 0) return;
    if (!confirm(`Are you sure you want to delete ${selectedNotifications.value.length} notification(s)?`)) return;

    Promise.all(
        selectedNotifications.value.map(id =>
            fetch(route('notifications.destroy', id), {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
        )
    ).then(() => {
        selectedNotifications.value = [];
        selectAll.value = false;
        router.reload();
    });
};

const markAllAsRead = () => {
    fetch(route('notifications.mark-all-read'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                router.reload();
            }
        });
};

const markAsRead = (notification) => {
    fetch(route('notifications.mark-read', notification.id), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                router.reload();
            }
        });
};

// Handle action URL click: mark as read then redirect (no reload so redirect isn't cancelled)
const handleActionClick = async (notification, event) => {
    event?.preventDefault();
    if (!notification.action_url) return;

    if (!notification.is_read && notification.id) {
        try {
            await fetch(route('notifications.mark-read', notification.id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            });
        } catch {
            // Continue to redirect even if mark-read fails
        }
    }
    router.visit(notification.action_url);
};

const markAsUnread = (notification) => {
    fetch(route('notifications.mark-unread', notification.id), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                router.reload();
            }
        })
        .catch(() => {
            markAsRead(notification);
        });
};

const deleteNotification = (notification) => {
    fetch(route('notifications.destroy', notification.id), {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                router.reload();
            }
        });
};

const TYPE_ICONS = Object.freeze({
    success: CheckCircle,
    error: XCircle,
    warning: AlertTriangle,
    info: Info,
});
const getTypeIcon = (type) => TYPE_ICONS[type] || Bell;

const TYPE_COLORS = Object.freeze({
    success: 'text-green-600 bg-green-50 dark:bg-green-950/30',
    error: 'text-red-600 bg-red-50 dark:bg-red-950/30',
    warning: 'text-amber-600 bg-amber-50 dark:bg-amber-950/30',
    info: 'text-blue-600 bg-blue-50 dark:bg-blue-950/30',
});
const getTypeColor = (type) => TYPE_COLORS[type] || 'text-gray-600 bg-gray-50 dark:bg-gray-950/30';

const PRIORITY_ORDER = Object.freeze({ urgent: 4, high: 3, normal: 2, low: 1 });

const PRIORITY_COLORS = Object.freeze({
    urgent: 'bg-red-600 text-white',
    high: 'bg-orange-500 text-white',
    normal: 'bg-blue-500 text-white',
    low: 'bg-gray-400 text-white',
});
const getPriorityColor = (priority) => PRIORITY_COLORS[priority] || 'bg-gray-400 text-white';

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return d.toLocaleDateString();
};

const formatDateGroup = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const days = Math.floor(diff / 86400000);
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return d.toLocaleDateString('en-US', { weekday: 'long' });
    if (days < 30) return `${Math.floor(days / 7)} weeks ago`;
    return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const getSortIcon = (field) => {
    if (sortField.value !== field) return ArrowUpDown;
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const readCount = computed(() => {
    return extendedNotifications.value.filter(n => n.is_read).length;
});

const unreadNotifications = computed(() => {
    return extendedNotifications.value.filter(n => !n.is_read);
});

const readNotifications = computed(() => {
    return extendedNotifications.value.filter(n => n.is_read);
});

const allNotifications = computed(() => {
    return extendedNotifications.value;
});

const getFilteredNotifications = computed(() => {
    let notifications = [];
    if (activeTab.value === 'unread') {
        notifications = unreadNotifications.value;
    } else if (activeTab.value === 'read') {
        notifications = readNotifications.value;
    } else {
        notifications = allNotifications.value;
    }

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        notifications = notifications.filter(n => 
            n.title?.toLowerCase().includes(query) ||
            n.message?.toLowerCase().includes(query) ||
            n.category?.toLowerCase().includes(query)
        );
    }

    // Apply sorting
    notifications = [...notifications].sort((a, b) => {
        let aVal, bVal;
        
        switch (sortField.value) {
            case 'title':
                aVal = a.title || '';
                bVal = b.title || '';
                break;
            case 'priority':
                aVal = PRIORITY_ORDER[a.priority] || 0;
                bVal = PRIORITY_ORDER[b.priority] || 0;
                break;
            case 'type':
                aVal = a.type || '';
                bVal = b.type || '';
                break;
            case 'created_at':
            default:
                aVal = new Date(a.created_at).getTime();
                bVal = new Date(b.created_at).getTime();
                break;
        }
        
        if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });

    return notifications;
});

const groupedNotifications = computed(() => {
    const grouped = {};
    getFilteredNotifications.value.forEach(notification => {
        const dateKey = formatDateGroup(notification.created_at);
        if (!grouped[dateKey]) {
            grouped[dateKey] = [];
        }
        grouped[dateKey].push(notification);
    });
    return grouped;
});

const hasSelected = computed(() => selectedNotifications.value.length > 0);
</script>

<template>
    <Head title="Notifications" />

    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Notifications</h1>
                <p class="text-muted-foreground">Manage and view all your notifications</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" class="gap-1.5" @click="showFilters = !showFilters">
                    <Filter class="h-3.5 w-3.5" />
                    Filters
                </Button>
                <Button size="sm" variant="default" class="gap-1.5" @click="markAllAsRead" :disabled="unreadCount === 0">
                    <CheckCheck class="h-3.5 w-3.5" />
                    Mark All Read
                </Button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Total</p>
                            <p class="text-2xl font-bold">{{ notifications.total || 0 }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                            <Bell class="h-5 w-5 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-2 border-red-200 dark:border-red-900">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Unread</p>
                            <p class="text-2xl font-bold text-red-600">{{ unreadCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                            <Mail class="h-5 w-5 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-2 border-green-200 dark:border-green-900">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Read</p>
                            <p class="text-2xl font-bold text-green-600">{{ readCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                            <CheckCircle class="h-5 w-5 text-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Search and Quick Filters -->
        <Card>
            <CardContent class="p-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search notifications..."
                            class="pl-9"
                        />
                    </div>
                    <div class="flex gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" size="sm" class="w-full sm:w-[140px] justify-between">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4" />
                                        <span>{{ dateFilter === 'today' ? 'Today' : dateFilter === 'week' ? 'This Week' : dateFilter === 'month' ? 'This Month' : 'All Time' }}</span>
                                    </div>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="dateFilter">
                                    <DropdownMenuRadioItem value="">All Time</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="today">Today</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="week">This Week</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="month">This Month</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" size="sm" class="w-full sm:w-[140px] justify-between">
                                    <div class="flex items-center gap-2">
                                        <ArrowUpDown class="h-4 w-4" />
                                        <span>{{ sortField === 'created_at' ? 'Date' : sortField === 'priority' ? 'Priority' : sortField === 'type' ? 'Type' : sortField === 'title' ? 'Title' : 'Sort by' }}</span>
                                    </div>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="sortField">
                                    <DropdownMenuRadioItem value="created_at">Date</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="priority">Priority</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="type">Type</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="title">Title</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button
                            variant="outline"
                            size="sm"
                            class="px-3"
                            @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                        >
                            <component :is="sortDirection === 'asc' ? ArrowUp : ArrowDown" class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Filters Panel -->
        <Card v-if="showFilters">
            <CardHeader class="border-b p-3">
                <div class="flex items-center justify-between">
                    <CardTitle class="text-sm flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        Filter Notifications
                    </CardTitle>
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="showFilters = false">
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs font-medium mb-1.5 block">Type</label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full h-9 justify-between">
                                    <span>{{ typeFilter === 'info' ? 'Info' : typeFilter === 'success' ? 'Success' : typeFilter === 'warning' ? 'Warning' : typeFilter === 'error' ? 'Error' : 'All Types' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="typeFilter">
                                    <DropdownMenuRadioItem value="">All Types</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="info">Info</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="success">Success</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="warning">Warning</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="error">Error</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div>
                        <label class="text-xs font-medium mb-1.5 block">Category</label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full h-9 justify-between">
                                    <span>{{ categoryFilter ? categoryFilter.replace('_', ' ').toUpperCase() : 'All Categories' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)] max-h-[300px] overflow-y-auto">
                                <DropdownMenuRadioGroup v-model="categoryFilter">
                                    <DropdownMenuRadioItem value="">All Categories</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem v-for="cat in categories" :key="cat" :value="cat">
                                        {{ cat.replace('_', ' ').toUpperCase() }}
                                    </DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div>
                        <label class="text-xs font-medium mb-1.5 block">Priority</label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full h-9 justify-between">
                                    <span>{{ priorityFilter === 'urgent' ? 'Urgent' : priorityFilter === 'high' ? 'High' : priorityFilter === 'normal' ? 'Normal' : priorityFilter === 'low' ? 'Low' : 'All Priorities' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="priorityFilter">
                                    <DropdownMenuRadioItem value="">All Priorities</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="urgent">Urgent</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="high">High</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="normal">Normal</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="low">Low</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div>
                        <label class="text-xs font-medium mb-1.5 block">Status</label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="w-full h-9 justify-between">
                                    <span>{{ statusFilter === '0' ? 'Unread' : statusFilter === '1' ? 'Read' : 'All Status' }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-[var(--radix-dropdown-menu-trigger-width)]">
                                <DropdownMenuRadioGroup v-model="statusFilter">
                                    <DropdownMenuRadioItem value="">All Status</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="0">Unread</DropdownMenuRadioItem>
                                    <DropdownMenuRadioItem value="1">Read</DropdownMenuRadioItem>
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <Button v-if="hasFilters" variant="outline" size="sm" @click="clearFilters">
                        <X class="h-3.5 w-3.5 mr-1.5" />
                        Clear Filters
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Notifications -->
        <Card>
            <CardContent class="p-0">
                <Tabs v-model="activeTab" class="w-full">
                    <div class="border-b px-4 pt-3">
                        <div class="flex flex-col gap-3 mb-3 sm:flex-row sm:items-center sm:justify-between">
                            <TabsList class="grid w-full sm:w-fit grid-cols-3">
                                <TabsTrigger value="all" class="gap-2">
                                    <Bell class="h-4 w-4" />
                                    All
                                    <Badge variant="secondary" class="ml-1">{{ notifications.total || 0 }}</Badge>
                                </TabsTrigger>
                                <TabsTrigger value="unread" class="gap-2">
                                    <Mail class="h-4 w-4" />
                                    Unread
                                    <Badge variant="destructive" class="ml-1">{{ unreadCount }}</Badge>
                                </TabsTrigger>
                                <TabsTrigger value="read" class="gap-2">
                                    <CheckCircle class="h-4 w-4" />
                                    Read
                                    <Badge variant="secondary" class="ml-1">{{ readCount }}</Badge>
                                </TabsTrigger>
                            </TabsList>
                            
                            <!-- Bulk Actions -->
                            <div v-if="hasSelected" class="flex items-center gap-2">
                                <span class="text-xs text-muted-foreground">{{ selectedNotifications.length }} selected</span>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" size="sm" class="gap-1.5">
                                            Bulk Actions
                                            <MoreHorizontal class="h-3.5 w-3.5" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="bulkMarkAsRead">
                                            <CheckCircle class="mr-2 h-4 w-4" />
                                            Mark as Read
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="bulkMarkAsUnread">
                                            <RotateCcw class="mr-2 h-4 w-4" />
                                            Mark as Unread
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="bulkDelete" class="text-red-600 focus:text-red-600">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete Selected
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </div>

                    <TabsContent value="all" class="m-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">
                                            <Checkbox
                                                :checked="selectAll"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </TableHead>
                                        <TableHead class="w-12">Status</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('type')">
                                                Type
                                                <component :is="getSortIcon('type')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[200px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('title')">
                                                Title
                                                <component :is="getSortIcon('title')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[250px] hidden md:table-cell">Message</TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('priority')">
                                                Priority
                                                <component :is="getSortIcon('priority')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">Category</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('created_at')">
                                                Date
                                                <component :is="getSortIcon('created_at')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="w-12 text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="getFilteredNotifications.length">
                                        <template v-for="(group, dateKey) in groupedNotifications" :key="dateKey">
                                            <TableRow class="bg-muted/30 hover:bg-muted/30">
                                                <TableCell colspan="9" class="font-semibold text-xs py-2">
                                                    {{ dateKey }}
                                                </TableCell>
                                            </TableRow>
                                            <TableRow
                                                v-for="notification in group"
                                                :key="notification.id"
                                                :class="[
                                                    'cursor-pointer transition-colors',
                                                    !notification.is_read && 'bg-blue-50/50 dark:bg-blue-950/10 hover:bg-blue-100/50 dark:hover:bg-blue-950/20'
                                                ]"
                                            >
                                                <TableCell>
                                                    <Checkbox
                                                        :checked="selectedNotifications.includes(notification.id)"
                                                        @update:checked="(checked) => toggleNotification(notification.id, checked)"
                                                        @click.stop
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <div class="flex items-center justify-center">
                                                        <span v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" />
                                                        <CheckCircle v-else class="h-4 w-4 text-green-600" />
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="flex items-center gap-2">
                                                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0', getTypeColor(notification.type)]">
                                                            <component :is="getTypeIcon(notification.type)" class="h-4 w-4" />
                                                        </div>
                                                        <span class="text-xs capitalize hidden sm:inline">{{ notification.type || '-' }}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="font-medium text-xs">{{ notification.title }}</div>
                                                </TableCell>
                                                <TableCell class="hidden md:table-cell">
                                                    <div class="text-xs text-muted-foreground line-clamp-2 max-w-[250px]">
                                                        {{ notification.message }}
                                                    </div>
                                                    <div v-if="notification.action_url && notification.action_text" class="mt-1">
                                                        <a
                                                            :href="notification.action_url"
                                                            class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                                                            @click.stop="(e) => handleActionClick(notification, e)"
                                                        >
                                                            <ExternalLink class="h-3 w-3" />
                                                            {{ notification.action_text }}
                                                        </a>
                                                    </div>
                                                </TableCell>
                                                <TableCell class="hidden lg:table-cell">
                                                    <Badge
                                                        v-if="notification.priority"
                                                        :class="getPriorityColor(notification.priority)"
                                                        class="text-[9px] font-bold"
                                                    >
                                                        {{ notification.priority.toUpperCase() }}
                                                    </Badge>
                                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                                </TableCell>
                                                <TableCell class="hidden lg:table-cell">
                                                    <Badge v-if="notification.category" variant="outline" class="text-[9px]">
                                                        {{ notification.category.replace('_', ' ').toUpperCase() }}
                                                    </Badge>
                                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                                </TableCell>
                                                <TableCell>
                                                    <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                                        <Clock class="h-3 w-3 shrink-0" />
                                                        <span class="whitespace-nowrap">{{ formatDate(notification.created_at) }}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell class="text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger as-child>
                                                            <Button variant="ghost" size="icon" class="h-8 w-8" @click.stop>
                                                                <MoreHorizontal class="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem
                                                                v-if="!notification.is_read"
                                                                @click="markAsRead(notification)"
                                                            >
                                                                <CheckCircle class="mr-2 h-4 w-4" />
                                                                Mark as Read
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                v-else
                                                                @click="markAsUnread(notification)"
                                                            >
                                                                <RotateCcw class="mr-2 h-4 w-4" />
                                                                Mark as Unread
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                v-if="notification.action_url"
                                                                @click="(e) => handleActionClick(notification, e)"
                                                            >
                                                                <span class="flex items-center">
                                                                    <ExternalLink class="mr-2 h-4 w-4" />
                                                                    View Details
                                                                </span>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem
                                                                @click="deleteNotification(notification)"
                                                                class="text-red-600 focus:text-red-600"
                                                            >
                                                                <Trash2 class="mr-2 h-4 w-4" />
                                                                Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        </template>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell colspan="9" class="h-24 text-center text-muted-foreground">
                                            <div class="flex flex-col items-center justify-center">
                                                <Bell class="h-12 w-12 mb-4 opacity-50" />
                                                <p class="text-sm font-semibold mb-1">No Notifications</p>
                                                <p class="text-xs">You don't have any notifications in this category.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </TabsContent>

                    <TabsContent value="unread" class="m-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">
                                            <Checkbox
                                                :checked="selectAll"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </TableHead>
                                        <TableHead class="w-12">Status</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('type')">
                                                Type
                                                <component :is="getSortIcon('type')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[200px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('title')">
                                                Title
                                                <component :is="getSortIcon('title')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[250px] hidden md:table-cell">Message</TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('priority')">
                                                Priority
                                                <component :is="getSortIcon('priority')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">Category</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('created_at')">
                                                Date
                                                <component :is="getSortIcon('created_at')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="w-12 text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="getFilteredNotifications.length">
                                        <TableRow
                                            v-for="notification in getFilteredNotifications"
                                            :key="notification.id"
                                            class="cursor-pointer transition-colors bg-blue-50/50 dark:bg-blue-950/10 hover:bg-blue-100/50 dark:hover:bg-blue-950/20"
                                        >
                                            <TableCell>
                                                <Checkbox
                                                    :checked="selectedNotifications.includes(notification.id)"
                                                    @update:checked="() => toggleNotification(notification.id)"
                                                    @click.stop
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center justify-center">
                                                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" />
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center gap-2">
                                                    <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0', getTypeColor(notification.type)]">
                                                        <component :is="getTypeIcon(notification.type)" class="h-4 w-4" />
                                                    </div>
                                                    <span class="text-xs capitalize hidden sm:inline">{{ notification.type || '-' }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium text-xs">{{ notification.title }}</div>
                                            </TableCell>
                                            <TableCell class="hidden md:table-cell">
                                                <div class="text-xs text-muted-foreground line-clamp-2 max-w-[250px]">
                                                    {{ notification.message }}
                                                </div>
                                                <div v-if="notification.action_url && notification.action_text" class="mt-1">
                                                    <a
                                                        :href="notification.action_url"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                                                        @click.stop="(e) => handleActionClick(notification, e)"
                                                    >
                                                        <ExternalLink class="h-3 w-3" />
                                                        {{ notification.action_text }}
                                                    </a>
                                                </div>
                                            </TableCell>
                                            <TableCell class="hidden lg:table-cell">
                                                <Badge
                                                    v-if="notification.priority"
                                                    :class="getPriorityColor(notification.priority)"
                                                    class="text-[9px] font-bold"
                                                >
                                                    {{ notification.priority.toUpperCase() }}
                                                </Badge>
                                                <span v-else class="text-xs text-muted-foreground">-</span>
                                            </TableCell>
                                            <TableCell class="hidden lg:table-cell">
                                                <Badge v-if="notification.category" variant="outline" class="text-[9px]">
                                                    {{ notification.category.replace('_', ' ').toUpperCase() }}
                                                </Badge>
                                                <span v-else class="text-xs text-muted-foreground">-</span>
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                                    <Clock class="h-3 w-3 shrink-0" />
                                                    <span class="whitespace-nowrap">{{ formatDate(notification.created_at) }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click.stop>
                                                            <MoreHorizontal class="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem @click="markAsRead(notification)">
                                                            <CheckCircle class="mr-2 h-4 w-4" />
                                                            Mark as Read
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            v-if="notification.action_url"
                                                            @click="(e) => handleActionClick(notification, e)"
                                                        >
                                                            <span class="flex items-center">
                                                                <ExternalLink class="mr-2 h-4 w-4" />
                                                                View Details
                                                            </span>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            @click="deleteNotification(notification)"
                                                            class="text-red-600 focus:text-red-600"
                                                        >
                                                            <Trash2 class="mr-2 h-4 w-4" />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell colspan="9" class="h-24 text-center text-muted-foreground">
                                            <div class="flex flex-col items-center justify-center">
                                                <CheckCircle class="h-12 w-12 mb-4 text-green-600" />
                                                <p class="text-sm font-semibold mb-1">All Caught Up!</p>
                                                <p class="text-xs">You have no unread notifications.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </TabsContent>

                    <TabsContent value="read" class="m-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">
                                            <Checkbox
                                                :checked="selectAll"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </TableHead>
                                        <TableHead class="w-12">Status</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('type')">
                                                Type
                                                <component :is="getSortIcon('type')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[200px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('title')">
                                                Title
                                                <component :is="getSortIcon('title')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[250px] hidden md:table-cell">Message</TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('priority')">
                                                Priority
                                                <component :is="getSortIcon('priority')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="min-w-[100px] hidden lg:table-cell">Category</TableHead>
                                        <TableHead class="min-w-[120px]">
                                            <Button variant="ghost" size="sm" class="h-8" @click="handleSort('created_at')">
                                                Date
                                                <component :is="getSortIcon('created_at')" class="ml-1 h-3.5 w-3.5" />
                                            </Button>
                                        </TableHead>
                                        <TableHead class="w-12 text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="getFilteredNotifications.length">
                                        <TableRow
                                            v-for="notification in getFilteredNotifications"
                                            :key="notification.id"
                                            class="cursor-pointer transition-colors opacity-75 hover:opacity-100"
                                        >
                                            <TableCell>
                                                <Checkbox
                                                    :checked="selectedNotifications.includes(notification.id)"
                                                    @update:checked="() => toggleNotification(notification.id)"
                                                    @click.stop
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center justify-center">
                                                    <CheckCircle class="h-4 w-4 text-green-600" />
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center gap-2">
                                                    <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0 opacity-60', getTypeColor(notification.type)]">
                                                        <component :is="getTypeIcon(notification.type)" class="h-4 w-4" />
                                                    </div>
                                                    <span class="text-xs capitalize hidden sm:inline">{{ notification.type || '-' }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium text-xs">{{ notification.title }}</div>
                                            </TableCell>
                                            <TableCell class="hidden md:table-cell">
                                                <div class="text-xs text-muted-foreground line-clamp-2 max-w-[250px]">
                                                    {{ notification.message }}
                                                </div>
                                                <div v-if="notification.action_url && notification.action_text" class="mt-1">
                                                    <a
                                                        :href="notification.action_url"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                                                        @click.stop="(e) => handleActionClick(notification, e)"
                                                    >
                                                        <ExternalLink class="h-3 w-3" />
                                                        {{ notification.action_text }}
                                                    </a>
                                                </div>
                                            </TableCell>
                                            <TableCell class="hidden lg:table-cell">
                                                <Badge
                                                    v-if="notification.priority"
                                                    :class="getPriorityColor(notification.priority)"
                                                    class="text-[9px] font-bold opacity-60"
                                                >
                                                    {{ notification.priority.toUpperCase() }}
                                                </Badge>
                                                <span v-else class="text-xs text-muted-foreground">-</span>
                                            </TableCell>
                                            <TableCell class="hidden lg:table-cell">
                                                <Badge v-if="notification.category" variant="outline" class="text-[9px] opacity-60">
                                                    {{ notification.category.replace('_', ' ').toUpperCase() }}
                                                </Badge>
                                                <span v-else class="text-xs text-muted-foreground">-</span>
                                            </TableCell>
                                            <TableCell>
                                                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                                    <Clock class="h-3 w-3 shrink-0" />
                                                    <span class="whitespace-nowrap">{{ formatDate(notification.created_at) }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click.stop>
                                                            <MoreHorizontal class="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem @click="markAsUnread(notification)">
                                                            <RotateCcw class="mr-2 h-4 w-4" />
                                                            Mark as Unread
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            v-if="notification.action_url"
                                                            @click="(e) => handleActionClick(notification, e)"
                                                        >
                                                            <span class="flex items-center">
                                                                <ExternalLink class="mr-2 h-4 w-4" />
                                                                View Details
                                                            </span>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            @click="deleteNotification(notification)"
                                                            class="text-red-600 focus:text-red-600"
                                                        >
                                                            <Trash2 class="mr-2 h-4 w-4" />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell colspan="9" class="h-24 text-center text-muted-foreground">
                                            <div class="flex flex-col items-center justify-center">
                                                <Mail class="h-12 w-12 mb-4 opacity-50" />
                                                <p class="text-sm font-semibold mb-1">No Read Notifications</p>
                                                <p class="text-xs">You haven't read any notifications yet.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </TabsContent>
                </Tabs>

                <!-- Infinite scroll sentinel + hint -->
                <div
                    ref="loadMoreSentinelRef"
                    class="flex min-h-[2rem] items-center justify-center gap-2 py-3 border-t"
                >
                    <span v-if="loadingMoreNotifications" class="text-xs text-muted-foreground">Loading more...</span>
                    <span
                        v-else-if="currentPageNum < lastPageNum && extendedNotifications.length > 0"
                        class="text-xs text-muted-foreground"
                    >
                        Scroll for more
                    </span>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.links?.length > 3" class="flex items-center justify-center gap-1 p-4 border-t">
                    <template v-for="(link, index) in notifications.links" :key="index">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 text-xs min-w-[2rem]"
                            as-child
                        >
                            <Link :href="link.url" v-html="link.label" />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs min-w-[2rem]"
                            disabled
                            v-html="link.label"
                        />
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
