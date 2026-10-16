# Chart of Accounts (COA) Module

Complete reference for the `ChartOfAccounts` module: how it is built today, and how future
development must follow the same structure.

---

## 1. Overview

The COA module owns the accounting backbone of the application:

- A **4-level chart of accounts** (Asset / Liability / Equity / Revenue / Expense down to postable ledger accounts).
- **Vouchers** (accounting vouchers) and **Voucher Types** (BRV, CRV, APV, INV, BPV…).
- **Double-entry transactions** posted against Level 4 accounts, with live balances.
- **Account Settings** — a mapping layer that binds business roles (`SRA`, `PIA`, `VPA`…) to accounts so other modules never hardcode account IDs.
- Default COA + default voucher types **seeded per company** at provisioning time.

Other modules (Order, Supplier) post journal entries through this module's services —
COA is a shared domain, not just a CRUD screen.

**Stack:** Laravel 8 + `nwidart/laravel-modules` (modular monolith), MySQL 8, Eloquent,
repository layer, Blade + Metronic 8 (Bootstrap 5) + jQuery + Yajra DataTables.

---

## 2. Module location

```
Modules/ChartOfAccounts/
├── module.json                     # name: ChartOfAccounts, alias: chartofaccounts, provider registered
├── Config/
├── Database/Migrations/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Providers/ChartOfAccountsServiceProvider.php
├── Repositories/
├── Resources/views/
│   ├── accounts/  settings/  vouchers/  voucher_types/
├── routes/web.php
├── Services/
└── Tests/                          # (currently empty — add tests here)
```

- Module status: `modules_statuses.json` → `"ChartOfAccounts": true`
- **View namespace is lowercase:** `chartofaccounts::accounts/index`
- **PHP namespace is PascalCase:** `Modules\ChartOfAccounts\...`
- The account service is historically named `ChatOfAccountService` (missing "r"). Keep the
  name as-is for now to avoid breaking imports; do not propagate the typo to new classes.

`Providers/ChartOfAccountsServiceProvider.php` does four things:
1. `loadViewsFrom(..., 'chartofaccounts')`
2. `loadMigrationsFrom(...)` (no `->publishes()`)
3. Registers `voucher` in the Eloquent morph map (L22-24)
4. `requires routes/web.php` under the `web` middleware group

---

## 3. Architecture rules (non-negotiable)

Every COA feature must respect `docs/architecture-constraints.md`:

```
Route (middleware only)
  → Controller (validation, transactions, JSON responses — no DB queries, no repositories)
    → Service (business logic — all DB access via repositories)
      → Repository (the ONLY layer that touches Eloquent query methods)
        → Model
```

| Layer | Allowed | Forbidden |
|---|---|---|
| Routes | Middlewares | Controllers (except string invocations), Services, Models |
| Controllers | Form Requests, Services, `DB::transaction` | Repositories, direct model queries |
| Services | Repositories, other Services, models for type hints | `Model::where()`, `Model::find()`, static query calls |
| Repositories | Eloquent models | Controllers, Services |

Current known violation to be aware of (do not copy): `app/View/Components/TrailBalance.php`
queries `Transaction` directly. New code must go through a service.

---

## 4. Request lifecycle (canonical example)

`POST /accounts/store` — creating a child account.

1. **Route** — `Modules/ChartOfAccounts/routes/web.php:16`
   ```php
   Route::group(['prefix' => 'accounts', 'middleware' => [
       'permission:view account', 'plan.feature:chart-of-accounts'
   ]], function () {
       Route::post('/store', '...\AccountController@store')->name('accounts.store');
   });
   ```
2. **Form Request** — `Http/Requests/StoreAccountRequest.php:14-21`
   ```php
   'name'    => 'required',
   'account' => 'required',   // parent uid
   'status'  => 'required',
   ```
3. **Controller** — `Http/Controllers/AccountController.php:35`
   ```php
   DB::beginTransaction();
   $this->service->store($request);   // injected ChatOfAccountService
   DB::commit();
   return response()->json(['success' => true, 'message' => 'Account Added', 'table_reload' => true]);
   ```
4. **Service** — `Services/ChatOfAccountService.php:34` (`store`)
   Switches on `$request->level`, resolves the parent by **`uid`**, computes the next
   `code`, then `$repository->create([...])` with `company_id = auth()->user()->company_id`.
5. **Repository** — `Repositories/AccountLevel1Repository.php` — a 15-line class extending
   `App\Repositories\BaseRepository`, constructed with `new AccountLevel1()`.

The **AJAX contract** every screen follows:

- Success → `{ success: true, message: '...', table_reload: true }`
- Validation failure → HTTP 422 with per-field errors (handled globally by
  `resources/views/layouts/script.blade.php`)
- The global handler shows toastr toasts and fires a `table_reload` event; views listen for
  it and re-fetch the table/tree.

---

## 5. Database schema

All models except `Transaction` extend **`App\Models\UIDModel`** (`app/Models/UIDModel.php`):
- `$primaryKey = 'uid'`, non-incrementing UUID column `uid` auto-set on `creating`
- **Route model binding is by `uid`**, not by `id`
- `$guarded = ['id']`
- **No soft deletes anywhere in this module** — deletes are hard and cascade.

All models define `scopeFilter()` scoping rows to `auth()->user()->company_id`
(row-level multi-tenancy). `company_id` is `nullable` so global/demo data can exist.

### 5.1 The hierarchy

```
account_level1s  (Asset / Liability / Equity / Revenue / Expense + start_code..end_code range)
  └── account_level2s   (parent_id → level1, code "001")
        └── account_level3s (parent_id → level2, code "001-001")
              └── account_level4s (parent_id → level3, code "001-001-001", balance DOUBLE)
```

| Table | Key columns |
|---|---|
| `account_level1s` | `name` enum(Asset, Liability, Equity, Revenue, Expense), `start_code` enum(000,100,200,300,400), `end_code` enum(099,199,299,399,499), `status`, `company_id` FK |
| `account_level2s` | `name`, `code`, `parent_id` FK→level1, `status`, `company_id` |
| `account_level3s` | `name`, `code`, `parent_id` FK→level2, `status`, `company_id` |
| `account_level4s` | `name`, `code`, `parent_id` FK→level3, **`balance` double default 0**, `status`, `company_id` |
| `account_settings` | `name` (short key, 30), `short` (10), `level` (level1..4), `account_id`, `company_id` |
| `voucher_types` | `name`, `short` (10), `credit_level`/`debit_level` enum(...default `level4`), `credit_id`, `debit_id`, `description`, `company_id` |
| `vouchers` | `voucher_no` (25), `voucher_type_id` FK, `date`, `amount` double, `type` enum(single, multiple, void), `description`, `created_by`, `creator_type` default `user` |
| `transactions` | `transaction_no` (50), `voucher_id` FK→vouchers, `account_id` FK→**account_level4s**, `amount` double, `type` enum(credit, debit), `source`, `description` |

Foreign keys cascade on delete/update. Consequence: deleting a Level 1/2/3 account can
cascade-delete the whole subtree **and** every transaction referencing a Level 4 account.
The delete modal (`accounts/delete_modal.blade.php`) warns about this — any new delete UI
must carry the same warning, and destructive operations should be permission-gated
(`delete account`).

> Note: `transactions.id` is a plain auto-increment and `Transaction` extends
> `Illuminate\Database\Eloquent\Model` (not `UIDModel`) — it is the only model in the
> module without a `uid`. Keep it consistent unless doing a deliberate migration.

### 5.2 Model relationship quirks (existing behavior)

- `AccountLevel1::level2()` → `hasMany`
- `AccountLevel2::level1()` → `hasOne(AccountLevel1, 'id', 'parent_id')` (inverted belongsTo semantics)
- `AccountLevel3::level2()`, `AccountLevel4::level3()` — same inverted `hasOne` pattern
- `AccountLevel4::level3()` is the natural inverse; balance aggregation:
  - `AccountLevel4::calculatebalance()` → own `balance`
  - Levels 1-3 → `sum` of descendant Level 4 balances via nested `whereHas(...)`
- `Transaction::voucher()` → `hasMany` (inverted; `Voucher::transactions()` is the natural side)
- `AccountSetting::account()` → **polymorphic-by-level switch**: returns a `hasOne` to
  `AccountLevel1/2/3/4` depending on `$this->level`, defaulting to Level 4
  (`Models/AccountSetting.php:17-35`)
- `Voucher::creator()` → `morphTo`, registered in the provider's morph map

---

## 6. Code generation rules

`ChatOfAccountService::store()` (`Services/ChatOfAccountService.php:34-107`) computes codes
when a child account is created:

| Parent level | Child | Code algorithm |
|---|---|---|
| Level 1 | Level 2 | `str_pad(max(sibling code)+1, 3, '0', LEFT)`; if no siblings, parent `start_code + 1` |
| Level 2 | Level 3 | strip parent prefix from max sibling code → increment → `parent_code . '-' . str_pad(seq,3,'0',LEFT)` |
| Level 3 | Level 4 | same as Level 3: `parent_code . '-' . str_pad(seq,3,'0',LEFT)` |
| default | (new top-level) | should create a Level 1 with `start_code = max+1`, `end_code = start+99` |

> ⚠ **Known bug:** the `default:` branch calls `$this->level2->create(...)` with
> `start_code` / `end_code` keys, but `account_level2s` has no such columns — this path is
> effectively broken. The UI currently only creates children under an existing Level 1, so
> it is not hit in practice. If you fix it, target `AccountLevel1Repository` and keep the
> `start_code`/`end_code` enum pairing (000-099, 100-199, …).

`update()` / `delete()` resolve the correct repository through
`getRepositoryByLevel($level)` — reuse that helper rather than re-writing the switch.

---

## 7. Routes

`Modules/ChartOfAccounts/routes/web.php`

| Method | URI | Name | Controller@action | Middleware |
|---|---|---|---|---|
| GET | `/accounts` | `accounts.index` | `AccountController@index` | `auth`, `permission:view account`, `plan.feature:chart-of-accounts` |
| POST | `/accounts/store` | `accounts.store` | `@store` | same |
| POST | `/accounts/update/{account}` | `accounts.update` | `@update` | same (`{account}` = **uid**) |
| POST | `/accounts/delete/{account}` | `accounts.delete` | `@delete` | same |
| GET | `/settings/accounts/{company?}` | `account.settings` | `AccountSettingController@index` | `auth`, `permission:account settings`, plan |
| POST | `/settings/accounts/update/{company?}` | `account.settings.update` | `@update` | same |
| * | `/vouchers` (resource) | `vouchers.*` | `VoucherController` | `auth`, `permission:view voucher`, plan |
| * | `/voucher-types` (resource) | `voucher-types.*` | `VoucherTypeController` | `auth`, `permission:view voucher type`, plan |
| GET | `/print/print/{voucher}` | `print.voucher` | `VoucherController@print` | **none** (public print view) |

Conventions:
- Two middleware on every screen: RBAC (`permission:...`) **and** plan gating
  (`plan.feature:chart-of-accounts`, see `docs/subscription-plans.md`).
- Accounts/settings use explicit `POST` verbs for store/update/delete (not resource).
- Vouchers/voucher-types use `Route::resource` (only index/store/update/destroy/print are
  actually implemented; `create/edit/show` are empty stubs).

---

## 8. Controllers & responses

Pattern used by all four controllers (`AccountController`, `AccountSettingController`,
`VoucherController`, `VoucherTypeController`):

- Constructor injection of the module Service(s).
- `index()` is dual-mode:
  - **AJAX** (`$request->ajax()`) → render a partial view (`accounts/tree` or a Yajra
    DataTable) and return `{ success: true, tree: '<html>' }` / JSON rows.
  - **Full page** → `return view('chartofaccounts::<resource>.index')`.
- Mutations wrap the service call in `DB::beginTransaction()` / `commit()` and return the
  standard JSON envelope (§4). Use try/catch + `rollBack()` for anything that can partially fail.
- Controllers never query models or repositories.

**Unrouted / unused surface (don't assume it works):**
- `VoucherController::voidVoucher` (L105) — exists, no route.
- `AccountSettingController::getAccount($short)` — exists, no route.
- `vouchers/create_modal.blade.php` and `vouchers/edit_modal.blade.php` are orphaned and
  actually contain *voucher-type* forms; `voucher_types/index.blade.php` does not include them.

---

## 9. Form Requests

| Class | Rules |
|---|---|
| `StoreAccountRequest` | `name` required, `account` required (parent uid), `status` required |
| `UpdateAccountRequest` | `name` required, `status` required, `level` required |
| `StoreVoucherTypeRequest` | `name` required, `short` required |
| `UpdateVoucherTypeRequest` | `name` required |

Plain string rules, no custom Rule objects. For new endpoints: create a
`StoreXRequest`/`UpdateXRequest` in `Http/Requests/`, put rules + `messages()` there, and
type-hint it in the controller. Add a `company_id`-aware unique rule when uniqueness matters.

---

## 10. Services

All in `Modules/ChartOfAccounts/Services/`. Constructor-inject repositories — never
`new` a repository or query a model.

| Service | Responsibility |
|---|---|
| `ChatOfAccountService` | COA tree read (`getAccounts()`), create/update/delete with code generation, `getRepositoryByLevel()` |
| `AccountSettingService` | Settings CRUD, `getAccount($short)` resolution (throws `AccountSettingNotFoundException` / `AccountSettingNotSetException`), `setAccountBalance($id, $balance)`, default settings map |
| `DefaultAccountService` | Per-company default COA seeding (`initiateAccounts($company)`), idempotent |
| `DefaultVoucherTypeService` | Per-company default voucher types (BRV, CRV, APV, INV, BPV) |
| `VoucherService` | Voucher listing (Yajra), `nextVoucherNo($short)` = `<short>-<count+1>`, `voidVoucher()` (reverses balances, sets `type='void'`) |
| `VoucherTypeService` | Voucher type CRUD |
| `TransactionService` | Journal line creation / queries used by Order & Supplier modules |

**Double-entry sign rules** (`VoucherService::$rules`, L14-35):

| Account type | Debit | Credit |
|---|---|---|
| Asset, Expense | `+` | `-` |
| Liability, Equity, Revenue | `-` | `+` |

Do not hardcode sign logic in controllers or other modules — extend `VoucherService`.

### Account Settings as the integration contract

Other modules must resolve accounts by **setting short code**, never by account id:

```php
$this->accountSettingService->getAccount('SRA');  // Sales/Receivable account → AccountLevel4
```

Default map (`AccountSettingService::getDefaultAccountSettings`, L113):
`SRA`→Payment Recievable, `SPRA`→Sales Person, `PIA`→Products, `PEA`→Vendor Payments,
`OSA`→Office Account, `VPA`→Vendor Payable, `SPA`→Office Account (all `level4`).

Adding a new business flow = add an `account_settings` row + default entry here, then
resolve it through the service.

---

## 11. Repositories

One repository per model, e.g. `Repositories/AccountLevel1Repository.php`:

```php
class AccountLevel1Repository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AccountLevel1());
    }
}
```

- All extend `App\Repositories\BaseRepository`, which provides `query()`, `create()`,
  DataTable column config, etc.
- **This is the only place Eloquent query methods may be called.**
- DataTable column arrays are configured in the constructor for voucher/transaction repos —
  follow that pattern for new list endpoints.

---

## 12. Frontend conventions

Server-rendered Blade, **no SPA**. Theme: Metronic 8 / Bootstrap 5, jQuery, Select2,
Yajra DataTables, toastr.

### View layout per resource

```
Resources/views/<resource>/
├── index.blade.php     # page shell: card, breadcrumbs, action buttons, @include modals, includes _js
├── _js.blade.php       # DataTable init, Select2 init, fetch + inject partial, listens for `table_reload`
├── tree.blade.php      # AJAX partial for accounts (nested <ul>)
├── create_modal.blade.php
├── edit_modal.blade.php
├── delete_modal.blade.php
```

### Shared UI kit
- `<x-breadcrums>`, `<x-icon>`, `<x-account-selector>` (Blade components, root app)
- `resources/views/layouts/script.blade.php` — global AJAX form handler (toasts, 422
  field errors, `table_reload` event) and `.modal-button` modal wiring via
  `data-base-url` + `data-modal_id` attributes.

### How modals work
1. List rows / tree nodes carry `data-modal_id`, `data-modal_name`, `data-modal_status`,
   `data-modal_level`, `data-modal_account`.
2. A `.modal-button` click copies those into the modal's fields and sets
   `form.action = data-base-url + '/' + id`.
3. Form submits with `data-ajax` → global handler → `table_reload` → view refetches.

### `<x-account-selector>`
`app/View/Components/AccountSelector.php` + `resources/views/components/account-selector.blade.php`.
Attributes: `accountLevel` (`all|level1|level2|level3|level4`), `selectLevel`, `name`,
`id`, `selected`, `isRequired`, `class`. Walks `level2Active/level3Active/level4Active`
to render indented options with `data-level`. The `_js.blade.php` writes the selected
option's `data-level` into the hidden `input.level`, which `UpdateAccountRequest` requires.
**Always use this component for account inputs** — do not hand-roll `<select>`s.

### Lists
Yajra server-side DataTables, defined per view, refreshed on the `table_reload` event.

---

## 13. Multi-tenancy, permissions, plan gating

1. **Tenancy** — every model has `company_id` and `scopeFilter()`; services stamp
   `auth()->user()->company_id` on create; `AccountSettingController` also accepts an
   explicit `{company?}` for admins.
2. **RBAC** — permissions seeded in `database/seeders/RolePermissionSeeder.php:110-139`
   (`Accounts`, `Voucher Types`, `Vouchers` sets: view/create/edit/delete/…).
   Guard UI with `@can('create account')` / `hasPermission('edit account')`, guard routes
   with `permission:...`.
3. **Plan gating** — `plan.feature:chart-of-accounts` middleware
   (`app/Http/Middleware/EnsurePlanFeature.php`) maps to
   `Modules/Billing/Services/PlanFeatureService.php:16`:
   `'chart-of-accounts' => ['Accounts', 'Vouchers', 'Voucher Types']`.
   New COA screens must be added to that array if they should be plan-gated.
   Reference: `docs/subscription-plans.md` (Basic plan → 403 on these URIs).

---

## 14. Seeding / per-company provisioning

On company creation, `app/Services/CompanyService.php:148-151` calls (via
`app/Services/DefaultService.php`):

| Method | Service | Notes |
|---|---|---|
| `initiateAccounts($company)` | `DefaultAccountService` | Skips if any `AccountLevel1` exists for the company (idempotent) |
| `initiateVouchers($company)` | `DefaultVoucherTypeService` | BRV, CRV, APV, INV, BPV |
| `initiateAccountSettings($company)` | `AccountSettingService` | Default short-code → account map |

The default tree lives in `DefaultAccountService::getDefaultAccounts()` (L87-282) as a
nested array: 5 top-level types with partial Level 2/3/4 subtrees. Level 4 names other
modules depend on: **Online Gateway, Office Account, Payment Recievable, Products,
Vendor Payable, Others Payment Methods, Sales Person, Vendor Payments**.

Local dev seeding: `database/seeders/AccountsSeeder.php`, `VoucherTypeSeeder.php` (these
call the same services with `$company = null` → global rows).

> When editing the default tree, update **both** `getDefaultAccounts()` and any settings
> map entries that reference the created Level 4 names.

---

## 15. Cross-module integration

```
Order/OrderService        ──┐
Supplier/PurchaseOrderService ─┤── AccountSettingService::getAccount(short)
Supplier/SupplierService  ──┘   VoucherService (create/void/nextVoucherNo)
                                TransactionService (create journal lines)
                                AccountSettingService::setAccountBalance()
```

| Flow | Settings used | Voucher type | Effect |
|---|---|---|---|
| Sales invoice (`OrderService` ~L60-165) | SRA, SPRA, PIA, PEA | `INV` (`multiple`) | debit/credit transactions + balance updates |
| Order payment (~L199-240) | SRA, OSA | `CRV` | |
| Order cancel/refund (~L170-260) | same | voids `INV`/`CRV`/payment | `VoucherService::voidVoucher` |
| Purchase order (`PurchaseOrderService` L82-152) | PIA, VPA | `APV` | |
| Supplier payment (`SupplierService` L125-174) | OSA, VPA | `BPV` | guards `InsufficientSupplierBalanceException` |
| Dashboard (`HomeService`) | — | — | revenue/expense sums from `TransactionRepository` |
| Trial balance (`TrailBalance` component) | — | — | `sum(debit) - sum(credit)` |

**Rule:** modules outside `ChartOfAccounts` must never import
`AccountLevel*` models to post entries. They call `VoucherService` / `TransactionService` /
`AccountSettingService`. (Today `TrailBalance` violates this — fix, don't imitate.)

---

## 16. Future development playbook

### 16.1 Adding a new COA screen (e.g. "Account Groups")

1. **Migration** — `Database/Migrations/2026_xx_xx_create_account_groups_table.php`.
   Include `id`, domain columns, `company_id` FK (nullable, cascade), `status` enum,
   `timestamps`, `uid` (uuid). Run `php artisan migrate`.
2. **Model** — `Models/AccountGroup.php`: `extends UIDModel`, `$guarded = ['id']`,
   `scopeFilter()` by company, relations using the existing style, `scopeActive()` if needed.
3. **Repository** — `Repositories/AccountGroupRepository.php`: 15-line
   `extends BaseRepository` with `parent::__construct(new AccountGroup())`.
   Add DataTable columns in the constructor for list endpoints.
4. **Service** — `Services/AccountGroupService.php`: inject the repository, implement
   business rules, stamp `company_id`, never call Eloquent directly.
5. **Form Requests** — `Http/Requests/StoreAccountGroupRequest.php`,
   `UpdateAccountGroupRequest.php`.
6. **Controller** — `Http/Controllers/AccountGroupController.php`: inject the service,
   dual-mode `index()` (AJAX partial / full view), JSON envelope responses,
   `DB::transaction` around mutations.
7. **Routes** — in `routes/web.php`, inside the `auth` group, with BOTH middlewares:
   ```php
   Route::group(['prefix' => 'account-groups', 'middleware' => [
       'permission:view account group', 'plan.feature:chart-of-accounts'
   ]], function () { ... });
   ```
8. **Views** — `Resources/views/account_groups/{index,_js,create_modal,edit_modal,delete_modal}.blade.php`
   extending `layouts.basic`, using `<x-breadcrumbs>`, `<x-account-selector>` where accounts
   are involved, `data-ajax` forms, `data-modal_*` attributes, `table_reload` listeners.
9. **Permissions** — add `view/create/edit/delete account group` to
   `RolePermissionSeeder` and to the `Accounts` permission group.
10. **Plan gating** — if the screen should be part of `chart-of-accounts`, it is already
    covered by the middleware; verify the label list in `PlanFeatureService` still matches.
11. **Sidebar** — add the entry in `app/Services/AsideSettingService.php` (see L30-36, 163-168)
    following the existing Accounts / Account Settings entries.
12. **Tests** — `Modules/ChartOfAccounts/Tests/` (currently empty; create
    `Feature/AccountGroupTest.php`, refresh DB, assert permission + plan 403s, CRUD flow,
    and that `scopeFilter` prevents cross-company reads).

### 16.2 Adding a new account setting / business flow

1. Add the row to `AccountSettingService::getDefaultAccountSettings()` (and the seeder if needed).
2. Resolve it in the consuming module with `getAccount('XXX')`.
3. Add sign-aware posting through `VoucherService` — never compute debits/credits elsewhere.
4. Ensure a voucher type exists in `DefaultVoucherTypeService` if a new `short` is required.

### 16.3 Adding a new voucher type

1. `DefaultVoucherTypeService` defaults (for new companies) + `database/seeders/VoucherTypeSeeder.php`.
2. `credit_level`/`debit_level` decide which levels are selectable — reuse
   `<x-account-selector accountLevel="...">`.
3. If other modules post to it, route through `VoucherService::nextVoucherNo($short)`.

### 16.4 Conventions checklist (review before merging)

- [ ] Namespace `Modules\ChartOfAccounts\...`, view namespace `chartofaccounts::`
- [ ] Route → Controller → Service → Repository → Model; no bypassing
- [ ] Model extends `UIDModel`, `$guarded = ['id']`, `scopeFilter()` by company
- [ ] Route parameters bind on **`uid`**
- [ ] Both `permission:*` and `plan.feature:chart-of-accounts` on routes
- [ ] AJAX responses use the `{success, message, table_reload}` envelope
- [ ] Forms use `data-ajax`; tables/trees refresh on `table_reload`
- [ ] Account inputs use `<x-account-selector>`; modal rows use `data-modal_*` + `data-base-url`
- [ ] Mutations wrapped in `DB::transaction`
- [ ] No hardcoded URLs (see §17), no direct model queries outside repositories
- [ ] Company stamping + idempotent seeding for any per-company data
- [ ] New permissions added to `RolePermissionSeeder`

---

## 17. Known issues / tech debt (do not replicate)

1. `ChatOfAccountService` misspelling (class + file).
2. `ChatOfAccountService::store()` `default:` branch writes `start_code`/`end_code` into
   `account_level2s`, which has no such columns (§6).
3. Inverted `hasOne` relations in Level2/3/4 and `Transaction::voucher()` being `hasMany`.
4. `Transaction` is the only model not on `UIDModel` (no `uid`).
5. Orphaned `vouchers/create_modal.blade.php` & `vouchers/edit_modal.blade.php`
   (voucher-type forms never `@include`d).
6. Unrouted methods: `VoucherController::voidVoucher`, `AccountSettingController::getAccount`.
7. Broken named routes referenced in views/repos: `voucher-type.store`, `voucher-type.update`,
   `voucher.type.update` — actual names are `voucher-types.store` / `voucher-types.update`.
8. `accounts/tree.blade.php:112` hardcodes `http://localhost/ssss/general/report/5/filter`.
9. Duplicate modal DOM IDs and duplicated inline SVG/styles — see
   `docs/application-ui-improvement.md:399-433`.
10. `TrailBalance` component queries `Transaction` directly (architecture violation).
11. `public/assets/js/custom/utilities/modals/create-account.js` is Metronic boilerplate,
    not wired to this module.
12. `Modules/ChartOfAccounts/Tests/` and `Config/` are empty.

---

## 18. Related documentation

- `docs/architecture-constraints.md` — mandatory layering rules
- `docs/structure.md` — directory/tech-stack reference
- `docs/subscription-plans.md` — `chart-of-accounts` plan lever
- `docs/saas-billing-implementation.md` — morph types on `Voucher`/`Transaction`
- `docs/application-ui-improvement.md` — COA UI defects backlog
- `README.md` — module responsibilities
