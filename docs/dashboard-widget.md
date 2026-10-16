# Dashboard Widgets — Design & Implementation Plan

**Status:** Proposal (not yet implemented)
**Author:** Engineering
**Related docs:** [`architecture-constraints.md`](./architecture-constraints.md), [`structure.md`](./structure.md), [`coa.md`](./coa.md), [`saas-billing-system.md`](./saas-billing-system.md)

---

## 1. Problem

Today the dashboard is a single, hardcoded page:

- `app/Http/Controllers/HomeController.php` renders `resources/views/dashboard/dashboard.blade.php`
  (tenant users) or `dashboard.admin` (platform users).
- All widget data is produced by one giant method, `app/Services/HomeService::getCharts()`,
  and returned as a flat associative array from `GET /?charts=true&ajax=true`.
- The Blade view + `resources/views/dashboard/_js.blade.php` hardcode every card, chart and
  table, each wrapped in a `@can('dashboard ...')` check.

Consequences:

1. **No meaningful dashboards per role.** The owner role (`company`) holds all 88 tenant
   permissions — including every `dashboard *` permission — while `hr`, `employee`,
   `accountant`, `sales person` and `stock manager` hold none. They all land on an empty or
   irrelevant dashboard.
2. **Not extensible.** Every new module that wants a widget must edit `HomeService`,
   `HomeController`, `dashboard.blade.php` *and* `_js.blade.php`. There is no ownership by
   module.
3. **No payroll / attendance / supplier widgets** despite those modules existing.
4. **Widget permissions are not role-friendly.** There is no convention for "this role should
   see this widget", and no way for an individual company to curate its dashboard.

### Goal

Introduce a **modular, permission-driven dashboard widget system** where:

- Every module can contribute **cards, charts and tables** without touching core dashboard files.
- Each widget is **guarded by its own permission**, so roles can be granted/denied widgets
  individually (`hr` sees payroll + attendance widgets, `employee` sees only their own, etc.).
- Widgets respect **plan-feature gating** just like sidebar links do.
- The dashboard is composed **dynamically** from the widgets the current user is allowed to see.

---

## 2. Current Building Blocks (what we reuse)

| Concern | Existing mechanism | Reuse for widgets |
|---|---|---|
| Permission check | `User::hasPermission($name)` / `@can(...)` (`RoleTrait`) | Each widget declares a permission name. |
| Permission storage | Global `permissions` (`company_id = null`), grouped by `permission_modules` | Add widget permissions to the `Dashboard` module (and module-specific groups where sensible). |
| Role defaults | `Modules/RBAC/Support/DefaultRoles::roles()` | Map new widget permissions onto `hr`, `employee`, `accountant`, `sales person`, `stock manager`. |
| Role provisioning | `Modules/RBAC/Services/RoleProvisioningService` | Used by seeders + company creation; extend mappings for new companies. |
| Plan gating | `Modules/Billing/Services/PlanFeatureService` (`isModuleGated`, `companyAllowsModule`) | Gate widgets whose module is plan-restricted (Payroll, Inventory, Chart of Accounts, Promotions). |
| Own-only scoping | `AttendanceHistoryController` / payroll `mine` pattern | Employee-facing widgets show only the acting user's data. |
| AJAX contract | `{success, message, redirect, table_reload, url}` (`layouts/script.blade.php`) | Widgets use the same contract. |
| Charts | ApexCharts (`average_sales_chart`, `profit_chart` in `_js.blade.php`) | Widget JS renderers reuse `UiTheme.palette()` + `convertToAbbreviation()`. |
| Dashboard shell | `resources/views/dashboard/dashboard.blade.php` | Rebuilt to iterate widgets from a grid. |
| Per-company config precedent | `settings.asidebar` JSON + `AsideSettingService` + `AsideSettingController` | Optional phase for per-company dashboard curation (`settings.dashboard`). |

### Known latent bug to fix as a prerequisite

`app/View/Components/AsideMenuLinks.php::moduleForPermission()` still queries
`Permission::where('company_id', auth()->user()->company_id)`, but permissions are now **global**
(`company_id = null`) after the RBAC refactor. The result is always empty, so
`moduleAllowedByPlan()` never gates anything on the sidebar. The widget system must not copy this
mistake; fix it so sidebar + widgets share one correct permission→module lookup. See
**§9.4**.

---

## 3. Design Overview

### 3.1 Pattern: Widget Registry + Widget Classes

Each widget is a small class implementing a `Widget` contract. Widgets register themselves into a
singleton `DashboardRegistry`, and a `DashboardService` resolves, filters and hydrates them for the
current user.

```
Route  GET  /dashboard                → HomeController@index     (shell)
Route  GET  /dashboard/widgets        → HomeController@widgets   (batched JSON payload)
Route  GET  /dashboard/widget/{key}   → HomeController@widget    (single widget, lazy/refresh)

HomeController
  └─ DashboardService            (resolves registry, filters by permission + plan, hydrates data)
       ├─ DashboardRegistry       (singleton; widgets registered by module ServiceProviders)
       ├─ PlanFeatureService      (plan gating)
       └─ Widget::data()  → module Service → Repository → Model
```

Why classes + registry over a config array:

- Each module **owns** its widgets (`Modules/Payroll/Dashboard/Widgets/...`).
- Widget metadata (permission, module, size, order) is colocated and type-safe.
- Data retrieval delegates to the module's existing **Service/Repository** (satisfies
  `architecture-constraints.md`: no querying in the widget or controller).
- Adding a widget is a 2-file change in one module (widget class + blade partial) plus a one-line
  registration.

### 3.2 Widget contract

```php
namespace App\Dashboard;

interface Widget
{
    public function key(): string;        // globally unique, dot-namespaced: 'payroll.pending'
    public function title(): string;      // human title, e.g. 'Pending Payroll'
    public function type(): string;       // 'card' | 'chart' | 'table'
    public function permission(): string; // RBAC permission name, e.g. 'dashboard payroll pending'
    public function module(): string;     // permission-module name for plan gating, e.g. 'Payroll'
    public function scope(): string;      // 'company' | 'own' | 'platform'
    public function size(): string;       // grid hint: 'col-3' | 'col-6' | 'col-12' (Bootstrap cols)
    public function order(): int;         // sort order within the dashboard
    public function data(): array;        // JSON payload (delegates to a Service)
    public function view(): string;       // blade partial that renders the container
}
```

Provide an `AbstractWidget` with sensible defaults so concrete widgets stay tiny.

> **`scope`** matters for authorization: `own` widgets force `auth()->user()->id`; `company`
> widgets are tenant-wide; `platform` widgets are for the SaaS owner dashboard only.

### 3.3 Registry

```php
namespace App\Dashboard;

class DashboardRegistry
{
    /** @var array<string, class-string<Widget>> */
    protected array $widgets = [];

    public function register(string $widgetClass): void { /* key => class, dedupe by key */ }
    public function all(): array { /* instantiate via container, keyed by Widget::key() */ }
}
```

- Registered as a **singleton** in `app/Providers/AppServiceProvider.php` (or a new
  `DashboardServiceProvider`).
- Each module's ServiceProvider registers its widgets in `boot()`:

```php
// Modules/Payroll/Providers/PayrollServiceProvider.php
public function boot(): void
{
    $registry = $this->app->make(\App\Dashboard\DashboardRegistry::class);
    $registry->register(Widgets\PendingPayrollCard::class);
    $registry->register(Widgets\PayrollDueCard::class);
    $registry->register(Widgets\PayrollCostChart::class);
    $registry->register(Widgets\RecentPayrollTable::class);
}
```

Because the registry is resolved lazily, provider ordering does not matter.

### 3.4 Service

```php
class DashboardService
{
    public function __construct(
        DashboardRegistry $registry,
        PlanFeatureService $planFeatures,
        // + repositories used by core widgets, via widget Services
    ) {}

    /** Widgets the current user may see, in order. */
    public function widgetsFor($user): array
    {
        return collect($this->registry->all())
            ->filter(fn ($w) => $w->scope() !== 'platform' || $user->isPlatformAdmin())
            ->filter(fn ($w) => $w->scope() === 'platform' || !$user->isPlatformAdmin())
            ->filter(fn ($w) => $user->hasPermission($w->permission()))
            ->filter(fn ($w) => $this->allowedByPlan($w))
            ->sortBy(fn ($w) => $w->order())
            ->values()
            ->all();
    }

    /** key => payload for a batch of widgets. */
    public function payload(array $widgets): array
    {
        $out = [];
        foreach ($widgets as $w) {
            $out[$w->key()] = $w->data();
        }
        return $out;
    }

    private function allowedByPlan(Widget $w): bool
    {
        $modules = $this->planFeatures->allowedModules(auth()->user()->company ?? null);
        if ($modules === null) return true;
        if (!$this->planFeatures->isModuleGated($w->module())) return true;
        return in_array($w->module(), $modules, true);
    }
}
```

---

## 4. File Layout

```
app/Dashboard/
├── Widget.php                       # contract
├── AbstractWidget.php               # base class (defaults)
├── DashboardRegistry.php            # singleton registry
├── DashboardService.php             # resolve + filter + hydrate
└── Widgets/                         # core (non-module) widgets
    ├── PlatformCompaniesCard.php
    ├── PlatformUsersCard.php
    └── PlatformRecentCompaniesTable.php

Modules/<Module>/Dashboard/Widgets/  # module-owned widgets
Modules/Payroll/Dashboard/Widgets/PendingPayrollCard.php
Modules/Employee/Dashboard/Widgets/AttendanceMonthlyChart.php
...

resources/views/dashboard/
├── index.blade.php                  # shell (was dashboard.blade.php); loops widgets into a grid
├── admin.blade.php                  # platform shell (reuses same grid mechanism)
├── _js.blade.php                    # dispatcher: payload key → widget renderer
└── widgets/
    ├── card.blade.php               # generic card partial
    ├── chart.blade.php              # generic chart container partial
    └── table.blade.php              # generic table container partial

Modules/<Module>/Resources/views/dashboard/
└── widgets/<widget>.blade.php       # module-specific partial (container + @push('js'))
```

**Generic partials.** Most widgets need only a container; a generic `widgets/card.blade.php`,
`widgets/chart.blade.php`, `widgets/table.blade.php` can render the standard markup using the
widget's metadata. Widgets with bespoke markup point `view()` at their own partial. This keeps the
common case tiny while allowing custom layouts.

---

## 5. Rendering & Endpoints

### 5.1 Shell (`HomeController@index`)

- Platform admin → `dashboard.admin`; tenant user → `dashboard.index`.
- Shell iterates `DashboardService::widgetsFor($user)`, wraps each in a Bootstrap grid column
  (`$widget->size()`), and renders `$widget->view()` with `['widget' => $widget]`.
- Shell renders **empty containers** with `data-widget-key="{{ $widget->key() }}"`; data is
  hydrated client-side so the page paints fast and partial failures don't block the page.

### 5.2 Batched payload (`HomeController@widgets`)

```
GET /dashboard/widgets   (ajax)
→ { success: true, widgets: { "payroll.pending": {...}, "attendance.monthly": {...} } }
```

- Only widgets the user is permitted to see are included (server enforces authorization).
- One request for the whole dashboard (mirrors today's `getCharts()`), keeps it fast.
- Optional `?refresh={key}` for a single-widget refresh (`GET /dashboard/widget/{key}`).

### 5.3 Client dispatcher (`_js.blade.php`)

- A registry of renderers keyed by widget key:

```js
const WidgetRenderers = {
  'payroll.pending': function ($el, data) { /* set text */ },
  'attendance.monthly': function ($el, data) { new ApexCharts(...).render(); },
  'recent.orders': function ($el, data) { /* fill table */ },
};

function getWidgets() {
  $.ajax({ url: dashboardUrl + '?ajax=true', ... }) // or /dashboard/widgets
    .done(function (res) {
      if (!res.success) return;
      Object.entries(res.widgets).forEach(([key, data]) => {
        const $el = document.querySelector(`[data-widget-key="${key}"]`);
        if ($el && WidgetRenderers[key]) WidgetRenderers[key]($el, data);
      });
    });
}
```

- Each module's widget partial may `@push('js')` to register its renderer, keeping JS next to the
  widget. (Alternative: one `_js.php`-style bundle per module.) Either way the dispatcher is
  generic and the shell never hardcodes widget names.
- Reuse existing helpers: `UiTheme.palette()`, `convertToAbbreviation()`,
  `directionPostive/directionNegetive`, `statusBadge` classes.

### 5.4 Tables

For small dashboard tables, embed rendered HTML in the payload (the existing
`_ajax_attendance.blade.php` + `response.view` pattern) and inject it, rather than spinning up a
DataTables server-side request per widget. Larger tables (e.g. full stock report) may keep their
existing DataTables endpoint and have the widget simply link to it.

### 5.5 External-API widgets (Stripe)

Some billing widgets (notably the platform **Stripe balance** and any live-charge figures) call a
remote API. Rules:

- **Only** call Stripe from a widget's `data()` via `StripeService` (never in the shell, never in a
  Blade view). `StripeService::balance()` already exists and returns
  `['available' => [...], 'pending' => [...]]`.
- **Cache** responses (e.g. `Cache::remember('billing.stripe_balance', 300, fn () => ...)`) so
  repeated dashboard loads don't hammer Stripe. Expose a manual "refresh" that busts the cache
  (see §9).
- **Degrade gracefully.** If `StripeService::isConfigured()` is false, or a
  `StripeServiceException` is thrown, the widget must return an "unavailable / not configured"
  payload — it must never throw out of the batched endpoint or block the rest of the dashboard.
- **Invert the mirror where possible.** Where a local mirror already exists (e.g.
  `billing_invoices`, `billing_payments`, `billing_subscriptions`), prefer the local table for
  counts/sums (fast, no API) and reserve live API calls for truly-Stripe-only values (balance).
- The endpoint stays behind the platform context check (§8) — a tenant can never call platform
  widget payloads.

---

## 6. Widget Catalog

Legend: **key** · type · permission · module (plan gate) · scope · roles.

### 6.1 Attendance (`Employees` module, not plan-gated)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `attendance.summary` | card (present/absent today) | `dashboard attendance summary` | company | hr |
| `attendance.monthly` | chart (per-day present count, current month) | `dashboard attendance monthly` | company | hr |
| `attendance.absent_today` | table (who is absent today) | `dashboard attendance absent today` | company | hr |
| `attendance.mine` | chart (my clock-in/out this month) | `dashboard my attendance` | own | employee, hr, accountant, sales, stock |

### 6.2 Payroll (`Payroll` module, plan-gated)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `payroll.pending` | card (draft runs count) | `dashboard payroll pending` | company | hr, accountant |
| `payroll.due` | card (draft run for current/previous month) | `dashboard payroll due` | company | hr, accountant |
| `payroll.paid_month` | card (amount paid this month) | `dashboard payroll paid month` | company | hr, accountant |
| `payroll.cost_chart` | chart (net payroll cost, last 6 months) | `dashboard payroll cost` | company | hr, accountant |
| `payroll.recent` | table (recent payroll runs + status) | `dashboard payroll recent` | company | hr |
| `payroll.my_payslips` | table (my latest payslips) | `dashboard my payslips` | own | employee, hr, accountant, sales, stock |

### 6.3 Sales / Orders (`Sales Orders` module)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `sales.month` | card (revenue this month + delta) | reuse `dashboard monthly sales` | company | sales, accountant |
| `orders.month` | card (orders this month) | reuse `dashboard monthly orders` | company | sales |
| `orders.cancelled_month` | card | reuse `dashboard monthly cancelled orders` | company | sales |
| `orders.refunded_month` | card | reuse `dashboard monthly refunded orders` | company | sales, accountant |
| `sales.avg_daily` | chart (7-day average) | reuse `dashboard daily average sales` | company | sales, accountant |
| `orders.recent` | table | reuse `dashboard recent orders` | company | sales |

### 6.4 Inventory / Stock (`Products`, plan-gated: Inventory)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `stock.low` | card (low-stock count) | `dashboard stock low` | company | stock manager |
| `stock.out` | card (out-of-stock count) | `dashboard stock out` | company | stock manager |
| `stock.value_by_category` | chart | `dashboard stock value` | company | stock manager, accountant |
| `stock.report` | table | reuse `dashboard stock report` | company | stock manager |

### 6.5 Chart of Accounts (`Accounts`, plan-gated: Chart of accounts)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `coa.today_profit` | donut card | reuse `dashboard profit loss report` | company | accountant |
| `coa.revenue_expense_chart` | chart (12 months) | `dashboard revenue expense` | company | accountant |
| `coa.receivables` | card | `dashboard receivables` | company | accountant |
| `coa.payables` | card | `dashboard payables` | company | accountant |

### 6.6 Suppliers (`Suppliers` module)

| key | type | permission | scope | suggested roles |
|---|---|---|---|---|
| `supplier.pending_po` | card (open purchase orders) | `dashboard supplier pending orders` | company | stock manager |
| `supplier.payable` | card (amount payable to suppliers) | `dashboard supplier payable` | company | accountant, stock manager |

### 6.7 Tenant Subscription & Plan (company admin / owner)

> **Naming note.** In this codebase the **platform** role is literally named `admin` (renamed from
> `superadmin`; `HomeController` renders `dashboard.admin` for it and its permission modules are
> `is_platform = true`). The **tenant owner** role is named `company`. This document uses
> "company admin / owner" for the tenant owner (`company`) and "superadmin / platform" for the
> platform role (`admin`). See §6.8 for the platform widgets.

These are the "current plan / subscription ending" widgets for the **company admin** (the owner
`company` role, plus optionally `accountant`). They are **not** plan-gated (`module()` is empty) so
a company with no active plan still sees the "choose a plan" card.

| key | type | data | permission | scope | suggested roles |
|---|---|---|---|---|---|
| `billing.no_plan` | card (empty state) | "No active plan — choose a plan" CTA → `billing.pricing`; shown only when `Company::activeSubscription()` is null | `dashboard current plan` | company | owner, accountant |
| `billing.current_plan` | card | Plan name, price (format `unit_amount/100` + `currency`), interval (`month`/`year`, `interval_count`), subscription `status` badge | `dashboard current plan` | company | owner, accountant |
| `billing.renewal` | card | **Subscription ending**: `current_period_end` date, **days remaining**, `status` (`active`/`trialing`/`past_due`), and "cancels at period end" warning when `cancel_at_period_end = true` | `dashboard subscription renewal` | company | owner, accountant |
| `billing.usage` | card (or small table) | Plan-limit usage: employees used / limit, roles used / limit, products, etc. via `PlanLimitService::usage()` + `limitFor()` (renders "Unlimited" when limit < 0) | `dashboard subscription usage` | company | owner, accountant |
| `billing.next_invoice` | card | Next/open invoice: `amount_due`, `currency`, `due_date` from `billing_invoices` (`status = open`) | `dashboard subscription renewal` | company | owner, accountant |
| `billing.invoices` | table | Recent invoices with `status` + links to `hosted_invoice_url` / `invoice_pdf` | `dashboard my invoices` | company | owner, accountant |

Data sources:

- Subscription/plan/price: `SubscriptionRepository` (new `activeForCompany()`), `PlanRepository`,
  `PriceRepository`. Add `SubscriptionRepository::activeForCompany(int $companyId)` and reuse it
  everywhere (it also removes the inlined active-subscription queries currently duplicated in
  `PlanFeatureService`/`PlanLimitService`).
- Limits: `PlanLimitService` (`usage`, `limitFor`).
- Invoices: `InvoiceRepository` (add aggregate helpers, e.g. `openInvoicesForCompany($companyId)`,
  `sumAmountDueOpenForCompany($companyId)`).

> No tenant-facing subscription screen exists today (only public `billing.pricing` / `billing.subscribe`).
> These widgets are the first surface that tells a company admin what plan they are on and when it
> ends; each card should link to `billing.pricing` for changes.

### 6.8 Platform / SaaS owner — superadmin (`platform` scope only)

The platform role in `RolePermissionSeeder` is granted **every** global permission, so new
platform permissions below require no extra role mapping. All are ungated by plan (platform users
have no company).

**Companies**

| key | type | data | permission |
|---|---|---|---|
| `platform.companies` | card | Total companies | `view company` |
| `platform.active_companies` | card | **Active** companies (`status = active`) + delta vs. last month | `dashboard active companies` |
| `platform.inactive_companies` | card | Inactive/suspended companies | `dashboard active companies` |
| `platform.new_companies` | card | New companies this month | `view company` |
| `platform.tenants` | card | Tenant users (users with `company_id` not null) | `view tenant` |
| `platform.recent_companies` | table | Recent companies | `view company` |

**Revenue & Stripe**

| key | type | data | permission |
|---|---|---|---|
| `platform.stripe_balance` | card | **Stripe balance**: available + pending (all currencies) via `StripeService::balance()`; cached; "not configured"/"unavailable" state (§5.5) | `dashboard stripe balance` |
| `platform.mrr` | card | MRR = Σ of active subscriptions' price `unit_amount` (normalized from `interval`) / 100 | `view billing report` |
| `platform.arpa` | card | Average revenue per active account (MRR / active subscriptions) | `view billing report` |
| `platform.revenue_chart` | chart | Revenue / new signups, last 6–12 months | `view billing report` |

**Due payments / collections**

| key | type | data | permission |
|---|---|---|---|
| `platform.due_payments` | card | Count + total `amount_due` of **open invoices** past their `due_date` | `dashboard due payments` |
| `platform.past_due_subscriptions` | card | Count of subscriptions with `status` in (`past_due`, `unpaid`) | `dashboard due payments` |
| `platform.failed_payments` | card | Count + total of `billing_payments` with `status = failed` | `dashboard due payments` |
| `platform.due_invoices` | table | Open/past-due invoices with company + amount + due date | `view billing invoice` |

> Platform numbers currently in `HomeService::getSuperadminStats()` (companies, active, inactive,
> users, recent) migrate into the "Companies" widgets above and the method is deleted.

### 6.9 Power-user backlog — the "everything" dashboard (candidates)

If one user held **every** role at once, the ideal dashboard is an **executive overview**:
money (revenue, profit, cash), flow (what is happening right now), exceptions (what needs
attention), and trends (up or down). The recommended shape is a **hero KPI strip** of 4–6
high-signal cards at the top, followed by collapsible/grouped domain sections.

The MVP sets in §6.1–6.8 already cover the essentials. The tables below are the **wider candidate
backlog**, grounded in tables that actually exist. Each row is tagged:

- **[now]** — queryable from existing tables as-is.
- **[agg]** — needs a new repository aggregate method (no schema change).
- **[schema]** — needs a new column/table; treat as **future**, not v1.

#### 6.9.1 Sales, Orders & POS operations

| key | type | data | readiness |
|---|---|---|---|
| `sales.today` | hero card | Revenue today vs yesterday (% delta); pairs with existing profit card | [now] transactions |
| `sales.hourly` | chart | Revenue by hour today — staffing / peak-hour view | [agg] transactions by hour |
| `sales.by_category` | chart (donut) | Revenue share by product category | [agg] join `order_products`→`product_categories` |
| `sales.by_payment_method` | chart (donut) | Cash vs card split (§`orders.payment_method`) | [agg] orders group by method |
| `sales.top_products` | table | Best sellers by qty / revenue in period | [agg] `order_products` sums |
| `sales.slow_movers` | table | Worst sellers / dead products | [agg] |
| `sales.by_salesperson` | chart/table | Revenue per cashier (§`order_users.user_id`) | [agg] |
| `orders.pending` | card + table | Orders `status = generated` (unpaid/unfulfilled) | [now] |
| `orders.overdue` | card | Orders past `due_date` and not `paid` | [now] |
| `orders.avg_value` | card | Average order value (AOV) | [now] |
| `orders.fulfilment_today` | card | Today's paid vs cancelled vs refunded counts | [now] |
| `returns.today` | card | Cancelled + refunded value today | [now] |
| `promotions.active` | card | Active promotions (`start_date ≤ today ≤ end_date`) | [now] promotions |

#### 6.9.2 Profit & Finance (Chart of Accounts)

| key | type | data | readiness |
|---|---|---|---|
| `coa.cash_position` | hero card | Cash + bank balances (sum of cash/bank `account_level4s` by short code) | [now] level4 `balance`, [agg] by code |
| `coa.monthly_profit` | chart | Revenue vs expense vs net profit, last 12 months | [agg] transactions by month |
| `coa.expenses_by_category` | chart | Expense split by account level-1 group | [agg] `transactions`→`account_level4s`→parents |
| `coa.receivables` / `coa.receivables_aging` | card + table | Customer receivables + buckets (0-30/31-60/61-90/90+) | [agg] |
| `coa.payables` / `coa.payables_aging` | card + table | Supplier payables + aging buckets | [agg] |
| `coa.trial_balance` | card | Debits vs credits + imbalance warning (integrity check) | [now] transactions |
| `coa.uncategorized` | card | Vouchers/transactions with a fallback/placeholder account | [agg] |
| `coa.tax_payable` | card | Tax/VAT payable balance (if tax accounts seeded) | [now] level4 by code |
| `journal.today` | table | Vouchers posted today | [now] |
| `journal.void` | card | Voided vouchers (`vouchers.type = void`) count/value | [now] |

#### 6.9.3 Inventory & Stock control

| key | type | data | readiness |
|---|---|---|---|
| `stock.value` | hero card | Total inventory value at cost (Σ `product.stock × purchase_price`) | [now] |
| `stock.low_table` | table | Low-stock products with **reorder qty** suggestion | [agg] |
| `stock.turnover` | chart/card | Inventory turnover / days-of-stock | [agg] sales vs stock |
| `stock.incoming` | card | Units on open purchase orders (expected inbound) | [agg] |
| `stock.movements_today` | table | Stock history entries today (+purchase/-sale) | [now] `stock_histories` |
| `stock.dead` | card | Products with no sales in N days (dead stock) | [agg] |
| `stock.by_category` | chart | Stock value by category | [agg] |

#### 6.9.4 Customers & CRM (users with role `customer`)

| key | type | data | readiness |
|---|---|---|---|
| `customers.new_month` | card | New customers this month (reuses `dashboard monthly customers`) | [now] |
| `customers.top` | table | Top customers by lifetime spend | [agg] orders→order_users |
| `customers.today_heroes` | table | Today's buyers (existing `cutomers.todayHeroes` in `HomeService`) | [now] |
| `customers.receivable` | card | Total outstanding from customers | [agg] |
| `customers.repeat_rate` | card | Repeat vs first-time buyer ratio in period | [agg] |
| `customers.inactive` | card | Customers with no order in N days | [agg] |

#### 6.9.5 Employees, Attendance & Payroll

| key | type | data | readiness |
|---|---|---|---|
| `employee.count` | hero card | Active employees vs total; new this month | [now] users |
| `employee.by_designation` | chart | Headcount by `employee_personal_information.designation` | [agg] |
| `attendance.working_now` | card | Users currently clocked in (last event = `clockin`) | [agg] |
| `attendance.late_today` | card | Late/absent today (needs a shift-start rule) | [agg] |
| `attendance.avg_hours` | chart | Average worked hours per day/user | [agg] |
| `attendance.monthly_rate` | card | Attendance % this month | [agg] |
| `payroll.ytd_cost` | card | Payroll cost year-to-date | [agg] |
| `payroll.upcoming` | card | Estimated next run (base salaries + allowances − deductions) | [agg] |

#### 6.9.6 Suppliers & Purchasing

| key | type | data | readiness |
|---|---|---|---|
| `supplier.top` | table | Top suppliers by purchase value | [agg] purchase_orders |
| `supplier.po_status` | chart | PO status breakdown (generated/refunded) | [now] |
| `supplier.received_today` | card | Goods received today (`stock_histories.purchase_order_id`) | [now] |

#### 6.9.7 "My" widgets (own scope — every employee)

| key | type | data | readiness |
|---|---|---|---|
| `me.attendance_today` | card | My clock-in/out status today + hours so far | [now] |
| `me.sales_today` | card | My sales today + this month (cashier performance) | [agg] orders by `order_users.user_id` |
| `me.payslip_latest` | card | My latest payslip net pay + status | [now] |
| `me.tasks` | card/list | Things assigned to me (see §6.9.8) | [schema] if no task table |

#### 6.9.8 System, approvals & activity (platform / owner)

| key | type | data | readiness |
|---|---|---|---|
| `platform.failed_jobs` | card | `failed_jobs` count (queue health) | [now] |
| `platform.webhook_health` | card | `billing_webhook_events.status = failed` count | [now] |
| `platform.signups` | card + table | `billing_signups` by status (pending/expired/failed) | [now] |
| `platform.trials_ending` | card + table | Trialing subscriptions with `current_period_end` within N days | [now] |
| `platform.churn` | card | Subs canceled this month; net new vs lost | [agg] |
| `platform.dunning` | card | Past-due + unpaid subscription value | [now] |
| `platform.coupons` | card | Active coupons / total redemptions | [now] |
| `activity.recent` | table | Recent activity feed (orders, vouchers, subscriptions) | [agg] union |
| `approvals.pending` | card | Items awaiting approval (payroll draft→processed, etc.) | [agg] |

> **Deliberately out of scope (need schema first):** register/shift management, POS drawer & expected
> cash, appointments/calendar, leave requests, customer loyalty points, product expiry/batch
> tracking, and per-cashier commission — none have backing tables today (`[schema]`). Add only if
> the underlying feature is built.

> **Design guidance for the "everything" view:** cap the hero strip at ~6 cards, keep deep tables
> behind "view all" links to their existing list screens, and prefer lazy sections for the heavy
> finance charts so the first paint stays fast.

---

## 7. Permissions & Role Provisioning

### 7.1 Add widget permissions (global)

Add the new `dashboard *` permissions to the **`Dashboard`** group in
`database/seeders/RolePermissionSeeder::$modules`, or — for widgets that clearly belong to a domain
— to that domain's module group. **Recommendation:** keep all dashboard widgets under one
`Dashboard` permission module for discoverability on the Permissions screen, and gate plan-feature
eligibility via the widget's own `module()` value (not the permission module).

Keep the existing ten `dashboard *` permissions and reuse them where a widget maps 1:1.

**New permissions added by this plan:**

- Tenant widget permissions (in the tenant `Dashboard` module):
  `dashboard current plan`, `dashboard subscription renewal`, `dashboard subscription usage`,
  `dashboard my invoices`, plus the module widgets in §6.1–6.6
  (`dashboard attendance summary`, `dashboard attendance monthly`,
  `dashboard attendance absent today`, `dashboard my attendance`, `dashboard payroll pending`,
  `dashboard payroll due`, `dashboard payroll paid month`, `dashboard payroll cost`,
  `dashboard payroll recent`, `dashboard my payslips`, `dashboard stock low`, `dashboard stock out`,
  `dashboard stock value`, `dashboard revenue expense`, `dashboard receivables`,
  `dashboard payables`, `dashboard supplier pending orders`, `dashboard supplier payable`).
- Platform widget permissions (in the platform `Billing` module):
  `dashboard stripe balance`, `dashboard due payments`, `dashboard active companies`.

> **Permission placement matters for tenants.** The existing `Billing`/`SaaS` permission modules are
> platform-only (`is_platform = true`), so tenant roles can never hold `view billing invoice` /
> `view subscription`. The tenant subscription widgets therefore use their own tenant-scoped
> `dashboard *` permissions above rather than reusing the platform ones. (If a dedicated tenant
> screen is ever wanted, add tenant permissions like `view own subscription` / `view own invoices`
> in a tenant `Subscription` module.)

**Backlog widgets (§6.9)** each get their own permission under the same convention (one per widget,
`dashboard <name>`), granted only when the widget is promoted from backlog to shipped. Because the
owner `company` role and the platform `admin` role are auto-granted all tenant / all global
permissions respectively, promoting a backlog widget requires **no role edits** — only the widget
class, its permission, and (for scoped roles) an optional `DefaultRoles` addition.

### 7.2 Map permissions to default roles

Extend `Modules/RBAC/Support/DefaultRoles::roles()`:

```php
'hr' => [ /* ...existing... */
    'dashboard attendance summary',
    'dashboard attendance monthly',
    'dashboard attendance absent today',
    'dashboard my attendance',
    'dashboard payroll pending',
    'dashboard payroll due',
    'dashboard payroll paid month',
    'dashboard payroll cost',
    'dashboard payroll recent',
    'dashboard my payslips',
],

'employee' => [
    'view own attendance',
    'view own payroll',
    'dashboard my attendance',
    'dashboard my payslips',
],

'accountant' => [ /* ...existing... */
    'dashboard my attendance',
    'dashboard my payslips',
    'dashboard payroll pending',
    'dashboard payroll due',
    'dashboard payroll paid month',
    'dashboard payroll cost',
    'dashboard profit loss report',
    'dashboard revenue expense',
    'dashboard receivables',
    'dashboard payables',
    'dashboard supplier payable',
    'dashboard monthly sales',
    'dashboard daily average sales',
    // tenant subscription / plan widgets
    'dashboard current plan',
    'dashboard subscription renewal',
    'dashboard subscription usage',
    'dashboard my invoices',
],

'sales person' => [ /* ...existing... */
    'dashboard my attendance',
    'dashboard my payslips',
    'dashboard monthly sales',
    'dashboard monthly orders',
    'dashboard monthly cancelled orders',
    'dashboard monthly refunded orders',
    'dashboard daily average sales',
    'dashboard recent orders',
],

'stock manager' => [ /* ...existing... */
    'dashboard my attendance',
    'dashboard my payslips',
    'dashboard stock low',
    'dashboard stock out',
    'dashboard stock value',
    'dashboard stock report',
    'dashboard supplier pending orders',
    'dashboard supplier payable',
],
```

- The owner `company` role keeps **all** tenant permissions (already the case), so it automatically
  sees everything permitted + plan-allowed — including the new tenant subscription widgets
  (`dashboard current plan`, `dashboard subscription renewal`, `dashboard subscription usage`,
  `dashboard my invoices`). No explicit mapping is needed for the owner; it is the primary audience
  for the subscription widgets.
- The platform `admin` role is granted **every** global permission by `RolePermissionSeeder`, so the
  new platform widget permissions (`dashboard stripe balance`, `dashboard due payments`,
  `dashboard active companies`) are picked up automatically — no separate platform mapping is
  needed.

### 7.3 Existing companies (data migration)

New permissions are only provisioned for companies created **after** the change. Add an idempotent
data migration (mirroring
`database/migrations/2026_10_16_000003_provision_payroll_permissions.php`) that, for every company:

1. Creates any missing widget permissions (global) — normally done by the seeder, but the migration
   must be safe to run standalone.
2. Uses `RoleProvisioningService` to `ensureRole()` each default role and **sync** the new
   permissions from `DefaultRoles` (additive; never removes existing grants).
3. Grants all tenant `dashboard *` permissions to existing owner (`company`) roles.

Keep it idempotent (re-runnable); migrations run without DDL wrapping in MySQL.

### 7.4 Permission → module lookup fix

Fix `AsideMenuLinks::moduleForPermission()` (and provide one shared helper, e.g.
`PermissionService::moduleMapForCompany()` or a small `PlanFeatureService` method) so it queries
**global** permissions (`company_id = null`) and returns `name => module`. Widgets use the same
helper, so sidebar and dashboard gating stay consistent.

---

## 8. Authorization Rules

A widget is visible iff **all** of:

1. **Role/tenant context:** `scope = platform` → platform admin only; `scope = company|own` →
   non-platform user.
2. **Permission:** `auth()->user()->hasPermission($widget->permission())`.
3. **Plan feature:** if the widget's `module()` is plan-gated, the company's allowed modules include
   it (or the company is ungated).
4. **Scope data filter (enforced in Service → Repository):**
   - `own` → filter by `auth()->user()->id` (employee must never see company totals).
   - `company` → filter by `auth()->user()->company_id` (repositories already expose `filter()`
     scopes / `*ForCompany` methods).

Widgets must **never** trust the client for authorization or scoping — the batched payload only
includes permitted widgets, and each widget's `data()` re-applies the scope.

### Escalation guard for `own` widgets

The employee-facing `dashboard my attendance` / `dashboard my payslips` permissions must **not**
grant company-wide data. Since the widget's service call is scoped to `auth()->user()->id`, granting
the permission can only ever expose the user's own rows — matching the existing
`attendance/me` and `payrolls/me` design.

---

## 9. Layout & UX

- Use Bootstrap grid: widgets declare `size()` (`col-12`, `col-6`, `col-4`, `col-3`) and `order()`.
- Offer an optional **hero KPI strip** at the top (4–6 compact cards, `size = col-3`/`col-2`) for
  the highest-signal cross-domain numbers (today's sales, profit, cash position, low stock, pending
  orders, active subscriptions), with the rest grouped below.
- Group visually into named sections (e.g. **Overview**, **HR**, **Finance**, **Stock**,
  **Subscription**, and — for the platform shell — **Companies**, **Revenue**, **Collections**) —
  optional; can be derived from `module()`/a `group()` hint on the widget.
- Empty state: if a user has no permitted widgets, render a friendly "No widgets available — ask an
  administrator for access" card instead of a blank page.
- Keep the header quick-actions (`Create Product`, `Create Purchase Order`, `Create Sale Order`)
  but gate each by its existing permission (already done).
- Add a lightweight per-widget loading skeleton in the container until the payload arrives.
- Manual refresh: a dashboard-level refresh button re-calls `/dashboard/widgets`.

---

## 10. Phased Implementation Plan

### Phase 0 — Foundations (no behavior change)
1. Add `Widget`, `AbstractWidget`, `DashboardRegistry`, `DashboardService`.
2. Register the registry as a singleton; add an empty `DashboardServiceProvider` (or wire in
   `AppServiceProvider`).
3. Build the generic grid shell + generic `card`/`chart`/`table` partials + the JS dispatcher.
4. Port **one** existing widget (e.g. `dashboard monthly sales`) to the new system behind the
   existing permission; keep the old `HomeService::getCharts()` path as fallback. Verify parity.

### Phase 1 — Migrate existing core widgets
5. Port all ten existing `dashboard *` widgets (sales, orders, profit, customers, stock, recent
   orders, attendance) to widget classes, reusing current permissions and data logic (move logic
   into the relevant module Service/Repository as needed).
6. Delete the hardcoded blocks from `dashboard.blade.php`/`_js.blade.php`; delete
   `HomeService::getCharts()` once unused (keep `getProducts()` used by the quick-order modals).
7. Migrate `getSuperadminStats()` into platform widgets + `dashboard/admin.blade.php`.

### Phase 2 — New module widgets
8. Payroll widgets (`payroll.*`) via `PayrollService` + `PayrollRepository` aggregates.
9. Attendance widgets (`attendance.*`) via `AttendanceHistoryService` +
   `AttendanceHistoryRepository` (add per-day/per-month aggregate methods).
10. Inventory (`stock.*`), COA (`coa.*`), Suppliers (`supplier.*`) widgets.
11. Add the concrete blade partials + renderers.

### Phase 3 — Tenant subscription widgets (company admin)
12. Add `SubscriptionRepository::activeForCompany()` and reuse it in `PlanFeatureService` /
    `PlanLimitService` (removing the inlined active-subscription queries).
13. Add `InvoiceRepository` aggregate helpers (`openInvoicesForCompany`,
    `sumAmountDueOpenForCompany`) and any `PaymentRepository` aggregates needed.
14. Build `billing.no_plan`, `billing.current_plan`, `billing.renewal`, `billing.usage`,
    `billing.next_invoice`, `billing.invoices` via `SubscriptionService`/`PlanLimitService`/
    `InvoiceService` (no plan gate; empty-state CTA to `billing.pricing`).

### Phase 4 — Platform / superadmin widgets
15. Companies widgets (`platform.companies`, `platform.active_companies`, `platform.inactive_companies`,
    `platform.new_companies`, `platform.tenants`, `platform.recent_companies`) — migrate
    `getSuperadminStats()` into these and delete the method.
16. Stripe balance widget with caching + graceful degradation (§5.5).
17. Due-payment widgets (`platform.due_payments`, `platform.past_due_subscriptions`,
    `platform.failed_payments`, `platform.due_invoices`) from `billing_invoices` /
    `billing_subscriptions` / `billing_payments`.
18. Revenue widgets (`platform.mrr`, `platform.arpa`, `platform.revenue_chart`).

### Phase 5 — Permissions & roles
19. Add new `dashboard *` permissions to `RolePermissionSeeder` (§7.1).
20. Extend `DefaultRoles` with the role→widget-permission mappings (§7.2); owner + platform roles
    are auto-granted.
21. Add the idempotent data migration (§7.3); run `migrate` + reseed on dev; verify existing
    companies receive the grants.
22. Fix the `AsideMenuLinks::moduleForPermission()` global-permission bug (§7.4).

### Phase 6 — Polish (optional)
23. Per-company dashboard curation: `settings.dashboard` JSON + a settings screen modelled on
    `AsideSettingService`/`AsideSettingController`, letting a company admin hide/reorder **permitted**
    widgets (permission filtering still enforced server-side).
24. Widget unit/feature tests (see §11).
25. Icons + empty states + responsive polish.

### Phase 7 — Power-user backlog (§6.9, on demand)
26. Promote backlog widgets from §6.9 in priority order, starting with `[now]` items (no schema
    work): `sales.today`, `coa.cash_position`, `stock.value`, `orders.pending`/`overdue`,
    `customers.today_heroes`, `platform.failed_jobs`/`webhook_health`/`signups`/`trials_ending`/
    `dunning`/`coupons`.
27. Add the `[agg]` repository methods as needed (e.g. `OrderRepository::revenueByHour()`,
    `topProducts()`, `revenueByCategory()`; `TransactionRepository::monthlyProfit()`,
    `expensesByGroup()`; `AttendanceHistoryRepository::workingNow()`, `avgHours()`), each covered by
    the same scope rules.
28. Defer all `[schema]` items until their feature exists.

---

## 11. Testing & Verification

Follow existing constraints (tests share the dev MySQL DB — **do not** use `RefreshDatabase`; use
transactional scripts that roll back).

- **Widget resolution:** given a user with permission X, `DashboardService::widgetsFor()` returns
  exactly the expected keys; a user without X does not receive it in the payload.
- **Plan gating:** a company on a plan without `Payroll` does not receive `payroll.*` widgets even
  when the permission is held.
- **Scope:** an `employee` hitting `/dashboard/widgets` gets `payroll.my_payslips` containing only
  their own rows; `hr` gets the company-wide variant.
- **Platform vs tenant:** platform admin sees only `platform.*`; tenant user never sees them, and a
  tenant request can never resolve a `platform.*` payload.
- **Tenant subscription:** a company with an active subscription gets `billing.current_plan` +
  `billing.renewal` (correct `current_period_end` / days-remaining / `cancel_at_period_end`); a
  company with **no** subscription gets `billing.no_plan` and none of the subscription cards;
  `billing.usage` reflects `PlanLimitService` (incl. "Unlimited" for limits < 0).
- **Stripe balance resilience:** with Stripe unconfigured or the API throwing, the widget returns an
  "unavailable" payload and the rest of the dashboard still loads; the cached value is used within
  its TTL and a manual refresh bypasses the cache.
- **Due payments:** open/past-due invoice and failed-payment counts/sums match the underlying
  `billing_invoices` / `billing_payments` rows.
- **Provisioning:** after the migration + reseed, `hr`/`employee`/`accountant`/etc. roles in a fresh
  company hold the expected widget permissions; the owner `company` role holds all tenant widget
  permissions; the platform `admin` role holds all platform widget permissions; existing companies
  get the additive grants; re-running the migration is a no-op.
- **Regression:** all previously visible widgets for the owner role still render.
- Lint every changed file (`php -l`), run `php artisan view:cache` / `view:clear`, and confirm no
  root-owned files and no temp scripts left behind.

---

## 12. Open Questions / Decisions Needed

1. **Permission granularity:** one permission per widget (proposed) vs. a few coarse permissions per
   module. Proposal: one per widget, for maximum role flexibility; revisit if the Permissions screen
   becomes noisy.
2. **Permission module grouping:** all widgets under a single `Dashboard` module (proposed) vs.
   spreading widget permissions across their domain modules (`Payroll`, `Employees`, …).
3. **Per-company curation (Phase 4):** do we want companies to hide/reorder widgets, or is
   role-based visibility enough for v1?
4. **Payload strategy:** one batched request (proposed) vs. per-widget lazy loading. Batched favors
   simplicity; lazy favors very large dashboards.
5. **Chart library:** keep ApexCharts everywhere (proposed) — no new dependency.
6. **Server-rendered vs client-hydrated widgets:** proposed hybrid (server renders containers, JS
   fills data). Confirm before porting all widgets.
7. **Stripe balance cache TTL:** propose 5 minutes (configurable). Confirm acceptable staleness.
8. **Multi-currency:** Stripe balance and invoice totals can be multi-currency. Render per-currency
   (proposed) vs. converting to a single display currency (needs FX rates). Which for v1?
9. **MRR normalization:** define how `month`/`year` intervals and `interval_count` are normalized
   to a monthly figure (proposed: `unit_amount / (interval == 'year' ? 12 : 1) / interval_count`).

---

## 13. Success Criteria

- A user's dashboard is composed **only** of widgets their role is permitted to see and their plan
  allows.
- `hr` sees attendance + payroll widgets; `employee` sees only their own attendance/payslips;
  `accountant` sees payroll + finance + subscription widgets; `stock manager` sees stock + supplier
  widgets; the owner sees everything (including current plan / subscription-ending).
- The **company admin** sees their current plan, when the subscription ends, remaining days, plan
  usage, and upcoming/recent invoices — or a clear "choose a plan" card when unsubscribed.
- The **superadmin** sees Stripe balance, companies (total/active/inactive/new), tenant users, due
  payments (open/past-due invoices, past-due subscriptions, failed payments), and MRR.
- Adding a new module widget requires **no** edits to `HomeController`, `HomeService`, the shell, or
  the dispatcher — only the widget class, its partial, and a one-line registration in the module's
  ServiceProvider.
- No dashboard code violates `architecture-constraints.md` (Controller → Service → Repository → Model).
