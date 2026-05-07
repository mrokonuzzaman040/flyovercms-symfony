import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from './utils/resolve-page'
import { route } from './utils/routes'
import axios from 'axios'
import '../css/app.css'

// Global route() helper — replaces Ziggy
window.route = route

// Send CSRF token on every Axios request (reads from <meta name="csrf-token">)
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}

createInertiaApp({
    resolve: (name) => resolvePageComponent(name, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
        app.config.globalProperties.$route = route
        app.use(plugin).mount(el)
    },
    progress: {
        color: '#6366f1',
    },
})
