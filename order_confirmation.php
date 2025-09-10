<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require login
require_login();

$user_id = get_current_user_id();
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get order details
$order_sql = "SELECT o.*, u.name as customer_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = $order_id AND o.user_id = $user_id";

$order = get_single_row($order_sql);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$items_sql = "SELECT oi.*, p.name, p.description 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = $order_id";

$order_items = get_multiple_rows($items_sql);

$page_title = 'Order Confirmation';
$show_search = false;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="card">
    <div class="section-head">
      <h2>✅ Order Confirmed!</h2>
      <p class="muted">Thank you for your purchase.</p>
    </div>
    
    <div class="grid two">
      <div>
        <h3>Order Details</h3>
        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
        <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
      </div>
      
      <div>
        <h3>Billing Address</h3>
        <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
      </div>
    </div>
    
    <div class="mt">
      <h3>Order Items</h3>
      <div class="cart-list">
        <?php foreach ($order_items as $item): ?>
          <div class="cart-row">
            <div class="media tiny placeholder"></div>
            <div class="grow">
              <h4><?php echo htmlspecialchars($item['name']); ?></h4>
              <p class="muted">Qty: <?php echo $item['quantity']; ?></p>
            </div>
            <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="actions mt">
      <a href="/products" class="btn btn-primary">Continue Shopping</a>
      <a href="/profile" class="btn btn-ghost">View Orders</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
