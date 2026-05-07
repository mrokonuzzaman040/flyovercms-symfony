-- Active: 1768665311302@@127.0.0.1@3306
<script setup>
import { ref, computed, watch, onMounted, onUnmounted, markRaw, shallowRef } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Badge } from '@/Components/ui/badge';
import { ScrollArea } from '@/Components/ui/scroll-area';
import { Sheet, SheetContent, SheetTrigger, SheetHeader, SheetTitle, SheetDescription } from '@/Components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Toaster } from '@/Components/ui/sonner';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import {
    Menu,
    Bell,
    User,
    Settings,
    LogOut,
    ChevronDown,
    Home,
    Users,
    Building2,
    Shield,
    FileText,
    DollarSign,
    Package,
    Megaphone,
    BarChart3,
    ListTodo,
    Sun,
    Moon,
    Key,
    Store,
    FolderOpen,
    FileQuestion,
    Receipt,
    Calendar,
    Globe,
    Box,
    Briefcase,
    UserPlus,
    CirclePlus,
    Phone,
    PhoneOff,
    UserX,
    PhoneMissed,
    CheckCircle,
    XCircle,
    CalendarCheck,
    Send,
    Search,
    CreditCard,
    Banknote,
    Cog,
    PauseCircle,
    CheckCheck,
    Plane,
    Ban,
    X,
    Clock,
    Sliders,
    Palette,
    Mail,
    ToggleLeft,
    MessageSquare,
    CircleCheck,
    UserCog,
    Tag,
    GitBranch,
    Contact,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import CommandPalette from '@/Components/CommandPalette.vue';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user);
const notifications = computed(() => {
    const notifs = page.props.notifications || [];
    let notificationArray = [];

    // Handle both array and paginated object (from notifications page)
    if (notifs && typeof notifs === 'object' && 'data' in notifs && Array.isArray(notifs.data)) {
        // It's a paginated response, extract the data array
        notificationArray = notifs.data;
    } else if (Array.isArray(notifs)) {
        // It's already an array from the middleware
        notificationArray = notifs;
    }

    // Filter out read notifications - only show unread ones
    return notificationArray.filter(notification => !notification.is_read);
});

// Dropdown list: starts from shared notifications, then "load more" appends from API
// shallowRef — list items are replaced wholesale, never deep-mutated
const displayedDropdownNotifications = shallowRef([]);
const loadingMoreNotifications = ref(false);
const dropdownNextPage = ref(2);
const hasMoreDropdownNotifications = ref(true);

watch(notifications, (val) => {
    displayedDropdownNotifications.value = [...val];
    dropdownNextPage.value = 2;
    hasMoreDropdownNotifications.value = val.length >= 10;
}, { immediate: true });

const fetchMoreNotifications = async () => {
    if (loadingMoreNotifications.value || !hasMoreDropdownNotifications.value) return;

    loadingMoreNotifications.value = true;
    try {
        const url = new URL(route('notifications.api'));
        url.searchParams.set('is_read', 'false');
        url.searchParams.set('per_page', '10');
        url.searchParams.set('page', String(dropdownNextPage.value));

        const response = await fetch(url.toString(), {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            hasMoreDropdownNotifications.value = false;
            return;
        }

        const data = await response.json();
        const list = data.notifications || [];
        if (list.length > 0) {
            displayedDropdownNotifications.value = [...displayedDropdownNotifications.value, ...list];
        }
        const pagination = data.pagination || {};
        hasMoreDropdownNotifications.value = (pagination.current_page || 0) < (pagination.last_page || 0);
        dropdownNextPage.value = (pagination.current_page || 1) + 1;
    } catch {
        hasMoreDropdownNotifications.value = false;
    } finally {
        loadingMoreNotifications.value = false;
    }
};

const notificationScrollRef = ref(null);
const onNotificationDropdownScroll = (e) => {
    const el = e.target;
    if (!el || loadingMoreNotifications.value || !hasMoreDropdownNotifications.value) return;
    const threshold = 60;
    if (el.scrollHeight - el.scrollTop - el.clientHeight < threshold) {
        fetchMoreNotifications();
    }
};

function playNotificationSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const playTone = (freq, startTime, duration) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = freq;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.2, startTime);
            gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            osc.start(startTime);
            osc.stop(startTime + duration);
        };
        playTone(523.25, 0, 0.1);
        playTone(659.25, 0.12, 0.15);
    } catch {
        // Ignore if audio not supported
    }
}
const sidebarMenu = computed(() => {
    const menu = page.props.sidebarMenu;
    if (!Array.isArray(menu)) return [];
    return menu.filter(item => item != null);
});

// Filter menu items based on search query
const filteredSidebarMenu = computed(() => {
    if (!sidebarSearchQuery.value.trim()) return sidebarMenu.value;

    const query = sidebarSearchQuery.value.toLowerCase().trim();

    const filterItems = (items) => {
        return items.filter(item => {
            if (item.type === 'section') {
                const filteredSectionItems = filterItems(item.items || []);
                return filteredSectionItems.length > 0 || item.title.toLowerCase().includes(query);
            } else if (item.type === 'dropdown') {
                const filteredDropdownItems = filterItems(item.items || []);
                return filteredDropdownItems.length > 0 || item.title.toLowerCase().includes(query);
            } else {
                return item.title.toLowerCase().includes(query);
            }
        }).map(item => {
            if (item.type === 'section' || item.type === 'dropdown') {
                return {
                    ...item,
                    items: filterItems(item.items || []),
                };
            }
            return item;
        });
    };

    return filterItems(sidebarMenu.value);
});
const appName = computed(() => page.props.appName || 'FlyoverCMS');

// Reactive current URL for active route detection
const currentUrl = computed(() => page.url || window.location.href);
const currentPath = computed(() => {
    try {
        const url = page.url || window.location.href;
        return new URL(url, window.location.origin).pathname;
    } catch {
        return window.location.pathname;
    }
});

// Reactive current route name
const currentRouteName = computed(() => {
    // Access reactive page.url to force re-evaluation on route changes
    const url = page.url;

    try {
        // Try to access route helper
        let routeHelper = null;
        if (typeof route !== 'undefined' && typeof route === 'function') {
            routeHelper = route;
        } else if (typeof window !== 'undefined' && window.route && typeof window.route === 'function') {
            routeHelper = window.route;
        }

        if (routeHelper) {
            try {
                const routeResult = routeHelper();
                if (routeResult && typeof routeResult.current === 'function') {
                    const routeName = routeResult.current();
                    if (routeName) return routeName;
                }
            } catch (e) {
                // route().current() might fail, continue to fallback
            }
        }
    } catch (e) {
        // Fallback: try to determine route from path
    }

    try {
        const path = currentPath.value || window.location.pathname;
        if (path) {
            if (path.includes('/leads/status/')) {
                return 'leads.status';
            } else if (path.includes('/leads/no-branch')) {
                return 'leads.no-branch';
            } else if (path.includes('/leads/create')) {
                return 'leads.create';
            } else if (path.includes('/departments/visa/leads')) {
                return 'departments.visa.leads';
            } else if (path.includes('/departments/visa/documents')) {
                return 'departments.visa.documents';
            } else if (path.includes('/departments/visa/analytics')) {
                return 'departments.visa.analytics';
            } else if (path.includes('/departments/visa/exports')) {
                return 'departments.visa.exports';
            } else if (path.includes('/departments/visa')) {
                return 'departments.visa.index';
            } else if (path.includes('/leads')) {
                return 'leads.index';
            }
        }
    } catch (e) {
        // If path access fails, return null
    }

    return null;
});

const isMobileMenuOpen = ref(false);
const openMenus = ref({});
const isDarkMode = ref(false);
const isMobile = ref(false);
const isSidebarCollapsed = ref(false);
const sidebarSearchQuery = ref('');

// Enhanced icon mapping from FontAwesome to Lucide
const iconMap = markRaw({
    'fas fa-gauge-high': Home,
    'fas fa-home': Home,
    'fas fa-users-gear': UserCog,
    'fas fa-users': Users,
    'fas fa-users-line': Users,
    'fas fa-user-group': Users,
    'fas fa-code-branch': GitBranch,
    'fas fa-building': Building2,
    'fas fa-key': Key,
    'fas fa-shield-halved': Shield,
    'fas fa-user-shield': Shield,
    'fas fa-user-tag': Tag,
    'fas fa-store': Store,
    'fas fa-address-book': Contact,
    'fas fa-list-check': ListTodo,
    'fas fa-folder-open': FolderOpen,
    'fas fa-file-circle-question': FileQuestion,
    'fas fa-file-alt': FileText,
    'fas fa-file-invoice': Receipt,
    'fas fa-money-bill-wave': Banknote,
    'fas fa-calendar-alt': Calendar,
    'fas fa-globe': Globe,
    'fas fa-box-open': Box,
    'fas fa-briefcase': Briefcase,
    'fas fa-bullhorn': Megaphone,
    'fas fa-megaphone': Megaphone,
    'fas fa-chart-bar': BarChart3,
    'fas fa-chart-pie': BarChart3,
    'fas fa-gear': Settings,
    'fas fa-sliders': Sliders,
    'fas fa-palette': Palette,
    'fas fa-envelope': Mail,
    'fas fa-toggle-on': ToggleLeft,
    'fas fa-comment-sms': MessageSquare,
    'fab fa-whatsapp': MessageSquare,
    'fas fa-message': MessageSquare,
    'fas fa-circle-check': CircleCheck,
    'fas fa-user-plus': UserPlus,
    'fas fa-circle-plus': CirclePlus,
    'fas fa-phone': Phone,
    'fas fa-phone-slash': PhoneOff,
    'fas fa-user-slash': UserX,
    'fas fa-phone-missed': PhoneMissed,
    'fas fa-check-circle': CheckCircle,
    'fas fa-times-circle': XCircle,
    'fas fa-calendar-check': CalendarCheck,
    'fas fa-paper-plane': Send,
    'fas fa-search': Search,
    'fas fa-credit-card': CreditCard,
    'fas fa-cog': Cog,
    'fas fa-pause-circle': PauseCircle,
    'fas fa-check-double': CheckCheck,
    'fas fa-plane': Plane,
    'fas fa-ban': Ban,
    'fas fa-times': X,
    'fas fa-user-clock': Clock,
    'fas fa-passport': FileText,
    'fas fa-wallet': CreditCard,
    'fas fa-spinner': Clock,
    'fas fa-clock': Clock,
    'fas fa-chart-line': BarChart3,
    'fas fa-download': FileText,
});

const getIcon = (iconClass) => {
    if (!iconClass) return FileText;
    const icon = iconMap[iconClass];
    // Ensure we always return a valid component
    if (!icon || typeof icon !== 'function') {
        return FileText;
    }
    return icon;
};

const getUserInitials = () => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Cache isRouteActive results per render cycle. Cache invalidates when the
// route changes; before that, the same (item, activeRoute) is queried 36x in
// the sidebar template — each call parses URLs/regexes. Memoize once.
let routeActiveCache = new Map();
let routeActiveCacheKey = '';

const isRouteActive = (activeRoute, item = null) => {
    try {
        const path = currentPath.value || window.location.pathname;
        const currentRoute = currentRouteName.value;
        const url = currentUrl.value || window.location.href;

        const cacheKey = `${path}|${currentRoute}|${url}`;
        if (cacheKey !== routeActiveCacheKey) {
            routeActiveCache = new Map();
            routeActiveCacheKey = cacheKey;
        }
        const itemKey = `${item?.route ?? ''}|${item?.url ?? ''}|${JSON.stringify(item?.route_params ?? '')}|${Array.isArray(activeRoute) ? activeRoute.join(',') : (activeRoute ?? '')}`;
        if (routeActiveCache.has(itemKey)) return routeActiveCache.get(itemKey);

        const compute = () => {
            if (!currentRoute || !path) return false;

        // Special handling for leads.status routes - check status parameter
        if (item?.route === 'leads.status' && item?.route_params?.status) {
            if (currentRoute === 'leads.status') {
                // Extract status from URL path (e.g., /leads/status/new -> new)
                const statusMatch = path.match(/\/leads\/status\/([^\/]+)/);
                const currentStatus = statusMatch ? statusMatch[1] : null;
                return currentStatus === item.route_params.status;
            }
            return false;
        }

        // Special handling for leads.index with inactive_days parameter
        if (item?.route === 'leads.index' && item?.route_params?.inactive_days) {
            if (currentRoute === 'leads.index') {
                try {
                    const url = new URL(currentUrl.value || window.location.href, window.location.origin);
                    const inactiveDays = url.searchParams.get('inactive_days');
                    return inactiveDays === String(item.route_params.inactive_days);
                } catch {
                    const urlParams = new URLSearchParams(window.location.search);
                    const inactiveDays = urlParams.get('inactive_days');
                    return inactiveDays === String(item.route_params.inactive_days);
                }
            }
            return false;
        }

        // Special handling for "All Leads" - should be active when on leads.index without status filter
        if (item?.route === 'leads.index' && !item?.route_params) {
            if (currentRoute === 'leads.index') {
                try {
                    const url = new URL(currentUrl.value || window.location.href, window.location.origin);
                    // Active if no status or inactive_days parameter
                    return !url.searchParams.has('status') && !url.searchParams.has('inactive_days');
                } catch {
                    const urlParams = new URLSearchParams(window.location.search);
                    return !urlParams.has('status') && !urlParams.has('inactive_days');
                }
            }
            return false;
        }

        // Visa department: exact route match or leads with status
        if (item?.route?.startsWith('departments.visa.')) {
            if (currentRoute === item.route) {
                if (item.route === 'departments.visa.leads') {
                    try {
                        const url = new URL(currentUrl.value || window.location.href, window.location.origin);
                        const currentStatus = url.searchParams.get('status');
                        const itemUrl = item?.url ? new URL(item.url, window.location.origin) : null;
                        const itemStatus = itemUrl?.searchParams.get('status');
                        if (itemStatus) {
                            return currentStatus === itemStatus;
                        }
                        return !currentStatus;
                    } catch (_) {
                        return true;
                    }
                }
                return true;
            }
            return false;
        }

        if (item?.route === 'departments.visa.index' && currentRoute === 'departments.visa.index') {
            return true;
        }

        if (!currentRoute) return false;

            if (Array.isArray(activeRoute)) {
                return activeRoute.some(r => matchesRoute(currentRoute, r));
            }
            return matchesRoute(currentRoute, activeRoute);
        };

        const result = compute();
        routeActiveCache.set(itemKey, result);
        return result;
    } catch (e) {
        console.warn('Error in isRouteActive:', e);
        return false;
    }
};

const matchesRoute = (currentRoute, pattern) => {
    if (!pattern || !currentRoute) return false;
    try {
        if (pattern === currentRoute) return true;
        if (typeof pattern === 'string' && pattern.endsWith('.*')) {
            const prefix = pattern.slice(0, -2);
            return typeof currentRoute === 'string' && currentRoute.startsWith(prefix);
        }
    } catch (e) {
        return false;
    }
    return false;
};

const getRouteUrl = (item) => {
    // If URL is directly provided, use it (highest priority)
    if (item?.url) {
        return item.url;
    }

    if (!item?.route) return '#';

    try {
        // Try to access route helper - it should be available globally via ZiggyVue
        let routeHelper = null;

        // Try multiple ways to access the route helper
        if (typeof route !== 'undefined' && typeof route === 'function') {
            routeHelper = route;
        } else if (typeof window !== 'undefined' && window.route && typeof window.route === 'function') {
            routeHelper = window.route;
        } else if (typeof globalThis !== 'undefined' && globalThis.route && typeof globalThis.route === 'function') {
            routeHelper = globalThis.route;
        }

        if (routeHelper) {
            try {
                // Use route helper with params
                if (item.route_params) {
                    return routeHelper(item.route, item.route_params);
                }
                if (item.params) {
                    return routeHelper(item.route, item.params);
                }
                return routeHelper(item.route);
            } catch (e) {
                // Route helper failed, fall through to manual construction
                console.warn('Route helper failed for:', item.route, e);
            }
        }
    } catch (e) {
        // Fall through to fallback
    }

    // Fallback: construct URLs manually for known routes
    try {
        const route = item.route;
        const params = item.route_params || item.params || {};

        // Handle specific routes
        if (route === 'leads.status' && params.status) {
            return `/leads/status/${params.status}`;
        }
        if (route === 'leads.index') {
            if (params.inactive_days) {
                return `/leads?inactive_days=${params.inactive_days}`;
            }
            return '/leads';
        }
        if (route === 'dashboard') {
            return '/dashboard';
        }
        if (route === 'leads.create') {
            return '/leads/create';
        }
        if (route === 'document-requests.index') {
            return '/document-requests';
        }
        if (route === 'payments.index') {
            return '/payments';
        }
        if (route === 'payment-instalments.index') {
            return '/payment-instalments';
        }
        if (route === 'users.index') {
            return '/users';
        }
        if (route === 'branches.index') {
            return '/branches';
        }
        if (route === 'permissions.index') {
            return '/permissions';
        }
        if (route === 'vendors.create') {
            return '/vendors/create';
        }
        if (route === 'vendors.index') {
            return '/vendors';
        }
        if (route === 'services.index') {
            return '/services';
        }
        if (route === 'packages.index') {
            return '/packages';
        }
        if (route === 'campaigns.index') {
            return '/campaigns';
        }
        if (route === 'communications.index') {
            return '/communications';
        }
        if (route === 'reports.index') {
            return '/reports';
        }
        if (route === 'settings.index') {
            return '/settings';
        }
        if (route === 'roles.index') {
            return '/roles';
        }
        if (route === 'departments.index') {
            return '/departments';
        }
        if (route === 'departments.transfers.pending' && (params.department || typeof params[0] !== 'undefined')) {
            const dept = params.department || params[0];
            return `/departments/transfers/${dept}/pending`;
        }
        if (route === 'departments.visa.index') {
            return '/departments/visa';
        }
        if (route === 'departments.visa.leads') {
            const q = params.status ? `?status=${params.status}` : '';
            return `/departments/visa/leads${q}`;
        }
        if (route === 'departments.visa.documents') {
            return '/departments/visa/documents';
        }
        if (route === 'departments.visa.analytics') {
            return '/departments/visa/analytics';
        }
        if (route === 'departments.visa.exports') {
            return '/departments/visa/exports';
        }
        if (route === 'invoices.index') {
            return '/invoices';
        }
        if (route === 'department.dashboard' && params.department) {
            return `/departments/${params.department}/dashboard`;
        }
        if (route === 'department.leads' && params.department) {
            return `/departments/${params.department}/leads`;
        }
        if (route === 'department.users' && params.department) {
            return `/departments/${params.department}/users`;
        }
        if (route === 'branch.dashboard' && params.branch) {
            return `/branches/${params.branch}/dashboard`;
        }
        if (route === 'branch.leads' && params.branch) {
            return `/branches/${params.branch}/leads`;
        }

        // Generic fallback: convert route name to URL
        let url = route.replace(/\./g, '/');
        if (Object.keys(params).length > 0) {
            // Handle route parameters
            const paramValues = Object.values(params);
            if (paramValues.length > 0) {
                url += '/' + paramValues.join('/');
            }
        }
        return '/' + url;
    } catch (e) {
        console.warn('Failed to construct URL for route:', item.route, e);
        return '#';
    }
};

const handleLinkClick = (item, event) => {
    const url = getRouteUrl(item);
    // If URL is invalid, try to navigate programmatically
    if (url === '#' || !url || url.startsWith('#')) {
        event?.preventDefault();
        try {
            // Try to use route helper programmatically
            let routeHelper = null;
            if (typeof route !== 'undefined' && typeof route === 'function') {
                routeHelper = route;
            } else if (typeof window !== 'undefined' && window.route && typeof window.route === 'function') {
                routeHelper = window.route;
            }

            if (routeHelper && item.route) {
                try {
                    const routeUrl = item.route_params
                        ? routeHelper(item.route, item.route_params)
                        : routeHelper(item.route);
                    router.visit(routeUrl, { preserveState: true, preserveScroll: true });
                } catch (e) {
                    console.warn('Failed to navigate to route:', item.route, e);
                }
            }
        } catch (e) {
            console.warn('Navigation failed for:', item.route, e);
        }
    }
    // For mobile menu, close it after navigation
    if (isMobile.value) {
        isMobileMenuOpen.value = false;
    }
};

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const logout = () => {
    router.post(route('logout'));
};

// CSRF token from Inertia props (stays in sync) or meta tag fallback
const getCsrfToken = () => page.props.csrf_token ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Mark notification as read (optionally skip reload when navigating away)
const markNotificationAsRead = async (notificationId, options = {}) => {
    const { reload = true } = options;
    if (!notificationId) return;

    try {
        const response = await fetch(route('notifications.mark-read', notificationId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
        });

        if (response.ok && reload) {
            router.reload({ only: ['notifications'] });
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

// Mark all notifications as read and refresh the list
const markAllNotificationsAsRead = async () => {
    if (notifications.value.length === 0) return;

    try {
        const response = await fetch(route('notifications.mark-all-read'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            router.reload({ only: ['notifications'] });
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

// Handle notification click: mark as read then navigate to action_url when present
const handleNotificationClick = async (notification, event) => {
    event?.preventDefault();

    if (!notification.is_read && notification.id) {
        await markNotificationAsRead(notification.id, { reload: false });
    }

    if (notification.action_url) {
        router.visit(notification.action_url);
    } else {
        router.reload({ only: ['notifications'] });
    }
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
    if (!isMobile.value) {
        isMobileMenuOpen.value = false;
    }
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value.toString());
};

let notificationChannelName = null;

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }

    const savedSidebarState = localStorage.getItem('sidebarCollapsed');
    if (savedSidebarState === 'true') {
        isSidebarCollapsed.value = true;
    }

    if (window.Echo && user.value?.id) {
        notificationChannelName = 'user.' + user.value.id;
        window.Echo.private(notificationChannelName).listen('.notification.created', () => {
            playNotificationSound();
            router.reload({ only: ['notifications'] });
        });
    }

    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    if (notificationChannelName && window.Echo) {
        window.Echo.leave(notificationChannelName);
    }
    window.removeEventListener('resize', checkMobile);
});
</script>

<template>
    <TooltipProvider>
        <div class="min-h-screen bg-background">
            <CommandPalette />
            <!-- Mobile Header -->
            <header
                class="lg:hidden fixed top-0 left-0 right-0 z-50 flex h-12 items-center gap-3 border-b bg-background px-3">
                <Sheet v-model:open="isMobileMenuOpen">
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="shrink-0 h-8 w-8">
                            <Menu class="h-4 w-4" />
                            <span class="sr-only">Toggle navigation menu</span>
                        </Button>
                    </SheetTrigger>
                    <SheetContent v-if="isMobileMenuOpen" side="left" class="w-72 p-0">
                        <SheetHeader class="sr-only">
                            <SheetTitle>Navigation Menu</SheetTitle>
                            <SheetDescription>Main navigation menu for {{ appName }}</SheetDescription>
                        </SheetHeader>
                        <div class="flex h-full flex-col overflow-hidden">
                            <div class="flex h-14 items-center justify-between border-b px-4 shrink-0">
                                <Link :href="route('dashboard')"
                                    class="flex items-center gap-2.5 font-semibold text-sm">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground text-xs font-bold shadow-sm">
                                        F
                                    </div>
                                    <span>{{ appName }}</span>
                                </Link>
                            </div>

                            <!-- Mobile Search -->
                            <div class="px-3 py-2.5 border-b shrink-0">
                                <div class="relative">
                                    <Search
                                        class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                                    <input v-model="sidebarSearchQuery" type="text" placeholder="Search menu..."
                                        class="w-full h-9 pl-8 pr-2.5 text-xs rounded-md bg-accent/50 border border-border text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all" />
                                    <button v-if="sidebarSearchQuery" @click="sidebarSearchQuery = ''"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>

                            <ScrollArea class="flex-1 min-h-0 overflow-hidden">
                                <nav class="flex flex-col gap-1 px-2 py-3">
                                    <template v-for="(menuItem, index) in filteredSidebarMenu" :key="index">
                                        <!-- Section -->
                                        <template v-if="menuItem.type === 'section'">
                                            <div class="px-3 pt-4 pb-2 first:pt-2">
                                                <span
                                                    class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                                    {{ menuItem.title }}
                                                </span>
                                            </div>
                                            <template v-for="(item, itemIndex) in menuItem.items" :key="itemIndex">
                                                <!-- Nested dropdown inside section -->
                                                <template v-if="item.type === 'dropdown'">
                                                    <Collapsible v-model:open="openMenus[`mobile-${item.title}`]"
                                                        :default-open="isRouteActive(item.active_route, item)">
                                                        <CollapsibleTrigger as-child>
                                                            <Button variant="ghost" size="sm"
                                                                class="w-full justify-between px-3 h-9 text-xs font-medium rounded-md"
                                                                :class="isRouteActive(item.active_route, item) ? 'bg-accent' : ''">
                                                                <span class="flex items-center gap-2.5">
                                                                    <component :is="getIcon(item.icon)"
                                                                        class="h-4 w-4 shrink-0" />
                                                                    <span class="truncate">{{ item.title }}</span>
                                                                </span>
                                                                <ChevronDown
                                                                    class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
                                                                    :class="openMenus[`mobile-${item.title}`] ? 'rotate-180' : ''" />
                                                            </Button>
                                                        </CollapsibleTrigger>
                                                        <CollapsibleContent>
                                                            <div
                                                                class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-border/50 pl-3">
                                                                <template v-for="(subItem, subIndex) in item.items"
                                                                    :key="subIndex">
                                                                    <Link :href="getRouteUrl(subItem)"
                                                                        class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                                        :class="isRouteActive(subItem.active_route, subItem)
                                                                            ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                                                        @click="(e) => { handleLinkClick(subItem, e); isMobileMenuOpen = false; }">
                                                                        <div
                                                                            class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                            <component :is="getIcon(subItem.icon)"
                                                                                class="h-3.5 w-3.5 shrink-0" />
                                                                            <span class="truncate">{{ subItem.title
                                                                            }}</span>
                                                                        </div>
                                                                        <Badge
                                                                            v-if="subItem.count !== undefined && subItem.count > 0"
                                                                            variant="secondary"
                                                                            class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                            :class="isRouteActive(subItem.active_route, subItem)
                                                                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                                : 'bg-muted text-muted-foreground'">
                                                                            {{ subItem.count }}
                                                                        </Badge>
                                                                    </Link>
                                                                </template>
                                                            </div>
                                                        </CollapsibleContent>
                                                    </Collapsible>
                                                </template>
                                                <!-- Regular item inside section -->
                                                <template v-else-if="item.type === 'item'">
                                                    <Link :href="getRouteUrl(item)"
                                                        class="flex items-center justify-between gap-2 rounded-md px-3 py-2 text-xs font-medium transition-all h-9"
                                                        :class="isRouteActive(item.active_route, item)
                                                            ? 'bg-primary text-primary-foreground shadow-sm'
                                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                                        @click="(e) => { handleLinkClick(item, e); isMobileMenuOpen = false; }">
                                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                            <component :is="getIcon(item.icon)"
                                                                class="h-4 w-4 shrink-0" />
                                                            <span class="truncate">{{ item.title }}</span>
                                                        </div>
                                                        <Badge v-if="item.count !== undefined && item.count > 0"
                                                            variant="secondary"
                                                            class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                            :class="isRouteActive(item.active_route, item)
                                                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                : 'bg-muted text-muted-foreground'">
                                                            {{ item.count }}
                                                        </Badge>
                                                    </Link>
                                                </template>
                                            </template>
                                        </template>

                                        <!-- Single item -->
                                        <template v-else-if="menuItem.type === 'item'">
                                            <Link :href="getRouteUrl(menuItem)"
                                                class="flex items-center justify-between gap-2 rounded-md px-3 py-2 text-xs font-medium transition-all h-9"
                                                :class="isRouteActive(menuItem.active_route, menuItem)
                                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                                @click="(e) => { handleLinkClick(menuItem, e); isMobileMenuOpen = false; }">
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                    <component :is="getIcon(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                                    <span class="truncate">{{ menuItem.title }}</span>
                                                </div>
                                                <Badge v-if="menuItem.count !== undefined && menuItem.count > 0"
                                                    variant="secondary"
                                                    class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                    :class="isRouteActive(menuItem.active_route, menuItem)
                                                        ? 'bg-primary-foreground/20 text-primary-foreground'
                                                        : 'bg-muted text-muted-foreground'">
                                                    {{ menuItem.count > 99 ? '99+' : menuItem.count }}
                                                </Badge>
                                            </Link>
                                        </template>

                                        <!-- Dropdown (top-level) -->
                                        <template v-else-if="menuItem.type === 'dropdown'">
                                            <div class="px-3 pt-4 pb-2">
                                                <span
                                                    class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                                    {{ menuItem.title }}
                                                </span>
                                            </div>
                                            <Collapsible v-model:open="openMenus[`mobile-${menuItem.title}`]"
                                                :default-open="isRouteActive(menuItem.active_route, menuItem)">
                                                <CollapsibleTrigger as-child>
                                                    <Button variant="ghost" size="sm"
                                                        class="w-full justify-between px-3 h-9 text-xs font-medium rounded-md"
                                                        :class="isRouteActive(menuItem.active_route, menuItem) ? 'bg-accent' : ''">
                                                        <span class="flex items-center gap-2.5">
                                                            <component :is="getIcon(menuItem.icon)"
                                                                class="h-4 w-4 shrink-0" />
                                                            <span class="truncate">All {{ menuItem.title }}</span>
                                                        </span>
                                                        <ChevronDown
                                                            class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
                                                            :class="openMenus[`mobile-${menuItem.title}`] ? 'rotate-180' : ''" />
                                                    </Button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent>
                                                    <div
                                                        class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-border/50 pl-3">
                                                        <template v-for="(item, itemIndex) in menuItem.items"
                                                            :key="itemIndex">
                                                            <!-- Nested dropdown inside top-level dropdown (mobile) -->
                                                            <template v-if="item.type === 'dropdown'">
                                                                <Collapsible
                                                                    v-model:open="openMenus[`mobile-${menuItem.title}-${item.title}`]"
                                                                    :default-open="isRouteActive(item.active_route, item)">
                                                                    <CollapsibleTrigger as-child>
                                                                        <Button variant="ghost" size="sm"
                                                                            class="w-full justify-between px-2.5 py-1.5 h-auto text-xs font-medium rounded-md"
                                                                            :class="isRouteActive(item.active_route, item) ? 'bg-accent' : ''">
                                                                            <span class="flex items-center gap-2.5">
                                                                                <component :is="getIcon(item.icon)"
                                                                                    class="h-3.5 w-3.5 shrink-0" />
                                                                                <span class="truncate">{{ item.title
                                                                                }}</span>
                                                                            </span>
                                                                            <ChevronDown
                                                                                class="h-3 w-3 transition-transform duration-200 shrink-0"
                                                                                :class="openMenus[`mobile-${menuItem.title}-${item.title}`] ? 'rotate-180' : ''" />
                                                                        </Button>
                                                                    </CollapsibleTrigger>
                                                                    <CollapsibleContent>
                                                                        <div
                                                                            class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-border/30 pl-3">
                                                                            <template
                                                                                v-for="(subItem, subIndex) in item.items"
                                                                                :key="subIndex">
                                                                                <Link :href="getRouteUrl(subItem)"
                                                                                    class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                                                    :class="isRouteActive(subItem.active_route, subItem)
                                                                                        ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                                                                    @click="isMobileMenuOpen = false">
                                                                                    <div
                                                                                        class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                                        <component
                                                                                            :is="getIcon(subItem.icon)"
                                                                                            class="h-3.5 w-3.5 shrink-0" />
                                                                                        <span class="truncate">{{
                                                                                            subItem.title }}</span>
                                                                                    </div>
                                                                                    <Badge
                                                                                        v-if="subItem.count !== undefined && subItem.count > 0"
                                                                                        variant="secondary"
                                                                                        class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                                        :class="isRouteActive(subItem.active_route, subItem)
                                                                                            ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                                            : 'bg-muted text-muted-foreground'">
                                                                                        {{ subItem.count }}
                                                                                    </Badge>
                                                                                </Link>
                                                                            </template>
                                                                        </div>
                                                                    </CollapsibleContent>
                                                                </Collapsible>
                                                            </template>
                                                            <!-- Regular item inside top-level dropdown (mobile) -->
                                                            <template v-else>
                                                                <Link :href="getRouteUrl(item)"
                                                                    class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                                    :class="isRouteActive(item.active_route, item)
                                                                        ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                                                                    @click="isMobileMenuOpen = false">
                                                                    <div
                                                                        class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                        <component :is="getIcon(item.icon)"
                                                                            class="h-3.5 w-3.5 shrink-0" />
                                                                        <span class="truncate">{{ item.title }}</span>
                                                                    </div>
                                                                    <Badge
                                                                        v-if="item.count !== undefined && item.count > 0"
                                                                        variant="secondary"
                                                                        class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                        :class="isRouteActive(item.active_route, item)
                                                                            ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                            : 'bg-muted text-muted-foreground'">
                                                                        {{ item.count }}
                                                                    </Badge>
                                                                </Link>
                                                            </template>
                                                        </template>
                                                    </div>
                                                </CollapsibleContent>
                                            </Collapsible>
                                        </template>
                                    </template>
                                </nav>
                            </ScrollArea>
                        </div>
                    </SheetContent>
                </Sheet>

                <div class="flex-1">
                    <Link :href="route('dashboard')" class="font-medium text-sm">{{ appName }}</Link>
                </div>

                <div class="flex items-center gap-1">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleDarkMode">
                        <Sun v-if="isDarkMode" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon" class="relative h-8 w-8">
                                <Bell class="h-4 w-4" />
                                <Badge v-if="notifications.length > 0"
                                    class="absolute -right-0.5 -top-0.5 h-4 w-4 rounded-full p-0 text-[10px]"
                                    variant="destructive">
                                    {{ notifications.length }}
                                </Badge>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-72">
                            <DropdownMenuLabel class="text-xs px-2 py-1.5">Notifications</DropdownMenuLabel>
                            <Button
                                v-if="displayedDropdownNotifications.length > 0"
                                variant="ghost"
                                size="sm"
                                class="w-full justify-start rounded-sm px-2 py-1.5 h-auto text-xs font-normal text-primary hover:bg-accent hover:text-primary"
                                @click.stop="markAllNotificationsAsRead"
                            >
                                <CheckCheck class="h-3.5 w-3.5 mr-2 shrink-0" />
                                Mark all as read
                            </Button>
                            <DropdownMenuSeparator />
                            <div v-if="displayedDropdownNotifications.length === 0"
                                class="p-3 text-center text-xs text-muted-foreground">
                                No new notifications
                            </div>
                            <template v-else>
                                <div
                                    ref="notificationScrollRef"
                                    class="max-h-[280px] overflow-y-auto overflow-x-hidden"
                                    @scroll="onNotificationDropdownScroll"
                                >
                                    <DropdownMenuItem
                                        v-for="notification in displayedDropdownNotifications"
                                        :key="notification.id"
                                        class="text-xs cursor-pointer"
                                        @click="(e) => handleNotificationClick(notification, e)"
                                    >
                                        <div class="flex flex-col gap-0.5 w-full">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="font-medium flex-1 min-w-0">{{
                                                    notification.title ||
                                                    notification.data?.title ||
                                                    'Notification'
                                                }}</span>
                                                <Button
                                                    v-if="!notification.is_read"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 shrink-0 p-0 text-primary hover:text-primary"
                                                    title="Mark as read"
                                                    @click.stop="(e) => { e.preventDefault(); markNotificationAsRead(notification.id); }"
                                                >
                                                    <CheckCheck class="h-3.5 w-3.5" />
                                                    <span class="sr-only">Mark as read</span>
                                                </Button>
                                            </div>
                                            <span class="text-[10px] text-muted-foreground">{{
                                                notification.message ||
                                                notification.data?.message ||
                                                ''
                                            }}</span>
                                            <a v-if="notification.action_url && notification.action_text"
                                                :href="notification.action_url"
                                                class="text-[10px] text-primary hover:underline mt-0.5"
                                                @click.stop="(e) => handleNotificationClick(notification, e)">
                                                {{ notification.action_text }}
                                            </a>
                                        </div>
                                    </DropdownMenuItem>
                                    <div v-if="loadingMoreNotifications"
                                        class="py-2 text-center text-xs text-muted-foreground">
                                        Loading...
                                    </div>
                                    <div v-else-if="hasMoreDropdownNotifications && displayedDropdownNotifications.length >= 10"
                                        class="py-1.5 text-center text-[10px] text-muted-foreground">
                                        Scroll for more
                                    </div>
                                </div>
                            </template>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="relative h-7 w-7 rounded-full">
                                <Avatar class="h-7 w-7">
                                    <AvatarImage v-if="user?.avatar" :src="user.avatar" :alt="user?.name" />
                                    <AvatarFallback class="text-[10px]">{{ getUserInitials() }}</AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel class="text-xs">
                                <div class="flex flex-col">
                                    <span>{{ user?.name }}</span>
                                    <span class="text-[10px] font-normal text-muted-foreground">{{ user?.email }}</span>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child class="text-xs">
                                <Link :href="route('profile.show')" class="flex items-center gap-2">
                                    <User class="h-3 w-3" />
                                    Profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child class="text-xs">
                                <Link :href="route('settings.index')" class="flex items-center gap-2">
                                    <Settings class="h-3 w-3" />
                                    Settings
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="logout" class="text-destructive text-xs">
                                <LogOut class="mr-2 h-3 w-3" />
                                Log out
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <div class="flex">
                <!-- Desktop Sidebar -->
                <aside :class="[
                    'hidden lg:flex h-screen flex-col border-r bg-sidebar fixed left-0 top-0 z-50 transition-all duration-300 ease-in-out overflow-hidden',
                    isSidebarCollapsed ? 'w-16' : 'w-64'
                ]">
                    <!-- Sidebar Header -->
                    <div class="flex h-14 items-center justify-between border-b px-3 gap-2 shrink-0">
                        <Link v-if="!isSidebarCollapsed" :href="route('dashboard')"
                            class="flex items-center gap-2.5 font-semibold text-sm text-sidebar-foreground hover:text-primary transition-colors">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground text-xs font-bold shadow-sm">
                                F
                            </div>
                            <span class="truncate">{{ appName }}</span>
                        </Link>
                        <div v-else
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground text-xs font-bold shadow-sm mx-auto">
                            F
                        </div>
                        <Button v-if="!isSidebarCollapsed" variant="ghost" size="icon"
                            class="h-7 w-7 shrink-0 text-sidebar-foreground/60 hover:text-sidebar-foreground hover:bg-sidebar-accent"
                            @click="toggleSidebar">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Search Bar (only when expanded) -->
                    <div v-if="!isSidebarCollapsed" class="px-3 py-2 border-b shrink-0">
                        <div class="relative">
                            <Search
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-sidebar-foreground/40" />
                            <input v-model="sidebarSearchQuery" type="text" placeholder="Search menu..."
                                class="w-full h-8 pl-8 pr-2.5 text-xs rounded-md bg-sidebar-accent/50 border border-sidebar-border text-sidebar-foreground placeholder:text-sidebar-foreground/40 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all" />
                            <button v-if="sidebarSearchQuery" @click="sidebarSearchQuery = ''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-sidebar-foreground/40 hover:text-sidebar-foreground">
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                    <div v-else class="px-2 py-2 border-b shrink-0">
                        <Button variant="ghost" size="icon"
                            class="h-8 w-8 shrink-0 text-sidebar-foreground/60 hover:text-sidebar-foreground hover:bg-sidebar-accent mx-auto"
                            @click="toggleSidebar">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <ScrollArea class="flex-1 min-h-0 overflow-hidden">
                        <nav class="flex flex-col gap-1 px-2 py-3">
                            <template v-for="(menuItem, index) in filteredSidebarMenu" :key="index">
                                <!-- Section -->
                                <template v-if="menuItem.type === 'section'">
                                    <div v-if="!isSidebarCollapsed" class="px-3 pt-4 pb-2 first:pt-2">
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">
                                            {{ menuItem.title }}
                                        </span>
                                    </div>
                                    <div v-else class="h-1"></div>
                                    <template v-for="(item, itemIndex) in menuItem.items" :key="itemIndex">
                                        <!-- Nested dropdown inside section -->
                                        <template v-if="item.type === 'dropdown'">
                                            <Collapsible v-model:open="openMenus[item.title]"
                                                :default-open="isRouteActive(item.active_route, item)">
                                                <template v-if="isSidebarCollapsed">
                                                    <Tooltip :key="`tooltip-${item.title}-collapsed`">
                                                        <TooltipTrigger as-child>
                                                            <CollapsibleTrigger as-child>
                                                                <Button variant="ghost" size="sm"
                                                                    class="w-full justify-center px-2 h-9 text-xs font-normal text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md"
                                                                    :class="isRouteActive(item.active_route, item) ? 'bg-sidebar-accent' : ''">
                                                                    <component :is="getIcon(item.icon)"
                                                                        class="h-4 w-4" />
                                                                </Button>
                                                            </CollapsibleTrigger>
                                                        </TooltipTrigger>
                                                        <TooltipContent side="right" class="text-xs">{{ item.title }}
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </template>
                                                <CollapsibleTrigger v-else as-child>
                                                    <Button variant="ghost" size="sm"
                                                        class="w-full justify-between px-3 h-9 text-xs font-medium text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md transition-all"
                                                        :class="isRouteActive(item.active_route, item) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''">
                                                        <span class="flex items-center gap-2.5">
                                                            <component :is="getIcon(item.icon)"
                                                                class="h-4 w-4 shrink-0" />
                                                            <span class="truncate">{{ item.title }}</span>
                                                        </span>
                                                        <ChevronDown
                                                            class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
                                                            :class="openMenus[item.title] ? 'rotate-180' : ''" />
                                                    </Button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent v-if="!isSidebarCollapsed">
                                                    <div
                                                        class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-sidebar-border/50 pl-3">
                                                        <template v-for="(subItem, subIndex) in item.items"
                                                            :key="subIndex">
                                                            <Link :href="getRouteUrl(subItem)"
                                                                class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                                :class="isRouteActive(subItem.active_route, subItem)
                                                                    ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                    : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                                @click="(e) => handleLinkClick(subItem, e)">
                                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                    <component :is="getIcon(subItem.icon)"
                                                                        class="h-3.5 w-3.5 shrink-0" />
                                                                    <span class="truncate">{{ subItem.title }}</span>
                                                                </div>
                                                                <Badge
                                                                    v-if="subItem.count !== undefined && subItem.count > 0"
                                                                    variant="secondary"
                                                                    class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                    :class="isRouteActive(subItem.active_route, subItem)
                                                                        ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                        : 'bg-sidebar-accent text-sidebar-foreground/80'">
                                                                    {{ subItem.count }}
                                                                </Badge>
                                                            </Link>
                                                        </template>
                                                    </div>
                                                </CollapsibleContent>
                                            </Collapsible>
                                        </template>
                                        <!-- Regular item inside section -->
                                        <template v-else-if="item.type === 'item'">
                                            <template v-if="isSidebarCollapsed">
                                                <Tooltip :key="`tooltip-item-${item.title}-collapsed`">
                                                    <TooltipTrigger as-child>
                                                        <Link :href="getRouteUrl(item)"
                                                            class="flex items-center justify-center gap-2 rounded-md px-2 py-2 text-xs transition-all h-9"
                                                            :class="isRouteActive(item.active_route, item)
                                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                                : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                            @click="(e) => handleLinkClick(item, e)">
                                                            <component :is="getIcon(item.icon)"
                                                                class="h-4 w-4 shrink-0" />
                                                        </Link>
                                                    </TooltipTrigger>
                                                    <TooltipContent side="right" class="text-xs">{{ item.title }}
                                                    </TooltipContent>
                                                </Tooltip>
                                            </template>
                                            <template v-else>
                                                <Link :href="getRouteUrl(item)"
                                                    class="flex items-center justify-between gap-2 rounded-md px-3 py-2 text-xs font-medium transition-all h-9"
                                                    :class="isRouteActive(item.active_route, item)
                                                        ? 'bg-primary text-primary-foreground shadow-sm'
                                                        : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                    @click="(e) => handleLinkClick(item, e)">
                                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                        <component :is="getIcon(item.icon)" class="h-4 w-4 shrink-0" />
                                                        <span class="truncate">{{ item.title }}</span>
                                                    </div>
                                                    <Badge v-if="item.count !== undefined && item.count > 0"
                                                        variant="secondary"
                                                        class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                        :class="isRouteActive(item.active_route, item)
                                                            ? 'bg-primary-foreground/20 text-primary-foreground'
                                                            : 'bg-sidebar-accent text-sidebar-foreground/80'">
                                                        {{ item.count }}
                                                    </Badge>
                                                </Link>
                                            </template>
                                        </template>
                                    </template>
                                </template>

                                <!-- Single item -->
                                <template v-else-if="menuItem.type === 'item'">
                                    <template v-if="isSidebarCollapsed">
                                        <Tooltip :key="`tooltip-menu-${menuItem.title}-collapsed`">
                                            <TooltipTrigger as-child>
                                                <Link :href="getRouteUrl(menuItem)"
                                                    class="relative flex items-center justify-center gap-2 rounded-md px-2 py-2 text-xs transition-all h-9"
                                                    :class="isRouteActive(menuItem.active_route, menuItem)
                                                        ? 'bg-primary text-primary-foreground shadow-sm'
                                                        : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                    @click="(e) => handleLinkClick(menuItem, e)">
                                                    <component :is="getIcon(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                                    <span v-if="menuItem.count !== undefined && menuItem.count > 0"
                                                        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-semibold text-destructive-foreground">
                                                        {{ menuItem.count > 99 ? '99+' : menuItem.count }}
                                                    </span>
                                                </Link>
                                            </TooltipTrigger>
                                            <TooltipContent side="right" class="text-xs">{{ menuItem.title }}
                                                <span v-if="menuItem.count > 0"> ({{ menuItem.count }} unread)</span>
                                            </TooltipContent>
                                        </Tooltip>
                                    </template>
                                    <template v-else>
                                        <Link :href="getRouteUrl(menuItem)"
                                            class="flex items-center justify-between gap-2 rounded-md px-3 py-2 text-xs font-medium transition-all h-9"
                                            :class="isRouteActive(menuItem.active_route, menuItem)
                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                            @click="(e) => handleLinkClick(menuItem, e)">
                                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                <component :is="getIcon(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                                <span class="truncate">{{ menuItem.title }}</span>
                                            </div>
                                            <Badge v-if="menuItem.count !== undefined && menuItem.count > 0"
                                                variant="secondary"
                                                class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                :class="isRouteActive(menuItem.active_route, menuItem)
                                                    ? 'bg-primary-foreground/20 text-primary-foreground'
                                                    : 'bg-sidebar-accent text-sidebar-foreground/80'">
                                                {{ menuItem.count > 99 ? '99+' : menuItem.count }}
                                            </Badge>
                                        </Link>
                                    </template>
                                </template>

                                <!-- Dropdown (top-level) -->
                                <template v-else-if="menuItem.type === 'dropdown'">
                                    <div v-if="!isSidebarCollapsed" class="px-3 pt-4 pb-2">
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">
                                            {{ menuItem.title }}
                                        </span>
                                    </div>
                                    <Collapsible v-model:open="openMenus[menuItem.title]"
                                        :default-open="isRouteActive(menuItem.active_route, menuItem)">
                                        <template v-if="isSidebarCollapsed">
                                            <Tooltip :key="`tooltip-dropdown-${menuItem.title}-collapsed`">
                                                <TooltipTrigger as-child>
                                                    <CollapsibleTrigger as-child>
                                                        <Button variant="ghost" size="sm"
                                                            class="w-full justify-center px-2 h-9 text-xs font-normal text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md"
                                                            :class="isRouteActive(menuItem.active_route, menuItem) ? 'bg-sidebar-accent' : ''">
                                                            <component :is="getIcon(menuItem.icon)" class="h-4 w-4" />
                                                        </Button>
                                                    </CollapsibleTrigger>
                                                </TooltipTrigger>
                                                <TooltipContent side="right" class="text-xs">All {{ menuItem.title }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </template>
                                        <CollapsibleTrigger v-else as-child>
                                            <Button variant="ghost" size="sm"
                                                class="w-full justify-between px-3 h-9 text-xs font-medium text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md transition-all"
                                                :class="isRouteActive(menuItem.active_route, menuItem) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''">
                                                <span class="flex items-center gap-2.5">
                                                    <component :is="getIcon(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                                    <span class="truncate">All {{ menuItem.title }}</span>
                                                </span>
                                                <ChevronDown
                                                    class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
                                                    :class="openMenus[menuItem.title] ? 'rotate-180' : ''" />
                                            </Button>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent v-if="!isSidebarCollapsed">
                                            <div
                                                class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-sidebar-border/50 pl-3">
                                                <template v-for="(item, itemIndex) in menuItem.items" :key="itemIndex">
                                                    <!-- Nested dropdown inside top-level dropdown -->
                                                    <template v-if="item.type === 'dropdown'">
                                                        <Collapsible
                                                            v-model:open="openMenus[`${menuItem.title}-${item.title}`]"
                                                            :default-open="isRouteActive(item.active_route, item)">
                                                            <CollapsibleTrigger as-child>
                                                                <Button variant="ghost" size="sm"
                                                                    class="w-full justify-between px-2.5 py-1.5 h-auto text-xs font-medium text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md transition-all"
                                                                    :class="isRouteActive(item.active_route, item) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''">
                                                                    <span class="flex items-center gap-2.5">
                                                                        <component :is="getIcon(item.icon)"
                                                                            class="h-3.5 w-3.5 shrink-0" />
                                                                        <span class="truncate">{{ item.title }}</span>
                                                                    </span>
                                                                    <ChevronDown
                                                                        class="h-3 w-3 transition-transform duration-200 shrink-0"
                                                                        :class="openMenus[`${menuItem.title}-${item.title}`] ? 'rotate-180' : ''" />
                                                                </Button>
                                                            </CollapsibleTrigger>
                                                            <CollapsibleContent>
                                                                <div
                                                                    class="ml-4 mt-1 flex flex-col gap-0.5 border-l-2 border-sidebar-border/30 pl-3">
                                                                    <template v-for="(subItem, subIndex) in item.items"
                                                                        :key="subIndex">
                                                                        <Link :href="getRouteUrl(subItem)"
                                                                            class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                                            :class="isRouteActive(subItem.active_route, subItem)
                                                                                ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                                : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                                            @click="(e) => handleLinkClick(subItem, e)">
                                                                            <div
                                                                                class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                                <component :is="getIcon(subItem.icon)"
                                                                                    class="h-3.5 w-3.5 shrink-0" />
                                                                                <span class="truncate">{{ subItem.title
                                                                                }}</span>
                                                                            </div>
                                                                            <Badge
                                                                                v-if="subItem.count !== undefined && subItem.count > 0"
                                                                                variant="secondary"
                                                                                class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                                :class="isRouteActive(subItem.active_route, subItem)
                                                                                    ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                                    : 'bg-sidebar-accent text-sidebar-foreground/80'">
                                                                                {{ subItem.count }}
                                                                            </Badge>
                                                                        </Link>
                                                                    </template>
                                                                </div>
                                                            </CollapsibleContent>
                                                        </Collapsible>
                                                    </template>
                                                    <!-- Regular item inside top-level dropdown -->
                                                    <template v-else>
                                                        <Link :href="getRouteUrl(item)"
                                                            class="flex items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition-all"
                                                            :class="isRouteActive(item.active_route, item)
                                                                ? 'bg-primary text-primary-foreground font-medium shadow-sm'
                                                                : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
                                                            @click="(e) => handleLinkClick(item, e)">
                                                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                                <component :is="getIcon(item.icon)"
                                                                    class="h-3.5 w-3.5 shrink-0" />
                                                                <span class="truncate">{{ item.title }}</span>
                                                            </div>
                                                            <Badge v-if="item.count !== undefined && item.count > 0"
                                                                variant="secondary"
                                                                class="h-5 min-w-[20px] px-1.5 text-[10px] font-semibold shrink-0"
                                                                :class="isRouteActive(item.active_route, item)
                                                                    ? 'bg-primary-foreground/20 text-primary-foreground'
                                                                    : 'bg-sidebar-accent text-sidebar-foreground/80'">
                                                                {{ item.count }}
                                                            </Badge>
                                                        </Link>
                                                    </template>
                                                </template>
                                            </div>
                                        </CollapsibleContent>
                                    </Collapsible>
                                </template>
                            </template>
                        </nav>
                    </ScrollArea>

                    <div class="border-t p-2.5 shrink-0">
                        <DropdownMenu>
                            <template v-if="isSidebarCollapsed">
                                <Tooltip :key="`tooltip-user-collapsed`">
                                    <TooltipTrigger as-child>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="sm"
                                                class="w-full justify-center px-2 h-10 rounded-md">
                                                <Avatar class="h-7 w-7">
                                                    <AvatarImage v-if="user?.avatar" :src="user.avatar"
                                                        :alt="user?.name" />
                                                    <AvatarFallback class="text-[10px]">{{ getUserInitials() }}
                                                    </AvatarFallback>
                                                </Avatar>
                                            </Button>
                                        </DropdownMenuTrigger>
                                    </TooltipTrigger>
                                    <TooltipContent side="right" class="text-xs">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ user?.name }}</span>
                                            <span class="text-[10px] text-muted-foreground">{{ user?.email }}</span>
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            </template>
                            <DropdownMenuTrigger v-else as-child>
                                <Button variant="ghost" size="sm"
                                    class="w-full justify-start gap-2.5 px-2.5 h-10 rounded-md hover:bg-sidebar-accent">
                                    <Avatar class="h-7 w-7 shrink-0">
                                        <AvatarImage v-if="user?.avatar" :src="user.avatar" :alt="user?.name" />
                                        <AvatarFallback class="text-[10px]">{{ getUserInitials() }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col items-start text-left min-w-0 flex-1">
                                        <span class="text-xs font-medium text-sidebar-foreground truncate w-full">{{
                                            user?.name
                                            }}</span>
                                        <span class="text-[10px] text-sidebar-foreground/50 truncate w-full">{{
                                            user?.email
                                            }}</span>
                                    </div>
                                    <ChevronDown class="h-3.5 w-3.5 text-sidebar-foreground/50 shrink-0" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48">
                                <DropdownMenuLabel class="text-xs">My Account</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child class="text-xs">
                                    <Link :href="route('profile.show')" class="flex items-center gap-2">
                                        <User class="h-3 w-3" />
                                        Profile
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child class="text-xs">
                                    <Link :href="route('settings.index')" class="flex items-center gap-2">
                                        <Settings class="h-3 w-3" />
                                        Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="logout" class="text-destructive text-xs">
                                    <LogOut class="mr-2 h-3 w-3" />
                                    Log out
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </aside>

                <!-- Main Content -->
                <main
                    :class="['flex-1 transition-all duration-300 ease-in-out', isSidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64']">
                    <!-- Desktop Header -->
                    <header
                        class="hidden lg:flex h-12 items-center gap-3 border-b bg-background px-4 sticky top-0 z-40">
                        <Breadcrumb v-if="breadcrumbs.length > 0" class="flex-1">
                            <BreadcrumbList>
                                <BreadcrumbItem>
                                    <BreadcrumbLink as-child>
                                        <Link :href="route('dashboard')">
                                            <Home class="h-3.5 w-3.5" />
                                        </Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <template v-for="(crumb, index) in breadcrumbs" :key="index">
                                    <BreadcrumbSeparator />
                                    <BreadcrumbItem>
                                        <BreadcrumbPage v-if="index === breadcrumbs.length - 1" class="text-xs">
                                            {{ crumb.title }}
                                        </BreadcrumbPage>
                                        <BreadcrumbLink v-else as-child class="text-xs">
                                            <Link :href="crumb.href">{{ crumb.title }}</Link>
                                        </BreadcrumbLink>
                                    </BreadcrumbItem>
                                </template>
                            </BreadcrumbList>
                        </Breadcrumb>
                        <div v-else class="flex-1"></div>

                        <div class="flex items-center gap-1">
                            <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleDarkMode">
                                <Sun v-if="isDarkMode" class="h-4 w-4" />
                                <Moon v-else class="h-4 w-4" />
                            </Button>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="relative h-8 w-8">
                                        <Bell class="h-4 w-4" />
                                        <Badge v-if="notifications.length > 0"
                                            class="absolute -right-0.5 -top-0.5 h-4 w-4 rounded-full p-0 text-[10px] flex items-center justify-center"
                                            variant="destructive">
                                            {{ notifications.length > 9 ? '9+' : notifications.length }}
                                        </Badge>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-72">
                                    <DropdownMenuLabel class="flex items-center justify-between text-xs px-2 py-1.5">
                                        <span>Notifications</span>
                                        <Badge variant="secondary" class="text-[10px]">{{ notifications.length }} new</Badge>
                                    </DropdownMenuLabel>
                                    <Button
                                        v-if="displayedDropdownNotifications.length > 0"
                                        variant="ghost"
                                        size="sm"
                                        class="w-full justify-start rounded-sm px-2 py-1.5 h-auto text-xs font-normal text-primary hover:bg-accent hover:text-primary"
                                        @click.stop="markAllNotificationsAsRead"
                                    >
                                        <CheckCheck class="h-3.5 w-3.5 mr-2 shrink-0" />
                                        Mark all as read
                                    </Button>
                                    <DropdownMenuSeparator />
                                    <div v-if="displayedDropdownNotifications.length === 0"
                                        class="p-3 text-center text-xs text-muted-foreground">
                                        No new notifications
                                    </div>
                                    <div
                                        v-else
                                        class="max-h-[250px] overflow-y-auto overflow-x-hidden"
                                        @scroll="onNotificationDropdownScroll"
                                    >
                                        <DropdownMenuItem
                                            v-for="notification in displayedDropdownNotifications"
                                            :key="notification.id"
                                            class="flex flex-col items-start gap-0.5 p-2 text-xs cursor-pointer"
                                            @click="(e) => handleNotificationClick(notification, e)"
                                        >
                                            <div class="flex items-start justify-between gap-2 w-full">
                                                <span class="font-medium flex-1 min-w-0">{{
                                                    notification.title ||
                                                    notification.data?.title ||
                                                    'Notification'
                                                }}</span>
                                                <Button
                                                    v-if="!notification.is_read"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 shrink-0 p-0 text-primary hover:text-primary"
                                                    title="Mark as read"
                                                    @click.stop="(e) => { e.preventDefault(); markNotificationAsRead(notification.id); }"
                                                >
                                                    <CheckCheck class="h-3.5 w-3.5" />
                                                    <span class="sr-only">Mark as read</span>
                                                </Button>
                                            </div>
                                            <span class="text-[10px] text-muted-foreground line-clamp-2">{{
                                                notification.message ||
                                                notification.data?.message ||
                                                ''
                                            }}</span>
                                            <a v-if="notification.action_url && notification.action_text"
                                                :href="notification.action_url"
                                                class="text-[10px] text-primary hover:underline mt-0.5"
                                                @click.stop="(e) => handleNotificationClick(notification, e)">
                                                {{ notification.action_text }}
                                            </a>
                                        </DropdownMenuItem>
                                        <div v-if="loadingMoreNotifications"
                                            class="py-2 text-center text-xs text-muted-foreground">
                                            Loading...
                                        </div>
                                        <div v-else-if="hasMoreDropdownNotifications && displayedDropdownNotifications.length >= 10"
                                            class="py-1.5 text-center text-[10px] text-muted-foreground">
                                            Scroll for more
                                        </div>
                                    </div>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem as-child class="justify-center text-xs">
                                        <Link :href="route('notifications.index')" class="text-primary font-medium">
                                            View all notifications
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" class="relative h-7 w-7 rounded-full">
                                        <Avatar class="h-7 w-7">
                                            <AvatarImage v-if="user?.avatar" :src="user.avatar" :alt="user?.name" />
                                            <AvatarFallback class="text-[10px]">{{ getUserInitials() }}</AvatarFallback>
                                        </Avatar>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48">
                                    <DropdownMenuLabel class="text-xs">
                                        <div class="flex flex-col">
                                            <span>{{ user?.name }}</span>
                                            <span class="text-[10px] font-normal text-muted-foreground">{{ user?.email
                                                }}</span>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem as-child class="text-xs">
                                        <Link :href="route('profile.show')" class="flex items-center gap-2">
                                            <User class="h-3 w-3" />
                                            Profile
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child class="text-xs">
                                        <Link :href="route('settings.index')" class="flex items-center gap-2">
                                            <Settings class="h-3 w-3" />
                                            Settings
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="logout" class="text-destructive text-xs">
                                        <LogOut class="mr-2 h-3 w-3" />
                                        Log out
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div class="p-3 lg:p-4 pt-14 lg:pt-4">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
        <Toaster />
    </TooltipProvider>
</template>
