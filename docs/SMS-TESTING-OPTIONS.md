# Free / trial SMS testing options

Checked **15 October 2026**. No message was sent, no provider account was opened and no credits were purchased during this work.

## সরাসরি উত্তর

যেকোনো দেশের যেকোনো নম্বরে সীমাহীন, reliable, completely free SMS পাঠানোর কোনো service আমি যাচাই করতে পারিনি। আমার নিজস্ব SIM, free SMS balance বা সরাসরি telecom connection নেই। Gateway credentials অথবা আপনার নিজের phone/SIM gateway ছাড়া এখানে বাস্তব SMS পাঠানোর ব্যবস্থা বর্তমানে নেই।

অন্যদিকে সীমিত free trial/credits দিয়ে নিজের বা সম্মত test recipient-এর নম্বরে পরীক্ষা করা যায়। Provider approval, route restrictions ও carrier delivery প্রযোজ্য; advertised credit actual delivery guarantee নয়।

## যাচাই করা options

### 1. BD Bulk SMS — bdbulksms.com

Official site নতুন প্রথম account-এর জন্য **10 free SMS credits** এবং Bangladesh local operators-এ service-এর কথা বলেছে। Bangladesh testing-এর জন্য আগে তাদের trial activation/API access ও OTP rules নিশ্চিত করতে পারেন। এটি provider-এর দাবি; আমি account খুলে handset delivery পরীক্ষা করিনি। [1](https://bdbulksms.com/)

**একই রকম নাম নিয়ে ভুল করবেন না:** `bdbulksms.com` এবং project-এ থাকা `bulksmsbd.com` / `bulksmsbd.net` adapter একই integration ধরে নেওয়া যাবে না। এই provider বেছে নিলে official API/response অনুযায়ী নতুন adapter লাগবে।

### 2. Twilio trial

বর্তমান official documentation-এ **30-day trial**, **100 SMS**, সর্বোচ্চ **5 verified recipients**, sign-up-country restriction এবং predefined content/templates-এর সীমা আছে। তাই arbitrary unverified customer number বা নিজের যেকোনো generated message সরাসরি পাঠানোর unrestricted free gateway নয়। Existing/custom/legacy account-এর console restrictions-ও যাচাই করুন। [1](https://www.twilio.com/docs/usage/trials) [2](https://www.twilio.com/docs/usage/tutorials/how-to-use-your-free-trial-account)

বর্তমান project-এ Twilio adapter/Verify integration নেই। একটি API key paste করলেই BulkSMSBD adapter Twilio হয়ে যাবে না।

### 3. Textbelt

Official homepage-এ public `textbelt` key দিয়ে **1 free text per day**-এর option আছে। এটি quick connectivity experiment-এর জন্য, production OTP বা কয়েকদিনের বহু-customer testing-এর জন্য নয়। Bangladesh নম্বরে বাস্তব delivery আমি যাচাই করিনি; আগে নিজের নম্বরের route availability provider-এর সঙ্গে নিশ্চিত করুন। [1](https://textbelt.com/)

### 4. নিজের Android + SIM gateway

**SMS Gateway for Android / SMSGate** phone-কে API-based gateway বানাতে পারে এবং software free/open-source। [2](https://docs.sms-gate.app/) [1](https://docs.sms-gate.app/pricing/)

তবে app free মানে telecom SMS free নয়। SIM balance/SMS bundle এবং operator-এর automation/A2P terms যাচাই করতে হবে। Phone online, permission ও network availability-ও দরকার। Small controlled testing-এ বিবেচনা করা যায়; business OTP-এর availability/latency নির্ভরযোগ্য ধরে নেওয়া ঠিক নয়। Admin panel-এ arbitrary phone-gateway URL খুলে দেওয়া হয়নি; authenticated, restricted integration আলাদাভাবে করতে হবে।

## আমার সুপারিশ

- প্রথমে Bangladesh provider-এর ছোট trial নিজের/সম্মত tester number-এ ব্যবহার করে route/latency পরীক্ষা করুন। Trial যথেষ্ট হলে পরে paid plan নিন।
- Trial provider নির্বাচনের আগে OTP support, API access, masking approval, Unicode/segment billing, test-recipient restrictions ও delivery status জেনে নিন।
- Zero-cost software tests আলাদাভাবে করা যায়: isolated fake transport দিয়ে success/failure/expiry/rate-limit/consent পরীক্ষা। এটি **বাস্তব ফোনে SMS নয়**; customer-facing OTP reveal বা authentication bypass হিসেবে ব্যবহার করা যাবে না।
- কোনো provider-এর free quota এড়াতে বহু account, quota bypass বা unauthorized public endpoint ব্যবহার করবেন না। Marketing-এর জন্য explicit consent এবং opt-out বজায় রাখুন।

বর্তমান project-এর live SMS safely disabled আছে; এই research-এর কারণে কোনো provider স্বয়ংক্রিয়ভাবে enable/change করা হয়নি।
