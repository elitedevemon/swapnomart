# Local installation — Laragon 6

> **Public test-key exception:** মালিকের অনুরোধে `app/.setup-key.php`-তে এখন একটি fixed public TEST key রাখা হয়েছে। শুধু controlled testing-এর জন্য। Fresh production-এর আগে file tracking সরিয়ে test file বাদ দিয়ে server-local private key তৈরি করুন। [নির্দেশনা ও ঝুঁকি](docs/PUBLIC-TEST-KEY.md)। নিচের auto-generated-key নির্দেশনা private/fresh production workflow-এর জন্য।

এই guide অনুসরণ করে Windows-এর **Laragon 6**-এ project install ও test করুন। Hosting-এ নেওয়ার নির্দেশনা: [cpanel.md](cpanel.md)।

> **সবচেয়ে জরুরি:** নতুন web installation-এর আগে `.env` তৈরি, SQL import বা `scripts/install.php` চালাবেন না। Web installer এগুলো নিজে সামলাবে। তবে খালি database ও database user আগে তৈরি করতে হবে। Existing store-এর ওপর installer চালাবেন না।

## ধাপ ১ — প্রয়োজনীয় সফটওয়্যার প্রস্তুত করুন

1. Laragon 6 খুলুন।
2. Laragon-এ **PHP 8.2 বা নতুন** এবং **MySQL 8.0 বা নতুন** ব্যবহার করুন। Laragon-এর নাম/version দেখেই bundled PHP/MySQL যথেষ্ট নতুন ধরে নেবেন না। পুরোনো হলে compatible version যোগ/নির্বাচন করুন।
3. PHP extensions চালু করুন:
   - `pdo_mysql`
   - `mbstring`
   - `curl`
   - `sodium`
   - `gd` — JPEG, PNG, WebP support-সহ
   - `exif`
4. Apache-এ `mod_rewrite` এবং project directory-র জন্য `AllowOverride All` দরকার।
5. PHP configuration বদলালে Apache restart করুন।
6. Laragon Terminal-এ চাইলে যাচাই করুন:

```bat
php -v
php -m
mysql --version
```

**নোট:** Terminal ও Apache আলাদা PHP version ব্যবহার করতে পারে। দুটোতেই requirements মিলতে হবে। MariaDB এই project-এর বর্তমান পরীক্ষায় certify করা হয়নি। Node/Composer runtime-এ লাগবে না; compiled assets সঙ্গে আছে।

## ধাপ ২ — Project files রাখুন

1. Fresh/uninstalled project files এই folder-এ রাখুন:

```text
C:\laragon\www\storefront
```

2. Folder-এর কাঠামো এমন হবে:

```text
storefront\
├── app\
├── database\
├── public\
├── scripts\
├── storage\
├── .htaccess
├── .env.example
├── .env.production.example
├── install.php
├── local.md
└── cpanel.md
```

3. অতিরিক্ত nested folder যেন না হয়, যেমন `storefront\project\app`।
4. Windows Explorer-এ file extensions দেখানো চালু রাখুন। Dotfiles/`.htaccess` বাদ দেবেন না।
5. Web installation-এর জন্য `.env.example` থেকে `.env` copy করবেন না। Fresh copy-তে `app/.installation-state` থাকার কথা নয়।

> আগে install করা project-এ `.env` বা installation marker থাকলে সেটি মুছে নতুন setup খোলার চেষ্টা করবেন না। আলাদা নতুন installation-এর জন্য untouched source copy এবং নতুন database নিন।

## ধাপ ৩ — Apache ও MySQL চালু করুন

1. Laragon-এর **Start All** চাপুন।
2. Apache ও MySQL running কি না দেখুন।
3. Laragon-এর **Auto Virtual Hosts** চালু রাখুন; প্রয়োজনে Reload/Restart করুন।
4. এই project-এর URL হবে:

```text
http://storefront.test
```

`http://localhost/storefront/` ব্যবহার করবেন না—arbitrary subfolder installation সমর্থিত নয়।

**Document root:** supplied root `.htaccess` project-root deployment সমর্থন করে। আরও ভালো isolation-এর জন্য virtual host DocumentRoot `C:/laragon/www/storefront/public` করতে পারেন; সেই directory-তে `AllowOverride All` ও `Require all granted` লাগবে।

## ধাপ ৪ — একটি খালি database তৈরি করুন

1. Laragon-এর Database/HeidiSQL অথবা configured phpMyAdmin খুলুন।
2. নিজের MySQL credentials দিয়ে connect করুন। Local installation-এ `root` ও blank password থাকতে পারে, কিন্তু আপনার configuration যাচাই করুন—এটি সব installation-এর default ধরে নেবেন না।
3. নতুন database তৈরি করুন:

```text
Database name: storefront_local
Character set: utf8mb4
Collation: utf8mb4_unicode_ci
```

4. সম্ভব হলে শুধু এই database-এর জন্য আলাদা user তৈরি ও privileges দিন। Local testing-এ আপনার existing local database user-ও ব্যবহার করতে পারেন।
5. Database **খালি রাখুন**। কোনো table বা SQL import করবেন না।
6. Host, port, database name, username ও password নিজের কাছে রাখুন। সাধারণ local host `localhost`/`127.0.0.1`, port `3306`; Laragon-এ অন্য port থাকলে সেটিই দিন।

## ধাপ ৫ — Installer খুলুন ও private key নিন

1. Browser-এ খুলুন:

```text
http://storefront.test
```

2. Fresh project স্বয়ংক্রিয়ভাবে `/install.php`-তে যাবে।
3. এবার local project-এর এই file text editor-এ খুলুন:

```text
C:\laragon\www\storefront\app\.setup-key.php
```

4. `PRIVATE SETUP KEY:`-এর পরে থাকা **64-character key-টুকু** copy করুন। পুরো PHP file copy করবেন না।
5. Installer-এর **Setup key** field-এ paste করুন।

Key প্রথমবার installer খোলার সময় তৈরি হয়। এটি browser URL দিয়ে পড়ার জন্য নয়; কাউকে share করবেন না। বাইরের কেউ যেন আপনার first admin তৈরি করতে না পারে, সেই জন্য key দরকার।

## ধাপ ৬ — Database connection test করুন

Installer form-এ দিন:

| Field | কী দেবেন |
|---|---|
| Database host | আপনার MySQL host, সাধারণত `localhost` |
| Port | সাধারণত `3306` |
| Database name | `storefront_local` |
| Database username | ধাপ ৪-এর user |
| Database password | ওই user-এর password; local configuration-এ blank হলে blank |
| Store URL | `http://storefront.test` |

**Test database connection** চাপুন। Connected এবং database empty দেখালে এগিয়ে যান। এই test connection/emptiness যাচাই করে; actual installation-এর জন্য schema-creation privileges-ও দরকার।

## ধাপ ৭ — Shop ও administrator সেট করুন

1. **Shop name:** নিজের দোকানের নাম দিন।
2. **Shop logo:** optional JPEG/PNG/WebP নির্বাচন করুন। Max 2 MB, প্রতি side max 4000 px ও মোট max 8 megapixels। Installer optimized WebP, max 512 px করে save করবে। SVG গ্রহণ করে না।
3. **Admin name:** নিজের নাম দিন।
4. **Admin phone:** নিজের ১১ অঙ্কের Bangladesh mobile number দিন।
5. **Admin password:** unique passphrase দিন—12–72 bytes। ইংরেজি character সাধারণত 1 byte; বাংলা character একাধিক byte।
6. **Confirm password:** একই password আবার দিন।
7. **Starting catalog** নির্বাচন করুন:
   - **Local demo:** sample products ও public demo logins-সহ testing। নিজের private admin-ও তৈরি হবে। এখানে বাস্তব customer data ব্যবহার করবেন না।
   - **Clean shop:** demo products/orders ছাড়া খালি দোকান; নিজের private admin তৈরি হবে।
8. **Install my shop** চাপুন। Processing চলাকালে refresh/close/repeated submit করবেন না।

## ধাপ ৮ — Login ও installation যাচাই করুন

1. সফল হলে storefront খুলবে।
2. Header account icon চাপুন।
3. আপনার admin phone/password দিয়ে login করুন।
4. Admin-এ shop name/logo সঠিক কি না দেখুন। পরে Website Studio থেকে পরিবর্তন করতে পারবেন।
5. Laragon Terminal-এ চালান:

```bat
cd C:\laragon\www\storefront
php scripts/check.php
```

বর্তমান complete schema-তে **27টি table** থাকার কথা। CLI check পাস হলেও browser/host acceptance আলাদাভাবে করতে হবে।

6. `/install.php` আবার খুললে **403 / Installation is locked** পাওয়া সঠিক আচরণ। এটি error নয়—one-time protection।

## ধাপ ৯ — দোকানের features পরীক্ষা করুন

- [ ] Bangla/English, mobile/desktop এবং light/dark/system theme।
- [ ] Admin product add/edit, price, stock, photos ও image processing।
- [ ] Budget filter-এর minimum/maximum visible catalog-এর আসল price-এর সঙ্গে মেলে।
- [ ] Favorite/cart, signup, generated password, returning-user login ও COD checkout।
- [ ] Order confirmation, customer tracking এবং My Returns।
- [ ] Optional email, explicit marketing consent ও unsubscribe।
- [ ] Admin orders/status tabs, notifications, Error log ও Website Studio।

Local demo-তে real payment/shipping/SMS delivery হয়েছে ধরে নেবেন না। নতুন installer configuration-এ SMS/Pusher sending চালু করা হয় না। SMS recovery-এর জন্য merchant credentials, explicit test এবং worker লাগবে; [account/communications guide](docs/ACCOUNT-COMMUNICATIONS.md) দেখুন।

## বিকল্প — Apache-এর বদলে PHP development server

Apache virtual host ব্যবহার না করলে MySQL চালু রেখে Laragon Terminal-এ:

```bat
cd C:\laragon\www\storefront
php -S 0.0.0.0:8081 -t public public/router.php
```

তারপর `http://localhost:8081` খুলুন এবং installer-এ একই Store URL দিন। একই port-এ আরেকটি server চালাবেন না। Terminal বন্ধ/Ctrl+C করলে এই development server বন্ধ হবে; এটি production hosting নয়।

## সাধারণ সমস্যা ও সমাধান

| সমস্যা | করণীয় |
|---|---|
| `storefront.test` খুলছে না | Apache running, Auto Virtual Hosts এবং Windows hosts mapping যাচাই করুন; Laragon Reload করুন। |
| CSS/JS নেই বা সব route 404 | Project nesting, document root, `.htaccess`, `mod_rewrite`, `AllowOverride All` দেখুন; subfolder URL নয়। |
| `.setup-key.php` নেই | আগে installer খুলুন; `app/` writable ও requirements ঠিক কি না দেখুন। Hidden file-টিও খুঁজুন। |
| Cannot connect to MySQL | MySQL service, host/port, database name, user/password ও privileges পরীক্ষা করুন। |
| Database is not empty | অন্য একটি নতুন খালি database ব্যবহার করুন; existing tables drop করবেন না। |
| Installation locked | Existing config/marker বা installation already started। Normal updates-এ installer নয়। |
| 503 after interrupted setup | Config/marker/database privately inspect করুন; marker delete বা automatic reinstall নয়। |
| PHP extension missing | Laragon-এর ব্যবহৃত PHP-তে extension enable করে Apache restart করুন। |
| Logo rejected | PNG/JPEG/WebP, file size ও dimensions limits মিলিয়ে আবার নির্বাচন করুন। |

## cPanel-এ যাওয়ার আগে

- **একই installed clean shop স্থানান্তর:** database, `.env`, `app/.installation-state`, uploads এবং private storage/keys একসঙ্গে move করতে হবে; installer rerun নয়।
- **Demo দেখে নতুন production শুরু:** untouched uninstalled source copy ও আলাদা empty production database ব্যবহার করুন। Installed demo-এর marker মুছে reuse করবেন না।
- পরের ধাপগুলো [cpanel.md](cpanel.md)-এ আছে।

**সীমা:** এই guide current implementation অনুযায়ী লেখা। Actual Windows Laragon configuration আপনার machine-এ যাচাই করতে হবে; আগের sandbox tests তার বিকল্প নয়।
