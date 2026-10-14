# Subscription Packages — Basic / Pro / Enterprise

Defines the three sellable packages and the five levers each one enforces.
This is the source of truth for what a package grants; it narrows the broader
billing plan in `docs/saas-billing-implementation.md` (Part A) to a concrete
catalog.

> **Status:** implemented. The catalog is seeded by `database/seeders/PlanSeeder.php`
> and enforcement lives in `PlanLimitService` / `PlanFeatureService` plus the
> `plan.feature` middleware. See §5 for the exact files.

## 1. Scope

Three packages, each restricting exactly these five parameters:

| # | Lever | Kind | Meaning |
| --- | --- | --- | --- |
| 1 | Employees | resource limit | Max employees a company can create |
| 2 | Roles | resource limit | Max roles a company can create |
| 3 | Chart of Accounts | feature access | Can the company open the accounting module |
| 4 | Inventory | feature access | Can the company open products / categories / stocks |
| 5 | Promotions | feature access | Can the company create and manage promotions |

Every lever is **enforced** (not just displayed). Resource limits block creation
at the limit; feature access hides and denies the module.

## 2. Package matrix (implemented)

| Lever | Basic | Pro | Enterprise |
| --- | --- | --- | --- |
| Employees (max) | 5 | 25 | Unlimited |
| Roles (max) | 2 | 5 | Unlimited |
| Chart of Accounts | — | ✅ | ✅ |
| Inventory | ✅ | ✅ | ✅ |
| Promotions | — | ✅ | ✅ |

- **Unlimited** is stored as `limit_value = -1` (the existing sentinel accepted
  by `PlanService::saveLimits()`; a missing row or `null` is also unlimited).
- Pricing (seeded as monthly `billing_prices`): Basic **$19/mo**, Pro **$49/mo**,
  Enterprise **$99/mo**.

## 3. Lever → implementation mapping

The billing schema already models all three kinds of lever
(`billing_plan_features`, `billing_plan_permissions`, `billing_plan_limits`), so
no new tables are needed — only a new limit key and enforcement code.

### 3.1 Resource limits → `billing_plan_limits`

| Lever | `resource_key` | Where enforced |
| --- | --- | --- |
| Employees | `employees` | `Modules/Employee/Services/EmployeeService.php` `store()` |
| Roles | `roles` | `Modules/RBAC/Services/RoleService.php` `store()` |

`roles` is **new** — it must be added to
`Modules/Billing/Services/PlanService::$limitKeys` (currently
`products, users, employees, suppliers, promotions, purchase_orders`). Existing
keys remain available; this package set only uses `employees` and `roles`.

- **Employee count** = users in the company assigned the `employee` role
  (`user_roles`; see `EmployeeService::getEmployees()`), scoped by
  `users.company_id`. The company owner and other staff do not count.
- **Role count** = `roles` where `company_id = <company>` (the provisioned
  `company` owner role is the base role and is **not** counted against the
  limit).

### 3.2 Feature access → `billing_plan_features` (permission modules)

A feature maps to one or more existing `permission_modules`. Selecting the
module is what grants access; the plan editor's **Modules** tab already
persists these as `billing_plan_features`.

| Feature | Permission modules granted |
| --- | --- |
| Chart of Accounts | `Accounts`, `Vouchers`, `Voucher Types` |
| Inventory | `Products`, `Categories`, `Purchase Orders` |
| Promotions | `Promotions` |

(Module names come from `database/seeders/RolePermissionSeeder.php`.)

## 4. Enforcement

### 4.1 Resource limits — `PlanLimitService`

New service `Modules/Billing/Services/PlanLimitService.php`:

- `withinLimit(string $key): bool` and
  `ensureWithinLimit(string $key, ?Company $company): void`.
- Reads the live limit from the company's current plan via the active
  `billing_subscriptions → plan → limits` chain (unlimited when the resource row
  is missing or `null`/`-1`).
- On breach throws `App\Exceptions\PlanLimitExceededException` (generic
  message, no upgrade CTA — matches the existing exception convention and the
  `Handler.php` JSON envelope).

Wired into:

- `Modules/Employee/Services/EmployeeService.php::store()` →
  `ensureWithinLimit('employees')` before creating the user.
- `Modules/RBAC/Services/RoleService.php::store()` →
  `ensureWithinLimit('roles')` before creating the role.

### 4.2 Feature access — runtime plan gate

Per-plan permission **provisioning** (`PlanProvisioningService`) is not built
yet, so feature access is enforced at runtime against the plan's modules.

New service `Modules/Billing/Services/PlanFeatureService.php`:

- `FEATURES` maps a lever to its permission modules (see §3.2).
- `allowedModules(?Company)` returns the module names on the active plan
  (`billing_plan_features → permission_modules`) or `null` when unrestricted
  (platform account / no active subscription).
- `companyAllowsFeature(?Company, string $feature)` requires every module of the
  feature to be included.

Enforced in two places:

- **Routes** — new `App\Http\Middleware\EnsurePlanFeature` (alias
  `plan.feature`) aborts `403` when a feature is missing. Applied alongside the
  existing permission middleware in `Modules/ChartOfAccounts/routes/web.php`
  (`plan.feature:chart-of-accounts`) and `Modules/Inventory/routes/web.php`
  (`plan.feature:inventory`, `plan.feature:promotions`).
- **Navigation** — `AsideMenuLinks::filterLinks()` also drops links whose
  permission maps to a gated module that the plan does not include (in addition
  to the existing `hasPermission()` check).

> **Note:** promotions appears both as a feature (module access) and as a
> pre-existing quantity limit key (`promotions`). This package set uses it as an
> access toggle; the quantity limit stays available but unused.

## 5. Database / code changes (implemented)

1. **`PlanService::$limitKeys`** — added `'roles' => 'Roles'` (so the plan
   editor's Limits tab and `saveLimits()` accept it).
2. **`billing_plan_limits`** — no schema change (`resource_key` is a free
   string); only the allowed-key list above changed.
3. **`App\Exceptions\PlanLimitExceededException`** — new, extends
   `CustomException` (`error` level, optional message/field) so the existing
   `Handler` renders the JSON envelope.
4. **`Modules/Billing/Services/PlanLimitService`** — new; resolves the active
   plan, reads limits, computes usage, throws on breach.
5. **`Modules/Billing/Services/PlanFeatureService`** — new; feature→module map
   and allowed-modules resolution.
6. **`App\Http\Middleware\EnsurePlanFeature`** + alias `plan.feature` in
   `app/Http/Kernel.php` — runtime module gate.
7. **Route wiring** — `Modules/ChartOfAccounts/routes/web.php`,
   `Modules/Inventory/routes/web.php`.
8. **Limit wiring** — `Modules/Employee/Services/EmployeeService::store()`,
   `Modules/RBAC/Services/RoleService::store()`.
9. **Sidebar** — `app/View/Components/AsideMenuLinks.php` filters gated modules.
10. **`Modules/RBAC/Services/RoleService::getRoleByName()`** — falls back to the
    shared template role (`company_id = null`) when a company-scoped role has
    not been provisioned. Fixes a pre-existing bug that made
    `EmployeeService::role()` throw ("Role doesn't exists") and is what makes the
    `employees` lever reachable.
11. **`database/seeders/PlanSeeder.php`** — new; seeds the three plans.

## 6. Creating the three plans

Two equivalent paths:

**A. Platform plan editor** (platform `admin` → Billing → Plans → Create), for
each package:

1. **Details** — name (`Basic`/`Pro`/`Enterprise`), slug, description, active,
   public, sort.
2. **Prices** — monthly amount (and optional annual).
3. **Modules** — tick the modules from §3.2 for that package (Basic: no
   Accounts/Vouchers/Voucher Types, no Promotions; Pro/Enterprise: all five).
4. **Limits** — set `employees` and `roles`; leave blank for Enterprise =
   unlimited.

The existing `Starter` plan (`billing_plans.slug = starter`, one `products`
limit) is a placeholder and should be **deactivated** once these three exist.

**B. Seeder** (implemented) — `PlanSeeder` upserts the three plans with their
monthly price, feature modules and `employees`/`roles` limits, then deactivates
`Starter`:

```bash
php artisan db:seed --class=PlanSeeder --force
```

`PlanSeeder` is idempotent: it rebuilds each plan's price/feature/limit bundle
on every run and deactivates `starter` (`is_public = false`, `status =
inactive`).

## 7. Edge cases

- **Existing companies** — left unprovisioned (hit the subscription gate) until
  a plan is applied, per the implementation doc. Applying a plan runs
  provisioning and enforces limits immediately.
- **Plan edits are live** — changing a plan's limits/features affects current
  subscribers (documented behavior).
- **Downgrade below current usage** — creating new employees/roles is blocked
  once over the new limit; existing rows are not deleted.
- **Owner role** — the provisioned `company` role is not counted toward the
  `roles` limit.
- **Employees via other paths** — only `EmployeeService::store()` creates
  `employee`-role users; keep the check there so all entry points are covered.

## 8. Acceptance criteria

- A Basic company cannot create a 6th employee or a 3rd role
  (`PlanLimitExceededException`, generic message).
- A Basic company has no sidebar entry and gets `403` for Accounts/Vouchers and
  Promotions; Inventory works.
- A Pro company can use Inventory, Promotions and Chart of Accounts, and is
  capped at 25 employees / 5 roles.
- An Enterprise company has no employee/role ceiling (`-1` = unlimited) and
  access to all five features.
- Limits are read live from the active subscription's plan.

## 9. Verification

Verified against company 1 (all tenant permissions held, to isolate the plan gate
from the permission gate):

| Scenario | Result |
| --- | --- |
| Basic — `/products`, `/categories`, `/stocks` | `200` |
| Basic — `/accounts`, `/voucher-types`, `/vouchers`, `/promotions` | `403` |
| Basic — sidebar hides Bank/Accounts and Promotions categories | ✅ |
| Pro — all of the above | `200` |
| Basic — 3rd role | blocked: *"Your current plan allows up to 2 roles…"* |
| Basic — 6th employee | blocked: *"Your current plan allows up to 5 employees…"* |

## 10. Open questions (resolved)

1. Limits/feature split — confirmed as §2.
2. Inactive records — all rows count (no `status` filter) to close loopholes.
3. Basic includes Inventory — confirmed.
4. Prices — Basic $19 / Pro $49 / Enterprise $99 monthly.
