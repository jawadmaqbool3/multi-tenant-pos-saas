# SaaS Billing System — Stripe Integration & Reports

How the `saas-billing-system` module talks to Stripe and what it reports back to
admins and end users. Code lives under `src/lib/saas-billing-system/`
(role-foldered, see `docs/architecture.md`), routes under
`src/app/api/saas-billing-system/`, and the billing schema in
`docs/saas_billing_system.dbml`.

## Principles

- **Stripe is the source of truth.** Every mutating flow writes to Stripe first,
  then reconciles the local row. Reads come from the local schema
  (`saas_stripe`, table prefix configurable via `SAAS_STRIPE_SCHEMA`).
- **Stripe ids are always resolved server-side** from the authenticated session
  or a local row — never trusted from the client.
- **No Stripe call runs inside a DB transaction.** The billing pool is small
  (`src/lib/db/pool.ts`); webhook handlers prefetch Stripe objects *before*
  opening a transaction so the transaction only writes.
- **Idempotent writes everywhere**: invoices key on the unique Stripe invoice id,
  payments on `payment_intent.*` / `charge.*`, usage on an `idempotency_key` plus
  a Stripe meter-event `identifier` (`saas_usage_` prefix), redemptions on the
  unique `stripe_discount_id`.

## Stripe client

`src/lib/saas-billing-system/libs/lib.stripe.ts` is the only place a client is
constructed:

- API version pinned: `STRIPE_API_VERSION = "2025-08-27.basil"`.
- Timeout 20s, `maxNetworkRetries = 2`.
- `isStripeConfigured()` guards every entrypoint so an unconfigured server
  returns a typed "unconfigured" error rather than a 500.

`src/lib/saas-billing-system/services/service.stripe.ts` is the `StripeService`
base class. It wraps every SDK call in `request()`, which translates SDK errors
into a `StripeServiceError` with a `StripeErrorKind`
(`unconfigured | authentication_failed | invalid_request | not_found |
rate_limited | api_unavailable | …`) and an `isRetryable` flag callers map to
HTTP status.

## Stripe API methods called

Each resource has a dedicated wrapper service; only these files touch the SDK.

| Resource | Wrapper | Methods |
| --- | --- | --- |
| Customers | `service.stripe-customer.ts`, `service.payment.ts` | `customers.create`, `customers.retrieve`, `customers.update`, `customers.listPaymentMethods` |
| Payment methods | `service.payment.ts` | `paymentMethods.retrieve`, `paymentMethods.attach`, `paymentMethods.detach` |
| Products | `service.stripe-product.ts` | `products.create`, `products.retrieve`, `products.update`, `products.list`, archive via `products.update({ active: false })` |
| Prices | `service.stripe-pricing.ts` | `prices.create`, `prices.retrieve`, `prices.update`, `prices.list`, archive via `prices.update({ active: false })` |
| Meters | `service.stripe-meter.ts` | `billing.meters.create`, `billing.meters.retrieve`, `billing.meters.list`, `billing.meters.deactivate` |
| Meter events | `libs/lib.usage-stripe.ts` | `billing.meterEvents.create` |
| Coupons | `service.stripe-coupon.ts` | `coupons.create`, `coupons.retrieve`, `coupons.update`, `coupons.del` |
| Promotion codes | `service.stripe-coupon.ts` | `promotionCodes.create`, `promotionCodes.retrieve`, `promotionCodes.update` (activate/archive) |
| Checkout | `service.checkout.ts` | `checkout.sessions.create` |
| Subscriptions | `service.subscription.ts`, `service.admin.ts` | `subscriptions.retrieve`, `subscriptions.update`, `subscriptions.cancel`, `subscriptions.resume` |
| Invoices | `service.invoice.ts`, `service.admin.ts` | `invoices.list`, `invoices.voidInvoice`, `invoices.del` |
| Charges | `service.webhook.ts` | `charges.retrieve` (prefetch for `payment_intent.succeeded`) |

Plan→Stripe reconciliation lives in `service.stripe-sync.ts`
(`service.stripe-product`, `service.stripe-pricing`); it creates/updates/archives
the Product and its Prices so the locally-published plan matches Stripe.

## Webhooks

Endpoint: `src/app/api/saas-billing-system/webhook/route.ts`
(`runtime = "nodejs"`, `dynamic = "force-dynamic"`).

1. The raw body is streamed with a hard cap of `MAX_BODY_BYTES = 1 MiB` (1 MiB),
   aborting oversized payloads.
2. The `STRIPE_WEBHOOK_SECRET` signature is verified before the event is
   dispatched to `service.webhook.ts`. Unsigned/invalid requests are rejected.

### Processing contract (`service.webhook.ts`)

- **Idempotent**: `stripe_webhook_events` is unique on `stripe_event_id`. A
  duplicate delivery is acknowledged and stops; the first delivery owns the sync.
- **Atomic**: the event row, the business sync, and the processed marker are
  written in one transaction. A failure rolls back and records
  `failed_at` / `failure_message` / `retry_count` so Stripe retries.
- **Only safe display fields are stored** from payloads.
- A unique violation from the idempotency row is distinguished from a duplicate
  business row (the latter is a real bug and must not be swallowed).
- **Prefetch before the transaction**: for subscription/invoice events the
  referenced subscription (and, for `payment_intent.succeeded`, the charge) is
  retrieved *outside* the transaction with a small retry/backoff
  (`PREFETCH_BACKOFF_MS = [0, 250, 750]`), so the transaction performs no network
  call.

### Handled event types

| Event(s) | Handler effect |
| --- | --- |
| `checkout.session.completed` | Links Stripe customer ↔ local user authoritatively from `users.stripe_customer_id` (a `client_reference_id` is only a safety-net backfill when emails match; metadata is never authoritative), updates the local `checkout_sessions` row, and backfills the subscription. |
| `customer.subscription.created` / `.updated` / `.deleted` | Syncs the local subscription row and records/clears the promotion redemption implied by the attached discount — both in the same transaction. |
| `invoice.created` / `.finalized` / `.paid` / `.payment_failed` / `.updated` / `.voided` / `.marked_uncollectible` | Upserts the local invoice keyed on the Stripe invoice id; for subscription invoices it also refreshes the subscription from the prefetched object. |
| `payment_intent.succeeded` / `.payment_failed` / `.processing` / `.canceled` | Syncs the local payment row (the charge supplies receipt URL and card details on success). |
| `charge.succeeded` / `.failed` / `.refunded` | Syncs the local payment row. |
| `payment_method.attached` / `.updated` / `.detached` | Keeps local payment-method rows current. |
| anything else | Ignored (acknowledged, no work). |

## Reports

### Admin

| Report | Endpoint | Source |
| --- | --- | --- |
| Overview totals | `GET admin/overview` | `queries/query.admin.ts` `getAdminOverview`: user count; active/trialing/past_due/canceled/paused subscription counts; successful/failed payments; `totalCollectedCents`; **pending Stripe usage reports**; currency breakdown. |
| Customer balance | `GET admin/balance?userId=` | Live Stripe customer balance for the linked user. |
| Subscriptions | `GET admin/subscriptions`, `POST admin/subscriptions/sync` | Local subscription list; bulk reconciliation against Stripe per customer. |
| Payments | `GET admin/payments` | Local payments page. |
| Promotion codes / redemptions | `GET admin/promotion-codes`, `…/[id]/redemptions` | Local codes + audit of who redeemed them. |
| Users / meters / features / plans | `admin/users`, `admin/usage-meters`, `admin/features`, `admin/plans` | Local admin lists and editors. |
| Retry unacknowledged usage | `POST admin/usage-reports/retry` | `service.admin.ts` `retryPendingUsageReports`: re-reports up to 50 pending usage events per call; idempotent via the meter-event identifier. Returns `reported / attempted / failed / skipped` — `skipped` marks events that can never be reported (missing customer or meter). Recorded in the admin audit log. |

Subscription mutations the admin surface exposes: **cancel**, **pause**,
**resume** (`subscriptions/[id]/{cancel,pause,resume}`), all performed against
Stripe first, then synced locally.

### End user

| Report | Endpoint | Source |
| --- | --- | --- |
| Usage history | `GET customer/usage/history?meter=&page=&limit=&from=&to=` | Paginated, daily-aggregated per-meter usage for the session user. |
| Current usage | `GET customer/usage` | Current subscription usage / allowance for the session user. |
| Invoices | `GET customer/invoices` | Local invoices for the session user (backfilled from Stripe on first read). |
| Payments | `GET customer/payments` | Local payments for the session user. |
| Subscription | `GET customer/subscription`, plus `…/cancel`, `…/resume`, `…/change-plan`, `…/sync` | The user's current plan, prices, features and meters. |
| Payment methods | `customer/payment-methods` (+ `…/setup`, `…/[id]`, `…/[id]/default`) | List/add/set-default/remove the user's cards. |

All user reports scope reads to the user id from the authenticated session; a
client never supplies a Stripe id or another user's id.
