<script setup>
import { computed } from 'vue';
import { Badge } from '@/Components/ui/badge';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    config: {
        type: Object,
        default: null,
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['sm', 'default', 'lg'].includes(value),
    },
});

// Default status configurations
const defaultStatusConfig = {
    // Lead statuses
    new: { label: 'New', variant: 'default', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' },
    contacted: { label: 'Contacted', variant: 'secondary', color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' },
    busy: { label: 'Busy', variant: 'secondary', color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' },
    unavailable: { label: 'Unavailable', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
    no_answer: { label: 'No Answer', variant: 'secondary', color: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400' },
    qualified: { label: 'Qualified', variant: 'default', color: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' },
    not_qualified: { label: 'Not Qualified', variant: 'secondary', color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' },
    follow_up: { label: 'Follow Up', variant: 'default', color: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' },
    office_visited: { label: 'Office Visited', variant: 'default', color: 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400' },
    documentation_pending: { label: 'Docs Pending', variant: 'secondary', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' },
    application_submitted: { label: 'Submitted', variant: 'default', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' },
    under_review: { label: 'Under Review', variant: 'secondary', color: 'bg-violet-100 text-violet-800 dark:bg-violet-900/30 dark:text-violet-400' },
    interview_scheduled: { label: 'Interview', variant: 'default', color: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' },
    approved: { label: 'Approved', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },

    in_process: { label: 'In Process', variant: 'default', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' },
    on_hold: { label: 'On Hold', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
    converted: { label: 'Converted', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    flight_done: { label: 'Converted', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    rejected: { label: 'Rejected', variant: 'destructive', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' },
    cancelled: { label: 'Cancelled', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
    lost: { label: 'Lost', variant: 'destructive', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' },

    // Priority statuses
    urgent: { label: 'Urgent', variant: 'destructive', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' },
    high: { label: 'High', variant: 'default', color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' },
    medium: { label: 'Medium', variant: 'secondary', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' },
    low: { label: 'Low', variant: 'secondary', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },

    // Generic statuses
    active: { label: 'Active', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    inactive: { label: 'Inactive', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
    pending: { label: 'Pending', variant: 'secondary', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' },
    completed: { label: 'Completed', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    failed: { label: 'Failed', variant: 'destructive', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' },
    success: { label: 'Success', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },

    // Boolean-like statuses
    yes: { label: 'Yes', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    no: { label: 'No', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
    true: { label: 'Yes', variant: 'default', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' },
    false: { label: 'No', variant: 'secondary', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' },
};

const statusInfo = computed(() => {
    // Handle null/undefined status
    if (!props.status) {
        return {
            label: 'Unknown',
            variant: 'secondary',
            color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        };
    }

    const normalizedStatus = String(props.status).toLowerCase().replace(/\s+/g, '_');

    // Check custom config first
    if (props.config?.[normalizedStatus]) {
        return props.config[normalizedStatus];
    }

    // Fall back to default config
    if (defaultStatusConfig[normalizedStatus]) {
        return defaultStatusConfig[normalizedStatus];
    }

    // Generate default for unknown statuses
    return {
        label: props.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
        variant: 'secondary',
        color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    };
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-xs px-1.5 py-0.5',
        default: 'text-xs px-2 py-0.5',
        lg: 'text-sm px-2.5 py-1',
    };
    return sizes[props.size] || sizes.default;
});
</script>

<template>
    <span v-if="status" class="inline-flex items-center rounded-full font-medium"
        :class="[statusInfo.color, sizeClasses]">
        {{ statusInfo.label }}
    </span>
</template>
