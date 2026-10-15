# Product-save fix and Error Log

## Product creation
The previous new-product form required a separate Bengali name, Bengali description and English URL slug. Bengali-only main names produced an empty slug; native validation could block submission without a useful summary. The form also started with an empty photo gallery without an explicit missing-photo explanation.

The updated flow:
- Name, category, valid price, stock and at least one photo are required.
- Separate Bengali fields are optional; missing values fall back to the main text (not an invented translation).
- A blank slug generates a stable URL; Bengali-only names use a unique `product-…` URL. Existing shared URLs remain stable on edits.
- Missing/invalid fields have an inline summary and a details-enabled toast. Duplicate custom URLs have a precise field error.
- Image processing completes before saving; product/gallery data is committed in one database transaction.

## Admin → Error log
Lists technical errors with timestamp, source, action, route, HTTP status and reference. Search/filter by severity or source, inspect details, or confirm **CLEAR** to empty recorded history. Clearing logs does not change products, orders or settings and does not disable future logging.

Admin error toasts have a **Details** button. The details panel is independent of the product editor so unsaved form inputs are retained. Server exceptions include sanitized type, relative file/line, argument-free call trace and safe database diagnostic codes; raw SQL and parameter values are excluded.

Coverage includes API error responses, handled server exceptions, PHP warnings/fatal/uncaught errors where the handler can run, browser runtime/promise/resource/request errors, form-validation failures and courier failure records after worker restart. Browser reports are rate-limited and repeated identical browser reports are temporarily deduplicated. Errors that happened before installation cannot be recovered retroactively.

## Private storage / limitations
The database-independent JSONL journal is inside private `APP_STORAGE` (default: `storage/`), partitioned by database name, with file locking and 0600 permissions. It is bounded at about 5 MB and retains recent entries when rotating. Keep only `public/` web-accessible, and ensure the application and worker can write to storage. Restart long-lived PHP/queue workers after upgrades.

Request bodies, field values, passwords, session cookies and courier credentials are not intentionally collected. Common secret assignments, email addresses, Bangladesh mobile numbers and URL query strings are redacted. Numeric account IDs may be retained for admin correlation. Do not manually place personal data or secrets into custom exception text.

Logging is best-effort: a browser that closes before sending a report, a network outage, invalid CSRF, rate limiting, an unwritable/full disk or errors occurring before the handler starts cannot be guaranteed to reach the journal. Logs are administrator-only. Public client reporting requires a valid session CSRF token and accepts only bounded technical fields.

## Verified on isolated MySQL
- Bengali-only product + photo creates successfully without separate translations or a manual slug.
- Missing photo shows a precise message; opening error details preserves the product form.
- API validation errors are stored and accessible from the admin toast.
- Email/phone/token redaction, Clear log, guest access denial and Bengali mobile layout pass.
- No unexpected browser exceptions occurred in the test.
