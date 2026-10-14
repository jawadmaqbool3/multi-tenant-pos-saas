# POS — Multi-Tenant SaaS Point of Sale

A **multi-tenant SaaS Point-of-Sale (POS) platform** built as a **Laravel 8 modular monolith**.

Companies ("tenants") self-register, get provisioned with an owner user, a role/permission set and a
default chart of accounts, then subscribe to a fixed-price plan via **Stripe Checkout** before they can
use the application. Tenant access is hard-gated until a subscription is `active` / `trialing`.

- **Language / framework:** PHP 8.1 · Laravel 8.75
- **Modular architecture:** [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules) (`Modules/*`)
- **Database:** MySQL 8
- **Billing:** `stripe/stripe-php` ^22 (Checkout + fixed-price subscriptions)
- **UI:** Blade + Metronic 8 (Bootstrap 5) precompiled assets + Yajra DataTables
- **Repository:** `git@github.com:jawadmaqbool3/pos.git`

---

## Table of contents

1. [Purpose](#purpose)
2. [Feature overview](#feature-overview)
3. [Architecture](#architecture)
4. [Requirements](#requirements)
5. [Getting started](#getting-started)
6. [Environment variables](#environment-variables)
7. [Subscription plans & feature gating](#subscription-plans--feature-gating)
8. [Stripe configuration](#stripe-configuration)
9. [Stripe webhooks](#stripe-webhooks)
10. [Deployment guide](#deployment-guide)
11. [Testing](#testing)
12. [Documentation](#documentation)

---

## Purpose

The application serves two audiences:

| Audience | Who | Can do |
| --- | --- | --- |
| **Platform owner** | Users with `company_id = null` and the `admin` role | Manage tenants (`companies`), the plan catalog, coupons, subscriptions, invoices, platform-wide settings |
| **Tenant** | Users belonging to a `Company` | Use the POS modules (orders, inventory, suppliers, employees, accounting) according to the permissions and plan they paid for |

Core business goals implemented in this repo:

1. **Self-service signup** — email + password, automatic company provisioning
   (`app/Http/Controllers/SignupController.php` → `app/Services/CompanyService.php`).
2. **Paywall** — tenants without an active subscription are redirected to the pricing page on every
   request (`app/Http/Middleware/EnsureSubscribed.php`, registered in the `web` group).
3. **Plan-gated features & limits** — a plan bundles permission modules (features) and numeric resource
   limits that are enforced at runtime, not just displayed.
4. **Stripe-driven subscriptions** — Stripe Checkout, a Stripe Customer per company, and an idempotent
   webhook endpoint that keeps local subscription state in sync.

---

## Feature overview

### Core application (`app/`)

- Tenant self-signup, company provisioning and per-tenant settings (`SignupService`, `CompanyService`).
- Dashboards: platform/tenant (`HomeController`, `dashboard.admin` vs `dashboard.dashboard`).
- Global settings: general, public dashboard widgets, sidebar menu (`SettingController` and friends).
- Custom exception types for domain rules (`PlanLimitExceededException`, `InsufficientStockException`,
  `StripeServiceException`).
- UI components/modals (`app/View/Components/*`) and DataTables builders.

### Modules (`Modules/*`, status in `modules_statuses.json`)

| Module | Responsibility |
| --- | --- |
| **Billing** | Plans, prices, plan features/limits, subscriptions, Stripe customers, invoices/payments, coupons & redemptions, signups, webhook event log, pricing/checkout screens |
| **Order** | Sales and orders, mark paid, cancel, refund, receipts and sales reports |
| **Inventory** | Categories, products, stock movements/history, promotions, barcode & stock printing |
| **Supplier** | Suppliers, purchase orders, supplier payments and ledger |
| **Employee** | Authentication (login/logout), users, employees, attendance history reports |
| **RBAC** | Roles, permissions, permission modules, role↔permission and user↔role pivots |
| **ChartOfAccounts** | 4-level chart of accounts, vouchers and voucher types, account settings |

---

## Architecture

**Pattern:** modular monolith. Each module is a self-contained vertical slice with its own
`Config`, `Database/Migrations`, `Http/{Controllers,Requests}`, `Models`, `Providers`, `Repositories`,
`Resources/views`, `routes` and `Services` folders.

**Layering (mandatory, see `docs/architecture-constraints.md`):**

```
Route → Controller → Service → Repository → Model
```

Controllers stay thin, wrap writes in `DB::beginTransaction`; services compose repositories and Stripe
SDK calls; repositories extend `App\Repositories\BaseRepository`; models extend `App\Models\UIDModel`
(exposes the `uid` route key).

**Multi-tenancy** is row-scoped, not database-scoped:

- `companies` is the tenant root; `users.company_id` links a user to a tenant.
- `Company::filter()` / `User::filter()` query scopes return everything for the platform `admin` role and
  otherwise scope to the caller's `company_id`.

**RBAC** is custom (no Spatie):

- `Modules/RBAC` + `app/Http/Traits/RoleTrait.php` (`hasRole()`, `hasPermission()`).
- Middleware: `role:`, `permission:`, `subscribed`, `plan.feature:` (aliases in `app/Http/Kernel.php`).
- Seeded roles: `admin` (platform), `company` (tenant owner), `manager`, `employee`, `customer`
  (`database/seeders/RolePermissionSeeder.php`).

**Routing:** core routes in `routes/web.php`; each module loads its own `routes/web.php` under the `web`
middleware group from its service provider.

---

## Requirements

| Tool | Version |
| --- | --- |
| PHP | 8.1 (platform pinned to `8.1.34` in `composer.json`) |
| Extensions | `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `xml`, `curl` |
| Composer | 2.x |
| MySQL | 8.x (or MariaDB 10.4+) |
| Docker | Docker 24+ / Docker Compose v2 (recommended) |
| Node.js | Only if you intend to rebuild assets (not required — see below) |

> **Frontend assets are precompiled.** The Metronic/Bootstrap bundle lives in `public/assets/` and is
> referenced directly (`resources/views/layouts/style.blade.php`, `script.blade.php`). The Mix scripts in
> `package.json` are legacy and there is no `webpack.mix.js`, so `npm run production` is a no-op in the
> Docker build (guarded with `|| true`).

---

## Getting started

### Option A — Docker (recommended)

The app runs as a container joined to an external MySQL network.

```bash
# 1. Create the shared network and a MySQL container (once)
docker network create mysql-stack-network

docker run -d --name mysql \
  --network mysql-stack-network \
  -e MYSQL_ROOT_PASSWORD=secret \
  -e MYSQL_DATABASE=pos \
  -p 3306:3306 \
  mysql:8.1

# 2. Configure the app
cp .env.example .env
#    edit .env → DB_HOST=mysql, DB_DATABASE=pos, DB_USERNAME=root, DB_PASSWORD=secret
#    set APP_URL to the URL you actually browse (http://localhost:8000 for local)

# 3. Build & start
docker compose build
docker compose up -d

# 4. Initialize the application
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan optimize:clear
```

The app is now on <http://localhost:8000>.

`docker-compose.yml` maps **`8000:80`**, reads configuration via `env_file: .env`, and only mounts
`./storage` (code is baked into the image — after editing PHP files, rebuild or run
`docker compose up -d --build`).

### Option B — Local PHP (no Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate

# point .env at a local MySQL instance
php artisan migrate --seed
php artisan serve --port 8000
```

### Default seeded credentials

| Account | Email | Password | Role |
| --- | --- | --- | --- |
| Platform owner | `superadmin@gmail.com` | `12345678` | `admin` |
| Tenant admin | `admin@gmail.com` | `12345678` | `company` |

Seeded by `database/seeders/UserTableSeeder.php`. **Change or remove these before going to production.**

`DatabaseSeeder` runs: `UserTableSeeder`, `SettingSeeder`, `RolePermissionSeeder`, `CompanySeeder`,
`AccountsSeeder`, `VoucherTypeSeeder`, `PlanSeeder`.

---

## Environment variables

Key variables used by this application (full list in `.env.example`):

| Variable | Purpose |
| --- | --- |
| `APP_NAME` / `APP_ENV` / `APP_KEY` / `APP_DEBUG` | Standard Laravel app settings |
| `APP_URL` | Absolute URLs generated by `asset()` / `url()`. Must match the public scheme+host |
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | MySQL connection (`DB_HOST=mysql` inside Compose) |
| `SESSION_DRIVER` / `CACHE_DRIVER` / `QUEUE_CONNECTION` | Defaults are `file` / `file` / `sync` (no worker required) |
| `MAIL_*` | Outgoing mail |
| `STRIPE_KEY` | Stripe **publishable** key |
| `STRIPE_SECRET` | Stripe **secret** key (server-side API calls) |
| `STRIPE_WEBHOOK_SECRET` | Signing secret of the `/stripe/webhook` endpoint |
| `STRIPE_CURRENCY` | Billing currency, e.g. `usd` (separate from POS display currency) |

> **HTTPS behind a proxy (ngrok, load balancer, Nginx):** the app must trust the
> `X-Forwarded-Proto` header, otherwise `asset()`/`url()` emit `http://` links on an `https://` page and
> browsers block the CSS/JS as mixed content. `App\Http\Middleware\TrustProxies` is configured to trust
> all proxies (`protected $proxies = '*';`). Keep that in place for any TLS-terminating proxy.

---

## Subscription plans & feature gating

The catalog is seeded by `database/seeders/PlanSeeder.php`; the source of truth is
`docs/subscription-plans.md`.

| Lever | Basic | Pro | Enterprise |
| --- | --- | --- | --- |
| Price (monthly) | $19 | $49 | $99 |
| Employees (max) | 5 | 25 | Unlimited |
| Roles (max) | 2 | 5 | Unlimited |
| Chart of Accounts | — | ✅ | ✅ |
| Inventory | ✅ | ✅ | ✅ |
| Promotions | — | ✅ | ✅ |

**How gating works**

- **Paywall:** `EnsureSubscribed` redirects tenants with no active subscription to `billing.pricing`.
  An active subscription is `status in (active, trialing)` with `current_period_end` in the future
  (`App\Models\Company::hasActiveSubscription()`).
- **Feature access:** `plan.feature:<feature>` middleware (`App\Http\Middleware\EnsurePlanFeature`) →
  `PlanFeatureService::companyAllowsFeature()`. Applied to Chart of Accounts and Inventory/Promotions routes.
- **Resource limits:** `PlanLimitService::ensureWithinLimit()` throws
  `PlanLimitExceededException` (HTTP 403, generic message) when a tenant exceeds employees/roles/etc.

**Billing surfaces**

| Route | Name | Purpose |
| --- | --- | --- |
| `GET /subscribe` | `billing.subscribe` | Paywall page for logged-in unsubscribed tenants |
| `GET /pricing` | `billing.pricing` | Public pricing/plans page |
| `POST /subscribe/checkout` | `billing.subscribe.checkout` | Create a Stripe Checkout Session, returns JSON `url` |
| `GET /subscribe/success` | `billing.subscribe.success` | Post-checkout redirect; activates as a webhook fallback |
| `GET /subscribe/cancel` | `billing.subscribe.cancel` | Cancelled-checkout redirect |
| `GET /plans` `GET /coupons` `GET /invoices` | `billing.*` | Platform catalog/invoice management (permission-gated) |
| `POST /stripe/webhook` | `billing.webhook` | Stripe webhook receiver (public, CSRF-exempt) |

---

## Stripe configuration

All Stripe settings live in `config/billing.php` and are read from the environment:

```php
'key'            => env('STRIPE_KEY'),             // publishable key
'secret'         => env('STRIPE_SECRET'),          // secret key
'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),  // /stripe/webhook signing secret
'currency'       => env('STRIPE_CURRENCY', 'usd'),
'api_version'    => '2025-08-27.basil',            // pinned Stripe API version
```

Add to `.env` (values from your Stripe Dashboard → **Developers → API keys**):

```dotenv
STRIPE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
STRIPE_CURRENCY=usd
```

**Behavior when unconfigured:** `StripeService::isConfigured()` returns `false`, the pricing page renders in
a "not configured" state, and `SubscribeController::checkout` responds with
`Billing is not configured yet. Please contact support.` — no crash.

**Customer & catalog mapping**

- Each company gets one Stripe Customer, stored in `billing_customers` (`stripe_customer_id`),
  created lazily by `SubscriptionService::ensureCustomer()`.
- Plans/prices are local rows (`billing_plans`, `billing_prices`) holding the Stripe IDs
  (`stripe_product_id`, `stripe_price_id`). Before Checkout, `StripeSyncService::syncPlan()` pushes the
  plan to Stripe as a Product with its Prices and persists the returned IDs; Checkout then uses the
  synced `stripe_price_id` (it fails fast if the plan could not be synced).
- Checkout is created with the company's Stripe customer, `success_url = /subscribe/success?session_id={CHECKOUT_SESSION_ID}`
  and `cancel_url = /subscribe/cancel`, plus `company_id`/`plan_id`/`price_id` metadata.
- Coupons (`billing_coupons`) map to Stripe promotion codes (`stripe_promotion_code_id`, synced by
  `CouponService`); a code entered on the pricing page is applied as a Checkout discount, otherwise
  `allow_promotion_codes` lets the customer enter one on Stripe's page. Redemptions are recorded in
  `billing_redemptions`.

---

## Stripe webhooks

**Endpoint:** `POST /stripe/webhook` (`Modules/Billing/routes/web.php` → `StripeWebhookController@handle`).

Implementation details:

- The route is **outside** the `auth` group and is **CSRF-exempt**
  (`app/Http/Middleware/VerifyCsrfToken.php` → `$except = ['stripe/webhook']`).
- The request body is verified with the Stripe SDK
  `\Stripe\Webhook::constructEvent($payload, $request->header('Stripe-Signature'), config('billing.webhook_secret'))`.
  Missing secret → `400`; invalid signature → `400`.
- **Idempotency:** every delivered event is persisted in `billing_webhook_events` keyed by the unique
  `stripe_event_id`. Events whose status is already `processed` are acknowledged with `200` and skipped.
  Statuses: `received → processing → processed` (or `failed`).
- A handler exception marks the row `failed` (with `failed_at`) and returns **`500`**, so Stripe retries
  with backoff.

### Events handled

| Stripe event | Handler | Effect |
| --- | --- | --- |
| `checkout.session.completed` | `onCheckoutCompleted()` | Reads the Checkout Session's `subscription`, calls `SubscriptionService::syncByStripeId()` |
| `customer.subscription.created` | `onSubscriptionChanged()` | Upserts the local `billing_subscriptions` row from Stripe |
| `customer.subscription.updated` | `onSubscriptionChanged()` | Syncs status, current period, plan/price, cancel-at-period-end |
| `customer.subscription.deleted` | `onSubscriptionChanged()` | Syncs the cancelled/ended subscription, which revokes the paywall bypass |

Unlisted event types are still **recorded** in `billing_webhook_events` and marked `processed`, but have no
handler. The same sync also runs defensively on the `GET /subscribe/success` redirect so a slow webhook can
never lock a paying tenant out.

### Create the webhook endpoint in Stripe

1. Stripe Dashboard → **Developers → Webhooks → Add endpoint**.
2. **Endpoint URL:** `https://<your-domain>/stripe/webhook`
   (during local development use the Stripe CLI or ngrok, see below).
3. **Events to send:** select exactly these four —
   - `checkout.session.completed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
4. Copy the endpoint's **Signing secret** (`whsec_…`) into `STRIPE_WEBHOOK_SECRET`.
5. After changing `.env`, clear the config cache: `php artisan config:clear` (or `optimize:clear`).

> Stripe delivers the raw JSON body and the `Stripe-Signature` header; do not put the endpoint behind
> middleware that rewrites or compresses the body, and exclude it from generic request logging that
> mutates content.

### Local / staging development with webhooks

Since Stripe cannot reach `localhost`, forward events:

```bash
# Option 1 — Stripe CLI (recommended)
stripe login
stripe listen --forward-to localhost:8000/stripe/webhook
#   prints: Ready! Your webhook signing secret is whsec_xxx
#   put whsec_xxx in STRIPE_WEBHOOK_SECRET, then trigger traffic:
stripe trigger checkout.session.completed
stripe trigger customer.subscription.updated

# Option 2 — ngrok (needed for redirect-based Checkout to be reachable)
ngrok http 8000
#   set APP_URL=https://<subdomain>.ngrok-free.app  (https!)
#   register https://<subdomain>.ngrok-free.app/stripe/webhook in the Stripe Dashboard
#   php artisan optimize:clear
```

Test card: `4242 4242 4242 4242`, any future expiry, any CVC.

**Verifying deliveries:** inspect `billing_webhook_events` (`type`, `status`, `failed_at`) or the Stripe
Dashboard → **Webhooks → your endpoint → Attempts**. A `failed` row means signature verification passed but
the handler threw — check `storage/logs/laravel.log`.

---

## Deployment guide

### 1. Build the image

```bash
git clone git@github.com:jawadmaqbool3/pos.git && cd pos
cp .env.example .env
# edit .env (production values — see checklist below)
docker compose build
```

The `Dockerfile` uses `php:8.1-apache`, enables `mod_rewrite`, installs the required PHP extensions,
runs `composer install --no-dev --optimize-autoloader`, and sets the Apache document root to
`/var/www/html/public`. Frontend assets are already committed under `public/assets/`.

### 2. Production environment checklist

```dotenv
APP_NAME="POS"
APP_ENV=production
APP_KEY=            # generated in step 4
APP_DEBUG=false
APP_URL=https://pos.example.com

DB_CONNECTION=mysql
DB_HOST=mysql        # or your managed MySQL host
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=pos
DB_PASSWORD=<strong-password>

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...   # signing secret of https://pos.example.com/stripe/webhook
STRIPE_CURRENCY=usd
```

- `APP_DEBUG=false` and `APP_ENV=production` are mandatory — stack traces would leak secrets.
- Keep `TrustProxies` trusting proxies (`'*'`) when TLS terminates upstream so HTTPS URLs are generated.
- **Never commit `.env`** (it is git-ignored) and rotate any keys that were ever exposed.

### 3. Start the stack

`docker-compose.yml` joins the **external** network `mysql-stack-network`, so the MySQL container must exist
on it first:

```bash
docker network create mysql-stack-network   # if it does not exist yet

docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force   # first deploy only; creates plans, roles, accounts
```

`--force` is required because `APP_ENV=production`.

### 4. Optimize caches

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Re-run these after every deploy that changes config, routes or Blade views. To invalidate everything
quickly there is an authenticated-independent helper route `GET /clear-cache` (calls `optimize:clear`) —
consider restricting or removing it in production.

### 5. Permissions & storage

Uploads and generated files are written to `storage/`, which is the only host-mounted volume
(`./storage:/var/www/html/storage`). Ensure it is writable by `www-data`:

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

Back up `storage/app` (uploads, logos, document images) together with the database.

### 6. TLS / reverse proxy

Terminate TLS at a reverse proxy (Nginx, Caddy, a cloud load balancer) or put a tunnel (ngrok) in front,
forwarding to port **8000** (or 80 inside the network). Requirements:

- Forward `X-Forwarded-Proto: https` and `Host`.
- Set `APP_URL` to the public `https://` URL — asset URLs are built from it once proxies are trusted.
- Point the Stripe webhook endpoint at `https://<public-domain>/stripe/webhook`.

### 7. Post-deploy verification

```bash
# app boots
curl -I https://pos.example.com/login               # 200
# assets resolve over https and are served as text/css
curl -I https://pos.example.com/assets/css/style.bundle.css
# plan catalog seeded
docker compose exec app php artisan tinker --execute="echo \Modules\Billing\Models\Plan::count();"
```

Then complete a test checkout with a test price and confirm the row in `billing_subscriptions` plus a
`processed` row in `billing_webhook_events`.

### 8. Operations notes

- **Queue:** `QUEUE_CONNECTION=sync` — no worker is required. If you switch to `database`/`redis`, run
  `php artisan queue:work` (e.g. as a Supervisor/systemd service in a separate container).
- **Scheduler:** `app/Console/Kernel.php` currently schedules nothing; no cron entry is required. If you add
  tasks, run Laravel's scheduler every minute.
- **Migrations:** always run with `--force` in production; the app must be in maintenance mode
  (`php artisan down`) during schema changes if you want zero failed requests.
- **Logs:** `storage/logs/laravel.log` (channel `stack`). Ship or rotate it in production.
- **Backups:** MySQL dump + `storage/app`.

---

## Testing

```bash
# PHPUnit (feature + unit suites)
php artisan test
# or
./vendor/bin/phpunit
```

The test suite is currently the Laravel skeleton (`tests/Feature/ExampleTest.php`,
`tests/Unit/ExampleTest.php`); `phpunit.xml` defines `testing` DB/environment variables. Add feature tests per
module as the codebase grows.

Static checks: run `php -l` on changed files; the project has no Pint/PHPStan configuration.

---

## Documentation

| Document | Contents |
| --- | --- |
| `docs/structure.md` | Full directory/module reference, auth and RBAC walkthrough |
| `docs/architecture-constraints.md` | Mandatory layer boundaries (Route → Controller → Service → Repository → Model) |
| `docs/subscription-plans.md` | The three sellable packages and the five enforced levers |
| `docs/saas-billing-implementation.md` | Billing implementation plan: plans, signup, Stripe subscriptions, webhook contract |
| `docs/saas-billing-system.md` | Stripe reference design (idempotency, source-of-truth rules) |
| `docs/application-ui-improvement.md` / `-02.md` | UI re-skin roadmaps |

---

## License

Proprietary — © Jawad Maqbool. The underlying Laravel framework is licensed under the MIT license.
