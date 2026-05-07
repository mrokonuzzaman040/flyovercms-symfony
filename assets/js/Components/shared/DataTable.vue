<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import { Skeleton } from '@/Components/ui/skeleton';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Search,
    X,
    ChevronDown,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: null,
    },
    sortField: {
        type: String,
        default: null,
    },
    sortDirection: {
        type: String,
        default: 'asc',
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    searchQuery: {
        type: String,
        default: '',
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...',
    },
    selectable: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: 'No results found.',
    },
    routeName: {
        type: String,
        default: null,
    },
    preserveState: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['sort', 'search', 'page-change', 'per-page-change', 'selection-change', 'update:searchQuery']);

const selectedRows = ref([]);
const localSearch = ref(props.searchQuery);

// Watch for external search query changes
watch(() => props.searchQuery, (newVal) => {
    localSearch.value = newVal;
});

// Watch for local search changes and emit update
watch(localSearch, (newVal) => {
    emit('update:searchQuery', newVal);
});

const allSelected = computed(() => {
    return props.data.length > 0 && selectedRows.value.length === props.data.length;
});

const someSelected = computed(() => {
    return selectedRows.value.length > 0 && selectedRows.value.length < props.data.length;
});

const toggleAll = (checked) => {
    if (checked) {
        selectedRows.value = props.data.map(row => row.id);
    } else {
        selectedRows.value = [];
    }
    emit('selection-change', selectedRows.value);
};

const toggleRow = (id, checked) => {
    if (checked) {
        selectedRows.value.push(id);
    } else {
        selectedRows.value = selectedRows.value.filter(rowId => rowId !== id);
    }
    emit('selection-change', selectedRows.value);
};

const isRowSelected = (id) => selectedRows.value.includes(id);

const handleSort = (field) => {
    if (!field) return;

    let direction = 'asc';
    if (props.sortField === field) {
        direction = props.sortDirection === 'asc' ? 'desc' : 'asc';
    }
    emit('sort', { field, direction });
};

const getSortIcon = (field) => {
    if (props.sortField !== field) return ArrowUpDown;
    return props.sortDirection === 'asc' ? ArrowUp : ArrowDown;
};

const handleSearch = () => {
    emit('search', localSearch.value);
};

const clearSearch = () => {
    localSearch.value = '';
    emit('search', '');
};

const handlePageChange = (page) => {
    emit('page-change', page);
};

const handlePerPageChange = (perPage) => {
    emit('per-page-change', parseInt(perPage));
};

// Get cell value from row using dot notation (e.g., 'user.name')
const getCellValue = (row, key) => {
    return key.split('.').reduce((obj, k) => obj?.[k], row);
};

// Pagination computed values
const paginationInfo = computed(() => {
    if (!props.pagination) return null;
    const { current_page, per_page, total, from, to } = props.pagination;
    return {
        currentPage: current_page,
        perPage: parseInt(per_page), // Ensure it's a number
        total,
        from: from || 0,
        to: to || 0,
        lastPage: Math.ceil(total / per_page),
    };
});

const defaultPerPageOptions = [10, 15, 20, 50, 100];

const perPageOptions = computed(() => {
    const options = [...defaultPerPageOptions];
    if (paginationInfo.value && !options.includes(paginationInfo.value.perPage)) {
        options.push(paginationInfo.value.perPage);
        options.sort((a, b) => a - b);
    }
    return options;
});
</script>

<template>
    <div class="space-y-4">
        <!-- Search Bar -->
        <div v-if="searchable" class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input v-model="localSearch" type="text" :placeholder="searchPlaceholder" class="pl-9 pr-9"
                    @keyup.enter="handleSearch" />
                <button v-if="localSearch" @click="clearSearch"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                    <X class="h-4 w-4" />
                </button>
            </div>
            <Button @click="handleSearch" variant="secondary">Search</Button>
        </div>

        <!-- Selection Info -->
        <div v-if="selectable && selectedRows.length > 0" class="flex items-center gap-2 text-sm text-muted-foreground">
            <Badge variant="secondary">{{ selectedRows.length }} selected</Badge>
            <Button variant="ghost" size="sm" @click="selectedRows = []; emit('selection-change', [])">
                Clear selection
            </Button>
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-if="selectable" class="w-12">
                            <Checkbox :checked="allSelected" :indeterminate="someSelected"
                                @update:checked="toggleAll" />
                        </TableHead>
                        <TableHead v-for="column in columns" :key="column.key" :class="[
                            column.sortable && 'cursor-pointer select-none',
                            column.class,
                        ]" :style="column.width ? { width: column.width } : {}"
                            @click="column.sortable && handleSort(column.key)">
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <component v-if="column.sortable" :is="getSortIcon(column.key)" class="h-4 w-4" />
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <!-- Loading State -->
                    <template v-if="loading">
                        <TableRow v-for="i in 5" :key="i">
                            <TableCell v-if="selectable">
                                <Skeleton class="h-4 w-4" />
                            </TableCell>
                            <TableCell v-for="column in columns" :key="column.key">
                                <Skeleton class="h-4 w-full" />
                            </TableCell>
                        </TableRow>
                    </template>

                    <!-- Empty State -->
                    <TableRow v-else-if="data.length === 0">
                        <TableCell :colspan="columns.length + (selectable ? 1 : 0)"
                            class="h-24 text-center text-muted-foreground">
                            {{ emptyMessage }}
                        </TableCell>
                    </TableRow>

                    <!-- Data Rows -->
                    <template v-else>
                        <TableRow v-for="row in data" :key="row.id"
                            v-memo="[row.id, row.updated_at, isRowSelected(row.id)]"
                            :class="{ 'bg-muted/50': isRowSelected(row.id) }">
                            <TableCell v-if="selectable">
                                <Checkbox :checked="isRowSelected(row.id)"
                                    @update:checked="toggleRow(row.id, $event)" />
                            </TableCell>
                            <TableCell v-for="column in columns" :key="column.key" :class="column.cellClass">
                                <!-- Custom slot -->
                                <slot v-if="$slots[`cell-${column.key}`]" :name="`cell-${column.key}`" :row="row"
                                    :value="getCellValue(row, column.key)" />
                                <!-- Default rendering -->
                                <template v-else>
                                    {{ getCellValue(row, column.key) ?? '-' }}
                                </template>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="paginationInfo" class="flex items-center justify-between px-2">
            <div class="flex-1 text-sm text-muted-foreground">
                <span v-if="selectable && selectedRows.length > 0">
                    {{ selectedRows.length }} of {{ paginationInfo.total }} row(s) selected.
                </span>
                <span v-else>
                    {{ paginationInfo.total }} results
                </span>
            </div>
            <div class="flex items-center space-x-6 lg:space-x-8">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">Rows per page</p>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm" class="h-8 w-[70px] justify-between">
                                {{ paginationInfo.perPage }}
                                <ChevronDown class="h-4 w-4 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem v-for="option in perPageOptions" :key="option"
                                @click="handlePerPageChange(String(option))">
                                {{ option }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="flex w-[100px] items-center justify-center text-sm font-medium">
                    Page {{ paginationInfo.currentPage }} of {{ paginationInfo.lastPage }}
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex"
                        :disabled="paginationInfo.currentPage === 1" @click="handlePageChange(1)">
                        <span class="sr-only">Go to first page</span>
                        <ChevronsLeft class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" class="h-8 w-8 p-0" :disabled="paginationInfo.currentPage === 1"
                        @click="handlePageChange(paginationInfo.currentPage - 1)">
                        <span class="sr-only">Go to previous page</span>
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" class="h-8 w-8 p-0"
                        :disabled="paginationInfo.currentPage === paginationInfo.lastPage"
                        @click="handlePageChange(paginationInfo.currentPage + 1)">
                        <span class="sr-only">Go to next page</span>
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex"
                        :disabled="paginationInfo.currentPage === paginationInfo.lastPage"
                        @click="handlePageChange(paginationInfo.lastPage)">
                        <span class="sr-only">Go to last page</span>
                        <ChevronsRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
