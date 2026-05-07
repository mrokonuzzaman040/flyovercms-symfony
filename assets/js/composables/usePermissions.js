import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for permission and role checks aligned with backend.
 * Uses auth.permissions and auth.roles from Inertia shared props (HandleInertiaRequests).
 * Always use the same permission slugs as backend (middleware, policies, SidebarService).
 */
export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => {
        const perms = page.props.auth?.permissions;
        return Array.isArray(perms) ? perms : [];
    });

    const roles = computed(() => {
        const r = page.props.auth?.roles;
        return Array.isArray(r) ? r : [];
    });

    const permissionSet = computed(() => new Set(permissions.value));
    const roleSet = computed(() => new Set(roles.value));

    const hasPermission = (slug) => {
        if (!slug || typeof slug !== 'string') return false;
        return permissionSet.value.has(slug);
    };

    const hasAnyPermission = (slugs) => {
        if (!Array.isArray(slugs) || slugs.length === 0) return false;
        const set = permissionSet.value;
        return slugs.some((s) => typeof s === 'string' && set.has(s));
    };

    const hasRole = (slug) => {
        if (!slug || typeof slug !== 'string') return false;
        return roleSet.value.has(slug);
    };

    const hasAnyRole = (slugs) => {
        if (!Array.isArray(slugs) || slugs.length === 0) return false;
        const set = roleSet.value;
        return slugs.some((s) => typeof s === 'string' && set.has(s));
    };

    return {
        permissions,
        roles,
        hasPermission,
        hasAnyPermission,
        hasRole,
        hasAnyRole,
    };
}
