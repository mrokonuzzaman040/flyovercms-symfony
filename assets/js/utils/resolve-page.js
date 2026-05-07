export function resolvePageComponent(name, pages) {
    const page = pages[`./Pages/${name}.vue`]
    if (!page) {
        throw new Error(`Page not found: ./Pages/${name}.vue`)
    }
    return typeof page === 'function' ? page() : page
}
