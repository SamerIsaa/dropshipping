# Azura Operations Booklet

## 1) Quick start checklist
- Run migrations: `php artisan migrate`.
- Create the first Site Settings record in Admin.
- Create Home Page Settings (optional) to override default homepage content.
- Add Categories and Products.
- Add Suppliers/Fulfillment Providers and map Supplier Products.
- Seed test data if needed: `php artisan db:seed --class=FullTestDataSeeder`.

## 2) Admin navigation map
- Catalog: Products, Categories, Product Reviews.
- Operations: Orders, Shipments, Fulfillment Jobs, Return Requests, Refunds.
- Payments: Payments, Payment Methods.
- Storefront: Home Page Settings.
- Administration: Site Settings, Users.
- Suppliers: Suppliers, Supplier Products.

## 3) How to create a product from AliExpress (manual)
1) Create or choose a Supplier:
   - Admin → Suppliers → Create.
   - Type: `aliexpress` (or custom), set name, contact info, and enable `is_active`.
2) Create the Product:
   - Admin → Products → Create.
   - Name, Category, Description, Slug (auto).
   - Pricing: Selling price + Cost price.
   - Supplier: select the AliExpress supplier you created.
   - Supplier product URL: paste the AliExpress product link.
   - Shipping estimate days: enter a realistic estimate.
   - Optional: add SEO meta title/description.
3) Add product images:
   - Product → Images tab → Add image URLs (or upload if configured).
4) Add variants (if applicable):
   - Product → Variants tab → Add sizes/colors.
   - Price/compare_at_price, SKU, currency, metadata.
5) Add supplier mapping (optional for automation):
   - Admin → Supplier Products → Create.
   - Link to product variant + supplier, add supplier SKU.

## 4) Homepage content (Noon-style)
1) Admin → Home Page Settings → Edit.
2) Top Strip: icon + title + subtitle (3 cards recommended).
3) Hero Slides:
   - Upload image, enter kicker/title/subtitle.
   - Primary/secondary CTA labels + links.
   - Meta list (tags like “Fast dispatch, Duty clarity”).
4) Rail Cards: short spotlight cards next to hero.
5) Banner Strip: bottom CTA banner.
6) Use “Preview” to open the homepage.

## 4.1) Category content + SEO
- Admin → Categories → Edit:
  - Hero title/subtitle, hero image, CTA label + link.
  - Description + SEO meta title/description.
- Category pages now live at `/categories/{slug}`.

## 4.2) Admin dashboard
- Admin landing page shows KPIs (orders, revenue, pending reviews, open returns).
- Operations overview widget flags pending orders, fulfillment issues, and payment issues.

## 5) Orders, fulfillment, tracking
- Orders appear in Admin → Orders.
- For manual payments: use “Mark as Paid” on the order.
- Fulfillment jobs appear in Admin → Fulfillment Jobs.
- Shipping/Tracking:
  - Update shipments and tracking events in Admin → Shipments.
  - Once items are delivered, order status becomes fulfilled.

## 6) Reviews moderation
- Customers can submit reviews only for fulfilled items.
- Reviews are pending by default unless auto-approve is enabled.
- Admin → Product Reviews → set status to Approved.
- Auto-approve:
  - Admin → Site Settings → enable “Auto-approve reviews”.
  - Or set “Auto-approve after (days)”.
  - Run command: `php artisan reviews:auto-approve` (scheduled daily).

## 7) Returns / RMA flow
- Customers request a return on the Order details page.
- Admin → Return Requests:
  - Approve → Mark received → Mark refunded.
  - Use Order actions to issue refunds if needed.

## 8) Site settings
- Support email/WhatsApp/phone.
- Shipping/customs messaging.
- Default fulfillment provider.
- Review automation rules.

## 9) Customer experience
- Storefront: browse, add to cart, checkout.
- Account: profile, addresses, payment methods, coupons, gift cards.
- Order details: review + return request after delivery.

## 10) Routine maintenance
- Verify supplier URLs + pricing weekly.
- Approve reviews daily.
- Track return requests and update statuses.
- Keep Home Page Settings updated with seasonal promos.
