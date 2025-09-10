<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require login for profile
require_login();

$user_id = get_current_user_id();
$user = get_current_user_data();

// Get user's recent orders
$orders_sql = "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at 
               FROM orders o 
               WHERE o.user_id = $user_id 
               ORDER BY o.created_at DESC 
               LIMIT 5";

$recent_orders = get_multiple_rows($orders_sql);

// Get user's wishlist items
$wishlist_sql = "SELECT p.name 
                 FROM wishlist w 
                 JOIN products p ON w.product_id = p.id 
                 WHERE w.user_id = $user_id 
                 ORDER BY w.created_at DESC 
                 LIMIT 5";

$wishlist_items = get_multiple_rows($wishlist_sql);

$page_title = 'Profile';
$show_search = true;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="dashboard">
    <aside class="card dash-nav">
      <h3>Account</h3>
      <a href="#" class="active">Overview</a>
      <a href="orders.php">Orders</a>
      <a href="addresses.php">Addresses</a>
      <a href="/wishlist">Wishlist</a>
      <a href="/cart">Cart</a>
    </aside>
    <div class="card dash-main">
      <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
      <p class="muted">Manage your account and view your order history.</p>
      
      <div class="grid two mt">
        <div class="tile">
          <h4>Recent Orders</h4>
          <?php if (empty($recent_orders)): ?>
            <p class="muted">No orders yet.</p>
          <?php else: ?>
            <ul class="list">
              <?php foreach ($recent_orders as $order): ?>
                <li>
                  #<?php echo htmlspecialchars($order['order_number']); ?> — 
                  <?php echo ucfirst($order['order_status']); ?>
                  <br><small class="muted">$<?php echo number_format($order['total_amount'], 2); ?> • <?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
        <div class="tile">
          <h4>Saved Items</h4>
          <?php if (empty($wishlist_items)): ?>
            <p class="muted">No saved items yet.</p>
          <?php else: ?>
            <ul class="list">
              <?php foreach ($wishlist_items as $item): ?>
                <li><?php echo htmlspecialchars($item['name']); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="tile mt">
        <h4>Account Information</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'] ?? 'now')); ?></p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
