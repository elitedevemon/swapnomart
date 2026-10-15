# GitHub connection — swapnomart

Repository: `https://github.com/elitedevemon/swapnomart.git`

## বর্তমান অবস্থা

- Local project-এ Git initialized; branch `main` এবং এই repository-র `origin` remote সেট করা হয়েছে।
- Source files-এর initial local commit তৈরি হয়েছে। **GitHub-এ কোনো file upload হয়নি**: non-interactive push dry-run authentication না থাকায় ব্যর্থ হয়েছে। নিজের কম্পিউটারে authenticate করে push করতে হবে।
- যাচাইয়ের সময় GitHub API repository-টিকে **Public** দেখিয়েছে। Private রাখতে চাইলে upload করার আগে নিচের visibility step করুন।
- এখানে সরাসরি GitHub connector সমর্থিত নয়। URL দিলেই account write permission পাওয়া যায় না। Password/access token chat-এ পাঠাবেন না।

## ১. দরকার হলে repository Private করুন

1. GitHub-এ নিজের account দিয়ে sign in করুন।
2. `elitedevemon/swapnomart` repository খুলুন।
3. **Settings → General → Danger Zone → Change repository visibility** খুঁজুন।
4. **Private** বেছে GitHub-এর confirmation সম্পন্ন করুন।

Public রেখেও নিজের project publish করতে পারেন, তবে আগে নিশ্চিত হোন source/demo assets প্রকাশ করা আপনার উদ্দেশ্য এবং সেগুলোর উপযুক্ত ব্যবহারাধিকার আছে। Private repository হলেও secrets commit করবেন না।

## ২. Project নিজের কম্পিউটারে নিন

1. Workspace-এর project files download করুন, যেমন `C:\laragon\www\storefront`-এ।
2. `.gitignore`, `.htaccess` এবং safe environment templates বাদ দেবেন না।
3. Git metadata/remote configuration download-এ আসবেই ধরে নেবেন না। নিচের commands remote নতুন করে সেট বা ঠিক করবে।

## ৩. Git authentication প্রস্তুত করুন

Windows-এ Git for Windows ও Git Credential Manager ব্যবহার করতে পারেন। Push-এর সময় browser sign-in এলে **নিজের কম্পিউটারেই** GitHub account দিয়ে authorize করুন। বিকল্পভাবে GitHub Desktop ব্যবহার করুন।

- GitHub account password terminal-এর Git password field-এ দিয়ে HTTPS push করার চেষ্টা করবেন না।
- Token দরকার হলে শুধু নিজের trusted credential manager/local prompt-এ ব্যবহার করুন; chat, URL, `.env`, source বা screenshot-এ নয়।
- এই sandbox-এ আপনার personal credential পাঠানোর দরকার নেই।

## ৪. Remote যাচাই/সেট করুন

Laragon Terminal বা Git Bash-এ project directory থেকে:

```sh
cd C:/laragon/www/storefront
git status
```

`not a git repository` বললে আগে চালান:

```sh
git init -b main
```

তারপর:

```sh
git remote -v
```

`origin` না থাকলে:

```sh
git remote add origin https://github.com/elitedevemon/swapnomart.git
```

`origin` থাকলে URL ঠিক করুন:

```sh
git remote set-url origin https://github.com/elitedevemon/swapnomart.git
```

## ৫. Private files বাদ আছে নিশ্চিত করুন

```sh
git check-ignore .env .env.local app/pusher-defaults.php app/.setup-key.php app/.installation-state
git status --short
```

প্রথম command-এ sensitive filenames দেখানো মানে ignore rules কাজ করছে। `.gitignore` আগে থেকেই tracked file সরায় না। Existing history-তে real secret থাকলে শুধু file delete করলেই নিরাপদ হবে না—secret rotate এবং history review দরকার। এই project-এর প্রথম local candidate review-এ known private Pusher secret ও common credential patterns পাওয়া যায়নি; এটি exhaustive security audit নয়।

**GitHub copy-তে যা থাকবে না:** real `.env`/environment variants, private Pusher defaults, installation key/state, private storage এবং runtime uploaded files। নতুন clone-এ নিজের configuration/provider credentials আলাদাভাবে দিতে হবে। Installed shop-এর database/uploads/private keys Git backup-এর বিকল্প হিসেবে বাদ দেওয়া যাবে না—সেগুলোর আলাদা private backup রাখুন।

## ৬. দরকার হলে local commit করুন, তারপর push

Local commit download-এ থাকলে এবং `git status` clean হলে নতুন commit লাগবে না। Git history না এলে বা নিজের intended changes থাকলে আগে নিজের name ও verified/noreply GitHub email **আসল values বসিয়ে** configure করুন:

```sh
git config user.name "YOUR_NAME"
git config user.email "YOUR_GITHUB_NOREPLY_EMAIL"
git add .
git diff --cached --stat
git diff --cached --name-only
```

Staged list-এ secrets/backup না থাকা নিশ্চিত করে:

```sh
git commit -m "Initial storefront project"
```

`nothing to commit` হলে আগের commit আছে; সেটি error নয়। Branch যাচাই করুন:

```sh
git branch --show-current
```

Fresh initial repository-র branch `main` না হলে সেটি rename করুন:

```sh
git branch -m main
```

শেষে:

```sh
git push -u origin main
```

Browser authentication prompt এলে নিজে authorize করুন। তারপর GitHub repository refresh করে files ও commit দেখতে পাচ্ছেন কি না যাচাই করুন।

**Push reject হলে `--force` দেবেন না।** আগে `git fetch origin` করে remote-এ অন্য commits/files আছে কি না দেখুন এবং history reconcile করুন।

## পরের updates

প্রতিবার intended changes ও staged files দেখে commit/push করুন। GitHub connection website hosting/deployment নয়। Laragon ও cPanel installation আলাদা: [local.md](local.md), [cpanel.md](cpanel.md)।
