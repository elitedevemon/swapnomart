# Public setup key — controlled testing only

**Owner-requested exception:** `app/.setup-key.php` now contains an intentionally published, fixed TEST key. The owner explicitly selected this after being informed of the first-admin takeover risk. It is not a private production credential.

## Testing-এ কী করবেন

1. আলাদা, নিয়ন্ত্রিত local/test environment-এ fresh project চালান।
2. `app/.setup-key.php` file text editor/File Manager-এ খুলুন।
3. `PRIVATE SETUP KEY:` label-এর পরের 64-character value installer-এ দিন। Label-টি existing parser-এর জন্য; এই published value গোপন নয়।
4. Admin/database/shop setup করুন। Existing installation-এর permanent lock আগের মতোই প্রযোজ্য; এই key সেটি খুলতে পারে না।

## গুরুত্বপূর্ণ ঝুঁকি

Public GitHub repository থেকে সবাই এই key দেখতে বা copy করতে পারে। এই shared key-সহ fresh installer internet-এ খোলা থাকলে অন্য ব্যক্তি প্রথম admin তৈরি করতে পারে। HTTPS এই public-key সমস্যা সমাধান করে না। **এই key production-এ ব্যবহার করবেন না।**

## Fresh production-এর আগে

এগুলো **শুধু নতুন/uninstalled production copy**-র জন্য। Existing live shop-এর `.env` বা `app/.installation-state` মুছবেন না।

1. Private production configuration এবং source copy আলাদা করে প্রস্তুত করুন।
2. Git-managed production copy হলে প্রথমে public test file-টির tracking সরান:

```sh
git rm --cached -- app/.setup-key.php
git commit -m "Remove shared test setup key from production source"
```

এতে current production branch থেকে file tracking সরবে; পুরোনো public test key history-তে থাকতে পারে, তাই সেটি কখনো real key হিসেবে reuse করবেন না।

3. File Manager দিয়ে working directory-র `app/.setup-key.php` **test file-টি delete করুন**। অন্য config/installation-state নয়।
4. Fresh installer খুলুন। এবার server নতুন random private `app/.setup-key.php` তৈরি করবে।
5. সেই server-local key ব্যবহার করুন; GitHub/chat/screenshot-এ share করবেন না।
6. নিশ্চিত করুন private file ignored এবং tracked নয়:

```sh
git check-ignore app/.setup-key.php
git ls-files -- app/.setup-key.php
```

প্রথম command-এ path দেখাবে, দ্বিতীয়টিতে কোনো output থাকবে না। Generated private key commit করবেন না।

**কেন untrack করা জরুরি:** `.gitignore` আগে থেকেই tracked file লুকায় না। Owner-এর অনুরোধে এই public test fileটি force-added হয়েছে; তাই এটিকে private key দিয়ে overwrite করে `git add .` চালালে private key leak হতে পারে। আগে tracking সরাতেই হবে।

Git ব্যবহার না করা fresh deployment-এ upload-এর আগে public test fileটি বাদ দিন; installer server-local private file তৈরি করবে।

অন্য private `.env`, Pusher secrets, install-state ও runtime storage files GitHub-এ upload করার অনুমতি এই exception দেয় না; সেগুলো excluded থাকছে।
