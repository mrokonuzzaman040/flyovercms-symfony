import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useModules() {
    const page = usePage();

    const activeModules = computed(() => {
        const modules = page.props.modules?.active;
        return Array.isArray(modules) ? modules : [];
    });

    const installedModules = computed(() => {
        const modules = page.props.modules?.installed;
        return Array.isArray(modules) ? modules : [];
    });

    const activeModuleSet = computed(() => new Set(activeModules.value));

    const isModuleActive = (slug) => {
        if (!slug || typeof slug !== 'string') {
            return false;
        }

        return activeModuleSet.value.has(slug);
    };

    return {
        activeModules,
        installedModules,
        isModuleActive,
    };
}
