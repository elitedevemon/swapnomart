# One-time web installation · Laragon 6 → cPanel

**WF-0017 · 15 October 2026**

## নতুন project-এর সহজ setup

1. **Fresh/uninstalled project copy** নিন। Web installer ব্যবহার করলে আগে `.env` বানাবেন না, full SQL import বা CLI installer চালাবেন না।
2. PHP 8.2+, MySQL 8+, `pdo_mysql`, `mbstring`, `curl`, `sodium`, `gd`, `exif` এবং GD JPEG/PNG/WebP support চালু রাখুন।
3. Laragon/HeidiSQL অথবা cPanel Database Wizard-এ **নতুন খালি database**, database user ও ওই database-এর privileges তৈরি করুন। Installer server-level database/user তৈরি করবে না এবং কোনো পুরোনো table মুছবে না।
4. Project domain খুলুন। Unconfigured project-এর homepage, product URLs, arbitrary application routes এবং API `/install.php`-তে redirect হবে। Assets/certificate verification/private-path protection আলাদা থাকে; private files কখনো installer দিয়ে প্রকাশিত হয় না।
5. File Manager-এ hidden files দেখিয়ে **`app/.setup-key.php`** খুলুন। `PRIVATE SETUP KEY:`-এর পরে থাকা **64-character key-টুকু** কপি করে form-এ দিন—পুরো PHP file নয়। এটি browser URL দিয়ে পড়ার জন্য নয়। Key পেজে প্রকাশ করা হয় না; ফলে random visitor আপনার first admin তৈরি করতে পারে না।
6. Database host/port/name/user/password দিন; **Test database connection** চাপুন। এটি connection এবং empty database check করে—সকল DDL permission-এর নিশ্চয়তা নয়।
7. Store URL, shop name, optional logo, admin name/phone/password ও confirmation দিন। নিজের unique passphrase (12–72 bytes) রাখুন।
8. **Clean shop** অথবা **Local demo** বেছে Install করুন। Demo শুধু localhost/`.test` domain-এর জন্য। দুটো mode-এই নিজের non-demo admin তৈরি হয়। Local demo-তে আগের sample catalog ও public demo login-ও থাকে—শুধু fictional data ব্যবহার করুন।
9. Success-এর পরে storefront খুলবে। Account icon দিয়ে নিজের admin phone/password-এ login করুন। চাইলে CLI থেকে `php scripts/check.php` চালান।

### Laragon 6

Project root যেমন `C:\laragon\www\storefront`। Apache/MySQL চালিয়ে `http://storefront.test` খুলুন। Domain-root virtual host ব্যবহার করুন; subfolder installation সমর্থিত নয়। Prefer `public/` document root; supplied root `.htaccess`-ও project-root document root সমর্থন করে। `mod_rewrite`, `AllowOverride All` দরকার।

শুধু PHP development server ব্যবহার করলে:

```bat
cd C:\laragon\www\storefront
php -S 0.0.0.0:8081 -t public public/router.php
```

Browser-এ `http://localhost:8081` খুলুন; একই port-এ দ্বিতীয় server চালাবেন না।

### cPanel

- Fresh source upload, empty database/user এবং **HTTPS আগে** চালু করুন। তারপর domain খুলুন। Remote/plain HTTP-তে setup credentials গ্রহণ করা হয় না; local machine থেকে HTTP Laragon testing করা যায়।
- `app/`, config destination, private storage ও uploads setup-এর সময় PHP user-এর writable হতে হবে। **777 নয়**। Setup-এর পরে app/source directory-কে write-protect করা ভালো; runtime storage/uploads writable রাখুন।
- Root `install.php` compatibility entry ও `public/install.php` একই guarded controller ব্যবহার করে। Shared hosting Apache access protection, TLS এবং actual PHP CLI/web configuration পরীক্ষা করুন।
- Reverse proxy থাকলে শুধু verified/trusted server configuration-এ `TRUST_PROXY_HEADERS=true` environment ব্যবহার করুন। Client-supplied forwarded headers অন্ধভাবে বিশ্বাস করবেন না।
- SMS/Pusher transmission installer থেকে শুরু হয় না; নতুন generated config-এ disabled। Merchant settings আলাদাভাবে configure/test করুন।

## একবার install হলে কেন আর খোলা যাবে না

- Stable private **`app/.installation-state`** file schema mutation-এর **আগেই** তৈরি হয়। সফল হলে state `installed` হয়।
- Installed project-এর `/install.php` GET ও POST দুইটাই **403**। কোনো web reset/reinstall switch নেই।
- `.env` মুছলে installer আবার খোলে না: application **503** দেয় এবং configuration restore করতে বলে। DB outage-ও reinstall permission নয়।
- Interrupted/corrupt install state **fail closed** থাকে। MySQL DDL rollback করা যায় না; installer পুরোনো/partial tables মুছে পুনরায় চেষ্টা করে না। Hosting owner-কে private error log, DB permissions, partial schema ও configuration পরীক্ষা করতে হবে।
- Filesystem mutex ও database advisory lock concurrent installation প্রতিরোধ করে। CLI installer-ও permanent marker এবং lock মেনে চলে।
- Existing `.env` বা database environment configuration থাকা পুরোনো installation **already configured** হিসেবে ধরা হয়; installer সেটি overwrite করে না। Existing store update করতে installer নয়, প্রযোজ্য migrations ব্যবহার করুন।

**সীমা:** filesystem/database-এর পূর্ণ ক্ষমতাসম্পন্ন server owner নিজে files/code বদলাতে পারেন; কোনো PHP app তা অসম্ভব করতে পারে না। এই guarantee application/web installer-এর জন্য। Normal update/redeployment-এ marker/config মুছবেন না।

## Logo ও credentials

- Logo: JPEG/PNG/WebP, max2MB, max4000px/side ও8MP। Image decode/re-encode করে random-named WebP (max512px) save হয়; SVG/executable payload/original filename রাখা হয় না। Default round logo optional fallback।
- Admin password hash হিসেবে DB-তে থাকে; raw password file/log-এ রাখা হয় না। Database password private configuration-এ থাকা লাগে।
- Generated `.env`-এ `DB_PASSWORD_B64` ব্যবহার করা হয়েছে যাতে quotes/backslashes-সহ database password সঠিক থাকে। **Base64 encryption নয়**—`.env` private রাখুন। Explicit `DB_PASSWORD` দিলে সেটি precedence পায়। Existing normal `.env` files আগের মতোই চলে।
- Setup key/CSRF/password URL/query string-এ পাঠানো হয় না; secrets rejected form/JSON response-এ echo হয় না।
- `.env`, `app/.installation-state`, private storage/keys, database ও uploads একসঙ্গে privately backup করুন। Setup key PHP file direct execution-ও content না দেখিয়ে404 দেয়; Apache/private-route protections তবু অবশ্যই verify করতে হবে।

## Laragon থেকে cPanel-এ নেওয়া

**একই installed shop move করলে:** DB export/import, uploads/private storage, installation marker ও config একসঙ্গে স্থানান্তর করুন। Hosting DB credentials/domain/HTTPS/private paths update করুন; installer পুনরায় চালাবেন না।

**Demo পরীক্ষা শেষে নতুন clean production চাইলে:** untouched **uninstalled source copy** + আলাদা empty production DB ব্যবহার করুন। Demo installation-এর marker delete করে live store reset করার চেষ্টা নয়। Original downloadable source-এ কোনো runtime install marker/setup key/real `.env` নেই।

CLI alternative (`scripts/install.php`) আগের মতো আছে। Web setup ও CLI setup একই fresh copy-তে একসঙ্গে ব্যবহার করবেন না।

## Existing project-এর এই update-এর files

```text
install.php                         NEW
public/install.php                  NEW
app/installation.php                NEW
app/installer.php                   NEW
app/config.php
public/index.php
public/api.php
public/router.php
.htaccess
public/.htaccess
.gitignore
scripts/install.php
public/assets/app.js
```

README, Bengali start guide, QA ও working-flow-ও updated। এই installer/budget update-এর জন্য **নতুন SQL migration নেই**; আগের communications migration প্রয়োজন হলে সেটি আলাদা। Existing `.env`, private Pusher config, installation marker বা storage overwrite করবেন না।

## Budget filter

- Storefront-এ দৃশ্যমান/active catalog-এর actual minimum ও maximum **selling price** থেকে slider bounds আসে; fixed ৳500/৳10,000 নেই।
- Minimum category/search/stock checkbox অনুযায়ী বদলে যায় না: পুরো visible store catalog-এর minimum। Inactive/hidden products এতে যোগ হয় না। Out-of-stock কিন্তু visible products অন্তর্ভুক্ত।
- Cheapest product ৳237 হলে lower label ও input minimum দুটোই ৳237। উচ্চমূল্যের products default upper limit-এর কারণে বাদ যায় না।
- Reset পুরো range ফিরিয়ে দেয়। Catalog refresh-এ bounds আপডেট হয়; custom chosen ceiling যতটা সম্ভব রাখা হয়, প্রয়োজনে clamp হয়। Full-range selection নতুন maximum অনুসরণ করে।
- Empty catalog ও all-same-price catalog-এ slider safely disabled হয়।

## যাচাই

Real Chromium/HTTP-তে first-run redirects, ownership-key secrecy, CSRF, wrong credentials, nonempty DB refusal, AJAX connection test, SVG rejection, successful demo installation/upload/private admin login, permanent GET/POST lock, missing config/corrupt marker fail-closed, budget/reset/refresh/mobile cases pass করেছে। Isolated clean production, interrupted schema permissions এবং CLI permanent-lock cases-ও pass। Actual Windows Laragon 6/Apache/cPanel acceptance এখনো আপনার host-এ করতে হবে; local PHP router Apache rules certify করে না।
