# Shopping update · v3.1

## Customer experience
- Favorite add/remove and cart additions use non-blocking, dismissible bilingual toast messages.
- Clicking a product-photo cart icon no longer opens the shopping bag. Single-size products add immediately; multi-size products show an inline size selector. The toast has an explicit **View cart** action.
- Added Hot selling (last 30 days), Best sellers (all delivered orders), New arrivals, editorial Best picks, actual price-reduction Offers, five category shelves, Under ৳1,000 and visitor-local Recently viewed.
- Ranking is derived from delivered-order quantities, not fabricated counters. Demo rankings are labelled; live mode excludes demo orders. Empty shelves are hidden.
- Admin → **Product showcases** controls section visibility and 4/8 products per shelf. Product editor → **Feature in Our best picks** selects editorial products.
- The previous wordmark is replaced by a temporary circular, name-free symbol and matching favicon. Generic “Lifestyle Store” metadata is used until the owner supplies a new name. Brand/SEO settings can replace it later; internal existing order IDs/database identifiers are retained.

## Product image processing
Requires PHP GD with JPEG/PNG/WebP support; EXIF support enables orientation correction.
- Product uploads are decoded and re-encoded into **1200 × 1500 WebP** with a **480 × 600 WebP** thumbnail.
- Aspect ratio is preserved with neutral padding: no stretching or product cropping. Small source images are not artificially enlarged; upload a sufficiently detailed source for good zoom quality.
- JPEG orientation is corrected when EXIF is available. Re-encoding strips camera/location metadata.
- General media uploads preserve aspect ratio and are bounded to a 2400px long edge. This keeps logos/banners distinct from the product preset.
- Images selected from the media library are normalized when saving a product; existing catalog images are not destructively batch-overwritten.
- Current limits: 2 MB per upload, 16 megapixels, 10,000px maximum side; server-memory validation and raster MIME checks apply. Larger/invalid images get a clear error.

## Progress and loading
- Non-blocking indeterminate loading indicator for foreground requests/navigation; routine background polling stays quiet.
- Button spinners and accessible busy/status feedback during form submissions.
- Image upload progress measures actual uploaded bytes, then changes to **Processing image** until the server finishes. A successful response displays final dimensions/file size.
- Product saving is blocked during image upload/processing. Failed uploads show an inline error and can be retried.
- Lazy-loaded product images have skeleton placeholders; reduced-motion preferences are respected.
- Overlapping route/save renders are serialized so the final screen follows the latest URL.

## Verification
An isolated MySQL/browser test passed cart/favorite toasts, inline size selection, explicit View cart, collection filters, local recent history, admin visibility controls, upload progress/busy guards, image conversion/thumbnail dimensions, product-save persistence, invalid upload rejection, Bengali mobile layouts and dark logo switching. No browser exceptions were recorded. A separate pixel check confirmed that source-image edges remain visible and proportional padding is used.

Run `php scripts/migrate-shopping.php` after upgrading an existing database. Do not replace a live database with the clean demo SQL. Install PHP GD/EXIF and restart PHP workers before enabling image uploads.
