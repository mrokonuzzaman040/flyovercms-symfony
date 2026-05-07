export const channelCampaignAccents = {
    sky: {
        iconContainer: 'bg-sky-500/10 dark:bg-sky-500/20 ring-1 ring-sky-500/20',
        iconText: 'text-sky-500',
        selectedPill: 'bg-sky-500/10 dark:bg-sky-500/20 ring-1 ring-sky-500/20',
        selectedPillText: 'text-sky-600 dark:text-sky-400',
        filterControl: 'flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 px-3 py-2 text-xs transition-shadow focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/20 dark:bg-background/50',
        actionButton: 'bg-sky-500 text-white shadow-sm hover:bg-sky-600',
        selectionStateSelected: 'border-sky-500 bg-sky-500 text-white',
        selectionStatePartial: 'border-sky-500 bg-sky-500/20',
        selectionStatePartialBar: 'bg-sky-500',
        selectedTable: 'border-sky-500/40 ring-1 ring-sky-500/20',
        checkbox: 'data-[state=checked]:border-sky-500 data-[state=checked]:bg-sky-500',
        loadingSpinner: 'border-sky-500/30 border-t-sky-500',
        selectedRow: 'border-l-2 border-l-sky-500 bg-sky-500/5 dark:bg-sky-500/10',
        selectedStatus: 'bg-sky-500/20 text-sky-600 ring-1 ring-sky-500/30 dark:text-sky-400',
        selectedFooterText: 'text-sky-600 dark:text-sky-400',
        selectedTemplate: 'border-sky-500 bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20 dark:text-sky-400',
        variableCode: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    },
    emerald: {
        iconContainer: 'bg-emerald-500/10 dark:bg-emerald-500/20 ring-1 ring-emerald-500/20',
        iconText: 'text-emerald-500',
        selectedPill: 'bg-emerald-500/10 dark:bg-emerald-500/20 ring-1 ring-emerald-500/20',
        selectedPillText: 'text-emerald-600 dark:text-emerald-400',
        filterControl: 'flex h-9 min-w-[120px] rounded-lg border border-border/50 bg-background/80 px-3 py-2 text-xs transition-shadow focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 dark:bg-background/50',
        actionButton: 'bg-emerald-500 text-white shadow-sm hover:bg-emerald-600',
        selectionStateSelected: 'border-emerald-500 bg-emerald-500 text-white',
        selectionStatePartial: 'border-emerald-500 bg-emerald-500/20',
        selectionStatePartialBar: 'bg-emerald-500',
        selectedTable: 'border-emerald-500/40 ring-1 ring-emerald-500/20',
        checkbox: 'data-[state=checked]:border-emerald-500 data-[state=checked]:bg-emerald-500',
        loadingSpinner: 'border-emerald-500/30 border-t-emerald-500',
        selectedRow: 'border-l-2 border-l-emerald-500 bg-emerald-500/5 dark:bg-emerald-500/10',
        selectedStatus: 'bg-emerald-500/20 text-emerald-600 ring-1 ring-emerald-500/30 dark:text-emerald-400',
        selectedFooterText: 'text-emerald-600 dark:text-emerald-400',
        selectedTemplate: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400',
        variableCode: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    },
};

export function resolveChannelCampaignAccent(accent) {
    return channelCampaignAccents[accent] ?? channelCampaignAccents.sky;
}
