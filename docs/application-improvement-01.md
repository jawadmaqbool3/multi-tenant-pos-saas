# Application improvement 01 — Phase 1 & 2

Severity legend: 🔴 high (breaks a core task) · 🟠 medium (visible defect, workaround exists) · 🟡 low (polish)
Status legend: ✅ implemented & verified · 🔧 implemented, not yet verified · ⏳ open

## Phase 1 — Global UI defects

### P1-1 🔴 Sidebar/menu icon renders as a plain circle — ✅ implemented

**Symptom.** One or more menu/hamburger icons render as an outlined circle instead of the intended glyph.

**Root cause.** `app/Support/Icon.php:92` — `return self::PATHS[$name] ?? self::PATHS['circle'];`. Any icon name missing from the `PATHS` map silently falls back to the `circle` path, so a typo or unregistered name is invisible in the UI.

**Fix.** Added the `menu` entry (`Icon.php:87`, three horizontal lines). Missing names should stay loud in future: consider throwing/asserting in local env instead of the circle fallback.

**Verify.** Icon renders as a hamburger where the `menu` name is used.

---

### P1-2 🔴 Mobile sidebar drawer: overlay appears, panel never slides in — ✅ implemented & verified

**Symptom.** Below 992px, tapping the header menu toggle dims the page (the `drawer-overlay` shows) but the sidebar panel stays off-screen.

**Root cause (corrected).** The header's mobile drawer block (`.app-header-menu.app-header-mobile-drawer`, 250px wide, `drawer-end`) was dead markup: its toggle element `#kt_app_header_menu_toggle` did not exist and `#kt_app_header_menu` was empty. At <992px KTDrawer still materialised it — adding `.drawer.drawer-end` (`display:flex !important; position:fixed; transform:translateX(100%)`). Because `.app-header` sets `backdrop-filter: blur(24px)`, it became the containing block for the "fixed" panel, so the panel hung off the right edge and inflated `document.scrollWidth`/`window.innerWidth` by exactly 250px in the 767–991px range (767→1017, 900→1150). Metronic's `getViewPort().width` (uses `window.innerWidth`) then read ≥ `--kt-lg` (992), so the real sidebar drawer deactivated (no `drawer`/`drawer-start` classes, width cleared) while the hamburger still created the overlay — hence "overlay appears, panel never slides in". The old `#kt_app_sidebar_footer` dependency was a red herring (harmless).

**Fix.** Removed the dead mobile drawer block from `resources/views/layouts/header.blade.php` (kept `#kt_app_header_wrapper`, added `ms-auto` to `.app-navbar` to keep the navbar right-aligned) and corrected the stale `data-kt-scroll-dependencies` in `resources/views/layouts/aside.blade.php` (dropped the non-existent `#kt_app_sidebar_footer`).

**Verify (done).** CDP at 375/390/576/768/900/991/992/1200: `overflow:0` everywhere; drawer active at all <992; click opens panel (`drawer-on`, rect x=0 w=225, sidebar above overlay z105); overlay click closes with no orphan overlay; navbar right edge ≈ clientW−20; no JS errors.


---

### P1-3 🔴 Companies list "Manager" column is always empty — ✅ implemented

**Symptom.** For the `admin` role the companies table shows a "Manager" header with a permanently blank cell.

**Root cause.**
- `resources/views/companies/index.blade.php:60` (header) and `:87` (column key `manager.name`).
- `App\Models\Company` has no `manager()` relation (only `setting`, `dashboardSetting`, `subscriptions`), and `app/Repositories/CompanyRepository.php:9` eager-loads only `setting` and defines no `manager.name` edit column. The key resolves to `null` for every row.

**Fix.** Replace the column with data that actually exists — a **Package** column sourced from the company's current plan (`Company::activeSubscription()` / `PlanLimitService::planForCompany()`), consistent with P1-6/P2-3. Alternatively add a real `manager()` relation if the schema does model one.

**Verify.** The column renders a meaningful value for every row, and the header matches the data.

**Implemented.** Header/column now read **Package** (`resources/views/companies/index.blade.php`), fed by a `subscription_plan` edit column in `CompanyRepository` that picks the active/trialing, unexpired subscription from an eager-loaded `subscriptions.plan` (one extra query, no N+1) and falls back to `No plan`. Verified live: the single company shows `Pro` (matching the P2-1 count) where the cell used to be blank.

---

### P1-4 🟠 Companies list logo column renders a blank/broken image — ✅ implemented

**Symptom.** Companies without an uploaded logo display the words *Image not available* in the Logo cell.

**Root cause.** `app/DataTables/DataTableBuilder.php:49-53` — `thumbnail()` trusts the stored filename: it returns `'Image not available'` only when `$file` is empty, and otherwise emits `<img src="assets/media/.../$file">` without checking the file exists. For the seeded company `setting.logo` is the string `logo.jpeg` while the file is absent from `public/assets/media/small/logo/`, so the column rendered an `<img>` with a dangling `src` — i.e. a blank/broken logo.

**Fix.** Added `DataTableBuilder::letterAvatar($name)` (uppercased 1–2 initials inside the standard `symbol symbol-45px` wrapper, `?` when the name is blank) and had the `setting.logo` column use it whenever the logo is empty **or** `is_file(public_path('assets/media/small/logo/'.$logo))` is false.

**Verify.** A company with no logo shows initials; one with a logo is unchanged; column alignment is preserved.

**Verified live.** Logo cell now renders `span.symbol-label` with `P` (company "POS"), zero `<img>` tags in the table, no "Image not available" text; row height/alignment unchanged.

---

### P1-5 🔴 Settings page blows up with "Attempt to read property 'uid' on string" — ✅ implemented

**Symptom.** General settings errors when a settings record is loaded through `SettingService`.

**Root cause.** `app/Services/SettingService.php:71-77` — `getSetting($setting = null)` returns its argument **unchanged** when it is truthy, so a caller passing a uid string gets the string back; `resources/views/settings/generalSettings.blade.php:17` then evaluates `$setting->uid`.

**Fix.** `SettingService::getSetting()`/`update()` both delegate to a new `resolveSetting()`: a `Setting` instance is returned as-is, a non-empty string is looked up by `uid`, and a `null`/empty value keeps the previous `filter()->first()` behaviour. `SettingController::index()` `abort(404)`s when nothing resolves, and `update()` throws `SettingNotFoundException` (already mapped by `Handler` to a friendly `{error,message}` payload) instead of dereferencing `null`.

**Verify.** Opening and saving general settings works with and without a pre-existing settings row; no property-on-string errors in `storage/logs/`.

**Verified live.** `GET /settings/general` (no uid) → 200; `GET /settings/general/27caf840…` (another company setting uid — previously threw) → 200 with its values rendered; unknown uid → 404; `POST /settings/general/update/27caf840…` round-tripping current values → `{"success":true,"reload":true,"message":"Settings Saved"}`; unknown uid on POST → `{"error":true,"message":"Setting not found."}`.

---

### P1-6 🟠 No company detail/history page (`CompanyController::show()` is a stub) — ✅ implemented & verified

**Symptom.** Clicking through to a single company yields nothing; there is no page showing what a company is on, what it has used, or what it has paid.

**Root cause.** `app/Http/Controllers/CompanyController.php:75-78` — `show($company = null)` has an empty body.

**Fix.** Build the page from existing services rather than new queries: `BillingDashboardService::currentPlan()`, `usage()`, `invoicesFor()`, `PlanLimitService`, `Company::subscriptions()`. Reuse `billing_subscriptions` / `billing_invoices` / `billing_plan_limits`, and render the trend chart with `public/assets/js/ui-charts.js` (ApexCharts) following `docs/dashboard-widget.md`.

**Verify.** Company page lists current plan, limits/usage, and invoice history with a chart; permission-gated like the list page.

---

### P1-7 🔴 Every confirm dialog renders *underneath* its backdrop (delete on plans, coupons, …) — ✅ implemented & verified

**Symptom.** Clicking any delete action shows only a dimmed page: the `#delete_modal` dialog is invisible and its confirm button cannot be clicked.

**Root cause.** `public/assets/css/theme-overrides.css:383` set `#kt_app_root { position: relative; z-index: 1; }`, which created a **stacking context around the entire application**. Bootstrap mounts `.modal-backdrop` on `<body>` (outside `#kt_app_root`) with `z-index: 1050`, while `.modal` sits *inside* `#kt_app_root` with `z-index: 1055` — that 1055 is scoped to the root's level of `1`, so the whole app subtree (dialog included) painted **below** the backdrop. Because no ancestor used `transform`/`filter`/`contain`, the cause was purely stacking, not a fixed-position containing block.

**Diagnosis method.** Headless Chrome + CDP (no browser MCP available): `document.elementFromPoint()` at the confirm button's centre returned `DIV.modal-backdrop.fade.show`; the ancestor audit showed `#kt_app_root` as the *only* stacking context in the chain.

**Fix.**
- `theme-overrides.css` — dropped `z-index: 1` from `#kt_app_root` (kept `position: relative`, which does not create a stacking context).
- `theme-overrides.css:377` — the ambient wash `body::after` moved from `z-index: 0` to `z-index: -1`. Its previous job of "sit behind the app" is what the root `z-index: 1` was doing; `-1` keeps it above the canvas background and behind all content.

**Verify (all automated, on `/plans` and `/coupons`).**
- Ancestor audit: zero stacking contexts on the modal's chain; `#kt_app_root` computed `z-index: auto`.
- Hit test: `elementFromPoint()` at the confirm button now returns `SPAN.indicator-label` (inside the button); at the viewport corner it returns the modal — i.e. dialog above backdrop.
- Pixel check (1600×900 screenshot): button pixel `rgb(229,72,77)`, dialog `rgb(255,255,255)`, outside the dialog `rgb(3,3,6)` (content `rgb(10,11,20)` dimmed by the 0.7 backdrop) — backdrop visible but behind.
- Ambient wash still renders: a body-background pixel matches the computed `--ui-wash-b` blend to within 1/255.
- Open → Cancel closes the modal and removes the backdrop (no orphan state) on both pages.

---

### P1-8 🟡 Global DataTable search crashes when a request has `search[value]` but no `columns` array — ✅ implemented (defensive only)

**Note:** Real UI always sends `columns[…]` with global search; the crash was only from hand-crafted/XHR requests missing `columns`. Fix is defensive input normalization. See root cause and verification below.

**Symptom.** A request like `/coupons?draw=3&length=10&search%5Bvalue%5D=zzz&start=0` returns `{"error":true,"message":"Something went wrong","console":"count(): Argument #1 ($value) must be of type Countable|array, null given"}`.

**Root cause.** `vendor/yajra/laravel-datatables-oracle/src/Utilities/Request.php:131` — `searchableColumnIndex()` runs `count($this->request->input('columns'))` unconditionally for a global search; with no `columns` input this is `count(null)`, a `TypeError` under PHP 8. `Handler`'s catch-all `renderable(Throwable)` then turns it into the generic HTTP 200 `{error:true,...}`.

**Scope — NOT a UI bug.** The real UI always sends the `columns[i][...]` array (verified by capturing the network request for a global search on `/coupons`: it includes `columns[0][data]…` and returns a valid `recordsFiltered`/`data` payload). Only hand-crafted/bookmarked/legacy request shapes hit this. Evidence of the hit exists in `storage/logs/errors-2026-10-14.log` (`19:26:50`, `user_id:1`) — a request with the same shape, i.e. not produced by today's front-end.

**Fix plan (low priority).** Either hard-normalise the request before it reaches yajra (close over `request('columns', [])` in `app/DataTables/DataTableBuilder::build()` — e.g. bail out of the search when `columns` is absent), or add a thin override so a missing `columns` input degrades to "no search" instead of a 500-class error.

**Verify.** The same URL returns a normal empty JSON table payload instead of the generic error.

---

## Phase 2

### P2-1 🔴 On plan listing we should have number of companies using specific plan — ✅ implemented

**Symptom.** The plans table (`Modules/Billing/Resources/views/plans/index.blade.php`) has no "Companies" column. Super-admins cannot see how many companies are on a given plan at a glance.

**Data source.** `billing_subscriptions` links `company_id` → `plan_id`. A company's usage of a plan is represented by its subscription. The current set of "companies using specific plan" is best approximated by counting subscriptions where `status` is `active`/`trialing` (valid, unexpired). This mirrors `SubscriptionRepository::activeWithPrice()` and `PlanLimitService::planForCompany()`.

**Investigation.**
- `SubscriptionRepository::query()` is available.
- `PlanRepository` builds the DataTable via `$this->plans->dataTable($this->plans->query())`. Columns defined in `plans/index.blade.php:78-84` and repo add/edit columns.
- No existing aggregation in `PlanRepository`.

**Fix.** 
1. In `PlanRepository`, add `dataTableWith`/aggregation. Option A: add a `subscriptions` relation or use `withCount(['subscriptions' => fn($q)=>$q->whereIn('status',['active','trialing'])])` on the Plan model, and expose as a virtual column. Or add a computed column via `$this->dataTableAddColumns['companies_count'] = ...`.
2. Add a new `<th>` in `plans/index.blade.php` (e.g. "Companies") and corresponding DataTable column: `{ "data": "companies_count", "orderable": true|false, "searchable": false }`.
3. Add the column to `dataTableRawColumns` in `PlanRepository` if rendered as HTML/raw.

**Verify.** Plans table shows a numeric count per plan; 0 for plans with no active/trialing subscriptions.

**Implemented.** `PlanRepository`: `$dataTableWith = ['prices', 'subscriptions']`, `companies_count` added to `dataTableRawColumns` and as an edit column counting `$plan->subscriptions->whereIn('status', ['active', 'trialing'])` from the eager-loaded collection (no N+1); `plans/index.blade.php` gained the `<th>Companies</th>` header and `{ data: companies_count, orderable: false, searchable: false }` column. **Requires `Plan::subscriptions()`** (`Modules/Billing/Models/Plan.php`) — that relation did not exist and its absence broke the whole plans page until it was added. Verified live: Basic 0 / Pro 1 / Enterprise 0.

---

### P2-2 🔴 Clicking edit button on plan shows "Attempt to read property 'id' on string" — ✅ implemented

**Symptom.** `edit($plan = null)` in `PlanController` passes the route param (a string uid) directly to `PlanService::getFormData($plan)`; inside getFormData, when `$plan` is truthy it does `$this->features->query()->where('plan_id', $plan->id)...` (e.g. line ~91 in PlanService), throwing `Attempt to read property "id" on string`.

**Root cause.**
- `Modules/Billing/Http/Controllers/PlanController.php:60-64`: `public function edit($plan = null)` has no type hint. Laravel resource binding gives the uid string.
- `Modules/Billing/Services/PlanService.php:76-96`: `$plan` treated as a model instance (accesses `$plan->id`). 
- Other methods (`update`, `destroy`) already type-hint `Plan $plan`, so implicit binding resolves by uid to the model.

**Fix.**
- Change `public function edit($plan = null)` to `public function edit(Plan $plan)` (implicit model binding by uid, consistent with `update/destroy`).
- Also make `PlanService::getFormData()` safely accept either a uid string or a model (defensive), though controller fix is sufficient: if `$plan` is a string, resolve it; else use as-is.
- Search for any other places where a plan is passed from a non-typed resource controller route (same pattern can occur elsewhere).

**Verify.** Clicking the edit button on a plan loads the edit page without the "Attempt to read property 'id' on string" error.

**Implemented.** `PlanController::edit(Plan $plan)` now type-hints the model (uid binding, consistent with `update`/`destroy`); `PlanService::getFormData()` resolves a string uid to a model and uses a `$planId` local so it accepts either shape.

---

### P2-3 🔴 Clicking a plan should open a plan detail/analytics page — ✅ implemented & verified

**Fix.** Implemented `PlanService::getDetail()` to load plan prices/limits/features/permissions and aggregate active companies (from active/trialing subscriptions), MRR (yearly normalized to monthly), subscriptions counts, and new subscriptions per month (last 6). Added `SubscriptionRepository::forPlan()`, `countsByMonthForPlan()`, `PlanRepository::viewButton()` gated by `view billing plan`, `PlanController::show(Plan $plan)`, and `Modules/Billing/Resources/views/plans/show.blade.php` with cards, prices/limits/modules, area chart (ApexCharts + UiTheme.palette), and companies table linking to company show page.

**Verify.** `/plans/{uid}` (Pro) 200 with sections: Active companies, Subscriptions, Est. MRR, Permissions, Prices, Plan limits, Modules included, New subscriptions (last 6 months), Companies on this plan; bad uid → 404; plan list includes view button.

---

### P2-4 🔴 Optimize the layout of coupon creation page — too long on left, too short on right — ✅ implemented

**Symptom.** The coupon creation form (`Modules/Billing/Resources/views/coupons/create.blade.php`, `_form.blade.php`) splits as `col-md-4` (left sidebar cards) and `col-md-8` (right content). The user finds it unbalanced: "long on left and short on right".

**Observation.** The right column (col-md-8) currently holds an informational card and action buttons (lines ~142-168 in `_form.blade.php`). The left holds the main input cards. On wide screens this gives the form controls less horizontal space than expected.

**Fix.** Re-balance the grid. Common Metronic pattern for this split is to swap proportions or use a more balanced layout (e.g. `col-lg-8 col-xxl-7` / `col-lg-4 col-xxl-5`), or put the informational content in the left sidebar or below. Alternatively use `row-cols`/flex or move "How coupons work" to a tab/accordion or reduce its width.

**Suggested changes.**
- Change the form wrapper layout: make the main fields column wider, e.g. `col-md-8 col-lg-7` and the side column `col-md-4 col-lg-5` (or 7/5, 8/4 with different breakpoints). Or flip to 6/6 on md and stack on small.
- Alternatively put the helper/info card into the left column as the bottom card, or move action buttons to the same column as the main form.
- Keep the same structure, adjust column classes in `coupons/_form.blade.php` (lines 14 and 142). Also mirror in `edit.blade.php` form layout (same include).

**Verify.** On desktop (>=1200px) the main form fields take more horizontal real estate and the overall page feels balanced; responsive behavior remains intact.

**Implemented.** Form fields moved to `col-md-8 col-lg-7 col-xxl-8`, sidebar (Status + "How coupons work" + actions) to `col-md-4 col-lg-5 col-xxl-4` in `Modules/Billing/Resources/views/coupons/_form.blade.php`.

---

### P2-5 🔴 Coupon creation throws error — check why — ✅ implemented & verified

**CONFIRMED (2026-10-17).** Two distinct failures were found in `storage/logs/errors-2026-10-17.log`:
1. `17:18:58` — `StripeServiceException: The parameter "redeem_by" expects a unix timestamp representing a date and time in the future. You specified 1591815600 which is in the past.` The user submitted an `expires_at` in the **past** (2020-06-11); `StripeSyncService::syncStripeCoupon()` maps `expires_at` → Stripe `redeem_by`, which Stripe rejects. The local record (`LAUNCH30`) was created but never got a `stripe_coupon_id`.
2. `17:40:32` / `17:40:37` — `Illuminate\Validation\ValidationException: The given data was invalid.` Coupon field validation failed (e.g. invalid `code` format with a space, `discount_value` > 100 for percent). These were surfaced to the browser as a **generic** `{"error":true,"message":"Something went wrong"}` because `app/Exceptions/Handler.php` registered a catch-all `renderable(Throwable)` that matched `ValidationException` too, returning HTTP 200 and hiding the field errors; every validation failure was also written to the `errors` log channel as `ERROR`.

**Fixes applied.**
- Added a dedicated `renderable(ValidationException)` in `app/Exceptions/Handler.php` (registered before the generic throwable handler) that returns **HTTP 422** with `{ error, message, errors }`; the frontend 422 handler (`resources/views/layouts/script.blade.php:132-149`) now shows per-field toasts and highlights inputs. `ValidationException` added to `$dontReport` so validation noise no longer hits the errors log.
- `StoreCouponRequest` / `UpdateCouponRequest`: `expires_at` rule is now `['nullable','date','after_or_equal:today']` so a past expiry is rejected up front instead of failing inside Stripe.
- `CouponService::store()/update()/delete()`: Stripe sync wrapped in try/catch (logged as warning) so a Stripe failure no longer 500s the request — a coupon is still created locally.

**Leftover data.** The failed `LAUNCH30` row (past expiry, `stripe_coupon_id = null`) remains in `billing_coupons` from the pre-fix attempt.

**Original symptom (historical — superseded by the fixes above).** Submitting the coupon create form throws an error. Possible causes (investigate before fixing):
1. **Stripe not configured / API error.** `CouponService::store()` calls `$this->stripe->syncCoupon($coupon)` (`CouponService.php:38-49`). `StripeSyncService::syncCoupon()` calls `$this->stripe->request(...)` and may throw if STRIPE_KEY/SECRET missing or Stripe API fails. In non-Stripe environments the service returns early if not configured (`StripeSyncService.php:65-67`), but `StripeService::isConfigured()` may still allow errors in certain states.
2. **Validation.** StoreCouponRequest: requires `discount_type`, `discount_value>0`, `currency` required_if `amount`, `duration_in_months` required_if `repeating`, `code` unique/regex. If JS doesn't populate fields or a required field missing, 422 returns; but user says "throws error" (likely 500).
3. **Date/format.** `expires_at` is `nullable|date`. If frontend sends invalid format, Laravel validation handles.
4. **Coupon model casting.** `expires_at` is likely a datetime; passed as Y-m-d string — fine. `percent_off`/`amount_off` integers.
5. **Duplicate code on creation.** Unique rule in StoreCouponRequest prevents duplicate codes (422), not 500.
6. **StripeSyncService::syncStripeCoupon()** when `amount_off`, sets `currency = strtolower($coupon->currency ?: config('billing.currency'))`. If currency missing and config missing, may be `usd` default; Stripe rejects invalid currency codes.
7. **After save, `$coupon->save()` is called twice in `syncCoupon()` (`StripeSyncService.php:76` and again inside helpers when creating product/price? Actually helpers call `$coupon->stripe_coupon_id = ...` then don't save; the final `$coupon->save()` at line 76 persists both). Fine.

**Concrete check.** Look at `StripeService` config check and exception handling. Also check if `STRIPE_KEY/STRIPE_SECRET` are set in `.env`. In a local dev environment without Stripe credentials, `isConfigured()` may return false (so it returns early) — in that case no API error. If they are set to dummy values, Stripe API throws.

**Suggested fix.** 
- Ensure `config/billing.php` defaults work. 
- Wrap Stripe sync in a try/catch inside `CouponService::store()` and treat Stripe sync failures as non-blocking (create the coupon locally and show a warning), or fail gracefully with a clear message. 
- Alternatively, in `StripeSyncService::syncCoupon()`, if not configured return silently; if configured but call fails, log and rethrow as a user-friendly `StripeServiceException` that AJAX handler maps to `{success:false, message:...}`.

**Verify.** Create a coupon with valid data. If Stripe is unconfigured, it should still create the local record and redirect to index with success (local-only mode). If Stripe is configured with valid keys, it should sync. If Stripe returns an error, show a clear validation/API error in the UI.

---

### P2-6 🟠 Verify Stripe coupon/promotion-code sync end-to-end (needs real keys) — 🟠 unverified (evidence of mismatched payload; requires live Stripe keys)

**Evidence.** `storage/logs/errors-2026-10-14.log:19:22:27` shows: `Received unknown parameter: coupon` when syncing a coupon. `StripeSyncService::syncStripePromotionCode()` currently builds a nested payload `['promotion'=>['type'=>'coupon','coupon'=>$id], ...]` but with the classic/embedded SDK usage observed in `StripeService::request()` the correct param for creating a promotion code linked to an existing coupon is a flat `coupon` (and/or the nested shape depends on Stripe API version). The sync already treats promotion-code creation as best-effort with `Log::warning` and returns early if stripe product/coupon missing; it may be stale relative to current Stripe API expectations.

**Fix plan.** With real Stripe API keys in env and test mode:
- Inspect actual Stripe SDK usage (`StripeService::client()` is a singleton; check Stripe API version) and compare to Stripe's `\Stripe\PromotionCode::create()` expected params.
- Correct `syncStripePromotionCode()` to use flat `coupon` (or `coupon_id` as required by the pinned version) and/or set `code` only when creating, avoid the nested `promotion` wrapper. Optionally check if promotion codes should be created per code or just linked.
- Test: create a coupon in app → local record + stripe coupon created → promotion code created/synced successfully; update expiry/active → no `unknown parameter` errors; delete → archived/deactivated safely. Also verify webhook idempotency still holds.

**Status.** Blocked pending real Stripe credentials and verification in a non-prod test mode. No code change made yet without live verification.

**Why.** P2-5 made Stripe sync non-blocking, which hides the fact that a *successful* sync has never been observed locally: `CouponService` logs a warning and still creates the coupon row locally. So "coupon syncs to Stripe" is currently unverified.

**Evidence (stale, investigate).** `storage/logs/errors-2026-10-14.log` records `19:22:27  local.ERROR: Received unknown parameter: coupon` with `type: App\Exceptions\StripeServiceException` at `Modules/Billing/Services/StripeService.php:67` (the `InvalidRequestException` branch of `request()`). Note the current `StripeSyncService::syncStripePromotionCode()` builds a **nested** payload (`['promotion' => ['type' => 'coupon', 'coupon' => $id], 'code' => …]`) whereas the classic Stripe API for `promotionCodes->create()` expects a **flat** `coupon` parameter — so the payload shape should be checked against the pinned Stripe SDK version before changing anything.

**Fix plan.** With test keys configured: create a coupon with `percent_off`, then confirm in Stripe that (a) a Coupon exists, (b) a Promotion Code exists with the same `code`, and (c) `billing_coupons.stripe_coupon_id` / `stripe_promotion_code_id` are populated. Adjust the payload shape if the API rejects it.

**Verify.** A coupon created in the UI produces both Stripe objects and the local row holds both Stripe ids; no `errors-*.log` warnings during creation.
