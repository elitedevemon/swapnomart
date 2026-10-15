# Delivery Hub — merchant setup and operations

Documentation reviewed: 14 October 2026. Adapters are implemented; real-account acceptance remains a go-live requirement. This guide does not promise error-free external provider behavior.

## 1. Credentials and environments

| Provider | Admin fields | Environments / origins |
|---|---|---|
| Pathao | Client ID, client secret, merchant email/username, merchant password; active pickup store ID | Local simulation; official sandbox `https://courier-api-sandbox.pathao.com`; live `https://api-hermes.pathao.com` |
| Steadfast | API key, secret key | Local simulation or live. Choose the documented origin appropriate to the merchant account: `https://portal.steadfast.com.bd/api/v1` (official package default) or `https://portal.packzy.com/api/v1` (reviewed Postman documentation). No arbitrary URLs or automatic redirects. |
| Manual | Optional tracking number | Local record only; arrange fulfillment yourself. |

Obtain credentials from your own merchant account. Documentation examples or credentials found online must never be used. Shared demo login must be disabled before secrets can be stored. A blank password field preserves a saved value; saved secrets are never returned to the browser.

Pathao uses the OAuth password grant and caches encrypted access/refresh tokens, refreshing them before expiry. Token caches are isolated by account, environment and endpoint. Steadfast sends `Api-Key` and `Secret-Key` headers.

**Connection tests are read-only business operations:** Pathao authenticates and lists stores; Steadfast reads the balance. They do not create orders, returns or pickups. A successful check is required before real/sandbox dispatch. Enable the provider, choose the environment and save before testing. Select the pickup store and save again for Pathao.

Changing account credentials/environment/origin does not silently redirect existing shipment tracking to a different account. Restore the original account before syncing/reconciling those shipments.

## 2. Implemented provider operations

| Operation | Pathao | Steadfast |
|---|---|---|
| Connection/auth | OAuth issue/refresh token + store list | Balance endpoint + API headers |
| Pickup-store configuration | Store list + store ID | Merchant account configuration |
| Recipient location lists | Cities → zones → areas | Full address, not an invented location lookup |
| Price estimate | Merchant price-plan API | No verified estimate endpoint; shop rates remain independent |
| Consignment creation | Durable, explicit individual dispatch | Durable, explicit individual dispatch |
| Status sync | Short-info by consignment | Status by consignment |
| Uncertain booking reconciliation | Verify entered consignment's merchant order reference | Verify invoice status + explicit merchant-portal confirmation of matching consignment ID |
| Webhooks | Shared signature + configurable handshake response | Bearer shared secret |
| Return request + return status | Merchant portal | Documented create/get return request endpoints |
| Pickup request | Existing merchant pickup-store workflow | Documented pickup request, portal address/police-station IDs |
| Packing document | Internal BONON slip; official label in merchant portal | Same |
| Cancellation / unsupported operations | Merchant portal | Merchant portal |

Bulk API variants were deliberately not guessed. Pathao bulk is asynchronous; Steadfast package/Postman examples differ in bulk-body format. This build dispatches orders individually rather than claiming safe bulk reconciliation without evidence. Other couriers can be recorded manually; no undocumented RedX/eCourier integration is claimed.

## 3. Checkout fees versus courier costs

Admin → Delivery Hub → Delivery rules:
- Inside Dhaka, Dhaka suburbs and outside-Dhaka standard rates.
- Free-standard-delivery threshold, optional express surcharge, optional free store pickup.
- English/Bengali delivery estimate text and pickup address.
- Preferred dispatch provider and fallback tracking interval (5–120 minutes).

The **server** recomputes checkout charges from actual product prices and the configured rules; client-submitted totals are ignored. The free threshold uses the pre-discount merchandise subtotal, matching existing cart behavior. Express adds its surcharge even when the standard component is free.

The customer's region choice is not an authenticated address classification. Review the actual address and delivery promise before dispatch. Express availability is not guaranteed by enabling a checkout option; choose a suitable fulfillment service. Store-pickup orders may only be fulfilled manually, not dispatched to a courier by this adapter.

A Pathao API estimate is an **internal courier estimate**, not a replacement for the customer's agreed delivery fee. Final courier/COD/additional charges can differ. COD collection defaults to the immutable order total, including customer shipping; prepaid/partial-payment accounting is not implemented.

## 4. Dispatch safely

1. Open a Pending/Processing order in Delivery Hub or select **Prepare shipment** from order details.
2. Review recipient name, Bangladesh mobile number, complete address, parcel weight/count and collection amount.
3. Select courier/environment and, for Pathao, normal or on-demand service where available.
4. Optional Pathao city/zone/area fields are omitted when blank, not sent as null, matching the current Bangladesh booking documentation. City and zone are required for a price estimate.
5. Check the confirmation checkbox. Simulation is labelled clearly and sends no network request. Live/sandbox jobs enter a persistent queue.
6. The worker claims the job, then creates one consignment. A successful HTTP response alone is insufficient: a consignment ID is required before marking it booked.
7. Track using verified webhooks, background reads or **Sync status**.

Pathao apparel parcels use `item_type=2`, weight 0.5–10 kg and integer COD amount. Its booking adapter validates recipient name 3–100 characters, phone and address 10–220 characters. Steadfast uses a stable unique invoice, validated phone and full recipient address. Input errors do not delete or mutate the original order.

Existing shipment rows or external tracking numbers block another live/sandbox booking. An unattempted queue item, definite rejection or local simulation can be cleared deliberately; the original order remains. An in-flight/uncertain/booked request cannot simply be deleted and resent.

## 5. Handling an uncertain result

A timeout, 5xx or an apparently successful response without a consignment ID becomes **Needs review**. A worker interruption after claiming a booking becomes uncertain after two minutes. **There are no automatic booking POST retries.**

- Look up the saved reference in the courier's merchant portal first.
- If it exists, use **Reconcile** to attach the existing consignment; the app performs a read-only lookup before accepting it.
- Pathao checks that the returned merchant reference matches.
- Steadfast's invoice-status response does not provide sufficient ownership detail to prove an arbitrary entered CID belongs to it, so an additional explicit portal verification is required. This limitation is exposed rather than hidden.
- If nothing is found, contact provider support and investigate before any new booking. This build fails closed rather than offering an unsafe “retry unknown” button.
- Returns/pickups also use one durable request and never blindly retry uncertain responses. Resolve them through the portal/support if no conclusive result is available.

Do not directly edit queue states in production to force a retry. A missing response is not proof that the courier rejected the parcel.

## 6. Webhooks

Use a permanent public HTTPS `APP_URL` before merchant integration. The admin shows:

```
https://your-domain/api?action=courier-webhook&provider=pathao
https://your-domain/api?action=courier-webhook&provider=steadfast
```

Set a random shared secret of at least 24 characters in BONON and the merchant portal, then enable authenticated webhooks.

### Pathao
- Validate incoming `X-PATHAO-Signature` by constant-time comparison to the merchant-configured secret. The reviewed contract does not describe this as a computed HMAC body signature.
- The `webhook_integration` handshake receives HTTP **202**.
- Copy the response-header name and expected value from your Pathao merchant webhook setup into the corresponding admin fields. The UI suggests `X-Pathao-Merchant-Webhook-Integration-Secret`; the current portal determines its required value. Do not invent or reuse another merchant's value.
- Normal authenticated events are queued and acknowledged with 202.

### Steadfast
- Validate `Authorization: Bearer <configured shared secret>` according to the official package's webhook example.
- Accepted events are acknowledged with 200.
- Apache rewrite config preserves the Authorization header. For Nginx/FPM, forward it as `HTTP_AUTHORIZATION`.

Webhook bodies are bounded, normalized to minimum tracking fields, hashed for deduplication and queued for the worker. Recipient addresses/phones in webhook payloads are not retained. Unknown consignments never create shop orders. Stale timestamps and backward lifecycle movement do not regress completed orders. Provider bare timestamps are interpreted in Asia/Dhaka; timestamp offsets are respected.

Webhook queue records are retained for 30 days; sanitized courier operation logs for 90 days. Incoming verification proves possession of the configured shared secret, not independent validation of the provider's infrastructure. Keep it private and rotate carefully.

## 7. Worker and recovery

Run continuously with a supervisor:

```sh
php scripts/courier-worker.php
```

Or use cron once per minute:

```cron
* * * * * cd /home/CPANEL_USERNAME/storefront && /usr/bin/php scripts/courier-worker.php --once >> /home/CPANEL_USERNAME/storefront-worker.log 2>&1
```

Use one approach. A MySQL advisory lock prevents overlapping workers for the same database. Durable rows remain when the worker stops. The hub shows the heartbeat; queued jobs wait without deleting orders.

Back up the database, private encryption key and uploaded media. Both web and worker must access the same key. Do not ship the key, real `.env`, credentials or operational/customer database in public source packages.

Returned/partially delivered orders have explicit statuses. Courier updates do not automatically restock physical goods or perform refunds/COD settlement; verify the returned parcel and reconcile money with the merchant portal. Payment collection, refunds, SMS, fraud scoring, accounts settlement and courier billing reconciliation are outside this integration.

## 8. Sources and acceptance checklist

Primary sources reviewed:
- Pathao official integration help: https://help.pathao.com/integrate-pathao-panel-with-website/
- Pathao official merchant Developer API: https://merchant.pathao.com/ (Developer API section). Its published documentation asset reviewed during development was `https://merchant.pathao.com/assets/developer-api-CSc6g0Xq.js`; build-hashed asset URLs can change.
- Steadfast official IT package/README, including endpoints and webhook example: https://github.com/steadfast-it/SteadFast-Courier-Laravel-Package
- Steadfast documented collection reviewed: https://documenter.getpostman.com/view/26211192/2sAYJ1mhpk

Before launch, with your own authorized account:
- [ ] Verify credentials and the selected production/sandbox origin.
- [ ] Select an active Pathao pickup store; test city/zone and rate lookup.
- [ ] Test approved sandbox consignments and status transitions, including error cases.
- [ ] Verify webhook setup using the merchant panel's handshake/event tests.
- [ ] With separate deliberate approval, make a controlled real-account acceptance booking and inspect actual charges/tracking.
- [ ] Test return/pickup workflow with the account's current documented fields and permissions.
- [ ] Test worker restart, backup restoration, monitoring and uncertain-booking procedures.
- [ ] Review policies, address classification, courier availability and manual fallback with the store operator.

These checks remain necessary: mocked responses cannot prove live account permissions, provider uptime, rates, pickup coverage or webhook delivery.


## Clean deployment package
For Laragon/cPanel paths, private storage, one-shot cron scheduling and fresh owner setup, follow `START-HERE-BN.md`. Historical environment-specific QA scripts are not bundled; no extra public/QA server is needed. Use the host-specific PHP CLI path.
