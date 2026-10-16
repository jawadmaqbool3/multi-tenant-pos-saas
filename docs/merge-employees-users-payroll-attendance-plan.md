# Plan — Employees/Users Merge + Payroll Module

> **Status:** Draft for review
> **Date:** 2026-10-16
> **Structure:**
> - **Phase 1 (§1–§8):** Merge Employees into Users with Payroll & Attendance toggles
> - **Phase 2 (§9):** New Payroll module for payroll-enabled users
> **Scope (Phase 1):** Employee module, User CRUD, Attendance system, Billing plan limits, Sidebar menus
> **Scope (Phase 2):** New `Payroll` module, allowance & deduction catalogs (CRUD), per-payslip line items, RBAC permissions, sidebar menus, Billing plan feature module

---

## 1. Goal

Unify the two currently separate creation flows (`users` CRUD and `employees` CRUD) into a single **Users** flow. While creating a user, the admin gets two checkboxes:

1. **Enable Payroll** — when checked, a **Monthly Salary** field is required.
2. **Enable Attendance** — when checked, the current attendance system can track that user (report selector + self clock-in).

No full Payroll module is built in this iteration — only the flag and the salary value are stored.

---

## 2. Current State (as-is)

### 2.1 Application structure

- Laravel + `nwidart/laravel-modules` (`^8.0`), modules under `Modules/`.
- Modules: `Billing`, `ChartOfAccounts`, `Employee`, `Inventory`, `Order`, `RBAC`, `Supplier`.
- **There is no payroll module** — no payroll tables, routes, controllers, services, or UI anywhere.
- Employees are **rows in `users`** with the `employee` role (no separate `employees` table).

### 2.2 The two creation flows (duplication to be removed)

| | **Users CRUD** | **Employees CRUD** |
|---|---|---|
| Route | `users` resource (`Modules/Employee/routes/web.php:19`) | `employees` resource (`Modules/Employee/routes/web.php:20`) |
| Controller | `Modules/Employee/Http/Controllers/UserController.php` | `Modules/Employee/Http/Controllers/EmployeeController.php` |
| Service | `Modules/Employee/Services/UserService.php` | `Modules/Employee/Services/EmployeeService.php` |
| Request | `StoreUserRequest` / `UpdateUserRequest` | `StoreEmployeeRequest` / `UpdateEmployeeRequest` |
| Views | `Modules/Employee/Resources/views/users/` | `Modules/Employee/Resources/views/employees/` |
| Role | Admin picks any company role (or hidden fallback) | Forces the `employee` role via `RoleService::getRoleByName('employee')` |
| Side data | None | Creates `employee_personal_information` row (salary + personal details) |
| Plan limit | **Not enforced** (gap) | `ensureWithinLimit('employees', ...)` (`EmployeeService.php:52-53`) |
| Permission | `view user` / `create user` / … | `view employee` / `create employee` / … |

Both flows create a `User` with `company_id = auth()->user()->company_id` and assign exactly **one** role (`RoleService::assignUserRole()` deletes any existing role row first — `Modules/RBAC/Services/RoleService.php:57-65`).

### 2.3 User CRUD — fields currently on the form

Source: `Modules/Employee/Resources/views/users/_form.blade.php`

| Field | Input name | Type | Notes | Lines |
|---|---|---|---|---|
| Avatar | `image` | file (.png/.jpg/.jpeg) + hidden `avatar_remove` | Uploaded to `assets/media/users/profile/`, resized 200×200 | 18–40 |
| Status | `status` | select (`active` / `inactive`) | Default `active`; inactive users cannot log in (`LoginService`) | 59–72 |
| Role | `role` | select (values = role `uid`) | Only rendered when actor has `create user` permission; otherwise hidden input with current role | 74–100 |
| Name | `name` | text | required, min 3 | 110–116 |
| Email | `email` | email | required, unique on `users.email` | 119–127 |
| Password | `password` | password | required on create only | 128–134 |
| Confirm Password | `co_password` | password | required on create, `min:8`, must match `password` | 136–143 |

**There are no checkboxes/flags of any kind on the user form today.**

Validation (`StoreUserRequest.php:14-24`):

```php
'password'    => 'required|same:co_password',
'co_password' => 'required|min:8',
'email'       => 'required|unique:users,email',
'name'        => 'required|min:3',
'role'        => 'required',
'status'      => 'required'
```

### 2.4 Employee CRUD — fields currently on the form

Source: `Modules/Employee/Resources/views/employees/_form.blade.php`

Same as Users (avatar, status, name, email, password, co_password — no role picker) **plus personal info** stored in `employee_personal_information`:

| Field | Column | Type |
|---|---|---|
| Father Name | `father_name` | string, nullable |
| Contact | `contact` | string, nullable |
| **Salary** | `salary` | **string, nullable** |
| Designation | `designation` | string, nullable |
| Address | `address` | string, nullable |

FK: `employee_personal_information.employee_id` → `users.id` (cascade).
Migration: `Modules/Employee/Database/Migrations/2023_01_04_162945_create_employee_personal_information_table.php`.

### 2.5 `users` table schema today

Migration: `Modules/Employee/Database/Migrations/2014_10_12_000000_create_users_table.php`

```php
id (unsignedInteger, PK), name, email (unique), company_id (nullable FK → companies, cascade),
image (nullable), status enum('active','inactive') default 'active', email_verified_at,
password, rememberToken, timestamps, uid (uuid)
```

Model: `Modules/Employee/Models/User.php` — primary key is `uid` (non-incrementing, generated in `boot()`), `$guarded = ['id']`, traits `EmployeeTrait` + `RoleTrait`, company scoping via local `scopeFilter()` (no global scope).

### 2.6 Attendance system today

| Piece | Path | Behavior |
|---|---|---|
| Table | `attendance_histories` (`2023_01_07_191952_...` migration) | `id`, `time` (dateTime), `user_id` (FK → users), `type` enum(`clockin`,`clockout`), timestamps |
| Model | `Modules/Employee/Models/AttendanceHistory.php` | Note: `user()` relation is missing `return` (existing bug) |
| Service | `Modules/Employee/Services/AttendanceHistoryService.php` | `store()` auto-toggles clockin/clockout for **`auth()->user()` only** (self service, L45–49) |
| Report selector | `AttendanceHistoryService::getEmployees()` L25–30 | Users with role `employee` — **no company scoping, no status filter** (cross-tenant bug) |
| Report fetch | `getUserByUid()` L20–23 | `firstOrFail` by uid — **no company scoping** (cross-tenant bug) |
| Routes | `attendance` resource (`routes/web.php:21`) | Gated by `view employee attendance report` |
| Dashboard widget | `resources/views/dashboard/dashboard.blade.php:244-280` | Clock in/out button, gated by `dashboard employee mark attendance` permission |
| Reports UI | `Resources/views/reports/employee/` | Month picker + employee selector → per-day present/absent table |

**No admin UI exists to clock a different user in/out** — marking is always self-service; the report is read-only.

### 2.7 Sidebar / menus today

- Built from `AsideSettingService::$menuLinks` (`app/Services/AsideSettingService.php`) — used for **new** companies (`CompanyService::store`, `CompanySeeder`).
- **Persisted per company** as JSON in `settings.asidebar`; rendered by `app/View/Components/AsideMenuLinks.php:133`. Admin can reorder/edit via the Aside Settings page.
- `database/seeders/SettingSeeder.php` seeds a default JSON (absolute URLs incl. `.../employees`).
- Relevant entries:
  - **HR → Employees** → `route('employees.index')`, permission `view employee` (`AsideSettingService.php:107-111`, `SettingSeeder.php:120-124`)
  - **Users → User** → `route('users.index')`, permission `view user`
  - **Reports → Employee Attendance** → `route('attendance.index')`, permission `view employee attendance report`

### 2.8 Billing plan limits today

- `Modules/Billing/Services/PlanLimitService.php` — `ensureWithinLimit('employees', $company)`.
- **Usage count** (`employeesUsage()`, L121–128): company users having the `employee` role.
- Enforced **only** in `EmployeeService::store()`; `UserService::store()` never checks limits.

### 2.9 Known gaps this plan addresses

1. Two overlapping flows that both create company users (duplication).
2. No payroll concept at all; salary only reachable through the Employees form.
3. Attendance report selector is **not company-scoped** and not status-filtered (cross-tenant leak).
4. `UserService` bypasses the plan's employee limit.
5. Inactive users appear in the attendance selector.

---

## 3. Confirmed Decisions

| # | Decision | Choice |
|---|---|---|
| 1 | Fate of Employees CRUD | **Merge fully into Users** — remove employees routes/controller/service/views; single Users flow |
| 2 | Payroll scope this iteration | **Flag + monthly salary only** (no Payroll module) |
| 3 | Flag storage | **Boolean columns on `users`** (`payroll_enabled`, `attendance_enabled`) — independent of role (users can hold only ONE role) |
| 4 | Existing users | **No backfill** — all existing users start with both flags off |
| 5 | Extra personal-info fields (`father_name`, `contact`, `designation`, `address`) | **Drop from UI** — data stays in DB but is no longer editable |
| 6 | Plan limit | **Enforce `employees` limit** for flagged users; unflagged users aren't counted |

---

## 4. Target State (to-be)

```
Users CRUD (single flow)
├── existing fields: avatar, status, role, name, email, password
├── ☑ Enable Payroll  → shows required "Monthly Salary" input
│     stores: users.payroll_enabled = 1
│              employee_personal_information.salary = <value>
├── ☑ Enable Attendance → (no extra input)
│     stores: users.attendance_enabled = 1
│
├── counts toward plan 'employees' limit when either flag is on
└── Users list shows Payroll/Attendance badges

Attendance system
├── report selector = users WHERE attendance_enabled = 1 (company-scoped)
├── self clock-in allowed only when auth()->user()->attendance_enabled
└── dashboard clock widget hidden when attendance not enabled

Employees CRUD, routes, views, HR → Employees menu: REMOVED
```

---

## 5. Detailed Changes

### 5.1 Schema — new migration

**File (new):** `Modules/Employee/Database/Migrations/2026_10_16_000001_add_payroll_and_attendance_flags_to_users_table.php`
(Conventions: non-namespaced class, `Schema::table('users', ...)`, `down()` = `dropColumn`.)

```php
$table->boolean('payroll_enabled')->default(false);
$table->boolean('attendance_enabled')->default(false);
```

No data backfill (decision 4).

**File (new):** `database/migrations/2026_10_16_000002_remove_employees_link_from_asidebar.php`
- Data migration: for every row in `settings`, decode `asidebar` JSON and remove links whose path ends with `/employees` (title `Employees`), re-encode. Ensures existing companies lose the dead HR → Employees menu entry after the route is removed.

### 5.2 Model

**`Modules/Employee/Models/User.php`** — add casts:

```php
'payroll_enabled' => 'boolean',
'attendance_enabled' => 'boolean',
```

(`$guarded = ['id']` already allows the new columns; `EmployeeTrait::empInfo()` already provides the `hasOne` relation used to prefill salary.)

### 5.3 Validation

**`Modules/Employee/Http/Requests/StoreUserRequest.php`** and **`UpdateUserRequest.php`** — add:

```php
'payroll_enabled'    => 'nullable|boolean',
'attendance_enabled' => 'nullable|boolean',
'salary'             => 'required_if:payroll_enabled,1|nullable|numeric|min:0',
```

Form posts a hidden `0` + checkbox `1` for each flag, so `false` is always sent.

### 5.4 Form UI

**`Modules/Employee/Resources/views/users/_form.blade.php`** — new **"Employment"** card in the left sidebar column (below Status, above Role):

- Checkbox `payroll_enabled` — label **"Enable Payroll"**
  - Salary input `salary` (`type="number"`, `min="0"`, `step="any"`), shown only when payroll checked; prefilled with `@$user->empInfo->salary`
- Checkbox `attendance_enabled` — label **"Enable Attendance"**
  - Help text: "Allows this user to be tracked by the attendance system (clock in/out and attendance reports)."
- Each checkbox preceded by `<input type="hidden" name="<flag>" value="0">` so unchecked posts `0`.

**`Modules/Employee/Resources/views/users/_js.blade.php`** — toggle salary field visibility + `required` attribute when the payroll checkbox changes; run once on page load so edit forms open in the correct state. (The global AJAX handler in `resources/views/layouts/script.blade.php` already serializes checkboxes/hidden inputs via `FormData`.)

### 5.5 Service — `Modules/Employee/Services/UserService.php`

- Constructor: inject `EmployeePersonalInformationRepository` (already exists, used by old `EmployeeService`) — and `App\Repositories\CompanyRepository` (existing).
- Add `company()` helper that resolves via the repository (not a direct `Company::where()` like the old `EmployeeService`): `return auth()->user()->company_id ? $this->companies->find(auth()->user()->company_id) : null;`
- **`store()`**:
  1. If `payroll_enabled` or `attendance_enabled` → `app(PlanLimitService::class)->ensureWithinLimit('employees', $this->company())`
  2. Add both flags to the `users->create([...])` payload (cast from request)
  3. If payroll on → `$this->employeeInfo->create(['employee_id' => $user->id, 'salary' => $request->salary])`
- **`update()`**:
  1. Enforce the limit only on **boundary crossing** (user previously had neither flag, request enables at least one) — avoids blocking unrelated edits when already at/over the cap
  2. Persist both flags in the `update([...])` payload
  3. If payroll on → `updateOrCreate` the personal-info row keyed by `employee_id` with `salary`
  4. If payroll turned off → leave stored salary data as-is (non-destructive; the flag governs behavior)
- `UserController::store()/update()` already wrap everything in `DB::beginTransaction()` — no controller change needed.

### 5.6 Attendance integration

**`Modules/Employee/Services/AttendanceHistoryService.php`**:

- `getEmployees()` → selector becomes flag-driven **and** company-scoped (fixes cross-tenant bug):

  ```php
  return $this->users->query()->filter()->where('attendance_enabled', true)->get();
  ```

- `getUserByUid()` → add `filter()` so report details can't be fetched across tenants.

**`Modules/Employee/Http/Controllers/AttendanceHistoryController.php`** — in `store()`, guard before calling the service:

```php
if (!auth()->user()->attendance_enabled) {
    return response(['success' => false, 'message' => 'Attendance is not enabled for your account']);
}
```

**`resources/views/dashboard/dashboard.blade.php:244`** — nest `@if(auth()->user()->attendance_enabled)` inside the existing `@can('dashboard employee mark attendance')` so the clock-in card is hidden for non-attendance users.

### 5.7 Billing — `Modules/Billing/Services/PlanLimitService.php`

Add a query method to `Modules/Employee/Repositories/UserRepository.php` (the data-access layer):

```php
public function countFlaggedForCompany(int $companyId): int
{
    return $this->query()
        ->where('company_id', $companyId)
        ->where(fn ($q) => $q->where('payroll_enabled', true)->orWhere('attendance_enabled', true))
        ->count();
}
```

Then replace the role-based usage count (`employeesUsage()`, L121–128) with a **repository call** — inject `Modules\Employee\Repositories\UserRepository` into `PlanLimitService` (constructor) and:

```php
return $this->users->countFlaggedForCompany($company->id);
```

Update the method docblock accordingly. (This also removes the direct `User::where()` from the Service, aligning with `docs/architecture-constraints.md`.) The pre-existing `ownerRoleId()` model/`DB::table` queries are left as tech debt, but new code must not imitate them.

### 5.8 Remove the Employees CRUD (the merge)

| Action | Item |
|---|---|
| Edit | `Modules/Employee/routes/web.php` — remove `Route::resource('employees', ...)` (L20) and the `EmployeeController` import (L4) |
| Delete | `Modules/Employee/Http/Controllers/EmployeeController.php` |
| Delete | `Modules/Employee/Services/EmployeeService.php` |
| Delete | `Modules/Employee/Http/Requests/StoreEmployeeRequest.php`, `UpdateEmployeeRequest.php` |
| Delete | `Modules/Employee/Resources/views/employees/` (index, create, edit, `_form`, `_js`) |
| Edit | `Modules/Employee/Repositories/UserRepository.php` — remove `employeeTable()` (only consumer was `EmployeeService`) |
| Edit | `app/Services/AsideSettingService.php` — remove HR → Employees entry (L107–111); HR keeps Suppliers |
| Edit | `database/seeders/SettingSeeder.php` — remove the Employees entry from the default JSON (L120–124) |
| New | data migration (§5.1) strips the entry from existing `settings.asidebar` rows |

**Intentionally left alone:**
- RBAC permission rows (`view/create/edit/delete employee`) remain seeded — now orphaned but harmless. `view employee attendance report` and `dashboard employee mark attendance` stay in use.
- `employee_personal_information` table and existing rows (salary data preserved; other columns become UI-less).
- Old `employee`-role assignments on existing users (no backfill / no role changes).

### 5.9 Users list — Flags column

- `Modules/Employee/Repositories/UserRepository.php` — add an `addColumns` entry (same pattern as `action`) rendering Payroll/Attendance badges; add to `$dataTableRawColumns`.
- `Modules/Employee/Resources/views/users/index.blade.php` — add `<th>Flags</th>` before Actions + matching entry in the DataTable `columns` array.

### 5.10 Docs cleanup

- `docs/structure.md:137` — remove `EmployeeController.php` from the controller list.
- `docs/subscription-plans.md` — employee limit now enforced in `UserService`, counted by flags (not role).
- `docs/saas-billing-implementation.md` — same references (limit wiring section).

---

## 6. Implementation Order

1. Migration + `User` casts (§5.1–5.2)
2. Form requests / validation (§5.3)
3. Form UI + JS toggle (§5.4)
4. `UserService` store/update + plan limit wiring (§5.5)
5. Attendance changes (§5.6)
6. `PlanLimitService::employeesUsage()` (§5.7)
7. Remove Employees CRUD + sidebar + data migration (§5.8)
8. Users list Flags column (§5.9)
9. Docs (§5.10)
10. Verify (§7)

---

## 7. Verification

Repo has only example tests (`tests/Feature|Unit/ExampleTest.php`), so verification is `php artisan migrate` + `php artisan test` + manual QA:

- [ ] `php artisan migrate` applies both new migrations cleanly
- [ ] Create user, **no flags** → no salary field required; not in attendance selector; not counted in plan usage
- [ ] Create user, **Payroll ON** → salary shown/required; validation error `required_if` when empty; `employee_personal_information` row created on success
- [ ] Create user, **Attendance ON** → user appears in attendance report selector (own company only; platform admin sees all)
- [ ] Edit user → both checkboxes prefill correctly (payroll reflects stored salary via `empInfo`)
- [ ] Turn Attendance OFF → user disappears from selector; self clock-in returns "Attendance is not enabled…" message; dashboard widget hidden
- [ ] At plan cap → enabling either flag throws `PlanLimitExceededException` (user-visible message); unrelated edits of an already-flagged user still save
- [ ] `/employees` routes → 404; HR → Employees menu gone for new companies (template) and existing companies (data migration)
- [ ] Flags column badges render on the users list
- [ ] Regression: login, role assignment, user delete (cascade removes personal info + user_roles), attendance report rendering

---

## 8. Consequences & Risks (accepted)

1. **No backfill** → existing `employee`-role users stop appearing in the attendance selector and stop counting toward plan limits until an admin edits them and enables the flags. Plan usage counts may **drop** for existing companies.
2. **Other personal-info fields** (`father_name`, `contact`, `designation`, `address`) remain in the DB but have no UI anymore.
3. **Orphaned permissions** (`view/create/edit/delete employee`) stay in the seeder/DB; nothing references them after the merge.
4. **Permission gating shift** — anyone who previously only had `view employee` (not `view user`) loses access to the merged list; access now follows `view user` / `create user` / `edit user`.
5. **Flags are visible to anyone who can create/edit users** — no dedicated `manage payroll` permission is introduced in this iteration (can be added later if needed).
6. Salary is stored as a **string** column (pre-existing schema); validation enforces numeric input, but the column type is unchanged.

---

# PHASE 2 — Payroll Module

## 9. Phase 2 — Payroll Module (employees with payroll enabled)

> **Depends on:** Phase 1 (the `users.payroll_enabled` flag and `employee_personal_information.salary`).
> Phase 2 can only be built after Phase 1 is in place.

### 9.1 Goal

Create a dedicated **`Payroll` module** that generates and manages **monthly payroll runs** for the company's users with `payroll_enabled = true`. Each run produces one **payslip per eligible employee**, snapshotting their monthly salary from `employee_personal_information.salary`.

Pay components are **configurable catalogs** with full CRUD:
- **Allowances** — company-defined types (e.g. House Rent, Medical, Transport).
- **Deductions** — company-defined types (e.g. Tax, Provident Fund, Fine).

Each payslip carries **line items** referencing these catalogs with an amount (`base + Σ allowances − Σ deductions = net`), editable while the run is a draft. The run lifecycle is `draft → processed → paid`.

**Explicitly out of scope for this phase** (candidates for Phase 3):
- **Automatic/statutory** computation (the deduction catalog is manual data entry, not an auto-calculated tax engine)
- Loans, advances, reimbursements
- Attendance- or absence-based automatic deductions
- PDF generation (payslips are printed via the browser's print dialog)
- Multi-currency conversion, bank/transfer file export

### 9.2 Module layout (nwidart convention, mirrors `Modules/Supplier`)

```
Modules/Payroll/
├── module.json                                  # providers: Modules\Payroll\Providers\PayrollServiceProvider
├── Config/config.php                            # module:make default
├── Providers/PayrollServiceProvider.php         # loadViewsFrom 'payroll', loadMigrationsFrom, morphMap, routes
├── Database/Migrations/
│   ├── 2026_10_16_000101_create_payrolls_table.php
│   ├── 2026_10_16_000102_create_payslips_table.php
│   ├── 2026_10_16_000103_create_allowances_table.php
│   ├── 2026_10_16_000104_create_deductions_table.php
│   ├── 2026_10_16_000105_create_payslip_allowances_table.php
│   └── 2026_10_16_000106_create_payslip_deductions_table.php
├── Http/
│   ├── Controllers/PayrollController.php
│   ├── Controllers/AllowanceController.php
│   ├── Controllers/DeductionController.php
│   └── Requests/StorePayrollRequest.php, UpdatePayslipRequest.php, UpdatePayrollStatusRequest.php,
│                StoreAllowanceRequest.php, UpdateAllowanceRequest.php,
│                StoreDeductionRequest.php, UpdateDeductionRequest.php
├── Models/Payroll.php, Models/Payslip.php, Models/Allowance.php, Models/Deduction.php,
│         Models/PayslipAllowance.php, Models/PayslipDeduction.php
├── Repositories/PayrollRepository.php, PayslipRepository.php,
│                PayslipAllowanceRepository.php, PayslipDeductionRepository.php,
│                AllowanceRepository.php, DeductionRepository.php
├── Services/PayrollService.php, Services/PayrollPostingService.php,
│            Services/AllowanceService.php, Services/DeductionService.php
├── Resources/views/
│   ├── payrolls/index.blade.php, create.blade.php, show.blade.php, _js.blade.php
│   ├── payrolls/print/payslip.blade.php
│   ├── allowances/index.blade.php, _form.blade.php, create.blade.php, edit.blade.php, _js.blade.php
│   └── deductions/index.blade.php, _form.blade.php, create.blade.php, edit.blade.php, _js.blade.php
└── routes/web.php
```

Registration:
- Scaffold with `php artisan module:make Payroll` **or** hand-create following `Modules/Supplier`.
- Add `"Payroll": true` to `modules_statuses.json`.
- `module.json` lists the provider; `composer.json` already maps `Modules\` → `Modules/` (no change).

**Conventions carried over from existing modules:**
`UIDModel` base (uid PK), `scopeFilter()` for company scoping, `BaseRepository` (+ DataTable), Service injected with `app()` fallback, controller wraps writes in `DB::beginTransaction()`, JSON responses `{success, message, redirect/table_reload, url}`.

**Architecture compliance (mandatory — `docs/architecture-constraints.md`):** the Payroll module follows `Request → Route → Controller → Service → Repository → Model` with no bypassing.

- **Controllers** call Services/Form Requests only — no `Model::query()`/`find()`/`where()`, no Repository imports; mutations wrapped in `DB::beginTransaction()`.
- **Services** orchestrate business logic and call **Repositories** for every DB read/write; they must not call Eloquent query methods (`Model::where()`, relation lazy-loads used as queries, `->sum()` on a relation) directly. `VoucherService`/`TransactionService`/`AccountSettingService` are Services, so calling them from a Service is allowed.
- **Repositories** are the only layer touching Eloquent; every new model gets a `BaseRepository` subclass carrying its query methods and DataTable columns.
- **Migrations/seeders/data-migrations** are provisioning scripts (not app layers) and may query the DB directly — the constraints apply to Route/Controller/Service/Repository code only.
- **Existing violation to not copy:** `Modules/Employee/Services/EmployeeService::company()` calls `Company::where()` and `PlanLimitService::employeesUsage()` calls `User::where()` — new code resolves these through `CompanyRepository` / `UserRepository` (see §5.5, §5.7, §9.5).

### 9.3 Database schema

**`payrolls`** — one row per company per month (the "run")

| Column | Type | Notes |
|---|---|---|
| `id` | unsignedInteger, autoIncrement | |
| `uid` | uuid | route key (`UIDModel`) |
| `company_id` | unsignedInteger FK → `companies` | cascade |
| `period` | date | first day of the payroll month |
| `status` | enum(`draft`,`processed`,`paid`) | default `draft` |
| `total_employees` | unsignedInteger | default 0 |
| `total_base`, `total_allowances`, `total_deductions`, `total_net` | decimal(12,2) | default 0 |
| `notes` | text, nullable | |
| `created_by` | unsignedInteger FK → `users`, nullable | `nullOnDelete` |
| `voucher_id` | unsignedInteger, nullable, index | COA `vouchers` id created when the run is marked `paid`; plain integer (no FK/relation, so no COA model is imported) — `null` = not yet posted (§9.10) |
| `processed_at`, `paid_at` | timestamp, nullable | |
| `timestamps` | | |
| | | **unique(`company_id`, `period`)** |

**`payslips`** — one row per employee per run

| Column | Type | Notes |
|---|---|---|
| `id`, `uid` | unsignedInteger + uuid | |
| `payroll_id` | unsignedInteger FK → `payrolls` | cascade |
| `employee_id` | unsignedInteger FK → `users` | cascade |
| `company_id` | unsignedInteger FK → `companies` | cascade (denormalized for scoping) |
| `base_salary` | decimal(12,2) | **snapshot** of salary at generation |
| `total_allowances` | decimal(12,2) | default 0 — **cached sum** of line items |
| `total_deductions` | decimal(12,2) | default 0 — **cached sum** of line items |
| `net_salary` | decimal(12,2) | `base_salary + total_allowances − total_deductions` |
| `notes` | text, nullable | |
| `timestamps` | | **unique(`payroll_id`, `employee_id`)** |

**`allowances`** — company-scoped catalog (CRUD)

| Column | Type | Notes |
|---|---|---|
| `id`, `uid` | unsignedInteger + uuid | |
| `company_id` | unsignedInteger FK → `companies` | cascade |
| `name` | string | e.g. "House Rent Allowance" |
| `status` | enum(`active`,`inactive`) | default `active` |
| `timestamps` | | **unique(`company_id`, `name`)** |

**`deductions`** — company-scoped catalog (CRUD)

| Column | Type | Notes |
|---|---|---|
| `id`, `uid` | unsignedInteger + uuid | |
| `company_id` | unsignedInteger FK → `companies` | cascade |
| `name` | string | e.g. "Tax", "Provident Fund" |
| `status` | enum(`active`,`inactive`) | default `active` |
| `timestamps` | | **unique(`company_id`, `name`)** |

**`payslip_allowances`** — line items (payslip → allowance, with amount)

| Column | Type | Notes |
|---|---|---|
| `id`, `uid` | unsignedInteger + uuid | |
| `payslip_id` | unsignedInteger FK → `payslips` | cascade |
| `allowance_id` | unsignedInteger FK → `allowances` | cascade |
| `amount` | decimal(12,2) | default 0 |
| `timestamps` | | **unique(`payslip_id`, `allowance_id`)** |

**`payslip_deductions`** — line items (payslip → deduction, with amount)

| Column | Type | Notes |
|---|---|---|
| `id`, `uid` | unsignedInteger + uuid | |
| `payslip_id` | unsignedInteger FK → `payslips` | cascade |
| `deduction_id` | unsignedInteger FK → `deductions` | cascade |
| `amount` | decimal(12,2) | default 0 |
| `timestamps` | | **unique(`payslip_id`, `deduction_id`)** |

Money is stored as `decimal(12,2)` (new tables). The source `salary` is a string column — cast + round to 2 decimals at snapshot time. Payslip totals are **cached** copies of the line-item sums (recomputed on every line-item change and before any status transition); the line items remain the source of truth.

### 9.4 Models & repositories

- `Payroll extends App\Models\UIDModel` — `$guarded = ['id']`; casts: `period` date, status string, totals decimal; relations `payslips()` hasMany, `company()` belongsTo, `creator()` belongsTo User; `scopeFilter()` → `where('company_id', auth()->user()->company_id)` (no admin bypass — payroll is tenant-only data).
- `Payslip extends App\Models\UIDModel` — relations `payroll()`, `employee()` (User), `company()`, `allowanceItems()` hasMany `PayslipAllowance`, `deductionItems()` hasMany `PayslipDeduction`; `scopeFilter()` by `company_id`.
- `Allowance` / `Deduction` extend `App\Models\UIDModel` — `$guarded = ['id']`, `status` cast, `scopeFilter()` by `company_id`; `payslipItems()` hasMany.
- `PayslipAllowance` / `PayslipDeduction` extend `App\Models\UIDModel` — relations `payslip()`, `allowance()` / `deduction()`; `amount` cast decimal.
- Repositories extend `BaseRepository`; DataTable columns/formatting and permission-gated action buttons live here, mirroring `UserRepository`. **Every DB read/write in the Payroll services goes through these** (`docs/architecture-constraints.md`):
  - `PayrollRepository` / `PayslipRepository` (runs & payslips)
  - `PayslipAllowanceRepository` / `PayslipDeductionRepository` (line items; also used by the catalog services' delete guards)
  - `AllowanceRepository` / `DeductionRepository` (catalog CRUD, DataTable index with `name`, `status`, actions)
- **Eligible-employee lookup** reuses `Modules\Employee\Repositories\UserRepository` (injected into `PayrollService`); add a scope-backed method rather than querying `User` in the service:
  ```php
  public function payrollEnabledForCompany(int $companyId)
  {
      return $this->query()
          ->where('company_id', $companyId)
          ->where('payroll_enabled', true)
          ->where('status', 'active')
          ->get();
  }
  ```
  (`empInfo` salary is read through the existing `EmployeePersonalInformationRepository`.)

### 9.5 Services

All services below receive their repositories via constructor injection and perform **no** direct Eloquent calls (`docs/architecture-constraints.md`); COA services (`AccountSettingService`, `VoucherService`, `TransactionService`) are injected where posting is needed.

`Modules/Payroll/Services/PayrollService.php` — injects `PayrollRepository`, `PayslipRepository`, `PayslipAllowanceRepository`, `PayslipDeductionRepository`, `Employee\Repositories\UserRepository`, `Employee\Repositories\EmployeePersonalInformationRepository`, `App\Repositories\CompanyRepository`, `PayrollPostingService`.

- `company()` helper returning the auth user's `Company` via `CompanyRepository::find(auth()->user()->company_id)`; **guard:** if `company_id` is null (platform admin), abort with a clear error — payroll is tenant-only.
- **`generate($period)`** (called from `store`):
  1. Reject duplicate: `payrollRepository->query()->where('company_id', company)->where('period', $period)->exists()` → `ValidationException`.
  2. Eligible employees = `userRepository->payrollEnabledForCompany($companyId)` (§9.4) — never a `User::where()`.
  3. Snapshot each into a `Payslip` via `payslipRepository->create([...])` (`base_salary` from the user's `empInfo->salary` read through `EmployeePersonalInformationRepository`, no line items yet, `net = base`).
     - Employees with `payroll_enabled` but **no salary** are **skipped** and returned so the UI can list them ("Skipped: no salary set — Jane Doe").
  4. Create the `Payroll` via `payrollRepository->create([... 'draft', 'created_by' => auth id ...])`, then `recomputeTotals($payroll)`.
- **`syncPayslipItems($payslip, array $allowanceItems, array $deductionItems)`** — only while the parent run is `draft`. Replaces the payslip's line items from arrays of `{id: <allowance|deduction uid>, amount}` rows (blank rows skipped) using `payslipAllowanceRepository` / `payslipDeductionRepository` (delete-then-create, or upsert); recomputes the payslip's `total_allowances`/`total_deductions`/`net_salary`, then `recomputeTotals($payroll)`.
- **`updatePayslipNotes($payslip, $notes)`** — draft only; `payslipRepository->update($payslip->id, ['notes' => $notes])`.
- **`updateStatus($payroll, $status)`** — allowed transitions only: `draft → processed` (sets `processed_at`), `processed → paid` (sets `paid_at`). Recomputes totals first. Invalid transitions rejected. On the `processed → paid` transition, within the same DB transaction, calls **`PayrollPostingService::postVoucher($payroll)`** (§9.10) and stores `voucher_id` via `payrollRepository`; if COA settings are missing the whole transition is rolled back with a clear error.
- **`destroy($payroll)`** — **draft only**; deletes payslips + line items through their repositories (or relies on DB cascade; service still guards status).
- **`recomputeTotals($payroll)`** — aggregates `total_employees` and the four run totals from the payslip rows via `payslipRepository` (e.g. an aggregate/`sum` method on the repository — not `$payroll->payslips()->sum()`), then `payrollRepository->update($payroll->id, [...])`.

`Modules/Payroll/Services/AllowanceService.php` and `DeductionService.php` (identical shape) — inject their own repository (`AllowanceRepository` / `DeductionRepository`) plus `PayslipAllowanceRepository` / `PayslipDeductionRepository` for the delete guard.

- `getList()` — DataTable/company-scoped catalog listing via the repository.
- `store($request)` — create catalog entry (`name`, `status`) scoped to `company_id` via repository; reject duplicate name per company.
- `update($request, $entry)` — update `name`/`status` via repository; reject duplicate name.
- `delete($entry)` — check references via the line-item repository (guard: warn/block if attached to a **non-draft** payroll — see §9.14), then `repository->delete($entry->id)`.
- Both services only operate on entries belonging to the auth user's company.

`Modules/Payroll/Services/PayrollPostingService.php` — injects `PayslipRepository`, the COA services (`AccountSettingService`, `VoucherService`, `TransactionService`), and `PayrollRepository`.

- **`postVoucher($payroll)`** — the **only** place payroll touches the ledger. Invoked inside `PayrollService::updateStatus()`'s transaction on the `processed → paid` transition, and idempotent (returns early when `$payroll->voucher_id` is set). Iterates payslips via `PayslipRepository`; resolves `SEA`/`SDP`/`OSA` via `AccountSettingService`, builds one `SLV` voucher through `VoucherService`, writes `TransactionService` rows, updates balances through `VoucherService::applyBalance()`, and stores `voucher_id` via `PayrollRepository` — it imports **no** `AccountLevel1..4` model and runs no Model query (§9.10).

### 9.6 Controllers & routes

**`Modules/Payroll/Http/Controllers/PayrollController.php`**

| Action | Method | Notes |
|---|---|---|
| `index` | GET | AJAX → DataTable of runs; else `payroll::payrolls.index` |
| `create` | GET | month picker form |
| `store` | POST | `generate()`; returns success + redirect to `payrolls.show` (or the skipped-employees warning) |
| `show` | GET | run detail: summary + payslips table (line items editable while draft) |
| `updatePayslip` | PATCH | `syncPayslipItems()` — replace the payslip's allowance/deduction line items (draft only) |
| `updateStatus` | PATCH | process / mark paid |
| `destroy` | DELETE | delete draft run |
| `printPayslip` | GET | print-friendly single payslip |

**`Modules/Payroll/Http/Controllers/AllowanceController.php`** / **`DeductionController.php`** — standard resource controllers (`index`, `create`, `store`, `edit`, `update`, `destroy`), mirroring `UserController`/`EmployeeController` (DB transaction wrapper, JSON responses, DataTable on AJAX `index`).

`Modules/Payroll/routes/web.php`:

```php
Route::group(['middleware' => ['auth', 'plan.feature:payroll']], function () {
    Route::resource('payrolls', PayrollController::class)->middleware('permission:view payroll');
    Route::resource('allowances', AllowanceController::class)->middleware('permission:view allowance');
    Route::resource('deductions', DeductionController::class)->middleware('permission:view deduction');

    Route::patch('payrolls/{payroll}/payslips/{payslip}',
        [PayrollController::class, 'updatePayslip'])
        ->name('payrolls.payslips.update')->middleware('permission:edit payroll');

    Route::patch('payrolls/{payroll}/status',
        [PayrollController::class, 'updateStatus'])
        ->name('payrolls.status.update')->middleware('permission:edit payroll');

    Route::get('print/payslip/{payslip}',
        [PayrollController::class, 'printPayslip'])
        ->name('payslip.print')->middleware('permission:view payroll');
});
```

Both gates apply: `plan.feature:payroll` (`EnsurePlanFeature`) rejects companies whose plan excludes the `Payroll` module, and `permission:*` (`EnsurePermission`) requires the RBAC permission (`docs/coa.md` §13).

**Requests:**
- `StorePayrollRequest`: `period` => `required|date_format:Y-m` + unique-per-company (via `Rule::unique('payrolls')->where('company_id', auth company)`).
- `UpdatePayslipRequest`: `allowances` / `deductions` => `array`; `allowances.*.allowance` => `nullable|exists:allowances,uid`; `allowances.*.amount` => `nullable|numeric|min:0`; same for `deductions.*.deduction`; `notes` nullable.
- `UpdatePayrollStatusRequest`: `status` => `required|in:processed,paid`.
- `StoreAllowanceRequest` / `UpdateAllowanceRequest`: `name` => `required|min:2` (unique per company), `status` => `required|in:active,inactive`.
- `StoreDeductionRequest` / `UpdateDeductionRequest`: same as allowance.
- Button-level gating via `@can('...')`, matching the existing custom `@can` directive (wraps `hasPermission`).

### 9.7 Permissions & RBAC provisioning

New tenant (non-platform) permission module **`Payroll`** with twelve permissions:

- Payroll runs: `view payroll`, `create payroll`, `edit payroll`, `delete payroll`
- Allowances: `view allowance`, `create allowance`, `edit allowance`, `delete allowance`
- Deductions: `view deduction`, `create deduction`, `edit deduction`, `delete deduction`

1. **`database/seeders/RolePermissionSeeder.php`** — add a `"Payroll" => [...]` group to the (non-platform) `$modules` array so fresh installs/template seeding get it.
2. **New data migration** `database/migrations/2026_10_16_000003_provision_payroll_permissions.php` (for **existing** companies, since company-scoped permission copies are normally only created at company creation or via Settings):
   - `firstOrCreate` the template `permission_modules` row `Payroll` (`is_platform = false`).
   - `firstOrCreate` the 12 template permissions (`company_id = null`, `is_platform = false`).
   - For **every existing company**: `firstOrCreate` company-scoped copies (`company_id`, same `module_id`, `is_platform = false`).
   - Attach those copies to each company's **owner role** (earliest user's role — same lookup intent as `PlanLimitService::ownerRoleId()`) via `permission_roles`, so payroll is usable out of the box; admins can re-assign via the Roles UI afterwards. (This is a **data migration**, so direct DB queries are acceptable here — `docs/architecture-constraints.md` governs Route/Controller/Service/Repository code, not provisioning scripts.)
   - `down()`: remove the company-scoped copies + template permissions + module (guarded).
3. **New companies** — `CompanyService::store()` already clones any selected non-platform template permissions, so the platform admin can tick Payroll at creation. `CompanySeeder` clones **all** non-platform template permissions automatically (no change needed).

### 9.8 Sidebar menu

- **`app/Services/AsideSettingService.php`** — add a new top-level section (before/after `HR`, e.g. icon `bi bi-cash-stack`) with:
  - `Payroll → "Payroll Runs"`, permission `view payroll`, `route('payrolls.index')`
  - `Payroll → "Allowances"`, permission `view allowance`, `route('allowances.index')`
  - `Payroll → "Deductions"`, permission `view deduction`, `route('deductions.index')`
- **`database/seeders/SettingSeeder.php`** — add the matching entries to the default `asidebar` JSON (fresh installs).
- **New data migration** `database/migrations/2026_10_16_000004_append_payroll_menu_to_asidebar.php` — idempotently append the Payroll section (with its three links) to every existing company's `settings.asidebar` (skip if a section named `Payroll` or links pointing at `/payrolls` / `/allowances` / `/deductions` already exist).

### 9.9 Views (Metronic UI patterns)

- **`payrolls/index.blade.php`** — server-side DataTable: Period · Status (badge) · Employees · Base · Allowances · Deductions · Net · Actions (Show, Delete). Toolbar "New Payroll Run" gated by `@can('create payroll')`.
- **`payrolls/create.blade.php`** — card with a `type="month"` input + notes; `data-ajax` submit to `payrolls.store` (month converted to the first-of-month date in the service).
- **`payrolls/show.blade.php`** — run header (period, status, totals), payslips table: Employee · Base · Allowances (total) · Deductions (total) · Net · Actions (Edit components, Print). Editing opens a **modal per payslip** with two dynamic row lists — allowances and deductions — each row = catalog `<select>` + amount `<input>`, with add/remove-row buttons and a live net preview. Modal Save → AJAX `PATCH payrolls.payslips.update` (draft only). Status action buttons: Process (draft) → Mark Paid (processed). Read-only for `processed`/`paid`.
- **`allowances/index.blade.php`** / **`deductions/index.blade.php`** — server-side DataTable: Name · Status · Actions. Toolbar "New Allowance"/"New Deduction" gated by `@can('create allowance')` / `@can('create deduction')`.
- **`allowances/_form.blade.php`** (+ create/edit sheets) and **`deductions/_form.blade.php`** — `name` + `status` select, mirroring the users `_form` structure and `data-ajax` submit.
- **`payrolls/print/payslip.blade.php`** — print-friendly payslip: company name/logo (from settings), employee name/designation, period, base salary, an **itemised** allowance list and deduction list with amounts, totals, net pay, generated date, signature lines. Styled with `@media print`.
- **`payrolls/_js.blade.php`** — DataTable init, payslip modal (add/remove component rows, live totals), status action handlers; `allowances/_js.blade.php` + `deductions/_js.blade.php` for status dots — following existing `_js` patterns.
- Currency: prefix with the company currency from settings when available, otherwise render plain numbers.

### 9.10 Chart of Accounts integration (mandatory)

Payroll must post to the ledger **through the ChartOfAccounts module only** — it must never import `AccountLevel1..4` models or write their tables directly (see `docs/coa.md` §15). All accounts are resolved through **account-setting short codes**, never hardcoded account IDs.

**New default accounts** — `DefaultAccountService::getDefaultAccounts()` gains two Level-4 leaves and their Level-3 parents:

| Under | Level 3 (new) | Level 4 (new) |
|---|---|---|
| Liability → Current Liability (`101`) | Payroll Liabilities (`101-002`) | Salary Deductions Payable (`101-002-001`) |
| Expense → Administrator Expenses (`401`) | Payroll Expense (`401-002`) | Salary Expense (`401-002-001`) |

**New account settings** — `AccountSettingService::getDefaultAccountSettings()` gains:

| name | short | account (Level 4) |
|---|---|---|
| Salary Expense Account | `SEA` | Salary Expense |
| Salary Deductions Payable Account | `SDP` | Salary Deductions Payable |

Payment reuses the existing **`OSA`** (Office Account) setting — no new asset account.

**New voucher type** — add an `SLV` ("Salary Voucher") entry to `DefaultVoucherTypeService::getDefaultVoucherTypes()` and to the existing `VoucherTypeSeeder` (which seeds fresh installs via `DefaultService::initiateVouchers()`):

```php
[
    "short" => "SLV",
    "name" => "Salary Voucher",
    "credit_level" => "level4",
    "debit_level" => "level4",
    "description" => "SLV voucher records monthly payroll: salary expense, deductions, and the net payment to the office account.",
],
```

**Provisioning for existing companies** — `DefaultAccountService::initiateAccounts()` **skips** any company that already has Level-1 rows, so existing companies will not get the new tree. Add COA-owned, idempotent provisioning, invoked from a new data migration `database/migrations/2026_10_16_000005_provision_payroll_coa.php` for every existing company (new companies get it automatically via `CompanyService`/seeders, which already call `initiate*`):

1. `DefaultAccountService::provisionPayrollAccounts($company)` — create the two Level-3/Level-4 branches **if absent**, looking up parents by `name` + `company_id`.
2. `AccountSettingService::initiateAccountSettings($company)` — already `firstOrCreate`s by `short`, so it creates `SEA`/`SDP` on the next call (call it after step 1 so the accounts resolve).
3. `DefaultVoucherTypeService::initiateVouchers($company)` — already skips existing shorts, so it creates `SLV`.

**Posting rule — only on transition to `paid`.** Ledger lines are written exactly once, when a run goes `processed → paid` (never on `draft`/`processed`), mirroring `SupplierService::payAmountToSupplier()`. A dedicated `Modules/Payroll/Services/PayrollPostingService.php` performs the write inside the existing `updateStatus()` DB transaction:

1. Resolve `SEA`, `SDP`, `OSA` via `AccountSettingService::getAccount($short)`. If the setting is missing/`null` (`AccountSettingNotFoundException` / `AccountSettingNotSetException`), **abort the transition** and surface a clear "Chart of accounts is not configured for payroll" message — never post a partial voucher.
2. `VoucherService::getVoucherTypeByShort('SLV')` → `nextVoucherNo('SLV')` → `createVoucher([... 'type' => 'multiple', 'amount' => gross, 'date' => $paid, 'created_by' => auth id])`, then `refresh()`.
3. For every payslip, create `TransactionService` rows (`transaction_no = "<voucher_no>-<n>"`, `source = 'payroll'`, `voucher_id`, `account_id`, `amount`, `type`):
   - **Debit** Salary Expense (`SEA`) = `base_salary + total_allowances`.
   - **Credit** Salary Deductions Payable (`SDP`) = `total_deductions` (emit only when `> 0`).
   - **Credit** Office Account (`OSA`) = `net_salary`.
   Debits equal credits by construction (`base + allowances == deductions + net`).
4. Update each touched account's balance via `VoucherService::applyBalance()` (below) — never by writing `balance` directly.
5. Store the created voucher on `payrolls.voucher_id`. Re-posting when `voucher_id` is already set is a **no-op**.

**Sign logic stays in COA** — `docs/coa.md` §15 forbids hardcoded debit/credit signs outside COA. `VoucherService` already resolves signs from its `rules` map (Asset `debit +`, Expense `debit +`, Liability `credit +`, …). Add one generic helper to `VoucherService` (a small COA addition, consistent with `voidVoucher()` which already does this). It resolves the level-1 account through the injected `AccountLevel4Repository` (eager-loaded relations) rather than lazy-loading a passed model, keeping DB access in the repository layer per `docs/architecture-constraints.md`:

```php
public function applyBalance($accountId, $type, $amount)
{
    $account = $this->accountLevel4->query()
        ->with('level3.level2.level1')
        ->where('id', $accountId)
        ->first();

    $operation = $this->getOperation($account->level3->level2->level1, $type);
    $new = $operation === '+' ? $account->balance + $amount : $account->balance - $amount;
    $this->accountLevel4->query()->where('id', $accountId)->update(['balance' => $new]);
}
```

`PayrollPostingService` passes account ids (obtained from `AccountSettingService`) to `applyBalance()` — it never reads relations or signs itself, and does **not** copy `SupplierService`'s hardcoded signs. It exposes `postVoucher($payroll)` so a future reversal can reuse it.

**Void/reversal:** out of scope this phase (see §9.14); the SLV voucher is not voided by status changes.

**Plan gating** — `Modules/Billing/Services/PlanFeatureService::FEATURES` gains `'payroll' => ['Payroll']` (one permission module covers runs + catalogs), and every Payroll route carries `middleware('plan.feature:payroll')` **in addition to** `permission:...` (see §9.6), per `docs/coa.md` §13.

### 9.11 Billing integration (side effect + note)

The new `permission_modules` row `Payroll` (`is_platform = 0`) will automatically appear as a **selectable feature module** in Billing plan configuration (`PlanService` lists non-platform modules). With the explicit `'payroll' => ['Payroll']` mapping in §9.10, payroll is gated: companies whose plan excludes the `Payroll` module get a 403 from `plan.feature:payroll`. No limit resource is added for payroll in this phase.

### 9.12 Phase 2 — Implementation order

1. Scaffold `Modules/Payroll` + register (`modules_statuses.json`, provider, routes, config)
2. Migrations: `payrolls`, `payslips`, `allowances`, `deductions`, `payslip_allowances`, `payslip_deductions`
3. Models + repositories (`Payroll`, `Payslip`, `Allowance`, `Deduction`, `PayslipAllowance`, `PayslipDeduction`)
4. Allowances & deductions CRUD: controllers, requests, services, views (catalog first — payslips depend on it)
5. Permissions: seeder group + provisioning migration (§9.7)
6. **COA wiring (§9.10):** `DefaultAccountService` (new accounts + `provisionPayrollAccounts()`), `AccountSettingService` (new settings), `DefaultVoucherTypeService` + `VoucherTypeSeeder` (`SLV`), `VoucherService::applyBalance()`, `PlanFeatureService` (`'payroll' => ['Payroll']`), and the `2026_10_16_000005_provision_payroll_coa` data migration
7. `PayrollService` (generate / syncPayslipItems / updateStatus / destroy / recompute) + `PayrollPostingService` (post on `paid`)
8. Payroll controller + requests + routes (with `plan.feature:payroll`)
9. Payslip views: index, create, show (+ line-item modal), `_js`, print/payslip
10. Sidebar: `AsideSettingService` + `SettingSeeder` + append migration
11. Billing plan-feature mapping for the platform admin (optional per-plan restriction)
12. Verify (§9.13)

### 9.13 Phase 2 — Verification

- [ ] `php artisan migrate` applies all six tables + both data migrations cleanly
- [ ] `php artisan route:list | grep -E "payroll|allowance|deduction"` shows all routes; module is enabled
- [ ] **Allowance/Deduction CRUD** — create/edit/delete catalog entries; duplicate name per company rejected; entries are company-scoped (company A never sees company B's catalogs)
- [ ] Generate a run for a month → one payslip per **active, `payroll_enabled`, company-scoped** user; employees without salary are **skipped and reported**
- [ ] Generating the same month twice → rejected (unique per company + validation)
- [ ] Add/remove allowance & deduction line items on a **draft** payslip → `total_allowances`/`total_deductions`/`net_salary` and run totals update; live net preview matches saved values
- [ ] Deleting a catalog entry cascades its line items (guard/behaviour for non-draft payrolls confirmed)
- [ ] `processed`/`paid` runs are **read-only** (no line-item edits, no delete); Process then Mark Paid transitions work and set timestamps
- [ ] Delete only allowed on draft runs
- [ ] Printable payslip renders **itemised** allowances/deductions and prints without the app chrome
- [ ] **COA provisioning (§9.10)** — after `migrate`, every company has the `Salary Expense` and `Salary Deductions Payable` Level-4 accounts, the `SEA`/`SDP` account settings (resolving to those accounts, not `null`), and the `SLV` voucher type; re-running the migration is a no-op
- [ ] **Posting on paid** — marking a run `paid` creates exactly one `SLV` voucher whose transactions balance (debits = credits); `Salary Expense` increases by `base + allowances`, `Salary Deductions Payable` increases by `deductions`, `Office Account` decreases by `net`; `payrolls.voucher_id` is set
- [ ] **No double-post** — re-saving/marking an already-`paid` run does not create a second voucher; `draft → processed` never posts
- [ ] **COA layering** — Payroll code imports no `AccountLevel*` model; accounts resolve only via `AccountSettingService::getAccount()`; signs come from `VoucherService::applyBalance()`
- [ ] **Unconfigured COA** — deleting/clearing a `SEA`/`SDP` setting makes the `processed → paid` transition fail with a clear message and **no** partial voucher
- [ ] **Plan gating** — a company whose plan excludes the `Payroll` module gets a 403 on payroll routes; an included company is unaffected
- [ ] **Tenant isolation** — company A cannot view/generate/edit company B payrolls, payslips, or catalogs; platform admin gets a clear "not available" behavior
- [ ] Permissions — tenant user without `view payroll`/`view allowance`/`view deduction` → 403; owner role has all 12 permissions after the provisioning migration; Role editor can grant/revoke them
- [ ] Sidebar — Payroll section (runs, allowances, deductions) appears for new companies and for existing companies (append migration)
- [ ] **Architecture (`docs/architecture-constraints.md`)** — no Payroll **Controller** imports a Repository or calls Model queries; no Payroll **Service** calls `Model::query()/where()/find()` or relation lazy-loads as queries (all via injected repositories); line-item reads/writes go through `PayslipAllowanceRepository`/`PayslipDeductionRepository`; the billing usage count goes through `UserRepository::countFlaggedForCompany()`; `UserService::company()` resolves via `CompanyRepository`; `PayrollPostingService` runs no Model query and imports no `AccountLevel*` model
- [ ] `php artisan test` passes

### 9.14 Phase 2 — Assumptions to confirm before build

1. **Lifecycle** is exactly `draft → processed → paid` (no revert, no rejection).
2. Allowances and deductions are **company-managed catalogs with CRUD**, and payslips store **line items** (`payslip_allowances` / `payslip_deductions`) referencing them with an amount. Totals are cached sums, recomputed on change.
3. Salary is snapshotted at generation; later salary edits do **not** rewrite historical payslips.
4. Only **active** payroll-enabled users are included; users missing salary are skipped (not treated as 0).
5. Payslips print via browser (`@media print`); no PDF library.
6. Payroll is a **tenant-only** feature (no platform-admin access, no `is_platform` module).
7. Payroll becomes a **plan feature module** automatically (platform admin can restrict per plan in Billing).
8. A new **`Payroll` sidebar section** is added (rather than nesting under the existing `HR` section).
9. Catalog entries are **name + status** only (no fixed amount/percentage on the catalog itself) — the amount is entered per payslip line item. Delete-cascade behaviour when an entry is already used on a non-draft payroll should be decided during build (proposed default: **block deletion** with a message if referenced by any non-draft payslip, otherwise allow).
10. **Posting happens on the `processed → paid` transition** (not on `draft`/`processed`), once per run, via a single `SLV` voucher. `draft`/`processed` runs leave the ledger untouched.
11. **Account mapping is settings-based, not per-catalog:** all base + allowances debit `SEA` (Salary Expense); all deductions credit `SDP` (Salary Deductions Payable); the net credits `OSA` (Office Account). Per-allowance/per-deduction accounts (via `<x-account-selector>` on the catalogs) are a **future** enhancement, not this phase — this keeps COA models out of the Payroll module.
12. **Journal reversal is out of scope.** Once a run is `paid` there is no edit, delete, or voucher void this phase; corrections would be a future credit/debit-note style entry reusing `PayrollPostingService`.

### 9.15 Phase 2 — Notes / environment

- **PHP is not on PATH in the current environment** — `php artisan`/`php artisan test` cannot be executed here. All code will be written following module conventions; run migrations/tests locally (or via Docker) after implementation.
- Existing shared helpers to reuse: `App\Core\Helper::_uid()` (via `UIDModel`), `App\Repositories\BaseRepository`, the custom `@can` directive (`AuthServiceProvider`), `permission:` middleware (`EnsurePermission`), and the global AJAX form handler (`resources/views/layouts/script.blade.php`).
- **COA posting precedent** to mirror: `Modules/Supplier/Services/SupplierService::payAmountToSupplier()` (create `SLV`-style voucher → `TransactionService` rows → balance updates). `docs/coa.md` is the authoritative rulebook for §9.10.
- **Layering** is enforced per `docs/architecture-constraints.md` (see the §9.2 compliance note): Services → Repositories → Models; no Model queries in Controllers/Services. The `2026_10_16_000003..000005` data migrations are provisioning scripts and may query the DB directly (they own resolving the owner role, existing companies, etc.), using `firstOrCreate` for idempotency.
- **Pre-existing tech debt not to imitate:** `Modules/Employee/Services/EmployeeService::company()` (`Company::where()`), `PlanLimitService::employeesUsage()/ownerRoleId()` (`User::where()`, `DB::table`), and `TrailBalance` querying `Transaction` directly. New/edited code routes through repositories.
