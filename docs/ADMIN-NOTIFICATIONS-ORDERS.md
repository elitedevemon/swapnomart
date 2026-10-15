# Admin update — Notifications, Pusher ও Order status

## কী বদলেছে

### Notification indicator
- Bell-এর স্থায়ী dot সরানো হয়েছে। **Unread notification > 0** হলেই লাল count badge দেখা যাবে; শূন্য হলে কোনো dot/count থাকবে না।
- Notification drawer খুললে দেখানো snapshot পর্যন্ত read হবে। Drawer খোলার সময় নতুন notification এলে সেটি ভুল করে read হবে না।
- আগের account বা পুরোনো in-flight polling response দিয়ে read badge আবার ফিরবে না।

### Notification sound
- নতুন private order notification এলে in-app alert এবং ছোট chime। Public catalog updates বা page load-এর পুরোনো notification-এ chime নেই।
- Bell → **Sound: On · Mute / Sound: Off · Enable**। Preference একই browser-এ থাকে; storage blocked হলে বর্তমান page-এ mute কাজ করে।
- একই notification পুনরায় এলে duplicate toast/chime আটকানো হয়; খুব কাছাকাছি event batch-এ অতিরিক্ত শব্দ কমানো হয়।
- **Test sound** দিয়ে পরীক্ষা করুন। প্রথম click/tap/keyboard interaction-এর আগে browser autoplay policy শব্দ আটকাতে পারে। Device/browser mute বা unsupported audio থাকলে শব্দ শোনা নাও যেতে পারে।
- Browser notification permission ও chime আলাদা controls। Native notification-এ দ্বিতীয় system sound না চাইতে `silent` option পাঠানো হয়; browser support ভিন্ন হতে পারে।
- সাইট খোলা থাকতে হবে। Closed-tab/background Web Push যোগ হয়নি; background tab-এর timer/browser restrictions প্রযোজ্য।

### Pusher defaults
- আপনার দেওয়া App ID/key/secret/cluster server-side defaults হিসেবে সেট করা হয়েছে। Secret এখানে বা public JavaScript-এ লেখা নেই।
- Private file: **`app/pusher-defaults.php`**। এটি `.gitignore`-এ আছে; `app/` web access supplied routing rules-এ blocked; direct execution-এও কোনো config output নেই।
- এই private config-এ secret আছে, তাই পুরো project publicly share/commit করবেন না। শুধু আপনার নিজের hosting-এ protected location-এ রাখুন। Hosting PHP account-এর read permission লাগবে; কখনো 777 দেবেন না।
- Studio → **Live & notifications**-এ default App ID/key/cluster দেখাবে; secret masked থাকবে। আগে নিজে save করা Pusher configuration থাকলে সেটিই priority পাবে—মুছে দেওয়া হবে না।
- এখানে Admin দিয়ে নতুন secret save করলে সেটি private database setting-এ encrypted থাকে। Default private PHP config-এর secret encrypted নয়; সেটিকে server-side secret file হিসেবেই সুরক্ষিত রাখুন।
- চাইলে `.env`/server environment-এ `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`, `PUSHER_ENABLED` দিয়ে fallback defaults override করা যায়। Explicit saved Admin settings-এর priority থাকে।
- আপনার দেওয়া credentials-এর signed read-only REST check **HTTP 200** এবং authenticated private-admin WebSocket subscription সফল হয়েছে। কোনো order/notification publish করা হয়নি। Polling fallback রাখা হয়েছে।
- Secret chat-এ শেয়ার করা হয়েছে; project অন্য কারও সঙ্গে share করার আগে Pusher থেকে rotate করে private config/Admin settings আপডেট করুন।

### Orders
Tabs: **All orders, Pending, Processing, Shipped, Partially delivered, Delivered, Returned, Cancelled**। প্রতিটিতে status অনুযায়ী মোট count থাকবে।

- Tab ও dropdown একই filter নিয়ন্ত্রণ করে; status পরিবর্তনে page1-এ যায়।
- Search-এ order/customer/phone-এর পাশাপাশি status-ও পাওয়া যাবে। যেমন: `pending`, `shipping`, `shipped`, `returned`, `পেন্ডিং`, `শিপিং`।
- Selected status এবং search একসঙ্গে apply হয়। সব status-এ search করতে **All orders** বেছে নিন।
- Mobile-এ tabs অনুভূমিক scroll করে। Keyboard-এ arrow/Home/End supported।
- Admin Products-এর সর্বশেষ **ID descending** পরিবর্তন অক্ষত আছে।

## Laragon-এর existing project আপডেট

আগে নিজের files/DB/private storage backup নিন। নিচের files একই path-এ replace/copy করুন:

```text
app/config.php
app/studio.php
app/studio-api.php
app/pusher-defaults.php          ← private; নতুন ফাইল
public/index.php
public/assets/app.js
public/assets/studio.js
public/assets/style.css
public/assets/studio.css
.gitignore                      ← private config commit ঠেকানোর rule
```

নিজের `.env`, database, uploads বা `storage/.encryption-key` overwrite/delete করবেন না। **এই update-এর জন্য database import, reset বা migration লাগবে না**, যদি existing Website Studio notification tables আগে থেকেই থাকে।

সব updated files একসঙ্গে কপি করে **Ctrl+F5** দিন। Changed JS/CSS-এ cache version-ও যোগ করা হয়েছে। PHP OPcache enabled হলে Laragon restart করুন।

### নিজে পরীক্ষা করুন
1. Admin login; unread নেই → bell-এ dot নেই।
2. Bell → Test sound। Mute করে আবার পরীক্ষা; refresh-এর পর mute preference থাকে কি না দেখুন।
3. অন্য browser/session থেকে একটি test order করুন। Admin page খোলা রাখুন; নতুন alert/badge/chime আসে কি না দেখুন। Mute থাকলে badge আসবে, chime নয়।
4. Drawer খুলে read করুন; নতুন unread না থাকলে badge চলে যাবে।
5. Orders tabs/counts, shipping/pending search ও pagination পরীক্ষা করুন।
6. Studio → Live & notifications-এ Pusher connection status দেখুন। সাধারণ chime-এর জন্য browser notification permission লাগে না; native browser notification-এর জন্য supported secure context/permission প্রয়োজন। Localhost exception থাকলেও plain-HTTP custom `.test` domain-এ কিছু browser feature সীমিত হতে পারে।

## যাচাইয়ের সীমা
PHP/JS syntax, actual functions দিয়ে isolated DOM/logic tests, PHP mocked-query tests, live Pusher REST/WebSocket transport checks হয়েছে। Secret public assets/API configuration-এ নেই—এটিও পরীক্ষা হয়েছে।

আপনার Windows Laragon-এ বাস্তব order → MySQL → PHP → browser sound পুরো flow এই পরিবেশ থেকে চালানো হয়নি। DOM tests বাস্তব speaker-এ শব্দ শোনার পরীক্ষা নয়। SQL schema/data পরিবর্তন করা হয়নি; নতুন public tunnel/server-ও চালানো হয়নি।
