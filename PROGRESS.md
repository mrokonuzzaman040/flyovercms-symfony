# FlyoverCMS Symfony — Migration Progress

Multi-tenant rebuild: Laravel 12 → Symfony 7.4 LTS + Inertia.js + Vue 3

---

## Phase 1 — Foundation ✅ COMPLETE

| Task | Status | Notes |
|------|--------|-------|
| Symfony 7.4 LTS scaffold | ✅ Done | `composer create-project symfony/skeleton:"7.4.*"` |
| Inertia.js bundle | ✅ Done | `tofandel/inertia-bundle ^4.0` |
| Vue 3 + Vite + Tailwind v4 | ✅ Done | Same stack as Laravel app |
| Doctrine ORM + MySQL | ✅ Done | Configured in `config/packages/doctrine.yaml` |
| Multi-tenant core | ✅ Done | TenantContext, TenantResolver, TenantFilter, TenantSubscriber |
| Security bundle | ✅ Done | Installed, config pending (User entity needed) |
| Redis cache + sessions | ✅ Done | Dev=filesystem, Prod=Redis |
| Symfony Messenger (queues) | ✅ Done | Dev=sync, Prod=Redis transport |
| cPanel deploy config | ✅ Done | `.htaccess`, `deploy/php.ini`, `deploy/deploy.sh` |

---

## Phase 2 — Entities + Auth ✅ COMPLETE

| Task | Status | Notes |
|------|--------|-------|
| Import MySQL schema → Doctrine entities | ✅ Done | User, Role, Permission, Branch, Department, Lead, Document |
| User entity + Auth controller | ✅ Done | Implements `UserInterface`, `PasswordAuthenticatedUserInterface` |
| Login / Logout Vue pages | ✅ Done | Ported + adapted (removed Ziggy, uses direct URLs) |
| Role + Permission entities | ✅ Done | ManyToMany with pivot tables |
| Inertia shared props | ✅ Done | `InertiaSharedPropsSubscriber` — auth, tenant, flash on every request |
| Initial DB migration | ✅ Done | `doctrine:migrations:migrate` — 14 queries, all tables created |
| `tenants` table | ✅ Done | New entity for multi-tenant — `slug`, `customDomain`, `isActive` |
| Security config | ✅ Done | Form login, bcrypt, ROLE_USER/ROLE_ADMIN hierarchy |
| Vue components + layouts | ✅ Done | All 30+ UI components copied from Laravel app (no changes needed) |

---

## Phase 3 — Module Controllers ✅ COMPLETE

| Module | Symfony Controller | Status | Notes |
|--------|-------------------|--------|-------|
| Dashboard | `Controller/Dashboard/DashboardController` | ✅ Done | Real stats from DB — leads by status, conversion rate, chart data |
| Leads | `Controller/Lead/LeadController` | ✅ Done | Full CRUD + LeadReadService + LeadWriteService |
| Departments | `Controller/Department/DepartmentController` | ✅ Done | CRUD + accounts/visa sub-routes |
| Documents | `Controller/Document/DocumentController` | ✅ Done | File upload to `/public/uploads/documents/` |
| Marketing | `Controller/Marketing/MarketingController` | ✅ Done | All campaign routes scaffolded |
| Messaging | `Controller/Messaging/MessagingController` | ✅ Done | Conversations scaffolded |
| Organization/Branches | `Controller/Organization/BranchController` | ✅ Done | Full CRUD + auto code generation |
| Settings | `Controller/Settings/SettingsController` | ✅ Done | Index + update + sub-routes |
| Admin Settings | `Controller/Settings/AdminSettingsController` | ✅ Done | Email/SMS/WhatsApp/Security/Modules |
| Reports | `Controller/Reports/ReportController` | ✅ Done | Summary stats + lead breakdown |
| System/Search | `Controller/System/GlobalSearchController` | ✅ Done | Lead search → JSON |
| System/Notifications | `Controller/System/NotificationController` | ✅ Done | Full CRUD + API endpoint + mark-unread |
| System/ActivityLog | `Controller/System/ActivityLogController` | ✅ Done | Paginated activity log |
| Users | `Controller/Users/UserController` | ✅ Done | Full CRUD |
| Roles | `Controller/Users/RoleController` | ✅ Done | Full CRUD + permission sync |
| Permissions | `Controller/Users/PermissionController` | ✅ Done | Full CRUD |
| Profile | `Controller/Users/ProfileController` | ✅ Done | View + update + password change |
| Vendors | `Controller/Catalog/VendorController` | ✅ Done | Full CRUD |
| Services | `Controller/Catalog/ServiceController` | ✅ Done | Full CRUD |
| Packages | `Controller/Catalog/PackageController` | ✅ Done | Full CRUD |
| Transfers | `Controller/Leads/TransferController` | ✅ Done | Pending/accept/reject/cancel scaffolded |
| Follow-ups | `Controller/Leads/FollowUpController` | ✅ Done | History/complete/reschedule scaffolded |
| Flyover BD | `Controller/FlyoverBd/FlyoverBdController` | ✅ Done | Index/bookings/contacts/inquiries |
| Vue Pages | `assets/js/Pages/` | ✅ Done | All pages copied from Laravel (25+ modules) |

**Total routes registered: 139** — verified via `php bin/console debug:router`

### New Entities (Phase 3 additions)
| Entity | Table | Status |
|--------|-------|--------|
| Vendor | `vendors` | ✅ Migrated |
| Service | `services` | ✅ Migrated |
| Package | `packages` | ✅ Migrated |
| Notification | `notifications` | ✅ Migrated |
| ActivityLog | `activity_logs` | ✅ Migrated |

---

## Phase 4 — Fix Vue Pages (Ziggy → Direct URLs) ✅ COMPLETE

All Vue pages use `route('name')` from Ziggy. Replaced with a JS `route()` helper.

| Task | Status | Notes |
|------|--------|-------|
| Auth/Login.vue | ✅ Fixed | Form posts to `/login` directly |
| `assets/js/utils/routes.js` | ✅ Done | 200+ route name → URL pattern map; handles primitive, array, and object params |
| `assets/js/app.js` | ✅ Done | `window.route = route` + Vue global property — zero changes to any Vue page file |

---

## Phase 4 — Vue Pages (Port from Laravel)

All Vue pages from `flyovercms/resources/js/Pages/` need minimal adaptation:
- Replace `route()` (Ziggy) with Symfony route helper (tofandel bundle provides this)
- Replace `$page.props.auth` with Symfony shared props via Inertia middleware
- Tailwind/Component imports: no changes needed

---

## Phase 5 — Production Hardening

| Task | Status |
|------|--------|
| PHPStan static analysis | ⏳ Pending |
| PHPUnit tests | ⏳ Pending |
| Symfony Profiler → remove from prod | ⏳ Pending |
| Rate limiting (symfony/rate-limiter) | ⏳ Pending |
| CSRF protection audit | ⏳ Pending |
| Websocket (Mercure or port Reverb) | ⏳ Pending |

---

## Phase 6 — API Platform (Future)

| Task | Status |
|------|--------|
| Install `api-platform/core` | ⏳ Future |
| Expose REST endpoints per module | ⏳ Future |
| JWT auth (lexik/jwt-authentication-bundle) | ⏳ Future |
| Decouple Vue frontend to standalone SPA | ⏳ Future |

---

## Project Structure

```
flyovercms-symfony/
├── assets/
│   ├── css/app.css                    # Tailwind v4
│   └── js/
│       ├── app.js                     # Inertia + Vue bootstrap
│       ├── Pages/                     # Vue page components
│       │   └── Dashboard/Index.vue    # Sample page
│       ├── Components/                # Shared Vue components
│       ├── Layouts/                   # Layout components
│       └── utils/resolve-page.js
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml              # ORM + TenantFilter
│   │   ├── inertia.yaml               # Inertia root template
│   │   ├── cache.yaml                 # Redis prod cache
│   │   ├── messenger.yaml             # Redis queues
│   │   └── framework.yaml            # Sessions
│   └── services.yaml                  # DI: TenantResolver root domain
├── deploy/
│   ├── php.ini                        # cPanel OPcache config
│   └── deploy.sh                      # Production deploy script
├── public/
│   └── .htaccess                      # Apache/cPanel rewrite rules
├── src/
│   ├── Controller/
│   │   └── Dashboard/DashboardController.php
│   ├── Entity/
│   │   └── Tenant.php
│   ├── Repository/
│   │   └── TenantRepository.php
│   └── Tenant/
│       ├── TenantContext.php          # Current tenant holder
│       ├── TenantFilter.php           # Doctrine SQL filter
│       ├── TenantResolver.php         # Subdomain → tenant lookup
│       ├── TenantScopedInterface.php  # Marker for filterable entities
│       └── TenantSubscriber.php       # KernelEvents::REQUEST hook
├── templates/
│   ├── base.html.twig                 # Vite assets + HTML shell
│   └── inertia/root.html.twig         # Inertia root div
├── .env                               # Environment config
├── vite.config.js
└── package.json
```

---

## Key Decisions

| Decision | Reason |
|----------|--------|
| Symfony 7.4 LTS (not 8.0) | Package ecosystem not ready for 8.0 (Inertia bundle max 7.x) |
| Single DB + tenant_id scoping | cPanel connection limits; simpler ops; stancl-style approach |
| Subdomain tenant resolution | `company.flyovercms.com` — clean, scalable, easy cPanel wildcard DNS |
| Redis for prod cache/queue | File cache fails at scale; Redis on cPanel VPS is standard |
| Dev=sync queue | No worker needed locally, same code path as prod |
| `tofandel/inertia-bundle ^4.0` | Only Inertia bundle supporting Symfony 7 |
