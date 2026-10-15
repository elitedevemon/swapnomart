# Lifestyle Store

Bilingual Bangladesh storefront with the existing cream/forest design, graphical admin, Website Studio, shopping showcases, Delivery Hub and three-day product returns.

**Start here: [বাংলা Laragon → cPanel guide](docs/START-HERE-BN.md).**

## Requirements
- PHP **8.2+**: PDO MySQL, mbstring, cURL, Sodium, GD with JPEG/PNG/WebP, EXIF; sessions enabled.
- **MySQL 8.0+**. SQL now uses `utf8mb4_unicode_ci`; MariaDB compatibility is not certified by the current tests.
- Apache 2.4 with rewrite/headers, `AllowOverride All`, and HTTPS for production. A domain-root virtual host is required; arbitrary subfolder URLs are not supported.
- Writable private storage and `public/assets/uploads/`. Do not use permissions 777.
- Compiled CSS/JS/fonts are included. Node, Composer, a tunnel, Docker and a continuously running courier worker are **not required** on cPanel.

## First-run web installer (recommended)
For a **fresh, uninstalled copy**, do not create `.env` or import SQL first. Create an empty MySQL database/user in Laragon/cPanel, then open the domain. Application routes redirect to `/install.php`. Read the private key from `app/.setup-key.php` using File Manager, then configure database, your private admin, shop name and optional logo. Public setup requires HTTPS; localhost/Laragon testing can use HTTP.

Installation seals `app/.installation-state`; completed or interrupted installation cannot be rerun through the web. Existing configurations are protected, not overwritten. See [one-time installation and budget guide](docs/WEB-INSTALLER.md). **Preserve the marker when updating/moving an installed shop.** For a separate clean production store after demo testing, use an untouched uninstalled source copy plus a new empty database—not the already-sealed demo copy.

## Fresh local demo (CLI alternative)
Create a **new empty** MySQL database `storefront_demo`, copy `.env.example` to `.env`, and set credentials. From the project root:

```sh
php scripts/install.php --demo
php scripts/check.php
php -S 0.0.0.0:8081 -t public public/router.php
```

Open `http://localhost:8081` (set the same `APP_URL` when using this command). Prefer the Laragon Apache virtual host described in the Bengali guide. Demo login is intentionally public: **fictional data only**.

## Fresh production database
Use a **separate empty database** and a database-scoped user. Copy `.env.production.example` to `.env`; fill the real domain, password and paths. Then:

```sh
php scripts/install.php --production --admin-name="Store Owner" --admin-phone=01XXXXXXXXX
php scripts/check.php
```

Replace the phone placeholder with your own valid 11-digit Bangladesh number. The installer prints a randomly generated owner password once; save it privately. It refuses any nonempty database and never deletes existing tables. MySQL DDL is not transactional: investigate a failed import rather than rerunning it against partial data.

If you manually imported **schema.sql only** into a new database, create the owner with:

```sh
php scripts/create-admin.php --admin-name="Store Owner" --admin-phone=01XXXXXXXXX
```

This command creates a new non-demo admin; it cannot overwrite or promote an existing account. No known production password is included. The web installer requires a private setup key and is permanently locked once installation starts; do not combine web and CLI setup on the same copy. For cPanel without Terminal/SSH, initialize the clean production database in Laragon, export it privately and import it with phpMyAdmin; see the guide.

## Features retained
- Bangla/English, light/dark/system themes, local premium fonts, left-opening mobile navigation.
- 120 sample products, slug/SEO pages, galleries/zoom, favorites/cart toasts, category/popular/offer showcases, COD checkout.
- Signup by name/phone/address without OTP; generated-password save form; customer tracking and account pages.
- Configuration-ready SMS OTP recovery, consent-based SMS campaigns, optional email capture/export and unsubscribe. SMS requires a tested merchant gateway and worker; email delivery is handled by your external platform.
- Graphical admin, product image optimization/progress, detailed admin error journal and clear action.
- Studio branding/metadata/favicon/content/styles/fonts, jQuery hero carousel, slides and media library.
- Polling, optional Pusher and opt-in browser notifications.
- Pathao/Steadfast configuration, manual/simulation workflows, persistent queue, reconciliation, webhooks and internal packing slips. **Live merchant acceptance has not been verified.**
- Three-day wrong/damaged/defective/materially-misdescribed return requests, private evidence and admin decisions. Refund/replacement settlement, return pickup and restocking remain **manual**.

## Project layout
`app/` backend · `public/` web entry/assets · `database/` clean install SQL · `scripts/` CLI operations · `resources/` editable Tailwind input · `storage/` private runtime data · `docs/` operations/testing guides.

Prefer setting the web document root to `public/`. Root-level Apache rules also support a cPanel `public_html` containing the full project, but their access protections **must be verified on the actual host before launch**. Keep private storage outside `public_html` using `APP_STORAGE` whenever possible. Never expose `.env`, SQL, keys, error journals or return evidence.

The download contains no preview database, credentials, tunnel runtime, ZIP archive or SQL backup. Existing sandbox data remains separate; it is not silently included in a fresh installation.

## Operations / updates
Back up the database, uploads, private evidence, `.env`, `app/.installation-state` and **storage/.encryption-key together**, outside the public web directory. Do not overwrite `.env` or private storage on updates. For an older schema, back up first and run the applicable migrations:

```sh
php scripts/migrate-studio.php
php scripts/migrate-couriers.php
php scripts/migrate-shopping.php
php scripts/migrate-returns.php
php scripts/migrate-communications.php
```

Fresh installs already include these tables. Never use either installation SQL file as an upgrade. Courier cron, return-evidence cleanup, go-live checks and no-Terminal deployment are documented in the Bengali guide.

Optional CSS rebuild: `npm install`, then `npm run css`. Upload the compiled assets; omit `node_modules`. Runtime does not fetch Tailwind from a CDN.

## Documentation
- [One-time web installer and catalog budget limits](docs/WEB-INSTALLER.md)
- [Free/trial SMS options and limitations](docs/SMS-TESTING-OPTIONS.md) — research only; no new gateway enabled.
- [Account recovery, checkout & communications update](docs/ACCOUNT-COMMUNICATIONS.md) — additive migration, SMS configuration/cron, separate marketing consent and optional email export. Live SMS stays disabled until merchant setup/test; no outbound email engine is included.
- [Admin notifications, sound/mute, Pusher defaults and order status update](docs/ADMIN-NOTIFICATIONS-ORDERS.md) — includes the exact Laragon replacement-file list and private credential handling.
- [Laragon, cPanel and launch checklist](docs/START-HERE-BN.md)
- [Delivery integration](docs/DELIVERY-GUIDE.md)
- [Returns](docs/RETURNS.md) · [Error journal](docs/ERROR-LOG.md)
- [Shopping/image behavior](docs/SHOPPING-UPDATE.md)
- [Verification and limitations](docs/QA.md)
- [Image provenance](docs/ASSET-SOURCES.json): demo photography is **not a commercial-use license**. Replace it with owned/licensed imagery before launch.
- [Working-flow audit trail](working-flow.md)

**Deployment preparation is not a guarantee of host-specific production readiness.** Complete actual Laragon/cPanel, HTTPS, backup/restore, customer-flow and merchant-provider acceptance checks before taking real orders.

## Sandbox preview recovery (not cPanel)
`scripts/preview-recover.py --install` installs a disposable Debian/Ubuntu testing runtime (sudo required); `--run` starts a single MySQL + PHP group. Public tunnel installation/startup is disabled by default. In Arena, expose PHP on `0.0.0.0:8081` as the **Storefront preview** process; do not launch a second PHP server on that port. Runtime/private data stay outside the downloaded source and may be lost when the sandbox is replaced. Recovery can seed a fresh fictional demo, not recover missing accounts/orders. Local HTTP checks do not prove that a user's browser or an external tunnel can reach the site.
