# শুরু করুন: Laragon → cPanel

প্রথমে Laragon-এ পরীক্ষা, তারপর নিজের cPanel hosting-এ আপলোড করুন। Workspace-এ ZIP বানানো হয়নি; সরাসরি project files ডাউনলোড করুন। বর্তমান testing server-এর private database/credentials/customer data এই download-এ নেই।

## ১. প্রয়োজনীয় সফটওয়্যার

- PHP 8.2 বা নতুন: `pdo_mysql`, `mbstring`, `curl`, `sodium`, `gd`, `exif`; GD-তে JPEG/PNG/WebP support।
- MySQL 8.0+। SQL-এর collation সাধারণ `utf8mb4_unicode_ci` করা হয়েছে, তবে এই release-এ MariaDB চালিয়ে যাচাই করা হয়নি।
- Apache 2.4, `mod_rewrite`, `.htaccess`/`AllowOverride All`; production-এ HTTPS।
- PHP upload limit অন্তত 2MB, `post_max_size` অন্তত 8MB, বাস্তব image processing-এর জন্য পর্যাপ্ত memory (যেমন 256MB)।
- Node/Composer runtime-এ লাগবে না। Compiled CSS, JS, fonts সঙ্গে আছে।

## ২. নতুন web installer (সহজ পদ্ধতি)

Fresh/uninstalled source copy-তে `.env` তৈরি বা SQL import না করে আগে Laragon/cPanel-এ empty database/user তৈরি করুন। Domain খুললে `/install.php` আসবে। File Manager-এ `app/.setup-key.php` খুলে `PRIVATE SETUP KEY:`-এর 64-character key কপি করুন। Form-এ database connection, admin name/phone/password, shop name/logo দিন। Local demo অথবা clean shop বেছে install করুন।

Public hosting-এ HTTPS দরকার। সফল বা interrupted install-এর পরে permanent marker setup বন্ধ রাখে; `.env` delete করলেও নতুন web install খুলবে না। Existing configured store-এ wizard disabled। বিস্তারিত: [One-time web installer guide](WEB-INSTALLER.md)। Web এবং CLI method একসঙ্গে ব্যবহার করবেন না।

### বিকল্প: Laragon-এ CLI demo installation

1. Project files রাখুন `C:\laragon\www\storefront`-এ। ওই directory-তেই `app`, `public`, `database`, `scripts`, `.env.example` থাকবে; পুরোনো `bonon` wrapper প্রয়োজন নেই।
2. Laragon-এ Apache ও MySQL চালু করুন। PHP extensions চালু করে Apache restart করুন।
3. phpMyAdmin/HeidiSQL দিয়ে **নতুন খালি** `storefront_demo` database তৈরি করুন, charset `utf8mb4`, collation `utf8mb4_unicode_ci`। বিদ্যমান দোকানের database ব্যবহার করবেন না।
4. `.env.example` কপি করে `.env` করুন (Windows-এ যেন `.env.txt` না হয়)। DB username/password/port ঠিক করুন।
5. Laragon Terminal-এ project directory থেকে চালান:

```bat
cd C:\laragon\www\storefront
php scripts/install.php --demo
php scripts/check.php
```

6. Laragon Auto Virtual Hosts ব্যবহার করে `http://storefront.test` খুলুন। `.env`-এ একই `APP_URL` রাখুন। Root `.htaccess` public assets/API-তে routing করে। আরও নিরাপদ পদ্ধতি: virtual host DocumentRoot সরাসরি `C:/laragon/www/storefront/public` করুন; `<Directory>`-তে `AllowOverride All` ও `Require all granted` দিন।
7. Login → Demo admin / Demo user দিয়ে পরীক্ষা করুন। Demo admin সবার জন্য উন্মুক্ত; এখানে বাস্তব গ্রাহকের ব্যক্তিগত তথ্য দেবেন না।

Apache না ব্যবহার করে অস্থায়ী PHP preview চাইলে:

```bat
php -S 0.0.0.0:8081 -t public public/router.php
```

তখন `APP_URL="http://localhost:8081"` হবে। এটি development server, production server নয়। একই storefront-এর একাধিক PHP server চালাবেন না।

## ৩. Laragon-এ যা পরীক্ষা করবেন

- Desktop ও mobile, Bangla/English, light/dark/system, বাম drawer, carousel ও gallery zoom।
- Favorite/cart চাপলে toast আসে; cart নিজে খুলে যায় না। Checkout ও নিজস্ব order tracking।
- নতুন demo customer signup-এ generated password দেখায়; তা দিয়ে পুনরায় login।
- Admin product add/edit, images resize/progress, Studio text/style/logo/slides এবং Error log/clear।
- Manual/simulation delivery; delivered order থেকে তিন দিনের return flow, customer/admin permissions ও evidence। প্রকৃত refund/return shipment স্বয়ংক্রিয় নয়।
- ব্রাউজারের Console/Network-এ unexpected error আছে কি না।

## ৪. Launch-এর জন্য আলাদা পরিষ্কার database

নতুন production-এর জন্য untouched **uninstalled source copy** নিন; আগে install-করা demo copy permanent lock করা থাকে। একই installed shop move করলে database/config/uploads/private keys/installation marker একসঙ্গে move করে config বদলান, installer rerun নয়।

Demo database মুছে বা তার orders delete করে production বানাবেন না। **আলাদা নতুন খালি database** ব্যবহার করুন। প্রকৃত product/catalog চাইলে admin দিয়ে যোগ করুন; demo image ব্যবহারের বাণিজ্যিক অনুমতি ধরে নেবেন না।

`.env.production.example` থেকে `.env` বানিয়ে সব placeholder বদলান:

```ini
APP_NAME="Lifestyle Store"
APP_ENV=production
APP_URL="https://your-real-domain.com"
APP_REQUIRE_HTTPS=true
TRUST_PROXY_HEADERS=false
demo=false
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE="your_database"
DB_USERNAME="your_database_user"
DB_PASSWORD="your-private-strong-password"
APP_STORAGE="/home/CPANEL_USERNAME/storefront-storage"
```

- cPanel database/user name সাধারণত account prefix-সহ হয়। ওই DB-তেই user privileges দিন, global/root user নয়।
- `TRUST_PROXY_HEADERS=false` রাখুন; trusted proxy-এর verified configuration ছাড়া client-supplied HTTPS header বিশ্বাস করবেন না।
- `APP_STORAGE`-র উদাহরণটি নিজের account path অনুযায়ী বদলাতে হবে। Laragon-এ production DB প্রস্তুত করলে স্থানীয় private path দিন; cPanel path সেখানে কাজ করবে না।

Terminal/SSH থাকলে:

```sh
php scripts/install.php --production --admin-name="Your Name" --admin-phone=01XXXXXXXXX
php scripts/check.php
```

নিজের বৈধ ১১ অঙ্কের নম্বর দিন। Owner-এর random password terminal-এ একবার দেখাবে—নিরাপদে সংরক্ষণ করুন। Password source code বা public file-এ রাখবেন না। Installer nonempty database পেলে থামে; কখনো স্বয়ংক্রিয় DROP/reset করে না। মাঝপথে DDL import ব্যর্থ হলে নতুন database-এর আংশিক অবস্থা তদন্ত করুন—MySQL DDL rollback হয় না।

**Manual schema import বিকল্প:** নতুন খালি DB-তে শুধু `database/schema.sql` import করে নিচের command চালান। Demo SQL import করবেন না।

```sh
php scripts/create-admin.php --admin-name="Your Name" --admin-phone=01XXXXXXXXX
```

এই command নতুন admin তৈরি করে; পুরোনো customer-কে promote বা password overwrite করে না।

## ৫. cPanel-এ upload ও routing

### পছন্দের পদ্ধতি: public/ document root

Full project রাখুন `/home/CPANEL_USERNAME/storefront`-এ; domain document root করুন `/home/CPANEL_USERNAME/storefront/public`। Private app/config/data web root-এর বাইরে থাকবে। সব host main-domain document root বদলাতে দেয় না।

### বিকল্প: full project public_html-এ

Full project-এর contents `public_html`-এ রাখুন—`app`, `public`, root `.htaccess` ইত্যাদি একই স্তরে। Root `.htaccess` private paths block এবং public routing করে। File Manager-এ **Show Hidden Files** চালু করে root/public/uploads-এর `.htaccess` এবং `.env` যথাস্থানে আছে নিশ্চিত করুন। `APP_STORAGE` অবশ্যই `public_html`-এর বাইরে রাখার চেষ্টা করুন।

এটি domain-root deployment: `https://domain.com/storefront/`-এর মতো arbitrary subfolder supported নয়। Addon/subdomain-এর নিজস্ব root ব্যবহার করুন।

- cPanel PHP selector/MultiPHP দিয়ে সঠিক version/extensions দিন। CLI PHP ও web PHP আলাদা হতে পারে—দুটিই পরীক্ষা করুন।
- Private directory account-owned 700 বা প্রয়োজন অনুযায়ী 750; public files সাধারণত 644, public directories 755। `.env` 600/640। Web PHP user যেন storage ও uploads লিখতে পারে; 777 দেবেন না।
- AutoSSL/HTTPS চালু করুন। প্রয়োজন হলে cPanel Force HTTPS Redirect দিন। Application plain HTTP-তে production request প্রত্যাখ্যান করে।
- শুধু চলার জন্য `node_modules`, build tools, screenshots, ZIP, backup SQL বা testing tunnel লাগে না।

### cPanel-এ Terminal/SSH না থাকলে

1. Laragon-এ **আলাদা clean production database** তৈরি করুন। স্থানীয় DB credentials এবং স্থানীয় writable storage path দিয়ে `demo=false` রেখে production installer চালিয়ে owner তৈরি করুন। CLI installation-এর জন্য web HTTPS connection লাগে না।
2. phpMyAdmin দিয়ে শুধু সেই পরিষ্কার database-এর SQL export করুন; export web directory/workspace download package-এর বাইরে ব্যক্তিগতভাবে রাখুন। Demo DB বা testing server-এর customer DB export করবেন না।
3. cPanel-এ নতুন empty database/user তৈরি করে phpMyAdmin দিয়ে এই export import করুন। DB import-এর size/time limit খেয়াল রাখুন।
4. Hosting-এর `.env`-এ hosting DB credentials, HTTPS domain এবং hosting storage path দিন। Source-এর `.env.production.example`-এ secret লিখবেন না।
5. Owner login এবং নিচের সব checklist চালান। এই পদ্ধতিতে host-side CLI preflight চালানো না গেলে PHP/extensions/permissions provider-এর সাহায্যে নিশ্চিত করুন।

## ৬. Delivery queue: cPanel Cron, আলাদা server নয়

Host-এর সঠিক PHP CLI path ও project path বসিয়ে প্রতি মিনিটে:

```cron
* * * * * cd /home/CPANEL_USERNAME/storefront && /usr/local/bin/php scripts/courier-worker.php --once >> /home/CPANEL_USERNAME/storefront-worker.log 2>&1
```

Full project `public_html`-এ হলে `cd` path বদলান। Log public directory-র বাইরে রাখুন ও rotate করুন। DB lock concurrent workers ঠেকায়। cPanel যদি প্রতি মিনিটের cron অনুমতি না দেয়, host-এর সীমা অনুযায়ী schedule করুন। প্রয়োজন ছাড়া continuous worker/server চালু রাখবেন না।

প্রথমে simulator/manual delivery ব্যবহার করুন। Pathao/Steadfast merchant credentials দিয়ে connection checks, webhook authentication এবং একটি নিয়ন্ত্রিত real order গ্রহণ পরীক্ষা না করে live dispatch enable করবেন না। Uncertain booking অন্ধভাবে retry করবেন না—merchant portal reconcile করুন।

## ৭. Backup, update, private evidence

একসঙ্গে private backup নিন: database, uploaded photos, `.env`, private storage-এর `.encryption-key` ও return evidence। Encryption key হারালে পুরোনো encrypted provider credentials decrypt হবে না। Backup public directory-তে নয়। Backup restore পরীক্ষা করুন।

পুরোনো install update করার আগে backup নিয়ে README-এর migrations চালান; install SQL দিয়ে existing database replace করবেন না।

Unsubmitted পুরোনো evidence পরিষ্কার:

```sh
php scripts/cleanup-return-evidence.php
php scripts/cleanup-return-evidence.php --apply
```

প্রথমটি dry run। দ্বিতীয়টি কেবল eligible unattached পুরোনো files সরায়; attached return evidence-এর retention আলাদাভাবে নির্ধারণ করতে হবে।

## ৮. Launch-এর আগে বাধ্যতামূলক পরীক্ষা

- [ ] HTTPS/cookie/session ও real owner login কাজ করে; demo buttons নেই; `.env`-এ `APP_ENV=production`, `demo=false`।
- [ ] `php scripts/check.php` pass (Terminal থাকলে)। এটি browser/security audit-এর বিকল্প নয়।
- [ ] `/.env`, `/database/schema.sql`, `/storage/`, `/app/config.php`, `/scripts/install.php`, `/public/.env`, `/assets/uploads/.htaccess` **403/404** দেয়—ফাইল/SQL/secret কখনো দেখায় না। Root এবং public document-root setup যে পদ্ধতি নিয়েছেন সেটিতেই পরীক্ষা করুন।
- [ ] `/`, `/api?action=catalog`, product slug, `/assets/...`, `/sw.js` কাজ করে; কোনো directory listing নেই।
- [ ] New signup/login, COD order, stock/total/delivery charges, tracking, admin updates এবং error handling পরীক্ষা হয়েছে।
- [ ] Unauthorized admin/evidence access এবং missing/invalid CSRF request প্রত্যাখ্যাত হয়।
- [ ] Image upload/resize, private return evidence, three-day eligibility এবং manual settlement flow সঠিক।
- [ ] Real delivery provider/webhook/Pusher acceptance প্রয়োজন হলে merchant credentials দিয়ে পরীক্ষা হয়েছে। Browser notifications permission/device অনুযায়ী ভিন্ন হতে পারে।
- [ ] Demo photos বদলে নিজের/licensed media, real brand/contact, delivery/return policy এবং বাস্তব operational process দেওয়া হয়েছে।
- [ ] Backup/restore, cron ও log rotation যাচাই হয়েছে।

**সীমা:** বর্তমান sandbox যাচাই actual Windows Laragon বা আপনার cPanel host-এর পরীক্ষা নয়। Host-specific routing/permissions, live courier এবং full go-live acceptance না করে “সব পরিবেশে production-ready” ধরে নেবেন না। Temporary public testing tunnel-এর uptime-এর নিশ্চয়তা নেই।

## Account / OTP / email update — ১৫ অক্টোবর ২০২৬

এই update-এ **নতুন migration প্রয়োজন**: backup নিয়ে `php scripts/migrate-communications.php`, তারপর `php scripts/check.php`। ছয়টি additive table; বর্তমান মোট 27। পুরোনো full SQL import/database reset নয়। Update-এর পরে আবার login করুন।

[বাংলা account ও communications guide](ACCOUNT-COMMUNICATIONS.md)-এ exact replacement files, Laragon 6, cPanel cron, SMS activation, consent/export, browser-password-save limits ও tests আছে। Gateway credentials নেই; live SMS/OTP চালু নয়। Optional email signup/order-এর requirement নয়; email sending engine অন্তর্ভুক্ত নয়।
