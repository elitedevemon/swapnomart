# Account recovery, checkout & communications update

**15 October 2026 · WF-0016 · Laragon 6 → cPanel**

## কী পরিবর্তন হয়েছে

- Header-এ শুধু account icon; screen reader-এর accessible label রাখা হয়েছে।
- Signup এখনো **নাম, ফোন, ঠিকানা**—কোনো OTP বা email বাধ্যতামূলক নয়। তৈরি হওয়া password একটি আসল username/password form-এ দেখানো হয়; reveal, copy ও save/continue আছে। Browser/Google-এর password-save prompt **নিশ্চিত করা যায় না**। Password browser storage-এ রাখা হয় না।
- সফল order-এর confirmation ও generated password আর পরের catalog/account refresh-এর ওপর নির্ভর করে না। Cart/request key একবারই clear হয়; backend-এর authenticated idempotency duplicate order আটকায়। Confirmed order-এর notification/contact-preference failure আলাদা log হয়। মূল reported pause-page error এই environment-এ reproduce হয়নি; defensive fix ও injected refresh-failure test হয়েছে।
- **My Returns → Sign out** ক্রম ঠিক করা হয়েছে। **Track my parcel** এখন নিজের order-এর authorized tracking endpoint থেকে shipment/status/timeline দেখায়। এটি store/courier sync-এর সর্বশেষ সংরক্ষিত তথ্য; handset GPS বা guaranteed live courier update নয়।
- Login ও returning-customer checkout-এ **Forgot password**। Reset-এর আগে SMS code দিয়ে ফোনের নিয়ন্ত্রণ প্রমাণ করতে হবে। Phone/order details জানলেই reset করা যাবে না।
- Checkout/account-এ optional contact email, আলাদা email/SMS marketing consent; newsletter-এ explicit checkbox। Checkout-এর unchecked boxes পুরোনো consent/email মুছে দেয় না। Account preferences-এ স্পষ্টভাবে consent প্রত্যাহার করা যায়।
- Admin → **Communications**: gateway settings, explicit paid test, worker status, OTP summary, masked attempt logs, consent audience/export/unsubscribe, verified SMS campaign queue/cancel।

## গুরুত্বপূর্ণ: এখনই live SMS চালু নয়

কোনো SMS provider credentials দেওয়া হয়নি। Configuration-ready **BulkSMSBD adapter** যুক্ত হয়েছে; এটি আপনার নির্বাচিত provider দাবি করা হচ্ছে না। অন্য gateway ব্যবহার করলে তার official API অনুযায়ী আলাদা adapter লাগবে। **Live OTP/SMS delivery পরীক্ষা হয়নি; email sender/SMTP engine যোগ করা হয়নি।**

Email audience সংগ্রহ/export করা যায়; email ownership যাচাই করা হয়নি। নিজের email platform-এ verification/double opt-in, bounce handling ও unsubscribe synchronization করে তবেই campaign পাঠান। CSV export নিজে email পাঠায় না। Authentication OTP কখনো marketing subscription নয়।

## Existing installation: আগে backup, তারপর update

Database, uploads, `.env`, private storage এবং `.encryption-key` একসঙ্গে privately backup করুন। `.env`/storage overwrite করবেন না। পুরোনো install-এর ওপর `schema.sql` বা `bonon-demo.sql` import করবেন না।

### Replace/add these application files together

```text
app/bootstrap.php
app/communications.php                  NEW
app/communications-api.php              NEW
public/api.php
public/index.php
public/assets/app.js
public/assets/enhancements.js
public/assets/studio.js
public/assets/returns.js
public/assets/communications.js          NEW
public/assets/communications.css         NEW
database/communications.sql              NEW
scripts/migrate-communications.php       NEW
scripts/communications-worker.php        NEW
scripts/check.php
```

এছাড়া downloadable project-এর `database/schema.sql`, `database/bonon-demo.sql`, `README.md`, `docs/START-HERE-BN.md`, `docs/QA.md`, এই guide ও `working-flow.md` আপডেট হয়েছে। দুটি full SQL file **শুধু নতুন খালি database-এর জন্য**। Existing private Pusher defaults অপরিবর্তিত; সেগুলো প্রকাশ করবেন না।

Laragon Terminal, project root থেকে:

```bat
cd C:\laragon\www\storefront
php scripts/migrate-communications.php
php scripts/check.php
```

Migration **ছয়টি additive table** তৈরি করে; মোট বর্তমান table **27**। Accounts/orders reset করে না এবং পুরোনো subscriber list-কে নতুন consent হিসেবে ধরে না। Migration পুনরায় চালানো নিরাপদ; existing malformed/partial tables নিজে repair করে না। পুরোনো অন্যান্য update না থাকলে README-এর আগের migrations-ও প্রযোজ্য।

cPanel-এ Terminal না থাকলে backup নিয়ে শুধু `database/communications.sql` বর্তমান database-এ phpMyAdmin দিয়ে import করতে পারেন। এরপর actual host acceptance checks করুন। Hard refresh **Ctrl+F5** এবং পুনরায় sign in করুন: security update পুরোনো login sessions invalid করে।

## SMS activation checklist

1. শুধু fictional-data demo-তে UI দেখুন। Real messaging-এর জন্য `.env`-এ `demo=false`, private non-demo admin, production HTTPS ও সঠিক public `APP_URL` লাগবে। Admin recovery এই customer OTP দিয়ে করা যায় না।
2. BulkSMSBD merchant account, valid balance এবং approved sender ID সংগ্রহ করুন। Public/source code-এ key লিখবেন না। Admin → Communications → Settings-এ key/sender/brand save করুন। Key encrypted private setting হিসেবে থাকে; read API key ফেরত দেয় না।
3. নিজের ফোন দিয়ে checkbox-এ অনুমতি দিয়ে **একটি chargeable test SMS** পাঠান। Gateway submission accepted হওয়া এবং ফোনে message পাওয়া আলাদাভাবে যাচাই করুন। **202 = accepted, delivered নয়।** Unknown outcome হলে provider portal পরীক্ষা করুন; blind retry নয়।
4. Test accepted হওয়ার পরে OTP recovery enable করে save করুন। Marketing দরকার হলে আলাদাভাবে enable করুন। Credentials/sender পরিবর্তন করলে sending বন্ধ হয়; নতুন test লাগে। Key মুছলে sending বন্ধ হয়।
5. নিচের worker/cron configure করুন। Admin-এর explicit “Send up to 5 queued messages” test-এর জন্য; automatic OTP-এর বদলি নয়।
6. নিজের customer phone দিয়ে request → receive → reset → new-password login পরীক্ষা করুন। Password না জেনে OTP ছাড়া staff যেন customer account overwrite না করে।
7. SMS marketing contact আগে আলাদা SMS-consent verification সম্পন্ন করবে। Campaign-এর প্রতিটি recipient active, verified SMS opt-in হতে হবে। সর্বোচ্চ 100 explicitly selected recipients; required unsubscribe link যোগ হয়। Queue করার পরও worker consent আবার পরীক্ষা করে।

Default cap 100 **provider attempts/day (UTC)**; এটি SMS segment/credit cap নয়। বাংলা/Unicode এবং long unsubscribe links-এর কারণে এক message-এ একাধিক billed segment হতে পারে। OTP ও test templates বাংলা; approved masking sender-এর provider language/template rules মেনে test করুন। No live sender/template acceptance has been certified.

### Worker / cron

Laragon-এ testing-এর সময় আলাদা Terminal-এ foreground worker চালাতে পারেন; বন্ধ করতে Ctrl+C:

```bat
cd C:\laragon\www\storefront
php scripts/communications-worker.php
```

Production cPanel cron **প্রতি মিনিটে**, hosting-এর সঠিক PHP binary/path ব্যবহার করুন:

```sh
* * * * * /usr/local/bin/php /home/CPANEL_USER/public_html/scripts/communications-worker.php --once >> /home/CPANEL_USER/private/sms-worker.log 2>&1
```

`CPANEL_USER`, PHP binary, document root এবং pre-created writable private log directory বদলান। Standard root `.env` থাকলে auto-load হয়। Outside-webroot config/storage ব্যবহার করলে web এবং CLI-কে একই paths/key দিন:

```sh
APP_ENV_FILE=/home/CPANEL_USER/private/store.env APP_STORAGE=/home/CPANEL_USER/private/store-storage /usr/local/bin/php /home/CPANEL_USER/public_html/scripts/communications-worker.php --once
```

One-shot worker সর্বোচ্চ 10 messages process করে; database lock duplicate simultaneous send worker আটকায়; OTP অগ্রাধিকার পায়। Cron প্রতি মিনিটে হলে কিছু delay স্বাভাবিক; default OTP expiry 5 মিনিট, configurable 3–10 মিনিট। High-volume delivery/queue monitoring hosting অনুযায়ী পরীক্ষা করুন। কোনো worker/tunnel স্থায়ীভাবে নতুন করে চালু করা হয়নি।

## Security and consent behavior

- Six-digit code; random challenge ID; keyed hash, same-browser binding, purpose binding এবং password-version binding। Plain OTP API response/log-এ নেই। Pending SMS body encrypted; attempt/cancellation-এর পর payload clear হয়।
- 60-second resend cooldown, phone প্রতি 3 requests/hour ও 5/day, IP প্রতি 5 requests/5 minutes; verification IP প্রতি 20 attempts/5 minutes এবং challenge প্রতি 5 wrong attempts। Unknown/admin/demo customer reset requests generic response পায়; তাদের reset SMS পাঠানো হয় না।
- Expiring, one-use reset; success-এর পরে পুরোনো password-bound sessions invalid। অন্য browser tab বা device-এ challenge transfer করবেন না।
- Reset ও SMS marketing verification আলাদা purpose। Email collection single opt-in/unverified; login/reset identity-এর বিকল্প নয়।
- Anonymous newsletter request আগের unsubscribe reverse করতে পারে না। Export শুধু active email consent অথবা verified active SMS consent দেয়; CSV-তে opt-out URL ও verification label থাকে। Export-পরবর্তী unsubscribe আপনার external email/SMS platform-এ sync করা merchant-এর দায়িত্ব।
- Unknown/timeout sends স্বয়ংক্রিয় retry হয় না। Rejected আলাদা state; provider record দেখুন। Campaign cancel কেবল queued messages থামায়—already accepted/in-flight SMS recall করা যায় না।
- Logs-এ masked phone, purpose, outcome, provider code/time থাকে; **OTP/message text থাকে না**। Private database/outbox contains contact PII: access ও backups সুরক্ষিত রাখুন।
- Cleanup action: 1 দিনের পুরোনো OTP outbox payload/rows, 7 দিনের পুরোনো challenges, 30 দিনের পুরোনো nonqueued outbox rows, 90 দিনের পুরোনো SMS logs সরায়। Worker expired unsent OTP payload মুছে দেয়। Consent history নিজে থেকে delete হয় না।

## Acceptance before real use

- New signup/order without OTP; generated password copied/saved; returning-user login and second order।
- Chrome/Google password manager behavior actual device-এ; browser prompt বাধ্য করা যায় না।
- Actual SMS arrival, wrong/expired/reused OTP rejection, resend limits, old-session invalidation, successful new-password login।
- Optional email with/without checkbox, profile withdrawal, token unsubscribe, audience/export suppression and external-platform synchronization।
- Track parcel after real authorized courier sync; My Returns placement; mobile/Bangla/dark theme; previous Studio/orders/products/notifications functionality।
- Actual Laragon 6/Apache এবং cPanel HTTPS, private paths, cron, backup/restore। Local PHP-router checks Apache/cPanel certify করে না।

See `QA.md` for executed tests and explicit limitations.
