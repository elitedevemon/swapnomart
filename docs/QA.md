# Latest runtime incident — 15 October 2026

At 14:00–14:03 Asia/Dhaka, the previous external runtime and private backups were absent in a replacement environment, and the old hostname did not resolve. The source survived. A fresh demo runtime was installed; previous live database/account changes were **not recovered**. New native public tunnel, catalog, admin session and returns-policy checks passed; an independent rendered-page fetch also succeeded after one initial502. These are check-time results, not an uptime promise. See the latest working-flow entry and recovery instructions.

# Verification record

## 15 October 2026 — cleaned deployment package

Environment: sandbox PHP 8.4 / MySQL 8.4.6. Windows Laragon, Apache hosting and cPanel were **not** available for actual execution in this pass.

### Executed and passed

- PHP syntax checks across `app/`, `public/` and `scripts/`.
- Fresh demo installation on an isolated empty database: **21 tables, 120 products, 84 fictional orders, 14 fictional users**.
- Fresh production installation on another isolated empty database: **21 tables, no products/orders, one non-demo administrator**.
- Generated production owner password verified against the stored hash; password not published in the audit/output.
- CLI preflight passed for both configurations.
- Both installers refused a second run against a nonempty database, without replacing tables.
- Admin-creation command refused an already-used phone, without promoting/overwriting that account.
- Application catalog/bootstrap executed against each newly installed database; correct catalog counts and three-day return policy.
- The initial CLI catalog fixture omitted `REQUEST_METHOD`; a warning mixed into captured output caused its JSON assertion to fail. The fixture metadata was corrected and both installation suites passed. No product code change was required for this fixture error.
- Disposable QA databases, scoped DB user and private temporary configuration/storage were removed in `finally`; the existing preview database was not used for destructive tests.
- Tailwind 3.4.19 compiled the moved `resources/tailwind.css` using the updated config. Output was temporary/private; the existing storefront CSS/design was not replaced. Browserslist reported an outdated-data warning, not a build failure.
- Public testing URL: homepage **200**, 120-product catalog, session-based demo-admin login, returns-admin policy, logout, homepage linked assets and existing product-media references.
- Over the **PHP development router/public tunnel**, `.env`, app/config, SQL, private storage, installer and upload `.htaccess` requests were blocked; unsafe/nonexistent PHP asset returned404. Anonymous admin API returned401; a write without CSRF returned419.
- Exactly one project MySQL server, one PHP storefront on8081 and one Cloudflare connector remained; no second QA storefront/tunnel was started. Supervisor has three programs: mysql, storefront, public-link. Platform proxies/kernels are not duplicate project servers and were not stopped.
- Preview snapshot helper wrote a private database backup outside the downloadable project. No ZIP archive or real preview credentials are included in the workspace.

### Not verified / required before launch

- Apache `.htaccess` behavior with both project-root and `public/` document roots. PHP-router results **do not certify Apache rules**.
- Actual Windows Laragon configuration, hosting PHP CLI/web differences, cPanel permissions, TLS/cookies, cron and resource limits.
- MariaDB compatibility (portable collation is not a full compatibility test).
- A complete post-cleanup browser regression, including checkout, media writes, private return-evidence upload/access and mobile interactions.
- Real Pathao/Steadfast merchant acceptance, live webhooks/Pusher/browser delivery and actual financial settlement. No merchant credentials or live orders were supplied for these tests.
- Host backup restoration, operational retention, monitoring, load/capacity/security review and licensing of demo imagery.
- Temporary tunnel uptime after the check, provider expiry, full environment replacement or external private-runtime persistence.

Follow the mandatory checklist in `START-HERE-BN.md`. A passing CLI check or a short HTTP smoke test is not a production certification.

## Historical feature testing

Before this deployment reorganization, isolated fixture/browser checks covered Studio, couriers/webhooks, shopping/image processing, admin error logging and returns. Return tests included eligibility boundaries, permissions/CSRF, private evidence, quantity reservations/idempotency, concurrent submissions, manual-review cases, status transitions, settlement safeguards and preservation of original stock/order/shipment data.

Those environment-specific scratch fixtures/screenshots were removed from the downloadable project during cleanup; their older results are historical, not a claim that every workflow was rerun after the storage/config changes. Detailed work and cleanup decisions are recorded in `working-flow.md`.

## Admin notifications / status tabs update — 15 October 2026

- PHP and JavaScript syntax checks passed. No schema/data changes or new servers/tunnels.
- JSDOM + real jQuery and extracted actual application functions: zero/unread/guest badge, gesture-gated chime, duplicate-event suppression, mute persistence/in-memory fallback, initial-backlog silence, old-account and stale-read poll guards, snapshot acknowledgment, status tabs/counts/dropdown/search (English/Bangla/shipping alias)/pagination, newest-first products without catalog mutation. Passed after supplying a missing permissionText stub in the first fixture attempt; that failure was in the isolated fixture, not product code.
- PHP actual helper/API functions with mocked queries: default credential inheritance, saved-admin overrides, encrypted secret handling, public config secrecy, private-channel auth/HMAC/customer rejection/missing-secret rejection, scoped inbox and monotonic snapshot read acknowledgment. Passed. These tests are not a real MySQL query integration run.
- Configured Pusher signed read-only Channels REST request200 and authenticated private-admin WebSocket subscription succeeded. No test order/notification was published. Browser end-to-end order-to-sound on actual Laragon remains to be checked.
- Private Pusher secret was absent from public assets/entrypoints, README and audit ledger; direct private configuration execution returned no content. Defaults are intentionally stored in a protected git-ignored PHP file for the owner, not encrypted at rest in that source file.


## Account / communications update — 15 October 2026 (WF-0016)

### Executed
- All project PHP syntax checks, changed JavaScript syntax checks and preflight passed on PHP/MySQL8.4.6. Preflight sees27tables. Additive migration rerun succeeded without resetting the preview DB; fresh schema tested in a separate disposable database.
- Real headless Chromium141/Playwright1.56.1 browser: fresh phone-based checkout, generated credential form, successful native POST/303 to account, icon-only accessible header, My Returns before Sign out, authorized tracking fetch/modal, optional opted-in email/profile persistence, disabled recovery notice, Admin Communications settings/audience, mobile overflow check, no JS page errors.
- Deliberately failed the catalog refresh after successful checkout (HTTP503): confirmed-order receipt and credential form stayed visible; no pause-page replacement. The original reported error did NOT reproduce before changes, so the exact user-host root cause remains unconfirmed.
- **47 isolated backend/API assertions passed**, using actual fresh MySQL tables and a CLI-only injected transport: default/demo disabled, hashed/nonreturned OTP and encrypted pending payload, payload clearing, resend cooldown, browser/purpose binding, real password-hash update, old-session invalidation, replay/expiry/five-attempt lock, unknown/admin generic requests, separate SMS-consent verification, checkout preserving existing email/consent, explicit withdrawal, accepted/rejected/unknown outcomes and no uncertain retry, stale payload cleanup, enabled channel not blocked by a disabled one, queued-send consent recheck, unique recipient constraint, masked/no-message logs; API auth/role/CSRF/POST gates, explicit newsletter consent, active-only unverified-labeled export, opt-out token behavior, anonymous resubscribe suppression, campaign idempotency/max100/cancel, private-key redaction and key-removal disablement.
- No real gateway request, chargeable SMS, email campaign or Pusher order event was sent. Preview Pusher publishing disabled privately during tests. The SMS fixture does not validate a live provider response envelope or handset delivery.

### Limits still applicable
- Actual Laragon6/Apache/cPanel, HTTPS/cookies, cron scheduling, load/concurrency and backup restoration require acceptance on the user's host. Headless browser form recognition does not prove Google's save UI will appear.
- BulkSMSBD is the provided adapter, not a user-selected/configured merchant account. Its fixed HTTPS POST/TLS path is implemented from provider documentation; actual sender approval, response payload, balance/segment billing, masking/template rules, carrier acceptance and delivery are unverified.
- Email addresses are single-opt-in/unverified; export/consent/unsubscribe are implemented, not an SMTP or email campaign sender. External-platform verification/suppression synchronization remains required.
- Tracking shows the latest authorized stored/synced data; no live courier credentials/parcel delivery were tested in this update. Earlier Studio, returns, couriers, media and notification results above are historical, not a claim of full rerun.
- Current runtime: one local MySQL + one storefront on8081, zero tunnels. Earlier tunnel-related notes in this file are historical only.


## One-time installer / budget update — 15 October 2026 (WF-0017)

- Real Chromium/HTTP on isolated source copies/disposable MySQL DBs: homepage/product/arbitrary/API redirects, private setup key absent from HTML and blocked at its URL, CSRF/key/DB-password/nonempty-database guards, AJAX connection test, SVG rejection, successful demo install/private owner/brand/logo and real password login, GET/POST reinstall403, missing-config/corrupt-marker503. PNG logo re-encoded WebP512×307.
- Browser budget checks: catalog min/max,237 minimum/15001 maximum, inactive exclusion, preserved/clamped custom ceiling after refresh, decimal endpoints, empty/single-price disabled states, full-range reset and mobile width; no JS page errors.
- Isolated clean-production/partial/CLI cases: one hashed-password owner/no products, production HTTPS/demo=false, special-character DB password config round-trip, schema-permission failure locked without config publication, no retry, CLI success/permanent re-run lock.
- Existing supervised PHP child8081 was temporarily pointed at private fixtures then restored in finally. MySQL never restarted; no duplicate PHP/tunnel/user-DB reset. Original preview remains installed and refuses installer access.
-34PHP files and changed JS syntax checked; existing27-table preflight passed. No new application migration. Prior47 communications assertions are historical, not rerun for this installer-only update.
- SMS offers researched only; no provider account/credentials/adapter changed and no SMS/email sent. Advertised credits do not certify Bangladesh handset delivery.
- Actual Windows Laragon6/Apache/cPanel rewrite/private-file/TLS/permissions behavior, host crashes and multi-worker concurrency/load require host-specific acceptance. PHP-router tests do not certify Apache.
