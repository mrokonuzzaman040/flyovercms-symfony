/**
 * Symfony route helper — drop-in replacement for Ziggy's route()
 *
 * Usage: route('leads.show', { id: 5 })  →  '/leads/5'
 *        route('leads.index')             →  '/leads'
 *        route('leads.index', { q: 'foo', page: 2 })  →  '/leads?q=foo&page=2'
 */

const ROUTES = {
  // Auth
  'login':                            '/login',
  'auth.login':                       '/login',
  'auth.logout':                      '/logout',
  'logout':                           '/logout',
  'password.request':                 '/forgot-password',
  'password.email':                   '/forgot-password',
  'password.update':                  '/reset-password',

  // Dashboard
  'dashboard':                        '/dashboard',
  'dashboard.index':                  '/dashboard',

  // Leads
  'leads.index':                      '/leads',
  'leads.create':                     '/leads/create',
  'leads.store':                      '/leads',
  'leads.show':                       '/leads/{id}',
  'leads.edit':                       '/leads/{id}/edit',
  'leads.update':                     '/leads/{id}',
  'leads.destroy':                    '/leads/{id}',
  'leads.status':                     '/leads/status/{status}',
  'leads.status.update':              '/leads/{id}/status',
  'leads.status.history':             '/leads/{id}/status/history',
  'leads.quick-update':               '/leads/{id}/quick-update',
  'leads.quick-transfer':             '/leads/{id}/quick-transfer',
  'leads.transfer':                   '/leads/{id}/transfer',
  'leads.transfer.store':             '/leads/{id}/transfer',
  'leads.assign-branch':              '/leads/{id}/assign-branch',
  'leads.bulk-assign-branch':         '/leads/bulk-assign-branch',
  'leads.no-branch':                  '/leads/no-branch',
  'leads.overdue-follow-ups':         '/leads/overdue-follow-ups',
  'leads.send-communication':         '/leads/{id}/send-communication',
  'leads.order-new-service':          '/leads/{id}/order-new-service',
  'leads.update-services-packages':   '/leads/{id}/update-services-packages',
  'leads.notes.store':                '/leads/{id}/notes',
  'leads.call-history':               '/leads/{id}/call-history',
  'leads.call-history.store':         '/leads/{id}/call-history',
  'leads.follow-up.store':            '/leads/{id}/follow-up',
  'leads.bulk-upload':                '/leads/bulk-upload',
  'leads.bulk-upload.store':          '/leads/bulk-upload',
  'leads.bulk-upload.update':         '/leads/bulk-upload/{id}',

  // Lead Documents
  'leads.documents.index':            '/leads/{lead_id}/documents',
  'leads.documents.create':           '/leads/{lead_id}/documents/create',
  'leads.documents.store':            '/leads/{lead_id}/documents',
  'leads.documents.edit':             '/leads/{lead_id}/documents/{id}/edit',
  'leads.documents.update':           '/leads/{lead_id}/documents/{id}',
  'leads.documents.destroy':          '/leads/{lead_id}/documents/{id}',
  'leads.documents.download':         '/leads/{lead_id}/documents/{id}/download',
  'leads.documents.preview':          '/leads/{lead_id}/documents/{id}/preview',

  // Departments
  'departments.index':                '/departments',
  'departments.create':               '/departments/create',
  'departments.store':                '/departments',
  'departments.edit':                 '/departments/{id}/edit',
  'departments.update':               '/departments/{id}',
  'departments.destroy':              '/departments/{id}',
  'departments.accounts':             '/departments/accounts',
  'departments.visa':                 '/departments/visa',

  // Departments — Transfers
  'departments.transfers.pending':    '/departments/transfers/pending',
  'departments.transfers.accept':     '/departments/transfers/{id}/accept',
  'departments.transfers.reject':     '/departments/transfers/{id}/reject',
  'departments.transfers.show-form':  '/departments/transfers/{id}',
  'departments.transfers.transfer':   '/departments/transfers',

  // Departments — Visa
  'departments.visa.index':           '/departments/visa',
  'departments.visa.show':            '/departments/visa/{id}',
  'departments.visa.leads':           '/departments/visa/leads',
  'departments.visa.analytics':       '/departments/visa/analytics',
  'departments.visa.documents':       '/departments/visa/{id}/documents',
  'departments.visa.request-document':'/departments/visa/{id}/request-document',
  'departments.visa.download-documents':'/departments/visa/{id}/documents/download',
  'departments.visa.export-pdf':      '/departments/visa/{id}/export-pdf',
  'departments.visa.exports':         '/departments/visa/exports',
  'departments.visa.update-status':   '/departments/visa/{id}/status',

  // Documents (standalone)
  'documents.index':                  '/documents',
  'documents.store':                  '/documents',
  'documents.destroy':                '/documents/{id}',

  // Document Requests
  'document-requests.index':          '/document-requests',
  'document-requests.show':           '/document-requests/{id}',
  'document-requests.fulfill':        '/document-requests/{id}/fulfill',
  'document-requests.cancel':         '/document-requests/{id}/cancel',

  // Branches
  'branches.index':                   '/branches',
  'branches.create':                  '/branches/create',
  'branches.store':                   '/branches',
  'branches.show':                    '/branches/{id}',
  'branches.edit':                    '/branches/{id}/edit',
  'branches.update':                  '/branches/{id}',
  'branches.destroy':                 '/branches/{id}',
  'branch.dashboard':                 '/branches/{id}/dashboard',
  'branch.leads':                     '/branches/{id}/leads',
  'branch.users':                     '/branches/{id}/users',
  'branch.assign-user':               '/branches/{id}/users',
  'branch.remove-user':               '/branches/{id}/users/{user_id}',

  // Marketing
  'marketing.index':                  '/marketing',
  'marketing.campaigns':              '/marketing/campaigns',
  'marketing.campaigns.index':        '/marketing/campaigns',
  'marketing.campaigns.create':       '/marketing/campaigns/create',
  'marketing.campaigns.store':        '/marketing/campaigns',
  'marketing.campaigns.show':         '/marketing/campaigns/{id}',
  'marketing.campaigns.edit':         '/marketing/campaigns/{id}/edit',
  'marketing.campaigns.update':       '/marketing/campaigns/{id}',
  'marketing.campaigns.destroy':      '/marketing/campaigns/{id}',
  'marketing.campaigns.start':        '/marketing/campaigns/{id}/start',
  'marketing.campaigns.cancel':       '/marketing/campaigns/{id}/cancel',
  'marketing.campaigns.resume':       '/marketing/campaigns/{id}/resume',
  'marketing.campaigns.leads':        '/marketing/campaigns/{id}/leads',
  'marketing.campaigns.prepare-recipients': '/marketing/campaigns/{id}/prepare-recipients',

  // Marketing — SMS
  'marketing.campaigns.sms':          '/marketing/campaigns/sms',
  'marketing.sms.campaigns.index':    '/marketing/campaigns/sms',
  'marketing.sms.campaigns.create':   '/marketing/campaigns/sms/create',
  'marketing.sms.quick-send':         '/marketing/sms/quick-send',

  // Marketing — Email
  'marketing.campaigns.email':        '/marketing/campaigns/email',
  'marketing.email.campaigns.index':  '/marketing/campaigns/email',
  'marketing.email.campaigns.create': '/marketing/campaigns/email/create',
  'marketing.email.quick-send':       '/marketing/email/quick-send',

  // Marketing — WhatsApp
  'marketing.campaigns.whatsapp':     '/marketing/campaigns/whatsapp',
  'marketing.whatsapp.index':         '/marketing/whatsapp',
  'marketing.whatsapp.show':          '/marketing/whatsapp/{id}',
  'marketing.whatsapp.send':          '/marketing/whatsapp/send',
  'marketing.whatsapp.send-media':    '/marketing/whatsapp/send-media',
  'marketing.whatsapp.history':       '/marketing/whatsapp/history',
  'marketing.whatsapp.poll':          '/marketing/whatsapp/poll',
  'marketing.whatsapp.chat.poll':     '/marketing/whatsapp/{id}/poll',

  // Marketing — Drip
  'marketing.campaigns.drip':         '/marketing/campaigns/drip',
  'marketing.drip-campaigns.index':   '/marketing/drip-campaigns',
  'marketing.drip-campaigns.create':  '/marketing/drip-campaigns/create',
  'marketing.drip-campaigns.store':   '/marketing/drip-campaigns',
  'marketing.drip-campaigns.show':    '/marketing/drip-campaigns/{id}',
  'marketing.drip-campaigns.edit':    '/marketing/drip-campaigns/{id}/edit',
  'marketing.drip-campaigns.update':  '/marketing/drip-campaigns/{id}',
  'marketing.drip-campaigns.destroy': '/marketing/drip-campaigns/{id}',
  'marketing.drip-campaigns.toggle-active': '/marketing/drip-campaigns/{id}/toggle-active',

  // Marketing — Automated Campaigns
  'marketing.automated-campaigns.index':   '/marketing/automated-campaigns',
  'marketing.automated-campaigns.create':  '/marketing/automated-campaigns/create',
  'marketing.automated-campaigns.store':   '/marketing/automated-campaigns',
  'marketing.automated-campaigns.show':    '/marketing/automated-campaigns/{id}',
  'marketing.automated-campaigns.edit':    '/marketing/automated-campaigns/{id}/edit',
  'marketing.automated-campaigns.update':  '/marketing/automated-campaigns/{id}',
  'marketing.automated-campaigns.destroy': '/marketing/automated-campaigns/{id}',
  'marketing.automated-campaigns.toggle-active': '/marketing/automated-campaigns/{id}/toggle-active',

  // Marketing — Campaign Alerts
  'marketing.campaign-alerts.index':   '/marketing/campaign-alerts',
  'marketing.campaign-alerts.create':  '/marketing/campaign-alerts/create',
  'marketing.campaign-alerts.store':   '/marketing/campaign-alerts',
  'marketing.campaign-alerts.show':    '/marketing/campaign-alerts/{id}',
  'marketing.campaign-alerts.edit':    '/marketing/campaign-alerts/{id}/edit',
  'marketing.campaign-alerts.update':  '/marketing/campaign-alerts/{id}',
  'marketing.campaign-alerts.destroy': '/marketing/campaign-alerts/{id}',
  'marketing.campaign-alerts.acknowledge': '/marketing/campaign-alerts/{id}/acknowledge',
  'marketing.campaign-alerts.reset':   '/marketing/campaign-alerts/{id}/reset',

  // Marketing — Multi-Channel
  'marketing.multi-channel-campaigns.index':   '/marketing/multi-channel-campaigns',
  'marketing.multi-channel-campaigns.create':  '/marketing/multi-channel-campaigns/create',
  'marketing.multi-channel-campaigns.store':   '/marketing/multi-channel-campaigns',
  'marketing.multi-channel-campaigns.show':    '/marketing/multi-channel-campaigns/{id}',
  'marketing.multi-channel-campaigns.edit':    '/marketing/multi-channel-campaigns/{id}/edit',
  'marketing.multi-channel-campaigns.update':  '/marketing/multi-channel-campaigns/{id}',
  'marketing.multi-channel-campaigns.destroy': '/marketing/multi-channel-campaigns/{id}',

  // Marketing — Templates
  'marketing.templates':              '/marketing/templates',
  'marketing.templates.index':        '/marketing/templates',
  'marketing.templates.create':       '/marketing/templates/create',
  'marketing.templates.store':        '/marketing/templates',
  'marketing.templates.show':         '/marketing/templates/{id}',
  'marketing.templates.edit':         '/marketing/templates/{id}/edit',
  'marketing.templates.update':       '/marketing/templates/{id}',
  'marketing.templates.destroy':      '/marketing/templates/{id}',
  'marketing.templates.duplicate':    '/marketing/templates/{id}/duplicate',

  // Marketing — Segments
  'marketing.segments':               '/marketing/segments',
  'marketing.segments.index':         '/marketing/segments',
  'marketing.segments.create':        '/marketing/segments/create',
  'marketing.segments.store':         '/marketing/segments',
  'marketing.segments.show':          '/marketing/segments/{id}',
  'marketing.segments.edit':          '/marketing/segments/{id}/edit',
  'marketing.segments.update':        '/marketing/segments/{id}',
  'marketing.segments.destroy':       '/marketing/segments/{id}',
  'marketing.segments.preview-size':  '/marketing/segments/{id}/preview-size',
  'marketing.segments.recalculate':   '/marketing/segments/{id}/recalculate',

  // Marketing — Analytics / Logs / Queue
  'marketing.analytics':              '/marketing/analytics',
  'marketing.analytics.dashboard':    '/marketing/analytics/dashboard',
  'marketing.logs.index':             '/marketing/logs',
  'marketing.queue.index':            '/marketing/queue',
  'marketing.queue.retry':            '/marketing/queue/{id}/retry',
  'marketing.queue.retry-all':        '/marketing/queue/retry-all',
  'marketing.queue.forget':           '/marketing/queue/{id}/forget',

  // Messaging
  'messaging.index':                  '/messaging',
  'messaging.conversations':          '/messaging/conversations',
  'messaging.conversations.index':    '/messaging/conversations',
  'messaging.conversations.show':     '/messaging/conversations/{id}',
  'messaging.conversations.store':    '/messaging/conversations',
  'messaging.conversations.messages': '/messaging/conversations/{id}/messages',
  'messaging.conversations.read':     '/messaging/conversations/{id}/read',
  'messaging.messages.store':         '/messaging/messages',
  'messaging.messages.forward':       '/messaging/messages/{id}/forward',

  // Reports
  'reports.index':                    '/reports',
  'reports.leads':                    '/reports/leads',
  'reports.generate':                 '/reports/generate',
  'reports.download':                 '/reports/download',

  // Settings
  'settings.index':                   '/settings',
  'settings.update':                  '/settings',
  'settings.notifications':           '/settings/notifications',
  'settings.security':                '/settings/security',

  // Admin Settings
  'admin.settings.index':             '/admin/settings',
  'admin.settings.email.update':      '/admin/settings/email',
  'admin.settings.sms.update':        '/admin/settings/sms',
  'admin.settings.whatsapp':          '/admin/settings/whatsapp',
  'admin.settings.whatsapp.update':   '/admin/settings/whatsapp',
  'admin.settings.security.update':   '/admin/settings/security',
  'admin.settings.modules.store':     '/admin/settings/modules',
  'admin.settings.modules.activate':  '/admin/settings/modules/{id}/activate',
  'admin.settings.modules.deactivate':'/admin/settings/modules/{id}/deactivate',
  'admin.settings.modules.destroy':   '/admin/settings/modules/{id}',

  // Search + Notifications
  'search.global':                    '/search',
  'global-search':                    '/search',
  'notifications.index':              '/notifications',
  'notifications.api':                '/notifications/api',
  'notifications.read':               '/notifications/{id}/read',
  'notifications.read-all':           '/notifications/read-all',
  'notifications.mark-read':          '/notifications/{id}/read',
  'notifications.mark-unread':        '/notifications/{id}/unread',
  'notifications.mark-all-read':      '/notifications/read-all',
  'notifications.destroy':            '/notifications/{id}',

  // Users
  'users.index':                      '/users',
  'users.create':                     '/users/create',
  'users.store':                      '/users',
  'users.show':                       '/users/{id}',
  'users.edit':                       '/users/{id}/edit',
  'users.update':                     '/users/{id}',
  'users.destroy':                    '/users/{id}',

  // Roles
  'roles.index':                      '/roles',
  'roles.create':                     '/roles/create',
  'roles.store':                      '/roles',
  'roles.show':                       '/roles/{id}',
  'roles.edit':                       '/roles/{id}/edit',
  'roles.update':                     '/roles/{id}',
  'roles.destroy':                    '/roles/{id}',

  // Permissions
  'permissions.index':                '/permissions',
  'permissions.create':               '/permissions/create',
  'permissions.store':                '/permissions',
  'permissions.show':                 '/permissions/{id}',
  'permissions.edit':                 '/permissions/{id}/edit',
  'permissions.update':               '/permissions/{id}',
  'permissions.destroy':              '/permissions/{id}',

  // User Roles
  'user-roles.index':                 '/user-roles',
  'user-roles.create':                '/user-roles/create',
  'user-roles.store':                 '/user-roles',
  'user-roles.show':                  '/user-roles/{id}',
  'user-roles.edit':                  '/user-roles/{id}/edit',
  'user-roles.update':                '/user-roles/{id}',

  // Profile
  'profile.show':                     '/profile',
  'profile.update':                   '/profile',
  'profile.password.update':          '/profile/password',

  // Vendors
  'vendors.index':                    '/vendors',
  'vendors.create':                   '/vendors/create',
  'vendors.store':                    '/vendors',
  'vendors.show':                     '/vendors/{id}',
  'vendors.edit':                     '/vendors/{id}/edit',
  'vendors.update':                   '/vendors/{id}',
  'vendors.destroy':                  '/vendors/{id}',

  // Services
  'services.index':                   '/services',
  'services.create':                  '/services/create',
  'services.store':                   '/services',
  'services.show':                    '/services/{id}',
  'services.edit':                    '/services/{id}/edit',
  'services.update':                  '/services/{id}',
  'services.destroy':                 '/services/{id}',

  // Packages
  'packages.index':                   '/packages',
  'packages.create':                  '/packages/create',
  'packages.store':                   '/packages',
  'packages.show':                    '/packages/{id}',
  'packages.edit':                    '/packages/{id}/edit',
  'packages.update':                  '/packages/{id}',
  'packages.destroy':                 '/packages/{id}',

  // Transfers
  'transfers.pending':                '/transfers/pending',
  'transfers.accept':                 '/transfers/{id}/accept',
  'transfers.accept-all':             '/transfers/accept-all',
  'transfers.reject':                 '/transfers/{id}/reject',
  'transfers.reject-all':             '/transfers/reject-all',
  'transfers.cancel':                 '/transfers/{id}/cancel',

  // Follow-ups
  'follow-ups.history':               '/follow-ups/history',
  'follow-ups.complete':              '/follow-ups/{id}/complete',
  'follow-ups.reschedule':            '/follow-ups/{id}/reschedule',
  'follow-ups.update':                '/follow-ups/{id}',

  // Activity Log
  'activity-log.index':               '/activity-log',

  // Office Visits
  'office-visits.complete':           '/office-visits/{id}/complete',

  // Flyover BD
  'flyover-bd.index':                 '/flyover-bd',
  'flyover-bd.bookings':              '/flyover-bd/bookings',
  'flyover-bd.contacts':              '/flyover-bd/contacts',
  'flyover-bd.inquiries':             '/flyover-bd/inquiries',

  // API
  'api.leads.transfer-users':         '/api/leads/transfer-users',
};

/**
 * Build a URL from a route name and optional parameters.
 *
 * Path params: { id: 5 }        → replaces {id} in the pattern
 * Extra params: anything not in the path pattern → appended as query string
 *
 * @param {string} name
 * @param {Record<string,any>|Array<any>|string|number} [params]
 * @returns {string}
 */
export function route(name, params = {}) {
  /** @type {Record<string,string>} */
  const routes = /** @type {any} */ (ROUTES);
  const pattern = routes[name];

  if (!pattern) {
    if (typeof import.meta !== 'undefined' && /** @type {any} */ (import.meta).env?.DEV) {
      console.warn(`[route] Unknown route: "${name}"`);
    }
    return `/${name.replace(/\./g, '/')}`;
  }

  // Primitive shorthand: route('leads.show', 5) → { id: 5 }
  if (params !== null && typeof params !== 'object') {
    params = { id: params };
  }

  // Array shorthand: route('foo', [1, 'bar']) → positional substitution
  if (Array.isArray(params)) {
    const placeholders = [...pattern.matchAll(/\{([^}]+)\}/g)].map(/** @param {any} m */ m => m[1]);
    /** @type {Record<string,any>} */
    const obj = {};
    placeholders.forEach(/** @param {string} key @param {number} i */ (key, i) => {
      if (/** @type {any[]} */ (params)[i] !== undefined) obj[key] = /** @type {any[]} */ (params)[i];
    });
    params = obj;
  }

  /** @type {Record<string,any>} */
  const p = /** @type {any} */ (params);
  const usedKeys = new Set();

  // Replace all {placeholder} tokens
  let url = pattern.replace(/\{([^}]+)\}/g, /** @param {string} _ @param {string} key */ (_, key) => {
    usedKeys.add(key);
    if (p[key] !== undefined) return p[key];
    if (typeof import.meta !== 'undefined' && /** @type {any} */ (import.meta).env?.DEV) {
      console.warn(`[route] Missing param "${key}" for route "${name}"`);
    }
    return `{${key}}`;
  });

  // Remaining params → query string
  const qs = Object.entries(p)
    .filter(([k]) => !usedKeys.has(k))
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
    .join('&');

  return qs ? `${url}?${qs}` : url;
}

export default route;
