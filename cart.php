<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require login for cart
require_login();

$user_id = get_current_user_id();
$message = '';
$message_type = '';

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    
    if ($action === 'add' && $product_id > 0) {
        // Check if product exists and is active
        $product_sql = "SELECT id, name, price FROM products WHERE id = $product_id AND status = 'active'";
        $product = get_single_row($product_sql);
        
        if ($product) {
            // Check if already in cart
            $check_sql = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            $cart_item = get_single_row($check_sql);
            
            if ($cart_item) {
                // Update quantity
                $new_quantity = $cart_item['quantity'] + 1;
                $update_sql = "UPDATE cart SET quantity = $new_quantity WHERE id = " . $cart_item['id'];
                execute_query($update_sql);
            } else {
                // Add new item
                $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
                execute_query($insert_sql);
            }
            $message = "✅ " . htmlspecialchars($product['name']) . " added to cart!";
            $message_type = 'success';
        }
    } elseif ($action === 'remove' && $product_id > 0) {
        $delete_sql = "DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id";
        execute_query($delete_sql);
        $message = "✅ Item removed from cart!";
        $message_type = 'success';
    } elseif ($action === 'update' && $product_id > 0) {
        $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
        if ($quantity > 0) {
            $update_sql = "UPDATE cart SET quantity = $quantity WHERE user_id = $user_id AND product_id = $product_id";
            execute_query($update_sql);
        } else {
            $delete_sql = "DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            execute_query($delete_sql);
        }
        $message = "✅ Cart updated!";
        $message_type = 'success';
    }
}

// Get cart items
$cart_sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = $user_id 
             ORDER BY c.created_at DESC";

$cart_items = get_multiple_rows($cart_sql);

// Calculate totals
$subtotal = 0;
$shipping = 3.00;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping;

$page_title = 'Cart';
$show_search = true;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="section-head">
    <h2>Your Cart</h2>
    <p class="muted">Review your items before checkout.</p>
  </div>
  
  <?php if (!empty($message)): ?>
    <div class="message <?php echo $message_type; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <?php if (empty($cart_items)): ?>
    <div class="card">
      <p class="muted">Your cart is empty. <a href="/products" class="link">Continue shopping</a></p>
    </div>
  <?php else: ?>
    <div class="cart-grid">
      <div class="cart-list card">
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-row">
            <div class="media tiny placeholder"></div>
            <div class="grow">
              <h3><?php echo htmlspecialchars($item['name']); ?></h3>
              <p class="muted">Qty: <?php echo $item['quantity']; ?></p>
              <p class="muted">Price: $<?php echo number_format($item['price'], 2); ?> each</p>
            </div>
            <div>
              <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
              <div class="actions">
                <a class="btn btn-ghost danger" href="/cart?action=remove&product_id=<?php echo $item['product_id']; ?>">Remove</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <aside class="card cart-summary">
        <h3>Order Summary</h3>
        <div class="rows">
          <div><span>Subtotal</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
          <div><span>Shipping</span><span>$<?php echo number_format($shipping, 2); ?></span></div>
          <div class="total"><span>Total</span><span>$<?php echo number_format($total, 2); ?></span></div>
        </div>
        <a class="btn btn-primary full" href="/payment">Proceed to Payment</a>
      </aside>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
