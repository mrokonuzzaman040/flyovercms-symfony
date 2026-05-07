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

## Phase 3 — Module Controllers

| Module | Laravel Source | Status |
|--------|---------------|--------|
| Dashboard | `Controllers/Dashboards/` | ⏳ Pending |
| Leads | `Controllers/Leads/` | ⏳ Pending |
| Departments | `Controllers/Departments/` | ⏳ Pending |
| Documents | `Controllers/Documents/` | ⏳ Pending |
| Marketing | `Controllers/Marketing/` | ⏳ Pending |
| Messaging | `Controllers/Messaging/` | ⏳ Pending |
| Organization | `Controllers/Organization/` | ⏳ Pending |
| Settings | `Controllers/Settings/` | ⏳ Pending |
| Reports | `Controllers/Reports/` | ⏳ Pending |
| System | `Controllers/System/` | ⏳ Pending |
| Users | `Controllers/Users/` | ⏳ Pending |
| Webhooks | `Controllers/Webhooks/` | ⏳ Pending |
| FlyoverBD API | `Controllers/FlyoverBD/` | ⏳ Pending |

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
