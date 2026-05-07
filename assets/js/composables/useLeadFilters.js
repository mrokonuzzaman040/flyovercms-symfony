import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';

export function useLeadFilters(props) {
    const localSearchQuery = ref(props.filters?.search || '');

    const localFilters = ref({
        search: props.filters?.search || '',
        status: props.filters?.status || 'all',
        branch_id: props.filters?.branch_id || 'all',
        assigned_to: props.filters?.assigned_to || 'all',
        priority: props.filters?.priority || 'all',
        date_from: props.filters?.date_from || '',
        date_to: props.filters?.date_to || '',
        service_type: props.filters?.service_type || 'all',
        service_id: props.filters?.service_id || 'all',
        package_id: props.filters?.package_id || 'all',
        destination_country: props.filters?.destination_country || 'all',
        inactive_days: props.filters?.inactive_days || '',
    });

    watch(() => props.filters, (newFilters) => {
        if (newFilters && typeof newFilters === 'object') {
            localFilters.value = {
                search: newFilters.search || '',
                status: newFilters.status || 'all',
                branch_id: newFilters.branch_id || 'all',
                assigned_to: newFilters.assigned_to || 'all',
                priority: newFilters.priority || 'all',
                date_from: newFilters.date_from || '',
                date_to: newFilters.date_to || '',
                service_type: newFilters.service_type || 'all',
                service_id: newFilters.service_id || 'all',
                package_id: newFilters.package_id || 'all',
                destination_country: newFilters.destination_country || 'all',
                inactive_days: newFilters.inactive_days || '',
            };
        }
    }, { deep: true });

    const debouncedSearch = useDebounceFn((query) => {
        router.get(route('leads.index'), { ...localFilters.value, search: query }, {
            preserveState: true, preserveScroll: true, replace: true,
        });
    }, 400);

    watch(localSearchQuery, (newVal) => {
        localFilters.value.search = newVal;
        debouncedSearch(newVal);
    });

    const applyFilters = () => {
        router.get(route('leads.index'), {
            ...localFilters.value,
            per_page: props.pagination?.per_page || 15,
        }, { preserveState: true, preserveScroll: true, replace: true });
    };

    const clearFilters = () => {
        localSearchQuery.value = '';
        localFilters.value = {
            search: '', status: 'all', branch_id: 'all', assigned_to: 'all', priority: 'all',
            date_from: '', date_to: '', service_type: 'all', service_id: 'all',
            package_id: 'all', destination_country: 'all', inactive_days: '',
        };
        router.get(route('leads.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const handleSort = ({ field, direction }) => {
        router.get(route('leads.index'), {
            ...localFilters.value,
            sort_by: field,
            sort_direction: direction,
        }, { preserveState: true, preserveScroll: true, replace: true });
    };

    const handleSearch = (query) => {
        localSearchQuery.value = query;
        localFilters.value.search = query;
    };

    const handlePageChange = (page) => {
        router.get(route('leads.index'), { ...localFilters.value, page }, { preserveState: true, preserveScroll: true });
    };

    const handlePerPageChange = (perPage) => {
        router.get(route('leads.index'), { ...localFilters.value, per_page: perPage, page: 1 }, { preserveState: true, preserveScroll: true });
    };

    const hasActiveFilters = computed(() => {
        const f = localFilters.value;
        return !!(
            f.search ||
            (f.status && f.status !== 'all') ||
            (f.branch_id && f.branch_id !== 'all') ||
            (f.assigned_to && f.assigned_to !== 'all') ||
            (f.priority && f.priority !== 'all') ||
            f.date_from || f.date_to ||
            (f.service_type && f.service_type !== 'all') ||
            (f.service_id && f.service_id !== 'all') ||
            (f.package_id && f.package_id !== 'all') ||
            (f.destination_country && f.destination_country !== 'all') ||
            f.inactive_days
        );
    });

    return {
        localSearchQuery, localFilters, hasActiveFilters,
        applyFilters, clearFilters, handleSort, handleSearch, handlePageChange, handlePerPageChange,
    };
}
