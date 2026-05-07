<script setup>
/**
 * Bridge component: watches Command's filterState.search and triggers API search.
 * Must be rendered inside Command (as child of CommandDialog) to access useCommand().
 */
import { watch, inject } from 'vue';
import { useCommand } from '@/Components/ui/command';

const performSearch = inject('commandPalettePerformSearch');
const { filterState } = useCommand();

if (performSearch && filterState) {
    watch(
        () => filterState.search,
        (val) => {
            performSearch(val ?? '');
        },
        { immediate: false }
    );
}
</script>

<template>
    <span v-show="false" aria-hidden="true" />
</template>
