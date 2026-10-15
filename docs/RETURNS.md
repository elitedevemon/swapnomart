# Product returns

## Entry points
- Public policy: `/#returns-policy` (also Delivery & returns in the footer/checkout).
- Customer: My account → My orders → open a delivered order → Request return. My returns lists requests and the timeline.
- Administrator: Returns desk → Requests or Policy settings.

## Policy and eligibility
Default: **3 × 24 hours from recorded delivery**, including the final boundary second. Wrong, damaged, defective or materially misdescribed products only; no size-preference or change-of-mind reason. Optional evidence, at most three photos per submitted request. Requests outside the known window are blocked. Missing historical delivery times and partially delivered orders are explicitly flagged for human eligibility verification; approval requires confirmation. Never infer a historical delivery date from the migration date.

The migration freezes the current policy onto pre-existing orders without a snapshot; checkout saves a snapshot for new orders. Each request also keeps its policy copy. Admin can change the enabled flag, 1–30 day window and bilingual terms for future orders. Old order terms remain available through “Saved order policy”. Newly observed full delivery sets the first timestamp only; courier updates prefer a valid provider update time, otherwise the observation time. This is not proof of physical handover: verify disputed delivery evidence with the customer/carrier.

## Workflow
`requested → approved → in_transit → received → completed`
- Requested and approved cases can be rejected; received cases can be rejected after inspection.
- Direct approved → received is allowed for a store handover.
- Customer may withdraw only a requested case.
- Each update adds a customer-visible timeline note. Internal notes remain admin-only.
- Concurrent submissions lock the original order. Active/completed quantities reserve order-item quantities; cancelled/rejected cases release them. Request keys prevent a repeated submit from creating a second case. Admin updates check the expected old status.
- Completion requires confirmation of an already performed external refund or replacement plus a transaction/dispatch reference. Positive refunds have at most two decimals, and combined recorded refunds cannot exceed the original order total. The UI suggests discounted item value; staff must verify the agreed amount and delivery-cost allocation.
- **No automatic money transfer, payment gateway action, return courier booking/pickup or restock.** The original order status, shipment and stock remain unchanged. Inspect goods before manually adjusting stock. Return transport, costs and settlement timing must be agreed in the customer-visible approval note.

## Private evidence
JPG/PNG/WebP, ≤2 MB and ≤12 MP; re-encoded to ≤1200 px JPEG without source metadata. Files are under private `APP_STORAGE/return-evidence` (default: `storage/return-evidence`), not the public media library, and served only to the owner/admin with no-store headers. Unsubmitted photos are restored on reopening the order's form and may be deleted by the owner. Attached case photos cannot be deleted through the draft action. Up to twelve unattached photos per order and upload rate limiting prevent accidental accumulation; submit no more than three.

Back up the private database **and** evidence directory. Keep `storage` outside the document root. Use HTTPS and real admin accounts; demo admin access is intentionally public and unsuitable for real customer data. For abandoned drafts run:

```sh
php scripts/cleanup-return-evidence.php          # dry run
php scripts/cleanup-return-evidence.php --apply  # only unattached >7 days, max1000/run
```

Attached evidence has no automatic deletion policy; establish retention rules and authorized removal/backups with the store before production.

## Upgrade
Back up first; run the existing Studio/courier/shopping migrations as needed, then:

```sh
php scripts/migrate-returns.php
```

It is idempotent and does not invent delivery dates. Fresh `database/schema.sql` and `database/bonon-demo.sql` include the return schema. Never import the demo dump over a live database.

## Verification
Historical isolated fixtures covered permissions/CSRF, invalid reasons, quantities/idempotency, expiry, manual review, evidence access, status transitions, settlement safeguards and concurrent quantity reservation. Those environment-specific fixture scripts are not included in the cleaned deployment package. See `QA.md` for current checks and `START-HERE-BN.md` for the required host acceptance checklist. No extra QA server is required to run the application.

Admin request list currently loads the latest1000 cases; customer list loads the latest200. Add server-side pagination before exceeding those volumes. No real courier acceptance or monetary settlement is tested/executed by this module.
