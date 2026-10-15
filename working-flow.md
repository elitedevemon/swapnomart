# Working Flow — কাজের ইতিহাস ও নিরাপদ পরিবর্তনের নিয়ম

> **প্রকল্পের প্রতিটি কাজ শুরু করার আগে এই ফাইল পড়তে হবে।**
> কোনো ফাইল তৈরি, পরিবর্তন, স্থানান্তর বা মুছে দিলে নিচের কাজের ইতিহাসে timestamp-সহ বিস্তারিত যোগ করতে হবে।

## ১. সময় ও লগের নিয়ম

- **Timezone:** `Asia/Dhaka` — বাংলাদেশ সময়, UTC+06:00।
- **Timestamp format:** `YYYY-MM-DD HH:mm:ss +06:00`। Timestamp প্রকৃত কাজের সময় থেকে নিতে হবে; অনুমান করা যাবে না।
- এই লগের মূল অবস্থান: `/home/user/working-flow.md`।
- নতুন কাজের জন্য নতুন entry যোগ হবে। পুরোনো entry মুছে বা নীরবে পাল্টে ইতিহাস পরিবর্তন করা যাবে না। ভুল থাকলে timestamp-সহ correction entry যোগ করতে হবে।
- একই কাজের progress/status আপডেটও আলাদা timestamp-এ লিখতে হবে।
- কোনো কাজের ফল জানা না থাকলে **UNKNOWN / যাচাই হয়নি** লিখতে হবে; সফল ধরে নেওয়া যাবে না।
- Password, token, secret key, private tunnel credentials, customer-এর ব্যক্তিগত তথ্য বা `.env`-এর সংবেদনশীল content এই ফাইলে লেখা যাবে না। প্রয়োজন হলে শুধু সংশ্লিষ্ট file path লিখতে হবে।

## ২. প্রতিটি কাজের আগে ও পরে যা করতে হবে

### কাজ শুরুর আগে

1. এই ফাইলের নিয়ম, বর্তমান অবস্থা ও সর্বশেষ entry পড়তে হবে।
2. ব্যবহারকারীর সর্বশেষ অনুরোধের সঙ্গে আগের অসম্পূর্ণ কাজ মিলিয়ে দেখতে হবে।
3. সংশ্লিষ্ট ফাইল, dependency, database ও process বাস্তবে আছে কি না পরীক্ষা করতে হবে। পুরোনো লগকে বর্তমান অবস্থার প্রমাণ ধরা যাবে না।
4. Service চালু করার আগে existing service/process/port পরীক্ষা করতে হবে; duplicate server চালানো যাবে না।
5. কাজের scope, affected paths ও ঝুঁকি লিখে **PLANNED / IN PROGRESS** entry যোগ করতে হবে।
6. গুরুত্বপূর্ণ ফাইল overwrite, database migration, destructive operation বা deletion-এর আগে backup/rollback ব্যবস্থা নিশ্চিত করতে হবে। ব্যবহারকারীর অনুমতি প্রয়োজন হলে তা নিতে হবে।

### কাজ চলাকালে

- **CREATE:** কী ফাইল/ফোল্ডার/service তৈরি হলো, পূর্ণ path এবং কেন প্রয়োজন—লিখতে হবে।
- **MODIFY:** আগের অবস্থা, কী বদলানো হয়েছে, কেন, প্রভাব ও compatibility—লিখতে হবে।
- **MOVE / RENAME:** পুরোনো path → নতুন path এবং যেসব reference বদলানো হয়েছে—লিখতে হবে।
- **DELETE / REMOVE:** সুনির্দিষ্ট path, কারণ, অনুমতি/অনুরোধের ভিত্তি এবং backup/পুনরুদ্ধারের উপায়—লিখতে হবে। যাচাই ছাড়া broad cleanup করা যাবে না।
- **SERVICE / DEPENDENCY:** install, start, stop, restart, configuration change বা runtime restoration হলে service name, port ও ফল লিখতে হবে। কোনো secret command argument কপি করা যাবে না।
- **FAILURE:** ব্যর্থ command/পরীক্ষার নিরাপদ সারাংশ, জানা কারণ, প্রভাব এবং অসম্পূর্ণ কাজ লিখতে হবে।
- কাজ করতে গিয়ে আগে থেকে কোনো ফাইল হারানো পাওয়া গেলে **DISCOVERED MISSING** লিখতে হবে। প্রমাণ ছাড়া কে বা কোন ব্যবস্থা মুছেছে তা নিশ্চিত বলা যাবে না।

### কাজ শেষ হওয়ার আগে

1. প্রাসঙ্গিক syntax, API, browser, service বা data-preservation পরীক্ষা করতে হবে।
2. কী পরীক্ষা করা হয়েছে এবং কী পরীক্ষা করা হয়নি—আলাদা করে লিখতে হবে।
3. কাজের ফল **DONE / PARTIAL / FAILED / BLOCKED** হিসেবে log করতে হবে।
4. বর্তমান অবস্থা, বাকি কাজ এবং পরবর্তী সহকারীর প্রথম করণীয় লিখতে হবে।
5. প্রয়োজনীয় backup সংরক্ষিত হয়েছে কি না যাচাই করতে হবে।
6. শেষে ব্যবহারকারীকে ফল জানাতে হবে; অসম্পূর্ণ বা অস্থায়ী সমাধানকে স্থায়ী সমাধান বলা যাবে না।

## ৩. প্রকল্পের পরিচিত অবস্থান ও সতর্কতা

### Workspace-এ থাকা গুরুত্বপূর্ণ পথ

| Path | উদ্দেশ্য |
|---|---|
| `/home/user/bonon/` | PHP/MySQL storefront-এর source project |
| `/home/user/bonon/public/` | একমাত্র public document root |
| `/home/user/bonon/.env` | Private application configuration; content এই লগে প্রকাশ করা যাবে না |
| `/home/user/bonon/storage/live-state.sql` | Private database recovery backup; live database দিয়ে প্রতিস্থাপনের আগে অবস্থা যাচাই করতে হবে |
| `/home/user/testing-runtime/README.md` | Runtime installation, recovery, service management ও পরীক্ষার নির্দেশনা |
| `/home/user/testing-runtime/supervisord.conf` | MySQL, PHP ও public-link process supervision |
| `/home/user/testing-runtime/storefront-testing.service` | systemd service definition |
| `/home/user/testing-link-supervisor.py` | Public tunnel health check, reconnect ও backup orchestration |
| `/home/user/testing-runtime/current-link.json` | শেষ সংরক্ষিত public URL; URL কাজ করছে এমন নিশ্চয়তা নয় |
| `/home/user/testing-runtime/link-status.json` | শেষ health check-এর সময়/ফল; নতুন করে যাচাই ছাড়া live status হিসেবে ব্যবহার করা যাবে না |
| `/home/user/testing-runtime/private/` | Private runtime credentials; কখনো public serve/package/log করা যাবে না |
| `/home/user/testing-runtime/logs/` | সংরক্ষিত diagnostic log tails |

### সর্বশেষ নথিভুক্ত runtime layout

- Service: `storefront-testing.service`।
- Supervisor programs: `mysql`, `storefront`, `public-link`।
- PHP storefront port: `8081`। MySQL port: `3306`।
- Runtime binaries: `/opt/storefront-testing/`।
- Live MySQL data: `/var/lib/storefront-testing/mysql/`।
- Sockets/PIDs: `/run/storefront-testing/`।
- Live service logs: `/var/log/storefront-testing/`।
- **গুরুত্বপূর্ণ live data/binary/socket/log workspace `.cache`-এ রাখা যাবে না।** আগে `.cache` অনুপস্থিত হয়ে service process থাকা সত্ত্বেও site/tunnel ভেঙেছিল; বিস্তারিত `testing-runtime/README.md`-এ আছে।
- Standard OS directory-ও সম্পূর্ণ environment replacement-এর পর থাকবেই—এমন নিশ্চয়তা নেই। Workspace backup ও restoration script আলাদা করে রাখতে হবে।
- Native runtime ও public tunnel-এর বর্তমান অবস্থা প্রতিবার পরীক্ষা করতে হবে। এই ফাইলে কোনো পুরোনো “RUNNING” বা “HTTP 200” লেখা থাকলে সেটি শুধু সংশ্লিষ্ট পরীক্ষার সময়ের ফল।
- বর্তমান storefront-এর পূর্বনির্ধারিত return policy: delivery থেকে ৩ দিন; ভুল/ক্ষতিগ্রস্ত/ত্রুটিপূর্ণ বা গুরুত্বপূর্ণ বিবরণগত অমিল থাকা পণ্যের জন্য। নতুন অনুরোধ ছাড়া নীতি বা পূর্বের design/features বদলানো যাবে না।

## ৪. Entry template

```markdown
### WF-XXXX — YYYY-MM-DD HH:mm:ss +06:00
- Status: PLANNED / IN PROGRESS / DONE / PARTIAL / FAILED / BLOCKED / UNKNOWN
- ব্যবহারকারীর অনুরোধ:
- কাজ শুরুর আগে যা পড়া/যাচাই করা হয়েছে:
- Action: CREATE / MODIFY / MOVE / DELETE / SERVICE / VERIFY / DISCOVERED MISSING
- সংশ্লিষ্ট path/service:
- আগের অবস্থা:
- কী করা হয়েছে / করতে হবে:
- কারণ ও সম্ভাব্য প্রভাব:
- Backup / rollback:
- পরীক্ষা ও ফল:
- পরীক্ষা হয়নি / সীমাবদ্ধতা:
- বাকি কাজ / পরবর্তী পদক্ষেপ:
```

---

## ৫. কাজের ইতিহাস

### WF-0001 — 2026-10-15 12:37:05 +06:00
- **Status:** IN PROGRESS — initial setup।
- **ব্যবহারকারীর অনুরোধ:** `working-flow.md` তৈরি করা; ভবিষ্যতে প্রতিবার কাজের আগে পড়া এবং তৈরি/সরানোসহ পরিবর্তনের বিস্তারিত timestamp-সহ লেখা।
- **কাজ শুরুর আগে যাচাই:** `/home/user/working-flow.md` ও `/home/user/bonon/working-flow.md` পরীক্ষা করা হয়েছে; কোনোটিই ছিল না। তাই পুরোনো working-flow log overwrite করা হচ্ছে না।
- **Action:** VERIFY + CREATE।
- **সংশ্লিষ্ট path:** `/home/user/working-flow.md`।
- **যা তৈরি করা হচ্ছে:** কাজের আগে/পরে checklist, timestamp ও secrecy rules, create/modify/move/delete-এর বিস্তারিত recording format, project reference paths, runtime সতর্কতা এবং append-only কাজের ইতিহাস।
- **Reference existence verification:** `/home/user/bonon/`, `storage/live-state.sql`, `/home/user/testing-link-supervisor.py`, `testing-runtime/README.md`, `supervisord.conf`, `storefront-testing.service` ও `current-link.json` পাওয়া গেছে। এটি শুধু file existence check।
- **কারণ:** আগের কাজ, dependency, deletion/missing-file incident ও পুনরুদ্ধারের তথ্য পরের কাজে হারিয়ে না ফেলা।
- **Backup / rollback:** নতুন documentation file; কোনো বিদ্যমান project file replace/delete করার প্রয়োজন নেই।
- **পরীক্ষা:** পূর্ববর্তী log না থাকার বিষয় এবং reference file-গুলোর অস্তিত্ব যাচাই করা হয়েছে। ফাইল লেখা শেষ হলে read-back verification হবে।
- **সীমাবদ্ধতা:** অতীতের সব পরিবর্তনের পূর্ণ ইতিহাস এই ফাইলে পুনর্গঠন করা হয়নি। আগের runtime layout পরিচিত context হিসেবে দেওয়া হয়েছে; এই documentation task-এ server/tunnel পুনরায় পরীক্ষা বা পরিবর্তন করা হয়নি।
- **বাকি কাজ:** নতুন file read-back করে completion entry যোগ করা এবং ব্যবহারকারীর সামনে ফাইলটি খোলা।


### WF-0002 — 2026-10-15 12:38:41+06:00
- **Status:** DONE — WF-0001-এর completion update।
- **Action:** VERIFY + MODIFY (শুধু এই log-এর completion entry যোগ করা)।
- **সংশ্লিষ্ট path:** `/home/user/working-flow.md`।
- **আগের অবস্থা:** নতুন ফাইলটি লেখা হয়েছিল; read-back verification বাকি ছিল।
- **যা করা হয়েছে:** ফাইলের সম্পূর্ণ content read-back করা হয়েছে; timezone, operation categories, deletion/backup নির্দেশনা, project reference এবং প্রথম history entry উপস্থিত থাকার পরীক্ষা পাস করেছে। এই completion entry যোগ করা হয়েছে; আগের entry অক্ষুণ্ণ আছে।
- **ফল:** ভবিষ্যৎ কাজের আগে এই ফাইল পড়া এবং প্রতিটি পরিবর্তনের timestamp-সহ বিস্তারিত recording-এর নিয়ম প্রতিষ্ঠিত। প্রথম documentation কাজের record-ও রাখা হয়েছে।
- **অন্য পরিবর্তন:** এই task-এ কোনো application code, database, media, runtime configuration বা service পরিবর্তন/মুছে দেওয়া হয়নি। কোনো secret এখানে লেখা হয়নি।
- **Backup / rollback:** বিদ্যমান ফাইল overwrite করা হয়নি; আলাদা project/data backup প্রয়োজন হয়নি।
- **সীমাবদ্ধতা:** এটি manual assistant-maintained work log; automatic filesystem monitoring নয়।
- **পরবর্তী পদক্ষেপ:** ব্যবহারকারীর সামনে এই ফাইলটি খুলে দেওয়া। পরের project task-এর প্রথম ধাপ হবে এই ফাইল পড়ে নতুন planning entry যোগ করা।

### WF-0003 — 2026-10-15 12:41:44+06:00
- **Status:** IN PROGRESS।
- **অনুরোধ:** Workspace-এ শুধু প্রয়োজনীয় পূর্ণ project রাখা; পুরোনো ZIP/garbage বাদ দেওয়া; কোনো নতুন ZIP না বানানো; Laragon ও cPanel deployment প্রস্তুত করা।
- **আগে পড়া:** working-flow.md সম্পূর্ণ।
- **Action:** PLAN / INVENTORY; পরে পৃথক CREATE/MODIFY/MOVE/DELETE entries হবে।
- **Scope:** Workspace inventory, source dependency audit, explicit obsolete artefact removal, private runtime/secret isolation, deployment documentation এবং বাস্তব smoke tests।
- **সুরক্ষা:** প্রথমে প্রয়োজনীয়তা ও references যাচাই; unique application/media/customer data নীরবে মুছব না। পরিবর্তনের backup workspace-এর বাইরে private temporary directory-তে রাখা হবে, যাতে download-এ duplicate project না থাকে। Historical ZIP অপসারণ ব্যবহারকারী স্পষ্টভাবে অনুমোদন করেছেন।
- **সীমাবদ্ধতা:** Laragon/Windows ও প্রকৃত cPanel এই sandbox-এ নেই; compatible config ও equivalent tests করা হবে, পরীক্ষা না করা বিষয় বলা হবে।
- **পরবর্তী ধাপ:** Inventory ও deployment audit।

### WF-0004 — 2026-10-15 12:44:52+06:00
- **Status:** IN PROGRESS — inventory complete; approved cleanup/deployment scope।
- **Action:** PLAN / BACKUP / MOVE / MODIFY / DELETE।
- **যাচাই:** Live DB-তে120 products ও85 orders সব demo;15 users-এর মধ্যে1 non-demo user আছে। তাই live database বা user data delete করা হবে না। Public upload references-এ3টি JPEG পাওয়া গেছে; unique referenced images রাখা হবে।
- **পরিকল্পিত layout:** `/home/user/bonon/`-এর maintained application source `/home/user/` project root-এ আনা; `working-flow.md` অপরিবর্তিত historyসহ রাখা।
- **Runtime isolation:** `/home/user/testing-runtime/` ও `testing-link-supervisor.py` → `/opt/storefront-testing/control/`; live `.env`, encryption key, evidence/error journals, live SQL backups → `/var/lib/storefront-testing/private/`। Runtime-এর source/config references নতুন layout অনুযায়ী সংশোধন হবে। Workspace-এ credentials বা current customer DB export রাখা হবে না।
- **Export cleanup:**3 historical ZIP, preview PNGs, installation logs, old `backups/`, SQLite seed/import tools, one-off patch scripts, stale QA fixtures/dumps ও unreferenced generated upload pairs অপসারণ করা হবে। Exact removed/moved path তালিকা completion entries-তে থাকবে। Maintained source/assets/schema/feature docs রেখে fresh install/admin creation, Apache protection, Laragon/cPanel guide যোগ হবে।
- **Backup / rollback:** `/var/lib/storefront-testing/cleanup-rollback/`-এ private directory copy (ZIP নয়); workspace download-এ থাকবে না। Platform replacement হলে এই temporary backup থাকার নিশ্চয়তা নেই।
- **Production scope:** Fresh production database ও real admin তৈরির CLI, default-secret-free config templates, secure public/root Apache routing, writable-directory protection; live courier/payment acceptance বা commercial imagery clearance দাবি নয়।
- **পরবর্তী ধাপ:** Backup, runtime stop, exact-path moves/removals; তারপর code/config tests।

### WF-0005 — 2026-10-15 12:45:34+06:00
- **Status:** PARTIAL — reorganization complete; deployment edits/tests pending।
- **Backup:** `/var/lib/storefront-testing/cleanup-rollback/`; full manifest retained there. Old ZIPs intentionally deleted without a second ZIP backup; maintained source was backed up. No new ZIP created.
- **Data preservation:** Private preview configuration, complete prior storage and the non-demo user database are retained outside workspace.
- **Changes (each operation records exact paths and reason):**
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/BONON-v3-Studio-Delivery.zip` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/package-lock.json` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/restore-link-runtime.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-admin.png` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/gd-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-checkout.cjs` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-browser-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/package.json` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-smoke.cjs` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/curl-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/browser-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/BONON-storefront-v2.zip` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/mysql-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/Storefront-v3.1-Shopping.zip` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-policy-dark.png` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/return-runtime.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/runtime-restore.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/returns-mobile.png` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/logo-render-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/php-install.log` — Obsolete distributable ZIP / generated preview, installation log or duplicate development harness; not application source.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/runtime-start-history.json` → `/opt/storefront-testing/control/runtime-start-history.json` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/README.md` → `/opt/storefront-testing/control/README.md` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/verify-public.cjs` → `/opt/storefront-testing/control/verify-public.cjs` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/.gitignore` → `/opt/storefront-testing/control/.gitignore` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/restore-latest.log` → `/opt/storefront-testing/control/restore-latest.log` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/logs` → `/opt/storefront-testing/control/logs` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/snapshot-database.php` → `/opt/storefront-testing/control/snapshot-database.php` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/private` → `/opt/storefront-testing/control/private` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/qa-service-recovery.cjs` → `/opt/storefront-testing/control/qa-service-recovery.cjs` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/supervisord.conf` → `/opt/storefront-testing/control/supervisord.conf` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/browser-install.log` → `/opt/storefront-testing/control/browser-install.log` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/storefront-testing.service` → `/opt/storefront-testing/control/storefront-testing.service` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/supervisor-install.log` → `/opt/storefront-testing/control/supervisor-install.log` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/install-runtime.sh` → `/opt/storefront-testing/control/install-runtime.sh` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/record-runtime-start.py` → `/opt/storefront-testing/control/record-runtime-start.py` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/current-link.json` → `/opt/storefront-testing/control/current-link.json` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/start-storefront.py` → `/opt/storefront-testing/control/start-storefront.py` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/restore-database.php` → `/opt/storefront-testing/control/restore-database.php` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-runtime/link-status.json` → `/opt/storefront-testing/control/link-status.json` — Sandbox service tooling moved out of downloadable project.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/testing-runtime` — Empty directory after runtime move.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/testing-link-supervisor.py` → `/opt/storefront-testing/control/testing-link-supervisor.py` — Sandbox-only tunnel manager.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/.env` → `/var/lib/storefront-testing/private/preview.env` — Private current preview credentials; retain outside project export.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/storage` → `/var/lib/storefront-testing/private/storage` — Preserve all live data, private keys, evidence and recovery history outside project export.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/Dockerfile` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/README.md` → `/home/user/README.md` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/database` → `/home/user/database` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/shopping-admin-diagnostic.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/.gitignore` → `/home/user/.gitignore` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/studio-mobile-product.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/delivery-hub.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/.env.example` → `/home/user/.env.example` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/.dockerignore` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/shopping-mobile-toast.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/package.json` → `/home/user/package.json` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/dark-admin-preview.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/backups/api-v1.php` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/backups/api-v2.php` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/backups/app-v1.js` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/backups/bootstrap-sqlite.php` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/backups/enhancements-v2.js` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/scripts` → `/home/user/scripts` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/admin-preview.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/studio-admin.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/delivery-mobile-bn.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/app` → `/home/user/app` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/errors-mobile.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/docs` → `/home/user/docs` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/dispatch-qa.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/public` → `/home/user/public` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/delivery-providers.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/studio-home.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/dark-bn-preview.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/chart-fix.txt` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/bonon/tailwind.config.js` → `/home/user/tailwind.config.js` — Flatten maintained project into workspace root for download/deployment.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/studio-mobile-menu.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/download_assets.py` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/shopping-home-top.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/compose.yaml` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/shopping-mobile-bn.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/shopping-cart-toast.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/make_catalog.py` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/mobile-v2.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/storefront-preview.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon/mobile-preview.png` — Obsolete preview screenshot, old backup/tool or unused Docker-only deployment wrapper; pre-change project preserved in private rollback directory.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/bonon` — Empty former project wrapper after flattening.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/app/seed.php` — Unreferenced SQLite/demo generation source; current MySQL install SQL retained.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/app/catalog.json` — Unreferenced SQLite/demo generation source; current MySQL install SQL retained.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-shopping.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/update_api.py` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-delivery-ui.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-couriers.php` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/migrate.php` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-errors.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-studio.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-returns-fixtures.php` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-webhooks.py` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/update_frontend.py` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-returns-concurrency.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-returns.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/qa-returns-checkout.cjs` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/scripts/wire-studio.py` — Historical one-off or fixed-fixture QA/import/patch script, not needed to run/install the delivered app; deployment tests will replace portable acceptance checks.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/docs/deploy/bonon-courier.service` — Old server-specific systemd deployment example; cPanel cron instructions will be supplied.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/docs/START-HERE-BN.md` — Outdated ZIP-era setup instructions replaced by current Laragon/cPanel guide.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/hero.png` — Verified unreferenced duplicate generated hero or remote-font scratch CSS; used local JPG/fonts remain.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/fonts-remote.css` — Verified unreferenced duplicate generated hero or remote-font scratch CSS; used local JPG/fonts remain.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/acd20d5c5c2980d57c149e30a7dd18d7-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/acd20d5c5c2980d57c149e30a7dd18d7-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/787781836066af66101a23cf33f934a4-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/36a3142b56a8b2e77396b1a3dbd1be73-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/89c33f1878265a5778c8fea43392cf1b-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/99dc7118dd907c0bb5416c9194382566-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/36a3142b56a8b2e77396b1a3dbd1be73-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/040a89b3c4eef32dc9ad28d6ae7763aa-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/787781836066af66101a23cf33f934a4-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/89c33f1878265a5778c8fea43392cf1b-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/p-9ee35dc1a6d4394e9d92899049fb-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/040a89b3c4eef32dc9ad28d6ae7763aa-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/99dc7118dd907c0bb5416c9194382566-thumb.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **DELETE** `/home/user/public/assets/uploads/p-9ee35dc1a6d4394e9d92899049fb-product.webp` — Generated QA upload with no reference in live products/order snapshots/slides/settings; rollback copy retained outside workspace.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/public/assets/image-sources.json` → `/home/user/docs/ASSET-SOURCES.json` — Preserve image provenance as documentation, not public runtime payload.
  - `2026-10-15 12:45:34+06:00` **MOVE** `/home/user/public/assets/input.css` → `/home/user/resources/tailwind.css` — Tailwind build input belongs with editable source resources.
  - `2026-10-15 12:45:34+06:00` **CREATE** `/home/user/storage` — Clean writable runtime directory for a new installation; preview will use external APP_STORAGE.
- **Next:** Adjust runtime references/configuration, supply safe fresh installation and Apache routing, then test before declaring completion.

### WF-0006 — 2026-10-15 12:46:56+06:00
- **Status:** IN PROGRESS — deployment/configuration changes।
- **CREATE planned:** `app/config.php`, root `.htaccess`, `storage/.htaccess`, upload `.htaccess`, `.env.production.example`, fresh-install/admin/preflight CLI scripts, Laragon/cPanel/go-live docs and portable deployment tests।
- **MODIFY planned:** bootstrap/errors/Studio/returns private-storage paths; public routing/protection; neutral default branding; DB collation/fresh-install safety; README/build configuration।
- **Preview-only external changes:** relocated control scripts and systemd unit will use project root `/home/user`, external APP_ENV_FILE/APP_STORAGE and external SQL backup. No preview secrets will be put back into downloadable workspace।
- **Backup:** Original files are available in private rollback copy from WF-0004. No ZIP creation।
- **Risk/tests:** Routing/security and fresh DB bootstrap can affect login and checkout; test Apache both root/public document roots, PHP router, production fresh install, demo customer/admin flows, private-path blocking and invalid CSRF।

### WF-0007 — 2026-10-15 12:48:29+06:00
- **Status:** IN PROGRESS — user-priority server consolidation।
- **অনুরোধ:** শুধু1 public tunnel,1 MySQL,1 storefront রাখব; পুরোনো/অতিরিক্ত testing server বন্ধ করব।
- **পূর্বপাঠ:** working-flow rules এবং WF-0005/0006-এর pending অবস্থা।
- **যাচাই:** Current process/port inventory-তে project-এর MySQL/PHP/tunnel/journal-watchers চালু নেই; controlled workspace reorganization-এর জন্য managed service inactive। অন্য চলমান ports platform-owned kernels/proxies—স্পর্শ করা হবে না। পুরোনো preview entries actual running services-এর সমান নয়।
- **Action planned:** Historical tool processes stop/status check; `/opt/storefront-testing/control/`-এর moved configs নতুন project root/external private env/storage-এ point করা; একটি systemd/Supervisor group-এ শুধুমাত্র mysql/storefront/public-link চালু করা।
- **Backup:** মূল runtime config-এর rollback copy `/var/lib/storefront-testing/cleanup-rollback/testing-runtime/`; application/customer DB untouched।
- **Scope সীমা:** আগের Laragon/cPanel installation/docs/tests কাজ এখনো অসম্পূর্ণ (WF-0006); এই server cleanup-কে production-release completion বলা হবে না।

## ৬. গুরুত্বপূর্ণ সীমাবদ্ধতা

এই নথি সহকারীর কাজের audit trail ও পুনরুদ্ধারের সহায়ক নির্দেশনা। এটি নিজে automatic filesystem monitor, backup service বা deletion prevention ব্যবস্থা নয়। লগ রাখা ভুল কমাতে সাহায্য করে, কিন্তু runtime reset, snapshot exclusion বা disk failure বন্ধ করতে পারে না। তাই backup, যাচাই এবং নিরাপদ পরিবর্তনের নিয়মও মানতে হবে।

### WF-0008 — 2026-10-15T12:51:31+06:00
- **WF-0007 COMPLETE (verified):** Exactly one mysqld PID16277, one PHP storefront PID16278 on8081, one cloudflared PID16281. Supervisor programs mysql/storefront/public-link RUNNING. Platform proxies/kernels untouched. Five historical tool IDs returned not_found: already absent, not ten live servers deleted. Old UI cards cannot be removed using available process tools.
- **Runtime changes:** relocated control scripts/config references now use `/home/user`, `/opt/storefront-testing/control`, external private preview.env/storage; systemd symlink updated, daemon-reload/enable/start. Only one new tool entry (Storefront) follows the managed stack journal.
- **Checks:** public homepage200, demo-admin session login, three-day return-policy admin API, logout, catalog120. No extra port8082 listener. Snapshot helper successfully rewrote private SQL backup outside workspace; downloadable storage contains only .htaccess/.gitkeep.
- **Public link:** https://load-tommy-society-gotten.trycloudflare.com (working at check time, no uptime guarantee).
- **Resume WF-0006:** Finish installation, portable SQL, build paths and Laragon/cPanel instructions; no additional persistent servers. CLI-only isolated database tests will use the existing MySQL instance, never the live DB. Original rollback files remain outside download.
- **MODIFY 2026-10-15T12:52:30+06:00** `database/schema.sql` — 21-table fresh schema; remove destructive DROP/LOCK/dump session directives and seeded counters; portable utf8mb4_unicode_ci. Demo inserts retained unchanged, no live DB changes.
- **MODIFY 2026-10-15T12:52:30+06:00** `database/bonon-demo.sql` — 21-table fresh schema; remove destructive DROP/LOCK/dump session directives and seeded counters; portable utf8mb4_unicode_ci. Demo inserts retained unchanged, no live DB changes.
- **MODIFY 2026-10-15T12:52:30+06:00** `package.json` — Correct moved CSS input, neutral project name, remove obsolete QA browser dependency; no Node runtime required.
- **MODIFY 2026-10-15T12:52:30+06:00** `tailwind.config.js` — Scan all application JavaScript when rebuilding Tailwind, not app.js alone.
- **MODIFY 2026-10-15T12:52:30+06:00** `.gitignore` — Retain essential Apache storage/upload protections in version control.
- **CREATE 2026-10-15T12:52:30+06:00** `scripts/cli-common.php` — CLI-only environment/extensions/database helpers; empty-DB-only demo/production install; generated non-demo owner credentials; non-destructive preflight. No web installer or credentials in code.
- **CREATE 2026-10-15T12:52:30+06:00** `scripts/install.php` — CLI-only environment/extensions/database helpers; empty-DB-only demo/production install; generated non-demo owner credentials; non-destructive preflight. No web installer or credentials in code.
- **CREATE 2026-10-15T12:52:30+06:00** `scripts/create-admin.php` — CLI-only environment/extensions/database helpers; empty-DB-only demo/production install; generated non-demo owner credentials; non-destructive preflight. No web installer or credentials in code.
- **CREATE 2026-10-15T12:52:30+06:00** `scripts/check.php` — CLI-only environment/extensions/database helpers; empty-DB-only demo/production install; generated non-demo owner credentials; non-destructive preflight. No web installer or credentials in code.

### WF-0009 — 2026-10-15T12:55:38+06:00
- **MODIFY** `README.md`: replace stale Bunon/Docker/ZIP/startup instructions with current flattened project, safe installer, production owner, backup and deployment limits.
- **CREATE** `docs/START-HERE-BN.md`: Laragon demo, clean production, cPanel both document roots/no-Terminal alternative, cron, permissions, privacy and mandatory go-live checklist.
- **MODIFY** `docs/RETURNS.md`, `docs/DELIVERY-GUIDE.md`, `docs/ERROR-LOG.md`: correct private storage/cron paths; remove instructions to run deleted sandbox fixtures.
- **CREATE outside download** `/opt/storefront-testing/control/qa-fresh-install.php`: throwaway QA DBs/user/private config, on existing MySQL only. Test resources deleted in finally; no additional server and no live DB changes.
- **First test failure:** CLI catalog fixture omitted REQUEST_METHOD; warning on stderr invalidated JSON decoding. Corrected the test request metadata (not an application regression) and reran.
- **PASSED:** demo install21tables/120products/84orders/14fictionalusers; clean production21tables/0products/0orders/1non-demoowner; owner generated password hash verification; preflight for both; reinstall refuses both nonempty DBs; catalog/bootstrap/three-day policy for both; owner overwrite refused. All QA databases/users/private files removed afterwards.
- **Still unverified:** actual Apache root/public rules, actual Windows Laragon/cPanel, full post-refactor browser/return-evidence flow and live merchant integration. Do not label those as passed.

### WF-0010 — 2026-10-15T12:57:16+06:00
- **MODIFY** `docs/QA.md`: current executed installation/preflight/router tests, historical feature coverage and explicit unverified host/browser/live-provider boundaries; no stale commands for deleted fixtures.
- **PASS** PHP lint, Tailwind3.4.19 build from moved input (Browserslist stale-data warning only), public homepage/assets/catalog, seven private/unsafe path blocks, anonymous admin401 and missing-CSRF419. No extra server was started.
- **DELETE 2026-10-15T12:57:16+06:00** `/var/lib/storefront-testing/private/tailwind-qa.css` — temporary successful build output outside download.
- **DELETE 2026-10-15T12:57:16+06:00** `/home/user/.sudo_as_admin_successful` — zero-byte sudo user-home marker, not project source or a running service.
- **Live data preserved:** read-only check120products/85orders/15users including1non-demo/0returns. Preview configuration, encryption key, evidence/storage and SQL snapshot remain private outside workspace; not included in source download. External runtime can still be lost on full environment replacement.
- **WF-0006 outcome:** deployment package structure, safe fresh installers, owner creation, preflight, portable collation, build paths and Laragon/cPanel docs completed. Root/public Apache protections supplied, but actual Apache/Windows/cPanel and full post-refactor browser/evidence regression remain unverified release gates. Not a universal production-ready certification.
- **WF-0007 outcome:** one MySQL + one storefront + one public connector retained. Existing history cards are not running servers; no tool was available to erase UI history. Platform infrastructure untouched.
- **Download audit:** approximately7.4MiB, no ZIP/tar archive, no node_modules/cache/QA screenshots/runtime binaries/preview env or private SQL backup; useful source/assets/docs and this requested ledger retained.
- **Final check 2026-10-15T12:57:16+06:00**: public homepage200; exact application process counts {"mysql": 1, "storefront": 1, "public_tunnel": 1}; testing URL unchanged. Check-time result only, not an uptime promise.

### WF-0011 — 2026-10-15T13:03:00+06:00
- **IN PROGRESS — user-requested clean server reset.** Read ledger before task. Current inventory: one application PHP server, one mysqld, one cloudflared; one unnecessary tool-owned journal follower. Other listeners belong to Arena infrastructure, not duplicate storefronts.
- **STOP tool entries:** six known historical IDs checked; the current Storefront journal follower was stopped, five historical entries already not_found. Confirmed no project journalctl remains.
- **Plan:** snapshot current database; stop/disable/remove current project service registration and Supervisor config; verify zero application servers. Reinstall MySQL/cloudflared binaries at their existing standard OS paths, reinstall the single three-program service configuration, then start via systemd (no new log-following preview wrapper). Preserve DB/private settings/evidence/uploads/source. Keep private rollback outside download. Do not stop platform kernels/proxies.
- **2026-10-15T13:03:25+06:00 STOP VERIFIED:** all project servers and supervisors stopped, zero application server processes. Database snapshot succeeded before stop. Disabled/removal of old service symlinks completed. Unused default supervisor.service had no programs and was inactive; disabled its boot registration to prevent a second manager.
- **CREATE** `/var/lib/storefront-testing/server-reset-20261015-130325` — private rollback outside workspace, not a running installation or ZIP.
- **MOVE 2026-10-15T13:03:25+06:00** `/opt/storefront-testing/mysql` → `/var/lib/storefront-testing/server-reset-20261015-130325/mysql` — remove prior binary/config installation while retaining rollback until new startup verifies.
- **MOVE 2026-10-15T13:03:25+06:00** `/opt/storefront-testing/cloudflared` → `/var/lib/storefront-testing/server-reset-20261015-130325/cloudflared` — remove prior binary/config installation while retaining rollback until new startup verifies.
- **MOVE 2026-10-15T13:03:25+06:00** `/opt/storefront-testing/control/supervisord.conf` → `/var/lib/storefront-testing/server-reset-20261015-130325/supervisord.conf` — remove prior binary/config installation while retaining rollback until new startup verifies.
- **MOVE 2026-10-15T13:03:25+06:00** `/opt/storefront-testing/control/storefront-testing.service` → `/var/lib/storefront-testing/server-reset-20261015-130325/storefront-testing.service` — remove prior binary/config installation while retaining rollback until new startup verifies.
- **COPY** private SQL snapshot to `/var/lib/storefront-testing/server-reset-20261015-130325/pre-reset.sql`; live MySQL data directory untouched.
- **CREATE** `/opt/storefront-testing/control/last-server-reset.json` — reset progress/rollback location only, no credentials.
- **2026-10-15T13:04:02+06:00 REINSTALL:** fresh MySQL8.4.6 and cloudflared2026.10.0 downloads installed under `/opt/storefront-testing`; installer removes its MySQL download archive. Existing PHP8.4.26 runtime retained (not falsely claimed as reinstalled). No DB initialization or data deletion occurred.
- **RECREATE** `/opt/storefront-testing/control/supervisord.conf` and `storefront-testing.service` from validated configuration; exactly three programs. Register one systemd unit and start it without a tool-owned journal watcher.
- **DELETE 2026-10-15T13:04:45+06:00** `/var/lib/storefront-testing/server-reset-20261015-130325/mysql` — obsolete pre-reset executable installation, after freshly installed replacement and public checks passed. Retained small configuration rollback and private pre-reset SQL only.
- **DELETE 2026-10-15T13:04:45+06:00** `/var/lib/storefront-testing/server-reset-20261015-130325/cloudflared` — obsolete pre-reset executable installation, after freshly installed replacement and public checks passed. Retained small configuration rollback and private pre-reset SQL only.
- **DELETE 2026-10-15T13:04:45+06:00** `/home/user/.sudo_as_admin_successful` — auto-created zero-byte sudo marker, not application source.
- **CREATE/REGISTER:** `/etc/systemd/system/storefront-testing.service` and its single enabled boot symlink; no tool-owned preview wrapper or journal follower recreated. `supervisor.service` remains disabled.
- **VERIFIED 2026-10-15T13:04:45+06:00** Supervisor3/3 RUNNING; one mysqld PID17053, one PHP storefront PID17054 on8081, one cloudflared PID17057; zero project journal followers, no8082 server. MySQL localhost3306 has a platform forwarding proxy, not a second MySQL process.
- **PASSED:** local catalog200/120products; public homepage200, admin session login, returns-admin3-day policy, logout. Preserved live counts120products/85orders/15users including1non-demo/0returns. DB/private settings/key/evidence/uploads/source not deleted.
- **COMPLETE:** requested application server reset. Final public link https://load-tommy-society-gotten.trycloudflare.com . Actual stop-to-zero and fresh MySQL/cloudflared installation performed; existing PHP interpreter reused. Historical UI cards cannot be erased through available tools, and are not evidence of additional running servers. Platform infrastructure untouched. No uptime guarantee.

### WF-0012 — 2026-10-15T13:58:43+06:00
- **IN PROGRESS: public link failure.** Ledger read first. Current environment lacks PHP, MySQL, Supervisor and all former /opt/storefront-testing, /var/lib/storefront-testing and private backups. Local8081 connection refused; old trycloudflare hostname cannot resolve. Workspace source remains. This is not merely an unhealthy tunnel.
- **Data limitation disclosed:** previous live preview data/one non-demo account cannot be recovered from absent external runtime backups. New preview will use bundled demo seed, not claim to preserve old live DB.
- **Plan:** add a portable CLI recovery script (no secrets) to project so reset recovery instructions survive; reinstall required runtime outside workspace, one managed mysql/storefront/public-link group, native new tunnel allocation; verify public HTTPS/catalog/login. No ZIP or additional QA server.
- **CREATE/MODIFY 2026-10-15T14:00:47+06:00** `scripts/preview-recover.py` — portable Linux testing recovery,3 managed services, native tunnel, private generated credentials. Initial installation stopped after MySQL download due to audit formatting typo (string called as function); corrected. Initial manual ledger append also had a datetime import typo; corrected here. No database seeded or customer records modified during failure. MySQL extracted and temporary archive removed outside workspace before failure.
- **Preview recovery 2026-10-15T14:00:49+06:00** INSTALL native Cloudflare connector outside workspace.
- **Preview recovery 2026-10-15T14:00:52+06:00** CREATE `/var/lib/storefront-testing/private/fresh-mysql` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:00:52+06:00** CREATE `/var/lib/storefront-testing/private/preview.env` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:00:52+06:00** CREATE `/var/lib/storefront-testing/private/root-password` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:00:52+06:00** CREATE `/opt/storefront-testing/control/setup-db.php` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:00:52+06:00** CREATE `/opt/storefront-testing/control/supervisord.conf` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:00:58+06:00** SEED empty disposable preview DB with bundled fictional demo data; no prior live data recovered.
- **Preview recovery 2026-10-15T14:01:04+06:00** CREATE `/opt/storefront-testing/control/public-url.json` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T14:01:04+06:00** ALLOCATE public testing URL https://define-minus-austin-gabriel.trycloudflare.com; update external APP_URL. Allocation alone is not a health check.
- **VERIFIED 2026-10-15T14:02:25+06:00** New public URL https://define-minus-austin-gabriel.trycloudflare.com; local/public catalog120 products; admin session login,3-day return-policy API and logout passed. An initial independent fetch returned502; subsequent independent catalog fetch and rendered homepage fetch (?check=restored) succeeded. Final clean homepage HTTP200. Do not conceal the initial transient502.
- **Managed process:** `testing-services-88bf57ee` runs foreground Supervisor, not a detached log follower. Exact server counts {"mysql": 1, "storefront": 1, "public_tunnel": 1}. Native tunnel has no extra credential replay HTTP server.
- **Data state:** newly seeded fictional120products/84orders/14users, NOT previous live120/85/15. Old external data/rollback unavailable in replacement environment; user informed. Recovery source now persists in workspace but does not guarantee process uptime or restore absent data.
- **DELETE 2026-10-15T14:02:25+06:00** `scripts/__pycache__` — generated validation cache/sudo marker; no application data.
- **DELETE 2026-10-15T14:02:25+06:00** `.sudo_as_admin_successful` — generated validation cache/sudo marker; no application data.
- **MODIFY 2026-10-15T14:02:25+06:00** `README.md`: persistent recovery-script usage, new URL allocation and explicit lost-runtime/data boundaries.
- **MODIFY 2026-10-15T14:02:25+06:00** `docs/QA.md`: distinguish current fresh-demo recovery from historical live-data-preservation tests.
- **WF-0012 COMPLETE at check time:** testing preview restored with new hostname; no promise of stability across sandbox replacement.

### WF-0013 — 2026-10-15T14:04:31+06:00
- **User scope:** abandon public tunnel; run local Arena preview only. Ledger read. Existing PHP8081 is locally200; MySQL and tunnel currently running.
- **Plan:** stop/remove public-link and current managed storefront entries; keep existing MySQL/data. Start exactly one PHP8081 directly through preview process tool so user gets an actual storefront preview card, not only a database-first manager card. No new tunnel/hostname. Change recovery default to local-only.
- **COMPLETE 2026-10-15T14:05:02+06:00** Stopped and removed Supervisor storefront/public-link programs. MySQL PID9250 preserved, no DB reseed. Started direct tool-owned PHP8081 process `storefront-preview-dd04f654`, UI name **Storefront preview**; only newly exposed port8081.
- **MODIFY** external supervisord.conf: MySQL only; preview.env APP_URL cleared to use relative site links, not obsolete Cloudflare hostname. **DELETE** external public-url.json, obsolete URL status.
- **MODIFY** scripts/preview-recover.py and README.md: future default installation/run local-only, no Cloudflare download or autostart. Local counts1MySQL/1PHP/0tunnels. Syntax validation passed without generated cache.
- **PASSED local checks:** homepage200, catalog120products, session admin login/logout, preview-style Host accepted, no X-Frame-Options block. No claim about user-browser success; no new public link supplied.

### WF-0014 — 2026-10-15T14:59:34+06:00
- **Task:** Admin Products newest-first, without altering storefront ordering. Ledger read first.
- **MODIFY** `public/assets/app.js`: renderAdminTable products branch sorts its filtered COPY by numeric ID descending before row rendering/pagination. Search/category filters keep descending order; shared S.products stays unchanged. No SQL/data/schema/server change.
- **Rollback:** remove `.sort((a,b)=>Number(b.id)-Number(a.id))` immediately after admin-category product filter. Previous app.js SHA256: 936104276e6ce893c962384bb45b72dfc84a9edf083b974e7290befb0db2b14c.
- **PASS 2026-10-15T14:59:34+06:00** Node syntax check and isolated actual renderAdminTable tests: numeric/string IDs descending, pages1/2, category/search, shared catalog unmodified, newly added ID first, empty-results handling. No browser/server test claimed. Only app.js needs replacement in existing Laragon installation; hard refresh after copying.

### WF-0015 — 2026-10-15T18:09:49+06:00
- **IN PROGRESS:** remove unconditional notification indicator; unread-only badge, new-event sound/device mute, race-safe mark-read; order-status tabs/counts and status search; user-authorized Pusher defaults. Ledger read first.
- **Credential handling:** provided secret only in protected server-side private defaults (git-ignored), never browser JavaScript/public API/audit contents. Admin saved configuration can override defaults. No secret value recorded here. Pusher connector unsupported; application REST integration is independent.
- **Environment:** prior local runtime/PHP absent now; do not claim a running preview or modify user Laragon DB. Use targeted isolated validation; no additional public tunnel.
- **CREATE** `app/pusher-defaults.php` — owner-supplied private defaults; mode0600, direct execution returns404, app path denied by routing. Secret intentionally not echoed here.
- **MODIFY 2026-10-15T18:10:33+06:00** `.gitignore` — Exclude private provider credentials from version control.
- **MODIFY 2026-10-15T18:10:33+06:00** `app/config.php` — Support server environment overrides for private Pusher defaults.
- **MODIFY 2026-10-15T18:10:33+06:00** `app/studio.php` — Server-only credential fallback; public realtime config exposes app key/cluster, never secret. Preserve configured admin overrides.
- **MODIFY 2026-10-15T18:10:33+06:00** `app/studio-api.php` — Mark only visible notification snapshot read; monotonic per-user read cursor; return actual remaining unread; reject missing Pusher secret.
- **MODIFY 2026-10-15T18:11:44+06:00** `public/assets/studio.js` — Unread-only count, no false dot; Web Audio chime and persistent per-browser mute/test; dedupe before all alerts; no initial backlog sound; identity/read-race guards; keep polling in hidden tabs (browser throttling still applies).
- **MODIFY 2026-10-15T18:11:44+06:00** `public/assets/app.js` — Remove hard-coded notification dot; add counted status tabs/dropdown sync/keyboard navigation, status+alias search, pagination reset/clamp. Keep newest-first admin products and storefront sorting intact.
- **MODIFY 2026-10-15T18:11:44+06:00** `public/assets/style.css` — Delete obsolete always-on dot CSS; responsive status tabs and notification sound controls.
- **MODIFY 2026-10-15T18:11:44+06:00** `public/assets/studio.css` — Unread-only notification count uses red alert badge.
- **MODIFY 2026-10-15T18:13:17+06:00** `public/index.php` — filemtime cache version for changed app/studio JS and CSS to avoid stale old notification code.
- **Pusher check:** signed read-only GET to configured regional Channels API returned200 with valid channels object. No notification/order event sent; WebSocket end-to-end delivery still requires browser test. Secret not exposed in output.
- **MODIFY 2026-10-15T18:16:18+06:00** `public/assets/studio.js`, `public/assets/app.js`: in-memory mute/dedup fallback if storage denied, unsupported-audio test feedback, accurate private-file versus encrypted-admin-settings text, Bengali status-search labels.

- **CREATE 2026-10-15T18:18:35+06:00** `docs/ADMIN-NOTIFICATIONS-ORDERS.md`: Bengali feature guide, exact10-file upgrade list including private defaults, no DB reset/migration, audio/browser limitations and test boundaries.
- **MODIFY 2026-10-15T18:18:35+06:00** `README.md`, `docs/QA.md`: link upgrade instructions and record current tests.
- **Validation setup:** installed PHP CLI/extensions (no application server) and jsdom26 dependencies under temporary `/tmp/storefront-feature-tests`; temporary PHP/mock-DOM/WebSocket test scripts created there. First DOM fixture failed due to missing permissionText stub; added stub and all tests passed.
- **PASS:** full PHP/JS syntax; DOM/logic unread badge, audio gesture/dedupe/mute/storage-denied fallback, initial silence, stale-account/read responses, read snapshot, status filters/counts/dropdown/keyboard handlers/search/pagination, product DESC unchanged. PHP mocked-query tests passed for scope/read watermark/defaults/encryption/private auth.
- **Live Pusher verification:** signed read-only Channels API200 and private-admin WebSocket subscription succeeded. No notification/order published; no live Laragon checkout-to-sound claim.
- **Security:** secret scan passed for public code/README/ledger; direct private config execution prints nothing; source secret intentionally only in protected git-ignored app/pusher-defaults.php.
- **DELETE 2026-10-15T18:18:35+06:00** `/tmp/storefront-feature-tests` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **DELETE 2026-10-15T18:18:35+06:00** `/tmp/update-storefront-notifications.py` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **DELETE 2026-10-15T18:18:35+06:00** `/tmp/update-storefront-ui.py` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **DELETE 2026-10-15T18:18:35+06:00** `/tmp/storefront-feature-install.log` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **DELETE 2026-10-15T18:18:35+06:00** `/tmp/storefront-feature-tests-install.log` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **DELETE 2026-10-15T18:18:35+06:00** `/home/user/.sudo_as_admin_successful` — temporary tests/dependencies/build-edit helper/install log/sudo marker after validation; no project data.
- **WF-0015 COMPLETE:** source changes ready for existing Laragon install; apply documented files together and hard refresh. No SQL import required. Actual user-browser sound/real-order integration remains user-host acceptance.

### WF-0016 — 2026-10-15T18:27:33+06:00
- **IN PROGRESS:** SMS OTP password reset (user selected; provider question skipped), admin OTP/SMS settings/logs and separate consent-based marketing; optional customer email/marketing audience; icon-only account header, generated-password save support, checkout success error, My Returns ordering and parcel tracking.
- **Security scope:** no phone-number-only reset, no exposed OTP, no automatic activation without configured/tested SMS gateway; OTP never reused as marketing. Signup still no OTP; email optional. No real SMS/campaign sends without merchant configuration and explicit action.
- **Plan:** additive migration, bounded single-use challenges/rate limits/session invalidation, private gateway secrets, explicit opt-in/export/campaign queue, unsubscribe, focused tests and upgrade guide. Existing databases must not be reset.
- **Preview recovery 2026-10-15T18:30:03+06:00** INSTALL MySQL8.4.6 binaries outside workspace; DELETE temporary download archive.
- **Preview recovery 2026-10-15T18:30:07+06:00** CREATE `/var/lib/storefront-testing/private/fresh-mysql` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T18:30:07+06:00** CREATE `/var/lib/storefront-testing/private/preview.env` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T18:30:07+06:00** CREATE `/var/lib/storefront-testing/private/root-password` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T18:30:07+06:00** CREATE `/opt/storefront-testing/control/setup-db.php` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T18:30:07+06:00** CREATE `/opt/storefront-testing/control/supervisord.conf` — private runtime/configuration; no secrets copied into project.
- **Preview recovery 2026-10-15T18:30:23+06:00** SEED empty disposable preview DB with bundled fictional demo data; no prior live data recovered.
- **MODIFY 2026-10-15T18:35:08+06:00** `app/communications.php` — Automatic OTP/marketing disabled in demo mode; shared demo accounts cannot send paid messages.
- **MODIFY 2026-10-15T18:35:08+06:00** `app/bootstrap.php` — Password fingerprint binds sessions to current password; resets invalidate all older sessions. Existing pre-update sessions must sign in again. Load communications helpers.
- **MODIFY 2026-10-15T18:35:08+06:00** `public/api.php` — Wire recovery/consent/admin routes and CSRF; native password-manager credential confirmation; bind sign-ins to password fingerprint; preserve committed-order success even if notifications/preferences fail; owner-authenticated idempotent retry.
- **MODIFY 2026-10-15T18:35:08+06:00** `database/schema.sql` — Include six additive communications tables in fresh installations; existing demo rows unchanged.
- **MODIFY 2026-10-15T18:35:08+06:00** `database/bonon-demo.sql` — Include six additive communications tables in fresh installations; existing demo rows unchanged.
- **CREATE 2026-10-15T18:35:08+06:00** `scripts/migrate-communications.php` — CLI-only, idempotent schema migration. No drops/imports/credential activation.
- **CREATE 2026-10-15T18:35:08+06:00** `scripts/communications-worker.php` — CLI-only OTP-priority SMS worker, one-shot cron or explicit foreground worker, database lock, no automatic uncertain retries.
- **MODIFY 2026-10-15T18:35:08+06:00** `scripts/check.php` — Preflight recognizes additive communications tables and dynamic table count.
- **MODIFY 2026-10-15T18:36:09+06:00** `public/assets/app.js` — Icon-only accessible account button; first-class tracking tab; explicit newsletter opt-in. Existing product DESC/order filters untouched.
- **MODIFY 2026-10-15T18:36:09+06:00** `public/assets/enhancements.js` — Remove duplicate fake tracking-as-orders button; route promise support; match password byte limit.
- **MODIFY 2026-10-15T18:36:09+06:00** `public/assets/returns.js` — Place My Returns before Sign out, not after it.
- **MODIFY 2026-10-15T18:36:09+06:00** `public/assets/studio.js` — Pause new background auth-dependent polls during login/checkout credential transition.
- **MODIFY 2026-10-15T18:36:09+06:00** `public/api.php` — Remove old non-consent newsletter write path rather than leaving a legacy alias accessible.
- **2026-10-15T18:39:15+06:00** `database/communications.sql` — CREATE six additive tables for preferences, explicit consent, OTP hashes, encrypted outbox, campaign metadata and masked logs.
- **2026-10-15T18:39:15+06:00** `app/communications.php` — CREATE bounded single-use session-bound/purpose-bound reset and SMS consent challenges, fixed TLS BulkSMSBD adapter, no OTP logging, queue/daily cap, unknown-send no-retry.
- **2026-10-15T18:39:15+06:00** `app/communications-api.php` — CREATE CSRF-protected recovery/admin settings/test/logs/audience export/campaign/opt-out routes. Gateway disabled by default. Legacy marketing consent not inferred.
- **2026-10-15T18:39:15+06:00** `public/assets/communications.js` — CREATE account recovery/manager-save form, confirmed-order screen, real tracking action, opt-in profile/checkout, admin communications tabs/settings/test/queue/consent/export/campaigns/logs.
- **2026-10-15T18:39:15+06:00** `public/assets/communications.css` — CREATE responsive feature styling.
- **2026-10-15T18:39:15+06:00** `public/index.php` — MODIFY load new assets with cache version; bump changed enhancements/returns assets.
- **2026-10-15T18:39:15+06:00** `public/api.php` — MODIFY admin customers email display to optional contact email when available.
- **Reproduction:** new seeded local checkout before modifications completed without A little pause. Exact user-host error not reproduced; added committed-order confirmation isolation, post-commit notification failure protection, session binding and stale-session mitigation rather than claiming an observed root cause. Only1 local MySQL+1 PHP preview, no public tunnel.

- **MODIFY 2026-10-15T18:48:43+06:00** `app/communications.php`, `app/communications-api.php`, `public/api.php`, `public/assets/communications.js` — preserve existing checkout consent/email, keep explicit profile withdrawal, attach unclaimed newsletter consent only after explicit authenticated opt-in, skip disabled queue channels, clear expired unsent OTP payloads, bounded cleanup, show rejected test honestly, CSV formula protection including leading whitespace/control characters, credential-modal recovery and post-commit response isolation. Bengali OTP/test templates for Bangladesh masking-language compatibility; live provider rules still unverified.
- **CREATE/MODIFY 2026-10-15T18:48:43+06:00** `docs/ACCOUNT-COMMUNICATIONS.md` CREATED; `README.md`, `docs/START-HERE-BN.md`, `docs/QA.md` MODIFIED — file list, six-table migration/27 total, session re-login, merchant/test/cron steps, consent and external-email limitations, exact executed tests and unverified hosting/delivery.
- **TEST 2026-10-15T18:48:43+06:00** Playwright1.56.1 + Chromium141 installed outside project; `/tmp/storefront-browser/repro.cjs` and `acceptance.cjs` CREATED and run. Real checkout plus injected post-confirmation catalog503 passed, native credentials POST303, returns/tracking/header/preferences/admin/mobile. Temporary `/tmp/communications-qa.php` and `communications-api-qa.php` CREATED:47 isolated assertions passed with injected CLI transport only. Initial fixture failures (unauthenticated purpose test and intentional duplicate recipient) corrected in fixture; cleanup tracked below.
- **TEST 2026-10-15T18:48:43+06:00** Fresh QA schema27tables, additive preview migration/re-run, PHP/JS lint and preflight passed. Preview SMS not configured; private preview Pusher disabled; no real SMS/email/Pusher order publication. Existing sole runtime retained.
- **MODIFY 2026-10-15T18:51:49+06:00** `public/api.php` — admin subscriber count reflects active opted-in email contacts after migration, not legacy unproven subscribers. Final PHP syntax checked.
- **CREATE/TEST/DELETE 2026-10-15T18:51:49+06:00** `/tmp/storefront-browser/final-acceptance.cjs` — final real-browser regression after post-commit changes passed. `/tmp/communications-cleanup.php` — removed3 specifically identified fictional customers/3 test orders and exact quantities restored; seed data kept. Removed one abandoned isolated QA database/storage from the initial fixture early-exit and revoked its temporary grant; all47-assertion successful runs already cleaned their own DBs. CLI cleanup emitted session-header warnings because it printed before bootstrap; cleanup still completed. Removed 4 matching fixture-only warning entries; unrelated private error history retained.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/wire-communications.py` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/patch-customer-ui.py` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/communications-qa.php` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/communications-api-qa.php` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/communications-cleanup.php` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/communications-runtime-install.log` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/storefront-browser-install.log` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/tmp/storefront-browser` — directory tree (518 files); temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/home/user/.npm` — directory tree (175 files); temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/home/user/.cache/fontconfig` — directory tree (19 files); temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/home/user/.sudo_as_admin_successful` — file; temporary fixture/edit helper/install log/browser npm dependency/cache/sudo marker, not application source or customer data.
- **DELETE 2026-10-15T18:51:49+06:00** `/home/user/.cache` — empty testing-cache directory after fontconfig cleanup.
- **FINAL CHECK 2026-10-15T18:51:49+06:00**30PHP files linted;5changedJS files syntax checked;47isolated backend/API assertions and real Chromium checkout/credential/returns/tracking/consent/admin/mobile checks passed. Preflight27tables; local homepageHTTP200; sole managed runtime still running (1MySQL,1PHP8081,0tunnels). No ZIP or root `.env` added. SMS gateway stays unconfigured/disabled; no real SMS/email sent.
- **WF-0016 COMPLETE 2026-10-15T18:51:49+06:00:** account/checkout/tracking and consent-based communications source update complete. `docs/ACCOUNT-COMMUNICATIONS.md` contains exact replacement list, additive migration and SMS worker/activation instructions. User-host pause root cause not reproduced; injected follow-up failure regression passed. Actual Laragon/cPanel, Google-save UI, live SMS/courier/email delivery remain unverified as documented.
- **FINAL REVIEW 2026-10-15T18:52:22+06:00** `app/communications.php` — isolate audit-write failure after committed OTP verification, so an already-changed password/consent does not become a false failure response. PHP lint passed; final47-assertion suite predates only this exception-isolation wrapper and admin subscriber-count display adjustment. No additional live sends.

### WF-0017 — 2026-10-15T18:55:43+06:00
- IN PROGRESS: research legitimate free/trial SMS (no actual sends), first-run web installer for DB/admin/shop name/logo with private ownership key, permanent fail-closed install state and existing-install protection; catalog-derived budget limits. Existing preview/data retained; no ZIP/tunnel/duplicate server.
- **CREATE 2026-10-15T18:59:13+06:00** `app/installation.php` — first-run guard/private setup owner key, mutex and permanent state; DB/admin/shop/logo setup with empty-DB restriction and fail-closed partial imports.
- **CREATE 2026-10-15T18:59:13+06:00** `app/installer.php` — first-run guard/private setup owner key, mutex and permanent state; DB/admin/shop/logo setup with empty-DB restriction and fail-closed partial imports.
- **CREATE 2026-10-15T18:59:13+06:00** `public/install.php` — first-run guard/private setup owner key, mutex and permanent state; DB/admin/shop/logo setup with empty-DB restriction and fail-closed partial imports.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `app/config.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `public/index.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `public/api.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `public/router.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `.htaccess` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `public/.htaccess` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `install.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `.gitignore` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `scripts/install.php` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE/MODIFY 2026-10-15T18:59:13+06:00** `public/assets/app.js` — WF-0017 integration: entrypoint guards/routes, private encoded-password config compatibility, CLI installation lock and catalog-derived budget boundaries/reset/live refresh. `install.php` is a new root compatibility entrypoint.
- **CREATE 2026-10-15T18:59:13+06:00** `/tmp/wf17-wire.py` — one-shot integration helper, remove after checks. No preview database reset or SMS sent.
- **MODIFY 2026-10-15T19:08:57+06:00** `app/installer.php`, `public/install.php`, `scripts/install.php`, `public/assets/app.js` — guarded PHP setup-key file, Bengali font, private interrupted-install logging/public error suppression, NUL-password rejection, CLI mutex/DB lock and large-catalog-safe price reduction.
- **CREATE 2026-10-15T19:08:57+06:00** `docs/WEB-INSTALLER.md` — setup/lock/move guide and sourced trial-SMS options/limitations; no provider activation or delivery claim.
- **CREATE 2026-10-15T19:08:57+06:00** `docs/SMS-TESTING-OPTIONS.md` — setup/lock/move guide and sourced trial-SMS options/limitations; no provider activation or delivery claim.
- **MODIFY 2026-10-15T19:08:57+06:00** `README.md` — web setup/CLI alternatives, sealed demo versus fresh production copies, marker-preserving moves, executed tests/limits. First documentation patch had a Python syntax error before execution; corrected without partial writes.
- **MODIFY 2026-10-15T19:08:57+06:00** `docs/START-HERE-BN.md` — web setup/CLI alternatives, sealed demo versus fresh production copies, marker-preserving moves, executed tests/limits. First documentation patch had a Python syntax error before execution; corrected without partial writes.
- **MODIFY 2026-10-15T19:08:57+06:00** `docs/QA.md` — web setup/CLI alternatives, sealed demo versus fresh production copies, marker-preserving moves, executed tests/limits. First documentation patch had a Python syntax error before execution; corrected without partial writes.
- **CREATE/TEST 2026-10-15T19:08:57+06:00** `/tmp/wf17-prepare.php`, `/tmp/wf17-fixture-path`, `/tmp/wf17-run.py`, `/tmp/wf17-unit.php`, `/tmp/wf17-unit-prepare.php`, `/tmp/wf17-browser/acceptance.cjs`, temporary npm dependency/cache/install log, private `wf17_*` fixture tree — scoped temporary DB user, six isolated databases, source/config/key/state/logo/session fixtures. Two browser runs, clean/partial/CLI cases, logo conversion passed. Existing supervised storefront child temporarily repointed then restored; no second server/tunnel or user DB reset. Cleanup follows.
- **CREATE/TEST 2026-10-15T19:10:54+06:00** `/tmp/wf17-final-prepare.php`, `/tmp/wf17-cleanup.php` — final seventh isolated DB and final real-browser regression passed on latest source, then all7 fixture DBs and scoped test user removed. Nonempty database sentinel verified unchanged before fixture deletion. Existing120-product preview remains intact; actual budget min590/max4390.
- **DELETE 2026-10-15T19:10:54+06:00** `/var/lib/storefront-testing/private/wf17_7e9906b1` — 922 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-browser` — 516 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-npm-cache` — 12 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-prepare.php` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-unit.php` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-unit-prepare.php` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-final-prepare.php` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-cleanup.php` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-fixture-path` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-run.py` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-wire.py` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/tmp/wf17-browser-install.log` — 1 temporary fixture/dependency/cache/config/key/session/logo/helper/log files in this file/tree; not application source or user data. Original supervisor config verified restored first.
- **DELETE 2026-10-15T19:10:54+06:00** `/home/user/.cache/fontconfig` — test-generated cache/marker only.
- **DELETE 2026-10-15T19:10:54+06:00** `/home/user/.cache` — empty test-cache directory.
- **WF-0017 COMPLETE 2026-10-15T19:10:54+06:00** — research documented without any SMS send/provider changes; guarded one-time installer and live catalog-budget ranges implemented.34PHP lint/changedJS syntax,3isolated real-browser runs, clean/partial/CLI/credential/logo checks and existing27-table preflight passed. Original single supervised PHP8081/MySQL restored;0tunnels;0ZIP; no real `.env`/setup key/install marker shipped. Actual Laragon6/Apache/cPanel and live SMS delivery remain unverified.

### WF-0018 — 2026-10-15T19:14:09+06:00
- IN PROGRESS: user requested root `local.md` and `cpanel.md`; write Bengali step-by-step guides matching current one-time web installer, Laragon6 prerequisites, fresh-versus-existing deployment, permanent lock, safe credentials/storage/cron and troubleshooting. Documentation-only; no server/database/application changes planned.
- **CREATE 2026-10-15T19:16:40+06:00** `/home/user/local.md` — Bengali Laragon6 step-by-step requirements, project layout, empty MySQL DB, private setup key, web form/admin/logo/catalog setup, login/checks, alternate8081 development command, troubleshooting and hosting handoff.
- **CREATE 2026-10-15T19:16:40+06:00** `/home/user/cpanel.md` — Bengali fresh-production and existing-shop transfer instructions; HTTPS, document-root layouts, scoped DB user, private key/form/lock, private storage/protection, preflight, optional cron, credential migration, troubleshooting and backups.
- **WF-0018 COMPLETE 2026-10-15T19:16:40+06:00** — both requested root Markdown guides created and checked against current installer/configuration/operations guides; relative links and code fences verified. Documentation-only: no application/config/database/server changes, no ZIP or credentials added. Actual user-host installation not performed.

### WF-0019 — 2026-10-15T19:31:49+06:00
- IN PROGRESS: user supplied https://github.com/elitedevemon/swapnomart.git for project connection. GitHub connector unsupported; prepare local Git safely, exclude private configuration/runtime files and test remote access without prompting for secrets. No upload/push success assumed.
- **MODIFY 2026-10-15T19:31:49+06:00** `.gitignore` — exclude real environment variants/npm/OS caches while retaining the two safe environment templates. Existing Pusher, installer-state/key and private runtime exclusions retained.
- **CREATE 2026-10-15T19:33:53+06:00** `.git/` — initialized local Git on `main`; configured `origin` to the user-supplied HTTPS repository URL (no credentials embedded). Git metadata/index/objects are task-related, not application runtime.
- **CHECK 2026-10-15T19:33:53+06:00** unauthenticated `git ls-remote` succeeded with no refs; GitHub REST metadata reports repository Public, default branch main. No write permission inferred. No Git credential helper configured. Candidate review:150files; known private Pusher secret/common credential patterns absent; protected files ignored. Not an exhaustive secret audit.
- **CREATE 2026-10-15T19:33:53+06:00** `github.md` — exact repository URL, Public-to-Private steps, safe local browser/Git Credential Manager authentication, ignored secrets/runtime limitations, remote restoration after download, non-force commit/push instructions. No remote upload yet.
- **LOCAL COMMIT 2026-10-15T19:33:53+06:00** prepare source using clearly automated identity `Arena project setup <setup@arena.invalid>`, not the user’s personal GitHub identity. Repository visibility and authentication must be resolved before real upload.
- **RESULT 2026-10-15T19:34:20+06:00** initial local commit created,151files. `git push --dry-run origin main` returned128: GitHub username/authentication unavailable with terminal prompts disabled. Dry-run only; no real push or files uploaded, no repository visibility changes, no credentials requested/exposed.
- **MODIFY 2026-10-15T19:34:20+06:00** `github.md` — confirmed local commit exists, explicitly records failed authentication/no upload instead of suggesting full remote connection.
- **WF-0019 LOCAL PREPARATION COMPLETE / REMOTE PUSH BLOCKED 2026-10-15T19:34:20+06:00** — origin set, secret exclusions verified, local source committed with automated metadata, user handoff guide ready. GitHub connector unsupported; user must authenticate on their own machine and optionally change currently-Public repository to Private before pushing. No servers/database/application behavior changed; no ZIP created.

### WF-0020 — 2026-10-15T19:40:56+06:00
- IN PROGRESS: user explicitly requested direct upload to existing GitHub repository. Connector unsupported; attempt official GitHub CLI browser/device authorization. No password/PAT in chat; auth state outside project; user must personally approve. Push only intended source to given remote, no force or visibility changes.
- **CREATE 2026-10-15T19:40:56+06:00** `/tmp/swapnomart-github-cli/gh` — official GitHub CLI v2.102.0, release SHA256 checked; archive handled in memory, no ZIP/download artifact in project. CREATE `/var/lib/storefront-testing/private/swapnomart-github-auth` — private temporary CLI authorization directory, outside workspace/snapshot; delete credentials after use. No new web server/tunnel.
- **AUTHORIZATION 2026-10-15T19:43:58+06:00** official GitHub CLI device flow completed after user explicitly approved; authenticated account `elitedevemon`, target repository push permission true. Token stored only in permission-restricted temporary directory outside project/workspace; never printed/committed. Auth process exited; no network listening port/server created.
- **PRE-PUSH 2026-10-15T19:43:58+06:00** remote verified exactly https://github.com/elitedevemon/swapnomart.git; no existing remote branches. Repository Public; user informed and explicitly requested upload. No visibility/branch-protection/settings changes or force push. MODIFY `github.md` to reflect successful authorization and current workflow.
- **UPLOAD VERIFIED 2026-10-15T19:45:35+06:00** normal non-force push created remote main. GitHub API commit8dfc995af9d712e587b3a0c70c973f7d0a6250a1 matched local HEAD; full recursive tree matched151tracked files, including installer/local/cPanel guides; private Pusher/default env/setup key/state absent. Repository visibility unchanged (Public).
- **DELETE 2026-10-15T19:45:35+06:00** `/var/lib/storefront-testing/private/swapnomart-github-auth` — 2 temporary auth/config files removed. DELETE `/tmp/swapnomart-github-cli` — official temporary CLI binary removed. Final audit-only push uses the already-authorized credential in short-lived process memory, never in source/URL/log. GitHub OAuth grant not revoked automatically.
- **MODIFY 2026-10-15T19:45:35+06:00** `github.md` — upload verified, credentials cleanup/future reauthorization documented.
- **WF-0020 SOURCE UPLOAD COMPLETE 2026-10-15T19:45:35+06:00** — requested repository uploaded and verified; sensitive files excluded; temporary auth files removed; no force push, repository-setting changes, new server/tunnel, database change or ZIP. Final audit/documentation commit is pushed and remote ref checked by this finishing command before reporting success.
