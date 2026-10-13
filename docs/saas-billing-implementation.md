# SaaS Billing — Plans, Self-Signup & Fixed-Price Subscriptions

Implementation plan for adding a fixed-price (monthly/annual) SaaS billing system
to the POS Laravel application, driven by `docs/saas-billing-system.md` and the
product requirements below.

## 1. Context

`docs/saas-billing-system.md` documents a **TypeScript/Next.js** billing module
(`src/lib/saas-billing-system/`, Stripe meters, a `saas_stripe` schema,
`docs/saas_billing_system.dbml`). **None of that exists in this repository.**
This repo is a **Laravel 8 modular monolith** (`nwidart/laravel-modules`, PHP 8.1,
Blade + Yajra DataTables). This plan implements the **fixed-price monthly/annual**
portion only (no usage/meters), translated to existing conventions, and extends it
with three product requirements:

- **A** — Plans bundle our real features: chosen **modules**, **resource limits**,
  and **permissions**.
- **B** — New **self-signup**: buy a package → provide company information.
- **C** — **Role rename**: `superadmin → admin`, `admin → company`.

Three sellable plans: **Basic** (basic features), **Pro** (all features, limited
resources), **Enterprise** (unlimited).

### Existing foundations

- Multi-tenant SaaS: `companies` are tenants, `users.company_id` links them,
  platform owner has `company_id = null`.
- `Role` / `Permission` support `is_platform`; `CompanyService::store()`
  provisions per-tenant roles/permissions from non-platform permissions.
- `database/seeders/RolePermissionSeeder.php` declares permission modules:
  Products, Categories, Accounts, Suppliers, Voucher Types, Vouchers,
  Sales Orders, Purchase Orders, Users, Managers, Employees, Customers,
  Promotions, Dashboard, Roles/Permissions, Settings.
- `database/seeders/RolePermissionSeeder.php` also declares a platform-only
  `SaaS` module (`view tenant`, `manage subscription`) — unused so far.
- Conventions to mirror: module layout
  `Modules/<Name>/{Config,Database/Migrations,Http/{Controllers,Requests},Models,Providers,Repositories,Resources/views,routes,Services}`;
  repositories extend `App\Repositories\BaseRepository`; services compose
  repositories; controllers wrap writes in `DB::beginTransaction`;
  `App\Models\UIDModel` provides the `uid` route key; each module
  `ServiceProvider` loads views, migrations, and `routes/web.php` under `web`.

## 2. Confirmed decisions

| Topic | Decision |
| --- | --- |
| Billable entity / operator | Company self-serve; platform `admin` can view/manage all |
| Checkout & cards | Stripe Checkout (redirect) + Customer Portal |
| Plan catalog | Platform admin CRUD; Stripe Product/Price synced from plan |
| Discounts | Coupons + promotion codes in scope |
| Gate | Hard gate tenant users when subscription missing/`past_due`/`canceled` |
| Trials / currency | None; single currency |
| Role rename | `superadmin → admin`, `admin → company`; keep `manager`/`employee`/`customer`; keep seeded emails |
| Plan provisioning | Copy plan permissions into company RBAC; record limits |
| Signup | Email + password before checkout; company info after payment |
| Limitable resources | Products, Users, Employees, Suppliers, Promotions, Purchase orders |
| Existing tenants | Left unprovisioned (they hit the gate) |
| Limit UX | Block with generic typed error (no upgrade CTA) |
| Suspend | Local access block (`companies.suspended_at`); Stripe subscription kept |
| Paid companies | Current subscription `active`/`trialing` |
| Due payments | `past_due`/`unpaid` subscriptions + overdue open invoices |
| Stripe balance | Live account balance (available + pending) + collected total |
| Usage graphs | Current used-vs-allocated only (no historical snapshots) |
| Billing currency | Dedicated `STRIPE_CURRENCY` (e.g. `usd`); `app.currency` PKR stays for POS |

---

## Part C — Role rename (do first; foundation for the rest)

Rename the platform role and the tenant owner role. Keep `manager`, `employee`,
and `customer`; keep the seeded emails as-is.

### Code references to update

Change every `hasRole('superadmin')`, `@role('superadmin')`, and
`role:superadmin` to `admin`:

| File | Location |
| --- | --- |
| `app/Http/Controllers/HomeController.php` | `:32` (`hasRole`), `:33` (view name optional) |
| `app/Models/Company.php` | `:16` |
| `Modules/ChartOfAccounts/Models/Voucher.php` | `:27` |
| `Modules/ChartOfAccounts/Models/Transaction.php` | `:16` |
| `Modules/RBAC/Models/Role.php` | `:19` |
| `Modules/Order/Models/Order.php` | `:18` |
| `Modules/Supplier/Models/Supplier.php` | `:15` |
| `Modules/Supplier/Models/PurchaseOrder.php` | `:24` |
| `Modules/Inventory/Models/Product.php` | `:16` |
| `Modules/Inventory/Models/Feature.php` | `:14` |
| `Modules/Inventory/Models/Promotion.php` | `:17` |
| `Modules/Inventory/Models/Category.php` | `:16` |
| `Modules/Employee/Models/User.php` | `:38` |
| `routes/web.php` | `:17` (`role:superadmin`) |
| `Modules/RBAC/Resources/views/roles/index.blade.php` | `@role('superadmin')` ×2 |
| `resources/views/companies/_form.blade.php` | `:76` |
| `resources/views/companies/index.blade.php` | `:84`, `:122`, manager column |

### Seeders & services

- `RolePermissionSeeder`: role list becomes
  `['admin', 'company', 'manager', 'employee', 'customer']`; the platform role
  variable renamed accordingly. The platform role is still assigned to
  `superadmin@gmail.com` (email intentionally kept).
- `CompanySeeder`: tenant role name `admin` → `company`.
- `CompanyService::store`: `$request->isManager ? 'manager' : 'company'`.

### Data migration (ordering matters — swap collision)

1. `Role::where('name', 'superadmin')->update(['name' => 'admin'])`
2. `Role::where('name', 'admin')->whereNotNull('company_id')->update(['name' => 'company'])`

`roles` has no unique constraint on `name`, so the intermediate state is safe.

### Cosmetic

- Login shortcut label `Superadmin Login` → `Admin Login`.
- Optionally rename the view `dashboard.superadmin` → `dashboard.admin`.

---

## Part A — Plans as bundles (modules + limits + permissions)

### Data model

- **billing_plans** — `uid`, `name`, `slug`, `description`,
  `stripe_product_id` (unique nullable), `status` enum(active,inactive),
  `is_public`, `sort`, timestamps.
- **billing_prices** — `uid`, `plan_id`, `stripe_price_id` (unique nullable),
  `interval` enum(month,year), `interval_count`, `unit_amount` (int cents),
  `currency`, `active`, timestamps.
- **billing_plan_features** — `plan_id`, `permission_module_id`
  (which feature modules are included).
- **billing_plan_permissions** — `plan_id`, `permission_id`
  (exact grants; non-platform permissions only).
- **billing_plan_limits** — `plan_id`,
  `resource_key` enum(products, users, employees, suppliers, promotions,
  purchase_orders), `limit_value` (int; `null` / `-1` = unlimited).

### Platform plan editor (platform `admin` role)

`PlanController` + views with tabs:

1. **Details** — name, slug, description, status, public, sort.
2. **Prices** — monthly + annual (amount, currency, active).
3. **Modules** — checkboxes grouped by `permission_modules`; selecting a module
   ticks its permissions by default.
4. **Permissions** — fine-grained checkboxes per module (tenant,
   `is_platform = false` only).
5. **Limits** — numeric inputs for the six resource keys; blank = unlimited.

On save: persist junctions, then `StripeSyncService::syncPlan()` (create/update
Product, create/update/archive Prices, persist Stripe ids).

### Provisioning (`PlanProvisioningService::provision(Company, Plan)`)

Runs at signup completion (and via a superadmin "apply plan" action):

1. Create the tenant `company` role.
2. For each plan permission, create a company `Permission` copy
   (`company_id`, `is_platform = false`) and attach it to the `company` role —
   reusing the exact pattern in `CompanyService::store`.
3. Limits are read live from the active subscription's plan at enforcement time.
   Caveat: editing a plan changes limits for current subscribers (documented
   behavior).
4. Optionally copy base sub-roles (`manager` / `employee` / `customer`) if
   configured.

### Limit enforcement (`PlanLimitService`)

`ensureWithinLimit(string $key, ?Company $company)` counts company-scoped rows
and throws `App\Exceptions\PlanLimitExceededException` (generic message, no
upgrade CTA). Wired into `store()` of:

- `Modules/Inventory/Services/ProductService.php` — products
- `Modules/Employee/Services/UserService.php` — users
- `Modules/Employee/Services/EmployeeService.php` — employees
- `Modules/Supplier/Services/SupplierService.php` — suppliers
- `Modules/Inventory/Services/PromotionService.php` — promotions
- `Modules/Supplier/Services/PurchaseOrderService.php` — purchase_orders

### Module gating

Because plan provisioning copies only the granted permissions, the existing
`permission:` route middleware and `AsideMenuLinks` permission filter already
hide/deny non-included modules. No new runtime module check is required
(friendly "not in plan" pages are optional polish).

---

## Part B — Self-signup: buy package → company info

### Pending signup model

- **billing_signups** — `uid`, `token` (unique), `user_id`, `plan_id`, `price_id`,
  `interval`, `stripe_checkout_session_id` (unique nullable), `stripe_customer_id`,
  `stripe_subscription_id`, `status` enum(pending, awaiting_info, completed,
  expired), `company_id` (nullable), `expires_at`, timestamps.

### Flow

1. **Public pricing** (`GET /pricing`, guest) — lists active public plans with a
   monthly/annual toggle and a feature/limit summary.
2. **Choose plan** → `POST /signup` (guest) with name, email, password, chosen
   price uid.
   - Create the `User` (status active, `company_id = null`, no role) +
     `billing_signups` row (status `pending`).
   - Create Stripe Checkout (`mode = subscription`, `customer_email`,
     `client_reference_id = signup uid`, promotion codes allowed) → redirect.
3. **Webhook** `checkout.session.completed` → locate the signup, store
   `stripe_customer_id` / `stripe_subscription_id`, set status `awaiting_info`.
   Authoritative linking; payload metadata is never trusted beyond the signup uid.
4. **Company info** (`GET`/`POST /signup/complete`, auth) — company name,
   company email/address/logo, admin user name.
   - Provision `Company`, `company` role, plan permissions, `DefaultService`
     accounts/vouchers/settings, and the `billing_customers` link; set the user
     `company_id` and attach the `company` role; mark the signup `completed`.
5. **Force gate** — middleware redirects any authenticated user with a pending
   signup and null `company_id` to `/signup/complete`; blocks users with neither a
   role nor a pending signup.

Abandoned checkouts expire via a scheduled command (optional).

**Risk:** `companies.created_by` is `NOT NULL`; make it nullable (migration) or
assign the platform admin / system user as creator for self-signup.

---

## Part 4 — Billing core (Stripe)

### Config & dependencies

- `composer require stripe/stripe-php` (pin API version `2025-08-27.basil`).
- `config/billing.php`: `key`, `secret`, `webhook_secret`, `currency`,
  `api_version`. `currency` is a **dedicated billing currency** sourced from
  `STRIPE_CURRENCY` (e.g. `usd`); it does **not** reuse `config('app.currency')`
  (which is `PKR` and stays for POS display).
- `.env` / `.env.example`: `STRIPE_KEY`, `STRIPE_SECRET`,
  `STRIPE_WEBHOOK_SECRET`, `STRIPE_CURRENCY=usd`.
- Module registration: add `Billing` to `modules_statuses.json` and
  `config/modules.php` (`enabled`).
- New exception `App\Exceptions\StripeServiceException` (carries `kind` +
  `isRetryable`, following the existing `App\Exceptions\*` pattern).

### Mirror tables (Stripe is the source of truth)

- **billing_customers** — `uid`, `company_id` (unique), `stripe_customer_id`
  (unique), `email`, timestamps.
- **billing_subscriptions** — `uid`, `company_id`, `stripe_subscription_id`
  (unique), `stripe_customer_id`, `stripe_price_id`, `plan_id`,
  `status` enum(incomplete, incomplete_expired, trialing, active, past_due,
  canceled, unpaid, paused), `interval`, `quantity`, `currency`,
  `current_period_start`, `current_period_end`, `cancel_at_period_end`,
  `canceled_at`, `ended_at`, `latest_invoice_id`, timestamps.
- **billing_invoices** — `uid`, `company_id`, `stripe_invoice_id` (unique),
  `stripe_subscription_id`, `stripe_customer_id`, `number`, `status`,
  `currency`, `subtotal`, `tax`, `total`, `amount_due`, `amount_paid`,
  `hosted_invoice_url`, `invoice_pdf`, `period_start`, `period_end`, `paid_at`,
  timestamps.
- **billing_payments** — `uid`, `company_id`, `stripe_payment_intent_id`
  (unique nullable), `stripe_charge_id` (unique nullable), `stripe_invoice_id`,
  `amount`, `currency`, `status`, `receipt_url`, `card_brand`, `card_last4`,
  `paid_at`, timestamps.
- **billing_coupons** — `uid`, `stripe_coupon_id` (unique),
  `stripe_promotion_code_id` (unique nullable), `name`, `percent_off`,
  `amount_off`, `currency`, `duration` enum(once, repeating, forever),
  `duration_in_months`, `max_redemptions`, `times_redeemed`, `expires_at`,
  `status` enum(active, archived), timestamps.
- **billing_redemptions** — `uid`, `coupon_id`, `company_id`,
  `subscription_id`, `stripe_discount_id` (unique), timestamps.
- **billing_webhook_events** — `stripe_event_id` (unique), `type`,
  `status` enum(received, processed, failed), `payload` (safe display fields
  only), `processed_at`, `failed_at`, `failure_message`, `retry_count`,
  timestamps.

### Services (`Modules/Billing/Services`)

- `StripeService` — the sole place the SDK client is constructed;
  `isConfigured()` guard; `request()` wraps SDK calls and maps errors to
  `StripeServiceException` (`unconfigured | authentication_failed |
  invalid_request | not_found | rate_limited | api_unavailable`) plus
  `isRetryable`; `balance()` wraps `balance.retrieve()` for the platform
  dashboard.
- `StripeSyncService` — `syncPlan(Plan)` create/update Product, create/update/
  archive Prices, persist Stripe ids.
- `StripeCustomerService`, `StripeProductService`, `StripePriceService`,
  `StripeCheckoutService`, `StripePortalService`, `StripeSubscriptionService`,
  `StripeInvoiceService`, `StripeCouponService`, `StripeWebhookService`.
- Business services: `BillingPlanService`, `BillingSubscriptionService`,
  `BillingInvoiceService`, `BillingCouponService`, `BillingOverviewService`,
  `PlanProvisioningService`, `PlanLimitService`, `SubscriptionGateService`.

**CLAUDE.md rule honored:** no Stripe call runs inside a DB transaction.

### Webhook contract

`POST billing/webhook`, registered **without** the `web` middleware group (no
CSRF/session). Steps:

1. Reject bodies > 1 MiB.
2. Verify `STRIPE_WEBHOOK_SECRET` via `\Stripe\Webhook::constructEvent(rawBody,
   sig, secret)`; invalid → 400.
3. Delegate to `StripeWebhookService`:
   - **Idempotent** — unique `billing_webhook_events.stripe_event_id`; a
     duplicate delivery is acknowledged and stops.
   - **Atomic** — event row + business sync + processed marker in one
     transaction; failure records `failed_at` / `failure_message` /
     `retry_count`.
   - **Prefetch before the transaction** — retrieve the referenced subscription
     (and charge for `payment_intent.succeeded`) outside the transaction with
     backoff `[0, 250, 750]ms`, so the transaction performs no network call.

Handled types: `checkout.session.completed`,
`customer.subscription.created/updated/deleted`,
`invoice.created/finalized/paid/payment_failed/updated/voided/marked_uncollectible`,
`payment_intent.succeeded/payment_failed/processing/canceled`,
`charge.succeeded/failed/refunded`, `payment_method.attached/updated/detached`;
anything else is acknowledged with no work.

### Checkout & portal (existing tenants)

- `POST billing/checkout` (tenant `company` role) → create a Checkout session.
- `POST billing/portal` → create a Customer Portal session.
- `POST billing/subscription/cancel|resume` (cancel at period end, then resume).

All company/Stripe ids are resolved **server-side** from
`auth()->user()->company_id`; never trusted from the client.

---

## Part 5 — Access gate

- `EnsureSubscriptionActive` middleware, alias `subscription.active`, **appended
  to the global `web` group** in `app/Http/Kernel.php` so it covers the app and
  all module routes.
- Allowlist (exempt): unauthenticated requests, `login`/`logout`,
  `clear.cache`, `pricing`, `signup.*`, all `billing.*`, password reset, the
  platform `admin` role, and users with a pending signup (forced to
  `/signup/complete`).
- `SubscriptionGateService::currentForCompany()`: entitled when the latest
  subscription status ∈ `{active, trialing}` and `current_period_end` is in the
  future; otherwise redirect to the gated page. A non-null
  `companies.suspended_at` (local platform suspend) also denies access,
  independent of Stripe status.
- **Land the gate last**, after billing works, to avoid locking everyone out.

---

## Part 6 — Reports (summary)

- **Platform** (`billing/overview`): see Part 7 for the full dashboard.
- **Tenant**: current plan + status + renewal date, invoices, payments (see
  Part 8).

---

## Part 7 — Platform (`admin`) reporting & controls

Charts use **ApexCharts**, already available via
`resources/views/layouts/script.blade.php` (`plugins.bundle.js` +
`widgets.bundle.js`) and used by the tenant dashboard
(`resources/views/dashboard/_js.blade.php`).

### 7.1 Platform dashboard (`HomeController` platform branch, enriched)

Existing cards (total/active/inactive companies, tenant users) plus:

- **Paid companies** — count of companies whose current subscription status ∈
  `{active, trialing}`.
- **Companies with due payments** — companies with a `past_due`/`unpaid`
  subscription **or** an overdue open invoice.
- **MRR estimate** — sum of active subscription prices normalized to monthly.
- **Total collected** — sum of paid local invoices.
- **Stripe balance** — live `balance.retrieve()` (available + pending) plus
  total collected.

Charts:

- Company growth over time.
- Subscription-status distribution (active/trialing/past_due/canceled/paused).
- Collected revenue over time.
- Plan distribution (Basic/Pro/Enterprise).

Tables: recent companies (plan + subscription status), recent
payments/invoices.

### 7.2 Companies list (extend `CompanyRepository` DataTable)

Add columns: **Plan**, **Subscription status**, **Renewal date**, **Usage
summary** (`used/limit`, e.g. `3/5 users`), Status, Actions. Row actions
(platform permission-gated): view detail, **cancel**, **pause**, **resume**,
**suspend/reactivate**, **change plan**. This replaces the currently broken
`manager.name` column (there is no `Company::manager` relation).

### 7.3 Company detail page (`billing/companies/{company}`, new)

- Billing summary: plan, price, interval, status, current period start/end,
  renewal date, `cancel_at_period_end`.
- **Allocation vs usage** table + bar chart for the six limit keys (Products,
  Users, Employees, Suppliers, Promotions, Purchase orders); "Unlimited" when
  the limit is `null` / `-1`. **Current snapshot only** (no historical
  snapshots — no `billing_usage_snapshots` table).
- Invoice/payment history and subscription timeline.

### 7.4 Subscription lifecycle actions (platform)

- **Cancel** — Stripe `subscriptions.cancel`.
- **Pause** — Stripe `pause_collection`.
- **Resume** — clear `pause_collection`; also clears a local suspension.
- **Suspend** — **local access block**: sets `companies.suspended_at` (new
  nullable column; the gate blocks all tenant users) while **keeping the Stripe
  subscription intact**; optional "also pause collection". Reversible via
  reactivate.
- **Change plan** — apply another plan: reconcile Stripe (update subscription
  item/price) and re-run provisioning (permissions + limits).

### 7.5 Payment reports

- **Paid companies** — active/trialing subscriptions.
- **Companies with due payments** — `past_due`/`unpaid` subscriptions plus
  overdue open invoices.
- **Stripe balance** — account balance (available + pending) + collected total.
- **Invoices** — all-company list with filters (status, company, date), hosted
  URL / PDF link.

---

## Part 8 — Tenant (`company`) surfaces

- **Current plan** — name, price, interval, status, renewal/current-period-end,
  `cancel_at_period_end`; upgrade/downgrade and manage card (Checkout /
  Portal).
- **Payment invoices** — list + view/download (`hosted_invoice_url`,
  `invoice_pdf`) and payment history.
- **Modules** — the rest of the app is gated by the plan (provisioned
  permissions + `EnsureSubscriptionActive`).

---

## Permissions, seeder & navigation

Extend `RolePermissionSeeder`:

- **Platform module `Billing`** (`is_platform = true`): `view billing plan`,
  `create billing plan`, `edit billing plan`, `delete billing plan`,
  `view subscription`, `manage subscription`, `view billing invoice`,
  `view coupon`, `create coupon`, `edit coupon`, `delete coupon`, `assign plan`,
  `suspend company`, `view company billing`, `view billing report`. Assigned to
  the renamed platform `admin` role.
- **Tenant module `Billing`** (`is_platform = false`): `manage company
  subscription`, `view company invoice`.

Navigation:

- Platform links (Overview, Plans, Subscriptions, Invoices, Coupons) added to
  the `SaaS` category in `AsideSettingService::$menuLinks` — automatically
  excluded from tenants via `getTenantMenuLinks()`.
- Tenant `Billing` category (My Subscription, Invoices) added to
  `getTenantMenuLinks()`, with an idempotent command/migration to append it to
  existing companies' `settings.asidebar`.

---

## Module layout — `Modules/Billing/`

```
Config/config.php
Database/Migrations/            (billing_plans, billing_prices,
                                 billing_plan_features, billing_plan_permissions,
                                 billing_plan_limits, billing_customers,
                                 billing_subscriptions, billing_invoices,
                                 billing_payments, billing_coupons,
                                 billing_redemptions, billing_webhook_events,
                                 billing_signups)
Http/Controllers/               Plan, Subscription, Invoice, Overview, Coupon,
                                Checkout, Portal, TenantSubscription, Signup,
                                Webhook, CompanyBillingDetail
Http/Requests/                  StorePlanRequest, UpdatePlanRequest,
                                StoreCouponRequest, SignupRequest,
                                CompleteSignupRequest
Http/Middleware/                EnsureSubscriptionActive, EnsureSignupComplete
Models/                         Plan, Price, PlanFeature, PlanPermission,
                                PlanLimit, Customer, Subscription, Invoice,
                                Payment, Coupon, Redemption, WebhookEvent,
                                Signup
Providers/                      BillingServiceProvider
Repositories/                   one per model (extend BaseRepository)
Resources/views/                plans/, subscriptions/, invoices/, coupons/,
                                overview/, tenant/, signup/, pricing/,
                                companies/show (allocation vs usage graphs)
routes/web.php                  (UI)
routes/webhook.php              (no web middleware)
Services/                       (listed above)
```

`BillingServiceProvider` loads views/migrations, `web.php` under `web`
middleware, and `webhook.php` with no `web` group.

---

## Phased task breakdown

> Each phase is implemented on its own branch (`phase/<n>-<slug>`) and merged
> into `main` when complete. Check the box when merged.

- [x] **Phase 1 — Role rename (C)** — code refs, seeders, data migration, login
  label; run tests.
- [x] **Phase 2 — Scaffold** — Stripe SDK, `config/billing.php`, env keys, module
  registration, `BillingServiceProvider`, `StripeService` + exception.
- [ ] **Phase 3 — Schema & models** — all migrations, `UIDModel`-based models,
  repositories.
- [ ] **Phase 4 — Plans (A)** — plan CRUD UI (details/prices/modules/permissions/
  limits) + `StripeSyncService`.
- [ ] **Phase 5 — Public pricing** — pricing page + plan feature/limit summary.
- [ ] **Phase 6 — Self-signup (B)** — signup request, pre-checkout account,
  `billing_signups`, checkout session, `EnsureSignupComplete`.
- [ ] **Phase 7 — Webhooks** — route (CSRF-exempt), signature verification,
  idempotent/atomic handler.
- [ ] **Phase 8 — Signup completion** — `PlanProvisioningService`, `company` role,
  permission copy, `DefaultService`, customer link, `created_by` migration.
- [ ] **Phase 9 — Portal & tenant billing** — portal session, checkout, cancel/
  resume, my-subscription/invoices pages.
- [ ] **Phase 10 — Invoices & payments** — webhook mirror + read UIs.
- [ ] **Phase 11 — Coupons** — admin CRUD + redemption recording + promotion codes
  in checkout.
- [ ] **Phase 12 — Limits** — `PlanLimitService` + `PlanLimitExceededException`
  wired into the six `store()` methods.
- [ ] **Phase 13 — Access gate** — middleware, allowlist, Kernel registration,
  gated page.
- [ ] **Phase 14 — Permissions & nav** — seeder additions, menu updates,
  tenant-asidebar backfill command.
- [ ] **Phase 15 — Platform reporting & controls (Part 7)** — enriched platform
  dashboard, companies-list columns, company detail page (allocation vs usage
  graphs), subscription lifecycle actions (cancel/pause/resume/suspend/change
  plan), payment reports + Stripe balance.
- [ ] **Phase 16 — Tenant billing surfaces (Part 8)** — current plan info,
  invoices, payments.
- [ ] **Phase 17 — Tests & docs** — see below.

---

## Testing (`vendor/bin/phpunit`)

`phpunit.xml` currently has sqlite commented out; enable an in-memory sqlite
connection or point at a test MySQL database. Cover:

- Role rename migration idempotency and `hasRole('admin')` /
  `hasRole('company')` behavior.
- Webhook: valid signature accepted, invalid rejected, duplicate
  `stripe_event_id` handled once, failure rolls back and records `failed_at`.
- Plan sync (Stripe client mocked): Product/Price create/update/archive and id
  persistence.
- Signup: pre-checkout user/signup creation → webhook advances status →
  completion provisions company, role, permissions, limits.
- `PlanLimitService`: blocks at limit for each of the six resources; unlimited
  when `null` / `-1`.
- Gate: entitled passes; `past_due`/`canceled`/missing redirects; allowlisted
  routes pass; platform `admin` exempt.
- Checkout controller scopes to session `company_id` and ignores client-supplied
  ids.

---

## Risks / notes

- **Global gate is invasive** — land it last; the allowlist must include
  signup/pricing/billing or users lock themselves out.
- **Existing tenants go unprovisioned** by decision — they hit the gate on next
  login. Provide a superadmin "apply plan" action so they can be assigned
  quickly.
- **`companies.created_by` is NOT NULL** — make nullable or assign a system
  creator for self-signup.
- **`companies.suspended_at`** — new nullable column backs the platform
  "suspend" action; the gate checks it independently of Stripe status.
- **Live plan limits** — editing a plan changes limits for current subscribers;
  documented behavior.
- **Stripe API pin** — align the SDK to `2025-08-27.basil`; no meter APIs are
  needed for fixed price.
- **Company deletion** — extend `CompanyService::destroy()` to cancel/clean the
  Stripe mapping.
- **`docs/saas_billing_system.dbml` is absent** — the schema sections here
  supersede it for our stack.

## Deliverable files (beyond `Modules/Billing/`)

- `config/billing.php`
- `.env`, `.env.example`
- `composer.json` / `composer.lock`
- `config/modules.php`, `modules_statuses.json`
- `app/Http/Kernel.php` (middleware), `app/Exceptions/StripeServiceException.php`,
  `app/Exceptions/PlanLimitExceededException.php`
- Migration adding `companies.suspended_at` (nullable timestamp)
- `app/Services/AsideSettingService.php`, `database/seeders/RolePermissionSeeder.php`,
  `database/seeders/CompanySeeder.php`
- `app/Services/CompanyService.php` (role default + cleanup hook)
- `app/Models/Company.php` (`subscription`, `suspend` helpers)
- `app/Http/Controllers/HomeController.php`, `routes/web.php`, and the tenant
  `hasRole('superadmin')` references listed in Part C
