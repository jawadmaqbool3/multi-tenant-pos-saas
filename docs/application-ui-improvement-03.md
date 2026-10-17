# Application UI/UX Improvement — Phase 03

## Verified Functional, UX, IA & Accessibility Audit + Remediation Plan

**Relationship to earlier phases**

| Doc | Scope |
|-----|-------|
| `application-ui-improvement.md` (Phase 01) | Visual re-tokenize + normalize plan (palette, buttons, cards). |
| `application-ui-improvement-02.md` (Phase 02) | Adopt the "Niche Spy Workspace" visual language (tokens/components, dark + light). |
| Module docs (`coa.md`, `dashboard-widget.md`, `merge-employees-users-payroll-attendance-plan.md`, `saas-billing-implementation.md`) | Feature-level designs. |
| **This doc (Phase 03)** | **What is actually broken / confusing / unreachable when the running app is walked page-by-page across all roles.** Not a re-skin — a defect + usability remediation plan. |

Phases 01/02 make the app *look* right. Phase 03 makes it *work* right and *navigate* right.

---

## 1. Method

The live app (`http://localhost:8000`, Docker `pos-app-1`) was driven with a real browser (Playwright),
walking the shell, dashboards, list pages, create/edit forms, reports, error pages and guest pages, and
switching between the seeded users:

| Role | Credentials | Password |
|------|-------------|----------|
| Super admin (SaaS platform) | `superadmin@gmail.com` | `12345678` |
| Company admin | `admin@gmail.com` | `12345678` |
| HR manager | `hr@gmail.com` | `12345678` |
| Accountant | `accountant@gmail.com` | `12345678` |
| Employee | `employee@gmail.com` | `12345678` |
| Sales person | `sales@gmail.com` | `12345678` |
| Stock manager | `stock@gmail.com` | `12345678` |

Evidence collected per page: DOM/accessibility snapshot, console + network errors, sidebar/menu
structure per role, table markup, form markup, responsive (390 px) and dark-mode rendering.

**Severity legend:** 🔴 broken (error / unreachable / data-loss risk) · 🟠 confusing or inconsistent ·
🟡 polish.

---

## 2. Verified findings

### 2.1 Global shell (affects every page)

| # | Sev | Finding | Evidence / location |
|---|-----|---------|---------------------|
| G1 | 🔴 | **GTM script 404 on every page.** Loads a relative, wrong-extension URL: `.../www.googletagmanager.com/gtm5445.html?id=GTM-5FS8GGP`. | `resources/views/layouts/basic.blade.php:25` and `Modules/Employee/Resources/views/auth/login.blade.php:28` → `j.src='../../../../www.googletagmanager.com/gtm5445.html?id='+i+dl`. Console 404 on all pages. Should be `https://www.googletagmanager.com/gtm.js?id=`. |
| G2 | 🔴 | **KTSearch JS crash on every app page.** `header-search` renders a toggle but no `data-kt-search-element="content"`, so Metronic's `KTSearch._handlers` dereferences null. The search icon opens nothing. | `layouts/header.blade.php:27-38`. Runtime `TypeError: Cannot read properties of null (reading 'addEventListener')` at `assets/js/scripts.bundle.js:4090`. |
| G3 | 🟠 | **Page title is literally `POS \| POS`.** Unquoted `@setting(name)` + hardcoded suffix; no per-page titles. | `layouts/basic.blade.php:6` → `<title>@setting(name) \| POS</title>`. |
| G4 | 🟠 | **Duplicate DOM id `kt_app_content_container`.** The layout wraps `@yield('content')` in that id, and ~26 views declare it again → duplicate ids, ambiguous JS/DOM targeting. | `layouts/basic.blade.php:69` + e.g. `dashboard/dashboard.blade.php:43`, `dashboard/admin.blade.php:27`, all `*/create.blade.php`, `*/edit.blade.php`. |
| G5 | 🟠 | **Sidebar menu container id is invalid:** `id="#kt_app_sidebar_menu"` (literal `#`). Any `document.querySelector('#kt_app_sidebar_menu')` fails; `data-kt-scroll-wrappers` mismatch. | `layouts/aside.blade.php:25` (referenced from `:24`). |
| G6 | 🔴 | **Sidebar links are persisted absolute ngrok URLs.** Menu entries in `settings.asidebar` JSON carry `https://unscanned-duncan-pseudoeditorial.ngrok-free.dev/...`, so the sidebar breaks on any other host/domain. | Seeded in `database/seeders/SettingSeeder.php:29`; decoded at `app/Services/AsideSettingService.php:241`; rendered by `app/View/Components/AsideMenuLinks.php:133`. Confirmed live: employee "My Attendance" href points at ngrok. |
| G7 | 🟠 | **Two sidebar entries both labelled "Accounts"** (Bank ▸ Accounts and Settings ▸ Accounts) — indistinguishable. | Confirmed for accountant/admin. |
| G8 | 🔴 | **Header "My Profile" 403s for restricted roles.** Links to `users.edit` which requires user-edit permission. | `layouts/header.blade.php:118`. Confirmed: employee → `Forbidden \| POS` (see G10). |
| G9 | 🟡 | **Dead header controls:** chat toggle (`#kt_drawer_chat_toggle`) has no drawer; top horizontal menu `#kt_app_header_menu` is empty; user-menu email link is `href="#"`. | `layouts/header.blade.php:42-49, 20-23, 107-108`. |
| G10 | 🟠 | **Error pages use the bare Laravel layout** — no app chrome/nav, and copy is contradictory ("You are not authorized… Sorry, something went wrong…"). | `resources/views/errors/403.blade.php` (+ 401/404/500…). |
| G11 | 🟠 | **Login has no "Forgot password" and no "Remember me".** No password-reset route exists anywhere in the app. | Confirmed in `routes/` (no `password.reset`/`PasswordController`). Login view: `resources/views/layouts/guest.blade.php` + login partial. |
| G12 | 🟠 | **Dev "Test Accounts" autofill dropdown ships in the login UI.** | Confirmed live on `/login`. Should be gated to non-production. |
| G13 | 🟡 | **Awkward login copy:** subheading "Your own management". | Confirmed live on `/login`. |
| G14 | 🟡 | **No public pricing page;** `/pricing` redirects to `/login`. | Confirmed live. |

### 2.2 Navigation & information architecture (role reachability)

| # | Sev | Finding | Evidence |
|---|-----|---------|----------|
| N1 | 🔴 | **Permitted features are unreachable from nav.** The sidebar is built from the saved `settings.asidebar`, which does not contain sections for every permittable feature. Accountant sees only Bank ▸ Accounts + Settings ▸ Accounts — **no Vouchers / Voucher Types** despite holding those permissions. | Confirmed: accountant sidebar. |
| N2 | 🔴 | **Employee cannot reach "My Payslips"** (link missing / permission mismatch) though they hold `view own payroll`. Sidebar shows only "My Attendance". | Confirmed: employee sidebar. |
| N3 | 🟠 | **Employee dashboard is empty** ("No widgets available — Ask an administrator…") even though they hold `dashboard my attendance` / `dashboard my payslips`. | Confirmed: employee `/`. |
| N4 | 🟠 | **Accountant dashboard shows company-level billing/plan widgets** (Current Plan, Subscription, Plan Usage) irrelevant to the role, and only a partial finance view. | Confirmed: accountant `/`. |
| N5 | 🟠 | **Nav is not permission-driven at build time.** Menu links carry their own stored path/permission and can drift from the RBAC permission list (root cause of N1/N2/G6). | `AsideMenuLinks.php`, `settings.asidebar`. |

### 2.3 List / index pages

| # | Sev | Finding | Evidence / location |
|---|-----|---------|---------------------|
| L1 | 🔴 | **DataTables error on Companies:** `Requested unknown parameter 'manager.name' for row 0, column 3`. | `resources/views/companies/index.blade.php:99` (`"data":"manager.name"`) vs table columns; header mismatch. |
| L2 | 🟠 | **Every index table reuses `id="category_table"`** (copy-paste), incl. `companies/index`, `users/index`, `vouchers/index`, `voucher_types/index`, `products`, `suppliers`, payroll etc. Fragile and confusing. | grep `category_table` across `Modules/*` + `resources/views`. |
| L3 | 🟠 | **Wrong search placeholder "Search Permissions"** on Sales Orders, Permissions, Voucher Types lists. | `Modules/Order/.../sales_orders/index.blade.php:21`, `Modules/RBAC/.../permissions/index.blade.php:21`, `Modules/ChartOfAccounts/.../voucher_types/index.blade.php:21`. |
| L4 | 🟠 | **Primary "create" action hidden in a kebab menu** on index pages — no prominent Add button. | Confirmed live (Products, etc.); toolbar is `.card-toolbar` kebab. |
| L5 | 🟠 | **Duplicate magnifier icon** in card headers (icon + placeholder icon). | Confirmed live; also noted in Phase 01 audit. |
| L6 | 🟠 | **Empty column headers** (blank `<th>`) on Products / Suppliers / Sales lists. | Confirmed live. |
| L7 | 🟡 | **Users list:** literal text "Image not available" instead of an avatar placeholder; empty **Flags** column. | `Modules/Employee/Resources/views/users/index.blade.php:61` (`Flags`). |

### 2.4 Forms

| # | Sev | Finding | Evidence / location |
|---|-----|---------|---------------------|
| F1 | 🔴 | **Orders create renders a `<form>` inside a `<form>`.** Outer form `create.blade.php:21`, inner form `_form.blade.php:1`. Invalid; submit behaviour undefined. | `Modules/Order/Resources/views/sales_orders/create.blade.php:21` + `_form.blade.php:1`. |
| F2 | 🔴 | **Invalid table structure:** `<table><tr><thead>…</thead><tbody>…</tbody><tfoot>…</tfoot></tr></table>`. | `Modules/Order/Resources/views/sales_orders/_form.blade.php:15-26`. |
| F3 | 🟠 | **`data-kt-redirect="categories.html"` leftover** on nearly every create/edit form (~10 files) — a form can redirect to a dead relative path. | `products/create:20`, `categories/create:20`, `promotions/create:20`, `users/create:20`, `companies/create:20`, `roles/edit:20`, `suppliers/create:20`, `purchase_orders/create:22`, `sales_orders/create:22`, … |
| F4 | 🟠 | **"Save Changes" on create forms** (should be "Create"/"Save"). | `products/_form:201`, `categories/_form:155`, `suppliers/_form:110`, `roles/_form:69`, `users/_form:185`, `companies/_form:178`, etc. |
| F5 | 🟠 | **Copy-paste help text in Product form** ("Set the category status.", "Set a description to the category…") and an irrelevant **Meta Options / SEO** block. | `Modules/Inventory/Resources/views/products/_form.blade.php:72,157,164`. |
| F6 | 🟠 | **Price field has the wrong placeholder** `placeholder="Product name"` and `step="0.1"`; typo "Pick date rage". | `products/_form.blade.php:148-150` (price), `:78` (typo). |
| F7 | 🟠 | **Product form lacks core POS fields** (SKU/barcode/cost price/stock qty & threshold). | `products/_form.blade.php`. |
| F8 | 🟡 | **Import pages** trigger a hidden file input from a text link; no drag-drop, no template download. | Confirmed live (Products import). |
| F9 | 🟡 | **Icon-only add-row button** on orders (`name="plus"`) has no accessible label. | `sales_orders/_form.blade.php:11-12`. |

### 2.5 Accounting / COA

| # | Sev | Finding | Evidence / location |
|---|-----|---------|---------------------|
| C1 | 🔴 | **Hardcoded broken link** `http://localhost/ssss/general/report/5/filter` in the COA tree. | `Modules/ChartOfAccounts/Resources/views/accounts/tree.blade.php:112`. |
| C2 | 🟠 | **Typos in seeded account names:** "Recievables", "Payment Recievable", "Web Chanel". | `Modules/ChartOfAccounts/Services/DefaultAccountService.php:207,212,304`; also `AccountSettingService.php:116`, `DefaultVoucherTypeService.php:64`. |
| C3 | 🟠 | **COA tree fully expanded by default**, no search/filter/collapse. | Confirmed live. |
| C4 | 🟡 | Status conveyed by **colour only** in the tree; duplicated inline SVG action blocks. | `accounts/tree.blade.php` (also noted in Phase 01 audit). |

### 2.6 Responsive / theming

| # | Sev | Finding |
|---|-----|---------|
| R1 | ✅ | Dark mode works (dark body `#07080f`, card `#0f1121`, sidebar `#0a0b14`). Needs a full light/dark parity pass. |
| R2 | 🟠 | At 390 px the card header stacks to ~189 px tall; the primary action is buried in the kebab. Tables scroll horizontally inside `.table-responsive` (acceptable), but toolbars and `float-end` balances get clumsy. |
| R3 | 🟡 | No per-page responsive regression test; some layouts rely on floats (`float-end`) rather than flex/grid. |

---

## 3. Root-cause themes

1. **Copy-paste from the Metronic demo** (GTM snippet, `category_table`, `categories.html` redirects, "Search Permissions", Meta/SEO blocks, demo chat drawer, "Your own management"). → a systematic cleanup pass removes most findings.
2. **Nav is data-driven from a stale seeded snapshot**, decoupled from RBAC and host. → generate nav from routes/permissions at render time.
3. **Role dashboards & nav not fully wired to permissions.** → permission-aware nav + widget sets.
4. **Invalid HTML** (nested ids, nested forms, malformed tables) → markup correctness pass.
5. **Missing product-grade auth UX** (no password reset/remember-me; dev picker shipped).

---

## 4. Phased remediation plan

> Ordering is by risk × blast-radius. Phases 0–2 are app-wide and cheap; 3–6 are per-module.

### Phase 0 — Global shell correctness (quick wins) — ~1 day
- **G1** Fix/remove the GTM snippet (correct `https://www.googletagmanager.com/gtm.js?id=…` or delete).
- **G2** Either implement the header search dropdown (`data-kt-search-element="content"`) or remove the search widget — stops the per-page JS error.
- **G3** Fix `<title>`: quote the setting, add `@yield('title')`, set per-page titles.
- **G4** Remove the duplicate `#kt_app_content_container` from all views (keep the layout's).
- **G5** Fix sidebar id to `kt_app_sidebar_menu` (no `#`).
- **G8** Point "My Profile" at a self-service profile route that every role can access (or hide it when not permitted).
- **G9** Remove the demo chat toggle, empty header menu, and `href="#"` email; or wire them up.
- **Acceptance:** zero console errors on dashboard + one list + one form; no duplicate ids; title is meaningful per page.

### Phase 1 — Navigation & IA — ~1.5 days
- **G6/N5** Build sidebar links from **named routes** (ideally route names / relative paths) instead of absolute stored URLs; add a migration to normalise existing `settings.asidebar`.
- **N1/N2** Ensure every permittable feature has a nav entry, filtered by permission at render (Vouchers/Voucher Types for accountant, My Payslips for employee).
- **G7** De-duplicate/rename the two "Accounts".
- **Acceptance:** each role can reach every feature it has permission for; no dead/absolute links; no duplicate labels.

### Phase 2 — List pages & DataTables — ~1.5 days
- **L1** Fix Companies DataTable columns (match header ↔ `data` keys).
- **L2** Replace `id="category_table"` with table-specific ids.
- **L3** Correct search placeholders.
- **L4** Promote the primary "create" action to a visible Add button; keep destructive/rare actions in the kebab.
- **L5** Remove duplicate magnifier icons.
- **L6/L7** Fill empty headers; avatar placeholder instead of "Image not available".
- **Acceptance:** no DataTables console errors on any index; every list has a visible primary CTA and a labelled search.

### Phase 3 — Forms & markup validity — ~2 days
- **F1/F2** Fix orders nested form + malformed order-items table.
- **F3** Remove all `data-kt-redirect="categories.html"`.
- **F4** Context-aware submit labels ("Create" vs "Save Changes").
- **F5/F6/F7** Rebuild the Product form: correct labels/help, correct Price placeholder/`step`, drop Meta/SEO, add SKU/barcode/cost/stock/threshold.
- **F9** Label icon-only buttons.
- **Acceptance:** all create/edit forms have valid nested HTML, correct labels, and a sensible redirect/return path.

### Phase 4 — Accounting/COA — ~1 day
- **C1** Replace hardcoded URLs with `route()` helpers.
- **C2** Fix seeded typos (with a data migration for existing rows).
- **C3/C4** Add tree search + default-collapsed nodes; add text/`aria-label` for status.
- **Acceptance:** no hardcoded hosts; typos corrected incl. existing data.

### Phase 5 — Auth & onboarding — ~1.5 days
- **G11** Add password reset (route + link) and "Remember me".
- **G12** Gate the Test Accounts picker behind `APP_ENV !== 'production'`.
- **G13/G14** Tighten login copy; decide on a public pricing/marketing entry.
- Improve error pages (**G10**) to use the app layout with accurate copy.
- **Acceptance:** reset flow works end-to-end; production build shows no dev picker.

### Phase 6 — Responsive, theming & a11y — ~1.5 days
- **R2/R3** Replace floats with flex/grid; test 360/768/1024/1440 for all key pages.
- **R1** Light/dark parity pass (coordinate with Phase 02 tokens).
- Accessibility sweep: `aria-label` on icon-only controls, `alt` on images, focus states, contrast, table `scope="col"`.
- **Acceptance:** WCAG AA on contrast/focus/names; no horizontal page overflow at 360 px.

### Phase 7 — Verification harness (ongoing)
- Role-based smoke script (Playwright) asserting: no console errors, every granted permission is reachable, no 403 from shell links, tables render without DataTables warnings.
- **Acceptance:** harness runs in CI against a seeded DB.

**Total estimate:** ~10 developer-days, plus the Phase 01/02 visual work.

---

## 5. Quick-win checklist (highest value, lowest risk)

- [ ] G1 GTM snippet (every page, 1 line × 2 files)
- [ ] G2 header search (stops the recurring JS error)
- [ ] G3 page title
- [ ] G4 duplicate `kt_app_content_container` ids
- [ ] G5 sidebar id `#`
- [ ] G8 "My Profile" 403
- [ ] F3 remove `categories.html` redirects (~10 files)
- [ ] L1 Companies DataTable error
- [ ] L3 search placeholder
- [ ] C1 hardcoded COA link
- [ ] C2 seeded typos

---

## 6. Verification matrix (definition of done)

For each role, every one of these must pass:

| Check | Method |
|-------|--------|
| No console errors | Browser console clean on dashboard, one list, one form, one report |
| No duplicate DOM ids | DOM scan per page |
| All granted features reachable | Walk nav; compare against role permissions |
| No shell link 403s | Click every header/sidebar link per role |
| Tables render | No DataTables warnings/errors on every list |
| Forms valid | No nested `<form>`; `<table>` structure valid |
| Responsive | No page-level horizontal scroll at 360/768/1024/1440 |
| Theme | Verified light **and** dark |
| a11y | Icon-only controls named; images have `alt`; visible focus |

---

## 7. Risks & mitigations

| Risk | Mitigation |
|------|-----------|
| Overlap with Phase 01/02 visual work | Phase 03 touches behaviour/markup; sequence markup fixes before/with token work to avoid re-editing the same files twice. |
| Nav refactor (Phase 1) breaks per-company custom menus | Keep `settings.asidebar` as an order/visibility override but resolve hrefs from route names; add a normalising migration. |
| Product-form scope creep (new fields) | Confirm field set + DB columns with product owner before Phase 3. |
| Removing the GTM/chat/search widgets may be desired | Confirm with owner whether to **fix** or **remove** (G1/G2/G9). |
| Password reset needs mail config | Confirm mail driver/queues before Phase 5. |
