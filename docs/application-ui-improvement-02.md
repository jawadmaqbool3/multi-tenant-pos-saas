# Application UI Improvement — Phase 02

## Adopting the "Niche Spy Workspace" visual language

**Reference design:** `https://portfolio-teal-sigma-icewu8o8ju.vercel.app/niche-spy-workspace/dashboard`
(and every page linked in its sidebar)

**Goal:** Re-skin the existing POS app so it uses the *same visual language* as the reference —
colors, typography, card treatment, buttons, dropdowns, badges, tabs, tables, icons, inputs and
modals — **without changing the existing page layouts / information architecture**, in **both dark
and light mode**. The reference is dark-only; we must derive an equally polished light mode.

**Relationship to Phase 01 (`application-ui-improvement.md`):** Phase 01 established the token
override layer (`public/assets/css/theme-overrides.css`) and picked a *similar* indigo/slate
palette. Phase 02 closes the remaining gap between what we shipped and the actual reference design
(precision of tokens, the component classes that were never ported, and full light-mode parity).

---

## 1. What the reference actually is (ground truth)

The reference is a **Next.js + Tailwind v4** app. Its design system is defined by:

1. A scoped CSS-variable block `.niche-scope` (colors).
2. A Tailwind v4 theme layer (`@layer theme`) mapping those colors to utility names.
3. A tiny `@layer components` with only 4 custom classes.
4. Everything else is **Tailwind utility combinations** applied per element.

### 1.1 Extracted color tokens (`target2.css` / `.niche-scope`)

| Token | Value | Role |
|-------|-------|------|
| `--color-void` | `#07080f` | App/page background |
| `--color-surface` | `#0c0e1a` | Header/footer chrome |
| `--color-raised` | `#101220` | Inputs, popovers, elevated chips |
| `--color-card` | `#0f1121` | Card surfaces |
| `--color-brand` | `#6c63ff` | Primary accent |
| `--color-brand-2` | `#a89dff` | Accent text/icons on dark |
| `--color-mint` | `#00d4aa` | Positive / secondary accent |
| `--color-ink` | `#f0f2fa` | Primary text |
| `--color-ink-2` | `#a8b0cc` | Secondary text |
| `--color-muted` | `#5a6380` | Muted/meta text |
| `--color-line` | `rgba(255,255,255,0.06)` | Hairline borders/dividers |
| `--color-line-hi` | `rgba(255,255,255,0.12)` | Stronger borders, button outlines |
| `--color-rose-400` | `#ff667f` | Danger |
| `--color-amber-400` | `#fcbb00` | Warning |
| `--color-blue-400` | `#54a2ff` | Info |
| `--color-white` | `#fff` | Inverse text on brand |

### 1.2 Geometry / elevation (Tailwind v4 theme)

| Token | Value |
|-------|-------|
| `--radius-sm` | `8px` |
| `--radius-md` | `14px` |
| `--radius-lg` | `20px` |
| `--radius-xl` | `28px` |
| `--shadow-md` | `0 8px 32px rgba(0,0,0,0.5)` |
| `--shadow-lg` | `0 24px 64px rgba(0,0,0,0.6)` |
| `--ease-spring` | `cubic-bezier(.175,.885,.32,1.275)` |

> Note: reference *cards* are `rounded-lg` = **20px**; small stat tiles are `rounded-md` = **14px**.
> Our current override uses a flat 14px for everything — needs correcting.

### 1.3 Typography

| Family | CSS var | Used for |
|--------|---------|----------|
| **Syne** (600/700/800) | `--font-display` | Headings, KPI numbers, logo wordmark, card titles |
| **Inter** (400–700) | `--font-sans` | Body, forms, buttons, tables |
| **JetBrains Mono** (400–600) | `--font-mono` | Eyebrows, table headers, numeric callouts, meta labels |

Already imported in `resources/views/layouts/style.blade.php` (Syne + JetBrains Mono).

### 1.4 The four custom component classes

```css
.eyebrow      /* mono, uppercase, .22em tracking, brand color, 22px rule before text */
.grad-text    /* brand -> brand-2 -> mint text gradient (used on 404 / hero numbers) */
.btn-primary  /* brand bg, white, radius-md, glow shadow; hover #7b73ff + translateY(-2px) */
.btn-outline  /* transparent, line-hi border, ink text; hover brand border/text + tint */
```

### 1.5 Global chrome (from `target1.css`)

- **Background wash:** `body:after` fixed radial gradients — `radial-gradient(80% 60% at 10% 0, #6c63ff1c, transparent 55%)` + `radial-gradient(60% 50% at 90% 100%, #00d4aa14, transparent 55%)`.
- **Scrollbar:** `width:3px`, thumb `var(--color-brand)`, `scrollbar-width:thin`.
- **Selection:** `background:#6c63ff59; color:var(--color-ink)`.
- **Focus ring:** `2px solid var(--color-brand-2); outline-offset:2px; radius-sm`.
- **Native `<select>`:** `color-scheme:dark; background:card; options: raised; checked: brand`.
- **Scroll progress bar** (`.scroll-progress`) and **scroll-to-top button** (`.scroll-top`) — brand→mint gradient.
- **Mobile nav drawer** (`.nav-drawer`) with slide-in animation.

### 1.6 Component patterns extracted from reference JS chunks

These are the *exact* utility recipes the reference reuses. We will reproduce them as our own
Blade components + CSS classes so pages stay declarative.

| Component | Reference recipe |
|-----------|------------------|
| **Section card** | `rounded-lg border border-line bg-card shadow-md` (header: `border-b border-line px-6 py-5`; body: `px-6 py-5`) |
| **Stat card** | `rounded-md border border-line bg-card p-5 shadow-md` (label: mono uppercase `text-[0.6rem] text-muted`; value: `font-display text-lg font-bold text-ink tabular-nums`) |
| **Primary button** | `inline-flex h-11 items-center gap-2 rounded-md bg-brand px-5 text-sm font-semibold text-white shadow-[0_0_0_1px_rgba(108,99,255,0.5),0_8px_28px_rgba(108,99,255,0.35)] hover:bg-[#7b73ff]` |
| **Outline button** | `inline-flex h-11 items-center gap-2 rounded-md border border-line-hi px-5 text-sm font-medium text-ink hover:border-brand hover:text-brand` |
| **Small/icon button** | `inline-flex h-9/h-10 items-center gap-1.5 rounded-md border border-line-hi px-3.5 text-sm text-ink hover:border-brand hover:text-brand` |
| **Badge — brand** | `rounded border border-brand/30 bg-brand/10 px-1.5 py-0.5 font-mono text-[0.6rem] uppercase tracking-[0.1em] text-brand-2` |
| **Badge — mint** | `inline-flex items-center rounded border border-mint/20 bg-mint/10 px-2 py-0.5 font-mono text-[0.68rem] font-medium text-mint tabular-nums` |
| **Badge — rose / amber** | `border-rose-400/25 bg-rose-400/10 text-rose-300` · `border-amber-400/25 bg-amber-400/10 text-amber-300` |
| **Chip (neutral)** | `inline-flex items-center rounded-md border border-line bg-white/[0.03] px-2.5 py-1 font-mono text-[0.68rem] text-ink-2` |
| **Input (default)** | `h-11 rounded-md border border-line-hi bg-raised px-4 text-sm text-ink outline-none focus:border-brand` |
| **Input (bare)** | `rounded-md border border-line-hi bg-white/[0.03] px-3.5 py-2.5 text-ink focus:border-brand placeholder:text-muted` |
| **Small input / select** | `h-9 rounded-md border border-line-hi bg-raised px-2.5 text-sm text-ink focus:border-brand` |
| **Checkbox** | `h-4 w-4 rounded border-line-hi bg-white/[0.03] accent-brand` |
| **Table** | `w-full border-collapse text-left text-sm`; head `border-b border-line` + `px-3 py-2 font-mono text-[0.62rem] uppercase tracking-[0.14em] text-muted`; row `border-b border-line last:border-0`; cell `px-3 py-2.5` |
| **Empty state** | `flex flex-col items-center justify-center rounded-lg border border-dashed border-line-hi px-4 py-10 text-center` |
| **Modal** | backdrop `fixed inset-0 z-[200] bg-black/70 backdrop-blur-sm`; panel `w-full max-w-md rounded-lg border border-line-hi bg-card shadow-lg` |
| **Toast** | `fixed bottom-5 right-5 z-[300] max-w-sm` stacked cards |
| **Progress bar** | track `h-1.5 rounded-full bg-white/5`; fill `h-full rounded-full bg-linear-to-r from-brand to-mint` |
| **Sidebar nav group** | section label `px-3 pb-1.5 font-mono text-[0.6rem] uppercase tracking-[0.16em] text-muted`; link `flex items-center gap-2.5 rounded-md px-3 py-2 text-sm`; active `bg-brand/10 font-medium text-brand-2` + left gradient bar |
| **Sidebar brand lockup** | `h-9 w-9 rounded-lg border border-brand/30 bg-brand/10 shadow-[0_0_24px_rgba(108,99,255,0.25)]` icon tile + wordmark |
| **Header (mobile)** | `sticky top-0 h-14 border-b border-line bg-void/85 backdrop-blur-xl` |

---

## 2. Current state of the POS app

### 2.1 Stack

| Item | Finding |
|------|---------|
| Templating | Blade — **124** `.blade.php` files (`resources/views` + `Modules/*/Resources/views`) |
| UI framework | Metronic 8 / Bootstrap 5 (pre-compiled, static) |
| Theme CSS | `public/assets/css/style.bundle.css` (~1.5 MB, token-based) |
| Override layer | `public/assets/css/theme-overrides.css` (**786 lines — already exists**) |
| Fonts | Inter + Syne + JetBrains Mono already imported |
| Icons | **Metronic inline SVG** sprite style (24px, `svg-icon-*` classes) |
| JS plugins | Select2, DataTables, ApexCharts, Toastr, Flatpickr/modals |

### 2.2 What Phase 01 already shipped (`theme-overrides.css`)

✅ Brand palette remap (`--kt-primary: #6c63ff`, mint success), dark + light token blocks
✅ Card borders/radius/shadow (flat 14px), dropdown/modal surfaces
✅ Button radius + primary glow, form control radius, `.form-label` mono uppercase
✅ Table restyle (mono uppercase headers, hover, pagination)
✅ Badge pill restyle incl. status dots
✅ Dark-sidebar active state with brand tint + gradient bar
✅ Scrollbars

### 2.3 Gaps vs. the reference

| Area | Status | Notes |
|------|--------|-------|
| Background radial wash (`body:after`) | ❌ missing | Reference's signature ambient glow |
| `.eyebrow` / `.grad-text` classes | ❌ missing | Core brand motifs |
| Card radius precision (20px sections / 14px stats) | ⚠️ partial | Currently flat 14px |
| Primary/outline/ghost button fidelity (glow, lift, sizes) | ⚠️ partial | Only primary glow present |
| Tabs (`nav-tabs` / line tabs) | ❌ not styled | Billing plan form uses them |
| Select2 / Flatpickr theming to `raised` surface | ⚠️ partial | Select2 radius only |
| Checkbox / switch (accent-brand) | ⚠️ partial | Bootstrap default |
| Toastr restyle | ❌ missing | Uses plugin defaults |
| Empty states | ❌ missing | No shared class |
| Scroll progress + scroll-top | ❌ missing | |
| Icons (lucide-style 16px stroke) | ❌ | Metronic SVG set |
| Numeric/mono meta styling (`tabular-nums`) | ⚠️ partial | Injection via JS only |
| Light-mode parity of dark-first components | ⚠️ partial | Sidebar forced dark; check contrast |
| Stat/KPI cards = reference `rounded-md p-5` recipe | ⚠️ partial | Metronic markup |

---

## 3. Design token spec for the POS app

We keep the **`--kt-*` bridge** (Metronic consumes those) and add our own `--ui-*` semantic layer.
Load order stays: `style.bundle.css` → `theme-overrides.css`.

### 3.1 Dark theme (`[data-theme=dark]`) — match reference exactly

```css
[data-theme=dark] {
  --ui-void:    #07080f;
  --ui-surface: #0c0e1a;
  --ui-raised:  #101220;
  --ui-card:    #0f1121;
  --ui-line:    rgba(255,255,255,0.06);
  --ui-line-hi: rgba(255,255,255,0.12);
  --ui-ink:     #f0f2fa;
  --ui-ink-2:   #a8b0cc;
  --ui-muted:   #5a6380;
  --ui-brand:   #6c63ff;
  --ui-brand-2: #a89dff;
  --ui-mint:    #00d4aa;
  /* bridge to Metronic */
  --kt-primary: #6c63ff; --kt-primary-active: #7b73ff;
  --kt-card-bg: var(--ui-card); --kt-card-border-color: var(--ui-line);
  --kt-body-bg: var(--ui-void);
  /* ...existing dark bridge retained... */
}
```

### 3.2 Light theme (`html:not([data-theme=dark])`) — derived, same semantics

The reference has no light mode, so derive it so every dark token has a light twin:

| Dark token | Light twin | Rationale |
|------------|-----------|-----------|
| `void #07080f` | `#f6f7fb` | Page wash (soft violet-grey) |
| `surface #0c0e1a` | `#ffffff` | Header / footer |
| `raised #101220` | `#ffffff` | Inputs / popovers |
| `card #0f1121` | `#ffffff` | Cards |
| `line rgba(255,255,255,.06)` | `rgba(16,19,28,.08)` | Hairline dividers |
| `line-hi rgba(255,255,255,.12)` | `rgba(16,19,28,.14)` | Strong borders / outline buttons |
| `ink #f0f2fa` | `#10131c` | Primary text |
| `ink-2 #a8b0cc` | `#4b5568` | Secondary text |
| `muted #5a6380` | `#8b93a7` | Muted/meta |
| `brand #6c63ff` | `#6c63ff` | Same accent |
| `brand-2 #a89dff` | `#5b52f0` | Accent **text** must darken for contrast on white |
| `mint #00d4aa` | `#00b894` | Positive accent (contrast) |
| rose/amber/blue 400 | `#e5484d` / `#d97706` / `#2563eb` | 600-ish for AA on white |

Ambient wash in light mode: same two radial gradients at **lower opacity**
(e.g. `#6c63ff14` and `#00d4aa10`) so it reads as a subtle tint, not a stain.

### 3.3 Geometry / motion (both themes)

```css
:root {
  --ui-radius-sm: 8px;  --ui-radius: 14px;
  --ui-radius-lg: 20px; --ui-radius-xl: 28px;
  --ui-shadow-md: 0 8px 32px -12px rgba(7,8,15,.5);
  --ui-shadow-lg: 0 24px 64px -18px rgba(7,8,15,.6);
  --ui-glow: 0 0 0 1px rgba(108,99,255,.5), 0 8px 28px rgba(108,99,255,.35);
  --ui-ease-spring: cubic-bezier(.175,.885,.32,1.275);
}
```

---

## 4. Component normalization spec

For each component: **reference look → POS implementation**. Where the same metronic class is used
across many views, style it centrally in `theme-overrides.css`; where markup is wrong, edit the
Blade (or introduce a component).

### 4.1 Cards
- `.card` → `border:1px solid var(--ui-line); border-radius:var(--ui-radius-lg); background:var(--ui-card); box-shadow:var(--ui-shadow-md)`.
- Stat cards (`.card` with `#sales_this_month` etc. / `.card-flush h-md-50`) → `--ui-radius` (14px) and `p-5` rhythm.
- Card header: `border-bottom:1px solid var(--ui-line)`, transparent bg, `px-6 py-5`.
- Remove `card-flush` chrome exceptions from `theme-overrides.css:387` so section cards never go borderless.
- **Files:** `theme-overrides.css`, `dashboard/dashboard.blade.php`, all `*/index.blade.php`.

### 4.2 Buttons
Establish a strict hierarchy and style by class:
- `.btn-primary` → brand bg, white, glow shadow, `radius-sm`; hover `#7b73ff` + `translateY(-1px)`.
- Add `.btn-outline` → transparent, `--ui-line-hi` border, ink text; hover brand border/text + `rgba(108,99,255,.05)` tint.
- `.btn-light`, `.btn-secondary`, `.bg-body` hybrids → neutral `--ui-raised` surface, `--ui-line` border.
- `.btn-icon` → square `radius-sm`, line-hi border, hover brand.
- Sizes: `.btn-sm` h-34, default h-40, `.btn-lg` h-48 (map from reference 40/44/50 to app density).
- **Files:** `theme-overrides.css`; replace hybrid `bg-body btn-color-gray-700` buttons in `dashboard/dashboard.blade.php`, and `nav-tabs`-adjacent CTAs.

### 4.3 Badges / status pills
Port the reference badge recipes as `.badge-*` classes and map existing `badge-light-*`:
- `.badge-brand`, `.badge-mint`, `.badge-danger`, `.badge-warning`, `.badge-info`, `.badge-neutral` (chip).
- Keep the status dot variant (already in overrides).
- **Files:** `theme-overrides.css`; ensure DataTables `status` column renderers output these classes (check `Modules/*/Repositories/*`).

### 4.4 Inputs, selects, textareas, checkboxes, switches
- `.form-control`, `.form-select` → `border:1px solid var(--ui-line-hi); background:var(--ui-raised); border-radius:var(--ui-radius-sm)`.
- `.form-control-solid` → `background:var(--ui-raised); border-color:transparent`.
- Focus → `border-color:var(--ui-brand); box-shadow:0 0 0 3px rgba(108,99,255,.22)`.
- `.form-check-input` → `accent-color:var(--ui-brand)`; switch track brand when checked.
- **Select2** → theme dropdown surface `var(--ui-raised)`, search input, hover/selected states brand-tinted.
- **Flatpickr** (if used) → same surface/brand.
- Custom file upload dropzone → `border:1px dashed var(--ui-line-hi); border-radius:var(--ui-radius)`.
- **Files:** `theme-overrides.css`, `layouts/style.blade.php` (file-upload box), form partials.

### 4.5 Tabs
Two reference-compatible variants:
- **Line tabs** (`.nav-line-tabs`, used in `billing::plans._form`) → transparent bg, `border-bottom:1px solid var(--ui-line)`, active link `color:brand; border-bottom:2px solid brand`.
- **Pill tabs** (optional) → `rounded-md`, active `bg-brand/10 text-brand-2`.
- **Files:** `theme-overrides.css`, `Modules/Billing/Resources/views/plans/_form.blade.php`.

### 4.6 Tables
Already close. Align numbers: header `font-mono 0.62rem uppercase .14em`, cells `px-3 py-2.5`,
row divider `--ui-line`, hover `rgba(108,99,255,.06)`. Ensure DataTables controls match `.form-*`.
- **Files:** `theme-overrides.css`; verify DataTables-generated markup.

### 4.7 Dropdowns / menus
- `.dropdown-menu`, Metronic `.menu-sub-dropdown` → `background:var(--ui-raised); border:1px solid var(--ui-line); border-radius:var(--ui-radius); box-shadow:var(--ui-shadow-lg)`.
- Menu item hover → `background:rgba(108,99,255,.08); color:var(--ui-brand-2)`.
- **Files:** `theme-overrides.css`, header/dashboard dropdowns.

### 4.8 Sidebar
- Keep dark-sidebar layout but **use reference tokens**: bg `#0a0b14` (or `--ui-surface`), border `--ui-line`.
- Section headings = `.eyebrow`-style mono uppercase muted labels.
- Active item = `bg-brand/10`, `text-brand-2`, 3px brand→mint left bar (already present); widen to match group indentation.
- Brand lockup: square icon tile (brand/30 border, brand/10 bg, glow) + Syne wordmark + mono subtitle.
- **Files:** `layouts/aside.blade.php`, `components/aside-menu-links.blade.php`, `asideSettings.blade.php`.

### 4.9 Header
- Sticky, `background: color-mix(in srgb, var(--ui-surface) 85%, transparent)`, `backdrop-blur`, border-bottom `--ui-line`.
- Icon buttons → `radius-sm`, line-hi border, hover brand (replace `btn-custom`).
- Keep theme switcher; restyle its dropdown as §4.7.
- **Files:** `layouts/header.blade.php`, `theme-overrides.css`.

### 4.10 Modals / offcanvas
- `.modal-content` → `background:var(--ui-card); border:1px solid var(--ui-line-hi); border-radius:var(--ui-radius-lg); box-shadow:var(--ui-shadow-lg)`.
- Backdrop → `rgba(0,0,0,.7)` + `backdrop-blur-sm`.
- Titles in Syne; footers use button tiers.
- **Files:** `theme-overrides.css`, `components/*-modal.blade.php`, `components/delete.blade.php`.

### 4.11 Toasts
Wrap Toastr with reference styling: surface `--ui-raised`, border `--ui-line`, left accent bar in
semantic color, radius `--ui-radius`, shadow `--ui-shadow-md`, mono title.
- **Files:** `layouts/script.blade.php` (toastr config), `theme-overrides.css`.

### 4.12 Empty states
Add `.empty-state` class matching reference. Apply to DataTables empty rows and `@empty` blocks.
- **Files:** `theme-overrides.css`, index views.

### 4.13 Ambient + micro-interactions
- `body::after` radial brand/mint wash (dark full strength, light at ~30%).
- `::selection` brand tint.
- `.scroll-progress` (2px brand→mint, top) and `.scroll-top` button — optional, low priority; add JS in `layouts/script.blade.php`.
- Numeric cells/KPI values get `font-variant-numeric: tabular-nums`.

### 4.14 Icons
Reference uses **16px stroke icons** (lucide-style: `viewBox 0 0 24 24`, `fill:none`,
`stroke:currentColor; stroke-width:2`). Metronic uses 24px filled SVGs.
- **Recommended:** introduce a small inline `<x-icon name="..."/>` Blade component using lucide paths,
  and migrate nav + action icons. Keep Metronic icons where migration is risky (charts, brand marks).
- This is the largest single sub-task; scope to nav + buttons + stat cards first.

---

## 5. Files / artifacts to touch

| File | Change |
|------|--------|
| `public/assets/css/theme-overrides.css` | **Primary** — full token re-spec + all component classes |
| `resources/views/layouts/style.blade.php` | Add `.eyebrow`/`.grad-text` if kept in Blade; upload dropzone class |
| `resources/views/layouts/basic.blade.php` | `body::after` hook (class), default theme, scroll-progress/top markup |
| `resources/views/layouts/header.blade.php` | Icon buttons, dropdown, theme menu restyle |
| `resources/views/layouts/aside.blade.php` + `components/aside-menu-links.blade.php` | Nav groups, lockup, active states |
| `resources/views/layouts/script.blade.php` | Toastr config, scroll-progress/top JS |
| `resources/views/components/*.blade.php` | Modals, selectors → new surfaces |
| `resources/views/dashboard/dashboard.blade.php` + `_js.blade.php` | Stat cards, chart palette, tabular nums |
| `Modules/*/Resources/views/**/*.blade.php` (all ~90) | Blueprint pass: tabs, forms, buttons, badges, empty states |
| **New** `resources/views/components/icon.blade.php` | Lucide-style icon component |
| **New** `resources/views/components/stat-card.blade.php` (optional) | Reference stat-tile recipe |
| **New** `resources/views/components/page-header.blade.php` (optional) | Eyebrow + Syne title + actions |
| **New** `public/assets/js/ui-theme.js` (optional) | Scroll progress/top, chart theme palette |

---

## 6. Phased rollout

| Phase | Scope | Deliverable | Est. |
|-------|-------|-------------|------|
| **0 — Tokens** | Re-spec `--ui-*` dark tokens to exact reference values; derive full light twin; radii/shadows/motion; background wash; selection; scrollbars | Whole app instantly matches reference palette in both themes | 0.5 d |
| **1 — Shell** | Sidebar (groups, lockup, active), header (sticky blur, icon buttons, dropdown), footer, mobile drawer | Navigation shell matches reference | 1 d |
| **2 — Cards & Stats** | Section/stat card recipes, KPI cards, chart card headers | Dashboard parity | 0.75 d |
| **3 — Buttons & Badges** | Button tiers + `.btn-outline`, `.eyebrow`, `.grad-text`, badge/chip classes | Consistent actions & statuses | 0.75 d |
| **4 — Forms** | Inputs/selects/textarea/checkbox/switch, Select2/Flatpickr, upload dropzone, tabs | All create/edit pages | 1 d |
| **5 — Data** | Tables, DataTables controls, pagination, empty states, dropdown filters | All index/report pages | 0.75 d |
| **6 — Overlays** | Modals, offcanvas, toasts, tooltips | Feedback surfaces | 0.5 d |
| **7 — Icons** | `<x-icon>` component; migrate nav + action icons to lucide-stroke | Visual vocabulary parity | 1 d |
| **8 — Micro & a11y** | Scroll progress/top, focus rings, `aria-label`, `alt`, contrast pass, print views | Polish + accessibility | 0.5–1 d |

Total: **~6.5–7.5 developer-days**.

### Per-view checklist (repeat for each screen)
- [ ] Cards use section/stat recipe (radius 20/14, line border, shadow-md)
- [ ] Buttons use the documented tier
- [ ] Inputs/selects use `raised` surface + brand focus
- [ ] Badges use `.badge-*` classes
- [ ] Tabs use line/pill variant
- [ ] Numeric values use `tabular-nums`
- [ ] Verified in **light and dark**
- [ ] Verified at 360 / 768 / 1024 / 1440 px
- [ ] No raw hex / inline colors

---

## 7. Light-mode derivation rules

To guarantee parity, every reference dark token gets one light twin (table in §3.2) and the
following rules apply:

1. **Never** use `bg-white` for a surface that must flip — always `--ui-card` / `--ui-raised`.
2. Accent **text** uses `--ui-brand-2` (dark lavender on dark → deep indigo on light).
3. Accent **fills** keep `--ui-brand` in both themes.
4. `bg-white/[0.03]` chips → `--ui-raised` (already flips).
5. The ambient wash opacity drops in light mode so text contrast stays ≥ 4.5:1.
6. Sidebar stays dark in both themes (matches reference) but must not clash with the light content area — keep the same `#0a0b14` and `--ui-line`.

---

## 8. Definition of done

- `theme-overrides.css` is the single source of truth; no page needs inline color overrides.
- Side-by-side with the reference, the dashboard, a list page, a form page, tabs, modal and toast are
  visually indistinguishable in **dark**; light mode is a faithful, high-contrast twin.
- All 124 templates pass the per-view checklist in both themes and at 4 breakpoints.
- No raw hex in Blade; no `bg-white` outside print; focus states visible; icon-only controls have
  accessible names.
- DataTables, Select2, ApexCharts, Toastr and modals still function.

---

## 9. Risks & mitigations

| Risk | Mitigation |
|------|-----------|
| Vendor specificity beats overrides | Keep loading last; scope with `:root`/`[data-theme]`; minimal `!important` |
| Plugin markup (Select2/DataTables/Toastr) differs from reference | Style at `.select2-*`, `.dataTables_*`, `#toast-container` level |
| Icon migration is large | Do nav/actions first; keep Metronic icons where needed |
| Light-mode contrast regressions | Token table + contrast check per phase; gradient wash toned down |
| Scope across 124 views | Phases 0–4 app-wide, then per-module tickets for pages 5–8 |
| Print views break | Exclude `*/print.blade.php` from surface overrides |
