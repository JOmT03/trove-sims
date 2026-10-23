# Trove scaffold â€” what was created & what is next

This scaffold added the **starting files** only (migrations, models, controller stubs).
It did NOT touch any existing file. Nothing here has business logic yet â€” the
controllers return 501 until the logic is built and tested.

## 1. Run the migrations
```
php artisan migrate
```
New tables: `sales`, `sale_items`, `sale_payments`, `inventory_lots`, `day_closures`.

## 2. Modules & next steps (build + test one at a time)

### POS  (app/Http/Controllers/PosController.php)
- Build `pos.index` view (two-pane: products + cart) and `PosController@store`
  (split payments, deduct finished stock, audit). Add routes behind `auth` (+ roles if needed):
  - GET  /pos             -> PosController@index   (pos.index)
  - POST /pos/sales       -> PosController@store   (pos.sales.store)

### Inventory FIFO  (app/Models/InventoryLot.php)
- On restock: create an `InventoryLot` (qty_received = qty_remaining, unit_cost, received_at).
- On consumption (production / order): walk `InventoryLot::fifo()` oldest-first,
  subtract from `qty_remaining`, and use each lot's `unit_cost` to compute real COGS.
- Keep `inventory.quantity_on_hand` as the sum of lot `qty_remaining` (or derive it).

### Reports > End of Day  (app/Http/Controllers/Reports/EodController.php)
- Build `reports.eod` view; add it inside the existing Reports page (alongside Weekly Batch Report).
  - GET  /reports/eod        -> Reports\EodController@index  (reports.eod)
  - POST /reports/eod/close  -> Reports\EodController@close  (reports.eod.close)  [Owner only]

### Orders = Customization  (manual rename â€” do this carefully)
- The existing **Commission** module becomes **Orders** (custom cakes).
  Rename Commission -> Order across model/controller/routes/views, OR point the
  routes at a renamed controller. Remove the OLD walk-in Order module (it is replaced by POS).
- Do this as its own commit so it is easy to review/revert.

## 3. Suggested commit order
1) migrations + models (this scaffold)  2) POS  3) Inventory FIFO  4) Reports/EOD  5) Orders rename
Open a PR per step and test before merging â€” same as the finance branch.