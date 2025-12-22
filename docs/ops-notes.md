# Ops notes (cPanel / production)

- **Queue worker**: run a database-backed worker (default connection `database`), e.g.  
  `php artisan queue:work database --queue=default --tries=3 --timeout=120`

- **Webhook secret**: set `PAYMENTS_WEBHOOK_SECRET` in `.env`; requests missing/invalid signatures are rejected.
- **Tracking webhook**: set `TRACKING_WEBHOOK_SECRET` in `.env` and send signed updates to `POST /webhooks/tracking/{provider}`.

- **Fulfillment dispatch**: admin can trigger per-item dispatch; ensure queue worker is running so `DispatchFulfillmentJob` executes.

- **Supplier metrics**: refresh in Filament (Suppliers > bulk “Refresh Metrics”). If you prefer cron, call the bulk action regularly or script `php artisan tinker "app(App\\Domain\\Fulfillment\\Services\\SupplierPerformanceService::class)->refreshForProvider(App\\Domain\\Fulfillment\\Models\\FulfillmentProvider::first());"` as a placeholder.
- **Admin access**: users with role `admin` or `staff` can access Filament. Ensure your main user is `admin`.
- **Site settings**: configure contact info, delivery window, and default fulfillment provider in Filament > Site Settings.

## Tracking webhook example

Send `X-Signature` as `hash_hmac('sha256', raw_body, TRACKING_WEBHOOK_SECRET)`:

```json
{
  "order_number": "DS-ABC12345",
  "order_item_id": 10,
  "tracking_number": "ZX1234567890",
  "carrier": "AliExpress",
  "tracking_url": "https://tracking.example/ZX1234567890",
  "shipped_at": "2025-01-10T14:20:00Z",
  "delivered_at": null,
  "events": [
    {
      "status_code": "in_transit",
      "status_label": "In transit",
      "description": "Departed origin facility",
      "location": "Shenzhen",
      "occurred_at": "2025-01-11T09:00:00Z"
    }
  ]
}
```

Example signature (PowerShell, using the raw JSON body):

```powershell
$payload = Get-Content -Raw .\\payload.json
$secret = $env:TRACKING_WEBHOOK_SECRET
$hmac = [System.Security.Cryptography.HMACSHA256]::new([Text.Encoding]::UTF8.GetBytes($secret))
($hmac.ComputeHash([Text.Encoding]::UTF8.GetBytes($payload)) | ForEach-Object { $_.ToString("x2") }) -join ""
```

## Payment webhook example

Send `X-Signature` as `hash_hmac('sha256', raw_body, PAYMENTS_WEBHOOK_SECRET)`:

```json
{
  "event_id": "evt_123456789",
  "order_number": "DS-ABC12345",
  "amount": 129.99,
  "currency": "USD",
  "status": "paid",
  "provider_reference": "txn_987654321"
}
```

Example signature (PowerShell, using the raw JSON body):

```powershell
$payload = Get-Content -Raw .\\payment.json
$secret = $env:PAYMENTS_WEBHOOK_SECRET
$hmac = [System.Security.Cryptography.HMACSHA256]::new([Text.Encoding]::UTF8.GetBytes($secret))
($hmac.ComputeHash([Text.Encoding]::UTF8.GetBytes($payload)) | ForEach-Object { $_.ToString("x2") }) -join ""
```
