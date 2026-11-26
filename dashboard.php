<?php
require __DIR__ . '/includes/db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PantryPal</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="topbar">
    <strong>PantryPal</strong>
    <span>Hi, <?= htmlspecialchars($_SESSION['user_name']) ?> &nbsp; <a href="logout.php">Log out</a></span>
  </header>

  <main>
    <!-- Dashboard summary -->
    <section class="stats">
      <div class="stat"><b id="sTotal">0</b><span>Total items</span></div>
      <div class="stat warn"><b id="sLow">0</b><span>Low stock</span></div>
      <div class="stat warn"><b id="sSoon">0</b><span>Expiring soon</span></div>
      <div class="stat bad"><b id="sExpired">0</b><span>Expired</span></div>
      <div class="stat"><b id="sShop">0</b><span>To buy</span></div>
    </section>

    <div class="columns">
      <!-- Inventory -->
      <section class="panel">
        <h2>Inventory</h2>
        <form id="groceryForm" class="row-form">
          <input type="hidden" id="gId">
          <input id="gName" placeholder="Item (e.g. Rice)" required>
          <input id="gQty" type="number" step="any" min="0" placeholder="Qty" required>
          <input id="gUnit" placeholder="Unit (kg)" required>
          <input id="gMin" type="number" step="any" min="0" placeholder="Min qty" required>
          <input id="gExpiry" type="date" title="Expiry date">
          <button class="btn" id="gSave" type="submit">Add item</button>
          <button class="btn ghost" id="gCancel" type="button" hidden>Cancel</button>
        </form>
        <input id="search" class="search" placeholder="Search items...">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Item</th><th>Quantity</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
            <tbody id="groceryBody"></tbody>
          </table>
        </div>
        <p id="groceryEmpty" class="muted" hidden>No items yet. Add your first grocery above.</p>
      </section>

      <!-- Shopping list -->
      <section class="panel">
        <h2>Shopping list</h2>
        <form id="shopForm" class="row-form">
          <input id="shName" placeholder="Item to buy" required>
          <input id="shQty" placeholder="Qty (optional)">
          <button class="btn" type="submit">Add</button>
        </form>
        <ul id="shopList" class="shop-list"></ul>
        <p id="shopEmpty" class="muted" hidden>Your shopping list is empty.</p>
        <button class="btn ghost" id="clearBought">Remove purchased items</button>
      </section>
    </div>
  </main>

  <script src="js/app.js"></script>
</body>
</html>
