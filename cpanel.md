# cPanel installation — ধাপে ধাপে

> **Public test-key exception:** মালিকের অনুরোধে `app/.setup-key.php`-তে এখন একটি fixed public TEST key রাখা হয়েছে। শুধু controlled testing-এর জন্য। Fresh production-এর আগে file tracking সরিয়ে test file বাদ দিয়ে server-local private key তৈরি করুন। [নির্দেশনা ও ঝুঁকি](docs/PUBLIC-TEST-KEY.md)। নিচের auto-generated-key নির্দেশনা private/fresh production workflow-এর জন্য।

প্রথমে local testing করতে চাইলে [local.md](local.md) অনুসরণ করুন। এই guide-এ **fresh production installation** এবং **আগে install করা shop স্থানান্তর** আলাদাভাবে দেওয়া আছে।

> নতুন web installation-এ আগে `.env` বানাবেন না, SQL import বা CLI installer চালাবেন না। Installer connection/admin/shop setup করবে। কিন্তু cPanel-এ empty database/user আগে তৈরি করতে হবে। Existing shop move/update করতে installer ব্যবহার করবেন না।

## ধাপ ১ — কোন পদ্ধতি ব্যবহার করবেন ঠিক করুন

### A. নতুন production shop

Untouched **uninstalled project copy** + নতুন empty database ব্যবহার করুন। নিচের ধাপ ২–১০ অনুসরণ করুন। Local demo-এর installed files/config/marker এনে fresh setup চালাবেন না।

### B. Laragon-এর একই installed shop স্থানান্তর

এই file-এর **“আগে install করা shop Laragon থেকে move করা”** section অনুসরণ করুন। এতে existing database import ও config update হবে; installer আবার চলবে না।

## ধাপ ২ — Hosting requirements যাচাই করুন

cPanel/hosting provider-এর সাহায্যে নিশ্চিত করুন:

- PHP **8.2 বা নতুন**।
- MySQL **8.0 বা নতুন**। MariaDB compatibility বর্তমান পরীক্ষায় certify করা হয়নি।
- PHP extensions: `pdo_mysql`, `mbstring`, `curl`, `sodium`, `gd`, `exif`। GD-তে JPEG/PNG/WebP support থাকতে হবে।
- Apache-compatible rewrite এবং `.htaccess` support; `AllowOverride All`। অন্য web server হলে provider-কে equivalent routing/access protection দিতে হবে।
- PHP user যেন setup-এর সময় app/config destination এবং runtime storage/uploads লিখতে পারে। Permissions **777 নয়**।
- PHP `upload_max_filesize` অন্তত 2 MB, `post_max_size` অন্তত 8 MB; image processing-এর উপযোগী memory limit।

cPanel-এর **MultiPHP Manager / Select PHP Version** থেকে version/extensions নির্বাচন করুন—host অনুযায়ী menu আলাদা হতে পারে। CLI PHP ও website PHP আলাদা হতে পারে।

## ধাপ ৩ — Domain ও HTTPS প্রস্তুত করুন

1. Domain-এর DNS hosting-এ point করুন।
2. cPanel Domains-এ সঠিক domain/document root নির্বাচন করুন।
3. **SSL/TLS Status / AutoSSL** দিয়ে valid certificate চালু করুন।
4. Browser-এ `https://your-domain.com` certificate warning ছাড়া খোলে কি না দেখুন।
5. প্রয়োজনে cPanel-এর **Force HTTPS Redirect** চালু করুন।

Public installer-এ database/admin password দেওয়ার **আগেই HTTPS** দরকার। HTTPS error এড়াতে application-এর security flag বন্ধ করবেন না। Reverse proxy থাকলে hosting provider-এর verified configuration নিন; arbitrary forwarded headers trust করবেন না।

## ধাপ ৪ — Project files upload করুন

### পছন্দের layout: `public/` document root

Host অনুমতি দিলে full project রাখুন:

```text
/home/CPANEL_USER/storefront/
```

Domain document root করুন:

```text
/home/CPANEL_USER/storefront/public/
```

এতে `app`, database SQL ও config web root-এর বাইরে থাকে। সব host primary domain-এর document root পরিবর্তন করতে দেয় না।

### বিকল্প layout: full project `public_html`-এ

Document root বদলানো না গেলে project-এর contents রাখুন:

```text
/home/CPANEL_USER/public_html/
├── app/
├── database/
├── public/
├── scripts/
├── storage/
├── .htaccess
└── install.php
```

- শুধু `public/` folder upload করবেন না; backend ও schema files-ও দরকার।
- cPanel File Manager → Settings → **Show Hidden Files** চালু করুন। Root, `public/`, storage ও uploads-এর supplied `.htaccess` files বাদ দেবেন না।
- Folder hierarchy ঠিক রাখুন। SFTP/FTP দিয়ে directory structure-সহ upload করা সুবিধাজনক। ZIP তৈরি করা বাধ্যতামূলক নয়।
- অন্য website-এর existing `public_html` files overwrite করবেন না; প্রয়োজনে আলাদা domain/subdomain root নিন।
- `https://your-domain.com/storefront/` ধরনের subfolder installation সমর্থিত নয়।
- Database backups, logs, screenshots, `node_modules` বা testing runtime upload করবেন না।

`CPANEL_USER` placeholder-টি আপনার cPanel username দিয়ে বদলাতে হবে।

## ধাপ ৫ — Empty database ও database user তৈরি করুন

1. cPanel-এর **MySQL Database Wizard / Manage My Databases** খুলুন।
2. নতুন database তৈরি করুন, যেমন:

```text
account_shop
```

3. আলাদা database user তৈরি করুন, যেমন:

```text
account_shopuser
```

4. Strong private password তৈরি ও password manager-এ সংরক্ষণ করুন।
5. User-কে **শুধু ওই database-এ** add করে installation-এর জন্য প্রয়োজনীয় privileges দিন; সাধারণ cPanel Wizard-এ ওই database-এর জন্য **ALL PRIVILEGES** নির্বাচন করা যায়। Global/root privileges নয়।
6. Hosting-এর database host জেনে নিন। অনেক host-এ `localhost`, কিন্তু সব ক্ষেত্রে নয়। সাধারণ port `3306`।
7. Database খালি রাখুন; `schema.sql` বা demo SQL import করবেন না।

cPanel prefix-সহ সম্পূর্ণ database/user name ব্যবহার করতে হবে।

## ধাপ ৬ — Installer খুলুন ও ownership key নিন

1. Browser-এ খুলুন:

```text
https://your-domain.com
```

2. Fresh project `/install.php`-তে redirect হবে।
3. File Manager দিয়ে **project root-এর** `app/.setup-key.php` খুলুন। উদাহরণ:

```text
/home/CPANEL_USER/storefront/app/.setup-key.php
```

অথবা full-project layout হলে:

```text
/home/CPANEL_USER/public_html/app/.setup-key.php
```

4. `PRIVATE SETUP KEY:`-এর পরে থাকা **64-character key-টুকু** copy করুন।
5. Installer-এর Setup key field-এ paste করুন। পুরো PHP file নয়; key share করবেন না।

Key প্রথমবার installer খুললে তৈরি হয়। এটি public browser URL দিয়ে পড়া যাবে না। File না পেলে hidden files ও `app/` write permission দেখুন।

## ধাপ ৭ — Database ও shop information দিন

### Database fields

| Field | কী দেবেন |
|---|---|
| Database host | Hosting-এর দেওয়া host |
| Port | Hosting-এর port, সাধারণত `3306` |
| Database name | Prefix-সহ সম্পূর্ণ database name |
| Database username | Prefix-সহ সম্পূর্ণ username |
| Database password | ধাপ ৫-এ তৈরি private password |
| Store URL | আপনার আসল `https://your-domain.com` — subfolder নয় |

**Test database connection** চাপুন। Connected এবং empty দেখালে এগিয়ে যান। Schema create করার permission-ও প্রয়োজন।

### Shop/admin fields

1. Shop name লিখুন।
2. Optional logo upload করুন: JPEG/PNG/WebP, max 2 MB, max 4000 px/side ও মোট 8 megapixels। Installer optimized WebP করে save করবে।
3. Admin name ও নিজের valid Bangladesh mobile number দিন।
4. Unique admin passphrase দিন এবং confirm করুন—12–72 bytes।
5. Starting catalog-এ **Clean shop** নিন। Public production domain-এ demo installation অনুমোদিত নয়।
6. **Install my shop** চাপুন। কাজ শেষ হওয়া পর্যন্ত refresh/close/repeated submit করবেন না।

## ধাপ ৮ — প্রথম login ও lock পরীক্ষা করুন

1. Success-এর পরে storefront খুলবে।
2. Account icon দিয়ে নিজের admin phone/password-এ login করুন।
3. Admin-এ shop name/logo দেখুন। Website Studio থেকে বাকি branding/content দিন।
4. `/install.php` আবার খুললে **403 / Installation is locked** আসবে—এটাই প্রত্যাশিত।
5. Generated `.env`-এ production configuration যাচাই করুন:

```ini
APP_ENV="production"
APP_URL="https://your-domain.com"
APP_REQUIRE_HTTPS="true"
TRUST_PROXY_HEADERS="false"
demo="false"
```

Generated config-এ database password `DB_PASSWORD_B64` হিসেবে থাকতে পারে। এটি special characters ঠিক রাখার encoding, **encryption নয়**। `.env` private রাখুন; screenshot/chat/public file-এ দেবেন না।

**Lock files মুছবেন না:** `app/.installation-state` installation শুরু হওয়ার আগেই seal করা হয়। Interrupted setup-ও automatic reset/retry করে না। `.env` মুছে নতুন installation খুলবে না; config/backup restore বা owner inspection লাগবে।

## ধাপ ৯ — Private storage ও access protection যাচাই করুন

Prefer private runtime storage web root-এর বাইরে রাখুন। Fresh setup-এর পর **real orders নেওয়ার আগে** চাইলে:

1. যেমন `/home/CPANEL_USER/storefront-private` directory তৈরি করুন।
2. বর্তমান storage-এর **সমস্ত contents**, hidden files ও `.encryption-key` থাকলে সেটিসহ, নতুন directory-তে copy করুন। আগে private backup নিন; চলমান store হলে maintenance window-তে writes বন্ধ রাখুন।
3. Generated `.env`-এ দিন:

```ini
APP_STORAGE="/home/CPANEL_USER/storefront-private"
```

4. Website এবং CLI/cron একই path ও encryption key ব্যবহার করছে নিশ্চিত করুন।
5. PHP account-এর ownership/read/write access ঠিক করুন। Private directory সাধারণত 700/750, private config 600/640, public files 644 ও public directories 755—actual host user/group অনুযায়ী provider-এর সাহায্য নিন। **777 নয়**।
6. যাচাই/backup ছাড়া পুরোনো storage contents delete করবেন না।

Browser বা HTTP client দিয়ে পরীক্ষা করুন:

```text
/.env
/app/.setup-key.php
/app/.installation-state
/database/schema.sql
/storage/
/scripts/install.php
/assets/uploads/.htaccess
```

এগুলো থেকে secret/file contents পাওয়া যাবে না; protected URLs সাধারণত 403/404 দেয়। শুধু কোনো blank/storefront page দেখা protection-এর পূর্ণ প্রমাণ নয়—বিশেষ করে raw config/SQL download হচ্ছে কি না পরীক্ষা করুন। Directory listing থাকলে বা private content দেখা গেলে launch বন্ধ রেখে hosting configuration ঠিক করুন।

## ধাপ ১০ — Preflight ও দোকানের পরীক্ষা

Terminal থাকলে, সঠিক project path থেকে:

```sh
cd /home/CPANEL_USER/storefront
php scripts/check.php
```

Full project `public_html`-এ হলে সেই path ব্যবহার করুন। Host-এর CLI PHP আলাদা হলে `php`-এর বদলে provider-এর দেওয়া absolute PHP binary path দিন। বর্তমান schema-তে 27টি table। Terminal না থাকলে provider-এর সহায়তায় requirements/schema/permissions যাচাই করুন; browser test বাদ দেবেন না।

- [ ] নিজের non-demo admin login; public demo login নেই।
- [ ] Shop name/logo, real contacts, delivery charges ও return policy ঠিক।
- [ ] Owned/licensed product photos ও আসল products যোগ করা হয়েছে।
- [ ] Bangla/English, mobile layout, themes, cart ও budget filter ঠিক।
- [ ] Controlled signup/login/checkout/order tracking পরীক্ষা হয়েছে।
- [ ] Stock/total, returns, image upload, admin status changes ও error logs পরীক্ষা হয়েছে।
- [ ] HTTPS/cookies, private-file protection ও backup/restore পরীক্ষা হয়েছে।

## প্রয়োজন হলে SMS ও courier cron

Fresh installer SMS/Pusher sending চালু করে না। Gateway/merchant credentials configure ও controlled test ছাড়া real delivery চালু করবেন না। Email collection/export আছে; built-in outbound email campaign sender নেই।

SMS OTP/queued messaging enable করলে cPanel **Cron Jobs**-এ প্রতি মিনিটে চালানোর command:

```sh
/usr/local/bin/php /home/CPANEL_USER/storefront/scripts/communications-worker.php --once >> /home/CPANEL_USER/private-logs/sms-worker.log 2>&1
```

Courier queue ব্যবহার করলে:

```sh
/usr/local/bin/php /home/CPANEL_USER/storefront/scripts/courier-worker.php --once >> /home/CPANEL_USER/private-logs/courier-worker.log 2>&1
```

- আগে `private-logs` directory তৈরি ও permissions ঠিক করুন; logs web root-এর বাইরে রাখুন।
- PHP binary/project/user paths placeholders—hosting-এর আসল paths দিন।
- cPanel form-এ minute/hour/day/month/weekday প্রতিটি `*` দিন, অথবা “Once Per Minute” নির্বাচন করুন। Host-এর cron limits মেনে চলুন।
- Cron-এ PHP development server বা continuous worker চালাবেন না। একই job duplicate schedule করবেন না।
- `.env` বাইরে রাখলে web এবং cron-এ একই `APP_ENV_FILE`/`APP_STORAGE` paths দিতে হবে।
- বিস্তারিত: [Account/SMS guide](docs/ACCOUNT-COMMUNICATIONS.md), [Delivery guide](docs/DELIVERY-GUIDE.md)।

## আগে install করা shop Laragon থেকে move করা

এটি fresh installation নয়; **installer rerun করবেন না**।

1. Local shop-এর writes বন্ধ করে consistent private backup নিন:
   - Database SQL export
   - Project files ও uploaded images
   - `.env`
   - `app/.installation-state`
   - Private storage, `.encryption-key` ও return evidence
2. cPanel-এ নতুন empty database/user তৈরি করুন।
3. **নিজের installed shop-এর export** সেই empty target database-এ import করুন। Live/existing target DB overwrite নয়। Backup SQL public directory-তে রেখে দেবেন না।
4. Project files, uploads, installation marker ও private storage সঠিক layout-এ transfer করুন। Hidden files বাদ দেবেন না।
5. Hosting `.env`-এ নতুন database host/port/name/user/password, HTTPS `APP_URL`, `APP_ENV=production`, `APP_REQUIRE_HTTPS=true`, `demo=false` এবং hosting storage path দিন। Source template-এ secrets লিখবেন না।
6. Database password বদলানোর সময় পুরোনো `DB_PASSWORD_B64` line অন্ধভাবে রেখে দেবেন না। Correct encoded value update করুন, অথবা explicit `DB_PASSWORD` ব্যবহার করে পুরোনো encoded line সরান। Explicit plain key precedence পায়; quotes/backslashes-সহ complex values হলে private/offline উপায়ে সঠিক encoding করুন—online encoder-এ secret দেবেন না।
7. CLI preflight ও ধাপ ১০-এর সব browser/security checks করুন। Cron configure করুন।

**Local demo হলে:** শুধু `demo=false` করলেই sample records মুছে যায় না। Demo পরীক্ষা শেষে নতুন production-এর জন্য untouched uninstalled source copy ও আলাদা empty DB দিয়ে fresh clean setup করাই ভালো। Installed demo-এর lock মুছে reset করবেন না।

## Troubleshooting

| সমস্যা | করণীয় |
|---|---|
| Installer আসে না, locked দেখায় | `.env`/existing configuration বা install state আছে; fresh এবং existing workflow গুলিয়ে গেছে কি না দেখুন। Lock delete নয়। |
| HTTPS required | আগে SSL ঠিক করুন; reverse proxy থাকলে provider-এর সাহায্যে trusted configuration নিন। |
| Connection failed | Prefix-সহ DB/user name, password, host/port, user-to-database privileges যাচাই করুন। |
| Database not empty | নতুন empty DB ব্যবহার করুন; existing data drop নয়। |
| 500 / missing assets / route404 | PHP version/extensions, document root, hidden `.htaccess`, rewrite/access rules ও private error logs দেখুন। |
| Permission error | PHP user ownership ও writable paths ঠিক করুন; 777 দেবেন না। |
| Interrupted installation /503 | Partial schema/config/marker এবং private logs inspect করুন; automatic retry/reset নয়। |
| OTP আসছে না | Gateway disabled/config/test/credit/cron দেখুন। Signup-এ OTP লাগে না; recovery-তে verified SMS দরকার। |
| CLI check ও web result আলাদা | CLI/web PHP versions, extensions এবং environment/storage paths মিলিয়ে নিন। |

## Update ও backup-এর নিয়ম

Code update-এর আগে database, uploads, config, install marker এবং private storage/key-এর backup নিন। Existing `.env`, marker, private Pusher settings বা storage overwrite করবেন না। Update-এর জন্য সংশ্লিষ্ট guide-এর additive migrations চালান—fresh installer/full schema SQL নয়।

**সীমা:** installer-এর isolated browser/MySQL tests হয়েছে; আপনার actual cPanel/Apache/TLS/permissions, cron এবং live SMS/courier delivery আলাদাভাবে যাচাই করতে হবে। এই guide production certification নয়।
